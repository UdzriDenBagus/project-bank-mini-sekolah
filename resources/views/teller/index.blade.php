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
                <div class="text-[10px] text-indigo-300/80 bg-white/5 p-2 rounded-xl flex items-center justify-between">
                    <span>Maks. Tarik Langsung: <b class="text-amber-300">Rp 1.000.000</b></span>
                    <span>> Rp 1 Jt: <b class="text-indigo-200">Wajib Janji H-1</b></span>
                </div>
            </div>

            <!-- Alert Khusus Janji Penarikan Nasabah (Jika Ada yang Aktif atau Pending) -->
            <div id="custAppointmentAlert" class="hidden p-3 rounded-2xl text-xs space-y-1.5 border">
                <div class="flex items-center justify-between">
                    <span id="custAppointmentBadge" class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase">JANJI TARIK</span>
                    <span id="custAppointmentAmount" class="font-black text-xs">Rp 0</span>
                </div>
                <p id="custAppointmentDesc" class="text-[11px] leading-relaxed"></p>
                <div id="custAppointmentAction" class="pt-1 flex justify-end"></div>
            </div>

            <!-- Tombol Transaksi Setor & Tarik -->
            <div class="space-y-2 pt-2">
                <div class="grid grid-cols-2 gap-3">
                    <button onclick="openDepositModal()" class="py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl shadow transition text-xs flex items-center justify-center"> Setor Tunai
                    </button>
                    <button onclick="openWithdrawModal()" class="py-3 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-2xl shadow transition text-xs flex items-center justify-center"> Tarik Tunai
                    </button>
                </div>
                <button onclick="openWithdrawalAppointmentModal()" type="button" class="w-full py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold rounded-2xl text-xs transition border border-indigo-200 flex items-center justify-center space-x-1.5 shadow-xs">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Buat Janji Tarik Tunai (> Rp 1 Jt / H-1)</span>
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
                <div class="flex items-center space-x-2 self-start sm:self-auto flex-wrap gap-y-2">
                    <button onclick="openAppointmentsReviewModal()" id="btnReviewAppointments" class="relative px-3.5 py-2 bg-indigo-600/80 hover:bg-indigo-600 text-white font-bold rounded-xl text-xs shadow-md transition flex items-center space-x-1.5 border border-indigo-400/40" title="Tinjau & ACC Perjanjian Penarikan Dana Besar Nasabah">
                        <svg class="w-3.5 h-3.5 text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        <span>Otorisasi Janji Tarik</span>
                        <span id="badgePendingApt" class="hidden px-1.5 py-0.5 rounded-full bg-rose-500 text-white text-[9px] font-black">0</span>
                    </button>
                    <button onclick="openCashAdvanceHistoryModal()" id="btnAdvanceHistory" class="px-3.5 py-2 bg-white/10 hover:bg-white/20 text-white font-bold rounded-xl text-xs shadow-sm transition flex items-center space-x-1.5 border border-white/20" title="Lihat riwayat permohonan modal awal & top up kas Anda">
                        <svg class="w-3.5 h-3.5 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Riwayat Modal</span>
                    </button>
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

