<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of the users
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $users,
            'message' => 'Users retrieved successfully'
        ]);
    }

    /**
     * Store a newly created resource in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json([
            'success' => true,
            'data' => $user->load('role'),
            'message' => 'User created successfully'
        ], 201);
    }

    /**
     * Display the specified resource
     */
    public function show(User $user)
    {
        return response()->json([
            'success' => true,
            'data' => $user->load('role'),
            'message' => 'User retrieved successfully'
        ]);
    }

    /**
     * Update the specified resource in storage
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string',
            'department' => 'nullable|string',
            'role_id' => 'nullable|exists:roles,id',
            'status' => 'nullable|in:active,inactive',
            'password' => 'nullable|string|min:8',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'success' => true,
            'data' => $user->load('role'),
            'message' => 'User updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage
     */
    public function destroy(User $user)
    {
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get all roles
     */
    public function getRoles()
    {
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => $roles,
            'message' => 'Roles retrieved successfully'
        ]);
    }

    /**
     * Create a new role
     */
    public function createRole(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:roles',
            'description' => 'nullable|string',
        ]);

        $role = Role::create($validated);

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => 'Role created successfully'
        ], 201);
    }
}
