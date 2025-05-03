<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Get the authenticated user details.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentUser(Request $request): JsonResponse
    {
        // The user is already authenticated via the Sanctum middleware
        // so we can directly access the authenticated user
        $user = $request->user();
        
        // Return the user details
        return response()->json([
            'user' => $user,
            'message' => 'User fetched successfully'
        ]);
    }
}