<!-- Modal Tarik Tunai (Verifikasi PIN, Batas Saldo Mengendap & Aturan H-1) -->
<div id="modalWithdraw" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-black text-slate-800 text-lg flex items-center space-x-2">
                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Transaksi Penarikan Tunai</span>
            </h3>
            <button type="button" onclick="closeModal('modalWithdraw')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <!-- Ketentuan Penarikan Box -->
        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-[11px] text-slate-600 space-y-1">
            <div class="flex justify-between">
                <span>• Saldo Mengendap Wajib:</span>
                <b class="text-slate-900">Rp 10.000</b>
            </div>
            <div class="flex justify-between">
                <span>• Penarikan Langsung Loket:</span>
                <b class="text-emerald-700">Maks. Rp 1.000.000</b>
            </div>
            <div class="flex justify-between text-amber-800 font-medium">
                <span>• Penarikan di atas Rp 1.000.000:</span>
                <b>Wajib Janji H-1</b>
            </div>
        </div>

        <form onsubmit="submitWithdraw(event)" class="space-y-3 text-sm">
            <div>
                <div class="flex justify-between items-center mb-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase">Nominal Penarikan (Rp) *</label>
                    <span id="withdrawHintText" class="text-[11px] text-slate-500 font-medium">Maks. langsung: Rp 0</span>
                </div>
                <input type="text" inputmode="numeric" id="withdrawAmount" required oninput="handleWithdrawAmountInput(this.value)" placeholder="Contoh: 20.000" class="format-rupiah w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none font-bold text-lg text-rose-700">
            </div>

            <!-- Warning Box Penarikan Dana Besar (> Rp 1.000.000) -->
            <div id="largeWithdrawAlert" class="hidden p-3.5 bg-amber-50 border border-amber-300 rounded-2xl text-xs space-y-2.5">
                <div class="flex items-center space-x-2 text-amber-900 font-bold">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Penarikan Dana Besar (> Rp 1.000.000)</span>
                </div>
                <p class="text-[11px] text-amber-800 leading-relaxed">
                    Sesuai SOP, penarikan di atas Rp 1.000.000 <b>wajib melakukan perjanjian dengan pihak teller minimal 1 hari sebelum penarikan (H-1)</b>.
                </p>

                <!-- Status Perjanjian Sistem Terdeteksi -->
                <div id="appointmentDetectedBox" class="hidden p-2.5 bg-emerald-100 border border-emerald-300 rounded-xl text-emerald-900 text-xs space-y-0.5">
                    <div class="font-bold flex items-center space-x-1.5 text-emerald-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span>Perjanjian H-1 Terverifikasi!</span>
                    </div>
                    <p id="appointmentDetectedText" class="text-[11px] text-emerald-700 mt-0.5">-</p>
                    <input type="hidden" id="verifiedAppointmentId" value="">
                </div>

                <!-- Konfirmasi Perjanjian Manual Oleh Teller -->
                <div id="appointmentManualConfirmBox" class="space-y-2 pt-1 border-t border-amber-200">
                    <label class="flex items-start space-x-2 cursor-pointer">
                        <input type="checkbox" id="checkManualAppointment" onchange="toggleManualAppointmentDate()" class="mt-0.5 rounded text-amber-600 focus:ring-amber-500">
                        <span class="text-[11px] text-amber-900 font-semibold leading-tight">
                            Konfirmasi: Nasabah telah membuat perjanjian dengan teller minimal 1 hari sebelumnya (H-1)
                        </span>
                    </label>
                    <div id="manualAppointmentDateField" class="hidden pl-5 space-y-1">
                        <label class="block text-[10px] font-bold text-amber-800 uppercase">Tanggal Perjanjian Disepakati (Maks. Kemarin)</label>
                        <input type="date" id="manualAppointmentDate" class="px-2.5 py-1.5 bg-white border border-amber-300 rounded-xl text-xs font-bold text-slate-700">
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-xs font-bold text-slate-700 uppercase">PIN Keamanan Nasabah (6-Digit) *</label>
                    <button type="button" onclick="promptResetPinInWithdraw()" class="text-[11px] font-bold text-amber-600 hover:text-amber-800 hover:underline flex items-center">
                        Lupa PIN Nasabah?
                    </button>
                </div>
                <input type="password" id="withdrawPin" required maxlength="6" minlength="6" placeholder="••••••" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none font-mono text-center tracking-widest text-lg font-bold">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan (Opsional)</label>
                <input type="text" id="withdrawDesc" placeholder="Contoh: Penarikan uang saku / keperluan sekolah" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-rose-500 focus:outline-none text-xs">
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t">
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

<!-- Modal Riwayat Pengajuan Modal Kas Loket -->
<div id="modalAdvanceHistory" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-base flex items-center space-x-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Riwayat Pengajuan Modal Kas Loket</span>
                </h3>
                <p class="text-[11px] text-slate-500">Daftar permohonan modal awal dan top up kas loket Anda beserta status persetujuan Admin</p>
            </div>
            <button type="button" onclick="closeModal('modalAdvanceHistory')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <div class="overflow-y-auto flex-1 pr-1">
            <div id="advanceHistoryLoading" class="py-12 text-center text-slate-400 text-xs">
                <svg class="animate-spin h-6 w-6 text-amber-600 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                Memuat riwayat pengajuan modal...
            </div>
            <div id="advanceHistoryEmpty" class="py-12 text-center text-slate-400 text-xs hidden">
                <div class="w-12 h-12 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <p class="font-bold text-slate-600">Belum Ada Riwayat Pengajuan</p>
                <p class="text-[11px] text-slate-400 mt-0.5">Semua permohonan modal awal dan top up kas Anda akan tercatat di sini.</p>
            </div>
            <div id="advanceHistoryList" class="space-y-3 hidden">
                <!-- Data akan dimuat via JavaScript -->
            </div>
        </div>

        <div class="flex justify-between items-center pt-3 border-t">
            <span class="text-[11px] text-slate-400">Menampilkan 50 pengajuan terakhir Anda</span>
            <button type="button" onclick="closeModal('modalAdvanceHistory')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal Buat Perjanjian Penarikan Dana Besar (> Rp 1 Jt, Minimal H-1) -->
