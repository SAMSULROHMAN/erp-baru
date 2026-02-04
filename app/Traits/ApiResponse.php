<?php

namespace App\Traits;

/**
 * Trait untuk standard API response format
 */
trait ApiResponse
{
    /**
     * Return success response
     */
    protected function successResponse($data = null, $message = 'Success', $statusCode = 200)
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return error response
     */
    protected function errorResponse($message = 'Error', $statusCode = 400, $errors = null)
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Return paginated response
     */
    protected function paginatedResponse($data, $message = 'Success', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }
}
