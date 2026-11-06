@extends('layouts.app')
@section('title', 'Loket Piket Teller - Bank Mini Sekolah')

@section('content')
<!-- ======================================================== -->
<!-- BAR STATUS OPERASIONAL PELAYANAN LOKET TELLER (BUKA / ISTIRAHAT / TUTUP) -->
<!-- ======================================================== -->
<div class="mb-6 bg-white rounded-3xl p-5 shadow-sm border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center space-x-3.5">
        <div id="tellerStatusDot" class="w-4 h-4 rounded-full bg-emerald-500 animate-pulse ring-4 ring-emerald-100 flex-shrink-0"></div>
        <div>
            <div class="flex items-center space-x-2">
                <span class="text-xs font-black uppercase tracking-wider text-slate-500">Status Layanan Loket:</span>
                <span id="tellerCurrentStatusBadge" class="text-xs font-black px-3 py-1 rounded-full uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
                    BUKA
                </span>
                <span id="tellerStatusUpdatedText" class="text-xs font-bold text-slate-600 font-mono bg-slate-100 px-2.5 py-1 rounded-xl border border-slate-200 shadow-sm"></span>
            </div>
            <p id="tellerStatusDescText" class="text-xs text-slate-500 mt-0.5">
                Loket Buka - Siap melayani setoran & penarikan nasabah (terhubung langsung ke mobile nasabah).
            </p>
        </div>
    </div>

    <!-- Tombol Pilihan Status Operasional -->
    <div class="flex items-center space-x-2 bg-slate-50 p-1.5 rounded-2xl border border-slate-200 self-start md:self-auto">
        <button onclick="setTellerServiceStatus('buka')" id="btnTellerStatusBuka" type="button" class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 bg-emerald-600 text-white shadow ring-2 ring-emerald-300">
            <span>🟢 Buka</span>
        </button>
        <button onclick="setTellerServiceStatus('istirahat')" id="btnTellerStatusIstirahat" type="button" class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 text-slate-600 hover:text-amber-700 hover:bg-amber-100/60">
            <span>🟡 Istirahat</span>
        </button>
        <button onclick="setTellerServiceStatus('tutup')" id="btnTellerStatusTutup" type="button" class="px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 text-slate-600 hover:text-rose-700 hover:bg-rose-100/60">
            <span>🔴 Tutup</span>
        </button>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- ======================================================== -->
    <!-- KOLOM KIRI: SCANNER QR & PROFIL NASABAH (5 Kolom) -->
    <!-- ======================================================== -->
    <div class="lg:col-span-5 space-y-6">
        
        <!-- Scanner & Search Box -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-black text-slate-800 text-base flex items-center"> Identifikasi Nasabah Loket
                </h3>
                <span class="text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full">Kamera / Manual</span>
            </div>

            <!-- Kamera Scanner Area -->
            <div class="space-y-2">
                <div id="reader" class="rounded-2xl overflow-hidden border-2 border-dashed border-indigo-200 w-full min-h-[190px] bg-slate-50 flex items-center justify-center">
                    <span class="text-xs text-slate-400 font-medium">Pemindai Kamera Sedang Nonaktif</span>
                </div>
                <button id="btnToggleCamera" onclick="toggleCameraScanner()" class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center justify-center"> Buka Kamera Pemindai QR
                </button>
            </div>

            <!-- Pencarian Manual -->
            <div class="flex space-x-2 pt-1">
                <input type="text" id="manualSearchInput" placeholder="Ketik No Rekening atau NIS..." class="flex-1 px-4 py-2.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 text-xs font-mono">
                <button onclick="searchCustomerManual()" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center"> Cari
                </button>
            </div>

            <!-- Tombol Cepat Aktivasi Anggota Baru (Revisi 3) -->
            <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-700 block">Aktivasi Anggota Baru</span>
                    <span class="text-[10px] text-slate-400">Aktifkan siswa menjadi nasabah penabung</span>
                </div>
                <button onclick="openActivateMemberTellerModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow transition flex items-center"> Aktivasi Nasabah
                </button>
            </div>
        </div>

        <!-- Kartu Identitas Profil Nasabah Aktif -->
        <div id="activeCustomerCard" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4 hidden transition-all duration-300">
            <div class="flex items-center justify-between border-b pb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Profil Penabung Aktif</span>
                <span id="custStatusBadge" class="text-[10px] px-2.5 py-0.5 rounded-full font-black bg-emerald-100 text-emerald-800">ACTIVE</span>
            </div>

            <div>
                <h4 id="custNamaText" class="text-xl font-black text-slate-800 leading-tight">Nama Siswa</h4>
                <p id="custRekeningText" class="text-sm font-mono text-indigo-600 font-bold mt-0.5">No. Rek: -</p>
                <p id="custKelasText" class="text-xs text-slate-500 mt-1">Kelas: - | NIS: -</p>
            </div>

            <!-- Saldo Berjalan Card -->
            <div class="p-5 rounded-2xl bg-gradient-to-br from-indigo-950 to-slate-900 text-white shadow-xl space-y-3">
                <div>
                    <span class="text-xs text-indigo-300 block font-semibold">Total Saldo Berjalan:</span>
                    <span id="custSaldoText" class="text-3xl font-black text-yellow-400 tracking-tight block mt-1">Rp 0</span>
                </div>
                <div class="pt-2.5 border-t border-indigo-800/80 flex items-center justify-between text-[11px] text-indigo-200">
                    <span> Saldo Mengendap: <b class="text-white">Rp 10.000</b></span>
                    <span id="custSaldoDitarikText">Bisa Ditarik: <b class="text-emerald-400">Rp 0</b></span>
                </div>
            </div>

            <!-- Tombol Transaksi Setor & Tarik -->
            <div class="grid grid-cols-2 gap-3 pt-2">
                <button onclick="openDepositModal()" class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow transition text-xs flex items-center justify-center"> Setor Tunai
                </button>
                <button onclick="openWithdrawModal()" class="py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl shadow transition text-xs flex items-center justify-center"> Tarik Tunai
                </button>
            </div>
        </div>

    </div>

    <!-- ======================================================== -->
    <!-- KOLOM KANAN: REKONSILIASI CLOSING & MUTASI HARI INI (7 Kolom) -->
    <!-- ======================================================== -->
    <div class="lg:col-span-7 space-y-6">

        <!-- KARTU REAL-TIME SALDO KAS LACI & MODAL AWAL TELLER -->
        <div class="bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 rounded-3xl shadow-xl p-6 text-white border border-indigo-900/40 relative overflow-hidden">
            <!-- Background Glow Accent -->
            <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-white/10 gap-3">
                <div>
                    <div class="flex items-center space-x-2">
                        <span class="text-xs font-black uppercase tracking-wider text-indigo-300">Kas Laci Loket Anda</span>
                        <span id="cashDrawerStatusBadge" class="text-[10px] px-2.5 py-0.5 rounded-full font-black uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">KAS SIAP</span>
                    </div>
                    <h4 class="text-xs text-slate-400 mt-0.5">Saldo uang fisik di tangan Anda untuk melayani setoran & penarikan</h4>
                </div>
                <div class="flex items-center space-x-2 self-start sm:self-auto">
                    <button onclick="openCashAdvanceModal()" id="btnRequestAdvance" class="px-3.5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 font-black rounded-xl text-xs shadow-md transition flex items-center">
                        <span id="btnRequestAdvanceText">Top Up / Modal Awal</span>
                    </button>
                </div>
            </div>

            <!-- Total Saldo Kas Laci Utama -->
            <div class="pt-4 pb-2">
                <span class="text-xs text-indigo-200 font-semibold block">Total Saldo Kas Fisik di Laci Saat Ini:</span>
                <div class="flex items-baseline space-x-2 mt-1">
                    <span id="tellerDrawerBalanceText" class="text-3xl sm:text-4xl font-black text-emerald-400 tracking-tight">Rp 0</span>
                    <span class="text-xs text-slate-400 font-mono">(Real-time Laci)</span>
                </div>
            </div>

            <!-- Rincian 3 Kolom: Modal Awal Disetujui, Setoran Masuk (+), Penarikan Keluar (-) -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-3">
                <div class="bg-white/5 border border-white/10 rounded-2xl p-3">
                    <span class="text-[11px] text-slate-400 block font-medium flex items-center"> Modal Awal Disetujui
                    </span>
                    <span id="tellerOpeningAdvanceText" class="text-base font-black text-amber-300 mt-1 block">Rp 0</span>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-3">
                    <span class="text-[11px] text-slate-400 block font-medium flex items-center"> Setoran Masuk (+)
                    </span>
                    <span id="tellerTotalDepositText" class="text-base font-black text-emerald-400 mt-1 block">Rp 0</span>
                </div>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-3">
                    <span class="text-[11px] text-slate-400 block font-medium flex items-center"> Penarikan Keluar (-)
                    </span>
                    <span id="tellerTotalWithdrawText" class="text-base font-black text-rose-400 mt-1 block">Rp 0</span>
                </div>
            </div>

            <!-- Banner Info Pengajuan Modal Awal (Pending / Rejected) -->
            <div id="advanceNoticePending" class="hidden mt-3 p-3 bg-amber-500/20 border border-amber-500/40 rounded-2xl text-xs text-amber-200 flex items-start space-x-2.5">
                
                <div class="flex-1">
                    <span class="font-bold text-amber-300 block">Pengajuan Modal Kas Sedang Diproses Admin</span>
                    <p class="text-[11px] text-amber-100/90 mt-0.5"> Permohonan modal awal/top up sebesar <b id="pendingAdvanceAmountText" class="text-white">Rp 0</b> telah dikirim dan menunggu persetujuan (Approve) dari Admin.
                    </p>
                </div>
            </div>

            <div id="advanceNoticeRejected" class="hidden mt-3 p-3 bg-rose-500/20 border border-rose-500/40 rounded-2xl text-xs text-rose-200 flex items-start space-x-2.5">
                
                <div class="flex-1">
                    <span class="font-bold text-rose-300 block">Pengajuan Modal Terakhir Ditolak Admin</span>
                    <p class="text-[11px] text-rose-100/90 mt-0.5"> Alasan: <span id="rejectedAdvanceReasonText" class="italic font-medium text-white">-</span>
                    </p>
                </div>
            </div>
        </div>

        <!-- Banner Peringatan Jika Closing Ditolak Admin (Revisi 1) -->
        <div id="closingRejectionBanner" class="hidden bg-rose-50 border-2 border-rose-300 rounded-3xl p-5 shadow-sm space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 text-rose-700 font-black text-sm">
                    
                    <span>PEMBERITAHUAN: LAPORAN CLOSING KAS ANDA DITOLAK ADMIN</span>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-300">DITOLAK</span>
            </div>
            <p class="text-xs text-rose-900 bg-white/70 p-3 rounded-2xl border border-rose-200">
                <b class="text-rose-950 block mb-0.5">Catatan / Alasan Penolakan dari Admin:</b>
                <span id="bannerRejectionReasonText" class="italic font-medium">Alasan penolakan...</span>
            </p>
            <div class="flex justify-end pt-1">
                <button onclick="openClosingModal()" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow flex items-center"> Periksa & Ajukan Ulang Closing
                </button>
            </div>
        </div>

        <!-- Banner Kas Ditutup (Transaksi Dinonaktifkan Setelah Rekonsiliasi) -->
        <div id="closedNoticeBanner" class="hidden bg-slate-900 border-2 border-slate-700 rounded-3xl p-5 shadow-lg text-white space-y-2">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2 text-amber-400 font-black text-sm">
                    
                    <span>KAS LOKET HARI INI TELAH DITUTUP (REKONSILIASI SELESAI)</span>
                </div>
                <span id="closedStatusBadge" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400 text-slate-900">DIREKONSILIASI</span>
            </div>
            <p class="text-xs text-slate-300 leading-relaxed"> Anda telah menyelesaikan rekonsiliasi kas harian loket. Seluruh transaksi operasional (Setoran, Penarikan, dan Aktivasi Rekening) dinonaktifkan untuk hari ini dan dapat dilanjutkan kembali pada hari kerja berikutnya.
            </p>
        </div>

        <!-- Banner Rekonsiliasi Kas Closing -->
        <div id="closingSummaryBanner" class="bg-gradient-to-r from-amber-500 to-amber-600 rounded-3xl shadow-lg p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <span id="closingBadgeText" class="text-xs font-black uppercase tracking-wider bg-amber-700/50 px-2.5 py-1 rounded-full text-amber-100">Akhir Jam Kerja</span>
                <h4 id="closingBannerTitle" class="text-xl font-black mt-1">Penutupan Kas Harian (Closing)</h4>
                <p id="closingBannerSubtitle" class="text-xs text-amber-100 mt-0.5">Cocokkan saldo digital komputer dengan uang fisik di laci loket</p>
            </div>
            <button id="btnOpenClosingBanner" onclick="openClosingModal()" class="px-5 py-3 bg-white text-amber-900 font-black rounded-2xl shadow hover:bg-amber-50 transition text-xs flex items-center justify-center whitespace-nowrap self-start sm:self-auto cursor-pointer">
                 <span id="btnOpenClosingText">Rekonsiliasi Kas</span>
            </button>
        </div>

        <!-- Tabel Mutasi Transaksi Loket Hari Ini (Revisi 5: Maksimal View Tabel dengan Pagination) -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-black text-slate-800 text-base flex items-center"> Mutasi Transaksi Loket Hari Ini
                    </h3>
                    <p class="text-[11px] text-slate-500">Daftar transaksi yang diproses piket Anda hari ini</p>
                </div>
                <button onclick="loadTodayTransactions()" class="text-xs text-indigo-600 font-bold hover:underline flex items-center"> Segarkan
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

            <!-- Kontrol Pagination Transaksi Hari Ini (Maksimal View 5-8 Data) -->
            <div id="todayTxPagination" class="flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 pt-3 border-t border-slate-100 gap-2"></div>
        </div>

    </div>

