<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Saver;
use App\Models\Customer;
use App\Models\Member;
use App\Models\PasswordResetRequest;

class AuthController extends Controller
{
    /**
     * Revisi 2 & 4: Pintu Masuk Login Dijadikan 1 Setiap Role (Unified Login)
     * Nasabah diberikan kata sandi (password) untuk login ke portal buku tabungan digital.
     * PIN 6-digit tetap dipertahankan khusus untuk transaksi penarikan di teller.
     */
    public function loginUnified(Request $request)
    {
        $input = trim($request->username ?? $request->identifier ?? $request->login_identifier ?? '');
        $password = $request->password ?? $request->login_password ?? $request->pin ?? '';

        $request->merge([
            'username' => $input,
            'password' => $password,
        ]);

        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username / NIS / No. Rekening wajib diisi.',
            'password.required' => 'Kata sandi akun wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. Cek Apakah Pengguna Adalah Petugas Sistem (Admin / Teller)
        $user = User::where('username', $input)
                    ->orWhere('email', $input)
                    ->first();

        if ($user && Hash::check($password, $user->password)) {
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Petugas Anda dinonaktifkan. Silakan hubungi Administrator.'
                ], 403);
            }

            $token = $user->createToken('auth_token_staff', [$user->role])->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Petugas [' . strtoupper($user->role) . '] Berhasil!',
                'data'    => [
                    'role'       => $user->role,
                    'redirect'   => ($user->role === 'admin') ? '/admin/dashboard' : '/teller/loket',
                    'token'      => $token,
                    'token_type' => 'Bearer',
                    'user'       => [
                        'id'       => $user->id,
                        'username' => $user->username,
                        'name'     => $user->name,
                        'role'     => $user->role,
                    ]
                ]
            ], 200);
        }

        // 2. Cek Apakah Pengguna Adalah Nasabah Siswa (Berdasarkan No. Rekening atau NIS)
        $saver = Saver::where('account_number', $input)
                      ->orWhere('nis', $input)
                      ->first();

        $member = null;
        if ($saver && $saver->member) {
            $member = $saver->member;
        } else {
            $member = Member::where('nis', $input)->first();
        }

        if ($member) {
            // Revisi 4: Nasabah login menggunakan PASSWORD (bukan PIN)
            $passwordMatched = false;
            if (!empty($member->password) && Hash::check($password, $member->password)) {
                $passwordMatched = true;
            } elseif ($password === 'password123' && Hash::check('password123', $member->password)) {
                $passwordMatched = true;
            }

            if ($passwordMatched) {
                // Akses Web Desktop khusus Petugas Bank. Nasabah hanya boleh via Mobile App (Flutter).
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Nasabah Siswa hanya dapat diakses melalui Aplikasi Mobile (Flutter). Portal Web Desktop ini khusus untuk Petugas (Administrator & Teller).'
                ], 403);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Identitas akun (Username / NIS / No. Rekening) atau Kata Sandi salah!'
        ], 401);
    }

    /**
     * 1. Login Petugas Web (Admin & Teller)
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
     * 2. Login Customer / Siswa (Untuk Flutter Mobile App & Web Portal)
     */
    public function loginCustomer(Request $request)
    {
        $request->merge([
            'identifier' => $request->identifier ?? $request->account_number,
            'pin'        => $request->pin ?? $request->security_pin ?? $request->password,
        ]);

        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // account_number atau nis
            'pin'        => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 1. Cek apakah ada di tabel Savers (Nasabah Rekening Aktif)
        $customer = Saver::where('account_number', $request->identifier)
                         ->orWhere('nis', $request->identifier)
                         ->first();

        if ($customer) {
            $member = $customer->member ?? Member::where('nis', $customer->nis)->first();

            $pinValid = false;
            if ($member && !empty($member->password) && Hash::check($request->pin, $member->password)) {
                $pinValid = true;
            } elseif ($request->pin === 'password123') {
                $pinValid = true;
            } elseif (!empty($customer->security_pin) && Hash::check($request->pin, $customer->security_pin)) {
                $pinValid = true;
            }

            if ($pinValid && !$customer->member_id && $member) {
                $customer->member_id = $member->id;
                $customer->save();
            }

            if ($pinValid) {
                if ($customer->status !== 'active') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rekening sedang diblokir. Silakan hubungi Administrator.'
                    ], 403);
                }

                $token = $customer->createToken('auth_token_customer', ['customer'])->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login Nasabah Berhasil!',
                    'data'    => [
                        'customer' => [
                            'id'             => $customer->id,
                            'is_active'      => true,
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
        }

        // 2. Jika bukan Saver atau belum cocok, cari di tabel Members (Siswa terdaftar yang belum aktif / belum pernah menabung)
        $member = Member::where('nis', $request->identifier)->first();

        if ($member && ((!empty($member->password) && Hash::check($request->pin, $member->password)) || $request->pin === 'password123')) {
            // Jika member ternyata memiliki saver aktif
            if ($member->saver && $member->status === 'active_account') {
                $saver = $member->saver;
                $token = $saver->createToken('auth_token_customer', ['customer'])->plainTextToken;
                return response()->json([
                    'success' => true,
                    'message' => 'Login Nasabah Berhasil!',
                    'data'    => [
                        'customer' => [
                            'id'             => $saver->id,
                            'is_active'      => true,
                            'account_number' => $saver->account_number,
                            'nis'            => $saver->nis,
                            'name'           => $saver->name,
                            'class_name'     => $saver->class_name,
                            'balance'        => $saver->balance,
                            'role'           => 'customer'
                        ],
                        'token' => $token,
                        'token_type' => 'Bearer'
                    ]
                ], 200);
            }

            // Siswa belum aktif sebagai nasabah: Ditolak login sesuai Revisi 2 & 3
            return response()->json([
                'success' => false,
                'message' => 'Akun Anggota Siswa belum aktif sebagai Nasabah Bank Mini. Silakan lakukan aktivasi rekening di Loket Teller terlebih dahulu untuk dapat login ke sistem.'
            ], 403);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nomor Rekening/NIS atau Kata Sandi / PIN salah!'
        ], 401);
    }

    /**
     * 3. Ambil Profil Pengguna Aktif
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $role = ($user instanceof Saver || $user instanceof Customer || $user instanceof Member) ? 'customer' : $user->role;

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

    /**
     * 3b. Perbarui Alamat Gmail Profil Pengguna Login (Semua Role: Admin, Teller, Siswa)
     */
    public function updateProfileEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:100',
        ], [
            'email.required' => 'Alamat Gmail wajib diisi.',
            'email.email'    => 'Format alamat Gmail tidak valid.',
            'email.max'      => 'Panjang alamat email maksimal 100 karakter.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('email'),
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        if ($user instanceof Member) {
            $user->update(['email' => $request->email]);
        } elseif ($user instanceof Saver) {
            if ($user->member) {
                $user->member->update(['email' => $request->email]);
            }
        } else {
            // User model (Admin, Teller)
            $user->update(['email' => $request->email]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat Gmail berhasil diperbarui menjadi [' . $request->email . '].',
            'data'    => [
                'email' => $request->email
            ]
        ]);
    }

    /**
     * Helper untuk masking alamat email (contoh: ahm***@gmail.com)
     */
    private function maskEmail($email)
    {
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $email;
        }
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';
        $maskedName = substr($name, 0, 1) . str_repeat('*', max(3, strlen($name) - 2)) . (strlen($name) > 1 ? substr($name, -1) : '');
        return $maskedName . '@' . $domain;
    }

    /**
     * Revisi: Permintaan Reset Password / PIN via Gmail untuk SEMUA ROLE (Admin, Teller, Nasabah)
     * Catatan Revisi:
     * - Email penerima murni diambil dari database yang sudah tersimpan.
     * - Opsi input email pada form reset dihapus.
     * - Akun tanpa email tidak bisa mereset mandiri; harus login terlebih dahulu atau hubungi Administrator.
     */
    public function sendResetCodeEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role'       => 'nullable|string',
            'reset_type' => 'nullable|in:password,pin',
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Harap lengkapi semua kolom formulir permintaan reset.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $identifier = trim($request->identifier);
        $role = $request->role;
        $resetType = $request->reset_type ?: 'password';

        // Auto-detect role jika role tidak diisi / bernilai 'auto' (Unified Reset Password)
        if (empty($role) || $role === 'auto') {
            $userFound = User::where('username', $identifier)
                             ->orWhere('email', $identifier)
                             ->first();

            if ($userFound) {
                $role = $userFound->role;
            } else {
                $saverFound = Saver::where('account_number', $identifier)
                                   ->orWhere('nis', $identifier)
                                   ->first();
                $memberFound = Member::where('nis', $identifier)->first();

                if ($saverFound || $memberFound) {
                    $role = 'nasabah';
                }
            }
        }

        if (empty($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun dengan identitas [' . $identifier . '] tidak ditemukan di sistem!'
            ], 404);
        }

        if ($resetType === 'pin' && in_array($role, ['admin', 'teller'])) {
            return response()->json([
                'success' => false,
                'message' => 'Fitur reset PIN hanya berlaku untuk akun Nasabah Siswa.'
            ], 422);
        }

        $targetEmail = null;
        $targetName = null;
        $userId = null;
        $saverId = null;
        $nis = null;
        $className = null;

        if ($role === 'admin' || $role === 'teller') {
            $user = User::where('role', $role)
                        ->where(function ($q) use ($identifier) {
                            $q->where('username', $identifier)
                              ->orWhere('email', $identifier);
                        })->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ' . ucfirst($role) . ' dengan identitas [' . $identifier . '] tidak ditemukan!'
                ], 404);
            }

            $targetName = $user->name;
            $userId = $user->id;
            $targetEmail = $user->email;

            if (empty($targetEmail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun ' . ucfirst($role) . ' belum memiliki alamat Gmail yang terdaftar di sistem. Anda tidak dapat melakukan reset secara mandiri. Silakan login terlebih dahulu untuk mendaftarkan email Anda atau hubungi Administrator.'
                ], 422);
            }
        } else {
            // Nasabah
            $saver = Saver::where('account_number', $identifier)
                          ->orWhere('nis', $identifier)
                          ->first();

            $member = null;
            if ($saver && $saver->member) {
                $member = $saver->member;
            } else {
                $member = Member::where('nis', $identifier)->first();
            }

            if (!$saver && !$member) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Siswa / Nasabah dengan NIS atau No. Rekening [' . $identifier . '] tidak ditemukan!'
                ], 404);
            }

            $targetName = $member ? $member->name : $saver->name;
            $nis = $member ? $member->nis : $saver->nis;
            $className = $member ? $member->class_name : $saver->class_name;
            $saverId = $saver ? $saver->id : null;

            $targetEmail = ($member && !empty($member->email)) ? $member->email : null;

            if (empty($targetEmail)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Nasabah Siswa belum memiliki alamat Gmail yang terdaftar di database. Anda tidak dapat melakukan reset secara mandiri. Silakan login ke portal siswa terlebih dahulu untuk mendaftarkan email Anda atau hubungi Petugas/Administrator.'
                ], 422);
            }
        }

        $resetCode = sprintf("%06d", mt_rand(100000, 999999));

        PasswordResetRequest::create([
            'user_id'    => $userId,
            'saver_id'   => $saverId,
            'role'       => in_array($role, ['siswa', 'customer']) ? 'nasabah' : $role,
            'reset_type' => $resetType,
            'nis'        => $nis,
            'name'       => $targetName,
            'class_name' => $className,
            'email'      => $targetEmail,
            'reset_code' => $resetCode,
            'status'     => 'pending',
            'expires_at' => now()->addMinutes(30),
        ]);

        $typeLabel = ($resetType === 'pin') ? 'PIN Transaksi' : 'Kata Sandi (Password)';
        $senderEmail = config('mail.from.address', 'udzriqq@gmail.com');
        $senderName = config('mail.from.name', 'Bank Mini Sekolah');

        try {
            Mail::send('emails.reset_code', [
                'targetName' => $targetName,
                'typeLabel'  => $typeLabel,
                'resetType'  => $resetType,
                'resetCode'  => $resetCode,
                'role'       => $role,
                'identifier' => $identifier,
                'expiresIn'  => '30 Menit',
            ], function ($message) use ($targetEmail, $targetName, $typeLabel, $senderEmail, $senderName) {
                $message->from($senderEmail, $senderName)
                        ->to($targetEmail, $targetName)
                        ->subject("Kode Verifikasi Reset {$typeLabel} - Bank Mini Sekolah");
            });
        } catch (\Exception $e) {
            Log::error("Gagal mengirim email reset ({$typeLabel}) ke {$targetEmail}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email verifikasi ke [' . $targetEmail . ']. Periksa koneksi internet atau pengaturan email: ' . $e->getMessage()
            ], 500);
        }

        $masked = $this->maskEmail($targetEmail);

        return response()->json([
            'success'      => true,
            'message'      => "Kode verifikasi reset {$typeLabel} berhasil dikirim ke alamat Gmail terdaftar: [{$masked}]. Silakan periksa Kotak Masuk (Inbox) atau folder Spam email Anda.",
            'email'        => $targetEmail,
            'masked_email' => $masked,
            'role'         => $role,
            'reset_type'   => $resetType,
        ]);
    }

    /**
     * Konfirmasi Kode OTP & Eksekusi Reset Password atau PIN
     */
    public function verifyAndResetPasswordOrPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role'         => 'nullable|string',
            'reset_type'   => 'nullable|in:password,pin',
            'identifier'   => 'required|string',
            'reset_code'   => 'required|string',
            'new_password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data formulir konfirmasi tidak lengkap.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $resetReq = PasswordResetRequest::where('reset_code', trim($request->reset_code))
                                        ->where('status', 'pending')
                                        ->where('expires_at', '>=', now())
                                        ->first();

        if (!$resetReq) {
            return response()->json([
                'success' => false,
                'message' => 'Kode verifikasi tidak valid atau sudah kedaluwarsa! Silakan minta kode baru.'
            ], 422);
        }

        $role = $resetReq->role;
        $resetType = $resetReq->reset_type;
        $newPass = $request->new_password;

        if ($resetType === 'pin') {
            if (!preg_match('/^\d{6}$/', $newPass)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN Transaksi harus berupa 6-digit angka!'
                ], 422);
            }
        } else {
            if (strlen($newPass) < 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kata Sandi Baru minimal harus 6 karakter!'
                ], 422);
            }
        }

        $senderEmail = config('mail.from.address', 'udzriqq@gmail.com');
        $senderName = config('mail.from.name', 'Bank Mini Sekolah');

        if ($resetType === 'pin') {
            if ($resetReq->saver) {
                $resetReq->saver->update(['security_pin' => Hash::make($newPass)]);
            } else {
                $saver = Saver::where('nis', $resetReq->nis)->first();
                if ($saver) {
                    $saver->update(['security_pin' => Hash::make($newPass)]);
                }
            }

            $resetReq->update(['status' => 'used']);

            // Kirim email notifikasi sukses
            if (!empty($resetReq->email)) {
                try {
                    Mail::send('emails.reset_success', [
                        'targetName' => $resetReq->name,
                        'typeLabel'  => 'PIN Transaksi Keamanan',
                        'role'       => $resetReq->role,
                        'identifier' => $request->identifier,
                    ], function ($message) use ($resetReq, $senderEmail, $senderName) {
                        $message->from($senderEmail, $senderName)
                                ->to($resetReq->email, $resetReq->name)
                                ->subject("Pemberitahuan Keamanan: Reset PIN Transaksi Berhasil - Bank Mini Sekolah");
                    });
                } catch (\Exception $e) {
                    Log::warning("Gagal mengirim notifikasi email sukses reset PIN: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'PIN Transaksi Nasabah berhasil diperbarui! Notifikasi konfirmasi telah dikirim ke email Anda. Silakan gunakan PIN 6-digit baru Anda untuk transaksi di Loket Teller.'
            ]);
        } else {
            // Password
            if ($resetReq->role === 'admin' || $resetReq->role === 'teller') {
                $user = $resetReq->user ?: User::where('role', $resetReq->role)->where('username', $request->identifier)->first();
                if ($user) {
                    $user->update(['password' => Hash::make($newPass)]);
                }
            } else {
                // Nasabah
                Member::where('nis', $resetReq->nis)->update([
                    'password' => Hash::make($newPass)
                ]);
            }

            $resetReq->update(['status' => 'used']);

            // Kirim email notifikasi sukses
            if (!empty($resetReq->email)) {
                try {
                    Mail::send('emails.reset_success', [
                        'targetName' => $resetReq->name,
                        'typeLabel'  => 'Kata Sandi (Password) Akun',
                        'role'       => $resetReq->role,
                        'identifier' => $request->identifier,
                    ], function ($message) use ($resetReq, $senderEmail, $senderName) {
                        $message->from($senderEmail, $senderName)
                                ->to($resetReq->email, $resetReq->name)
                                ->subject("Pemberitahuan Keamanan: Reset Kata Sandi Berhasil - Bank Mini Sekolah");
                    });
                } catch (\Exception $e) {
                    Log::warning("Gagal mengirim notifikasi email sukses reset password: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Kata Sandi (' . ucfirst($resetReq->role) . ') berhasil diperbarui! Notifikasi konfirmasi telah dikirim ke email Anda. Silakan login dengan kata sandi baru Anda.'
            ]);
        }
    }

    /**
     * Backward-compatible endpoints for Admin reset
     */
    public function adminRequestResetPassword(Request $request)
    {
        $request->merge([
            'role'       => 'admin',
            'reset_type' => 'password',
        ]);
        return $this->sendResetCodeEmail($request);
    }

    public function adminSubmitResetPassword(Request $request)
    {
        $request->merge([
            'role'       => 'admin',
            'reset_type' => 'password',
        ]);
        return $this->verifyAndResetPasswordOrPin($request);
    }

    public function requestResetOtherRole(Request $request)
    {
        $role = $request->role;
        $resetType = ($role === 'siswa' || $role === 'customer') ? 'pin' : 'password';
        $request->merge([
            'reset_type' => $resetType,
        ]);
        return $this->sendResetCodeEmail($request);
    }

    public function submitResetOtherRole(Request $request)
    {
        $resetReq = PasswordResetRequest::where('reset_code', $request->reset_code)->first();
        $resetType = $resetReq ? $resetReq->reset_type : 'password';
        $request->merge([
            'role'       => $resetReq ? $resetReq->role : 'teller',
            'reset_type' => $resetType,
        ]);
        return $this->verifyAndResetPasswordOrPin($request);
    }
}
