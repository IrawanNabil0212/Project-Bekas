<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    // =================================================================
    // 1. REGISTER
    // =================================================================
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Registrasi Berhasil!',
            'data'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer'
        ], 201);
    }

    // =================================================================
    // 2. LOGIN
    // =================================================================
    public function login(Request $request): JsonResponse
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'success' => false,
                'message' => 'Login Gagal! Email atau Password salah.'
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success'      => true,
            'message'      => 'Login Berhasil',
            'data'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer'
        ]);
    }

    // =================================================================
    // 3. LOGOUT
    // =================================================================
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Logout'
        ]);
    }

    // =================================================================
    // 4. ME (AMBIL DATA USER)
    // =================================================================
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    // =================================================================
    // 5. UPDATE PROFILE (FIX ERROR 500)
    // =================================================================
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        // Validasi dasar
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        // --- LOGIC PERBAIKAN ---
        // 1. Ambil input (bisa 'phone' atau 'no_hp' dari frontend)
        $phoneInput   = $request->input('phone') ?? $request->input('no_hp');
        $addressInput = $request->input('address') ?? $request->input('alamat_lengkap');

        // 2. Simpan ke Database (PASTIKAN NAMA KOLOM 'phone' & 'address')
        $user->update([
            'name'    => $request->name,
            'email'   => $request->email,
            
            // Simpan ke kolom 'phone'. Jika input kosong, pakai data lama.
            'phone'   => $phoneInput ?? $user->phone, 
            
            // Simpan ke kolom 'address'. Jika input kosong, pakai data lama.
            'address' => $addressInput ?? $user->address,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'data'    => $user
        ]);
    }
}