</div>

<!-- ======================================================== -->
<!-- MODALS TRANSAKSI & STRUK -->
<!-- ======================================================== -->

<!-- Modal Setor Tunai -->
<div id="modalDeposit" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Transaksi Setor Tunai
        </h3>
        <form onsubmit="submitDeposit(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nominal Setoran (Rp)</label>
                <input type="text" inputmode="numeric" id="depositAmount" required placeholder="Contoh: 50.000" class="format-rupiah w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none font-bold text-lg text-emerald-700">
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
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Transaksi Penarikan Tunai
        </h3>
        <p class="text-xs text-slate-500">Saldo mengendap minimum yang wajib tersisa di rekening adalah <b>Rp 10.000</b>.</p>
        <form onsubmit="submitWithdraw(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nominal Penarikan (Rp)</label>
                <input type="text" inputmode="numeric" id="withdrawAmount" required placeholder="Contoh: 20.000" class="format-rupiah w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none font-bold text-lg text-rose-700">
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-600 uppercase">PIN Keamanan Nasabah (6-Digit)</label>
                    <button type="button" onclick="promptResetPinInWithdraw()" class="text-[11px] font-bold text-amber-600 hover:text-amber-800 hover:underline flex items-center"> Lupa PIN Nasabah?
                    </button>
                </div>
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
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMK" class="h-10 w-10 mx-auto mb-1.5 object-contain">
                <h4 class="font-black text-sm uppercase">BANK MINI SEKOLAH</h4>
                <p class="text-[10px] text-slate-500 font-sans">SMK Negeri 1 Bantul • Bukti Transaksi Kasir</p>
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
            <div class="text-center text-[10px] text-slate-400 pt-2 border-t"> Simpan struk ini sebagai bukti transaksi resmi. Terima kasih!
            </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2 no-print">
            <button type="button" onclick="closeModal('modalReceipt')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow flex items-center"> Cetak Struk
            </button>
        </div>
    </div>
