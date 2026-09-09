@extends('layouts.app')
@section('title', 'Pintu Masuk Login Petugas - Bank Mini Sekolah')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        
        <!-- Header Card Gradient -->
        <div class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 p-8 text-white text-center relative overflow-hidden">
            <div class="inline-flex p-4 bg-indigo-800/80 rounded-2xl mb-3 border border-indigo-500 shadow-inner">
                <i class="fa-solid fa-building-columns text-4xl text-yellow-400"></i>
            </div>
            <h2 class="text-2xl font-black tracking-wide">Portal Petugas Bank Mini</h2>
            <p class="text-xs text-indigo-200 mt-1">Administrator • Piket Teller • Supervisor</p>
        </div>

        <!-- Form Login Petugas -->
        <div class="p-8 space-y-5">
            <form onsubmit="handleLoginStaff(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Username Petugas</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" id="staff_username" required class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition" placeholder="admin / teller1 / supervisor">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Kata Sandi (Password)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="staff_password" required class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition" placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" id="btnStaffSubmit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition duration-200 text-sm flex items-center justify-center">
                    <span>Masuk ke Sistem</span>
                    <i class="fa-solid fa-arrow-right ml-2"></i>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-center">
                <p class="text-[11px] text-slate-400">
                    <i class="fa-solid fa-mobile-screen mr-1 text-indigo-500"></i> Untuk nasabah siswa, silakan gunakan <b>Aplikasi Mobile Flutter</b>.
                </p>
            </div>
        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // Login Handler Petugas
    async function handleLoginStaff(e) {
        e.preventDefault();
        const username = document.getElementById('staff_username').value;
        const password = document.getElementById('staff_password').value;
        const btn = document.getElementById('btnStaffSubmit');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Memverifikasi...';

        const res = await fetch('/api/auth/login/staff', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        const result = await res.json();
        btn.disabled = false;
        btn.innerHTML = '<span>Masuk ke Sistem</span> <i class="fa-solid fa-arrow-right ml-2"></i>';

        if (res.ok && result.success) {
            localStorage.setItem('bank_token', result.data.token);
            localStorage.setItem('user_role', result.data.user.role);
            localStorage.setItem('user_name', result.data.user.name);

            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil!',
                text: `Selamat bertugas, ${result.data.user.name} (${result.data.user.role.toUpperCase()})`,
                timer: 1300,
                showConfirmButton: false
            }).then(() => {
                const role = result.data.user.role;
                if (role === 'admin') window.location.href = '/admin/dashboard';
                else if (role === 'teller') window.location.href = '/teller/loket';
                else if (role === 'supervisor') window.location.href = '/supervisor/dashboard';
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Autentikasi Gagal',
                text: result.message || 'Username atau kata sandi tidak terdaftar.'
            });
        }
    }
</script>
@endsection