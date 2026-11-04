<?php

namespace App\Models;

/**
 * Kompatibilitas Kode Lama:
 * Customer menjadi alias turunan dari Saver agar fitur/panggilan lama tetap berjalan lancar.
 */
class Customer extends Saver
{
}