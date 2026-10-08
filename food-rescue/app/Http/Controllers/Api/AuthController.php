<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private const ABILITIES = [
        'donatur'  => ['donasi:create', 'donasi:update'],
        'penerima' => ['klaim:create', 'ulasan:create', 'laporan:create'],
        'relawan'  => ['pengantaran:update', 'bukti:upload'],
        'admin'    => ['*'],
    ];

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:100'],
            'email'      => ['required', 'email', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
            'role'       => ['required', 'in:donatur,penerima,relawan'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'alamat'     => ['nullable', 'string'],
        ]);

        $user = User::create([
            'name'       => $data['name'],
            'email'      => $data['email'],
            'password'   => Hash::make($data['password']),
            'role'       => $data['role'],
            'no_telepon' => $data['no_telepon'],
            'alamat'     => $data['alamat'] ?? null,
        ]);

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Registrasi akun berhasil',
            'data'   => $user
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Kombinasi email dan kata sandi tidak sesuai.',
            ], 401);
        }

        $abilities = self::ABILITIES[$user->role] ?? ['*'];
        $token = $user->createToken('token-akses', $abilities)->plainTextToken;

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Login berhasil',
            'data'   => [
                'user'  => $user,
                'token' => $token,
            ]
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'sukses' => true,
            'pesan'  => 'Sesi berhasil diakhiri.'
        ]);
    }
}