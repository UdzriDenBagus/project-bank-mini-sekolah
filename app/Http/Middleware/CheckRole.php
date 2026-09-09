<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Customer;

class CheckRole
{
    /**
     * Menangani proteksi akses berbasis peran (RBAC)
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // 1. Cek apakah user terautentikasi
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda belum terautentikasi (Unauthenticated).'
            ], 401);
        }

        // 2. Deteksi role user (Petugas vs Nasabah)
        $userRole = ($user instanceof Customer) ? 'customer' : $user->role;

        // 3. Cek apakah akun aktif atau diblokir
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan atau diblokir. Silakan hubungi Administrator.'
            ], 403);
        }

        // 4. Cek kesesuaian role dengan parameter middleware
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak (HTTP 403 Forbidden): Peran [' . strtoupper($userRole) . '] tidak memiliki wewenang mengakses fitur ini.'
            ], 403);
        }

        return $next($request);
    }
}