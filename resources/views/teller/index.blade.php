@extends('layouts.app')
@section('title', 'Loket Piket Teller - Bank Mini Sekolah')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- ======================================================== -->
    <!-- KOLOM KIRI: SCANNER QR & PROFIL NASABAH (5 Kolom) -->
    <!-- ======================================================== -->
    <div class="lg:col-span-5 space-y-6">
        
        <!-- Scanner & Search Box -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-slate-800 text-base flex items-center">
                    <i class="fa-solid fa-qrcode text-indigo-600 mr-2 text-lg"></i> Identifikasi Nasabah Loket
                </h3>
                <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Kamera / Manual</span>
            </div>

            <!-- Kamera Scanner Area -->
            <div class="space-y-2">
                <div id="reader" class="rounded-2xl overflow-hidden border-2 border-dashed border-indigo-200 w-full min-h-[190px] bg-slate-50 flex items-center justify-center">
                    <span class="text-xs text-slate-400 font-medium">Pemindai Kamera Sedang Nonaktif</span>
                </div>
                <button id="btnToggleCamera" onclick="toggleCameraScanner()" class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center justify-center">
                    <i class="fa-solid fa-camera mr-1.5"></i> Buka Kamera Pemindai QR
                </button>
            </div>

            <!-- Pencarian Manual -->
            <div class="flex space-x-2 pt-1">
                <input type="text" id="manualSearchInput" placeholder="Ketik No Rekening atau NIS..." class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs font-mono">
                <button onclick="searchCustomerManual()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center">
                    <i class="fa-solid fa-magnifying-glass mr-1"></i> Cari
                </button>
            </div>
        </div>

        <!-- Kartu Identitas Profil Nasabah Aktif -->
        <div id="activeCustomerCard" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4 hidden transition-all duration-300">
            <div class="flex items-center justify-between border-b pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Profil Nasabah Aktif</span>
                <span id="custStatusBadge" class="text-[10px] px-2.5 py-0.5 rounded-full font-black bg-emerald-100 text-emerald-800">ACTIVE</span>
            </div>

            <div>
                <h4 id="custNamaText" class="text-xl font-black text-slate-800 leading-tight">Nama Siswa</h4>
                <p id="custRekeningText" class="text-sm font-mono text-indigo-600 font-bold mt-0.5">No. Rek: -</p>
                <p id="custKelasText" class="text-xs text-slate-500 mt-1">Kelas: - | NIS: -</p>
            </div>

            <!-- Saldo Berjalan Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-950 to-slate-900 text-white shadow-xl">
                <span class="text-xs text-indigo-300 block font-semibold">Total Saldo Berjalan:</span>
                <span id="custSaldoText" class="text-3xl font-black text-yellow-400 tracking-tight block mt-1">Rp 0</span>
            </div>

            <!-- Tombol Transaksi Setor & Tarik -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button onclick="openDepositModal()" class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow transition text-xs flex items-center justify-center">
                    <i class="fa-solid fa-circle-arrow-down mr-1.5 text-sm"></i> Setor Tunai
                </button>
                <button onclick="openWithdrawModal()" class="py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl shadow transition text-xs flex items-center justify-center">
                    <i class="fa-solid fa-circle-arrow-up mr-1.5 text-sm"></i> Tarik Tunai
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- KOLOM KANAN: REKONSILIASI CLOSING & MUTASI HARI INI (7 Kolom) -->
    <!-- ======================================================== -->
    <div class="lg:col-span-7 space-y-6">

        <!-- Banner Rekonsiliasi Kas Closing -->
        <div class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-3xl shadow-lg p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span class="text-xs font-black uppercase tracking-wider bg-amber-700/50 px-2.5 py-1 rounded-full text-amber-100">Akhir Jam Kerja</span>
                <h4 class="text-xl font-black mt-1">Penutupan Kas Harian (Closing)</h4>
                <p class="text-xs text-amber-100 mt-0.5">Cocokkan saldo digital komputer dengan uang fisik di laci loket</p>
            </div>
            <button onclick="openClosingModal()" class="px-5 py-3 bg-white text-amber-900 font-black rounded-2xl shadow hover:bg-amber-50 transition text-xs flex items-center justify-center whitespace-nowrap self-start sm:self-auto">
                <i class="fa-solid fa-cash-register mr-1.5 text-base"></i> Rekonsiliasi Kas
            </button>
        </div>

        <!-- Tabel Mutasi Transaksi Loket Hari Ini -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-slate-800 text-base flex items-center">
                    <i class="fa-solid fa-receipt text-indigo-600 mr-2 text-lg"></i> Mutasi Transaksi Loket Hari Ini
                </h3>
                <button onclick="loadTodayTransactions()" class="text-xs text-indigo-600 font-bold hover:underline flex items-center">
                    <i class="fa-solid fa-rotate-right mr-1"></i> Segarkan
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-3">Kode TRX</th>
                            <th class="py-3 px-3">Nasabah</th>
                            <th class="py-3 px-3 text-center">Jenis</th>
                            <th class="py-3 px-3 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="todayTableBody">
                        <tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">Memuat mutasi hari ini...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<!-- ======================================================== -->