</div>

<!-- Modal Rekonsiliasi Kas Harian Closing -->
<div id="modalClosing" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Rekonsiliasi Kas Harian Teller
        </h3>

        <!-- Kotak Peringatan Jika Closing Ditolak Admin (Revisi 1) -->
        <div id="clRejectionBox" class="hidden p-3.5 bg-rose-50 border border-rose-200 rounded-2xl text-xs text-rose-900 space-y-1">
            <div class="flex items-center font-bold text-rose-700 space-x-1.5">
                
                <span>Closing Terakhir DITOLAK oleh Administrator!</span>
            </div>
            <p class="text-[11px] text-rose-800">
                <b>Alasan Penolakan:</b> <span id="clRejectionText" class="font-medium">-</span>
            </p>
            <p class="text-[10px] text-rose-600">Silakan periksa kembali uang fisik Anda dan ajukan ulang rekonsiliasi kas ini.</p>
        </div>

        <!-- Kotak Status Rekonsiliasi Jika Sudah Disubmit / Disetujui -->
        <div id="clStatusBox" class="hidden p-3.5 bg-indigo-50 border border-indigo-200 rounded-2xl text-xs text-indigo-950 space-y-1">
            <div class="flex items-center justify-between font-bold">
                <span class="flex items-center text-indigo-700"> Status Kas Hari Ini:
                </span>
                <span id="clStatusBadge" class="px-2 py-0.5 rounded-full text-[10px] uppercase font-black bg-indigo-600 text-white">SUBMITTED</span>
            </div>
            <p id="clStatusNote" class="text-[11px] text-indigo-800 leading-relaxed"> Laporan rekonsiliasi kas telah dikirim ke Administrator. Seluruh transaksi dinonaktifkan hingga hari berikutnya.
            </p>
        </div>

        <div class="bg-slate-50 p-4 rounded-2xl border space-y-2 text-xs">
            <div class="flex justify-between"><span>Saldo Awal Kas:</span><b id="clOpening">Rp 0</b></div>
            <div class="flex justify-between text-emerald-700"><span>(+) Total Setoran Hari Ini:</span><b id="clDeposit">Rp 0</b></div>
            <div class="flex justify-between text-rose-700"><span>(-) Total Penarikan Hari Ini:</span><b id="clWithdraw">Rp 0</b></div>
            <div class="border-t pt-2 flex justify-between text-sm font-black text-indigo-900">
                <span>Saldo Akhir Sistem:</span>
                <b id="clSystem">Rp 0</b>
            </div>
        </div>

        <form id="formClosingLoket" onsubmit="submitClosingData(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Hitungan Uang Fisik di Laci Loket (Rp)</label>
                <input type="text" inputmode="numeric" id="clPhysicalInput" required placeholder="Masukkan total uang fisik (misal: 100.000)" class="format-rupiah w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold text-lg text-amber-800">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Catatan Closing (Opsional)</label>
                <textarea id="clNotes" rows="2" placeholder="Catatan jika ada uang rusak atau selisih..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalClosing')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
                <button type="submit" id="btnSubmitClosing" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                    
                    <span id="btnSubmitClosingText">Kirim Rekonsiliasi Kas</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Aktivasi Rekening Anggota Baru di Teller (Revisi 3) -->
<div id="modalActivateMemberTeller" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-base">Aktivasi Rekening Nasabah</h3>
                <p class="text-[11px] text-slate-500">Pendaftaran akun nasabah & setoran perdana di Loket Teller</p>
            </div>
            <button type="button" onclick="closeModal('modalActivateMemberTeller')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <!-- Pencarian Anggota Belum Aktif -->
        <div id="actTellerSearchSection" class="space-y-2">
            <label class="block text-xs font-bold text-slate-700 uppercase">Cari Anggota Belum Aktif</label>
            <div class="flex space-x-1.5">
                <input type="text" id="actTellerSearchQuery" placeholder="Ketik NIS atau Nama Siswa..." class="flex-1 px-3 py-2 rounded-xl border border-slate-300 text-xs focus:ring-2 focus:ring-emerald-500 focus:outline-none">
                <button type="button" onclick="searchUnactivatedMembersTeller()" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs transition">
                    Cari
                </button>
            </div>
            <div id="actTellerSearchResults" class="hidden max-h-36 overflow-y-auto border border-slate-200 rounded-xl divide-y bg-slate-50 text-xs"></div>
        </div>

        <!-- Detail Siswa Terpilih -->
        <div id="actTellerSelectedBox" class="p-3.5 bg-emerald-50/70 border border-emerald-200 rounded-2xl text-xs space-y-1">
            <input type="hidden" id="actTellerMemberId">
            <div class="flex justify-between">
                <span class="text-slate-500">Nama Siswa:</span>
                <span id="actTellerSelectedName" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">NIS:</span>
                <span id="actTellerSelectedNis" class="font-mono font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kelas:</span>
                <span id="actTellerSelectedClass" class="font-bold text-slate-800">-</span>
            </div>
        </div>

        <!-- Form Aktivasi: Setoran Awal, Password Login, PIN Transaksi -->
        <form onsubmit="submitActivateMemberTeller(event)" class="space-y-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Setoran Awal (Wajib Minimal Rp 10.000) *</label>
                <input type="text" inputmode="numeric" id="actTellerInitialDeposit" required placeholder="Minimal 10.000" class="format-rupiah w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs font-bold font-mono">
                <p class="text-[10px] text-amber-600 font-bold mt-0.5"> Syarat wajib: Setoran tunai perdana minimal Rp 10.000.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kata Sandi (Password) Login Siswa *</label>
                <input type="password" id="actTellerPassword" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs">
                <p class="text-[10px] text-slate-400 mt-0.5">Digunakan siswa untuk login ke sistem setelah menjadi nasabah.</p>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">PIN Transaksi (Tepat 6 Digit Angka) *</label>
                <input type="password" id="actTellerPin" required maxlength="6" pattern="[0-9]{6}" placeholder="6 Digit Angka (misal: 123456)" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs font-mono tracking-widest text-center font-bold">
                <p class="text-[10px] text-slate-400 mt-0.5">Digunakan siswa untuk otorisasi penarikan dan transaksi tabungan.</p>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t">
                <button type="button" onclick="closeModal('modalActivateMemberTeller')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnSubmitActivateTeller" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                    
                    <span>Aktifkan Rekening & Setor</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Pengajuan Modal Awal / Top Up Kas Loket Teller -->
