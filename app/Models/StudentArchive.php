<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentArchive extends Model
{
    protected $table = 'student_archives';

    protected $fillable = [
        'filename',
        'original_name',
        'file_path',
        'file_size',
        'student_count',
        'student_summary',
    ];

    protected $casts = [
        'file_size'     => 'integer',
        'student_count' => 'integer',
    ];
}
