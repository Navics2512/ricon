<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = validator($request->all(), [
            'udomain' => 'required|string',
            'password' => 'required|string'
        ], [
            'udomain.required' => 'Udomain wajib diisi.',
            'password.required' => 'Password wajib diisi.'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // cek udomain
        $user = \App\Models\User::where('udomain', $request->udomain)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Udomain tidak ditemukan.',
                'error_type' => 'udomain'
            ], 401);
        }

        // cek password
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Password salah.',
                'error_type' => 'password'
            ], 401);
        }

        // generate token
        $token = \Tymon\JWTAuth\Facades\JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }



    public function me()
    {
        return response()->json(Auth::user());
    }


    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Logged out']);
    }


    public function refresh()
    {
        return $this->respondWithToken(Auth::refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => Auth::factory()->getTTL() * 60,
        ]);
    }
}
