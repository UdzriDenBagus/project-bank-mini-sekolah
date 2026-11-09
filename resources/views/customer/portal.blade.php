@extends('layouts.app')
@section('title', 'Portal Nasabah Siswa - Bank Mini')

@section('content')
<div class="max-w-6xl mx-auto space-y-6 pb-12">

    <!-- Top Header: Profil Nasabah Siswa -->
    <div class="bg-gradient-to-r from-indigo-950 via-indigo-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl relative overflow-hidden border border-indigo-800/40">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center space-x-4">
                <img src="{{ asset('images/logo_fintech.png') }}" alt="Logo Bank Mini" class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl object-cover shadow-md border border-white/20">
                <div class="space-y-1">
                    <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full bg-indigo-500/20 border border-indigo-400/30 text-indigo-200 text-xs font-semibold">
                        <span>Portal Tabungan Digital Siswa</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight" id="customer_name">Memuat Nama...</h1>
                    <p class="text-xs sm:text-sm text-indigo-200" id="customer_info">NIS: - • Kelas: - • No. Rek: -</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="loadCustomerData()" class="px-4 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 border border-white/10">
                    <span>Segarkan Data</span>
                </button>
                <button onclick="handleCustomerLogout()" class="px-4 py-2 bg-rose-600/80 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition flex items-center space-x-1.5 shadow">
                    <span>Keluar Akun</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Grid Finansial: Saldo, Saldo Mengendap, dan Batas Tarik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <!-- Saldo Total Tabungan -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-2">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Total Saldo Tabungan</span>
            <div class="text-2xl sm:text-3xl font-black text-slate-900" id="stat_balance">Rp 0</div>
            <p class="text-xs text-slate-500">Saldo riil yang tersimpan di buku tabungan.</p>
        </div>

        <!-- Saldo Mengendap Minimum -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Saldo Mengendap Wajib</span>
                <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold text-[10px] border border-amber-200">Wajib Bank</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-600">Rp 10.000</div>
            <p class="text-xs text-slate-500">Batas saldo minimum yang tidak dapat ditarik.</p>
        </div>

        <!-- Maksimal Dana Yang Dapat Ditarik -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Maksimal Saldo Ditarik</span>
                <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200">Tersedia</span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-600" id="stat_available">Rp 0</div>
            <p class="text-xs text-slate-500">Saldo tabungan dikurangi saldo mengendap Rp 10.000.</p>
        </div>
    </div>

    <!-- Section Perjanjian Penarikan Dana (> Rp 1.000.000) -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-lg font-black text-slate-900">Perjanjian Penarikan Dana Besar (> Rp 1.000.000)</h2>
                <p class="text-xs text-slate-500">Penarikan di atas Rp 1.000.000 wajib membuat janji minimal H-1 sebelum penarikan dan disetujui (ACC) oleh Teller.</p>
            </div>
            <div id="btnModalAppointmentContainer">
                <button type="button" onclick="openAppointmentModal()" id="btnOpenModalAppointment" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center space-x-1.5">
                    <span>+ Ajukan Janji Tarik Tunai</span>
                </button>
            </div>
        </div>

        <!-- Warning jika saldo < 1 Juta -->
        <div id="alertInsufficientBalance" class="hidden m-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
            <div class="font-bold flex items-center space-x-1.5">
                <span>Informasi Syarat Pengajuan Janji Penarikan</span>
            </div>
            <p class="text-[11px] text-amber-800 leading-relaxed" id="insufficientBalanceMsg">
                Saldo tabungan Anda saat ini belum memenuhi syarat untuk mengajukan perjanjian penarikan dana besar. Pengajuan perjanjian penarikan khusus hanya dapat dilakukan jika saldo tabungan Anda di atas Rp 1.000.000. Untuk penarikan dana di bawah / sampai dengan Rp 1.000.000, Anda dapat langsung melakukan penarikan di loket Teller tanpa perlu membuat perjanjian.
            </p>
        </div>

        <!-- Tabel Daftar Riwayat Pengajuan Perjanjian -->
        <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Daftar Pengajuan Perjanjian Penarikan Saya</h3>
                <span class="text-xs text-slate-500" id="appointment_count">0 Pengajuan</span>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-100">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100">
                        <tr>
                            <th class="p-3.5">Tanggal Pengajuan</th>
                            <th class="p-3.5">Rencana Penarikan</th>
                            <th class="p-3.5">Nominal Penarikan</th>
                            <th class="p-3.5">Keperluan / Catatan</th>
                            <th class="p-3.5">Status Pengajuan</th>
                            <th class="p-3.5">Petugas Teller</th>
                            <th class="p-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentTableBody" class="divide-y divide-slate-100 text-slate-700">
                        <tr>
                            <td colspan="7" class="p-6 text-center text-slate-400">Memuat data perjanjian...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Riwayat Mutasi Buku Tabungan -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-base font-black text-slate-900">Riwayat Mutasi Buku Tabungan</h2>
                <p class="text-xs text-slate-500">Catatan transaksi setoran dan penarikan pada rekening Anda.</p>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100">
                    <tr>
                        <th class="p-3.5">Waktu Transaksi</th>
                        <th class="p-3.5">Kode Transaksi</th>
                        <th class="p-3.5">Jenis</th>
                        <th class="p-3.5">Nominal</th>
                        <th class="p-3.5">Saldo Akhir</th>
                        <th class="p-3.5">Keterangan</th>
                    </tr>
                </thead>
                <tbody id="mutationTableBody" class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td colspan="6" class="p-6 text-center text-slate-400">Memuat mutasi rekening...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODAL: AJUKAN PERJANJIAN PENARIKAN DANA (> Rp 1.000.000) -->
