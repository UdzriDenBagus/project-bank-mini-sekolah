<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\AccountingJournal;
use App\Models\PasswordResetRequest;
use App\Models\StudentArchive;
use App\Models\TellerCashAdvance;
use ZipArchive;

class AdminController extends Controller
{
    // ==========================================
    // 1. MANAJEMEN PENGGUNA SISTEM (USERS CRUD)
    // ==========================================

    public function indexUsers()
    {
        $users = User::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $users]);
    }

    public function storeUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|unique:users,username|max:50',
            'password' => 'required|string|min:6',
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|email|max:100',
            'role'     => 'required|in:admin,teller',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'status'   => 'active',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Akun Petugas [' . strtoupper($user->role) . '] berhasil dibuat!',
            'data'    => $user
        ], 201);
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Petugas tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:50|unique:users,username,' . $id,
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|email|max:100',
            'role'     => 'required|in:admin,teller',
            'status'   => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $dataUpdate = [
            'username' => $request->username,
            'name'     => $request->name,
            'email'    => $request->email,
            'role'     => $request->role,
            'status'   => $request->status,
        ];

        if ($request->status === 'inactive' && auth()->id() == $id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak dapat memblokir akun Anda sendiri yang sedang aktif digunakan!'
            ], 422);
        }

        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        $user->update($dataUpdate);

        $statusMsg = $request->status === 'inactive' ? ' (Akun Telah Diblokir)' : '';
        return response()->json([
            'success' => true,
            'message' => 'Data Petugas berhasil diperbarui!' . $statusMsg,
            'data'    => $user
        ]);
    }

    public function destroyUser($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Kebijakan Sistem: Akun petugas tidak dapat dihapus. Silakan gunakan fitur Blokir akun.'
        ], 403);
    }

    // ==========================================
    // 2. MANAJEMEN ANGGOTA SISWA (MEMBERS CRUD)
    // ==========================================

    public function indexMembers(Request $request)
    {
        $this->processStudentLifecycle();

        $query = Member::query()->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nis', 'LIKE', "%{$search}%")
                  ->orWhere('name', 'LIKE', "%{$search}%")
                  ->orWhere('class_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('per_page')) {
            $members = $query->paginate((int)$request->per_page);
        } else if ($request->filled('page')) {
            $members = $query->paginate(20);
        } else {
            $members = $query->get();
        }

        return response()->json(['success' => true, 'data' => $members]);
    }

    public function storeMember(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nis'             => 'required|string|unique:members,nis|max:30',
            'name'            => 'required|string|max:100',
            'class_name'      => 'required|string|max:50',
            'email'           => 'nullable|email|max:100',
            'graduation_year' => 'required|digits:4|integer',
            'password'        => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $member = Member::create([
            'nis'             => $request->nis,
            'name'            => $request->name,
            'class_name'      => $request->class_name,
            'email'           => $request->email,
            'graduation_year' => $request->graduation_year,
            'password'        => $request->filled('password') ? Hash::make($request->password) : null,
            'status'          => 'inactive_account',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Anggota Siswa berhasil ditambahkan! Kata sandi akun login dan PIN transaksi akan diatur saat aktivasi rekening nasabah.',
            'data'    => $member
        ], 201);
    }

    /**
     * Unduh Template Excel Tambah Anggota
     * Header Tabel: NIS, NAMA SISWA, KELAS, EMAIL (OPSIONAL)
     */
    public function downloadMemberTemplate(Request $request = null)
    {
        $staticPath = public_path('templates/template_tambah_anggota.xlsx');
        if (file_exists($staticPath)) {
            return response()->download($staticPath, 'template_tambah_anggota.xlsx', [
                'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'max-age=0, must-revalidate',
            ]);
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Anggota');

        // Header Table
        $sheet->setCellValue('A1', 'NIS');
        $sheet->setCellValue('B1', 'NAMA SISWA');
        $sheet->setCellValue('C1', 'KELAS');
        $sheet->setCellValue('D1', 'EMAIL');

        // Styling Header
        $headerStyle = [
            'font' => [
                'bold'  => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size'  => 11,
            ],
            'fill' => [
                'fillType'   => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E293B'], // Slate-800
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical'   => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:D1')->applyFromArray($headerStyle);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Contoh baris pengisian data
        $sheet->setCellValueExplicit('A2', '1001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Ahmad Fauzi');
        $sheet->setCellValue('C2', '10 RPL 1');
        $sheet->setCellValue('D2', 'ahmad.fauzi@gmail.com');

        $sheet->setCellValueExplicit('A3', '1002', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', 'Siti Rahma');
        $sheet->setCellValue('C3', '10 AKL 2');
        $sheet->setCellValue('D3', 'siti.rahma@gmail.com');

        $sheet->setCellValueExplicit('A4', '1003', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B4', 'Budi Santoso');
        $sheet->setCellValue('C4', '11 TKJ 1');
        $sheet->setCellValue('D4', '');

        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(30);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'template_tambah_anggota.xlsx', [
            'Content-Type'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Import Data Anggota dari File Excel (.xlsx, .xls, .csv)
     */
    public function importMembersExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Berkas Excel wajib diunggah!', 'errors' => $validator->errors()], 422);
        }

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, ['xlsx', 'xls', 'csv', 'txt'])) {
            return response()->json(['success' => false, 'message' => 'Format berkas tidak didukung. Harap upload berkas dengan format .xlsx, .xls, atau .csv!'], 422);
        }

        $rows = [];
        try {
            if (in_array($ext, ['csv', 'txt'])) {
                if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
                    while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                        if (count($data) === 1 && strpos($data[0], ';') !== false) {
                            $data = str_getcsv($data[0], ';');
                        }
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
            } else {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray(null, true, true, false);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal membaca berkas Excel: ' . $e->getMessage()], 422);
        }

        if (empty($rows) || count($rows) < 2) {
            return response()->json(['success' => false, 'message' => 'Berkas Excel kosong atau hanya berisi baris header!'], 422);
        }

        // Cari index kolom dari header baris pertama
        $headerRow = $rows[0];
        $nisCol = -1;
        $nameCol = -1;
        $classCol = -1;
        $emailCol = -1;

        foreach ($headerRow as $idx => $colName) {
            $cleaned = strtoupper(trim((string)$colName));
            if ($cleaned === 'NIS' || str_contains($cleaned, 'NIS') || str_contains($cleaned, 'NOMOR INDUK')) {
                $nisCol = $idx;
            } elseif ($cleaned === 'NAMA SISWA' || str_contains($cleaned, 'NAMA')) {
                $nameCol = $idx;
            } elseif ($cleaned === 'KELAS' || str_contains($cleaned, 'KELAS') || str_contains($cleaned, 'CLASS')) {
                $classCol = $idx;
            } elseif ($cleaned === 'EMAIL' || str_contains($cleaned, 'EMAIL') || str_contains($cleaned, 'SUREL') || str_contains($cleaned, 'GMAIL')) {
                $emailCol = $idx;
            }
        }

        if ($nisCol === -1) $nisCol = 0;
        if ($nameCol === -1) $nameCol = 1;
        if ($classCol === -1) $classCol = 2;
        if ($emailCol === -1 && isset($headerRow[3])) $emailCol = 3;

        $imported = 0;
        $skipped = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                $nis = isset($row[$nisCol]) ? trim((string)$row[$nisCol]) : '';
                $name = isset($row[$nameCol]) ? trim((string)$row[$nameCol]) : '';
                $class = isset($row[$classCol]) ? trim((string)$row[$classCol]) : '';
                $email = ($emailCol !== -1 && isset($row[$emailCol])) ? trim((string)$row[$emailCol]) : null;

                if (empty($nis) && empty($name)) {
                    continue;
                }

                if (empty($nis)) {
                    $skipped++;
                    $errors[] = "Baris " . ($i + 1) . ": NIS tidak boleh kosong.";
                    continue;
                }

                if (empty($name)) {
                    $skipped++;
                    $errors[] = "Baris " . ($i + 1) . " (NIS: {$nis}): Nama Siswa tidak boleh kosong.";
                    continue;
                }

                if (empty($class)) {
                    $class = '10 RPL 1';
                }

                $existing = Member::withTrashed()->where('nis', $nis)->first();
                if ($existing) {
                    $skipped++;
                    $errors[] = "Baris " . ($i + 1) . ": NIS '{$nis}' sudah terdaftar ({$existing->name}).";
                    continue;
                }

                $currentYear = (int)date('Y');
                $gradYear = $currentYear + 2;
                if (preg_match('/\b(11|XI)\b/i', $class)) {
                    $gradYear = $currentYear + 1;
                } elseif (preg_match('/\b(12|XII)\b/i', $class)) {
                    $gradYear = $currentYear;
                }

                Member::create([
                    'nis'             => $nis,
                    'name'            => $name,
                    'class_name'      => $class,
                    'email'           => !empty($email) ? $email : null,
                    'graduation_year' => $gradYear,
                    'password'        => Hash::make('password123'),
                    'status'          => 'inactive_account',
                ]);

                $imported++;
            }

            DB::commit();

            $msg = "Berhasil mengimpor {$imported} data anggota siswa.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} baris dilewati).";
            }

            return response()->json([
                'success'  => true,
                'message'  => $msg,
                'imported' => $imported,
                'skipped'  => $skipped,
                'errors'   => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateMember(Request $request, $id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Data anggota tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nis'             => 'required|string|max:30|unique:members,nis,' . $id,
            'name'            => 'required|string|max:100',
            'class_name'      => 'required|string|max:50',
            'email'           => 'nullable|email|max:100',
            'graduation_year' => 'required|digits:4|integer',
            'password'        => 'nullable|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $dataUpdate = $request->only(['nis', 'name', 'class_name', 'graduation_year', 'email']);
        if ($request->filled('password')) {
            $dataUpdate['password'] = Hash::make($request->password);
        }

        $member->update($dataUpdate);

        // Sinkronkan ke data Penabung jika anggota sudah memiliki rekening
        if ($member->saver) {
            $member->saver->update([
                'nis'        => $member->nis,
                'name'       => $member->name,
                'class_name' => $member->class_name,
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Data Anggota berhasil diperbarui!', 'data' => $member]);
    }

    /**
     * Generator Nomor Rekening Otomatis
     * Format: [2 Digit Tahun][2 Digit Kode Sekolah '01'][5 Digit Nomor Urut]
     */
    private function generateAccountNumber()
    {
        $prefix = date('y') . '01'; // Contoh: '2601'
        $latest = Saver::withTrashed()
                       ->where('account_number', 'LIKE', $prefix . '%')
                       ->orderBy('account_number', 'desc')
                       ->first();

        if (!$latest) {
            $nextSeq = 1;
        } else {
            $lastSeq = (int) substr($latest->account_number, strlen($prefix));
            $nextSeq = $lastSeq + 1;
        }

        return $prefix . str_pad($nextSeq, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Aktivasi Rekening Anggota Siswa oleh Admin
     */
    public function activateMember(Request $request, $id)
    {
        $member = Member::find($id);
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Data anggota siswa tidak ditemukan.'], 404);
        }

        if ($member->status === 'active_account') {
            return response()->json(['success' => false, 'message' => 'Anggota ini sudah berstatus rekening aktif!'], 422);
        }

        $validator = Validator::make($request->all(), [
            'initial_deposit' => 'required|integer|min:10000',
            'password'        => 'required|string|min:6',
            'security_pin'    => 'required|digits:6',
        ], [
            'initial_deposit.required' => 'Nasabah harus setoran minimal Rp 10.000 untuk dapat diaktivasi sebagai penabung.',
            'initial_deposit.min'      => 'Jika nasabah ingin mengaktifkan menjadi penabung, nasabah tersebut harus setoran minimal Rp 10.000.',
            'password.required'        => 'Kata sandi login akun siswa wajib diisi saat aktivasi rekening!',
            'password.min'             => 'Kata sandi login akun siswa minimal 6 karakter!',
            'security_pin.required'    => 'PIN transaksi 6-digit wajib diisi saat aktivasi rekening!',
            'security_pin.digits'      => 'PIN transaksi harus tepat 6 digit angka!',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors()
            ], 422);
        }

        $amount = (int) $request->initial_deposit;
        if ($amount < 10000) {
            return response()->json([
                'success' => false,
                'message' => 'Jika nasabah ingin mengaktifkan menjadi penabung, nasabah tersebut harus setoran minimal Rp 10.000. Jika tidak setoran minimal 10k, tidak bisa diaktivasi sebagai penabung.'
            ], 422);
        }

        $pin = $request->security_pin;
        $password = $request->password;

        DB::beginTransaction();
        try {
            $accountNumber = $this->generateAccountNumber();

            // Hash PIN transaksi dan Password login akun
            $pinHash = Hash::make($pin);
            $passwordHash = Hash::make($password);

            // Cek apakah sudah pernah ada Saver yang terhapus (soft delete) untuk member ini
            $existingSaver = Saver::withTrashed()->where('member_id', $member->id)->first();
            if ($existingSaver) {
                $existingSaver->restore();
                $existingSaver->update([
                    'account_number' => $accountNumber,
                    'nis'            => $member->nis,
                    'name'           => $member->name,
                    'class_name'     => $member->class_name,
                    'security_pin'   => $pinHash,
                    'balance'        => $amount,
                    'status'         => 'active',
                ]);
                $saver = $existingSaver;
            } else {
                $saver = Saver::create([
                    'member_id'      => $member->id,
                    'account_number' => $accountNumber,
                    'nis'            => $member->nis,
                    'name'           => $member->name,
                    'class_name'     => $member->class_name,
                    'security_pin'   => $pinHash,
                    'balance'        => $amount,
                    'status'         => 'active',
                ]);
            }

            // Update status anggota menjadi active_account serta simpan kata sandi login
            $member->update([
                'status'   => 'active_account',
                'password' => $passwordHash,
            ]);

            // Jika ada setoran awal > 0, catat transaksi & jurnal
            if ($amount > 0) {
                $transCode = 'TRX-ACT-' . date('YmdHis') . '-' . rand(100, 999);
                $transaction = Transaction::create([
                    'transaction_code'        => $transCode,
                    'saver_id'                => $saver->id,
                    'saver_name_snapshot'     => $saver->name,
                    'account_number_snapshot' => $saver->account_number,
                    'user_id'                 => $request->user()->id,
                    'transaction_date'        => now(),
                    'transaction_type'        => 'deposit',
                    'amount'                  => $amount,
                    'balance_before'          => 0,
                    'balance_after'           => $amount,
                    'description'             => 'Setoran Awal Aktivasi Rekening oleh Admin',
                ]);

                AccountingJournal::create([
                    'transaction_id' => $transaction->id,
                    'account_code'   => '101',
                    'account_name'   => 'Kas Loket Teller',
                    'position'       => 'debit',
                    'amount'         => $amount,
                ]);

                AccountingJournal::create([
                    'transaction_id' => $transaction->id,
                    'account_code'   => '201',
                    'account_name'   => 'Simpanan Tabungan Siswa',
                    'position'       => 'credit',
                    'amount'         => $amount,
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Rekening Siswa [' . $member->name . '] Berhasil Diaktifkan! No. Rek: ' . $accountNumber,
                'data'    => [
                    'member'         => $member,
                    'saver'          => $saver,
                    'account_number' => $accountNumber,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal aktivasi rekening: ' . $e->getMessage()], 500);
        }
    }

    public function destroyMember($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Kebijakan Sistem: Data anggota yang sudah di-input tidak dapat dihapus.'
        ], 403);
    }

    /**
     * Fitur Tinggal Kelas untuk Siswa (Nasabah maupun Anggota yang belum aktif)
     */
    public function holdBackMember(Request $request, $id)
    {
        // Cari member (termasuk jika sebelumnya sempat ter-soft delete)
        $member = Member::withTrashed()->find($id);
        if (!$member) {
            // Cek jika ID yang dikirimkan adalah ID saver
            $saverCheck = Saver::withTrashed()->find($id);
            if ($saverCheck && $saverCheck->member_id) {
                $member = Member::withTrashed()->find($saverCheck->member_id);
            }
        }

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Data siswa / anggota tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'class_name'      => 'required|string|max:50',
            'graduation_year' => 'nullable|integer|min:2000|max:2100',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Jika member sebelumnya ter-soft delete, pulihkan dulu
        if ($member->trashed()) {
            $member->restore();
        }

        $cleanClass = Member::stripGraduatedPrefix($request->class_name);
        if (empty($cleanClass)) {
            $cleanClass = Member::stripGraduatedPrefix($member->class_name) ?: '10 RPL 1';
        }

        // Tahun kelulusan bertambah 1 tahun jika tinggal kelas
        $currentYear = (int) date('Y');
        $newGradYear = $request->filled('graduation_year')
            ? (int) $request->graduation_year
            : max($currentYear + 1, (int) $member->graduation_year + 1);

        $member->class_name           = $cleanClass;
        $member->graduation_year      = $newGradYear;
        $member->promoted_years_count = max(0, (int) $member->promoted_years_count - 1);
        $member->last_promoted_at     = now(); // Kunci agar tidak terkena auto-promote di tahun yang sama
        $member->created_at           = now()->subYear(); // Aman dari batas 3 tahun
        $member->is_archived          = false;
        $member->archived_at          = null;

        // Cek saver (apakah sudah aktif sebagai nasabah penabung)
        $saver = Saver::withTrashed()->where('member_id', $member->id)->first();
        if ($saver) {
            if ($saver->trashed()) {
                $saver->restore();
            }
            $saver->update([
                'name'       => $member->name,
                'class_name' => $cleanClass,
                'status'     => 'active',
                'deleted_at' => null,
            ]);
            $member->status = 'active_account';
        } else {
            $member->status = 'inactive_account';
        }

        $member->save();

        $accountStatusText = $saver ? 'Nasabah Aktif' : 'Anggota Belum Buka Rekening';

        return response()->json([
            'success' => true,
            'message' => "Siswa [{$member->name}] berhasil ditetapkan Tinggal Kelas di [{$cleanClass}] (Tahun Lulus: {$newGradYear}) dengan status: {$accountStatusText}.",
            'data'    => [
                'member' => $member,
                'saver'  => $saver,
            ]
        ]);
    }

    // ==========================================
    // 3. MASTER DATA PENABUNG (SAVERS)
    // ==========================================

    public function indexSavers()
    {
        $this->processStudentLifecycle();
        $savers = Saver::whereNull('deleted_at')
                       ->whereHas('member', function($q) {
                           $q->whereNull('deleted_at');
                       })
                       ->with('member')
                       ->orderBy('id', 'desc')
                       ->get();
        return response()->json(['success' => true, 'data' => $savers]);
    }

    // Kompatibilitas rute lama
    public function indexCustomers()
    {
        return $this->indexSavers();
    }

    /**
     * Revisi 6: Ubah Status Rekening Penabung (Aktif / Non-aktif)
     * Menggantikan aksi hapus pada bagian rekening penabung.
     */
    public function toggleSaverStatus(Request $request, $id)
    {
        $saver = Saver::find($id);
        if (!$saver) {
            return response()->json(['success' => false, 'message' => 'Data penabung tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $newStatus = $request->status;
        $saver->update(['status' => $newStatus]);

        $label = ($newStatus === 'active') ? 'Aktif' : 'Non-aktif';

        return response()->json([
            'success' => true,
            'message' => 'Status rekening penabung [' . $saver->name . '] berhasil diubah menjadi ' . $label . '!',
            'data'    => $saver
        ]);
    }

    /**
     * Soft Delete Penabung (Aman untuk Audit Transaksi)
     */
    public function destroySaver($id)
    {
        $saver = Saver::find($id);
        if (!$saver) {
            return response()->json(['success' => false, 'message' => 'Penabung tidak ditemukan'], 404);
        }

        // Jalankan Soft Delete
        $saver->delete();

        // Update status anggota menjadi inactive kembali jika perlu
        if ($saver->member) {
            $saver->member->update(['status' => 'inactive_account']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Akun Penabung dinonaktifkan (Soft Delete). Seluruh riwayat transaksi audit tetap aman.'
        ]);
    }

    /**
     * Fitur Lupa PIN: Reset PIN Penabung oleh Admin
     */
    public function resetPinSaver(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'new_pin' => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'PIN baru harus 6 digit angka!', 'errors' => $validator->errors()], 422);
        }

        $saver = Saver::find($id);
        if (!$saver) {
            return response()->json(['success' => false, 'message' => 'Penabung tidak ditemukan'], 404);
        }

        $saver->update([
            'security_pin' => Hash::make($request->new_pin)
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN Keamanan untuk [' . $saver->name . '] berhasil direset ke ' . $request->new_pin . '!'
        ]);
    }

    // ==========================================
    // 4. FITUR LAPORAN BULANAN (MONTHLY REPORT)
    // ==========================================

    public function monthlyReport(Request $request)
    {
        $month = $request->query('month', date('n'));
        $year  = $request->query('year', date('Y'));

        $totalDeposit = Transaction::whereYear('transaction_date', $year)
                                   ->whereMonth('transaction_date', $month)
                                   ->where('transaction_type', 'deposit')
                                   ->sum('amount');

        $totalWithdraw = Transaction::whereYear('transaction_date', $year)
                                    ->whereMonth('transaction_date', $month)
                                    ->where('transaction_type', 'withdraw')
                                    ->sum('amount');

        $countTx = Transaction::whereYear('transaction_date', $year)
                              ->whereMonth('transaction_date', $month)
                              ->count();

        // Rincian Harian
        $daily = Transaction::select(
                DB::raw('DATE(transaction_date) as tx_date'),
                DB::raw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as total_deposit"),
                DB::raw("SUM(CASE WHEN transaction_type = 'withdraw' THEN amount ELSE 0 END) as total_withdraw"),
                DB::raw('COUNT(id) as total_count')
            )
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->groupBy(DB::raw('DATE(transaction_date)'))
            ->orderBy('tx_date', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'month'          => (int) $month,
                'year'           => (int) $year,
                'period_name'    => date('F Y', mktime(0, 0, 0, $month, 1, $year)),
                'total_deposit'  => (int) $totalDeposit,
                'total_withdraw' => (int) $totalWithdraw,
                'net_cashflow'   => (int) ($totalDeposit - $totalWithdraw),
                'total_tx'       => (int) $countTx,
                'daily_records'  => $daily,
            ]
        ]);
    }

    // ==========================================
    // 5. AUDIT JURNAL AKUNTANSI (BACK-END)
    // ==========================================

    public function auditJournals(Request $request)
    {
        $query = AccountingJournal::with(['transaction.customer', 'transaction.user'])
                                  ->orderBy('id', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $journals = $query->paginate(20);

        $totalDebit  = AccountingJournal::where('position', 'debit')->sum('amount');
        $totalCredit = AccountingJournal::where('position', 'credit')->sum('amount');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_debit'  => (int) $totalDebit,
                'total_credit' => (int) $totalCredit,
                'is_balanced'  => ($totalDebit === $totalCredit),
            ],
            'data'    => $journals
        ]);
    }

    // ==========================================
    // 6. SIKLUS HIDUP & SAMPAH DATA SISWA (TRASH)
    // ==========================================

    /**
     * Otomatisasi Lifecycle Siswa:
     * - Siswa terdaftar >= 3 tahun otomatis masuk ke Sampah (Soft Delete).
     * - Siswa di Sampah > 2 bulan otomatis dihapus permanen.
     * - KUNCI: Riwayat transaksi bank mini tidak pernah dihapus!
     */
    /**
     * Kenaikan Kelas Tahunan & Kelulusan Setiap Bulan Juli:
     * - Setiap bulan Juli (1 tahun sekali), semua siswa aktif akan naik kelas 1 tingkat.
     * - Siswa kelas 12 / XII akan dinyatakan LULUS pada bulan Juli.
     */
    public function processJulyClassPromotion($force = false)
    {
        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;

        // Hanya dieksekusi di bulan Juli (month >= 7) atau jika force == true
        if (!$force && $currentMonth < 7) {
            return 0;
        }

        $activeMembers = Member::whereNull('deleted_at')->get();
        $promotedCount = 0;

        foreach ($activeMembers as $m) {
            // Jika siswa sudah lulus tetapi masih ada di tabel aktif, langsung pindahkan ke Data Siswa Terhapus (Soft Delete)
            if (Member::isGraduated($m->class_name) || $m->status === 'graduated') {
                if ($m->saver && !$m->saver->trashed()) {
                    $m->saver->update(['status' => 'inactive']);
                    $m->saver->delete();
                }
                $m->update(['status' => 'graduated']);
                $m->delete();
                $promotedCount++;
                continue;
            }

            // Siswa yang baru didaftarkan di bulan Juli tahun berjalan tidak langsung naik kelas di bulan yang sama
            if (!$force && $m->created_at && $m->created_at->format('Y-m') >= now()->format('Y-07')) {
                continue;
            }

            // Cek apakah sudah pernah dinaikkan kelas pada tahun ini
            if (!$force && $m->last_promoted_at && $m->last_promoted_at->year >= $currentYear) {
                continue;
            }

            $oldClass = $m->class_name;
            $newClass = Member::promoteClassName($oldClass);

            $updateData = [
                'class_name'           => $newClass,
                'promoted_years_count' => (int) $m->promoted_years_count + 1,
                'last_promoted_at'     => now(),
            ];

            $isNowGraduated = Member::isGraduated($newClass);
            if ($isNowGraduated) {
                $updateData['graduation_year'] = $currentYear;
                $updateData['status'] = 'graduated';
            }

            $m->update($updateData);

            if ($m->saver) {
                $m->saver->update([
                    'class_name' => $newClass,
                    'status'     => $isNowGraduated ? 'inactive' : $m->saver->status
                ]);
            }

            // REVISI 4: Jika siswa dinyatakan LULUS (misal dari kelas 12 / XII),
            // data anggota dan rekening penabung otomatis dipindahkan ke "Data Siswa Terhapus" (Soft Delete)
            // agar tidak lagi muncul di Data Anggota maupun Rekening Penabung!
            if ($isNowGraduated) {
                if ($m->saver && !$m->saver->trashed()) {
                    $m->saver->delete();
                }
                $m->delete();
            }

            $promotedCount++;
        }

        return $promotedCount;
    }

    /**
     * Endpoint API Admin untuk menjalankan / simulasi Kenaikan Kelas Juli secara manual
     */
    public function triggerJulyPromotion(Request $request)
    {
        $force = $request->boolean('force', true);
        $count = $this->processJulyClassPromotion($force);

        return response()->json([
            'success' => true,
            'message' => "Proses Kenaikan Kelas & Kelulusan Bulan Juli selesai. {$count} data siswa berhasil diproses (naik kelas / lulus ke data terhapus).",
            'promoted_count' => $count,
        ]);
    }

    public function processStudentLifecycle()
    {
        // 1. Kenaikan Kelas Tahunan & Kelulusan Bulan Juli
        $this->processJulyClassPromotion();

        // 2. Siswa lulus yang masih berstatus aktif dipindahkan ke Sampah (Data Terhapus)
        $graduatedActive = Member::whereNull('deleted_at')
                                 ->get()
                                 ->filter(fn($m) => Member::isGraduated($m->class_name) || $m->status === 'graduated');

        foreach ($graduatedActive as $gm) {
            if ($gm->saver && !$gm->saver->trashed()) {
                $gm->saver->update(['status' => 'inactive']);
                $gm->saver->delete();
            }
            $gm->delete();
        }

        // 3. Siswa terdaftar lebih dari 3 tahun -> Masuk ke Sampah
        $expiredMembers = Member::where('created_at', '<=', now()->subYears(3))
                                ->whereNull('deleted_at')
                                ->get();

        foreach ($expiredMembers as $m) {
            if ($m->saver && !$m->saver->trashed()) {
                $m->saver->delete();
            }
            $m->delete();
        }

        // 4. Revisi 8: Siswa di Data Terhapus lebih dari 2 bulan -> Otomatis Dikonversi & Diarsipkan ke format ZIP/Folder
        // TIDAK BOLEH ADA DATA YANG TERHAPUS PERMANEN!
        $expiredTrashed = Member::onlyTrashed()
                                ->where('deleted_at', '<=', now()->subMonths(2))
                                ->where('is_archived', false)
                                ->get();

        if ($expiredTrashed->count() > 0) {
            $this->createArchiveZip($expiredTrashed, 'Otomatis (> 2 Bulan)');
        }
    }

    /**
     * Helper untuk membuat berkas ZIP dari data siswa terhapus
     */
    public function createArchiveZip($members, $batchTitle = 'Pengarsipan')
    {
        if ($members->isEmpty()) {
            return null;
        }

        $archiveDir = storage_path('app/student_archives');
        if (!file_exists($archiveDir)) {
            mkdir($archiveDir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $zipFilename = 'arsip_siswa_terhapus_' . $timestamp . '_' . rand(100, 999) . '.zip';
        $zipFullPath = $archiveDir . DIRECTORY_SEPARATOR . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        // 1. File Informasi Arsip
        $readme = "=========================================================\n";
        $readme .= "ARSIP RESMI DATA SISWA TERHAPUS - BANK MINI SEKOLAH\n";
        $readme .= "=========================================================\n";
        $readme .= "Tanggal Pengarsipan : " . date('d F Y H:i:s') . "\n";
        $readme .= "Kategori Arsip       : " . $batchTitle . "\n";
        $readme .= "Jumlah Data Siswa   : " . $members->count() . " siswa\n";
        $readme .= "---------------------------------------------------------\n";
        $readme .= "Peraturan: Seluruh data siswa yang telah terhapus lebih dari 2 bulan\n";
        $readme .= "diarsipkan secara aman ke dalam berkas ZIP ini. Tidak ada data\n";
        $readme .= "yang dihapus permanen agar jejak rekam perbankan sekolah tetap terjaga.\n";
        $readme .= "=========================================================\n";
        $zip->addFromString('INFO_ARSIP.txt', $readme);

        // 2. Rekapan Data Format CSV (Bisa dibuka di Microsoft Excel)
        $csvHeader = "NIS,Nama Siswa,Kelas,Tahun Lulus,Nomor Rekening,Saldo Terakhir,Tanggal Dihapus,Status Akun\n";
        $csvBody = '';
        $studentSummary = [];

        foreach ($members as $m) {
            $saver = $m->saver()->withTrashed()->first();
            $accNo = $saver ? $saver->account_number : '-';
            $balance = $saver ? (int)$saver->balance : 0;
            $deletedAt = $m->deleted_at ? $m->deleted_at->format('Y-m-d H:i:s') : '-';

            $csvBody .= '"' . $m->nis . '","' . str_replace('"', '""', $m->name) . '","' . $m->class_name . '","' . $m->graduation_year . '","' . $accNo . '","' . $balance . '","' . $deletedAt . '","' . $m->status . "\"\n";

            // 3. Rekam Kartu Digital Siswa & Riwayat Transaksi ke dalam folder siswa_detail/
            $transactions = [];
            if ($saver) {
                $transactions = $saver->transactions()->orderBy('id', 'desc')->get([
                    'transaction_code', 'transaction_date', 'transaction_type', 'amount', 'balance_after', 'description'
                ])->toArray();
            }

            $detail = [
                'nis'             => $m->nis,
                'name'            => $m->name,
                'class_name'      => $m->class_name,
                'graduation_year' => $m->graduation_year,
                'account_number'  => $accNo,
                'balance'         => $balance,
                'status'          => $m->status,
                'deleted_at'      => $deletedAt,
                'transactions'    => $transactions,
            ];

            $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $m->name);
            $zip->addFromString("siswa_detail/{$m->nis}_{$safeName}.json", json_encode($detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            $studentSummary[] = [
                'id'             => $m->id,
                'nis'            => $m->nis,
                'name'           => $m->name,
                'class_name'     => $m->class_name,
                'account_number' => $accNo,
                'balance'        => $balance,
                'deleted_at'     => $deletedAt,
            ];

            // Tandai sudah diarsipkan
            $m->update([
                'is_archived' => true,
                'archived_at' => now(),
            ]);
        }

        $zip->addFromString('daftar_siswa_terhapus.csv', $csvHeader . $csvBody);
        $zip->close();

        $fileSize = file_exists($zipFullPath) ? filesize($zipFullPath) : 0;

        $archive = StudentArchive::create([
            'filename'        => $zipFilename,
            'original_name'   => 'Arsip Siswa Terhapus - ' . date('d/m/Y H:i') . ' (' . $batchTitle . ')',
            'file_path'       => 'student_archives/' . $zipFilename,
            'file_size'       => $fileSize,
            'student_count'   => $members->count(),
            'student_summary' => json_encode($studentSummary),
        ]);

        return $archive;
    }

    /**
     * Daftar Berkas ZIP Arsip Siswa Terhapus
     */
    public function indexArchives()
    {
        $archives = StudentArchive::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $archives]);
    }

    /**
     * Manual Trigger Pembuatan Arsip ZIP Siswa Terhapus
     */
    public function generateArchive(Request $request)
    {
        $query = Member::onlyTrashed();

        if ($request->filled('only_old') && $request->only_old) {
            $query->where('deleted_at', '<=', now()->subMonths(2));
        }

        // Ambil yang belum diarsipkan; jika semua sudah, izinkan backup semua yang terhapus
        $members = (clone $query)->where('is_archived', false)->get();
        if ($members->isEmpty()) {
            $members = $query->get();
        }

        if ($members->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data siswa terhapus yang dapat diarsipkan.'
            ], 422);
        }

        $archive = $this->createArchiveZip($members, 'Manual Admin');

        return response()->json([
            'success' => true,
            'message' => 'Berhasil membuat berkas ZIP arsip untuk ' . $members->count() . ' siswa terhapus!',
            'data'    => $archive
        ]);
    }

    /**
     * Unduh Berkas ZIP Arsip
     */
    public function downloadArchive($id)
    {
        $archive = StudentArchive::find($id);
        if (!$archive) {
            abort(404, 'Data arsip tidak ditemukan.');
        }

        $filePath = storage_path('app/' . $archive->file_path);
        if (!file_exists($filePath)) {
            abort(404, 'Berkas ZIP fisik tidak ditemukan di penyimpanan server.');
        }

        return response()->download($filePath, $archive->filename);
    }

    /**
     * Buka / Lihat Isi File ZIP Arsip
     */
    public function inspectArchive($id)
    {
        $archive = StudentArchive::find($id);
        if (!$archive) {
            return response()->json(['success' => false, 'message' => 'Data arsip tidak ditemukan.'], 404);
        }

        $filePath = storage_path('app/' . $archive->file_path);
        if (!file_exists($filePath)) {
            return response()->json(['success' => false, 'message' => 'Berkas ZIP fisik tidak ditemukan di server.'], 404);
        }

        $zip = new ZipArchive();
        $filesList = [];

        if ($zip->open($filePath) === true) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                $filesList[] = [
                    'name' => $stat['name'],
                    'size' => $stat['size'],
                ];
            }
            $zip->close();
        }

        $students = json_decode($archive->student_summary, true) ?: [];

        return response()->json([
            'success' => true,
            'data'    => [
                'archive'  => $archive,
                'files'    => $filesList,
                'students' => $students,
            ]
        ]);
    }

    /**
     * Daftar Data Siswa Terhapus (Revisi 8: Istilah diubah dari Sampah Data Siswa)
     */
    public function indexTrash()
    {
        $this->processStudentLifecycle();

        $trashed = Member::onlyTrashed()
                         ->with(['saver' => function($q) {
                             $q->withTrashed();
                         }])
                         ->orderBy('deleted_at', 'desc')
                         ->get()
                         ->map(function($m) {
                             $archiveDate = $m->deleted_at ? $m->deleted_at->copy()->addMonths(2) : null;
                             $daysRemaining = $archiveDate ? max(0, (int) now()->diffInDays($archiveDate, false)) : 0;
                             $isOver2Months = $archiveDate ? now()->greaterThanOrEqualTo($archiveDate) : false;

                             return [
                                 'id'               => $m->id,
                                 'nis'              => $m->nis,
                                 'name'             => $m->name,
                                 'class_name'       => $m->class_name,
                                 'graduation_year'  => $m->graduation_year,
                                 'account_number'   => $m->saver ? $m->saver->account_number : '-',
                                 'balance'          => $m->saver ? (int)$m->saver->balance : 0,
                                 'deleted_at'       => $m->deleted_at ? $m->deleted_at->format('d/m/Y H:i') : '-',
                                 'archive_date'     => $archiveDate ? $archiveDate->format('d/m/Y') : '-',
                                 'days_remaining'   => $daysRemaining,
                                 'is_over_2_months' => $isOver2Months,
                                 'is_archived'      => (bool) $m->is_archived,
                                 'archived_at'      => $m->archived_at ? $m->archived_at->format('d/m/Y H:i') : null,
                             ];
                         });

        return response()->json(['success' => true, 'data' => $trashed]);
    }

    /**
     * Pulihkan Siswa dari Data Siswa Terhapus (misal: Siswa Tidak Naik Kelas)
     */
    public function restoreMember(Request $request, $id)
    {
        $member = Member::onlyTrashed()->find($id);
        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Data siswa terhapus tidak ditemukan'], 404);
        }

        $member->restore();

        // 1. Dapatkan & bersihkan nama kelas agar tidak lagi berstatus Lulus/Alumni
        $rawClass = $request->input('class_name', $member->class_name);
        $cleanClass = Member::stripGraduatedPrefix($rawClass);
        if (empty($cleanClass)) {
            $cleanClass = $member->class_name ? Member::stripGraduatedPrefix($member->class_name) : 'XII';
        }
        if (empty($cleanClass) || Member::isGraduated($cleanClass)) {
            $cleanClass = 'XII';
        }

        // 2. Tahun kelulusan (minimal tahun sekarang + 1 agar berstatus siswa aktif)
        $currentYear = (int) date('Y');
        $gradYear = (int) $request->input('graduation_year', $member->graduation_year);
        if ($gradYear <= $currentYear) {
            $gradYear = $currentYear + 1;
        }

        // 3. Reset data agar tidak langsung terpicu lifecycle kelulusan/penghapusan otomatis
        $member->class_name           = $cleanClass;
        $member->graduation_year      = $gradYear;
        $member->created_at           = now()->subYear(); // aman dari batas 3 tahun
        $member->promoted_years_count = 2;
        $member->last_promoted_at     = now(); // tandai sudah dievaluasi tahun ini
        $member->is_archived          = false;
        $member->archived_at          = null;

        // 4. Pulihkan Rekening Penabung (Saver) jika ada
        $saver = Saver::withTrashed()->where('member_id', $member->id)->first();
        if ($saver) {
            $saver->restore();
            $saver->update([
                'name'       => $member->name,
                'class_name' => $cleanClass,
                'status'     => 'active',
                'deleted_at' => null,
            ]);
            $member->status = 'active_account';
        } else {
            $member->status = 'inactive_account';
        }

        $member->save();

        return response()->json([
            'success' => true,
            'message' => 'Data Siswa [' . $member->name . '] dan rekening tabungannya berhasil dipulihkan!'
        ]);
    }

    /**
     * Hapus Permanen DILARANG (Revisi 8: Tidak boleh ada data yang terhapus permanen)
     */
    public function forceDeleteMember($id)
    {
        return response()->json([
            'success' => false,
            'message' => 'Kebijakan Keamanan: Data siswa tidak boleh dihapus secara permanen. Data siswa terhapus setelah 2 bulan tersimpan aman dalam berkas ZIP/folder arsip di bawah tabel.'
        ], 403);
    }

    // ==========================================
    // 7. REQUEST RESET PASSWORD PETUGAS & SISWA
    // ==========================================

    /**
     * Daftar Permintaan Reset Password
     */
    public function indexPasswordRequests()
    {
        $requests = PasswordResetRequest::orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $requests]);
    }

    /**
     * Admin Resetkan Password/PIN Langsung
     */
    public function directResetPasswordRequest(Request $request, $id)
    {
        $req = PasswordResetRequest::find($id);
        if (!$req) {
            return response()->json(['success' => false, 'message' => 'Permintaan reset tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if ($req->role === 'siswa') {
            if (!preg_match('/^\d{6}$/', $request->new_password)) {
                return response()->json(['success' => false, 'message' => 'PIN Siswa harus tepat 6 digit angka!'], 422);
            }
            if ($req->saver) {
                $req->saver->update(['security_pin' => Hash::make($request->new_password)]);
            } else {
                $saver = Saver::where('nis', $req->nis)->first();
                if ($saver) {
                    $saver->update(['security_pin' => Hash::make($request->new_password)]);
                }
            }
        } elseif ($req->user) {
            $req->user->update(['password' => Hash::make($request->new_password)]);
        }

        $req->update(['status' => 'used']);

        return response()->json([
            'success' => true,
            'message' => 'Kata Sandi / PIN untuk [' . $req->name . '] berhasil direset langsung oleh Admin!'
        ]);
    }

    /**
     * Hapus Permintaan Reset Password
     */
    public function destroyPasswordRequest($id)
    {
        $req = PasswordResetRequest::find($id);
        if (!$req) {
            return response()->json(['success' => false, 'message' => 'Data permintaan reset tidak ditemukan'], 404);
        }
        $req->delete();
        return response()->json(['success' => true, 'message' => 'Permintaan reset password berhasil dihapus.']);
    }

    // ==========================================
    // 9. OTORISASI MODAL AWAL & TOP UP KAS TELLER
    // ==========================================

    /**
     * Ambil Daftar Pengajuan Modal Awal & Top Up Teller
     */
    public function indexCashAdvances(Request $request)
    {
        $query = TellerCashAdvance::with(['teller', 'approvedBy'])->orderBy('id', 'desc');

        if ($request->filled('date')) {
            $query->whereDate('request_date', $request->date);
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('teller_id')) {
            $query->where('teller_id', $request->teller_id);
        }

        $advances = $query->paginate(15);
        $pendingCount = TellerCashAdvance::where('status', 'pending')->count();

        return response()->json([
            'success'       => true,
            'data'          => $advances,
            'pending_count' => $pendingCount,
        ]);
    }

    /**
     * Otorisasi / Setujui Modal Awal Teller (Approve)
     */
    public function approveCashAdvance(Request $request, $id)
    {
        $advance = TellerCashAdvance::with('teller')->find($id);
        if (!$advance) {
            return response()->json(['success' => false, 'message' => 'Data pengajuan modal kas tidak ditemukan.'], 404);
        }

        if ($advance->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan ini sudah berstatus ' . $advance->status . ' dan tidak dapat diproses lagi.'], 422);
        }

        DB::beginTransaction();
        try {
            $advance->update([
                'status'           => 'approved',
                'approved_by'      => $request->user()->id,
                'approved_at'      => now(),
                'rejection_reason' => null,
            ]);

            // Catat Jurnal Akuntansi:
            // Debet: 101 Kas Loket Teller ($tellerName)
            // Kredit: 102 Kas Brankas Utama Bank Mini
            $tellerName = $advance->teller ? $advance->teller->name : 'Teller #' . $advance->teller_id;

            AccountingJournal::create([
                'transaction_id' => null,
                'account_code'   => '101',
                'account_name'   => 'Kas Loket Teller (' . $tellerName . ')',
                'position'       => 'debit',
                'amount'         => $advance->amount,
            ]);

            AccountingJournal::create([
                'transaction_id' => null,
                'account_code'   => '102',
                'account_name'   => 'Kas Brankas Utama Bank Mini',
                'position'       => 'credit',
                'amount'         => $advance->amount,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan modal awal sebesar Rp ' . number_format($advance->amount, 0, ',', '.') . ' untuk ' . $tellerName . ' berhasil disetujui (Approved)!',
                'data'    => $advance->fresh(['teller', 'approvedBy'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyetujui pengajuan modal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Tolak Pengajuan Modal Awal Teller (Reject)
     */
    public function rejectCashAdvance(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|max:255',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi agar teller mengetahui alasannya.',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $advance = TellerCashAdvance::with('teller')->find($id);
        if (!$advance) {
            return response()->json(['success' => false, 'message' => 'Data pengajuan kas tidak ditemukan.'], 404);
        }

        if ($advance->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Pengajuan ini sudah berstatus ' . $advance->status . ' dan tidak dapat diproses lagi.'], 422);
        }

        $advance->update([
            'status'           => 'rejected',
            'approved_by'      => $request->user()->id,
            'approved_at'      => now(),
            'rejection_reason' => $request->rejection_reason,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan saldo modal berhasil ditolak.',
            'data'    => $advance->fresh(['teller', 'approvedBy'])
        ]);
    }
}