<div id="modalWithdrawalAppointment" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-base flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Perjanjian Tarik Tunai Dana Besar</span>
                </h3>
                <p class="text-[11px] text-slate-500">Pendaftaran janji penarikan dana > Rp 1 Juta minimal 1 hari sebelumnya (H-1)</p>
            </div>
            <button type="button" onclick="closeModal('modalWithdrawalAppointment')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <form id="formWithdrawalAppointment" onsubmit="submitWithdrawalAppointment(event)" class="space-y-3 text-sm">
            <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-2xl space-y-1">
                <span class="text-[11px] text-indigo-700 font-bold uppercase block">Nasabah Terpilih:</span>
                <p id="aptCustName" class="font-black text-slate-900 text-sm">-</p>
                <div class="flex justify-between text-xs text-indigo-900 pt-1 border-t border-indigo-200">
                    <span>Saldo Saat Ini: <b id="aptCustBalance">Rp 0</b></span>
                    <span>Tersedia (Sisa 10k): <b id="aptCustAvailable" class="text-emerald-700">Rp 0</b></span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Rencana Tanggal Penarikan *</label>
                <input type="date" id="aptWithdrawalDate" required class="w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs font-bold bg-white text-slate-800">
                <span class="text-[10px] text-slate-400 mt-1 block">Wajib minimal 1 hari ke depan (H-1 dari hari ini).</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nominal Rencana Penarikan (Rp) *</label>
                <input type="text" inputmode="numeric" id="aptAmount" required placeholder="Contoh: 1.500.000" class="format-rupiah w-full px-3 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-bold text-lg text-indigo-700">
                <span class="text-[10px] text-slate-400 mt-1 block">Khusus penarikan di atas Rp 1.000.000. Saldo mengendap Rp 10.000 tetap terjaga.</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Kebutuhan Nasabah (Opsional)</label>
                <textarea id="aptNotes" rows="2" placeholder="Contoh: Kebutuhan biaya daftar ulang / studi tur / beli perlengkapan..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs"></textarea>
            </div>

            <div class="flex justify-end space-x-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modalWithdrawalAppointment')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnSubmitApt" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow flex items-center space-x-1.5">
                    <span>Simpan Perjanjian H-1</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Otorisasi & Review Perjanjian Penarikan Dana (> Rp 1.000.000) -->