<div id="modalCashAdvance" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 id="modalAdvanceTitle" class="font-black text-slate-800 text-base">Pengajuan Modal Kas Loket</h3>
                <p class="text-[11px] text-slate-500">Permohonan modal awal/top up uang fisik ke Admin</p>
            </div>
            <button type="button" onclick="closeModal('modalCashAdvance')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <form id="formCashAdvance" onsubmit="submitCashAdvance(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Jenis Permohonan</label>
                <select id="advType" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs font-bold bg-white">
                    <option value="initial">Modal Awal Pembukaan Loket (Pagi Hari)</option>
                    <option value="topup">Top Up / Tambahan Kas Loket (Saldo Laci Menipis)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Modal yang Diajukan (Rp) *</label>
                <input type="text" inputmode="numeric" id="advAmount" required placeholder="Contoh: 500.000" class="format-rupiah w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none font-bold text-lg text-amber-700">
                <span class="text-[10px] text-slate-400 mt-1 block">Minimal pengajuan Rp 10.000. Uang masuk ke laci setelah di-approve Admin.</span>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Kebutuhan (Opsional)</label>
                <textarea id="advNotes" rows="2" placeholder="Contoh: Butuh pecahan Rp 20.000 dan Rp 50.000 untuk melayani penarikan pagi..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-amber-500 focus:outline-none text-xs"></textarea>
            </div>
            <div class="flex justify-end space-x-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modalCashAdvance')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnSubmitAdvance" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                    
                    <span>Kirim Pengajuan ke Admin</span>
                </button>
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
                        searchCustomer(decodedText);
                        toggleCameraScanner();
                    }
                );
                isScanning = true;
                btn.innerHTML = ' Matikan Kamera Pemindai';
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
            btn.innerHTML = ' Buka Kamera Pemindai QR';
        }
    }

    function searchCustomerManual() {
        const q = document.getElementById('manualSearchInput').value.trim();
        if (q) searchCustomer(q);
    }

    // ================= PENCARIAN PENABUNG =================
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
            document.getElementById('activeCustomerCard').classList.add('hidden');
            if (res && res.data && res.data.is_unactivated_member) {
                const m = res.data.member;
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan: Anggota Belum Aktif!',
                    html: `
                        <div class="text-left p-4 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 space-y-2">
                            <div class="flex items-center space-x-2 text-amber-700 font-bold">
                                
                                <span>Anggota Belum Aktif Sebagai Nasabah</span>
                            </div>
                            <div class="border-t border-amber-200/60 pt-2 space-y-1">
                                <p><strong>Nama Siswa:</strong> ${m.name}</p>
                                <p><strong>NIS:</strong> ${m.nis}</p>
                                <p><strong>Kelas:</strong> ${m.class_name}</p>
                            </div>
                            <div class="p-2.5 bg-white/80 rounded-xl border border-amber-200 text-[11px] text-slate-600"> Anggota ini belum aktif sebagai nasabah. Anda dapat langsung mengaktifkannya sekarang dengan mengisi setoran awal, kata sandi login, dan PIN transaksi.
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: ' Aktivasi Sekarang',
                    cancelButtonText: 'Tutup'
                }).then((result) => {
                    if (result.isConfirmed) {
                        openActivateMemberTellerModal(m);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tidak Ditemukan',
                    text: (res && res.data && res.data.message) || 'Penabung tidak ditemukan'
                });
            }
        }
    }

    // ================= SETOR TUNAI =================
    function openDepositModal() {
        if (window.isTellerClosedToday) {
            Swal.fire({
                icon: 'warning',
                title: 'Kas Loket Telah Ditutup',
                text: 'Rekonsiliasi kas hari ini telah dilakukan. Transaksi loket dinonaktifkan dan baru dapat dilakukan kembali pada hari kerja berikutnya.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        if (!activeCustomer) return;
        document.getElementById('depositAmount').value = '';
        document.getElementById('depositDesc').value = '';
        document.getElementById('modalDeposit').classList.remove('hidden');
    }

    async function submitDeposit(e) {
        e.preventDefault();
        const rawAmount = unformatRupiahNumber(document.getElementById('depositAmount').value);
        if (!rawAmount || rawAmount < 1000) {
            Swal.fire('Nominal Kurang', 'Nominal setoran minimal Rp 1.000!', 'warning');
            return;
        }

        const payload = {
            saver_id: activeCustomer.id,
            customer_id: activeCustomer.id,
            amount: rawAmount,
            description: document.getElementById('depositDesc').value
        };

        const res = await fetchAPI('/teller/transactions/deposit', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            closeModal('modalDeposit');
            searchCustomer(activeCustomer.account_number);
            loadTodayTransactions();
            loadTellerCashBalance();
            showReceiptModal(res.data.data.receipt);
        } else {
            Swal.fire('Gagal Setor', (res && res.data && res.data.message) || 'Gagal memproses transaksi', 'error');
        }
    }

    // ================= TARIK TUNAI =================
    function openWithdrawModal() {
        if (window.isTellerClosedToday) {
            Swal.fire({
                icon: 'warning',
                title: 'Kas Loket Telah Ditutup',
                text: 'Rekonsiliasi kas hari ini telah dilakukan. Transaksi loket dinonaktifkan dan baru dapat dilakukan kembali pada hari kerja berikutnya.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        if (!activeCustomer) return;
        document.getElementById('withdrawAmount').value = '';
        document.getElementById('withdrawPin').value = '';
        document.getElementById('withdrawDesc').value = '';
        document.getElementById('modalWithdraw').classList.remove('hidden');
    }

    // Revisi: Lupa PIN nasabah saat penarikan di Teller direset melalui kode OTP yang dikirimkan ke Gmail
    async function promptResetPinInWithdraw() {
        if (!activeCustomer) {
            Swal.fire('Nasabah Belum Terpilih', 'Cari dan pilih nasabah terlebih dahulu untuk mereset PIN.', 'warning');
            return;
        }

        if (!activeCustomer.email) {
            Swal.fire({
                icon: 'warning',
                title: 'Gmail Nasabah Belum Terdaftar',
                html: `
                    <div class="text-left text-xs space-y-2 p-2">
                        <p>Akun nasabah <b>${activeCustomer.name}</b> belum memiliki alamat Gmail terdaftar di database.</p>
                        <p class="text-slate-500">Reset PIN mandiri via Gmail tidak dapat dilakukan. Nasabah harus login ke portal siswa terlebih dahulu untuk melengkapi alamat email, atau hubungi Administrator.</p>
                    </div>
                `,
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        // Tahap 1: Konfirmasi Pengiriman Kode OTP ke Gmail Terdaftar (Tanpa Input Email)
        const { isConfirmed: sendConfirmed } = await Swal.fire({
            title: 'Reset PIN via Gmail Nasabah',
            html: `
                <div class="text-left text-xs space-y-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                    <div class="space-y-1 pb-2 border-b border-slate-200">
                        <div><b>Nasabah:</b> ${activeCustomer.name} (${activeCustomer.class_name || '-'})</div>
                        <div><b>No. Rekening:</b> <span class="font-mono text-indigo-600 font-bold">${activeCustomer.account_number}</span></div>
                        <div><b>NIS:</b> <span class="font-mono font-bold">${activeCustomer.nis}</span></div>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1"> Alamat Gmail Terdaftar Nasabah
                        </label>
                        <div class="p-2.5 bg-indigo-50 border border-indigo-200 rounded-xl font-mono text-indigo-700 font-bold text-xs flex items-center">
                             ${activeCustomer.email}
                        </div>
                        <p class="text-[11px] text-slate-500 mt-2 leading-relaxed"> Sistem akan mengirimkan <b>Kode OTP Verifikasi 6-Digit</b> langsung ke alamat Gmail di atas untuk otorisasi sebelum PIN direset.
                        </p>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: ' Kirim Kode ke Gmail',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b'
        });

        if (!sendConfirmed) return;

        Swal.fire({
            title: 'Mengirim Kode OTP ke Gmail...',
            text: `Menghubungkan ke server Gmail untuk mengirim kode verifikasi ke [${activeCustomer.email}]...`,
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const sendRes = await fetchAPI('/auth/password/send-code', {
            method: 'POST',
            body: JSON.stringify({
                role: 'nasabah',
                reset_type: 'pin',
                identifier: activeCustomer.account_number
            })
        });

        if (!sendRes || !sendRes.data || !sendRes.data.success) {
            Swal.fire('Gagal Mengirim Kode', (sendRes && sendRes.data && sendRes.data.message) || 'Terjadi kesalahan sistem saat mengirim email OTP.', 'error');
            return;
        }

        const maskedEmail = sendRes.data.masked_email || activeCustomer.email;

        // Tahap 2: Input Kode OTP dari Gmail & PIN Baru
        const { value: verifyData, isConfirmed: verifyConfirmed } = await Swal.fire({
            title: 'Verifikasi Kode & Set PIN Baru',
            html: `
                <div class="text-left text-xs space-y-3 pt-1">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs"> Kode verifikasi telah dikirim ke Gmail <b>${maskedEmail}</b>. Minta nasabah membuka aplikasi Gmail / Kotak Masuk mereka.
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Kode OTP Verifikasi (6-Digit dari Gmail)</label>
                        <input type="text" id="swalOtpCode" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="Contoh: 123456" class="w-full px-3 py-2 border rounded-xl font-mono text-center text-lg font-black tracking-widest text-indigo-900 border-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">PIN Transaksi Baru (6-Digit Angka)</label>
                        <input type="password" id="swalNewPin" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="6 Digit Angka" class="w-full px-3 py-2 border rounded-xl font-mono text-center tracking-widest text-base font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Konfirmasi PIN Baru (6-Digit Angka)</label>
                        <input type="password" id="swalConfirmPin" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="Ulangi 6 Digit Angka" class="w-full px-3 py-2 border rounded-xl font-mono text-center tracking-widest text-base font-bold text-slate-800 focus:outline-none focus:ring-2 focus:ring-amber-500">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: ' Verifikasi & Simpan PIN',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            preConfirm: () => {
                const otp = document.getElementById('swalOtpCode').value.trim();
                const newPin = document.getElementById('swalNewPin').value.trim();
                const confirmPin = document.getElementById('swalConfirmPin').value.trim();

                if (!otp || otp.length !== 6 || !/^\d{6}$/.test(otp)) {
                    Swal.showValidationMessage('Masukkan tepat 6 digit angka kode OTP dari Gmail!');
                    return false;
                }
                if (!newPin || newPin.length !== 6 || !/^\d{6}$/.test(newPin)) {
                    Swal.showValidationMessage('PIN baru harus tepat 6 digit angka!');
                    return false;
                }
                if (newPin !== confirmPin) {
                    Swal.showValidationMessage('Konfirmasi PIN baru tidak sesuai / tidak cocok!');
                    return false;
                }
                return { otp, newPin };
            }
        });

        if (!verifyConfirmed || !verifyData) return;

        Swal.fire({
            title: 'Memverifikasi Kode & Mengubah PIN...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const verifyRes = await fetchAPI('/auth/password/verify-reset', {
            method: 'POST',
            body: JSON.stringify({
                role: 'nasabah',
                reset_type: 'pin',
                identifier: activeCustomer.account_number,
                reset_code: verifyData.otp,
                new_password: verifyData.newPin
            })
        });

        if (verifyRes && verifyRes.data && verifyRes.data.success) {
            document.getElementById('withdrawPin').value = verifyData.newPin;
            Swal.fire({
                icon: 'success',
                title: 'PIN Berhasil Direset via Gmail!',
                text: 'PIN baru berhasil diverifikasi & disimpan. Kolom PIN telah otomatis terisi, silakan klik tombol Otorisasi & Tarik.',
                confirmButtonColor: '#059669'
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Verifikasi Gagal!',
                text: (verifyRes && verifyRes.data && verifyRes.data.message) || 'Kode OTP tidak valid atau sudah kedaluwarsa.',
                confirmButtonColor: '#e11d48'
            });
        }
    }

    async function submitWithdraw(e) {
        e.preventDefault();
        const rawAmount = unformatRupiahNumber(document.getElementById('withdrawAmount').value);
        if (!rawAmount || rawAmount < 1000) {
            Swal.fire('Nominal Kurang', 'Nominal penarikan minimal Rp 1.000!', 'warning');
            return;
        }

        const payload = {
            saver_id: activeCustomer.id,
            customer_id: activeCustomer.id,
            amount: rawAmount,
            security_pin: document.getElementById('withdrawPin').value,
            description: document.getElementById('withdrawDesc').value
        };

        const res = await fetchAPI('/teller/transactions/withdraw', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            closeModal('modalWithdraw');
            searchCustomer(activeCustomer.account_number);
            loadTodayTransactions();
            loadTellerCashBalance();
            showReceiptModal(res.data.data.receipt);
        } else {
            const msg = (res && res.data && res.data.message) || 'Gagal memproses penarikan';
            const isPinError = msg.toLowerCase().includes('pin');
            if (isPinError) {
                Swal.fire({
                    icon: 'error',
                    title: 'Otorisasi PIN Gagal!',
                    text: msg,
                    showCancelButton: true,
                    confirmButtonColor: '#f59e0b',
                    confirmButtonText: ' Reset PIN Nasabah Sekarang',
                    cancelButtonText: 'Coba Lagi'
                }).then(result => {
                    if (result.isConfirmed) {
                        promptResetPinInWithdraw();
                    }
                });
            } else {
                Swal.fire('Penarikan Ditolak!', msg, 'error');
            }
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

    // ================= MUTASI HARI INI & PAGINATION (Revisi 5) =================
    let allTodayTransactions = [];
    let todayTxCurrentPage = 1;
    const todayTxPageSize = 5; // Maksimal view 5 data per halaman agar tidak perlu scroll ke bawah

    async function loadTodayTransactions() {
        const res = await fetchAPI('/teller/transactions/today');
        if (res && res.data && res.data.success) {
            allTodayTransactions = res.data.data || [];
            todayTxCurrentPage = 1;
            renderTodayTransactionsTable();
        }
        checkClosingNotice();
    }

    function renderTodayTransactionsTable() {
        const tbody = document.getElementById('todayTableBody');
        const pagination = document.getElementById('todayTxPagination');
        const total = allTodayTransactions.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">Belum ada transaksi di loket hari ini</td></tr>';
            pagination.innerHTML = '';
            return;
        }

        const totalPages = Math.ceil(total / todayTxPageSize);
        if (todayTxCurrentPage > totalPages) todayTxCurrentPage = totalPages;
        if (todayTxCurrentPage < 1) todayTxCurrentPage = 1;

        const startIdx = (todayTxCurrentPage - 1) * todayTxPageSize;
        const pageItems = allTodayTransactions.slice(startIdx, startIdx + todayTxPageSize);

        tbody.innerHTML = pageItems.map(t => {
            const name = (t.saver && t.saver.name) ? t.saver.name : ((t.customer && t.customer.name) ? t.customer.name : (t.saver_name_snapshot || '-'));
            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-3 font-mono text-xs font-bold text-slate-700">${t.transaction_code}</td>
                    <td class="py-3 px-3 font-semibold text-slate-800">${name}</td>
                    <td class="py-3 px-3 text-center">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase ${t.transaction_type === 'deposit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                            ${t.transaction_type}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-right font-black ${t.transaction_type === 'deposit' ? 'text-emerald-700' : 'text-rose-700'}">
                        ${t.transaction_type === 'deposit' ? '+' : '-'}Rp ${Number(t.amount).toLocaleString('id-ID')}
                    </td>
                </tr>
            `;
        }).join('');

        // Render Pagination Controls (Sebelumnya & Selanjutnya)
        pagination.innerHTML = `
            <div> Menampilkan <b>${startIdx + 1} - ${Math.min(startIdx + todayTxPageSize, total)}</b> dari <b>${total}</b> mutasi
            </div>
            <div class="flex items-center space-x-1">
                <button onclick="goToTodayTxPage(${todayTxCurrentPage - 1})" ${todayTxCurrentPage <= 1 ? 'disabled class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold transition"'}> Sebelumnya
                </button>
                <span class="px-2.5 py-1 text-slate-700 font-bold bg-slate-100 rounded-lg">${todayTxCurrentPage} / ${totalPages}</span>
                <button onclick="goToTodayTxPage(${todayTxCurrentPage + 1})" ${todayTxCurrentPage >= totalPages ? 'disabled class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-300 cursor-not-allowed"' : 'class="px-2.5 py-1 rounded-lg border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold transition"'}> Selanjutnya 
                </button>
            </div>
        `;
    }

    function goToTodayTxPage(page) {
        todayTxCurrentPage = page;
        renderTodayTransactionsTable();
    }

    // ================= CLOSING REKONSILIASI KAS & NOTIFIKASI TOLAK (Revisi 1) =================
    window.isTellerClosedToday = false;

    async function checkClosingNotice() {
        const res = await fetchAPI('/teller/closing/calculation');
        if (res && res.data && res.data.success) {
            const closing = res.data.data.existing_closing;
            const isClosed = res.data.data.is_closed;
            window.isTellerClosedToday = !!isClosed;

            const bannerRejection = document.getElementById('closingRejectionBanner');
            const bannerClosed = document.getElementById('closedNoticeBanner');
            const btnOpenText = document.getElementById('btnOpenClosingText');

            if (closing && closing.status === 'rejected') {
                window.isTellerClosedToday = false;
                const reason = closing.rejection_reason || closing.notes || 'Periksa kembali kesesuaian fisik kas dengan sistem.';
                document.getElementById('bannerRejectionReasonText').innerText = `"${reason}"`;
                bannerRejection.classList.remove('hidden');
                bannerClosed.classList.add('hidden');
                if (btnOpenText) btnOpenText.innerText = 'Ajukan Ulang Rekonsiliasi';
            } else if (isClosed) {
                bannerRejection.classList.add('hidden');
                bannerClosed.classList.remove('hidden');
                const st = (closing.status === 'approved') ? 'DISETUJUI' : (closing.status === 'dispute' ? 'SELISIH (DISPUTE)' : 'MENUNGGU VERIFIKASI');
                document.getElementById('closedStatusBadge').innerText = st;
                if (btnOpenText) btnOpenText.innerText = 'Status Rekonsiliasi (Selesai)';
            } else {
                bannerRejection.classList.add('hidden');
                bannerClosed.classList.add('hidden');
                if (btnOpenText) btnOpenText.innerText = 'Rekonsiliasi Kas';
            }
        }
    }

    async function openClosingModal() {
        const btn = document.getElementById('btnOpenClosingBanner');
        if (btn) btn.disabled = true;

        const res = await fetchAPI('/teller/closing/calculation');
        if (btn) btn.disabled = false;

        if (res && res.data && res.data.success) {
            const d = res.data.data;
            document.getElementById('clOpening').innerText = `Rp ${Number(d.opening_balance).toLocaleString('id-ID')}`;
            document.getElementById('clDeposit').innerText = `Rp ${Number(d.total_deposit).toLocaleString('id-ID')}`;
            document.getElementById('clWithdraw').innerText = `Rp ${Number(d.total_withdraw).toLocaleString('id-ID')}`;
            document.getElementById('clSystem').innerText = `Rp ${Number(d.system_balance).toLocaleString('id-ID')}`;
            
            if (d.existing_closing && d.existing_closing.physical_balance) {
                document.getElementById('clPhysicalInput').value = formatRupiahNumber(d.existing_closing.physical_balance);
            } else {
                document.getElementById('clPhysicalInput').value = formatRupiahNumber(Math.max(0, d.system_balance));
            }

            const rejectionBox = document.getElementById('clRejectionBox');
            const statusBox = document.getElementById('clStatusBox');
            const submitBtn = document.getElementById('btnSubmitClosing');

            if (d.existing_closing && d.existing_closing.status === 'rejected') {
                const reason = d.existing_closing.rejection_reason || d.existing_closing.notes || 'Ditolak oleh Administrator.';
                document.getElementById('clRejectionText').innerText = reason;
                rejectionBox.classList.remove('hidden');
                statusBox.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            } else if (d.is_closed) {
                rejectionBox.classList.add('hidden');
                statusBox.classList.remove('hidden');
                const st = (d.existing_closing.status === 'approved') ? 'DISETUJUI' : (d.existing_closing.status === 'dispute' ? 'DISPUTE' : 'SUBMITTED');
                document.getElementById('clStatusBadge').innerText = st;
                if (d.existing_closing.status === 'approved') {
                    document.getElementById('clStatusNote').innerText = 'Rekonsiliasi kas hari ini telah disetujui oleh Administrator. Data kas telah dikunci.';
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                } else {
                    document.getElementById('clStatusNote').innerText = 'Laporan rekonsiliasi kas telah dikirim ke Administrator. Anda tetap dapat memperbarui jika ada perbaikan fisik.';
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                }
            } else {
                rejectionBox.classList.add('hidden');
                statusBox.classList.add('hidden');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }

            document.getElementById('modalClosing').classList.remove('hidden');
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Membuka Rekonsiliasi',
                text: (res && res.data && res.data.message) || 'Tidak dapat memuat data kalkulasi kas loket.'
            });
        }
    }

    async function submitClosingData(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmitClosing');
        const rawPhysical = unformatRupiahNumber(document.getElementById('clPhysicalInput').value);

        if (isNaN(rawPhysical) || rawPhysical < 0) {
            Swal.fire('Input Tidak Valid', 'Masukkan total uang fisik dengan benar (minimal Rp 0).', 'warning');
            return;
        }

        if (btn) {
            btn.disabled = true;
            btn.innerHTML = ' Mengirim Rekonsiliasi...';
        }

        const payload = {
            physical_balance: rawPhysical,
            notes: document.getElementById('clNotes').value
        };

        try {
            const res = await fetchAPI('/teller/closing/submit', { method: 'POST', body: JSON.stringify(payload) });
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = ' Kirim Rekonsiliasi Kas';
            }

            if (res && res.data && res.data.success) {
                closeModal('modalClosing');
                window.isTellerClosedToday = true;
                checkClosingNotice();
                Swal.fire({
                    icon: res.data.data.status === 'dispute' ? 'warning' : 'success',
                    title: res.data.data.status === 'dispute' ? 'Terdeteksi Selisih (Dispute)' : 'Closing Terkirim (Balance)',
                    text: res.data.message
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Rekonsiliasi Kas',
                    text: (res && res.data && (res.data.message || (res.data.errors ? Object.values(res.data.errors)[0][0] : null))) || 'Terjadi kesalahan sistem saat memproses rekonsiliasi kas.'
                });
            }
        } catch (err) {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = ' Kirim Rekonsiliasi Kas';
            }
            Swal.fire('Kesalahan Jaringan', err.message, 'error');
        }
    }

    // ================= 7. AKTIVASI ANGGOTA BARU DI TELLER =================
    function openActivateMemberTellerModal(memberData = null) {
        if (window.isTellerClosedToday) {
            Swal.fire({
                icon: 'warning',
                title: 'Kas Loket Telah Ditutup',
                text: 'Rekonsiliasi kas hari ini telah dilakukan. Aktivasi rekening penabung dinonaktifkan dan baru dapat dilakukan kembali pada hari kerja berikutnya.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }
        const queryInput = document.getElementById('actTellerSearchQuery');
        const resultsBox = document.getElementById('actTellerSearchResults');
        const memberIdInput = document.getElementById('actTellerMemberId');
        const nameText = document.getElementById('actTellerSelectedName');
        const nisText = document.getElementById('actTellerSelectedNis');
        const classText = document.getElementById('actTellerSelectedClass');
        const depositInput = document.getElementById('actTellerInitialDeposit');
        const passInput = document.getElementById('actTellerPassword');
        const pinInput = document.getElementById('actTellerPin');

        if (depositInput) depositInput.value = '10.000';
        if (passInput) passInput.value = '';
        if (pinInput) pinInput.value = '';
        if (resultsBox) resultsBox.classList.add('hidden');

        if (memberData && memberData.id) {
            memberIdInput.value = memberData.id;
            nameText.innerText = memberData.name || '-';
            nisText.innerText = memberData.nis || '-';
            classText.innerText = memberData.class_name || '-';
            if (queryInput) queryInput.value = memberData.nis || '';
        } else {
            memberIdInput.value = '';
            nameText.innerText = 'Pilih anggota terlebih dahulu...';
            nisText.innerText = '-';
            classText.innerText = '-';
            if (queryInput) queryInput.value = '';
        }

        document.getElementById('modalActivateMemberTeller').classList.remove('hidden');
    }

    async function searchUnactivatedMembersTeller() {
        const q = (document.getElementById('actTellerSearchQuery').value || '').trim();
        const resultsBox = document.getElementById('actTellerSearchResults');
        resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400 text-xs">Mencari anggota belum aktif...</div>';
        resultsBox.classList.remove('hidden');

        const res = await fetchAPI(`/teller/members/unactivated?query=${encodeURIComponent(q)}`);
        if (res && res.data && res.data.success) {
            const list = res.data.data || [];
            if (list.length === 0) {
                resultsBox.innerHTML = '<div class="p-3 text-center text-slate-400 text-xs">Tidak ditemukan anggota yang belum aktif.</div>';
                return;
            }

            resultsBox.innerHTML = list.map(m => {
                const safeName = (m.name || '').replace(/'/g, "\\'");
                const safeClass = (m.class_name || '').replace(/'/g, "\\'");
                return `
                    <div onclick="selectUnactivatedMemberForActivation(${m.id}, '${safeName}', '${m.nis}', '${safeClass}')" class="p-2.5 hover:bg-emerald-50 cursor-pointer transition flex items-center justify-between">
                        <div>
                            <span class="font-bold text-slate-800 block">${m.name}</span>
                            <span class="text-[10px] text-slate-500 font-mono">NIS: ${m.nis} | Kelas: ${m.class_name}</span>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full">Pilih</span>
                    </div>
                `;
            }).join('');
        } else {
            resultsBox.innerHTML = '<div class="p-3 text-center text-rose-500 text-xs">Gagal mengambil data anggota.</div>';
        }
    }

    function selectUnactivatedMemberForActivation(id, name, nis, className) {
        document.getElementById('actTellerMemberId').value = id;
        document.getElementById('actTellerSelectedName').innerText = name;
        document.getElementById('actTellerSelectedNis').innerText = nis;
        document.getElementById('actTellerSelectedClass').innerText = className;
        document.getElementById('actTellerSearchResults').classList.add('hidden');
    }

    async function submitActivateMemberTeller(e) {
        e.preventDefault();
        const memberId = document.getElementById('actTellerMemberId').value;
        if (!memberId) {
            Swal.fire('Peringatan', 'Harap pilih data anggota yang akan diaktivasi terlebih dahulu!', 'warning');
            return;
        }

        const depositRaw = document.getElementById('actTellerInitialDeposit').value;
        const initial_deposit = unformatRupiahNumber(depositRaw) || 0;
        const password = document.getElementById('actTellerPassword').value.trim();
        const security_pin = document.getElementById('actTellerPin').value.trim();

        if (initial_deposit < 10000) {
            Swal.fire({
                icon: 'warning',
                title: 'Setoran Awal Minimal Rp 10.000',
                text: 'Aktivasi nasabah mewajibkan setoran perdana minimal Rp 10.000.',
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        if (password.length < 6) {
            Swal.fire('Kata Sandi Kurang', 'Kata sandi (password) login siswa minimal 6 karakter!', 'warning');
            return;
        }

        if (!/^\d{6}$/.test(security_pin)) {
            Swal.fire('PIN Tidak Valid', 'PIN transaksi harus berupa tepat 6 digit angka!', 'warning');
            return;
        }

        const btn = document.getElementById('btnSubmitActivateTeller');
        btn.disabled = true;
        btn.innerHTML = ' Mengaktifkan...';

        const payload = {
            member_id: memberId,
            amount: Number(initial_deposit),
            password: password,
            security_pin: security_pin,
        };

        const res = await fetchAPI('/teller/members/activate', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        btn.disabled = false;
        btn.innerHTML = ' <span>Aktifkan Rekening & Setor</span>';

        if (res && res.data && res.data.success) {
            closeModal('modalActivateMemberTeller');
            const data = res.data.data;
            const saver = data.saver;

            Swal.fire({
                icon: 'success',
                title: 'Rekening Berhasil Diaktifkan!',
                html: `
                    <div class="text-left space-y-2 text-xs">
                        <p class="font-medium text-slate-700">${res.data.message}</p>
                        <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl space-y-1 font-mono text-emerald-900">
                            <div><b>No. Rekening:</b> ${saver.account_number}</div>
                            <div><b>Nama Nasabah:</b> ${saver.name}</div>
                            <div><b>Setoran Awal:</b> Rp ${Number(initial_deposit).toLocaleString('id-ID')}</div>
                            <div><b>Status Akun:</b> Siswa sekarang dapat login ke sistem</div>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#10b981'
            });

            loadTodayTransactions();
            loadTellerCashBalance();
            if (saver && saver.account_number) {
                searchCustomer(saver.account_number);
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Aktivasi Rekening',
                text: (res && res.data && res.data.message) || 'Terjadi kesalahan sistem.'
            });
        }
    }

    // ================= SALDO MODAL AWAL & KAS LACI TELLER =================
    let currentTellerCash = {
        opening_balance: 0,
        total_deposit: 0,
        total_withdraw: 0,
        current_balance: 0,
        has_approved: false,
        pending_request: null
    };

    async function loadTellerCashBalance() {
        const res = await fetchAPI('/teller/cash-advances/today');
        if (res && res.data && res.data.success) {
            const data = res.data.data;
            currentTellerCash = data;

            // Update Teks Total Saldo Laci
            const balanceEl = document.getElementById('tellerDrawerBalanceText');
            const formattedBal = `Rp ${Number(data.current_balance).toLocaleString('id-ID')}`;
            balanceEl.innerText = formattedBal;

            // Indikator Warna Status Laci
            const badgeEl = document.getElementById('cashDrawerStatusBadge');
            if (data.current_balance > 500000) {
                balanceEl.className = 'text-3xl sm:text-4xl font-black text-emerald-400 tracking-tight';
                badgeEl.className = 'text-[10px] px-2.5 py-0.5 rounded-full font-black uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/30';
                badgeEl.innerText = 'KAS AMAN';
            } else if (data.current_balance > 0) {
                balanceEl.className = 'text-3xl sm:text-4xl font-black text-amber-300 tracking-tight';
                badgeEl.className = 'text-[10px] px-2.5 py-0.5 rounded-full font-black uppercase bg-amber-500/20 text-amber-300 border border-amber-500/30';
                badgeEl.innerText = 'KAS MENIPIS';
            } else {
                balanceEl.className = 'text-3xl sm:text-4xl font-black text-rose-400 tracking-tight';
                badgeEl.className = 'text-[10px] px-2.5 py-0.5 rounded-full font-black uppercase bg-rose-500/20 text-rose-300 border border-rose-500/30';
                badgeEl.innerText = 'KAS KOSONG';
            }

            // Update Kolom Rincian
            document.getElementById('tellerOpeningAdvanceText').innerText = `Rp ${Number(data.opening_balance).toLocaleString('id-ID')}`;
            document.getElementById('tellerTotalDepositText').innerText = `+Rp ${Number(data.total_deposit).toLocaleString('id-ID')}`;
            document.getElementById('tellerTotalWithdrawText').innerText = `-Rp ${Number(data.total_withdraw).toLocaleString('id-ID')}`;

            // Tombol Ajukan Modal
            const btnAdv = document.getElementById('btnRequestAdvanceText');
            if (data.has_approved) {
                btnAdv.innerText = 'Top Up Kas Loket';
            } else {
                btnAdv.innerText = 'Ajukan Modal Awal';
            }

            // Banner Pending Request
            const pendingBanner = document.getElementById('advanceNoticePending');
            if (data.pending_request) {
                pendingBanner.classList.remove('hidden');
                document.getElementById('pendingAdvanceAmountText').innerText = `Rp ${Number(data.pending_request.amount).toLocaleString('id-ID')}`;
            } else {
                pendingBanner.classList.add('hidden');
            }

            // Banner Rejected Request
            const rejectedBanner = document.getElementById('advanceNoticeRejected');
            const lastReq = data.requests_today && data.requests_today[0];
            if (lastReq && lastReq.status === 'rejected' && !data.pending_request) {
                rejectedBanner.classList.remove('hidden');
                document.getElementById('rejectedAdvanceReasonText').innerText = lastReq.rejection_reason || 'Tidak ada alasan khusus dari Admin';
            } else {
                rejectedBanner.classList.add('hidden');
            }
        }
    }

    function openCashAdvanceModal() {
        if (window.isTellerClosedToday) {
            Swal.fire('Kas Telah Ditutup', 'Kas loket hari ini telah ditutup (Closing). Tidak dapat mengajukan modal lagi hari ini.', 'warning');
            return;
        }

        if (currentTellerCash.pending_request) {
            Swal.fire({
                icon: 'info',
                title: 'Pengajuan Masih Menunggu Admin',
                text: `Anda masih memiliki pengajuan saldo modal sebesar Rp ${Number(currentTellerCash.pending_request.amount).toLocaleString('id-ID')} yang belum diproses oleh Admin.`,
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        document.getElementById('advAmount').value = '';
        document.getElementById('advNotes').value = '';
        
        // Pilih tipe otomatis
        const typeSelect = document.getElementById('advType');
        if (currentTellerCash.has_approved) {
            typeSelect.value = 'topup';
            document.getElementById('modalAdvanceTitle').innerText = 'Top Up Saldo Kas Loket';
        } else {
            typeSelect.value = 'initial';
            document.getElementById('modalAdvanceTitle').innerText = 'Pengajuan Modal Awal Loket';
        }

        document.getElementById('modalCashAdvance').classList.remove('hidden');
    }

    async function submitCashAdvance(e) {
        e.preventDefault();
        const rawAmount = unformatRupiahNumber(document.getElementById('advAmount').value);
        if (!rawAmount || rawAmount < 10000) {
            Swal.fire('Nominal Kurang', 'Pengajuan modal awal/top up minimal Rp 10.000!', 'warning');
            return;
        }

        const btn = document.getElementById('btnSubmitAdvance');
        btn.disabled = true;
        btn.innerHTML = ' Mengirim...';

        const payload = {
            amount: rawAmount,
            type: document.getElementById('advType').value,
            notes: document.getElementById('advNotes').value.trim()
        };

        const res = await fetchAPI('/teller/cash-advances', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        btn.disabled = false;
        btn.innerHTML = ' <span>Kirim Pengajuan ke Admin</span>';

        if (res && res.data && res.data.success) {
            closeModal('modalCashAdvance');
            Swal.fire({
                icon: 'success',
                title: 'Pengajuan Berhasil Dikirim!',
                text: res.data.message,
                confirmButtonColor: '#10b981'
            });
            loadTellerCashBalance();
        } else {
            Swal.fire('Gagal Mengajukan Modal', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem.', 'error');
        }
    }

    // =========================================================
    // PENGATURAN STATUS OPERASIONAL PELAYANAN LOKET TELLER
    // =========================================================
    async function loadTellerServiceStatus() {
        try {
            const res = await fetch('/api/public/teller-status');
            const data = await res.json();
            if (data && data.success && data.data) {
                renderTellerStatusUI(data.data);
            }
        } catch (e) {
            console.error('Gagal memuat status loket:', e);
        }
    }

    // Jam Real-Time On-Time khusus Status Layanan Loket (WIB / Asia-Jakarta)
    function updateTellerStatusClock() {
        const updated = document.getElementById('tellerStatusUpdatedText');
        if (!updated) return;
        const now = new Date();
        try {
            const dateStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' });
            const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false, timeZone: 'Asia/Jakarta' }).replace(/\./g, ':');
            updated.innerText = `(${dateStr}, ${timeStr} WIB)`;
        } catch (e) {
            const pad = n => String(n).padStart(2, '0');
            updated.innerText = `(${pad(now.getDate())} Nov ${now.getFullYear()}, ${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())} WIB)`;
        }
    }
    setInterval(updateTellerStatusClock, 1000);
    updateTellerStatusClock();

    function renderTellerStatusUI(info) {
        const status = (info.status || 'buka').toLowerCase();
        const badge = document.getElementById('tellerCurrentStatusBadge');
        const dot = document.getElementById('tellerStatusDot');
        const desc = document.getElementById('tellerStatusDescText');
        
        updateTellerStatusClock();

        const btnBuka = document.getElementById('btnTellerStatusBuka');
        const btnIstirahat = document.getElementById('btnTellerStatusIstirahat');
        const btnTutup = document.getElementById('btnTellerStatusTutup');

        [btnBuka, btnIstirahat, btnTutup].forEach(b => {
            if (!b) return;
            b.className = 'px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 text-slate-600 hover:bg-slate-200/60';
        });

        if (status === 'buka') {
            if (badge) {
                badge.innerText = 'BUKA';
                badge.className = 'text-xs font-black px-3 py-1 rounded-full uppercase bg-emerald-100 text-emerald-800 border border-emerald-300';
            }
            if (dot) dot.className = 'w-4 h-4 rounded-full bg-emerald-500 animate-pulse ring-4 ring-emerald-100 flex-shrink-0';
            if (desc) desc.innerText = 'Loket Buka - Siap melayani setoran & penarikan nasabah (terhubung langsung ke mobile nasabah).';
            if (btnBuka) btnBuka.className = 'px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 bg-emerald-600 text-white shadow ring-2 ring-emerald-300';
        } else if (status === 'istirahat') {
            if (badge) {
                badge.innerText = 'ISTIRAHAT';
                badge.className = 'text-xs font-black px-3 py-1 rounded-full uppercase bg-amber-100 text-amber-800 border border-amber-300';
            }
            if (dot) dot.className = 'w-4 h-4 rounded-full bg-amber-500 ring-4 ring-amber-100 flex-shrink-0';
            if (desc) desc.innerText = 'Loket Sedang Istirahat - Pelayanan di loket sedang dijeda sementara.';
            if (btnIstirahat) btnIstirahat.className = 'px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 bg-amber-500 text-slate-950 shadow ring-2 ring-amber-300';
        } else if (status === 'tutup') {
            if (badge) {
                badge.innerText = 'TUTUP';
                badge.className = 'text-xs font-black px-3 py-1 rounded-full uppercase bg-rose-100 text-rose-800 border border-rose-300';
            }
            if (dot) dot.className = 'w-4 h-4 rounded-full bg-rose-500 ring-4 ring-rose-100 flex-shrink-0';
            if (desc) desc.innerText = 'Loket Tutup - Pelayanan loket teller hari ini telah ditutup.';
            if (btnTutup) btnTutup.className = 'px-3.5 py-2 rounded-xl text-xs font-black transition flex items-center space-x-1.5 bg-rose-600 text-white shadow ring-2 ring-rose-300';
        }
    }

    async function setTellerServiceStatus(newStatus) {
        Swal.fire({
            title: 'Memperbarui Status...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const res = await fetchAPI('/teller/service-status', {
            method: 'POST',
            body: JSON.stringify({ status: newStatus })
        });

        if (res && res.data && res.data.success) {
            renderTellerStatusUI(res.data.data);
            Swal.fire({
                icon: 'success',
                title: 'Status Berhasil Diperbarui!',
                text: res.data.message,
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            Swal.fire('Gagal Mengubah Status', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem.', 'error');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        ['activateAmountInput', 'depositAmount', 'withdrawAmount', 'clPhysicalInput', 'actTellerInitialDeposit', 'advAmount'].forEach(id => {
            attachRupiahMask(document.getElementById(id));
        });
        loadTodayTransactions();
        loadTellerCashBalance();
        loadTellerServiceStatus();
    });
</script>
@endsection