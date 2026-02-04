<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $admin;
    protected $role;

    public function setUp(): void
    {
        parent::setUp();

        $this->role = Role::factory()->create([
            'name' => 'admin',
            'description' => 'Administrator'
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->role->id,
            'email' => 'admin@test.com',
        ]);

        $this->user = User::factory()->create([
            'email' => 'user@test.com'
        ]);

        $this->actingAs($this->admin);
    }

    public function test_can_list_users()
    {
        $response = $this->getJson('/api/users');

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonStructure([
                    'data' => [
                        'data' => [
                            '*' => ['id', 'name', 'email', 'status']
                        ]
                    ]
                ]);
    }

    public function test_can_create_user()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'newuser@test.com',
            'password' => 'password123',
            'phone' => '081234567890',
            'department' => 'Sales',
            'role_id' => $this->role->id,
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(201)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.name', 'Test User')
                ->assertJsonPath('data.email', 'newuser@test.com');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com'
        ]);
    }

    public function test_cannot_create_user_with_duplicate_email()
    {
        $userData = [
            'name' => 'Test User',
            'email' => $this->user->email,
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/users', $userData);

        $response->assertStatus(422);
    }

    public function test_can_update_user()
    {
        $updateData = [
            'name' => 'Updated Name',
            'department' => 'Marketing',
        ];

        $response = $this->putJson("/api/users/{$this->user->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.name', 'Updated Name');

        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'department' => 'Marketing'
        ]);
    }

    public function test_can_show_user()
    {
        $response = $this->getJson("/api/users/{$this->user->id}");

        $response->assertStatus(200)
                ->assertJson(['success' => true])
                ->assertJsonPath('data.id', $this->user->id);
    }

    public function test_can_delete_user()
    {
        $userId = $this->user->id;

        $response = $this->deleteJson("/api/users/{$userId}");

        $response->assertStatus(200)
                ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_can_get_roles()
    {
        $response = $this->getJson('/api/users/roles');

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }

    public function test_can_create_role()
    {
        $roleData = [
            'name' => 'manager',
            'description' => 'Manager Role',
        ];

        $response = $this->postJson('/api/users/create-role', $roleData);

        $response->assertStatus(201)
                ->assertJson(['success' => true]);

        $this->assertDatabaseHas('roles', [
            'name' => 'manager'
        ]);
    }

    public function test_can_search_users()
    {
        $response = $this->getJson('/api/users?search=' . $this->user->email);

        $response->assertStatus(200)
                ->assertJson(['success' => true]);
    }
}
