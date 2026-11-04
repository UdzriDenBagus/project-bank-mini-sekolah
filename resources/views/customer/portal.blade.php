@extends('layouts.app')
@section('title', 'Layanan Nasabah - Bank Mini')

@section('content')
<script>
    window.location.href = '/login';
</script>
<div class="max-w-md mx-auto text-center py-12 space-y-4">
    <div class="p-6 bg-white rounded-3xl border border-slate-200 shadow-sm space-y-3">
        <h3 class="text-base font-bold text-slate-800">Layanan Khusus Aplikasi Mobile</h3>
        <p class="text-xs text-slate-500 leading-relaxed">
            Portal Buku Tabungan dan Informasi Saldo Nasabah Siswa hanya dapat diakses melalui Aplikasi Mobile Flutter.
        </p>
        <a href="/login" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded-xl text-xs font-bold">
            Kembali ke Halaman Login Petugas
        </a>
    </div>
</div>
@endsection
