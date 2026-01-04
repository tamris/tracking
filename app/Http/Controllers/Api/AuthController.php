<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Google\Client as GoogleClient;

class AuthController extends Controller
{
    public function login(Request $request) // API login
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // opsional: biar 1 token aktif per user
        $user->tokens()->delete();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login sukses',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:6'],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil. Silakan login.',
        ], 201);
    }

    // public function googleLogin(Request $request)
    // {
    //     $request->validate([
    //         'id_token' => ['required', 'string'],
    //     ]);

    //     $client = new GoogleClient([
    //         'client_id' => config('services.google.client_id'), // penting: match sama yang dipakai Flutter
    //     ]);

    //     $payload = $client->verifyIdToken($request->id_token);

    //     if (! $payload) {
    //         throw ValidationException::withMessages([
    //             'id_token' => ['Token Google tidak valid.'],
    //         ]);
    //     }

    //     // google "sub" itu unique user id
    //     $gid   = $payload['sub'] ?? null;
    //     $email = $payload['email'] ?? null;
    //     $name  = $payload['name'] ?? 'Pengguna';

    //     if (! $email || ! $gid) {
    //         throw ValidationException::withMessages([
    //             'id_token' => ['Data akun Google tidak lengkap (email/sub).'],
    //         ]);
    //     }

    //     Log::info('Google mobile login', ['email' => $email, 'gid' => $gid]);

    //     $user = DB::transaction(function () use ($email, $gid, $name) {
    //         return User::updateOrCreate(
    //             ['email' => $email],
    //             [
    //                 'name' => $name,
    //                 'google_id' => $gid,
    //                 // kalau user baru, kasih password random (tidak dipakai login manual)
    //                 'password' => Str::password(40),
    //             ]
    //         );
    //     });

    //     // opsional: biar 1 token aktif per device/user
    //     $user->tokens()->delete();

    //     $token = $user->createToken('flutter-google')->plainTextToken;

    //     return response()->json([
    //         'message' => 'Login Google sukses',
    //         'token' => $token,
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //         ],
    //     ]);
    // }
}
