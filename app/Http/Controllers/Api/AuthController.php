<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        $user = User::where('email', $request->email)->where('aktif', true)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages(['email' => 'Email atau password salah.']);
        }

        return response()->json([
            'token' => $user->createToken($request->input('perangkat', 'api'))->plainTextToken,
            'pengguna' => ['id' => $user->id, 'nama' => $user->name, 'peran' => $user->peran?->nama],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['pesan' => 'Token dihapus.']);
    }
}
