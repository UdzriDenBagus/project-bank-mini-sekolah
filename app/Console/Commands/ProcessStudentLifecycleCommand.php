<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Member;
use App\Models\Transaction;

class ProcessStudentLifecycleCommand extends Command
{
    protected $signature = 'students:lifecycle {--force : Paksa jalankan kenaikan kelas tanpa menunggu bulan Juli}';
    protected $description = 'Otomatisasi siklus data siswa: Kenaikan kelas tahunan & kelulusan setiap bulan Juli, 3 tahun masuk sampah, 2 bulan arsip ZIP.';

    public function handle()
    {
        $this->info('Memulai pengecekan lifecycle siswa...');
        $force = (bool) $this->option('force');
        $currentMonth = (int) now()->month;
        $currentYear = (int) now()->year;

        // 1. Kenaikan kelas tahunan & kelulusan setiap bulan Juli
        $promotedCount = 0;
        if ($force || $currentMonth >= 7) {
            $activeMembers = Member::whereNull('deleted_at')->get();

            foreach ($activeMembers as $m) {
                if (Member::isGraduated($m->class_name)) {
                    continue;
                }

                if (!$force && $m->created_at && $m->created_at->format('Y-m') >= now()->format('Y-07')) {
                    continue;
                }

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

                if (Member::isGraduated($newClass)) {
                    $updateData['graduation_year'] = $currentYear;
                }

                $m->update($updateData);

                if ($m->saver) {
                    $m->saver->update(['class_name' => $newClass]);
                }

                $promotedCount++;
            }
            $this->info("{$promotedCount} data siswa berhasil diproses pada siklus bulan Juli (naik kelas / lulus).");
        } else {
            $this->comment("Bukan bulan Juli (bulan sekarang: {$currentMonth}). Kenaikan kelas otomatis dijadwalkan pada bulan Juli.");
        }

        // 2. Pindahkan siswa >= 3 tahun ke sampah
        $expiredMembers = Member::where('created_at', '<=', now()->subYears(3))
                                ->whereNull('deleted_at')
                                ->get();

        $trashedCount = 0;
        foreach ($expiredMembers as $m) {
            if ($m->saver) {
                $m->saver->delete();
            }
            $m->delete();
            $trashedCount++;
        }
        $this->info("{$trashedCount} data siswa dipindahkan ke sampah (masa simpan 3 tahun).");

        // 3. Hapus permanen siswa di sampah >= 2 bulan
        $purgedMembers = Member::onlyTrashed()
                               ->where('deleted_at', '<=', now()->subMonths(2))
                               ->get();

        $purgedCount = 0;
        foreach ($purgedMembers as $pm) {
            if ($pm->saver) {
                // Pertahankan riwayat transaksi dengan saver_id = null
                Transaction::where('saver_id', $pm->saver->id)->update(['saver_id' => null]);
                $pm->saver->forceDelete();
            }
            $pm->forceDelete();
            $purgedCount++;
        }
        $this->info("{$purgedCount} data siswa di sampah dihapus permanen (riwayat transaksi tetap aman).");

        return Command::SUCCESS;
    }
}
