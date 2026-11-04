<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $fillable = [
        'user_id',
        'nis',
        'name',
        'email',
        'class_name',
        'graduation_year',
        'password',
        'status',
        'promoted_years_count',
        'last_promoted_at',
        'is_archived',
        'archived_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'promoted_years_count' => 'integer',
        'last_promoted_at'     => 'datetime',
        'is_archived'          => 'boolean',
        'archived_at'          => 'datetime',
    ];

    public function getRoleAttribute(): string
    {
        return 'customer';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function saver(): HasOne
    {
        return $this->hasOne(Saver::class, 'member_id')->withTrashed();
    }

    /**
     * Mendapatkan Tahun Ajaran (Academic Year) berdasarkan siklus kalender sekolah Indonesia (Juni - Juni):
     * Contoh:
     * - Periode 1 Juni 2025 s.d. 31 Mei 2026 = Tahun Ajaran "2025/2026"
     * - Periode 1 Juni 2026 s.d. 31 Mei 2027 = Tahun Ajaran "2026/2027"
     */
    public static function getAcademicYear($date = null): string
    {
        $dt = $date ? \Carbon\Carbon::parse($date) : now();
        $year = (int)$dt->year;
        $month = (int)$dt->month;

        // Bulan Juni (6) ke atas masuk periode awal Tahun Ajaran Baru (Juni s.d. Desember)
        // Bulan Januari s.d. Mei (1 s.d. 5) merupakan periode semester genap Tahun Ajaran berjalan
        if ($month >= 6) {
            return "{$year}/" . ($year + 1);
        } else {
            return ($year - 1) . "/{$year}";
        }
    }

    /**
     * Hitung tahun kelulusan otomatis berdasarkan jenjang kelas (Masa studi SMK 3 tahun):
     * - Kelas X (10)  : currentYear + 2 (Tahun ke-1, lulus 2 tahun lagi -> contoh: 2026 + 2 = 2028)
     * - Kelas XI (11) : currentYear + 1 (Tahun ke-2, lulus 1 tahun lagi -> contoh: 2026 + 1 = 2027)
     * - Kelas XII (12): currentYear     (Tahun ke-3 / tingkat akhir, lulus tahun ini -> 2026)
     */
    public static function calculateGraduationYear(?string $className, ?int $baseYear = null): int
    {
        $currentYear = $baseYear ?: (int)date('Y');
        if (empty($className)) {
            return $currentYear + 2;
        }

        $trimmed = trim($className);

        // Kelas 12 / XII atau Alumni: lulus tahun berjalan (currentYear)
        if (preg_match('/\b(12|XII|Lulus|Alumni)\b/i', $trimmed) || preg_match('/^(12|XII)\b/i', $trimmed)) {
            return $currentYear;
        }

        // Kelas 11 / XI: lulus 1 tahun berikutnya (currentYear + 1)
        if (preg_match('/\b(11|XI)\b/i', $trimmed) || preg_match('/^(11|XI)\b/i', $trimmed)) {
            return $currentYear + 1;
        }

        // Kelas 10 / X: siswa baru 3 tahun masa studi, lulus 2 tahun berikutnya (currentYear + 2)
        if (preg_match('/\b(10|X)\b/i', $trimmed) || preg_match('/^(10|X)\b/i', $trimmed)) {
            return $currentYear + 2;
        }

        return $currentYear + 2;
    }

    /**
     * Cek apakah kelas merupakan tingkat akhir (Kelas 12 / XII)
     */
    public static function isFinalGrade(?string $className): bool
    {
        if (empty($className)) return false;
        return (bool) preg_match('/\b(12|XII)\b/i', $className);
    }

    /**
     * Cek apakah siswa sudah berstatus lulus
     */
    public static function isGraduated(?string $className): bool
    {
        if (empty($className)) return false;
        return (bool) preg_match('/\b(Lulus|Alumni)\b/i', $className);
    }

    /**
     * Bersihkan prefix status Lulus / Alumni dari nama kelas
     * Contoh: "Lulus (Alumni XII RPL 1)" -> "XII RPL 1"
     */
    public static function stripGraduatedPrefix(?string $className): string
    {
        if (empty($className)) return '';
        $trimmed = trim($className);
        if (preg_match('/^Lulus\s*\(\s*Alumni\s+(.*?)\s*\)$/i', $trimmed, $m)) {
            return trim($m[1]);
        }
        if (preg_match('/^Lulus\s*\(\s*(.*?)\s*\)$/i', $trimmed, $m)) {
            return trim($m[1]);
        }
        $cleaned = preg_replace('/^(Lulus|Alumni)\s+/i', '', $trimmed);
        return trim($cleaned);
    }

    /**
     * Helper untuk menurunkan 1 tingkat kelas (Tinggal Kelas / Demote):
     * - "Lulus (Alumni XII RPL 1)" -> "XII RPL 1"
     * - "XII RPL 1" -> "XI RPL 1"
     * - "XI RPL 1"  -> "X RPL 1"
     * - "12 RPL 1"  -> "11 RPL 1"
     * - "11 RPL 1"  -> "10 RPL 1"
     * - "IX"        -> "VIII"
     * - "VIII"      -> "VII"
     */
    public static function demoteClassName(?string $className): ?string
    {
        if (empty($className)) return $className;
        $trimmed = self::stripGraduatedPrefix($className);

        // 1. Angka Romawi: XII -> XI, XI -> X
        if (preg_match('/\bXII\b/i', $trimmed)) {
            return preg_replace('/\bXII\b/i', 'XI', $trimmed, 1);
        }
        if (preg_match('/\bXI\b/i', $trimmed)) {
            return preg_replace('/\bXI\b/i', 'X', $trimmed, 1);
        }

        // 2. Angka Biasa: 12 -> 11, 11 -> 10
        if (preg_match('/\b12\b/', $trimmed)) {
            return preg_replace('/\b12\b/', '11', $trimmed, 1);
        }
        if (preg_match('/\b11\b/', $trimmed)) {
            return preg_replace('/\b11\b/', '10', $trimmed, 1);
        }

        // 3. SMP: IX -> VIII, VIII -> VII
        if (preg_match('/\bIX\b/i', $trimmed)) {
            return preg_replace('/\bIX\b/i', 'VIII', $trimmed, 1);
        }
        if (preg_match('/\bVIII\b/i', $trimmed)) {
            return preg_replace('/\bVIII\b/i', 'VII', $trimmed, 1);
        }

        // 4. Jika berawalan 'Kelas <angka>' atau '<angka>'
        if (preg_match('/^(Kelas\s*)?(\d+)(.*)$/i', $trimmed, $matches)) {
            $prefix = $matches[1] ?? '';
            $num = (int)$matches[2];
            $suffix = $matches[3] ?? '';
            if ($num > 10) {
                return $prefix . ($num - 1) . $suffix;
            }
        }

        return $trimmed;
    }

    /**
     * Helper untuk menaikkan 1 tingkat kelas siswa:
     * - "10 RPL 1" -> "11 RPL 1"
     * - "11 RPL 1" -> "12 RPL 1"
     * - "12 RPL 1" -> "Lulus (Alumni 12 RPL 1)"
     * - "X RPL 1"  -> "XI RPL 1"
     * - "XI RPL 1" -> "XII RPL 1"
     * - "XII RPL 1" -> "Lulus (Alumni XII RPL 1)"
     * - "Kelas 10" -> "Kelas 11"
     * - "Kelas 12" -> "Lulus (Alumni Kelas 12)"
     */
    public static function promoteClassName(?string $className): ?string
    {
        if (empty($className)) {
            return $className;
        }

        $trimmed = trim($className);

        // Jika sudah Lulus / Alumni, tidak perlu diubah lagi
        if (self::isGraduated($trimmed)) {
            return $trimmed;
        }

        // 1. Cek Kelas Tingkat Akhir (12 / XII) -> Pada bulan Juli menjadi LULUS
        if (preg_match('/\bXII\b/i', $trimmed) || preg_match('/\b12\b/', $trimmed) || preg_match('/^(Kelas\s*)?12\b/i', $trimmed)) {
            return 'Lulus (Alumni ' . $trimmed . ')';
        }

        // 2. Angka Romawi: XI -> XII, X -> XI
        if (preg_match('/\bXI\b/i', $trimmed)) {
            return preg_replace('/\bXI\b/i', 'XII', $trimmed, 1);
        }
        if (preg_match('/\bX\b/i', $trimmed)) {
            return preg_replace('/\bX\b/i', 'XI', $trimmed, 1);
        }

        // 3. Angka Biasa: 11 -> 12, 10 -> 11
        if (preg_match('/\b11\b/', $trimmed)) {
            return preg_replace('/\b11\b/', '12', $trimmed, 1);
        }
        if (preg_match('/\b10\b/', $trimmed)) {
            return preg_replace('/\b10\b/', '11', $trimmed, 1);
        }

        // 4. Jenjang SMP/Umum: VIII -> IX, VII -> VIII, 8 -> 9, 7 -> 8, 9/IX -> Lulus
        if (preg_match('/\bIX\b/i', $trimmed) || preg_match('/\b9\b/', $trimmed)) {
            return 'Lulus (Alumni ' . $trimmed . ')';
        }
        if (preg_match('/\bVIII\b/i', $trimmed)) {
            return preg_replace('/\bVIII\b/i', 'IX', $trimmed, 1);
        }
        if (preg_match('/\bVII\b/i', $trimmed)) {
            return preg_replace('/\bVII\b/i', 'VIII', $trimmed, 1);
        }
        if (preg_match('/\b8\b/', $trimmed)) {
            return preg_replace('/\b8\b/', '9', $trimmed, 1);
        }
        if (preg_match('/\b7\b/', $trimmed)) {
            return preg_replace('/\b7\b/', '8', $trimmed, 1);
        }

        // 5. Jika berawalan 'Kelas <angka>' atau '<angka>' (1-11)
        if (preg_match('/^(Kelas\s*)?(\d+)(.*)$/i', $trimmed, $matches)) {
            $prefix = $matches[1] ?? '';
            $num = (int)$matches[2];
            $suffix = $matches[3] ?? '';
            if ($num == 12) {
                return 'Lulus (Alumni ' . $trimmed . ')';
            }
            if ($num >= 1 && $num <= 11) {
                return $prefix . ($num + 1) . $suffix;
            }
        }

        return $trimmed;
    }
}