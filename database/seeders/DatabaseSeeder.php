<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Customer;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Administrator
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'Administrator Bank Mini',
                'password' => Hash::make('password123'),
                'role'     => 'admin',
                'status'   => 'active',
            ]
        );

        // 2. Akun Piket Teller
        User::updateOrCreate(
            ['username' => 'teller1'],
            [
                'name'     => 'Piket Teller 1 (Siti Aminah)',
                'password' => Hash::make('password123'),
                'role'     => 'teller',
                'status'   => 'active',
            ]
        );

        // 3. Akun Supervisor (Kepala Bank Mini)
        User::updateOrCreate(
            ['username' => 'supervisor'],
            [
                'name'     => 'Supervisor (Budi Santoso, S.E.)',
                'password' => Hash::make('password123'),
                'role'     => 'supervisor',
                'status'   => 'active',
            ]
        );

        // 4. Akun Nasabah Siswa Demo
        Customer::updateOrCreate(
            ['account_number' => '260100001'],
            [
                'nis'          => '1001',
                'name'         => 'Ahmad Fauzi',
                'class_name'   => 'XII RPL 1',
                'security_pin' => Hash::make('123456'), // 6 Digit PIN
                'balance'      => 50000,                // Saldo Awal Rp 50.000
                'status'       => 'active',
            ]
        );
    }
}