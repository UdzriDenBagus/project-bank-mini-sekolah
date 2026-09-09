<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\Customer;

class AuthController extends Controller
{
    /**
     * 1. Login Petugas Web (Admin, Teller, Supervisor)
     */
    public function loginStaff(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password salah!'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Akun dinonaktifkan. Hubungi Administrator.'
            ], 403);
        }

        $token = $user->createToken('auth_token_staff', [$user->role])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Petugas Berhasil!',
            'data'    => [
                'user'  => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'name'     => $user->name,
                    'role'     => $user->role,
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    /**
     * 2. Login Customer / Siswa (Untuk Flutter Mobile App)
     */
    public function loginCustomer(Request $request)
    {
        $request->merge([
            'identifier' => $request->identifier ?? $request->account_number,
            'pin'        => $request->pin ?? $request->security_pin,
        ]);

        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // account_number atau nis
            'pin'        => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('account_number', $request->identifier)
                            ->orWhere('nis', $request->identifier)
                            ->first();

        if (!$customer || !Hash::check($request->pin, $customer->security_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Rekening/NIS atau PIN 6-digit salah!'
            ], 401);
        }

        if ($customer->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening sedang diblokir. Silakan temui Teller loket.'
            ], 403);
        }

        $token = $customer->createToken('auth_token_customer', ['customer'])->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Nasabah Berhasil!',
            'data'    => [
                'customer' => [
                    'id'             => $customer->id,
                    'account_number' => $customer->account_number,
                    'nis'            => $customer->nis,
                    'name'           => $customer->name,
                    'class_name'     => $customer->class_name,
                    'balance'        => $customer->balance,
                    'role'           => 'customer'
                ],
                'token' => $token,
                'token_type' => 'Bearer'
            ]
        ], 200);
    }

    /**
     * 3. Ambil Profil Pengguna Aktif
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $role = ($user instanceof Customer) ? 'customer' : $user->role;

        return response()->json([
            'success' => true,
            'data'    => [
                'profile' => $user,
                'role'    => $role
            ]
        ]);
    }

    /**
     * 4. Logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token telah dihapus dari sistem.'
        ]);
    }
}