@extends('layouts.app')
@section('title', 'Buku Tabungan Digital Siswa - Bank Mini')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Profil Siswa & Kartu ATM Digital -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Saldo & Identitas Card (2 Kolom) -->
        <div class="md:col-span-2 bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 rounded-3xl p-6 text-white shadow-xl flex flex-col justify-between space-y-6 border border-indigo-700/40">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-indigo-800 rounded-2xl border border-indigo-600">
                        <i class="fa-solid fa-graduation-cap text-2xl text-yellow-400"></i>
                    </div>
                    <div>
                        <span class="text-xs text-indigo-300 uppercase tracking-widest font-bold block">Tabungan Siswa</span>
                        <h3 id="custName" class="text-xl font-black tracking-tight">Memuat nama...</h3>
                    </div>
                </div>
                <span id="custClass" class="text-xs bg-indigo-800/80 px-3 py-1 rounded-full font-bold border border-indigo-600">-</span>
            </div>

            <div>
                <span class="text-xs text-indigo-300 font-medium">Saldo Tabungan Saat Ini:</span>
                <h2 id="custBalance" class="text-4xl font-black text-yellow-400 tracking-tight mt-1">Rp 0</h2>
            </div>

            <div class="flex items-center justify-between border-t border-indigo-800/80 pt-4 text-xs font-mono">
                <div>
                    <span class="text-indigo-400 block text-[10px]">NOMOR REKENING</span>
                    <span id="custAccount" class="font-bold text-sm tracking-wider">-</span>
                </div>
                <div class="text-right">
                    <span class="text-indigo-400 block text-[10px]">NOMOR INDUK SISWA (NIS)</span>
                    <span id="custNis" class="font-bold text-sm tracking-wider">-</span>
                </div>
            </div>
        </div>

        <!-- Kartu QR Code Pemindai Teller (1 Kolom) -->
        <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200 flex flex-col items-center justify-center text-center space-y-3">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">QR Code Rekening Anda</span>
            <div id="portalQrCode" class="p-3 bg-white border-2 border-indigo-100 rounded-2xl shadow-inner flex items-center justify-center"></div>
            <p class="text-[11px] text-slate-500 font-medium">Tunjukkan QR ini ke Teller loket untuk setor/tarik tunai.</p>
        </div>

    </div>

    <!-- Tabel Buku Tabungan Digital (Mutasi) -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-black text-slate-800 text-lg flex items-center">
                <i class="fa-solid fa-clock-rotate-left text-indigo-600 mr-2"></i> Riwayat Mutasi Buku Tabungan
            </h3>
            <button onclick="loadCustomerPortal()" class="text-xs text-indigo-600 font-bold hover:underline flex items-center">
                <i class="fa-solid fa-rotate-right mr-1"></i> Segarkan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                        <th class="py-3 px-4">Tanggal Transaksi</th>
                        <th class="py-3 px-4">Kode Transaksi</th>
                        <th class="py-3 px-4">Keterangan</th>
                        <th class="py-3 px-4 text-center">Jenis</th>
                        <th class="py-3 px-4 text-right">Nominal</th>
                        <th class="py-3 px-4 text-right">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody id="mutationsTableBody">
                    <tr><td colspan="6" class="text-center py-6 text-slate-400 text-xs">Memuat mutasi tabungan...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    async function loadCustomerPortal() {
        // 1. Load Profile & Saldo
        const resBalance = await fetchAPI('/customer/balance');
        if (resBalance && resBalance.data && resBalance.data.success) {
            const d = resBalance.data.data;
            document.getElementById('custName').innerText = d.name;
            document.getElementById('custClass').innerText = d.class_name;
            document.getElementById('custAccount').innerText = d.account_number;
            document.getElementById('custNis').innerText = d.nis;
            document.getElementById('custBalance').innerText = `Rp ${Number(d.balance).toLocaleString('id-ID')}`;

            // Render QR Code
            const qrContainer = document.getElementById('portalQrCode');
            qrContainer.innerHTML = '';
            new QRCode(qrContainer, {
                text: d.account_number,
                width: 130,
                height: 130,
                colorDark: "#1e1b4b",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        }

        // 2. Load Mutations
        const resMutations = await fetchAPI('/customer/mutations');
        const tbody = document.getElementById('mutationsTableBody');
        if (resMutations && resMutations.data && resMutations.data.success) {
            const list = resMutations.data.data.data || resMutations.data.data;
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-slate-400 text-xs">Belum ada riwayat transaksi pada rekening ini.</td></tr>';
                return;
            }

            tbody.innerHTML = list.map(m => `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-4 text-xs font-bold text-slate-700">${new Date(m.transaction_date).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 font-mono text-xs text-indigo-600 font-bold">${m.transaction_code}</td>
                    <td class="py-3 px-4 text-xs text-slate-600">${m.description || '-'}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase ${m.transaction_type === 'deposit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                            ${m.transaction_type === 'deposit' ? 'SETOR' : 'TARIK'}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right font-black ${m.transaction_type === 'deposit' ? 'text-emerald-700' : 'text-rose-700'}">
                        ${m.transaction_type === 'deposit' ? '+' : '-'}Rp ${Number(m.amount).toLocaleString('id-ID')}
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-slate-900">
                        Rp ${Number(m.balance_after).toLocaleString('id-ID')}
                    </td>
                </tr>
            `).join('');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadCustomerPortal();
    });
</script>
@endsection