<!-- MODALS TRANSAKSI & STRUK -->
<!-- ======================================================== -->

<!-- Modal Setor Tunai -->
<div id="modalDeposit" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center">
            <i class="fa-solid fa-circle-arrow-down text-emerald-600 mr-2"></i> Transaksi Setor Tunai
        </h3>
        <form onsubmit="submitDeposit(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nominal Setoran (Rp)</label>
                <input type="number" id="depositAmount" required min="1000" step="1000" placeholder="Contoh: 50000" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-lg text-emerald-700">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Keterangan (Opsional)</label>
                <input type="text" id="depositDesc" placeholder="Setoran tabungan bulanan" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalDeposit')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnDepositSubmit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow">Proses Setoran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tarik Tunai (Verifikasi PIN & Batas Saldo Mengendap) -->
<div id="modalWithdraw" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center">
            <i class="fa-solid fa-circle-arrow-up text-rose-600 mr-2"></i> Transaksi Penarikan Tunai
        </h3>
        <p class="text-xs text-slate-500">Saldo mengendap minimum yang wajib tersisa di rekening adalah <b>Rp 10.000</b>.</p>
        <form onsubmit="submitWithdraw(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nominal Penarikan (Rp)</label>
                <input type="number" id="withdrawAmount" required min="1000" step="1000" placeholder="Contoh: 20000" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none font-bold text-lg text-rose-700">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">PIN Keamanan Nasabah (6-Digit)</label>
                <input type="password" id="withdrawPin" required maxlength="6" minlength="6" placeholder="••••••" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none font-mono text-center tracking-widest text-lg font-bold">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Keterangan (Opsional)</label>
                <input type="text" id="withdrawDesc" placeholder="Penarikan uang saku" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none">
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalWithdraw')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnWithdrawSubmit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl text-xs shadow">Otorisasi & Tarik</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Dialog Struk Bukti Transaksi Thermal -->
<div id="modalReceipt" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-center">
        
        <!-- Cetak Area Struk -->
        <div id="printableArea" class="bg-white text-slate-900 border border-slate-200 rounded-2xl p-5 shadow-sm text-left font-mono text-xs space-y-3">
            <div class="text-center border-b pb-2">
                <h4 class="font-black text-sm uppercase">BANK MINI SEKOLAH</h4>
                <p class="text-[10px] text-slate-500">Struk Bukti Transaksi Loket</p>
            </div>
            <div class="space-y-1 text-[11px]">
                <div class="flex justify-between"><span>No TRX:</span><b id="recTrxCode">-</b></div>
                <div class="flex justify-between"><span>Waktu:</span><span id="recDate">-</span></div>
                <div class="flex justify-between"><span>Piket Teller:</span><span id="recTeller">-</span></div>
                <div class="flex justify-between"><span>Nasabah:</span><span id="recCust">-</span></div>
                <div class="flex justify-between"><span>No Rekening:</span><span id="recAcc">-</span></div>
            </div>
            <div class="border-t border-b py-2 space-y-1">
                <div class="flex justify-between text-xs">
                    <span id="recType" class="font-black">SETORAN</span>
                    <b id="recAmount" class="font-black text-sm">Rp 0</b>
                </div>
            </div>
            <div class="flex justify-between text-xs font-bold pt-1">
                <span>Sisa Saldo:</span>
                <b id="recBalance" class="text-indigo-700">Rp 0</b>
            </div>
            <div class="text-center text-[10px] text-slate-400 pt-2 border-t">
                Simpan struk ini sebagai bukti transaksi resmi. Terima kasih!
            </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2 no-print">
            <button type="button" onclick="closeModal('modalReceipt')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Struk
            </button>
        </div>
    </div>
</div>

