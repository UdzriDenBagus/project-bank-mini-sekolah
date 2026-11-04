@extends('layouts.app')
@section('title', 'Sinkronisasi Data Lokal ke Hosting - Bank Mini Sekolah')

@section('content')
<div class="max-w-4xl mx-auto py-10 px-4 sm:px-6">
    <div class="bg-white rounded-3xl shadow-xl overflow-hidden border border-slate-100">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-indigo-900 to-indigo-800 p-8 text-white">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center backdrop-blur border border-white/20">
                    <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-2xl font-black">Sinkronisasi Data ke Hosting</h1>
                    <p class="text-indigo-200 text-sm mt-1">Salin semua data nasabah, transaksi, dan akun dari laptop lokal ke https://bank-mini-udzri.web.id</p>
                </div>
            </div>
        </div>

        <div class="p-8 space-y-6">
            
            @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-start space-x-3">
                <svg class="w-6 h-6 text-emerald-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <div>
                    <h3 class="font-bold text-sm">Sinkronisasi Berhasil!</h3>
                    <p class="text-xs mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start space-x-3">
                <svg class="w-6 h-6 text-rose-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                <div>
                    <h3 class="font-bold text-sm">Gagal Sinkronisasi</h3>
                    <p class="text-xs mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            @endif

            <!-- Ringkasan Data Lokal -->
            <div>
                <h3 class="text-sm font-bold text-slate-700 uppercase tracking-wider mb-3">Ringkasan Data di Laptop Anda Saat Ini:</h3>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                        <span class="block text-2xl font-black text-indigo-600">{{ $counts['members'] ?? 0 }}</span>
                        <span class="text-xs text-slate-500 font-semibold">Anggota Siswa</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                        <span class="block text-2xl font-black text-indigo-600">{{ $counts['savers'] ?? 0 }}</span>
                        <span class="text-xs text-slate-500 font-semibold">Rekening Penabung</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                        <span class="block text-2xl font-black text-indigo-600">{{ $counts['transactions'] ?? 0 }}</span>
                        <span class="text-xs text-slate-500 font-semibold">Total Transaksi</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 text-center">
                        <span class="block text-2xl font-black text-indigo-600">{{ $counts['users'] ?? 0 }}</span>
                        <span class="text-xs text-slate-500 font-semibold">Pengguna Petugas</span>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi Utama -->
            <div class="pt-6 border-t border-slate-100 space-y-4">
                <form action="/sync-data/push" method="POST" onsubmit="this.btnSubmit.disabled=true; this.btnSubmit.innerText='Sedang Mengirim Data ke Hosting...';">
                    @csrf
                    <button type="submit" name="btnSubmit" class="w-full py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow-lg transition duration-200 flex items-center justify-center space-x-2 text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <span>🚀 SALIN SEMUA DATA KE HOSTING SEKARANG (1-KLIK)</span>
                    </button>
                </form>

                <div class="text-center">
                    <span class="text-xs text-slate-400">Atau jika ingin file cadangan SQL bersih tanpa error:</span>
                    <div class="mt-2">
                        <a href="/sync-data/download-sql" class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 underline">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Unduh File SQL Bersih (100% Bebas Error Procedure/Definer)
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

