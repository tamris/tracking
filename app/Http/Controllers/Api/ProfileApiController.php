<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileApiController extends Controller
{
    /**
     * GET /api/profile
     * Melihat data diri user yang sedang login
     */
    public function show(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'Profile retrieved successfully',
            'data' => $request->user()
        ], 200);
    }

    /**
     * PUT /api/profile
     * Update nama, email, atau password
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'name'             => ['required', 'string', 'min:3', 'max:100'],
            'email'            => [
                'required', 
                'email', 
                'max:150', 
                Rule::unique('users', 'email')->ignore($user->id) // Abaikan email user sendiri
            ],
            'current_password' => ['nullable', 'string', 'min:6'],
            'password'         => ['nullable', 'string', 'min:6', 'confirmed'], // Butuh field password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        // 2. Logic Update Data Dasar
        $user->name = $request->name;
        $user->email = $request->email;

        // 3. Logic Ganti Password (Opsional)
        if ($request->filled('password')) {
            // Cek apakah current_password diisi?
            if (!$request->filled('current_password')) {
                return response()->json([
                    'status' => false,
                    'message' => 'Current password is required to set a new password.'
                ], 422);
            }

            // Cek apakah password lama benar?
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Password saat ini (Current Password) salah.'
                ], 400);
            }

            // Hash password baru
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $user
        ], 200);
    }
}