<!-- Modal Rekonsiliasi Kas Harian Closing -->
<div id="modalClosing" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center">
            <i class="fa-solid fa-cash-register text-amber-600 mr-2"></i> Rekonsiliasi Kas Harian Teller
        </h3>

        <div class="bg-slate-50 p-4 rounded-2xl border space-y-2 text-xs">
            <div class="flex justify-between"><span>Saldo Awal Kas:</span><b id="clOpening">Rp 0</b></div>
            <div class="flex justify-between text-emerald-700"><span>(+) Total Setoran Hari Ini:</span><b id="clDeposit">Rp 0</b></div>
            <div class="flex justify-between text-rose-700"><span>(-) Total Penarikan Hari Ini:</span><b id="clWithdraw">Rp 0</b></div>
            <div class="border-t pt-2 flex justify-between text-sm font-black text-indigo-900">
                <span>Saldo Akhir Sistem:</span>
                <b id="clSystem">Rp 0</b>
            </div>
        </div>

        <form onsubmit="submitClosingData(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hitungan Uang Fisik di Laci Loket (Rp)</label>
                <input type="number" id="clPhysicalInput" required min="0" step="1000" placeholder="Masukkan total uang fisik" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold text-lg text-amber-800">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Catatan Closing (Opsional)</label>
                <textarea id="clNotes" rows="2" placeholder="Catatan jika ada uang rusak atau selisih..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalClosing')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow">Kirim Rekonsiliasi</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let activeCustomer = null;
    let html5QrCode = null;
    let isScanning = false;

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // ================= SCANNER QR CODE WEBCAM =================
    async function toggleCameraScanner() {
        const btn = document.getElementById('btnToggleCamera');
        if (!isScanning) {
            html5QrCode = new Html5Qrcode("reader");
            const config = { fps: 10, qrbox: { width: 200, height: 200 } };
            
            try {
                await html5QrCode.start(
                    { facingMode: "environment" },
                    config,
                    (decodedText) => {
                        // Saat QR Berhasil Terbaca
                        searchCustomer(decodedText);
                        toggleCameraScanner(); // Stop scanner otomatis
                    }
                );
                isScanning = true;
                btn.innerHTML = '<i class="fa-solid fa-stop mr-1.5 text-rose-500"></i> Matikan Kamera Pemindai';
            } catch (err) {
                Swal.fire('Kamera Gagal', 'Pastikan izin webcam/kamera telah diaktifkan.', 'error');
            }
        } else {
            if (html5QrCode) {
                await html5QrCode.stop();
                html5QrCode = null;
            }
            isScanning = false;
            document.getElementById('reader').innerHTML = '<span class="text-xs text-slate-400 font-medium">Pemindai Kamera Sedang Nonaktif</span>';
            btn.innerHTML = '<i class="fa-solid fa-camera mr-1.5"></i> Buka Kamera Pemindai QR';
        }
    }

    function searchCustomerManual() {
        const q = document.getElementById('manualSearchInput').value.trim();
        if (q) searchCustomer(q);
    }

    // ================= PENCARIAN NASABAH =================
    async function searchCustomer(query) {
        const res = await fetchAPI(`/teller/customers/search?query=${encodeURIComponent(query)}`);
        if (res && res.data && res.data.success) {
            activeCustomer = res.data.data;
            document.getElementById('custNamaText').innerText = activeCustomer.name;
            document.getElementById('custRekeningText').innerText = `No. Rek: ${activeCustomer.account_number}`;
            document.getElementById('custKelasText').innerText = `Kelas: ${activeCustomer.class_name} | NIS: ${activeCustomer.nis}`;
            document.getElementById('custSaldoText').innerText = `Rp ${Number(activeCustomer.balance).toLocaleString('id-ID')}`;
            document.getElementById('activeCustomerCard').classList.remove('hidden');
        } else {
            Swal.fire({ icon: 'warning', title: 'Tidak Ditemukan', text: (res && res.data && res.data.message) || 'Nasabah tidak ditemukan' });
        }
    }

    // ================= SETOR TUNAI =================
    function openDepositModal() {
        if (!activeCustomer) return;
        document.getElementById('depositAmount').value = '';
        document.getElementById('depositDesc').value = '';
        document.getElementById('modalDeposit').classList.remove('hidden');
    }

    async function submitDeposit(e) {
        e.preventDefault();
        const payload = {
            customer_id: activeCustomer.id,
            amount: document.getElementById('depositAmount').value,
            description: document.getElementById('depositDesc').value
        };

        const res = await fetchAPI('/teller/transactions/deposit', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            closeModal('modalDeposit');
            searchCustomer(activeCustomer.account_number); // Refresh saldo
            loadTodayTransactions();
            showReceiptModal(res.data.data.receipt);
        } else {
            Swal.fire('Gagal Setor', (res && res.data && res.data.message) || 'Gagal memproses transaksi', 'error');
        }
    }

    // ================= TARIK TUNAI =================
    function openWithdrawModal() {
        if (!activeCustomer) return;
        document.getElementById('withdrawAmount').value = '';
        document.getElementById('withdrawPin').value = '';
        document.getElementById('withdrawDesc').value = '';
        document.getElementById('modalWithdraw').classList.remove('hidden');
    }

    async function submitWithdraw(e) {
        e.preventDefault();
        const payload = {
            customer_id: activeCustomer.id,
            amount: document.getElementById('withdrawAmount').value,
            security_pin: document.getElementById('withdrawPin').value,
            description: document.getElementById('withdrawDesc').value
        };

        const res = await fetchAPI('/teller/transactions/withdraw', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            closeModal('modalWithdraw');
            searchCustomer(activeCustomer.account_number); // Refresh saldo
            loadTodayTransactions();
            showReceiptModal(res.data.data.receipt);
        } else {
            Swal.fire('Penarikan Ditolak!', (res && res.data && res.data.message) || 'Gagal memproses penarikan', 'error');
        }
    }

    // ================= STRUK RECEIPT MODAL =================
    function showReceiptModal(r) {
        document.getElementById('recTrxCode').innerText = r.transaction_code;
        document.getElementById('recDate').innerText = r.date;
        document.getElementById('recTeller').innerText = r.teller;
        document.getElementById('recCust').innerText = r.name;
        document.getElementById('recAcc').innerText = r.account_number;
        document.getElementById('recType').innerText = r.type;
        document.getElementById('recAmount').innerText = `Rp ${Number(r.amount).toLocaleString('id-ID')}`;
        document.getElementById('recBalance').innerText = `Rp ${Number(r.balance).toLocaleString('id-ID')}`;
        document.getElementById('modalReceipt').classList.remove('hidden');
    }

    // ================= MUTASI HARI INI =================
    async function loadTodayTransactions() {
        const res = await fetchAPI('/teller/transactions/today');
        const tbody = document.getElementById('todayTableBody');
        if (res && res.data && res.data.success) {
            const list = res.data.data;
            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">Belum ada transaksi di loket hari ini</td></tr>';
                return;
            }
            tbody.innerHTML = list.map(t => `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-3 font-mono text-xs font-bold text-slate-700">${t.transaction_code}</td>
                    <td class="py-3 px-3 font-semibold text-slate-800">${t.customer ? t.customer.name : '-'}</td>
                    <td class="py-3 px-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase ${t.transaction_type === 'deposit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                            ${t.transaction_type}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-right font-black ${t.transaction_type === 'deposit' ? 'text-emerald-700' : 'text-rose-700'}">
                        ${t.transaction_type === 'deposit' ? '+' : '-'}Rp ${Number(t.amount).toLocaleString('id-ID')}
                    </td>
                </tr>
            `).join('');
        }
    }

    // ================= CLOSING REKONSILIASI KAS =================
    async function openClosingModal() {
        const res = await fetchAPI('/teller/closing/calculation');
        if (res && res.data && res.data.success) {
            const d = res.data.data;
            document.getElementById('clOpening').innerText = `Rp ${Number(d.opening_balance).toLocaleString('id-ID')}`;
            document.getElementById('clDeposit').innerText = `Rp ${Number(d.total_deposit).toLocaleString('id-ID')}`;
            document.getElementById('clWithdraw').innerText = `Rp ${Number(d.total_withdraw).toLocaleString('id-ID')}`;
            document.getElementById('clSystem').innerText = `Rp ${Number(d.system_balance).toLocaleString('id-ID')}`;
            document.getElementById('clPhysicalInput').value = d.system_balance; // Default klop
            document.getElementById('modalClosing').classList.remove('hidden');
        }
    }

    async function submitClosingData(e) {
        e.preventDefault();
        const payload = {
            physical_balance: document.getElementById('clPhysicalInput').value,
            notes: document.getElementById('clNotes').value
        };

        const res = await fetchAPI('/teller/closing/submit', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            closeModal('modalClosing');
            Swal.fire({
                icon: res.data.data.status === 'dispute' ? 'warning' : 'success',
                title: res.data.data.status === 'dispute' ? 'Terdeteksi Selisih (Dispute)' : 'Closing Terkirim (Klop)',
                text: res.data.message
            });
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadTodayTransactions();
    });
</script>
@endsection