<!-- ======================================================== -->
<div id="modalCustomerAppointment" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-5 border border-slate-100">
        
        <div class="flex items-center justify-between pb-3 border-b">
            <div>
                <h3 class="font-black text-slate-800 text-base">Ajukan Janji Tarik Tunai (> Rp 1 Jt)</h3>
                <p class="text-xs text-slate-500">Wajib diajukan minimal 1 hari sebelum penarikan (H-1)</p>
            </div>
            <button type="button" onclick="closeAppointmentModal()" class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1 rounded-lg transition">✕</button>
        </div>

        <!-- Banner Info Batas Saldo Mengendap -->
        <div class="p-3.5 rounded-2xl bg-indigo-50 border border-indigo-200 text-xs text-indigo-900 space-y-1">
            <div class="font-bold flex items-center justify-between">
                <span>Saldo Tersedia untuk Ditarik:</span>
                <span class="font-black text-indigo-700 text-sm" id="modalMaxAvailable">Rp 0</span>
            </div>
            <p class="text-[11px] text-indigo-800 leading-relaxed">
                Total saldo tabungan Anda wajib menyisakan <b>Rp 10.000</b> sebagai saldo mengendap di bank mini.
            </p>
        </div>

        <form onsubmit="submitCustomerAppointment(event)" class="space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Tanggal Rencana Penarikan (Minimal H+1 / Besok) <span class="text-rose-600">*</span>
                </label>
                <input type="date" id="input_withdrawal_date" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition font-medium">
                <p class="text-[11px] text-slate-500 mt-1">Pilih tanggal kapan Anda akan datang ke loket Teller untuk mengambil uang tunai.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Nominal Penarikan (> Rp 1.000.000) <span class="text-rose-600">*</span>
                </label>
                <input type="number" id="input_appointment_amount" min="1000001" step="1000" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition font-medium" placeholder="Contoh: 1500000">
                <div class="flex items-center justify-between text-[11px] text-slate-500 mt-1">
                    <span>Minimal: Rp 1.000.001</span>
                    <span id="labelMaxAmount">Maksimal: Rp 0</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Keperluan / Catatan Penarikan (Opsional)
                </label>
                <textarea id="input_appointment_notes" rows="2" class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs transition" placeholder="Contoh: Biaya daftar ulang / keperluan study tour..."></textarea>
            </div>

            <div class="pt-3 border-t flex items-center justify-end space-x-2">
                <button type="button" onclick="closeAppointmentModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition">
                    Batal
                </button>
                <button type="submit" id="btnSubmitAppointment" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow">
                    Kirim Pengajuan Perjanjian
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    let currentCustomer = null;

    // Pastikan token nasabah ada
    document.addEventListener('DOMContentLoaded', () => {
        const token = localStorage.getItem('bank_token') || localStorage.getItem('token');
        if (!token) {
            window.location.href = '/login';
            return;
        }

        // Set minimal tanggal penarikan = besok (H+1)
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const yyyy = tomorrow.getFullYear();
        const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const dd = String(tomorrow.getDate()).padStart(2, '0');
        document.getElementById('input_withdrawal_date').min = `${yyyy}-${mm}-${dd}`;

        loadCustomerData();
        loadAppointments();
        loadMutations();
    });

    function getAuthHeaders() {
        const token = localStorage.getItem('bank_token') || localStorage.getItem('token');
        return {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + token
        };
    }

    function formatRupiah(number) {
        return 'Rp ' + Number(number || 0).toLocaleString('id-ID');
    }

    // 1. Muat Informasi Saldo & Profil Siswa
    async function loadCustomerData() {
        try {
            const res = await fetch('/api/customer/balance', {
                headers: getAuthHeaders()
            });
            if (res.status === 401 || res.status === 403) {
                handleCustomerLogout();
                return;
            }
            const result = await res.json();
            if (result.success && result.data) {
                currentCustomer = result.data;
                document.getElementById('customer_name').textContent = currentCustomer.name || 'Nasabah Siswa';
                document.getElementById('customer_info').textContent = `NIS: ${currentCustomer.nis || '-'} • Kelas: ${currentCustomer.class_name || '-'} • No. Rek: ${currentCustomer.account_number || '-'}`;
                
                document.getElementById('stat_balance').textContent = formatRupiah(currentCustomer.balance);
                document.getElementById('stat_available').textContent = formatRupiah(currentCustomer.max_available_balance);
                document.getElementById('modalMaxAvailable').textContent = formatRupiah(currentCustomer.max_available_balance);
                document.getElementById('labelMaxAmount').textContent = 'Maksimal: ' + formatRupiah(currentCustomer.max_available_balance);

                // Cek Aturan: Jika saldo di bawah 1 juta maka tidak bisa mengajukan penarikan
                const canMakeAppointment = (currentCustomer.balance > 1000000 && currentCustomer.max_available_balance > 1000000);
                const btnOpen = document.getElementById('btnOpenModalAppointment');
                const alertInsufficient = document.getElementById('alertInsufficientBalance');

                if (!canMakeAppointment) {
                    btnOpen.classList.add('opacity-50', 'cursor-not-allowed');
                    btnOpen.setAttribute('disabled', 'disabled');
                    btnOpen.title = 'Saldo Anda di bawah Rp 1.000.000. Perjanjian khusus untuk penarikan > 1 Juta.';
                    alertInsufficient.classList.remove('hidden');
                    document.getElementById('insufficientBalanceMsg').textContent = `Saldo tabungan Anda saat ini Rp ${Number(currentCustomer.balance).toLocaleString('id-ID')} (tersedia Rp ${Number(currentCustomer.max_available_balance).toLocaleString('id-ID')} setelah saldo mengendap Rp 10.000). Pengajuan perjanjian penarikan hanya dapat dilakukan jika saldo tabungan di atas Rp 1.000.000.`;
                } else {
                    btnOpen.classList.remove('opacity-50', 'cursor-not-allowed');
                    btnOpen.removeAttribute('disabled');
                    alertInsufficient.classList.add('hidden');
                    document.getElementById('input_appointment_amount').max = currentCustomer.max_available_balance;
                }
            }
        } catch (e) {
            console.error('Error loadCustomerData:', e);
        }
    }

    // 2. Muat Riwayat Perjanjian Penarikan
    async function loadAppointments() {
        try {
            const res = await fetch('/api/customer/withdrawal-appointments', {
                headers: getAuthHeaders()
            });
            const result = await res.json();
            const tbody = document.getElementById('appointmentTableBody');
            
            if (result.success && Array.isArray(result.data)) {
                document.getElementById('appointment_count').textContent = `${result.data.length} Pengajuan`;
                if (result.data.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-slate-400">Belum ada pengajuan perjanjian penarikan dana besar.</td></tr>`;
                    return;
                }

                tbody.innerHTML = result.data.map(apt => {
                    let cancelBtn = '';
                    if (apt.status === 'pending') {
                        cancelBtn = `<button onclick="cancelAppointment(${apt.id})" class="px-2.5 py-1 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 rounded-lg text-[11px] font-bold transition">Batalkan</button>`;
                    } else if (apt.status === 'approved') {
                        cancelBtn = `<span class="text-[11px] font-bold text-emerald-600">Siap Dicairkan di Loket</span>`;
                    } else {
                        cancelBtn = `-`;
                    }

                    return `
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="p-3.5 font-medium">${apt.appointment_date}</td>
                            <td class="p-3.5 font-bold text-indigo-700">${apt.withdrawal_date}</td>
                            <td class="p-3.5 font-black text-slate-900">${apt.amount_formatted}</td>
                            <td class="p-3.5 text-slate-500 max-w-xs truncate" title="${apt.notes || '-'}">${apt.notes || '-'}</td>
                            <td class="p-3.5">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-black border ${apt.status_badge}">
                                    ${apt.status_label}
                                </span>
                            </td>
                            <td class="p-3.5 font-medium text-slate-600">${apt.teller_name || '<span class="text-slate-400 italic">Menunggu Review</span>'}</td>
                            <td class="p-3.5 text-center">${cancelBtn}</td>
                        </tr>
                    `;
                }).join('');
            }
        } catch (e) {
            console.error('Error loadAppointments:', e);
        }
    }

    // 3. Muat Mutasi Rekening Tabungan
    async function loadMutations() {
        try {
            const res = await fetch('/api/customer/mutations', {
                headers: getAuthHeaders()
            });
            const result = await res.json();
            const tbody = document.getElementById('mutationTableBody');

            let list = [];
            if (result.success && result.data) {
                if (Array.isArray(result.data)) {
                    list = result.data;
                } else if (result.data.data && Array.isArray(result.data.data)) {
                    list = result.data.data;
                }
            }

            if (list.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-slate-400">Belum ada riwayat mutasi transaksi.</td></tr>`;
                return;
            }

            tbody.innerHTML = list.slice(0, 10).map(trx => {
                const isDeposit = trx.transaction_type === 'deposit';
                const badge = isDeposit
                    ? `<span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">SETORAN</span>`
                    : `<span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">PENARIKAN</span>`;
                
                const amountColor = isDeposit ? 'text-emerald-600' : 'text-rose-600';
                const prefix = isDeposit ? '+ ' : '- ';

                const dateFormatted = new Date(trx.transaction_date).toLocaleString('id-ID', {
                    day: '2-digit', month: '2-digit', year: 'numeric',
                    hour: '2-digit', minute: '2-digit'
                });

                return `
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="p-3.5 text-slate-500 font-mono text-[11px]">${dateFormatted}</td>
                        <td class="p-3.5 font-mono font-bold text-slate-700">${trx.transaction_code}</td>
                        <td class="p-3.5">${badge}</td>
                        <td class="p-3.5 font-black ${amountColor}">${prefix}${formatRupiah(trx.amount)}</td>
                        <td class="p-3.5 font-bold text-slate-800">${formatRupiah(trx.balance_after)}</td>
                        <td class="p-3.5 text-slate-500 text-[11px]">${trx.description || '-'}</td>
                    </tr>
                `;
            }).join('');
        } catch (e) {
            console.error('Error loadMutations:', e);
        }
    }

    // Modal Control
    function openAppointmentModal() {
        if (!currentCustomer || currentCustomer.balance <= 1000000 || currentCustomer.max_available_balance <= 1000000) {
            Swal.fire({
                icon: 'warning',
                title: 'Saldo Belum Mencukupi',
                text: 'Pengajuan perjanjian penarikan dana besar (> Rp 1.000.000) hanya dapat dilakukan oleh nasabah dengan saldo di atas Rp 1.000.000.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        document.getElementById('modalCustomerAppointment').classList.remove('hidden');
    }

    function closeAppointmentModal() {
        document.getElementById('modalCustomerAppointment').classList.add('hidden');
    }

    // Submit Pengajuan Perjanjian Penarikan
    async function submitCustomerAppointment(e) {
        e.preventDefault();
        const withdrawal_date = document.getElementById('input_withdrawal_date').value;
        const amount = parseInt(document.getElementById('input_appointment_amount').value);
        const notes = document.getElementById('input_appointment_notes').value;
        const btn = document.getElementById('btnSubmitAppointment');

        if (!withdrawal_date || !amount) {
            Swal.fire('Input Belum Lengkap', 'Harap isi tanggal penarikan dan nominal penarikan.', 'warning');
            return;
        }

        if (amount <= 1000000) {
            Swal.fire('Nominal Kurang', 'Perjanjian penarikan khusus untuk nominal di atas Rp 1.000.000.', 'warning');
            return;
        }

        if (currentCustomer && amount > currentCustomer.max_available_balance) {
            Swal.fire('Melebihi Saldo', `Nominal penarikan melebihi saldo tersedia (${formatRupiah(currentCustomer.max_available_balance)}). Saldo wajib menyisakan Rp 10.000 saldo mengendap.`, 'error');
            return;
        }

        btn.disabled = true;
        btn.textContent = 'Mengirim Pengajuan...';

        try {
            const res = await fetch('/api/customer/withdrawal-appointments', {
                method: 'POST',
                headers: getAuthHeaders(),
                body: JSON.stringify({ withdrawal_date, amount, notes })
            });

            const result = await res.json();
            btn.disabled = false;
            btn.textContent = 'Kirim Pengajuan Perjanjian';

            if (res.ok && result.success) {
                closeAppointmentModal();
                Swal.fire({
                    icon: 'success',
                    title: 'Pengajuan Terkirim!',
                    text: result.message || 'Pengajuan perjanjian penarikan berhasil dikirim dan sedang menunggu ACC Petugas Teller.',
                    confirmButtonColor: '#4f46e5'
                });
                loadAppointments();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Pengajuan Gagal',
                    text: result.message || 'Terjadi kesalahan saat mengajukan perjanjian.'
                });
            }
        } catch (e) {
            btn.disabled = false;
            btn.textContent = 'Kirim Pengajuan Perjanjian';
            Swal.fire('Kesalahan Sistem', 'Tidak dapat menghubungi server: ' + e.message, 'error');
        }
    }

    // Batalkan Pengajuan
    async function cancelAppointment(id) {
        const confirm = await Swal.fire({
            title: 'Batalkan Pengajuan?',
            text: 'Apakah Anda yakin ingin membatalkan pengajuan perjanjian penarikan ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Kembali'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetch(`/api/customer/withdrawal-appointments/${id}`, {
                method: 'DELETE',
                headers: getAuthHeaders()
            });
            const result = await res.json();
            if (res.ok && result.success) {
                Swal.fire('Dibatalkan', result.message, 'success');
                loadAppointments();
            } else {
                Swal.fire('Gagal', result.message || 'Gagal membatalkan pengajuan.', 'error');
            }
        } catch (e) {
            Swal.fire('Error', 'Kesalahan koneksi: ' + e.message, 'error');
        }
    }

    function handleCustomerLogout() {
        localStorage.removeItem('bank_token');
        localStorage.removeItem('token');
        localStorage.removeItem('user_role');
        localStorage.removeItem('user_name');
        window.location.href = '/login';
    }
</script>
@endsection