<div id="modalAppointmentsReview" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-7 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-lg flex items-center space-x-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Otorisasi Janji Tarik Tunai (> Rp 1.000.000)</span>
                </h3>
                <p class="text-xs text-slate-500">Tinjau dan berikan ACC / Tolak atas pengajuan perjanjian penarikan dana nasabah</p>
            </div>
            <button type="button" onclick="closeModal('modalAppointmentsReview')" class="text-slate-400 hover:text-slate-700 text-lg font-bold p-1">✕</button>
        </div>

        <!-- Filter Status Buttons -->
        <div class="flex items-center justify-between gap-2 flex-wrap">
            <div class="inline-flex rounded-xl bg-slate-100 p-1 text-xs font-bold text-slate-600">
                <button type="button" onclick="filterAppointmentsReview('pending')" id="tabAptPending" class="px-3 py-1.5 rounded-lg bg-white shadow-xs text-indigo-700 font-bold">Menunggu ACC</button>
                <button type="button" onclick="filterAppointmentsReview('approved')" id="tabAptApproved" class="px-3 py-1.5 rounded-lg text-slate-600">Di-ACC</button>
                <button type="button" onclick="filterAppointmentsReview('all')" id="tabAptAll" class="px-3 py-1.5 rounded-lg text-slate-600">Semua</button>
            </div>
            <button type="button" onclick="loadAppointmentsReview()" class="px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition flex items-center space-x-1">
                <span>Segarkan Data</span>
            </button>
        </div>

        <!-- Tabel Perjanjian -->
        <div class="overflow-x-auto rounded-2xl border border-slate-100">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[11px] border-b border-slate-100">
                    <tr>
                        <th class="p-3">Nasabah</th>
                        <th class="p-3">Saldo Tabungan</th>
                        <th class="p-3">Nominal Tarik</th>
                        <th class="p-3">Rencana Penarikan</th>
                        <th class="p-3">Status</th>
                        <th class="p-3 text-center">Aksi / Otorisasi</th>
                    </tr>
                </thead>
                <tbody id="tblAppointmentsReviewBody" class="divide-y divide-slate-100 text-slate-700">
                    <tr><td colspan="6" class="p-6 text-center text-slate-400">Memuat data permohonan...</td></tr>
                </tbody>
            </table>
        </div>
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
            
            const saldoBisaDitarik = Math.max(0, Number(activeCustomer.balance) - 10000);
            const maxDirectTarik = Math.min(1000000, saldoBisaDitarik);
            const ditarikEl = document.getElementById('custSaldoDitarikText');
            if (ditarikEl) {
                ditarikEl.innerHTML = `Bisa Ditarik: <b class="text-emerald-400">Rp ${maxDirectTarik.toLocaleString('id-ID')}</b>`;
            }

            // Update Alert Janji Penarikan Nasabah
            const aptAlert = document.getElementById('custAppointmentAlert');
            const aptBadge = document.getElementById('custAppointmentBadge');
            const aptAmount = document.getElementById('custAppointmentAmount');
            const aptDesc = document.getElementById('custAppointmentDesc');
            const aptAction = document.getElementById('custAppointmentAction');

            if (aptAlert) {
                if (activeCustomer.active_appointment) {
                    const apt = activeCustomer.active_appointment;
                    aptAlert.className = 'p-3 rounded-2xl text-xs space-y-1.5 border bg-emerald-50 border-emerald-300 text-emerald-950 block';
                    aptBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-200 text-emerald-800';
                    aptBadge.innerText = 'DI-ACC • SIAP DITARIK';
                    aptAmount.className = 'font-black text-xs text-emerald-700';
                    aptAmount.innerText = `Rp ${Number(apt.amount).toLocaleString('id-ID')}`;
                    aptDesc.innerHTML = `Perjanjian penarikan dana sebesar <b>Rp ${Number(apt.amount).toLocaleString('id-ID')}</b> telah <b>DI-ACC</b> untuk tanggal <b>${apt.withdrawal_date}</b>. Penarikan dana besar dapat diproses hari ini!`;
                    aptAction.innerHTML = '';
                } else if (activeCustomer.has_pending_appointment && activeCustomer.pending_appointment) {
                    const apt = activeCustomer.pending_appointment;
                    aptAlert.className = 'p-3 rounded-2xl text-xs space-y-1.5 border bg-amber-50 border-amber-300 text-amber-950 block';
                    aptBadge.className = 'px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-200 text-amber-800';
                    aptBadge.innerText = 'MENUNGGU ACC TELLER';
                    aptAmount.className = 'font-black text-xs text-amber-700';
                    aptAmount.innerText = `Rp ${Number(apt.amount).toLocaleString('id-ID')}`;
                    aptDesc.innerHTML = `Nasabah mengajukan janji penarikan dana sebesar <b>Rp ${Number(apt.amount).toLocaleString('id-ID')}</b> untuk tanggal <b>${apt.withdrawal_date}</b> via akun nasabah.`;
                    aptAction.innerHTML = `
                        <button type="button" onclick="approveAppointment(${apt.id}, '${escapeHtml(activeCustomer.name)}', ${apt.amount})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition">
                            ✓ ACC Sekarang
                        </button>
                    `;
                } else {
                    aptAlert.className = 'hidden';
                }
            }

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

        const balance = Number(activeCustomer.balance || 0);
        const maxAvail = Math.max(0, balance - 10000);
        const maxDirect = Math.min(1000000, maxAvail);

        document.getElementById('withdrawAmount').value = '';
        document.getElementById('withdrawPin').value = '';
        document.getElementById('withdrawDesc').value = '';

        const hintEl = document.getElementById('withdrawHintText');
        if (hintEl) {
            hintEl.innerHTML = `Maks. langsung: <b class="text-rose-700">Rp ${maxDirect.toLocaleString('id-ID')}</b>`;
        }

        // Reset Box Alert Penarikan Dana Besar (> 1 Jt)
        const alertBox = document.getElementById('largeWithdrawAlert');
        if (alertBox) alertBox.classList.add('hidden');

        const aptBox = document.getElementById('appointmentDetectedBox');
        if (aptBox) aptBox.classList.add('hidden');

        document.getElementById('verifiedAppointmentId').value = '';
        document.getElementById('checkManualAppointment').checked = false;
        document.getElementById('manualAppointmentDateField').classList.add('hidden');

        // Set tanggal perjanjian default ke kemarin
        const yesterday = new Date(Date.now() - 86400000).toISOString().slice(0, 10);
        const manualDateInput = document.getElementById('manualAppointmentDate');
        if (manualDateInput) {
            manualDateInput.value = yesterday;
            manualDateInput.max = yesterday;
        }

        // Cek jika nasabah ini memiliki perjanjian aktif H-1
        if (activeCustomer.has_active_appointment && activeCustomer.active_appointment) {
            const apt = activeCustomer.active_appointment;
            if (aptBox) aptBox.classList.remove('hidden');
            document.getElementById('verifiedAppointmentId').value = apt.id;
            document.getElementById('appointmentDetectedText').innerText = `Perjanjian ID #${apt.id} dibuat pada ${apt.appointment_date} untuk penarikan Rp ${Number(apt.amount).toLocaleString('id-ID')}.`;
            document.getElementById('checkManualAppointment').checked = true;
        }

        document.getElementById('modalWithdraw').classList.remove('hidden');
    }

    function handleWithdrawAmountInput(val) {
        const raw = unformatRupiahNumber(val);
        const alertBox = document.getElementById('largeWithdrawAlert');
        if (!alertBox) return;

        if (raw > 1000000) {
            alertBox.classList.remove('hidden');
        } else {
            alertBox.classList.add('hidden');
        }
    }

    function toggleManualAppointmentDate() {
        const checked = document.getElementById('checkManualAppointment').checked;
        const field = document.getElementById('manualAppointmentDateField');
        if (field) {
            field.classList.toggle('hidden', !checked);
        }
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

        const balance = Number(activeCustomer.balance || 0);
        const maxAvail = Math.max(0, balance - 10000);
        const maxDirect = Math.min(1000000, maxAvail);

        // 1. Validasi saldo mengendap Rp 10.000
        if (rawAmount > maxAvail) {
            Swal.fire({
                icon: 'warning',
                title: 'Saldo Mengendap Tidak Terpenuhi',
                html: `
                    <div class="text-left text-xs space-y-2 p-2">
                        <p>Penarikan ditolak karena sisa saldo setelah penarikan tidak boleh di bawah saldo mengendap minimum (<b>Rp 10.000</b>).</p>
                        <div class="p-2.5 bg-slate-100 rounded-xl space-y-1">
                            <div>Saldo Saat Ini: <b>Rp ${balance.toLocaleString('id-ID')}</b></div>
                            <div>Saldo Mengendap Wajib: <b>Rp 10.000</b></div>
                            <div class="text-rose-700 font-bold">Maksimal yang Bisa Ditarik: Rp ${maxAvail.toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                `,
                confirmButtonColor: '#e11d48'
            });
            return;
        }

        // 2. Validasi penarikan > Rp 1.000.000 wajib perjanjian H-1
        const verifiedAptId = document.getElementById('verifiedAppointmentId') ? document.getElementById('verifiedAppointmentId').value : '';
        const manualConfirmed = document.getElementById('checkManualAppointment') ? document.getElementById('checkManualAppointment').checked : false;
        const manualDate = document.getElementById('manualAppointmentDate') ? document.getElementById('manualAppointmentDate').value : '';

        if (rawAmount > 1000000 && !verifiedAptId && !manualConfirmed) {
            Swal.fire({
                icon: 'warning',
                title: 'Wajib Perjanjian H-1 (> Rp 1 Juta)',
                html: `
                    <div class="text-left text-xs space-y-2 p-2">
                        <p>Penarikan dana sebesar <b class="text-rose-700">Rp ${rawAmount.toLocaleString('id-ID')}</b> berada di atas batas penarikan langsung loket (<b>Rp 1.000.000</b>).</p>
                        <div class="p-2.5 bg-amber-50 border border-amber-200 rounded-xl text-amber-900 space-y-1">
                            <div><b>Ketentuan Bank Mini:</b></div>
                            <div>Penarikan di atas Rp 1.000.000 wajib melakukan perjanjian dengan pihak teller minimal 1 hari sebelumnya (H-1) untuk persiapan fisik uang kas laci.</div>
                        </div>
                        <p class="text-slate-500">Maksimal penarikan langsung saat ini tanpa perjanjian adalah <b>Rp ${maxDirect.toLocaleString('id-ID')}</b>.</p>
                    </div>
                `,
                confirmButtonColor: '#f59e0b',
                confirmButtonText: 'Pahami Aturan'
            });
            return;
        }

        // Jika manual check tapi tanggal bukan H-1 (misal hari ini atau masa depan)
        if (rawAmount > 1000000 && manualConfirmed && !verifiedAptId) {
            const todayStr = new Date().toISOString().slice(0, 10);
            if (!manualDate || manualDate >= todayStr) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Perjanjian Tidak Valid',
                    text: 'Perjanjian penarikan dana besar wajib dilakukan minimal 1 hari sebelum penarikan (H-1 / maksimal kemarin).',
                    confirmButtonColor: '#f59e0b'
                });
                return;
            }
        }

        const payload = {
            saver_id: activeCustomer.id,
            customer_id: activeCustomer.id,
            amount: rawAmount,
            security_pin: document.getElementById('withdrawPin').value,
            description: document.getElementById('withdrawDesc').value,
            appointment_id: verifiedAptId || null,
            appointment_confirmed: manualConfirmed,
            appointment_date: manualDate || null
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

    // ================= JANJI PENARIKAN DANA BESAR (> RP 1 JT) =================
    function openWithdrawalAppointmentModal() {
        if (!activeCustomer) {
            Swal.fire('Nasabah Belum Dipilih', 'Silakan cari atau scan nasabah terlebih dahulu.', 'warning');
            return;
        }

        const balance = Number(activeCustomer.balance || 0);
        const maxAvail = Math.max(0, balance - 10000);

        if (maxAvail <= 1000000) {
            Swal.fire({
                icon: 'info',
                title: 'Saldo Belum Memenuhi Kriteria (> Rp 1 Jt)',
                html: `
                    <div class="text-left text-xs space-y-2 p-2">
                        <p>Perjanjian H-1 khusus untuk penarikan dana di atas <b>Rp 1.000.000</b>.</p>
                        <p>Saldo nasabah saat ini: <b>Rp ${balance.toLocaleString('id-ID')}</b> (tersedia: <b>Rp ${maxAvail.toLocaleString('id-ID')}</b>).</p>
                        <p class="text-emerald-700 font-bold">Nasabah ini dapat langsung melakukan penarikan reguler di loket tanpa perlu perjanjian H-1.</p>
                    </div>
                `,
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        document.getElementById('aptCustName').innerText = `${activeCustomer.name} (${activeCustomer.account_number})`;
        document.getElementById('aptCustBalance').innerText = `Rp ${balance.toLocaleString('id-ID')}`;
        document.getElementById('aptCustAvailable').innerText = `Rp ${maxAvail.toLocaleString('id-ID')}`;
        document.getElementById('aptAmount').value = '';
        document.getElementById('aptNotes').value = '';

        // Tanggal minimal besok (H+1)
        const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10);
        const dateInput = document.getElementById('aptWithdrawalDate');
        if (dateInput) {
            dateInput.value = tomorrow;
            dateInput.min = tomorrow;
        }

        document.getElementById('modalWithdrawalAppointment').classList.remove('hidden');
    }

    async function submitWithdrawalAppointment(e) {
        e.preventDefault();
        const rawAmount = unformatRupiahNumber(document.getElementById('aptAmount').value);
        if (!rawAmount || rawAmount <= 1000000) {
            Swal.fire('Nominal Kurang', 'Perjanjian penarikan H-1 khusus untuk nominal di atas Rp 1.000.000! Untuk penarikan <= Rp 1 Jt dapat langsung ditarik di loket.', 'warning');
            return;
        }

        const balance = Number(activeCustomer.balance || 0);
        const maxAvail = Math.max(0, balance - 10000);
        if (rawAmount > maxAvail) {
            Swal.fire('Melebihi Saldo', `Nominal penarikan melebihi batas saldo tersedia (Rp ${maxAvail.toLocaleString('id-ID')}) dengan memperhitungkan saldo mengendap Rp 10.000.`, 'warning');
            return;
        }

        const btn = document.getElementById('btnSubmitApt');
        btn.disabled = true;
        btn.innerHTML = '<span>Menyimpan...</span>';

        const payload = {
            saver_id: activeCustomer.id,
            amount: rawAmount,
            withdrawal_date: document.getElementById('aptWithdrawalDate').value,
            notes: document.getElementById('aptNotes').value.trim()
        };

        const res = await fetchAPI('/teller/withdrawal-appointments', {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        btn.disabled = false;
        btn.innerHTML = '<span>Simpan Perjanjian H-1</span>';

        if (res && res.data && res.data.success) {
            closeModal('modalWithdrawalAppointment');
            Swal.fire({
                icon: 'success',
                title: 'Perjanjian Berhasil Dicatat!',
                text: res.data.message,
                confirmButtonColor: '#10b981'
            });
            searchCustomer(activeCustomer.account_number);
        } else {
            Swal.fire('Gagal Mencatat Perjanjian', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem.', 'error');
        }
    }

    // ================= OTORISASI PERJANJIAN PENARIKAN (> 1 JT) =================
    let currentAptFilter = 'pending';

    async function openAppointmentsReviewModal() {
        document.getElementById('modalAppointmentsReview').classList.remove('hidden');
        loadAppointmentsReview();
    }

    function filterAppointmentsReview(status) {
        currentAptFilter = status;
        const btnPending = document.getElementById('tabAptPending');
        const btnApproved = document.getElementById('tabAptApproved');
        const btnAll = document.getElementById('tabAptAll');

        if (btnPending) btnPending.className = 'px-3 py-1.5 rounded-lg text-slate-600';
        if (btnApproved) btnApproved.className = 'px-3 py-1.5 rounded-lg text-slate-600';
        if (btnAll) btnAll.className = 'px-3 py-1.5 rounded-lg text-slate-600';

        if (status === 'pending' && btnPending) {
            btnPending.className = 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-indigo-700 font-bold';
        } else if (status === 'approved' && btnApproved) {
            btnApproved.className = 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-emerald-700 font-bold';
        } else if (btnAll) {
            btnAll.className = 'px-3 py-1.5 rounded-lg bg-white shadow-xs text-indigo-700 font-bold';
        }

        loadAppointmentsReview();
    }

    async function loadPendingAppointmentsBadge() {
        try {
            const res = await fetchAPI('/teller/withdrawal-appointments?status=pending');
            if (res && res.data && res.data.success) {
                const count = res.data.pending_count !== undefined ? res.data.pending_count : (res.data.data ? res.data.data.length : 0);
                const badge = document.getElementById('badgePendingApt');
                if (badge) {
                    if (count > 0) {
                        badge.innerText = `${count}`;
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                }
            }
        } catch(e) {
            console.error('Error loadPendingAppointmentsBadge:', e);
        }
    }

    async function loadAppointmentsReview() {
        const tbody = document.getElementById('tblAppointmentsReviewBody');
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-400">Memuat data permohonan...</td></tr>';

        try {
            const res = await fetchAPI(`/teller/withdrawal-appointments?status=${currentAptFilter}`);
            if (res && res.data && res.data.success) {
                const list = res.data.data || [];
                loadPendingAppointmentsBadge();

                if (list.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-400">Tidak ada pengajuan perjanjian dengan status ini.</td></tr>';
                    return;
                }

                tbody.innerHTML = list.map(apt => {
                    const saver = apt.saver || {};
                    const balance = Number(saver.balance || 0);
                    const isPending = apt.status === 'pending';
                    const isApproved = apt.status === 'approved';

                    let badge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300">Menunggu ACC</span>';
                    if (isApproved) {
                        badge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">Di-ACC</span>';
                    } else if (apt.status === 'rejected') {
                        badge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-rose-100 text-rose-800 border border-rose-300">Ditolak</span>';
                    } else if (apt.status === 'completed') {
                        badge = '<span class="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-300">Selesai Dicairkan</span>';
                    }

                    let actions = '-';
                    const safeName = (saver.name || '').replace(/'/g, "\\'");
                    if (isPending) {
                        actions = `
                            <div class="flex items-center justify-center space-x-1.5">
                                <button onclick="approveAppointment(${apt.id}, '${safeName}', ${apt.amount})" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                    ✓ ACC
                                </button>
                                <button onclick="rejectAppointment(${apt.id}, '${safeName}')" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-xs transition">
                                    ✕ Tolak
                                </button>
                            </div>
                        `;
                    } else if (isApproved) {
                        actions = `<span class="text-xs font-bold text-emerald-700">Telah Di-ACC</span>`;
                    }

                    return `
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3">
                                <div class="font-bold text-slate-900">${saver.name || '-'}</div>
                                <div class="text-[11px] text-slate-500 font-mono">No. Rek: ${saver.account_number || '-'} • NIS: ${saver.nis || '-'}</div>
                            </td>
                            <td class="p-3 font-semibold text-slate-800">Rp ${balance.toLocaleString('id-ID')}</td>
                            <td class="p-3 font-black text-rose-600">Rp ${Number(apt.amount).toLocaleString('id-ID')}</td>
                            <td class="p-3 font-bold text-indigo-700">${apt.withdrawal_date}</td>
                            <td class="p-3">${badge}</td>
                            <td class="p-3 text-center">${actions}</td>
                        </tr>
                    `;
                }).join('');
            }
        } catch(e) {
            tbody.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-rose-500">Gagal memuat: ${e.message}</td></tr>`;
        }
    }

    async function approveAppointment(id, name, amount) {
        const confirm = await Swal.fire({
            title: 'ACC Perjanjian Penarikan?',
            html: `Apakah Anda yakin ingin menyetujui (ACC) pengajuan penarikan dana nasabah <b>${name}</b> sebesar <b>Rp ${Number(amount).toLocaleString('id-ID')}</b>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, ACC Sekarang',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const res = await fetchAPI(`/teller/withdrawal-appointments/${id}/approve`, { method: 'POST' });
            if (res && res.data && res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Di-ACC!',
                    text: res.data.message,
                    confirmButtonColor: '#10b981'
                });
                loadAppointmentsReview();
                loadPendingAppointmentsBadge();
                if (activeCustomer) searchCustomer(activeCustomer.account_number);
            } else {
                Swal.fire('Gagal ACC', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem.', 'error');
            }
        } catch(e) {
            Swal.fire('Error', e.message, 'error');
        }
    }

    async function rejectAppointment(id, name) {
        const { value: reason } = await Swal.fire({
            title: 'Tolak Perjanjian Penarikan',
            input: 'text',
            inputLabel: `Alasan Penolakan untuk Nasabah ${name}:`,
            inputPlaceholder: 'Misal: Saldo tidak mencukupi / salah tanggal...',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Tolak Pengajuan',
            cancelButtonText: 'Batal'
        });

        if (reason === undefined) return;

        try {
            const res = await fetchAPI(`/teller/withdrawal-appointments/${id}/reject`, {
                method: 'POST',
                body: JSON.stringify({ reason: reason || 'Ditolak oleh Petugas Teller' })
            });
            if (res && res.data && res.data.success) {
                Swal.fire('Ditolak', res.data.message, 'success');
                loadAppointmentsReview();
                loadPendingAppointmentsBadge();
                if (activeCustomer) searchCustomer(activeCustomer.account_number);
            } else {
                Swal.fire('Gagal Menolak', (res && res.data && res.data.message) || 'Terjadi kesalahan.', 'error');
            }
        } catch(e) {
            Swal.fire('Error', e.message, 'error');
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

    async function openCashAdvanceHistoryModal() {
        const modal = document.getElementById('modalAdvanceHistory');
        const loading = document.getElementById('advanceHistoryLoading');
        const empty = document.getElementById('advanceHistoryEmpty');
        const list = document.getElementById('advanceHistoryList');

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        empty.classList.add('hidden');
        list.classList.add('hidden');
        list.innerHTML = '';

        try {
            const res = await fetchAPI('/teller/cash-advances/history');
            loading.classList.add('hidden');

            if (res && res.data && res.data.success && Array.isArray(res.data.data) && res.data.data.length > 0) {
                list.classList.remove('hidden');
                list.innerHTML = res.data.data.map(item => {
                    const isInitial = (item.type === 'initial');
                    const createdDate = item.created_at ? new Date(item.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : item.request_date;
                    let statusBadge = '';
                    let statusBorder = 'border-slate-200';

                    if (item.status === 'pending') {
                        statusBorder = 'border-amber-300 bg-amber-50/40';
                        statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[11px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">Menunggu Admin</span>';
                    } else if (item.status === 'approved') {
                        const approver = item.approved_by ? (item.approved_by.name || 'Admin') : 'Admin';
                        statusBorder = 'border-emerald-200 bg-emerald-50/30';
                        statusBadge = `<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-emerald-100 text-emerald-800">Disetujui (${approver})</span>`;
                    } else if (item.status === 'rejected') {
                        statusBorder = 'border-rose-200 bg-rose-50/30';
                        statusBadge = '<span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold uppercase bg-rose-100 text-rose-800">Ditolak</span>';
                    }

                    return `
                        <div class="p-3.5 rounded-2xl border ${statusBorder} flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-black uppercase ${isInitial ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'}">
                                        ${isInitial ? 'Modal Awal' : 'Top Up Kas'}
                                    </span>
                                    <span class="text-xs text-slate-500 font-mono">${createdDate}</span>
                                </div>
                                <div class="text-lg font-black text-slate-900 font-mono">
                                    Rp ${Number(item.amount).toLocaleString('id-ID')}
                                </div>
                                ${item.notes ? `<p class="text-xs text-slate-600 italic">" ${item.notes} "</p>` : ''}
                                ${item.status === 'rejected' && item.rejection_reason ? `<div class="p-2 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 mt-1"><b>Alasan Penolakan:</b> ${item.rejection_reason}</div>` : ''}
                            </div>
                            <div class="self-start sm:self-center">
                                ${statusBadge}
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                empty.classList.remove('hidden');
            }
        } catch (err) {
            loading.classList.add('hidden');
            empty.classList.remove('hidden');
            empty.innerHTML = `<p class="text-rose-500 font-bold">Gagal memuat riwayat pengajuan.</p>`;
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
        ['activateAmountInput', 'depositAmount', 'withdrawAmount', 'clPhysicalInput', 'actTellerInitialDeposit', 'advAmount', 'aptAmount'].forEach(id => {
            attachRupiahMask(document.getElementById(id));
        });
        loadTodayTransactions();
        loadTellerCashBalance();
        loadTellerServiceStatus();
        loadPendingAppointmentsBadge();
    });
</script>
@endsection