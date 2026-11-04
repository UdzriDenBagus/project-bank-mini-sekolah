@extends('layouts.app')
@section('title', 'Panel Administrator - Bank Mini Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Header Panel Admin -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Sistem Utama & Konfigurasi</span>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mt-1 flex items-center"> Dashboard Administrator
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola Pengguna Internal, Anggota Siswa, Rekening Penabung, Laporan Bulanan, dan Audit Jurnal</p>
        </div>

        <!-- Tab Navigation Buttons -->
        <div class="flex flex-wrap gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
            <button onclick="switchAdminTab('users')" id="tabBtnUsers" class="px-4 py-2 rounded-xl text-xs font-bold transition bg-indigo-600 text-white shadow"> Akun Petugas
            </button>
            <button onclick="switchAdminTab('members')" id="tabBtnMembers" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Data Anggota
            </button>
            <button onclick="switchAdminTab('customers')" id="tabBtnCustomers" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Rekening Penabung
            </button>
            <button onclick="switchAdminTab('closings')" id="tabBtnClosings" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Otorisasi Closing Kas
            </button>
            <button onclick="switchAdminTab('advances')" id="tabBtnAdvances" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900 flex items-center"> Modal Awal Teller
                <span id="badgePendingAdvances" class="ml-1.5 px-2 py-0.5 rounded-full text-[10px] bg-amber-500 text-white font-black hidden">0</span>
            </button>
            <button onclick="switchAdminTab('trash')" id="tabBtnTrash" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Data Siswa Terhapus
            </button>
            <button onclick="switchAdminTab('monthly')" id="tabBtnMonthly" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Laporan Bulanan
            </button>
            <button onclick="switchAdminTab('journals')" id="tabBtnJournals" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Audit Jurnal
            </button>
            <button onclick="switchAdminTab('config')" id="tabBtnConfig" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900"> Konfigurasi
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: MANAJEMEN AKUN PETUGAS (USERS CRUD) -->
    <!-- ======================================================== -->
    <div id="sectionUsers" class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <h3 class="font-black text-slate-800 text-lg flex items-center"> Daftar Petugas Bank Mini
                </h3>
                <button onclick="openAddUserModal()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center self-start"> Tambah Petugas Baru
                </button>
            </div>

            <!-- Menu Pencarian & Memilah-milih Data Petugas (Accept All) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="relative w-full sm:w-80">
                    
                    <input type="text" id="searchUsersInput" oninput="filterAdminTable('users')" placeholder="Cari nama, username, atau role petugas..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="clearAdminSearch('users')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearUsersBtn">✕</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSelectAllPage('users')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center shadow-xs" title="Pilih Semua Data di Halaman Ini"> Accept All (Pilih Semua)
                    </button>
                    <button type="button" onclick="deselectAll('users')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center"> Batalkan
                    </button>
                    <span id="selectedBadge_users" class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold font-mono">0 Terpilih</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-3 text-center w-10">
                                <input type="checkbox" id="checkAll_users" onchange="toggleSelectAllPage('users')" title="Pilih Semua / Accept All" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-4">Nama Petugas</th>
                            <th class="py-3 px-4">Username</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="6" class="text-center py-6 text-slate-400 text-xs">Memuat data petugas...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="usersPagination"></div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: DATA ANGGOTA SISWA (MEMBERS CRUD) -->
    <!-- ======================================================== -->
    <div id="sectionMembers" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <div>
                    <h3 class="font-black text-slate-800 text-lg flex items-center"> Data Anggota Siswa Sekolah
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Tambah data anggota langsung lewat web atau unggah berkas Excel. Aktivasi rekening nasabah dilakukan di Loket Teller.</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
                    <!-- Opsi 1: Tambah Langsung Lewat Web -->
                    <button onclick="openAddMemberModal()" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center"> Tambah Langsung (Web)
                    </button>
                    <!-- Opsi 2: Tambah Lewat Excel -->
                    <button onclick="openImportExcelModal()" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center gap-2">
                        <img src="{{ asset('images/excel.png') }}" alt="Excel" class="w-4 h-4 object-contain">
                        <span>Tambah Lewat Excel</span>
                    </button>
                    <!-- Kenaikan Kelas Tahunan Bulan Juli -->
                    <button onclick="triggerJulyPromotionAdmin()" class="px-3 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition shadow flex items-center" title="Kenaikan Kelas & Kelulusan Bulan Juli"> Kenaikan Kelas Juli
                    </button>
                </div>
            </div>

            <!-- Menu Pencarian & Memilah-milih Data Anggota (Accept All) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="relative w-full sm:w-80">
                    
                    <input type="text" id="searchMembersInput" oninput="filterAdminTable('members')" placeholder="Cari NIS, nama, atau kelas siswa..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="clearAdminSearch('members')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearMembersBtn">✕</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSelectAllPage('members')" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center shadow-xs" title="Pilih Semua Data di Halaman Ini"> Accept All (Pilih Semua)
                    </button>
                    <button type="button" onclick="deselectAll('members')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center"> Batalkan
                    </button>
                    <span id="selectedBadge_members" class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold font-mono">0 Terpilih</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-3 text-center w-10">
                                <input type="checkbox" id="checkAll_members" onchange="toggleSelectAllPage('members')" title="Pilih Semua / Accept All" class="rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">Tahun Lulus</th>
                            <th class="py-3 px-4 text-center">Status Rekening</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="membersTableBody">
                        <tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Memuat data anggota...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="membersPagination"></div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: MASTER DATA PENABUNG (SAVERS) -->
    <!-- ======================================================== -->
    <div id="sectionCustomers" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <div>
                    <h3 class="font-black text-slate-800 text-lg flex items-center"> Master Rekening Penabung Siswa
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar buku rekening tabungan aktif & cetak kartu QR.</p>
                </div>
            </div>

            <!-- Banner Info: Pembukaan rekening di Tab Data Anggota -->
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 flex items-center gap-2 mb-4">
                
                <span><strong>Informasi:</strong> Pembuatan dan aktivasi rekening Penabung dilakukan melalui tombol <strong>Aktivasi</strong> di menu <strong>Data Anggota</strong> oleh Administrator.</span>
            </div>

            <!-- Menu Pencarian & Memilah-milih Data Penabung (Accept All) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                <div class="relative w-full sm:w-80">
                    
                    <input type="text" id="searchCustomersInput" oninput="filterAdminTable('customers')" placeholder="Cari No. Rekening, NIS, nama, atau kelas..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    <button type="button" onclick="clearAdminSearch('customers')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearCustomersBtn">✕</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSelectAllPage('customers')" class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-bold transition flex items-center shadow-xs" title="Pilih Semua Data di Halaman Ini"> Accept All (Pilih Semua)
                    </button>
                    <button type="button" onclick="deselectAll('customers')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center"> Batalkan
                    </button>
                    <span id="selectedBadge_customers" class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold font-mono">0 Terpilih</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-3 text-center w-10">
                                <input type="checkbox" id="checkAll_customers" onchange="toggleSelectAllPage('customers')" title="Pilih Semua / Accept All" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-4">No. Rekening</th>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4 text-right">Saldo Saat Ini</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <tr><td colspan="8" class="text-center py-6 text-slate-400 text-xs">Memuat data penabung...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="customersPagination"></div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: LAPORAN ARUS KAS BULANAN (MONTHLY REPORT) -->
    <!-- ======================================================== -->
    <div id="sectionMonthly" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b gap-3">
                <div>
                    <h3 class="font-black text-slate-800 text-lg flex items-center"> Laporan Arus Kas Bulanan
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Rekapitulasi keuangan bulanan Bank Mini Sekolah</p>
                </div>
                <!-- Filter Bulan & Tahun -->
                <div class="flex flex-wrap items-center gap-2">
                    <select id="selReportMonth" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $m == date('n') ? 'selected' : '' }}>Bulan {{ $m }}</option>
                        @endfor
                    </select>
                    <input type="number" id="selReportYear" value="{{ date('Y') }}" class="w-24 px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold font-mono">
                    <button onclick="loadMonthlyReport()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow transition"> Tampilkan
                    </button>
                    <button onclick="printMonthlyReport()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow flex items-center transition"> Cetak PDF
                    </button>
                </div>
            </div>

            <!-- Summary Cards Laporan Bulanan -->
            <div id="monthlySummaryBox" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Setoran</span>
                    <h4 id="repTotalDeposit" class="text-lg font-black text-emerald-600 mt-1">Rp 0</h4>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                    <span class="text-xs font-bold text-slate-500 uppercase">Total Penarikan</span>
                    <h4 id="repTotalWithdraw" class="text-lg font-black text-rose-600 mt-1">Rp 0</h4>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                    <span class="text-xs font-bold text-slate-500 uppercase">Arus Kas Bersih (Net)</span>
                    <h4 id="repNetCashflow" class="text-lg font-black text-indigo-600 mt-1">Rp 0</h4>
                </div>
                <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
                    <span class="text-xs font-bold text-slate-500 uppercase">Volume Transaksi</span>
                    <h4 id="repTotalTx" class="text-lg font-black text-slate-800 mt-1">0 Trx</h4>
                </div>
            </div>

            <!-- Menu Pencarian Rincian Kas Harian -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 mb-1">
                <h5 class="text-xs font-bold uppercase text-slate-600 flex items-center"> Rincian Mutasi Kas Harian
                </h5>
                <div class="relative w-full sm:w-64">
                    
                    <input type="text" id="searchMonthlyInput" oninput="filterMonthlyDailyTable()" placeholder="Cari tanggal (YYYY-MM-DD)..." class="w-full pl-8 pr-7 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="clearMonthlySearch()" class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearMonthlyBtn">✕</button>
                </div>
            </div>

            <!-- Rincian Harian -->
            <div class="overflow-x-auto pt-2">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-4 text-center">Tanggal</th>
                            <th class="py-3 px-4 text-right">Total Setoran</th>
                            <th class="py-3 px-4 text-right">Total Penarikan</th>
                            <th class="py-3 px-4 text-right">Net Kas Harian</th>
                            <th class="py-3 px-4 text-center">Volume Trx</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyDailyBody">
                        <tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">Pilih periode dan klik Tampilkan...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: AUDIT JURNAL AKUNTANSI (BACK-END) -->
    <!-- ======================================================== -->
    <div id="sectionJournals" class="space-y-6 hidden">
        <!-- Balance Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <span class="text-xs font-bold uppercase text-slate-500">Total Debet</span>
                <h4 id="totalDebitText" class="text-xl font-black text-slate-800 mt-1">Rp 0</h4>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <span class="text-xs font-bold uppercase text-slate-500">Total Kredit</span>
                <h4 id="totalCreditText" class="text-xl font-black text-slate-800 mt-1">Rp 0</h4>
            </div>
            <div id="balanceCard" class="bg-emerald-50 rounded-2xl p-5 border border-emerald-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase text-emerald-700">Status Pembukuan</span>
                    <h4 id="balanceStatusText" class="text-xl font-black text-emerald-800 mt-1">BALANCE (SEIMBANG)</h4>
                </div>
                
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <h3 class="font-black text-slate-800 text-lg flex items-center"> Buku Besar Jurnal Akuntansi (Double-Entry)
                </h3>
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative w-full sm:w-64">
                        
                        <input type="text" id="searchJournalsInput" oninput="filterAdminTable('journals')" placeholder="Cari kode akun / nama akun..." class="w-full pl-8 pr-7 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        <button type="button" onclick="clearAdminSearch('journals')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearJournalsBtn">✕</button>
                    </div>
                    <input type="date" id="journalFilterDate" onchange="loadJournals()" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">Kode Akun</th>
                            <th class="py-3 px-4">Nama Akun Akuntansi</th>
                            <th class="py-3 px-4 text-center">Posisi</th>
                            <th class="py-3 px-4 text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody id="journalsTableBody">
                        <tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">Memuat jurnal...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="journalsPagination"></div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 6: KONFIGURASI GLOBAL PERBANKAN -->
    <!-- ======================================================== -->
    <div id="sectionConfig" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 max-w-2xl">
            <h3 class="font-black text-slate-800 text-lg flex items-center mb-4"> Parameter Inti Perbankan Mini
            </h3>
            <div class="space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Batas Saldo Mengendap Minimum</label>
                    <input type="text" value="Rp 10.000" disabled class="w-full px-4 py-2.5 rounded-xl border bg-slate-100 text-slate-700 font-bold">
                    <span class="text-[11px] text-slate-400 mt-1 block">Terkunci secara mutlak pada aturan bisnis backend penarikan tunai.</span>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Format Penomoran Rekening Otomatis</label>
                    <input type="text" value="[Tahun 2 Digit][Kode Sekolah 01][Urutan 5 Digit] -> 260100001" disabled class="w-full px-4 py-2.5 rounded-xl border bg-slate-100 text-slate-700 font-mono text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Perangkat Pemindai QR</label>
                    <input type="text" value="Kamera / Webcam Terintegrasi HTML5-QRCode" disabled class="w-full px-4 py-2.5 rounded-xl border bg-slate-100 text-slate-700 font-bold">
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: OTORISASI CLOSING KAS TELLER (OTORISASI ADMIN) -->
    <!-- ======================================================== -->
    <div id="sectionClosings" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b">
                <div>
                    <h3 class="font-black text-slate-800 text-lg flex items-center"> Laporan Closing Kas Loket Teller
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Validasi Fisik Kas Teller, Otorisasi Pemindahan ke Brankas Utama (102), Cetak Laporan Audit, & Dispute Resolution</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <button onclick="acceptAllPendingClosings()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition flex items-center shadow" title="Setujui Semua Laporan Closing Pending"> Accept All Closing (Setujui Semua)
                    </button>
                    <button onclick="loadAdminClosings()" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center"> Segarkan Laporan
                    </button>
                </div>
            </div>

            <!-- Menu Pencarian & Memilah-milih Laporan Closing (Accept All) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                <div class="relative w-full sm:w-80">
                    
                    <input type="text" id="searchClosingsInput" oninput="filterAdminTable('closings')" placeholder="Cari tanggal, nama teller, status..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                    <button type="button" onclick="clearAdminSearch('closings')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearClosingsBtn">✕</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSelectAllPage('closings')" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl text-xs font-bold transition flex items-center shadow-xs" title="Pilih Semua Data di Halaman Ini"> Accept All (Pilih Semua)
                    </button>
                    <button type="button" onclick="deselectAll('closings')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center"> Batalkan
                    </button>
                    <span id="selectedBadge_closings" class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold font-mono">0 Terpilih</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-3 text-center w-10">
                                <input type="checkbox" id="checkAll_closings" onchange="toggleSelectAllPage('closings')" title="Pilih Semua / Accept All" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-4">Tanggal</th>
                            <th class="py-3 px-4">Piket Teller</th>
                            <th class="py-3 px-4 text-right">Saldo Sistem</th>
                            <th class="py-3 px-4 text-right">Uang Fisik Laci</th>
                            <th class="py-3 px-4 text-right">Selisih Kas</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi & Otorisasi</th>
                        </tr>
                    </thead>
                    <tbody id="adminClosingsBody">
                        <tr><td colspan="8" class="text-center py-6 text-slate-400 text-xs">Memuat laporan closing...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="closingsPagination"></div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB: OTORISASI MODAL AWAL & TOP UP KAS TELLER -->
    <!-- ======================================================== -->
    <div id="sectionAdvances" class="space-y-6 hidden">
        <!-- Balance Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <span class="text-xs font-bold uppercase text-slate-500 flex items-center"> Menunggu Persetujuan
                </span>
                <h4 id="advPendingCountText" class="text-2xl font-black text-amber-600 mt-1">0 Permohonan</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Memerlukan otorisasi Admin agar modal masuk laci</p>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
                <span class="text-xs font-bold uppercase text-slate-500 flex items-center"> Modal Disetujui Hari Ini
                </span>
                <h4 id="advApprovedTodayText" class="text-2xl font-black text-emerald-600 mt-1">Rp 0</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Total uang fisik kasir yang telah diserahterimakan</p>
            </div>
            <div class="bg-indigo-50 rounded-2xl p-5 border border-indigo-200 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase text-indigo-700">Akuntansi Jurnal Otomatis</span>
                    <h4 class="text-sm font-black text-indigo-900 mt-1">Kas Loket (101) ⇆ Brankas (102)</h4>
                    <p class="text-[10px] text-indigo-700/80 mt-0.5">Saldo laci bertambah tanpa menyebabkan minus saat penarikan</p>
                </div>
                
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b">
                <div>
                    <h3 class="font-black text-slate-800 text-lg flex items-center"> Daftar Pengajuan Modal Awal & Top Up Teller
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Otorisasi permohonan modal awal pembukaan loket & suntikan kas saat saldo laci teller menipis</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="loadAdminCashAdvances()" class="px-4 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-xl text-xs font-bold transition flex items-center"> Segarkan Data
                    </button>
                </div>
            </div>

            <!-- Filter Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                <div class="relative w-full sm:w-80">
                    
                    <input type="text" id="searchAdvancesInput" oninput="filterAdminTable('advances')" placeholder="Cari nama teller, catatan..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500 transition">
                    <button type="button" onclick="clearAdminSearch('advances')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearAdvancesBtn">✕</button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select id="filterAdvanceStatus" onchange="loadAdminCashAdvances()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                        <option value="all">Semua Status</option>
                        <option value="pending" selected>Menunggu Approval (Pending)</option>
                        <option value="approved">Disetujui (Approved)</option>
                        <option value="rejected">Ditolak (Rejected)</option>
                    </select>
                    <input type="date" id="filterAdvanceDate" onchange="loadAdminCashAdvances()" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-4">Tanggal & Waktu</th>
                            <th class="py-3 px-4">Petugas Teller</th>
                            <th class="py-3 px-4 text-center">Jenis</th>
                            <th class="py-3 px-4 text-right">Nominal Pengajuan</th>
                            <th class="py-3 px-4">Catatan Teller</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="advancesTableBody">
                        <tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Memuat permohonan modal...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="advancesPagination"></div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 5: DATA SISWA TERHAPUS & ARSIP BERKAS ZIP (> 2 BULAN) -->
    <!-- ======================================================== -->
    <div id="sectionTrash" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-slate-800 text-lg flex items-center"> Data Siswa Terhapus
                        </h3>
                        <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 rounded-full text-[10px] font-black uppercase">Arsip Lifecycle</span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1"> Siswa terdaftar yang dinonaktifkan / dihapus akan masuk ke daftar Data Siswa Terhapus. Data <strong>tidak boleh dihapus permanen</strong>. 
                        Data yang telah melampaui masa tenggang 2 bulan secara otomatis dikemas menjadi berkas ZIP / folder arsip yang dapat dibuka dan diunduh sewaktu-waktu. 
                        Tersedia opsi <strong>Aktifkan Kembali</strong> jika siswa tidak naik kelas. Seluruh riwayat transaksi tabungan tetap aman.
                    </p>
                </div>
                <button onclick="loadAdminTrash(); loadAdminArchives();" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center self-start"> Segarkan Data
                </button>
            </div>

            <!-- Menu Pencarian & Memilah-milih Data Siswa Terhapus (Accept All) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                <div class="relative w-full sm:w-80">
                    
                    <input type="text" id="searchTrashInput" oninput="filterAdminTable('trash')" placeholder="Cari NIS, nama, kelas, no rekening..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <button type="button" onclick="clearAdminSearch('trash')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearTrashBtn">✕</button>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleSelectAllPage('trash')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center shadow-xs" title="Pilih Semua Data di Halaman Ini"> Accept All (Pilih Semua)
                    </button>
                    <button type="button" onclick="deselectAll('trash')" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-bold transition flex items-center"> Batalkan
                    </button>
                    <span id="selectedBadge_trash" class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold font-mono">0 Terpilih</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-3 text-center w-10">
                                <input type="checkbox" id="checkAll_trash" onchange="toggleSelectAllPage('trash')" title="Pilih Semua / Accept All" class="rounded text-slate-700 focus:ring-slate-500 w-4 h-4 cursor-pointer">
                            </th>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4">No. Rekening</th>
                            <th class="py-3 px-4 text-right">Saldo Terakhir</th>
                            <th class="py-3 px-4">Tanggal Dihapus</th>
                            <th class="py-3 px-4 text-center">Status / Waktu Arsip</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="trashTableBody">
                        <tr><td colspan="9" class="text-center py-6 text-slate-400 text-xs">Memuat data siswa terhapus...</td></tr>
                    </tbody>
                </table>
            </div>
            <div id="trashPagination"></div>

            <!-- ======================================================== -->
            <!-- DAFTAR BERKAS ZIP ARSIP DATA SISWA (> 2 BULAN) -->
            <!-- ======================================================== -->
            <div class="mt-8 pt-6 border-t border-slate-200 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="font-black text-slate-800 text-base flex items-center"> Berkas ZIP / Folder Arsip Siswa Terhapus (> 2 Bulan)
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5"> Data siswa yang telah melewati batas 2 bulan otomatis dikemas menjadi berkas ZIP arsip. Berkas ini dapat dibuka untuk memeriksa isinya maupun diunduh kapan saja.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="triggerGenerateArchive()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center"> Kemas ZIP Sekarang
                        </button>
                        <button onclick="loadAdminArchives()" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition">
                            
                        </button>
                    </div>
                </div>

                <!-- Menu Pencarian Berkas Arsip ZIP -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                    <div class="relative w-full sm:w-80">
                        
                        <input type="text" id="searchArchivesInput" oninput="filterAdminTable('archives')" placeholder="Cari nama berkas arsip ZIP..." class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                        <button type="button" onclick="clearAdminSearch('archives')" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs hidden" id="clearArchivesBtn">✕</button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                                <th class="py-3 px-4">Nama Berkas ZIP</th>
                                <th class="py-3 px-4">Tanggal Arsip</th>
                                <th class="py-3 px-4 text-center">Jumlah Siswa</th>
                                <th class="py-3 px-4 text-center">Ukuran Berkas</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="archivesTableBody">
                            <tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">Memuat berkas arsip ZIP...</td></tr>
                        </tbody>
                    </table>
                </div>
                <div id="archivesPagination"></div>
            </div>
        </div>
    </div>

</div>

<!-- ======================================================== -->
<!-- MODALS -->
<!-- ======================================================== -->

<!-- Modal Tambah Petugas -->
<div id="modalAddUser" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Tambah Petugas Baru
        </h3>
        <form onsubmit="submitAddUser(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="addUserName" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Username</label>
                <input type="text" id="addUserUsername" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email (Untuk Reset Password Gmail Admin)</label>
                <input type="email" id="addUserEmail" placeholder="contoh: admin@gmail.com" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kata Sandi (Password)</label>
                <input type="password" id="addUserPassword" required minlength="6" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Peran (Role)</label>
                <select id="addUserRole" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="teller">Piket Teller (Operator Loket)</option>
                    <option value="admin">Administrator (Sistem)</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalAddUser')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow">Simpan Petugas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Petugas -->
<div id="modalEditUser" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Edit Data Petugas
        </h3>
        <form onsubmit="submitEditUser(event)" class="space-y-3 text-sm">
            <input type="hidden" id="editUserId">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                <input type="text" id="editUserName" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Username</label>
                <input type="text" id="editUserUsername" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email (Khusus Admin untuk Reset Password)</label>
                <input type="email" id="editUserEmail" placeholder="admin@gmail.com" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" id="editUserPassword" minlength="6" placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Peran (Role)</label>
                <select id="editUserRole" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="teller">Piket Teller (Operator Loket)</option>
                    <option value="admin">Administrator (Sistem)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Akun</label>
                <select id="editUserStatus" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="active">Aktif</option>
                    <option value="inactive">Diblokir (Nonaktif)</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalEditUser')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Audit Detail Mutasi Teller (Otorisasi Closing Kas) -->
<div id="modalAuditDetail" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-lg flex items-center"> Audit Transaksi Teller
                </h3>
                <p id="auditSubTitle" class="text-xs text-slate-500 mt-0.5">Detail transaksi harian teller</p>
            </div>
            <button onclick="closeModal('modalAuditDetail')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <div class="relative w-full">
            
            <input type="text" id="searchAuditInput" oninput="filterAuditDetailTable()" placeholder="Cari kode TRX, nama nasabah, jenis transaksi..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
        </div>

        <div class="overflow-y-auto flex-1">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b bg-slate-50 text-slate-600 uppercase font-bold">
                        <th class="py-2.5 px-3">Kode TRX</th>
                        <th class="py-2.5 px-3">Nasabah / Penabung</th>
                        <th class="py-2.5 px-3 text-center">Jenis</th>
                        <th class="py-2.5 px-3 text-right">Nominal</th>
                    </tr>
                </thead>
                <tbody id="auditTableBody">
                    <tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">Memuat rincian transaksi...</td></tr>
                </tbody>
            </table>
        </div>

        <div class="border-t pt-3 flex justify-between items-center">
            <a id="btnModalPrintAudit" href="#" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center shadow transition"> Cetak Laporan Audit PDF
            </a>
            <button type="button" onclick="closeModal('modalAuditDetail')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup Audit</button>
        </div>
    </div>
</div>

<!-- Modal Tambah Anggota Siswa (Members) -->
<div id="modalAddMember" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Tambah Anggota Siswa Sekolah
        </h3>
        <p class="text-xs text-slate-500">Pendaftaran data siswa sekolah. Kata sandi akun login dan PIN transaksi akan diisi saat aktivasi rekening nasabah.</p>
        <form onsubmit="submitAddMember(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">NIS (Nomor Induk Siswa)</label>
                <input type="text" id="addMemberNis" required placeholder="Contoh: 1001" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Siswa</label>
                <input type="text" id="addMemberName" required placeholder="Nama siswa..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kelas / Jurusan</label>
                <input type="text" id="addMemberClass" oninput="autoCalculateAddGradYear()" required placeholder="Contoh: X RPL 1" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Format: X / XI / XII (Tahun kelulusan di bawah otomatis terhitung)</span>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Siswa (Gmail untuk Reset Password/PIN)</label>
                <input type="email" id="addMemberEmail" placeholder="nama.siswa@gmail.com (opsional)" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tahun Lulus (3 Tahun Masa Studi)</label>
                <input type="number" id="addMemberGradYear" required value="{{ (int)date('Y') + 2 }}" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Kelas X = {{ (int)date('Y') + 2 }}, Kelas XI = {{ (int)date('Y') + 1 }}, Kelas XII = {{ (int)date('Y') }}</span>
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalAddMember')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow">Simpan Anggota</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Anggota Siswa (Ubah Kelas & Tahun Lulus) -->
<div id="modalEditMember" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center"> Ubah Data Anggota Siswa
        </h3>
        <form onsubmit="submitEditMember(event)" class="space-y-3">
            <input type="hidden" id="editMemberId">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nomor Induk Siswa (NIS)</label>
                <input type="text" id="editMemberNis" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Siswa</label>
                <input type="text" id="editMemberName" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kelas / Jurusan</label>
                <input type="text" id="editMemberClass" oninput="autoCalculateEditGradYear()" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Format: X / XI / XII (Tahun kelulusan di bawah otomatis terhitung)</span>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email Siswa (Gmail)</label>
                <input type="email" id="editMemberEmail" placeholder="nama.siswa@gmail.com" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Tahun Lulus (3 Tahun Masa Studi)</label>
                <input type="number" id="editMemberGradYear" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
                <span class="text-[10px] text-slate-400 mt-0.5 block">Kelas X = {{ (int)date('Y') + 2 }}, Kelas XI = {{ (int)date('Y') + 1 }}, Kelas XII = {{ (int)date('Y') }}</span>
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalEditMember')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Anggota via Upload Excel (Revisi 1) -->
<div id="modalImportExcel" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 flex items-center justify-center border border-emerald-200 p-1.5 shadow-sm shrink-0">
                    <img src="{{ asset('images/excel.png') }}" alt="Logo Excel" class="w-full h-full object-contain">
                </div>
                <div>
                    <h3 class="font-black text-slate-800 text-base">Tambah Anggota Lewat Excel</h3>
                    <p class="text-xs text-slate-500">Impor data banyak anggota sekaligus menggunakan file Excel</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modalImportExcel')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <!-- 1. Opsi Unduh Template Table -->
        <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-900 flex items-center gap-1.5">
                    <img src="{{ asset('images/excel.png') }}" alt="Excel" class="w-4 h-4 object-contain">
                    <span>Unduh Template Tabel Excel</span>
                </span>
                <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-emerald-200 text-emerald-800 font-bold">.XLSX</span>
            </div>
            <p class="text-[11px] text-emerald-800 leading-relaxed"> Template tabel sudah dilengkapi header kolom: <b>NIS</b>, <b>NAMA SISWA</b>, dan <b>KELAS</b>. Anda cukup mengisi baris kolom di bawah header tersebut.
            </p>
            <div class="pt-1">
                <a href="{{ asset('templates/template_tambah_anggota.xlsx') }}" download="template_tambah_anggota.xlsx" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-xs transition cursor-pointer">
                    <img src="{{ asset('images/excel.png') }}" alt="Excel" class="w-4 h-4 object-contain">
                    <span>Unduh Template Tabel Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- 2. Form Upload File Excel -->
        <form id="formImportExcel" onsubmit="submitImportExcel(event)" class="space-y-4 text-xs pt-1">
            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1.5"> Pilih File Excel yang Sudah Diisi (.xlsx, .xls, .csv) *
                </label>
                <div class="border-2 border-dashed border-slate-300 rounded-2xl p-5 text-center hover:border-emerald-500 transition bg-slate-50/50">
                    <img src="{{ asset('images/excel.png') }}" alt="Excel File" class="w-10 h-10 mx-auto mb-2 object-contain drop-shadow-xs">
                    <p class="text-xs font-bold text-slate-700">Klik untuk memilih file Excel</p>
                    <p class="text-[10px] text-slate-400 mt-0.5">Format didukung: Microsoft Excel (.xlsx, .xls) atau CSV (.csv)</p>
                    <input type="file" id="importExcelFile" required accept=".xlsx, .xls, .csv, .txt" class="mt-3 block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                </div>
            </div>

            <!-- Catatan Password Default -->
            <div class="p-3 bg-slate-100 border border-slate-200 rounded-xl text-[11px] text-slate-600 space-y-1">
                <p><strong> Informasi Akun Baru:</strong></p>
                <p>Setiap anggota yang diimpor akan mendapatkan kata sandi awal <code class="px-1.5 py-0.5 bg-white rounded border font-mono text-indigo-600 font-bold">password123</code> dan berstatus belum aktif. Siswa dapat login setelah diaktivasi di Loket Teller.</p>
            </div>

            <div class="flex justify-end space-x-2 pt-2 border-t">
                <button type="button" onclick="closeModal('modalImportExcel')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnSubmitImportExcel" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                    
                    <span>Upload & Impor Anggota</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Aktivasi Rekening Anggota Siswa (Role Admin) -->
<div id="modalActivateMemberAdmin" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <div class="flex items-center justify-between border-b pb-3">
            <h3 class="font-black text-slate-800 text-lg flex items-center"> Aktivasi Rekening Nasabah
            </h3>
            <button type="button" onclick="closeModal('modalActivateMemberAdmin')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>
        
        <!-- Ringkasan Profil Siswa -->
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-2xl text-xs space-y-1.5">
            <input type="hidden" id="actMemberId">
            <div class="flex justify-between">
                <span class="text-slate-500">Nama Siswa:</span>
                <span id="actMemberName" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">NIS:</span>
                <span id="actMemberNis" class="font-mono font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kelas / Jurusan:</span>
                <span id="actMemberClass" class="font-bold text-slate-800">-</span>
            </div>
            <div class="flex justify-between border-t border-slate-200/80 pt-1.5">
                <span class="text-slate-500">Status Rekening:</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-amber-100 text-amber-800">Belum Aktif</span>
            </div>
        </div>

        <form onsubmit="submitActivateMember(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Setoran Awal (Wajib Minimal Rp 10.000) *</label>
                <input type="text" inputmode="numeric" id="actInitialDeposit" required placeholder="Minimal 10.000" class="format-rupiah w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs font-bold font-mono">
                <p class="text-[10px] text-amber-600 font-bold mt-1"> Syarat aktivasi penabung: Wajib melakukan setoran tunai pertama minimal Rp 10.000.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kata Sandi (Password) Login Siswa *</label>
                <input type="password" id="actPassword" required minlength="6" placeholder="Minimal 6 karakter..." class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs">
                <p class="text-[10px] text-slate-400 mt-1">Kata sandi akun siswa untuk login ke portal web bank mini.</p>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">PIN Transaksi Keamanan (Tepat 6-Digit Angka) *</label>
                <input type="password" id="actSecurityPin" required maxlength="6" pattern="[0-9]{6}" placeholder="6 Digit Angka (misal: 123456)" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none text-xs font-mono tracking-widest text-center font-bold">
                <p class="text-[10px] text-slate-400 mt-1">PIN 6 digit angka untuk verifikasi otorisasi penarikan dan transaksi tabungan.</p>
            </div>
            <div class="flex justify-end space-x-2 pt-3 border-t">
                <button type="button" onclick="closeModal('modalActivateMemberAdmin')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" id="btnSubmitActivateAdmin" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                    
                    <span>Aktifkan Rekening</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Cetak Kartu Tabungan Siswa + QR Code -->
<div id="modalCardQR" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-center">
        <h3 class="font-black text-slate-800 text-base flex items-center justify-center"> Kartu Tabungan Digital Siswa
        </h3>
        
        <div id="printableArea" class="p-5 rounded-2xl bg-gradient-to-br from-indigo-950 to-slate-900 text-white shadow-xl text-left border border-indigo-700/50 space-y-4">
            <div class="flex items-center justify-between border-b border-indigo-700/50 pb-2">
                <div class="flex items-center space-x-2">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK" class="h-6 w-6 object-contain">
                    <span class="text-[10px] font-black uppercase tracking-wider text-yellow-400">BANK MINI SEKOLAH</span>
                </div>
                <span class="text-[10px] text-indigo-300 font-mono">SMK NEGERI 1 BANTUL</span>
            </div>

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h4 id="cardNama" class="font-black text-base leading-tight">Nama Siswa</h4>
                    <p id="cardRekening" class="text-xs font-mono text-indigo-300 font-bold mt-0.5">260100001</p>
                    <p id="cardKelas" class="text-[11px] text-slate-300 mt-1">Kelas: XII RPL 1</p>
                    <p id="cardNis" class="text-[11px] text-slate-400">NIS: 1001</p>
                </div>
                <!-- Box QR Code Container -->
                <div id="qrcodeBox" class="p-2 bg-white rounded-xl shadow shrink-0 flex items-center justify-center"></div>
            </div>
        </div>

        <div class="flex justify-end space-x-2 pt-2 no-print">
            <button type="button" onclick="closeModal('modalCardQR')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow flex items-center"> Cetak Kartu
            </button>
        </div>
    </div>
</div>

<!-- Modal Buka / Periksa Arsip ZIP Siswa -->
<div id="modalInspectArchive" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl space-y-4 max-h-[85vh] flex flex-col">
        <div class="flex items-center justify-between border-b pb-3">
            <div>
                <h3 class="font-black text-slate-800 text-lg flex items-center"> Periksa Isi Berkas ZIP Arsip
                </h3>
                <p id="inspectArchiveName" class="text-xs text-slate-500 font-mono mt-0.5">arsip_data_siswa.zip</p>
            </div>
            <button onclick="closeModal('modalInspectArchive')" class="text-slate-400 hover:text-slate-700 text-lg font-bold">✕</button>
        </div>

        <div class="overflow-y-auto flex-1 space-y-4 pr-1">
            <!-- Info Box -->
            <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-2xl text-xs space-y-1">
                <div class="flex justify-between"><span class="text-slate-500">Jumlah Siswa:</span> <b id="inspectArchiveCount" class="text-indigo-900">0</b></div>
                <div class="flex justify-between"><span class="text-slate-500">Ukuran ZIP:</span> <b id="inspectArchiveSize" class="text-indigo-900">0 KB</b></div>
                <div class="flex justify-between"><span class="text-slate-500">Tanggal Arsip Dibuat:</span> <b id="inspectArchiveDate" class="text-slate-700">-</b></div>
            </div>

            <!-- Berkas di dalam ZIP -->
            <div>
                <h5 class="text-xs font-bold uppercase text-slate-600 mb-1.5 flex items-center"> Daftar Berkas di Dalam ZIP:
                </h5>
                <ul id="inspectFileList" class="text-xs font-mono space-y-1 bg-slate-50 p-3 rounded-xl border border-slate-200 text-slate-700 max-h-28 overflow-y-auto">
                </ul>
            </div>

            <!-- Daftar Siswa Terarsip -->
            <div>
                <h5 class="text-xs font-bold uppercase text-slate-600 mb-1.5 flex items-center"> Ringkasan Siswa dalam Paket Ini:
                </h5>
                <div class="overflow-x-auto border border-slate-200 rounded-xl max-h-48 overflow-y-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-600 font-bold border-b sticky top-0">
                            <tr>
                                <th class="p-2">NIS</th>
                                <th class="p-2">Nama Siswa</th>
                                <th class="p-2">Kelas</th>
                                <th class="p-2 text-right">Saldo Terakhir</th>
                            </tr>
                        </thead>
                        <tbody id="inspectStudentsBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="border-t pt-3 flex justify-between items-center">
            <a id="btnDownloadArchiveFromModal" href="#" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs flex items-center shadow transition"> Unduh Berkas ZIP
            </a>
            <button type="button" onclick="closeModal('modalInspectArchive')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Tutup</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tab Controller
    function switchAdminTab(tab) {
        const sections = ['Users', 'Members', 'Customers', 'Closings', 'Advances', 'Trash', 'Monthly', 'Journals', 'Config'];
        sections.forEach(s => {
            const el = document.getElementById(`section${s}`);
            if (el) el.classList.add('hidden');
            const btn = document.getElementById(`tabBtn${s}`);
            if (btn) btn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900';
        });

        const map = {
            'users': 'Users',
            'members': 'Members',
            'customers': 'Customers',
            'closings': 'Closings',
            'advances': 'Advances',
            'trash': 'Trash',
            'monthly': 'Monthly',
            'journals': 'Journals',
            'config': 'Config'
        };

        const activeSec = map[tab] || (tab.charAt(0).toUpperCase() + tab.slice(1));
        const targetEl = document.getElementById(`section${activeSec}`);
        if (targetEl) targetEl.classList.remove('hidden');
        const targetBtn = document.getElementById(`tabBtn${activeSec}`);
        if (targetBtn) targetBtn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition bg-indigo-600 text-white shadow';

        if (tab === 'users') loadUsers();
        if (tab === 'members') loadMembers();
        if (tab === 'customers') loadCustomers();
        if (tab === 'closings') loadAdminClosings();
        if (tab === 'advances') loadAdminCashAdvances();
        if (tab === 'trash') {
            loadAdminTrash();
            loadAdminArchives();
        }
        if (tab === 'monthly') loadMonthlyReport();
        if (tab === 'journals') loadJournals();
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // =========================================================
    // SELECTION & "ACCEPT ALL" MEMILAH-MILIH DATA TABEL
    // =========================================================
    const adminSelectedItems = {
        users: new Set(),
        members: new Set(),
        customers: new Set(),
        closings: new Set(),
        trash: new Set()
    };

    function updateSelectedBadge(key) {
        if (!adminSelectedItems[key]) return;
        const badge = document.getElementById(`selectedBadge_${key}`);
        const count = adminSelectedItems[key].size;
        if (badge) {
            badge.innerText = `${count} Terpilih`;
            if (count > 0) {
                badge.className = 'px-2.5 py-1 bg-indigo-100 text-indigo-800 rounded-xl text-xs font-bold font-mono animate-pulse';
            } else {
                badge.className = 'px-2.5 py-1 bg-slate-100 text-slate-600 rounded-xl text-xs font-bold font-mono';
            }
        }

        const chkAll = document.getElementById(`checkAll_${key}`);
        if (chkAll) {
            const rowCheckboxes = document.querySelectorAll(`.row-chk-${key}`);
            if (rowCheckboxes.length > 0) {
                const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
                chkAll.checked = allChecked;
                chkAll.indeterminate = !allChecked && someChecked;
            } else {
                chkAll.checked = false;
                chkAll.indeterminate = false;
            }
        }
    }

    function toggleSelectAllPage(key) {
        if (!adminSelectedItems[key]) return;
        const rowCheckboxes = document.querySelectorAll(`.row-chk-${key}`);
        if (rowCheckboxes.length === 0) return;

        const anyUnchecked = Array.from(rowCheckboxes).some(cb => !cb.checked);
        const targetState = anyUnchecked;

        rowCheckboxes.forEach(cb => {
            cb.checked = targetState;
            const val = String(cb.value);
            if (targetState) {
                adminSelectedItems[key].add(val);
            } else {
                adminSelectedItems[key].delete(val);
            }
        });

        updateSelectedBadge(key);
    }

    function deselectAll(key) {
        if (!adminSelectedItems[key]) return;
        adminSelectedItems[key].clear();
        document.querySelectorAll(`.row-chk-${key}`).forEach(cb => cb.checked = false);
        updateSelectedBadge(key);
    }

    function onRowCheckboxChange(key, id, checked) {
        if (!adminSelectedItems[key]) return;
        const strId = String(id);
        if (checked) {
            adminSelectedItems[key].add(strId);
        } else {
            adminSelectedItems[key].delete(strId);
        }
        updateSelectedBadge(key);
    }

    // =========================================================
    // PAGINATION & LIVE SEARCH HELPER UNTUK TABEL ADMIN
    // =========================================================
    const adminPagination = {
        users: { page: 1, pageSize: 6, data: [], rawData: [] },
        members: { page: 1, pageSize: 6, data: [], rawData: [] },
        customers: { page: 1, pageSize: 6, data: [], rawData: [] },
        closings: { page: 1, pageSize: 6, data: [], rawData: [] },
        advances: { page: 1, pageSize: 8, data: [], rawData: [] },
        trash: { page: 1, pageSize: 6, data: [], rawData: [] },
        archives: { page: 1, pageSize: 5, data: [], rawData: [] },
        journals: { page: 1, pageSize: 8, data: [], rawData: [] }
    };

    function filterAdminTable(key) {
        const inputId = `search${key.charAt(0).toUpperCase() + key.slice(1)}Input`;
        const clearBtnId = `clear${key.charAt(0).toUpperCase() + key.slice(1)}Btn`;
        const input = document.getElementById(inputId);
        const clearBtn = document.getElementById(clearBtnId);
        const q = input ? input.value.trim().toLowerCase() : '';
        if (clearBtn) clearBtn.classList.toggle('hidden', !q);

        const raw = adminPagination[key].rawData || [];
        if (!q) {
            adminPagination[key].data = [...raw];
        } else {
            adminPagination[key].data = raw.filter(item => {
                return Object.values(item).some(val => {
                    if (val === null || val === undefined) return false;
                    if (typeof val === 'object') {
                        return Object.values(val).some(v => v !== null && v !== undefined && String(v).toLowerCase().includes(q));
                    }
                    return String(val).toLowerCase().includes(q);
                });
            });
        }

        adminPagination[key].page = 1;
        if (key === 'users') renderUsersTable();
        if (key === 'members') renderMembersTable();
        if (key === 'customers') renderCustomersTable();
        if (key === 'closings') renderClosingsTable();
        if (key === 'advances') renderAdvancesTable();
        if (key === 'trash') renderTrashTable();
        if (key === 'archives') renderArchivesTable();
        if (key === 'journals') renderJournalsTable();
    }

    function clearAdminSearch(key) {
        const input = document.getElementById(`search${key.charAt(0).toUpperCase() + key.slice(1)}Input`);
        if (input) {
            input.value = '';
            filterAdminTable(key);
            input.focus();
        }
    }

    function renderAdminPagination(key, containerId) {
        const p = adminPagination[key];
        const container = document.getElementById(containerId);
        if (!container) return;
        const total = p.data.length;
        const totalPages = Math.ceil(total / p.pageSize) || 1;

        if (total === 0) {
            container.innerHTML = '';
            return;
        }

        const start = (p.page - 1) * p.pageSize + 1;
        const end = Math.min(p.page * p.pageSize, total);

        container.innerHTML = `
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500 pt-3 border-t border-slate-100 mt-2">
                <div>Menampilkan <b>${start} - ${end}</b> dari <b>${total}</b> data</div>
                <div class="flex items-center gap-1.5">
                    <button ${p.page <= 1 ? 'disabled' : ''} onclick="changeAdminPage('${key}', ${p.page - 1})" class="px-3 py-1.5 rounded-lg border font-bold transition ${p.page <= 1 ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300 shadow-xs'}"> Sebelumnya
                    </button>
                    <span class="px-3 py-1.5 bg-slate-100 font-mono font-bold text-slate-700 rounded-lg text-xs">
                        ${p.page} / ${totalPages}
                    </span>
                    <button ${p.page >= totalPages ? 'disabled' : ''} onclick="changeAdminPage('${key}', ${p.page + 1})" class="px-3 py-1.5 rounded-lg border font-bold transition ${p.page >= totalPages ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : 'bg-white text-slate-700 hover:bg-slate-50 border-slate-300 shadow-xs'}"> Selanjutnya 
                    </button>
                </div>
            </div>
        `;
    }

    function changeAdminPage(key, newPage) {
        const p = adminPagination[key];
        const totalPages = Math.ceil(p.data.length / p.pageSize) || 1;
        if (newPage < 1 || newPage > totalPages) return;
        p.page = newPage;
        if (key === 'users') renderUsersTable();
        if (key === 'members') renderMembersTable();
        if (key === 'customers') renderCustomersTable();
        if (key === 'closings') renderClosingsTable();
        if (key === 'trash') renderTrashTable();
        if (key === 'archives') renderArchivesTable();
        if (key === 'journals') renderJournalsTable();
    }

    // ================= 1. LOAD USERS =================
    async function loadUsers() {
        const res = await fetchAPI('/admin/users');
        if (res && res.data && res.data.success) {
            adminPagination.users.rawData = res.data.data || [];
            filterAdminTable('users');
        }
    }

    function renderUsersTable() {
        const tbody = document.getElementById('usersTableBody');
        const p = adminPagination.users;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-6 text-slate-400 text-xs">Belum ada akun petugas.</td></tr>';
            renderAdminPagination('users', 'usersPagination');
            updateSelectedBadge('users');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(u => {
            const isBlocked = (u.status !== 'active');
            const safeName = (u.name || '').replace(/'/g, "\\'");
            return `
            <tr class="border-b hover:bg-slate-50 transition">
                <td class="py-3 px-3 text-center">
                    <input type="checkbox" class="row-chk-users rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" value="${u.id}" ${adminSelectedItems.users.has(String(u.id)) ? 'checked' : ''} onchange="onRowCheckboxChange('users', ${u.id}, this.checked)">
                </td>
                <td class="py-3 px-4 font-bold text-slate-800">
                    ${u.name}
                    <span class="block text-[11px] text-slate-400 font-normal font-mono">${u.email || '<em class="text-slate-300 italic">Email belum diatur</em>'}</span>
                </td>
                <td class="py-3 px-4 font-mono text-xs text-slate-600">${u.username}</td>
                <td class="py-3 px-4">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase ${
                        u.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                        u.role === 'teller' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-800'
                    }">
                        ${u.role}
                    </span>
                </td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold ${
                        !isBlocked ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'
                    }">
                        ${!isBlocked ? ' Aktif' : ' Diblokir'}
                    </span>
                </td>
                <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                    <button onclick='openEditUserModal(${JSON.stringify(u)})' class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition"> Edit
                    </button>
                    ${!isBlocked ? `
                        <button onclick="toggleUserStatus(${u.id}, '${u.username}', '${safeName}', '${u.role}', 'active')" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition" title="Blokir akses login petugas"> Blokir
                        </button>
                    ` : `
                        <button onclick="toggleUserStatus(${u.id}, '${u.username}', '${safeName}', '${u.role}', 'inactive')" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition" title="Buka blokir akses login petugas"> Buka Blokir
                        </button>
                    `}
                </td>
            </tr>
            `;
        }).join('');

        renderAdminPagination('users', 'usersPagination');
        updateSelectedBadge('users');
    }

    function openAddUserModal() {
        document.getElementById('modalAddUser').classList.remove('hidden');
    }

    async function submitAddUser(e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('addUserName').value,
            username: document.getElementById('addUserUsername').value,
            email: document.getElementById('addUserEmail').value,
            password: document.getElementById('addUserPassword').value,
            role: document.getElementById('addUserRole').value,
        };

        const res = await fetchAPI('/admin/users', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.data.message, timer: 1200, showConfirmButton: false });
            closeModal('modalAddUser');
            loadUsers();
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.data && res.data.errors) ? Object.values(res.data.errors)[0][0] : 'Gagal membuat user' });
        }
    }

    function openEditUserModal(u) {
        document.getElementById('editUserId').value = u.id;
        document.getElementById('editUserName').value = u.name;
        document.getElementById('editUserUsername').value = u.username;
        document.getElementById('editUserEmail').value = u.email || '';
        document.getElementById('editUserPassword').value = '';
        document.getElementById('editUserRole').value = u.role === 'supervisor' ? 'admin' : u.role;
        document.getElementById('editUserStatus').value = u.status;
        document.getElementById('modalEditUser').classList.remove('hidden');
    }

    async function submitEditUser(e) {
        e.preventDefault();
        const id = document.getElementById('editUserId').value;
        const payload = {
            name: document.getElementById('editUserName').value,
            username: document.getElementById('editUserUsername').value,
            email: document.getElementById('editUserEmail').value,
            role: document.getElementById('editUserRole').value,
            status: document.getElementById('editUserStatus').value,
        };
        const pass = document.getElementById('editUserPassword').value;
        if (pass) payload.password = pass;

        const res = await fetchAPI(`/admin/users/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil', text: res.data.message, timer: 1200, showConfirmButton: false });
            closeModal('modalEditUser');
            loadUsers();
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: (res && res.data && res.data.errors) ? Object.values(res.data.errors)[0][0] : 'Gagal memperbarui user' });
        }
    }

    async function toggleUserStatus(id, username, name, role, currentStatus) {
        const isCurrentlyActive = (currentStatus === 'active');
        const actionText = isCurrentlyActive ? 'Blokir' : 'Buka Blokir';
        const nextStatus = isCurrentlyActive ? 'inactive' : 'active';

        Swal.fire({
            title: `${actionText} Petugas?`,
            text: isCurrentlyActive 
                ? `Petugas [${name}] akan diblokir dan tidak dapat login ke sistem.` 
                : `Blokir akan dibuka dan petugas [${name}] dapat login kembali.`,
            icon: isCurrentlyActive ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: isCurrentlyActive ? '#ef4444' : '#10b981',
            confirmButtonText: `Ya, ${actionText}`,
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetchAPI(`/admin/users/${id}`, {
                    method: 'PUT',
                    body: JSON.stringify({ username, name, role, status: nextStatus })
                });
                if (res && res.data && res.data.success) {
                    Swal.fire({ 
                        icon: 'success', 
                        title: `Berhasil`, 
                        text: res.data.message || `Status petugas berhasil diperbarui`, 
                        timer: 1200, 
                        showConfirmButton: false 
                    });
                    loadUsers();
                } else {
                    Swal.fire({ 
                        icon: 'error', 
                        title: 'Gagal', 
                        text: (res && res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || `Gagal memproses ${actionText.toLowerCase()}` 
                    });
                }
            }
        });
    }

    async function deleteUser(id) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Dapat Dihapus',
            text: 'Kebijakan Sistem: Akun petugas tidak dapat dihapus. Silakan gunakan menu Blokir akun.'
        });
    }

    // ================= 2. LOAD MEMBERS =================
    async function loadMembers() {
        const res = await fetchAPI('/admin/members');
        if (res && res.data && res.data.success) {
            adminPagination.members.rawData = res.data.data.data || res.data.data || [];
            filterAdminTable('members');
        }
    }

    function renderMembersTable() {
        const tbody = document.getElementById('membersTableBody');
        const p = adminPagination.members;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Belum ada data anggota siswa. Klik Tambah Anggota.</td></tr>';
            renderAdminPagination('members', 'membersPagination');
            updateSelectedBadge('members');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(m => {
            const isActive = (m.status === 'active_account');
            const safeName = (m.name || '').replace(/'/g, "\\'");
            const safeClass = (m.class_name || '').replace(/'/g, "\\'");
            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-3 text-center">
                        <input type="checkbox" class="row-chk-members rounded text-indigo-600 focus:ring-indigo-500 w-4 h-4 cursor-pointer" value="${m.id}" ${adminSelectedItems.members.has(String(m.id)) ? 'checked' : ''} onchange="onRowCheckboxChange('members', ${m.id}, this.checked)">
                    </td>
                    <td class="py-3 px-4 font-mono font-bold text-slate-800">${m.nis}</td>
                    <td class="py-3 px-4 font-bold text-slate-900">
                        ${m.name}
                        <span class="block text-[11px] font-mono font-normal ${m.email ? 'text-indigo-600' : 'text-slate-400 italic'}">
                            ${m.email ? `${m.email}` : 'Email belum ada'}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs text-slate-600">${m.class_name}</td>
                    <td class="py-3 px-4 font-mono text-xs text-slate-600">${m.graduation_year}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase ${
                            isActive ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                        }">
                            ${isActive ? ' Rekening Aktif' : ' Belum Aktif'}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                        <button onclick='openEditMemberModal(${JSON.stringify(m)})' class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition" title="Ubah Data Siswa (Nama, Kelas, Tahun Lulus)"> Edit
                        </button>
                        <button onclick="editMemberEmail(${m.id}, '${safeName}', '${(m.email || '').replace(/'/g, "\\'")}')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition" title="Ubah atau Daftarkan Gmail Siswa"> Email
                        </button>
                        <button onclick="openHoldBackModal(${m.id}, '${safeName}', '${m.nis}', '${safeClass}', ${m.graduation_year || new Date().getFullYear()}, ${isActive})" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold transition inline-flex items-center" title="Tinggal Kelas / Tidak Naik Kelas"> Tinggal Kelas
                        </button>
                        ${!isActive ? `
                            <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-semibold inline-flex items-center" title="Aktivasi rekening nasabah dilakukan oleh Petugas Teller"> Aktivasi di Teller
                            </span>
                        ` : ''}
                    </td>
                </tr>
            `;
        }).join('');

        renderAdminPagination('members', 'membersPagination');
        updateSelectedBadge('members');
    }

    function openAddMemberModal() {
        document.getElementById('modalAddMember').classList.remove('hidden');
    }

    function openImportExcelModal() {
        const fileInput = document.getElementById('importExcelFile');
        if (fileInput) fileInput.value = '';
        document.getElementById('modalImportExcel').classList.remove('hidden');
    }

    async function submitImportExcel(e) {
        e.preventDefault();
        const fileInput = document.getElementById('importExcelFile');
        if (!fileInput.files || fileInput.files.length === 0) {
            Swal.fire('Peringatan', 'Silakan pilih berkas Excel (.xlsx / .xls / .csv) terlebih dahulu!', 'warning');
            return;
        }

        const btn = document.getElementById('btnSubmitImportExcel');
        btn.disabled = true;
        btn.innerHTML = ' Mengimpor...';

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);

        try {
            const token = localStorage.getItem('bank_token') || localStorage.getItem('token');
            if (!token) {
                btn.disabled = false;
                btn.innerHTML = '<span>Upload & Impor Anggota</span>';
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Telah Berakhir',
                    text: 'Sesi login Anda tidak ditemukan. Silakan login kembali.',
                    confirmButtonColor: '#4f46e5'
                }).then(() => {
                    window.location.href = '/login';
                });
                return;
            }

            const res = await fetch('/api/admin/members/import-excel', {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await res.json().catch(() => ({}));
            btn.disabled = false;
            btn.innerHTML = '<span>Upload & Impor Anggota</span>';

            if (res.status === 401) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesi Telah Berakhir',
                    text: 'Token autentikasi kedaluwarsa. Silakan login ulang.',
                    confirmButtonColor: '#4f46e5'
                }).then(() => {
                    localStorage.clear();
                    window.location.href = '/login';
                });
                return;
            }

            if (res.ok && data.success) {
                closeModal('modalImportExcel');
                fileInput.value = ''; // Reset input file
                let detailHtml = `<p class="text-sm font-medium mb-2">${data.message}</p>`;
                if (data.errors && data.errors.length > 0) {
                    detailHtml += `<div class="max-h-40 overflow-y-auto text-left text-xs bg-amber-50 p-2.5 rounded-xl border border-amber-200 text-amber-800 space-y-1">
                        <strong>Catatan Baris Dilewati:</strong>
                        <ul class="list-disc pl-4 mt-1">
                            ${data.errors.map(err => `<li>${err}</li>`).join('')}
                        </ul>
                    </div>`;
                }
                Swal.fire({
                    icon: 'success',
                    title: 'Impor Excel Berhasil!',
                    html: detailHtml,
                    confirmButtonColor: '#10b981'
                });
                loadMembers();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Impor Excel',
                    text: data.message || 'Terjadi kesalahan saat memproses file Excel.'
                });
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = ' <span>Upload & Impor Anggota</span>';
            Swal.fire('Error', 'Gagal menghubungi server: ' + err.message, 'error');
        }
    }

    async function triggerJulyPromotionAdmin() {
        Swal.fire({
            title: 'Jalankan Kenaikan Kelas Juli?',
            text: 'Semua siswa aktif akan dinaikkan kelasnya (misal X -> XI, XI -> XII, dan kelas 12/XII akan dinyatakan Lulus). Proses ini normalnya berjalan otomatis 1 tahun sekali pada bulan Juli.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Ya, Jalankan Sekarang',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Kenaikan Kelas...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                const res = await fetchAPI('/admin/members/promote-july', {
                    method: 'POST',
                    body: JSON.stringify({ force: true })
                });

                if (res && res.data && res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Kenaikan Kelas Selesai!',
                        text: res.data.message,
                        confirmButtonColor: '#10b981'
                    });
                    loadMembers();
                    if (typeof loadCustomers === 'function') loadCustomers();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Kenaikan Kelas',
                        text: (res && res.data && res.data.message) || 'Terjadi kesalahan sistem.'
                    });
                }
            }
        });
    }

    async function submitAddMember(e) {
        e.preventDefault();
        const payload = {
            nis: document.getElementById('addMemberNis').value,
            name: document.getElementById('addMemberName').value,
            class_name: document.getElementById('addMemberClass').value,
            graduation_year: document.getElementById('addMemberGradYear').value,
            email: document.getElementById('addMemberEmail').value.trim() || null,
        };

        const res = await fetchAPI('/admin/members', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Anggota Ditambahkan!',
                text: res.data.message,
                confirmButtonColor: '#4f46e5'
            });
            closeModal('modalAddMember');
            document.getElementById('addMemberNis').value = '';
            document.getElementById('addMemberName').value = '';
            document.getElementById('addMemberClass').value = '';
            document.getElementById('addMemberEmail').value = '';
            loadMembers();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menambah Anggota',
                text: (res && res.data && res.data.errors) ? Object.values(res.data.errors)[0][0] : ((res && res.data && res.data.message) || 'Terjadi kesalahan')
            });
        }
    }

    function autoCalculateAddGradYear() {
        const val = (document.getElementById('addMemberClass').value || '').trim();
        const currentYear = new Date().getFullYear();
        let gradYear = currentYear + 2;
        if (/\b(12|XII)\b/i.test(val) || /^12|^XII/i.test(val)) {
            gradYear = currentYear;
        } else if (/\b(11|XI)\b/i.test(val) || /^11|^XI/i.test(val)) {
            gradYear = currentYear + 1;
        } else if (/\b(10|X)\b/i.test(val) || /^10|^X/i.test(val)) {
            gradYear = currentYear + 2;
        }
        document.getElementById('addMemberGradYear').value = gradYear;
    }

    function openEditMemberModal(m) {
        document.getElementById('editMemberId').value = m.id;
        document.getElementById('editMemberNis').value = m.nis;
        document.getElementById('editMemberName').value = m.name;
        document.getElementById('editMemberClass').value = m.class_name;
        document.getElementById('editMemberEmail').value = m.email || '';
        document.getElementById('editMemberGradYear').value = m.graduation_year || 2028;
        openModal('modalEditMember');
    }

    function autoCalculateEditGradYear() {
        const val = (document.getElementById('editMemberClass').value || '').trim();
        const currentYear = new Date().getFullYear();
        let gradYear = currentYear + 2;
        if (/\b(12|XII)\b/i.test(val) || /^12|^XII/i.test(val)) {
            gradYear = currentYear;
        } else if (/\b(11|XI)\b/i.test(val) || /^11|^XI/i.test(val)) {
            gradYear = currentYear + 1;
        } else if (/\b(10|X)\b/i.test(val) || /^10|^X/i.test(val)) {
            gradYear = currentYear + 2;
        }
        document.getElementById('editMemberGradYear').value = gradYear;
    }

    async function submitEditMember(e) {
        e.preventDefault();
        const id = document.getElementById('editMemberId').value;
        const payload = {
            nis: document.getElementById('editMemberNis').value.trim(),
            name: document.getElementById('editMemberName').value.trim(),
            class_name: document.getElementById('editMemberClass').value.trim(),
            graduation_year: document.getElementById('editMemberGradYear').value,
            email: document.getElementById('editMemberEmail').value.trim() || null,
        };

        const res = await fetchAPI(`/admin/members/${id}`, { method: 'PUT', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Data Siswa Diperbarui!',
                text: res.data.message,
                confirmButtonColor: '#4f46e5'
            });
            closeModal('modalEditMember');
            loadMembers();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Memperbarui Siswa',
                text: (res && res.data && res.data.errors) ? Object.values(res.data.errors)[0][0] : ((res && res.data && res.data.message) || 'Terjadi kesalahan')
            });
        }
    }

    async function editMemberEmail(id, name, currentEmail) {
        const { value: email } = await Swal.fire({
            title: `Kelola Gmail Siswa`,
            html: `<div class="text-left text-xs text-slate-600 space-y-2 mb-2">
                     <p>Nama Siswa: <strong class="text-slate-800">${name}</strong></p>
                     <p class="text-slate-500">Email ini akan digunakan oleh sistem untuk mengirimkan <strong>Kode Verifikasi OTP Reset Password</strong> dan <strong>PIN Transaksi Tabungan</strong> langsung ke inbox Gmail siswa.</p>
                   </div>`,
            input: 'email',
            inputValue: (currentEmail && currentEmail !== 'null') ? currentEmail : '',
            inputPlaceholder: 'nama.siswa@gmail.com',
            showCancelButton: true,
            confirmButtonText: ' Simpan Email',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#4f46e5',
            inputValidator: (value) => {
                if (value && !value.includes('@')) {
                    return 'Format email tidak valid (harus mengandung @)!';
                }
            }
        });

        if (email !== undefined) {
            const member = adminPagination.members.data.find(m => m.id == id);
            if (!member) return;

            Swal.fire({ title: 'Menyimpan Email...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const payload = {
                nis: member.nis,
                name: member.name,
                class_name: member.class_name,
                graduation_year: member.graduation_year,
                email: email.trim() || null
            };

            const res = await fetchAPI(`/admin/members/${id}`, {
                method: 'PUT',
                body: JSON.stringify(payload)
            });

            if (res && res.data && res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Diperbarui!',
                    text: 'Email siswa berhasil disimpan.',
                    confirmButtonColor: '#10b981'
                });
                loadMembers();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menyimpan',
                    text: (res && res.data && res.data.message) || 'Terjadi kesalahan'
                });
            }
        }
    }

    function openActivateMemberModal(id, name, nis, className) {
        document.getElementById('actMemberId').value = id;
        document.getElementById('actMemberName').innerText = name;
        document.getElementById('actMemberNis').innerText = nis;
        document.getElementById('actMemberClass').innerText = className;
        document.getElementById('actInitialDeposit').value = '10.000';
        if (document.getElementById('actPassword')) document.getElementById('actPassword').value = '';
        if (document.getElementById('actSecurityPin')) document.getElementById('actSecurityPin').value = '';
        document.getElementById('modalActivateMemberAdmin').classList.remove('hidden');
    }

    // =========================================================
    // AKTIVASI PENABUNG: SETORAN MINIMAL 10.000, PASSWORD & PIN
    // =========================================================
    async function submitActivateMember(e) {
        e.preventDefault();
        const id = document.getElementById('actMemberId').value;
        const depositRaw = document.getElementById('actInitialDeposit').value;
        const initial_deposit = unformatRupiahNumber(depositRaw) || 0;
        const password = document.getElementById('actPassword') ? document.getElementById('actPassword').value.trim() : '';
        const security_pin = document.getElementById('actSecurityPin') ? document.getElementById('actSecurityPin').value.trim() : '';

        if (initial_deposit < 10000) {
            Swal.fire({
                icon: 'warning',
                title: 'Setoran Awal Minimal Rp 10.000',
                text: `Untuk mengaktifkan nasabah menjadi penabung, nasabah harus melakukan setoran awal minimal Rp 10.000 (saat ini diinput: Rp ${Number(initial_deposit).toLocaleString('id-ID')}).`,
                confirmButtonColor: '#f59e0b'
            });
            return;
        }

        if (!password || password.length < 6) {
            Swal.fire('Kata Sandi Kurang', 'Kata sandi akun login siswa wajib diisi minimal 6 karakter!', 'warning');
            return;
        }

        if (!security_pin || (!/^\d{6}$/.test(security_pin))) {
            Swal.fire('PIN Tidak Valid', 'PIN Keamanan harus berupa tepat 6 digit angka!', 'warning');
            return;
        }

        const btn = document.getElementById('btnSubmitActivateAdmin');
        btn.disabled = true;
        btn.innerHTML = ' Mengaktifkan...';

        const payload = {
            initial_deposit: Number(initial_deposit),
            password: password,
            security_pin: security_pin,
        };

        const res = await fetchAPI(`/admin/members/${id}/activate`, {
            method: 'POST',
            body: JSON.stringify(payload)
        });

        btn.disabled = false;
        btn.innerHTML = ' <span>Aktifkan Rekening</span>';

        if (res && res.data && res.data.success) {
            closeModal('modalActivateMemberAdmin');
            const saverData = res.data.data;
            const saverName = saverData.name || (saverData.member && saverData.member.name) || document.getElementById('actMemberName').innerText;
            Swal.fire({
                icon: 'success',
                title: 'Rekening Berhasil Diaktifkan!',
                html: `<p class="text-sm font-medium">${res.data.message}</p>
                       <div class="mt-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs text-emerald-900 text-left font-mono">
                           <b>No. Rekening Baru:</b> ${saverData.account_number}<br>
                           <b>Nama Nasabah:</b> ${saverName}<br>
                           <b>Setoran Awal:</b> Rp ${Number(initial_deposit).toLocaleString('id-ID')}
                       </div>`,
                confirmButtonColor: '#10b981'
            });
            loadMembers();
            if (typeof loadCustomers === 'function') loadCustomers();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal Aktivasi Rekening',
                text: (res && res.data && res.data.message) || 'Terjadi kesalahan sistem'
            });
        }
    }

    async function deleteMember(id, name, status) {
        Swal.fire({
            icon: 'info',
            title: 'Tidak Dapat Dihapus',
            text: 'Kebijakan Sistem: Data anggota yang sudah diinput tidak dapat dihapus.'
        });
    }

    // =========================================================
    // FITUR TINGGAL KELAS (UNTUK NASABAH MAUPUN ANGGOTA BELUM AKTIF)
    // =========================================================
    async function openHoldBackModal(memberId, name, nis, currentClass, currentGradYear, isSaver) {
        let cleanClass = (currentClass || '')
            .replace(/^Lulus\s*\(\s*Alumni\s*/i, '')
            .replace(/\)$/, '')
            .replace(/^(Lulus|Alumni)\s*/i, '')
            .trim();

        // Prediksi nama kelas jika turun 1 tingkat
        let demotedPrediction = cleanClass;
        if (cleanClass.match(/\bXII\b/i)) demotedPrediction = cleanClass.replace(/\bXII\b/i, 'XI');
        else if (cleanClass.match(/\bXI\b/i)) demotedPrediction = cleanClass.replace(/\bXI\b/i, 'X');
        else if (cleanClass.match(/\b12\b/)) demotedPrediction = cleanClass.replace(/\b12\b/, '11');
        else if (cleanClass.match(/\b11\b/)) demotedPrediction = cleanClass.replace(/\b11\b/, '10');
        else if (cleanClass.match(/\bIX\b/i)) demotedPrediction = cleanClass.replace(/\bIX\b/i, 'VIII');
        else if (cleanClass.match(/\bVIII\b/i)) demotedPrediction = cleanClass.replace(/\bVIII\b/i, 'VII');

        const curGrad = parseInt(currentGradYear) || (new Date().getFullYear());
        const newGradYear = curGrad + 1;

        const { value: formValues } = await Swal.fire({
            title: `<div class="flex items-center justify-center gap-2 text-slate-800 text-lg font-black"> Penetapan Tinggal Kelas</div>`,
            html: `
                <div class="text-left space-y-3 text-xs">
                    <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl">
                        <div class="font-bold text-slate-800 text-sm mb-1">${name} <span class="font-mono text-slate-500 font-normal">(${nis})</span></div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold ${isSaver ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'}">
                                ${isSaver ? ' Nasabah Penabung' : ' Anggota Belum Buka Rekening'}
                            </span>
                            <span class="text-slate-600">Kelas Saat Ini: <b>${cleanClass}</b></span>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Pilih Mode Kelas:</label>
                        <select id="swalHoldOption" onchange="window.onHoldOptionChange()" class="w-full px-3 py-2 border rounded-xl font-medium focus:ring-2 focus:ring-amber-500 text-xs bg-slate-50">
                            <option value="same_grade">1. Tetap di Tingkat Saat Ini (${cleanClass})</option>
                            ${demotedPrediction !== cleanClass ? `<option value="demote">2. Turun 1 Tingkat (${demotedPrediction})</option>` : ''}
                            <option value="custom">3. Ubah Kelas Kustom Manual</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Nama Kelas Baru:</label>
                        <input type="text" id="swalTargetClass" value="${cleanClass}" class="w-full px-3 py-2 border rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-amber-500 text-xs">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Tahun Kelulusan Baru (+1 Tahun):</label>
                        <input type="number" id="swalTargetGradYear" value="${newGradYear}" class="w-full px-3 py-2 border rounded-xl font-mono font-bold text-slate-800 focus:ring-2 focus:ring-amber-500 text-xs">
                        <span class="text-[10px] text-slate-400 mt-0.5 block">Otomatis bertambah 1 tahun dari ${curGrad} menjadi ${newGradYear}.</span>
                    </div>
                </div>
            `,
            didOpen: () => {
                window.onHoldOptionChange = function() {
                    const opt = document.getElementById('swalHoldOption').value;
                    const input = document.getElementById('swalTargetClass');
                    if (opt === 'same_grade') {
                        input.value = cleanClass;
                    } else if (opt === 'demote') {
                        input.value = demotedPrediction;
                    } else {
                        input.value = '';
                        input.focus();
                    }
                };
            },
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            confirmButtonText: ' Simpan Tinggal Kelas',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                const targetClass = document.getElementById('swalTargetClass').value.trim();
                const targetGrad = document.getElementById('swalTargetGradYear').value.trim();
                if (!targetClass) {
                    Swal.showValidationMessage('Nama kelas baru wajib diisi!');
                    return false;
                }
                return {
                    class_name: targetClass,
                    graduation_year: parseInt(targetGrad) || newGradYear
                };
            }
        });

        if (formValues) {
            Swal.fire({ title: 'Memproses Tinggal Kelas...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

            const res = await fetchAPI(`/admin/members/${memberId}/hold-back`, {
                method: 'POST',
                body: JSON.stringify(formValues)
            });

            if (res && res.data && res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: res.data.message,
                    confirmButtonColor: '#10b981'
                });
                loadMembers();
                loadCustomers();
            } else {
                Swal.fire('Gagal', (res && res.data && (res.data.message || (res.data.errors && Object.values(res.data.errors)[0][0]))) || 'Gagal memproses tinggal kelas', 'error');
            }
        }
    }

    // ================= 3. LOAD SAVERS (PENABUNG) =================
    async function loadCustomers() {
        const res = await fetchAPI('/admin/customers');
        if (res && res.data && res.data.success) {
            adminPagination.customers.rawData = res.data.data || [];
            filterAdminTable('customers');
        }
    }

    function renderCustomersTable() {
        const tbody = document.getElementById('customersTableBody');
        const p = adminPagination.customers;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-slate-400 text-xs">Belum ada penabung aktif. Arahkan anggota untuk aktivasi di loket teller atau tombol Aktivasi.</td></tr>';
            renderAdminPagination('customers', 'customersPagination');
            updateSelectedBadge('customers');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(c => {
            const safeName = (c.name || '').replace(/'/g, "\\'");
            const safeClass = (c.class_name || '').replace(/'/g, "\\'");
            const isAct = (c.status === 'active');

            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-3 text-center">
                        <input type="checkbox" class="row-chk-customers rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 cursor-pointer" value="${c.id}" ${adminSelectedItems.customers.has(String(c.id)) ? 'checked' : ''} onchange="onRowCheckboxChange('customers', ${c.id}, this.checked)">
                    </td>
                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">${c.account_number}</td>
                    <td class="py-3 px-4 font-mono text-xs text-slate-600">${c.nis}</td>
                    <td class="py-3 px-4 font-bold text-slate-800">${c.name}</td>
                    <td class="py-3 px-4 text-xs text-slate-600">${c.class_name}</td>
                    <td class="py-3 px-4 text-right font-black text-emerald-700">Rp ${Number(c.balance).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase ${isAct ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'}">
                            ${c.status}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                        <button onclick="showCardQR('${c.account_number}', '${c.nis}', '${safeName}', '${safeClass}')" class="px-2.5 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition inline-flex items-center"> QR
                        </button>
                        <button onclick="openHoldBackModal(${c.member_id || c.id}, '${safeName}', '${c.nis}', '${safeClass}', ${(c.member ? c.member.graduation_year : (c.graduation_year || new Date().getFullYear()))}, true)" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold transition inline-flex items-center" title="Tinggal Kelas / Tidak Naik Kelas"> Tinggal Kelas
                        </button>
                        ${isAct ? `
                            <button onclick="toggleSaverStatus(${c.id}, 'inactive', '${safeName}')" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-lg text-xs font-bold transition inline-flex items-center"> Nonaktifkan
                            </button>
                        ` : `
                            <button onclick="toggleSaverStatus(${c.id}, 'active', '${safeName}')" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition inline-flex items-center"> Aktifkan
                            </button>
                        `}
                    </td>
                </tr>
            `;
        }).join('');

        renderAdminPagination('customers', 'customersPagination');
        updateSelectedBadge('customers');
    }

    // =========================================================
    // REVISI 6: STATUS AKTIF / NON-AKTIF REKENING PENABUNG (TANPA HAPUS)
    // =========================================================
    async function toggleSaverStatus(id, newStatus, name) {
        const actionLabel = (newStatus === 'active') ? 'Aktifkan' : 'Nonaktifkan';
        Swal.fire({
            title: `${actionLabel} Rekening [${name}]?`,
            text: `Status rekening penabung akan diubah menjadi ${newStatus === 'active' ? 'AKTIF' : 'NONAKTIF'}. Seluruh riwayat saldo dan audit perbankan tetap aman.`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: newStatus === 'active' ? '#059669' : '#f59e0b',
            confirmButtonText: `Ya, ${actionLabel}`,
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetchAPI(`/admin/customers/${id}/toggle-status`, {
                    method: 'POST',
                    body: JSON.stringify({ status: newStatus })
                });
                if (res && res.data && res.data.success) {
                    Swal.fire('Berhasil!', res.data.message, 'success');
                    loadCustomers();
                } else {
                    Swal.fire('Gagal!', (res && res.data && res.data.message) || 'Gagal memperbarui status penabung', 'error');
                }
            }
        });
    }


    function showCardQR(account, nis, name, className) {
        document.getElementById('cardNama').innerText = name;
        document.getElementById('cardRekening').innerText = account;
        document.getElementById('cardKelas').innerText = `Kelas: ${className}`;
        document.getElementById('cardNis').innerText = `NIS: ${nis}`;

        const box = document.getElementById('qrcodeBox');
        box.innerHTML = '';
        new QRCode(box, {
            text: account,
            width: 80,
            height: 80,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H
        });

        document.getElementById('modalCardQR').classList.remove('hidden');
    }

    // ================= 4. LOAD MONTHLY REPORT =================
    let monthlyDailyRecords = [];

    async function loadMonthlyReport() {
        const m = document.getElementById('selReportMonth').value;
        const y = document.getElementById('selReportYear').value;
        const res = await fetchAPI(`/admin/reports/monthly?month=${m}&year=${y}`);

        if (res && res.data && res.data.success) {
            const d = res.data.data;
            document.getElementById('repTotalDeposit').innerText = 'Rp ' + Number(d.total_deposit).toLocaleString('id-ID');
            document.getElementById('repTotalWithdraw').innerText = 'Rp ' + Number(d.total_withdraw).toLocaleString('id-ID');
            document.getElementById('repNetCashflow').innerText = 'Rp ' + Number(d.net_cashflow).toLocaleString('id-ID');
            document.getElementById('repTotalTx').innerText = d.total_tx + ' Trx';

            monthlyDailyRecords = d.daily_records || [];
            filterMonthlyDailyTable();
        }
    }

    function filterMonthlyDailyTable() {
        const input = document.getElementById('searchMonthlyInput');
        const clearBtn = document.getElementById('clearMonthlyBtn');
        const q = input ? input.value.trim().toLowerCase() : '';
        if (clearBtn) clearBtn.classList.toggle('hidden', !q);

        const tbody = document.getElementById('monthlyDailyBody');
        const filtered = !q ? monthlyDailyRecords : monthlyDailyRecords.filter(r => {
            return String(r.tx_date || '').toLowerCase().includes(q) ||
                   String(r.total_deposit || '').includes(q) ||
                   String(r.total_withdraw || '').includes(q) ||
                   String(r.total_count || '').includes(q);
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">${q ? 'Tidak ada data tanggal yang cocok dengan pencarian.' : 'Tidak ada mutasi transaksi pada periode ini.'}</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(r => `
            <tr class="border-b hover:bg-slate-50 transition">
                <td class="py-3 px-4 text-center font-mono font-bold text-slate-800">${r.tx_date}</td>
                <td class="py-3 px-4 text-right font-black text-emerald-600">Rp ${Number(r.total_deposit).toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-right font-black text-rose-600">Rp ${Number(r.total_withdraw).toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-right font-black text-indigo-700">Rp ${(Number(r.total_deposit) - Number(r.total_withdraw)).toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 text-center font-bold">${r.total_count}</td>
            </tr>
        `).join('');
    }

    function clearMonthlySearch() {
        const input = document.getElementById('searchMonthlyInput');
        if (input) {
            input.value = '';
            filterMonthlyDailyTable();
            input.focus();
        }
    }

    function printMonthlyReport() {
        const m = document.getElementById('selReportMonth').value;
        const y = document.getElementById('selReportYear').value;
        window.open(`/reports/monthly/print?month=${m}&year=${y}`, '_blank');
    }

    // ================= 5. LOAD JOURNALS =================
    async function loadJournals() {
        const filterDate = document.getElementById('journalFilterDate').value;
        const endpoint = filterDate ? `/admin/audit-journals?date=${filterDate}` : '/admin/audit-journals';
        const res = await fetchAPI(endpoint);

        if (res && res.data && res.data.success) {
            const summary = res.data.summary;
            adminPagination.journals.rawData = res.data.data.data || res.data.data || [];

            document.getElementById('totalDebitText').innerText = `Rp ${Number(summary.total_debit).toLocaleString('id-ID')}`;
            document.getElementById('totalCreditText').innerText = `Rp ${Number(summary.total_credit).toLocaleString('id-ID')}`;

            const card = document.getElementById('balanceCard');
            const txt = document.getElementById('balanceStatusText');
            if (summary.is_balanced) {
                card.className = 'bg-emerald-50 rounded-2xl p-5 border border-emerald-200 shadow-sm flex items-center justify-between';
                txt.innerText = 'BALANCE (SEIMBANG)';
                txt.className = 'text-xl font-black text-emerald-800 mt-1';
            } else {
                card.className = 'bg-rose-50 rounded-2xl p-5 border border-rose-200 shadow-sm flex items-center justify-between';
                txt.innerText = 'TIDAK BALANCE (SELISIH)';
                txt.className = 'text-xl font-black text-rose-800 mt-1';
            }

            filterAdminTable('journals');
        }
    }

    function renderJournalsTable() {
        const tbody = document.getElementById('journalsTableBody');
        const p = adminPagination.journals;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">Tidak ada data jurnal akuntansi.</td></tr>';
            renderAdminPagination('journals', 'journalsPagination');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(j => `
            <tr class="border-b hover:bg-slate-50 transition">
                <td class="py-3 px-4 text-xs font-bold text-slate-700">${new Date(j.created_at).toLocaleString('id-ID')}</td>
                <td class="py-3 px-4 font-mono font-bold text-indigo-600">${j.account_code}</td>
                <td class="py-3 px-4 text-slate-800 font-semibold">${j.account_name}</td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2 py-0.5 rounded-full text-xs font-black uppercase ${j.position === 'debit' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'}">
                        ${j.position}
                    </span>
                </td>
                <td class="py-3 px-4 text-right font-black text-slate-900">Rp ${Number(j.amount).toLocaleString('id-ID')}</td>
            </tr>
        `).join('');

        renderAdminPagination('journals', 'journalsPagination');
    }

    // =========================================================
    // REVISI 1: OTORISASI CLOSING KAS DENGAN TOMBOL TOLAK & ALASAN
    // =========================================================
    async function loadAdminClosings() {
        const res = await fetchAPI('/admin/closings');
        if (res && res.data && res.data.success) {
            adminPagination.closings.rawData = res.data.data.data || res.data.data || [];
            filterAdminTable('closings');
        }
    }

    function renderClosingsTable() {
        const tbody = document.getElementById('adminClosingsBody');
        const p = adminPagination.closings;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-6 text-slate-400 text-xs">Belum ada laporan closing yang diajukan oleh teller.</td></tr>';
            renderAdminPagination('closings', 'closingsPagination');
            updateSelectedBadge('closings');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(c => {
            const diff = Number(c.difference);
            const isApproved = (c.status === 'approved');
            const isRejected = (c.status === 'rejected');
            const isDispute = (c.status === 'dispute' || diff !== 0);
            const safeTellerName = (c.teller ? c.teller.name : 'Teller').replace(/'/g, "\\'");

            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-3 text-center">
                        <input type="checkbox" class="row-chk-closings rounded text-amber-600 focus:ring-amber-500 w-4 h-4 cursor-pointer" value="${c.id}" ${adminSelectedItems.closings.has(String(c.id)) ? 'checked' : ''} onchange="onRowCheckboxChange('closings', ${c.id}, this.checked)">
                    </td>
                    <td class="py-3 px-4 text-xs font-bold text-slate-700">${c.closing_date}</td>
                    <td class="py-3 px-4 font-semibold text-slate-800">${c.teller ? c.teller.name : '-'}</td>
                    <td class="py-3 px-4 text-right font-bold text-slate-700">Rp ${Number(c.system_balance).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right font-bold text-indigo-700">Rp ${Number(c.physical_balance).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-right font-black ${diff === 0 ? 'text-emerald-600' : 'text-rose-600'}">
                        ${diff === 0 ? 'Klop (Rp 0)' : (diff > 0 ? '+Rp ' : '-Rp ') + Math.abs(diff).toLocaleString('id-ID')}
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase ${
                            isApproved ? 'bg-emerald-100 text-emerald-800' :
                            isRejected ? 'bg-rose-100 text-rose-800' :
                            isDispute ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800'
                        }">
                            ${isApproved ? ' Disetujui' :
                              isRejected ? ' Ditolak' : c.status}
                        </span>
                        ${isRejected && c.rejection_reason ? `
                            <span class="block text-[10px] text-rose-600 font-medium mt-1 max-w-[180px] mx-auto truncate" title="${c.rejection_reason}"> Alasan: ${c.rejection_reason}
                            </span>
                        ` : ''}
                    </td>
                    <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                        <button onclick="auditDetail(${c.id})" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition inline-flex items-center"> Audit
                        </button>
                        <a href="/reports/closing/${c.id}/print" target="_blank" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition inline-flex items-center"> Cetak PDF
                        </a>
                        ${!isApproved ? `
                            <button onclick="approveClosing(${c.id}, ${diff})" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow inline-flex items-center"> Approve
                            </button>
                            <button onclick="promptRejectClosing(${c.id}, '${safeTellerName}')" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold transition shadow inline-flex items-center"> Tolak
                            </button>
                        ` : `
                            <span class="text-xs text-emerald-600 font-bold ml-2 inline-flex items-center"> Otorisasi Selesai
                            </span>
                        `}
                    </td>
                </tr>
            `;
        }).join('');

        renderAdminPagination('closings', 'closingsPagination');
        updateSelectedBadge('closings');
    }

    // =========================================================
    // FITUR ACCEPT ALL: OTORISASI SEMUA CLOSING PENDING SEKALIGUS
    // =========================================================
    async function acceptAllPendingClosings() {
        const pending = (adminPagination.closings.rawData || []).filter(c => c.status !== 'approved');
        if (pending.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Semua Sudah Disetujui',
                text: 'Tidak ada laporan closing kas yang berstatus pending atau butuh otorisasi.',
                confirmButtonColor: '#4f46e5'
            });
            return;
        }

        const result = await Swal.fire({
            title: 'Accept All Closing Kas?',
            html: `
                <div class="text-left text-xs space-y-2">
                    <p>Apakah Anda yakin ingin menyetujui sekaligus <b>${pending.length} laporan closing kas loket teller</b> yang berstatus pending?</p>
                    <div class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-900 font-medium"> Saldo fisik kas loket teller akan langsung dipindahkan dan diamankan ke Kas Brankas Utama (Akun 102).
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: ` Ya, Setujui Semua (${pending.length})`,
            cancelButtonText: 'Batal'
        });

        if (result.isConfirmed) {
            Swal.fire({
                title: 'Memproses Otorisasi Massal...',
                text: 'Harap tunggu...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetchAPI('/admin/closings/approve-all', {
                method: 'POST',
                body: JSON.stringify({ notes: 'Disetujui serentak via menu Accept All Administrator.' })
            });

            if (res && res.data && res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Otorisasi Berhasil!',
                    text: res.data.message,
                    confirmButtonColor: '#059669'
                });
                loadAdminClosings();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Otorisasi',
                    text: (res && res.data && res.data.message) || 'Terjadi kesalahan sistem saat memproses otorisasi massal.'
                });
            }
        }
    }

    async function promptRejectClosing(closingId, tellerName) {
        const { value: reason } = await Swal.fire({
            title: `Tolak Closing Kas [${tellerName}]?`,
            text: 'Admin wajib memasukkan alasan penolakan mandiri. Catatan ini akan otomatis masuk dan tampil di role teller yang melapor kas tersebut.',
            input: 'textarea',
            inputLabel: 'Catatan / Alasan Penolakan:',
            inputPlaceholder: 'Tuliskan alasan penolakan secara jelas (contoh: Uang fisik kurang Rp 50.000, harap hitung ulang fisik kas di laci)...',
            inputAttributes: { rows: 3 },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Tolak Closing Sekarang',
            cancelButtonText: 'Batal',
            inputValidator: (val) => {
                if (!val || !val.trim()) {
                    return 'Alasan penolakan closing kas wajib diisi mandiri oleh Admin!';
                }
            }
        });

        if (reason) {
            const res = await fetchAPI(`/admin/closings/${closingId}/reject`, {
                method: 'POST',
                body: JSON.stringify({ rejection_reason: reason.trim() })
            });

            if (res && res.data && res.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Closing Berhasil Ditolak',
                    text: res.data.message,
                    confirmButtonColor: '#e11d48'
                });
                loadAdminClosings();
            } else {
                Swal.fire('Gagal Menolak', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem', 'error');
            }
        }
    }

    let currentAuditTransactions = [];

    async function auditDetail(closingId) {
        document.getElementById('btnModalPrintAudit').href = `/reports/closing/${closingId}/print`;
        const res = await fetchAPI(`/admin/closings/${closingId}/audit`);
        if (res && res.data && res.data.success) {
            const closing = res.data.data.closing;
            currentAuditTransactions = res.data.data.transactions || [];

            document.getElementById('auditSubTitle').innerText = `Teller: ${closing.teller ? closing.teller.name : '-'} | Tanggal: ${closing.closing_date}`;
            const searchInput = document.getElementById('searchAuditInput');
            if (searchInput) searchInput.value = '';
            renderAuditDetailTable();
            document.getElementById('modalAuditDetail').classList.remove('hidden');
        }
    }

    function renderAuditDetailTable() {
        const input = document.getElementById('searchAuditInput');
        const q = input ? input.value.trim().toLowerCase() : '';
        const tbody = document.getElementById('auditTableBody');

        const filtered = !q ? currentAuditTransactions : currentAuditTransactions.filter(t => {
            const name = (t.saver && t.saver.name) ? t.saver.name : (t.saver_name_snapshot || (t.saver_name || ''));
            const accNo = (t.saver && t.saver.account_number) ? t.saver.account_number : (t.account_number_snapshot || (t.account_number || ''));
            const className = (t.saver && t.saver.class_name) ? t.saver.class_name : (t.saver && t.saver.member && t.saver.member.class_name ? t.saver.member.class_name : '');
            return String(t.transaction_code || '').toLowerCase().includes(q) ||
                   String(name).toLowerCase().includes(q) ||
                   String(accNo).toLowerCase().includes(q) ||
                   String(className).toLowerCase().includes(q) ||
                   String(t.transaction_type || '').toLowerCase().includes(q) ||
                   String(t.amount || '').includes(q);
        });

        if (filtered.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-6 text-slate-400 text-xs">${q ? 'Tidak ada transaksi yang cocok dengan pencarian.' : 'Tidak ada transaksi tercatat pada tanggal closing ini.'}</td></tr>`;
            return;
        }

        tbody.innerHTML = filtered.map(t => {
            const name = (t.saver && t.saver.name) ? t.saver.name : (t.saver_name_snapshot || (t.saver_name || 'Nasabah'));
            const accNo = (t.saver && t.saver.account_number) ? t.saver.account_number : (t.account_number_snapshot || (t.account_number || '-'));
            const className = (t.saver && t.saver.class_name) ? t.saver.class_name : (t.saver && t.saver.member && t.saver.member.class_name ? t.saver.member.class_name : '');
            const isGraduated = t.saver && (t.saver.status === 'inactive' || t.saver.deleted_at !== null || (t.saver.member && t.saver.member.status === 'graduated'));
            const txTime = t.transaction_date ? new Date(t.transaction_date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : '';

            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-2.5 px-3">
                        <span class="font-mono font-bold text-slate-800 block">${t.transaction_code}</span>
                        ${txTime ? `<span class="text-[10px] text-slate-400 font-mono">${txTime} WIB</span>` : ''}
                    </td>
                    <td class="py-2.5 px-3">
                        <div class="flex items-center space-x-1.5">
                            <span class="font-bold text-slate-900">${name}</span>
                            ${isGraduated ? '<span class="px-1.5 py-0.2 rounded text-[9px] font-black uppercase bg-slate-100 text-slate-600 border border-slate-300">Lulus / Arsip</span>' : ''}
                        </div>
                        <div class="text-[11px] text-slate-500 flex items-center gap-2 mt-0.5">
                            <span class="font-mono text-indigo-600 font-medium">Rek: ${accNo}</span>
                            ${className ? `<span class="text-slate-400">• Kelas: <b class="text-slate-700">${className}</b></span>` : ''}
                        </div>
                    </td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase ${t.transaction_type === 'deposit' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                            ${t.transaction_type === 'deposit' ? 'Setor Tunai' : 'Tarik Tunai'}
                        </span>
                    </td>
                    <td class="py-2.5 px-3 text-right font-black ${t.transaction_type === 'deposit' ? 'text-emerald-700' : 'text-rose-700'}">
                        ${t.transaction_type === 'deposit' ? '+' : '-'}Rp ${Number(t.amount).toLocaleString('id-ID')}
                    </td>
                </tr>
            `;
        }).join('');
    }

    function filterAuditDetailTable() {
        renderAuditDetailTable();
    }

    async function approveClosing(closingId, difference) {
        let confirmText = 'Laporan closing teller akan disetujui.';
        if (difference !== 0) {
            confirmText += ` Perhatian: Terdapat selisih kas sebesar Rp ${Math.abs(difference).toLocaleString('id-ID')}!`;
        }

        const { value: notes } = await Swal.fire({
            title: 'Otorisasi & Pindahkan Kas ke Brankas?',
            text: confirmText,
            input: 'text',
            inputPlaceholder: 'Catatan otorisasi brankas (opsional)...',
            icon: difference !== 0 ? 'warning' : 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: 'Ya, Otorisasi & Simpan ke Brankas (102)',
            cancelButtonText: 'Batal'
        });

        if (notes !== undefined) {
            const res = await fetchAPI(`/admin/closings/${closingId}/approve`, {
                method: 'POST',
                body: JSON.stringify({ notes: notes || 'Disetujui Administrator.' })
            });

            if (res && res.data && res.data.success) {
                Swal.fire('Disetujui!', res.data.message, 'success');
                loadAdminClosings();
            } else {
                Swal.fire('Gagal Otorisasi', (res && res.data && res.data.message) || 'Gagal memproses approval', 'error');
            }
        }
    }

    // =========================================================
    // REVISI 8: DATA SISWA TERHAPUS & ARSIP BERKAS ZIP (> 2 BULAN)
    // =========================================================
    async function loadAdminTrash() {
        const res = await fetchAPI('/admin/trash');
        if (res && res.data && res.data.success) {
            adminPagination.trash.rawData = res.data.data || [];
            filterAdminTable('trash');
        }
    }

    function renderTrashTable() {
        const tbody = document.getElementById('trashTableBody');
        const p = adminPagination.trash;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="9" class="text-center py-6 text-slate-400 text-xs">Tidak ada data siswa terhapus saat ini.</td></tr>';
            renderAdminPagination('trash', 'trashPagination');
            updateSelectedBadge('trash');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(s => {
            const safeName = (s.name || '').replace(/'/g, "\\'");
            const safeClass = (s.class_name || '').replace(/'/g, "\\'");

            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-3 text-center">
                        <input type="checkbox" class="row-chk-trash rounded text-slate-700 focus:ring-slate-500 w-4 h-4 cursor-pointer" value="${s.id}" ${adminSelectedItems.trash.has(String(s.id)) ? 'checked' : ''} onchange="onRowCheckboxChange('trash', ${s.id}, this.checked)">
                    </td>
                    <td class="py-3 px-4 font-mono font-bold text-slate-700">${s.nis}</td>
                    <td class="py-3 px-4 font-bold text-slate-800">${s.name}</td>
                    <td class="py-3 px-4 text-xs font-semibold text-slate-600">${s.class_name}</td>
                    <td class="py-3 px-4 font-mono text-xs">${s.account_number || '-'}</td>
                    <td class="py-3 px-4 text-right font-bold text-slate-700">Rp ${Number(s.balance || 0).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-xs text-slate-500">${s.deleted_at}</td>
                    <td class="py-3 px-4 text-center">
                        ${s.is_archived ? `
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-indigo-100 text-indigo-800"> Diarsipkan ke ZIP
                            </span>
                        ` : s.is_over_2_months ? `
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-amber-100 text-amber-800"> Siap Dikemas (> 2 Bln)
                            </span>
                        ` : `
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-black ${s.days_remaining > 15 ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800'}">
                                ${s.days_remaining} hari lagi
                            </span>
                            <span class="block text-[10px] text-slate-400 mt-0.5">s/d ${s.permanent_date}</span>
                        `}
                    </td>
                    <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                        <button onclick="restoreMemberFromTrash(${s.id}, '${s.nis}', '${safeName}', '${safeClass}')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow inline-flex items-center"> Pulihkan Siswa
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        renderAdminPagination('trash', 'trashPagination');
        updateSelectedBadge('trash');
    }

    async function restoreMemberFromTrash(id, nis, name, currentClass) {
        let cleanClass = (currentClass || '')
            .replace(/^Lulus\s*\(\s*Alumni\s*/i, '')
            .replace(/\)$/, '')
            .replace(/^(Lulus|Alumni)\s*/i, '')
            .trim();

        const { value: newClass } = await Swal.fire({
            title: 'Pulihkan & Aktifkan Siswa?',
            html: `
                <p class="text-xs text-slate-600 mb-3">Aktifkan kembali status sebagai siswa aktif (misal: karena <b>tidak naik kelas</b> atau keliru terhapus). Rekening penabung dan saldo tabungan akan aktif kembali.</p>
                <div class="text-left text-xs mb-1 font-bold text-slate-700">Nama Siswa: <b>${name}</b> (NIS: ${nis})</div>
                <div class="text-left text-xs mb-1 font-bold text-slate-700">Kelas / Jurusan:</div>
            `,
            input: 'text',
            inputValue: cleanClass || currentClass,
            inputPlaceholder: 'Masukkan kelas siswa aktif (misal: XII RPL 1)...',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            confirmButtonText: 'Ya, Aktifkan Kembali',
            cancelButtonText: 'Batal'
        });

        if (newClass !== undefined) {
            const res = await fetchAPI(`/admin/trash/${id}/restore`, {
                method: 'POST',
                body: JSON.stringify({ class_name: newClass })
            });

            if (res && res.data && res.data.success) {
                Swal.fire('Berhasil!', res.data.message, 'success');
                loadAdminTrash();
                loadAdminArchives();
                loadMembers();
                loadCustomers();
            } else {
                Swal.fire('Gagal', (res && res.data && res.data.message) || 'Gagal memulihkan siswa', 'error');
            }
        }
    }

    // =========================================================
    // DAFTAR ARSIP BERKAS ZIP DATA SISWA (> 2 BULAN)
    // =========================================================
    async function loadAdminArchives() {
        const res = await fetchAPI('/admin/archives');
        if (res && res.data && res.data.success) {
            adminPagination.archives.rawData = res.data.data || [];
            filterAdminTable('archives');
        }
    }

    function renderArchivesTable() {
        const tbody = document.getElementById('archivesTableBody');
        const p = adminPagination.archives;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">Belum ada paket arsip ZIP. Klik "Kemas ZIP Sekarang" jika terdapat siswa > 2 bulan.</td></tr>';
            renderAdminPagination('archives', 'archivesPagination');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(a => `
            <tr class="border-b hover:bg-slate-50 transition">
                <td class="py-3 px-4 font-mono font-bold text-slate-800">
                     ${a.original_name || a.filename}
                </td>
                <td class="py-3 px-4 text-xs text-slate-600">${a.created_at}</td>
                <td class="py-3 px-4 text-center">
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-indigo-100 text-indigo-800">
                        ${a.student_count} Siswa
                    </span>
                </td>
                <td class="py-3 px-4 text-center font-mono text-xs text-slate-600">${a.file_size_formatted || Math.round(a.file_size/1024) + ' KB'}</td>
                <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                    <button onclick="inspectArchiveZip(${a.id})" class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition inline-flex items-center"> Buka Isi ZIP
                    </button>
                    <a href="${a.download_url || `/admin/archives/${a.id}/download`}" class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition inline-flex items-center shadow-xs"> Unduh ZIP
                    </a>
                </td>
            </tr>
        `).join('');

        renderAdminPagination('archives', 'archivesPagination');
    }

    async function triggerGenerateArchive() {
        Swal.fire({
            title: 'Kemas Data Siswa Terhapus ke ZIP?',
            text: 'Sistem akan memeriksa siswa terhapus yang sudah lewat 2 bulan dan mengemasnya ke dalam berkas ZIP / folder arsip yang aman.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Kemas Berkas ZIP',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetchAPI('/admin/archives/generate', { method: 'POST' });
                if (res && res.data && res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Arsip Berhasil Dibuat!',
                        text: res.data.message
                    });
                    loadAdminArchives();
                    loadAdminTrash();
                } else {
                    Swal.fire('Info Arsip', (res && res.data && res.data.message) || 'Tidak ada data siswa yang telah melewati 2 bulan untuk dikemas', 'info');
                }
            }
        });
    }

    async function inspectArchiveZip(id) {
        const res = await fetchAPI(`/admin/archives/${id}/inspect`);
        if (res && res.data && res.data.success) {
            const a = res.data.data;
            document.getElementById('inspectArchiveName').innerText = a.filename;
            document.getElementById('inspectArchiveCount').innerText = `${a.student_count} Siswa`;
            document.getElementById('inspectArchiveSize').innerText = a.file_size_formatted || `${Math.round(a.file_size / 1024)} KB`;
            document.getElementById('inspectArchiveDate').innerText = a.created_at;
            document.getElementById('btnDownloadArchiveFromModal').href = a.download_url || `/admin/archives/${id}/download`;

            const listEl = document.getElementById('inspectFileList');
            if (a.files && a.files.length > 0) {
                listEl.innerHTML = a.files.map(f => `<li>${f}</li>`).join('');
            } else {
                listEl.innerHTML = '<li class="text-slate-400">Berkas internal ZIP</li>';
            }

            const stuBody = document.getElementById('inspectStudentsBody');
            if (a.students && a.students.length > 0) {
                stuBody.innerHTML = a.students.map(s => `
                    <tr class="border-b hover:bg-slate-50">
                        <td class="p-2 font-mono font-bold text-slate-700">${s.nis}</td>
                        <td class="p-2 font-bold text-slate-800">${s.name}</td>
                        <td class="p-2 text-slate-600">${s.class_name}</td>
                        <td class="p-2 text-right font-bold text-emerald-700">Rp ${Number(s.balance || 0).toLocaleString('id-ID')}</td>
                    </tr>
                `).join('');
            } else {
                stuBody.innerHTML = '<tr><td colspan="4" class="p-3 text-center text-slate-400">Tidak ada rincian siswa.</td></tr>';
            }

            document.getElementById('modalInspectArchive').classList.remove('hidden');
        } else {
            Swal.fire('Gagal Membuka Arsip', (res && res.data && res.data.message) || 'Berkas arsip tidak dapat dibuka', 'error');
        }
    }

    // ==========================================
    // 9. OTORISASI MODAL AWAL & TOP UP KAS TELLER
    // ==========================================

    async function checkPendingAdvancesCount() {
        const res = await fetchAPI('/admin/cash-advances?status=pending');
        if (res && res.data && res.data.success) {
            const count = res.data.pending_count || (res.data.data && res.data.data.total) || 0;
            const badge = document.getElementById('badgePendingAdvances');
            if (badge) {
                badge.innerText = count;
                badge.classList.toggle('hidden', count === 0);
            }
            const pendingText = document.getElementById('advPendingCountText');
            if (pendingText) {
                pendingText.innerText = `${count} Permohonan`;
            }
        }
    }

    async function loadAdminCashAdvances() {
        const status = document.getElementById('filterAdvanceStatus') ? document.getElementById('filterAdvanceStatus').value : 'all';
        const date = document.getElementById('filterAdvanceDate') ? document.getElementById('filterAdvanceDate').value : '';
        
        let url = `/admin/cash-advances?status=${status}`;
        if (date) url += `&date=${date}`;

        const res = await fetchAPI(url);
        if (res && res.data && res.data.success) {
            const items = res.data.data.data || res.data.data || [];
            adminPagination.advances.rawData = items;
            
            // Hitung modal approved hari ini
            const todayStr = new Date().toISOString().slice(0, 10);
            let approvedToday = 0;
            items.forEach(a => {
                if (a.status === 'approved' && a.request_date && a.request_date.startsWith(todayStr)) {
                    approvedToday += Number(a.amount || 0);
                }
            });
            const approvedTodayEl = document.getElementById('advApprovedTodayText');
            if (approvedTodayEl) {
                approvedTodayEl.innerText = `Rp ${approvedToday.toLocaleString('id-ID')}`;
            }

            // Update pending count
            checkPendingAdvancesCount();

            filterAdminTable('advances');
        }
    }

    function renderAdvancesTable() {
        const tbody = document.getElementById('advancesTableBody');
        const p = adminPagination.advances;
        const total = p.data.length;

        if (total === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Tidak ada data permohonan modal awal teller yang sesuai filter.</td></tr>';
            renderAdminPagination('advances', 'advancesPagination');
            return;
        }

        const startIdx = (p.page - 1) * p.pageSize;
        const pageItems = p.data.slice(startIdx, startIdx + p.pageSize);

        tbody.innerHTML = pageItems.map(a => {
            const tellerName = a.teller ? a.teller.name : `Teller #${a.teller_id}`;
            const tellerEmail = a.teller ? a.teller.email : '';
            const safeTellerName = tellerName.replace(/'/g, "\\'");
            const isInitial = (a.type === 'initial');
            const createdDate = a.created_at ? new Date(a.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) : a.request_date;

            let statusBadge = '';
            let actionButtons = '';

            if (a.status === 'pending') {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-black uppercase bg-amber-100 text-amber-800 border border-amber-300 animate-pulse">Menunggu Approval</span>';
                actionButtons = `
                    <div class="flex items-center justify-end space-x-1.5">
                        <button onclick="approveCashAdvanceAdmin(${a.id}, ${a.amount}, '${safeTellerName}')" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center" title="Setujui Modal"> Approve
                        </button>
                        <button onclick="rejectCashAdvanceAdmin(${a.id}, ${a.amount}, '${safeTellerName}')" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition flex items-center" title="Tolak Pengajuan"> Tolak
                        </button>
                    </div>
                `;
            } else if (a.status === 'approved') {
                const approver = a.approved_by ? (a.approved_by.name || 'Admin') : 'Admin';
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-emerald-100 text-emerald-800"> Disetujui</span>';
                actionButtons = `<span class="text-[11px] text-slate-500 font-medium">Oleh: <b>${approver}</b></span>`;
            } else if (a.status === 'rejected') {
                statusBadge = '<span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase bg-rose-100 text-rose-800"> Ditolak</span>';
                actionButtons = `<button onclick="showRejectionReasonModal('${(a.rejection_reason || 'Tidak ada catatan khusus').replace(/'/g, "\\'")}')" class="text-xs text-rose-600 font-bold hover:underline">Lihat Alasan</button>`;
            }

            return `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-4 font-mono text-xs text-slate-600">${createdDate}</td>
                    <td class="py-3 px-4">
                        <span class="font-bold text-slate-900 block">${tellerName}</span>
                        <span class="text-[11px] text-slate-400 font-mono">${tellerEmail}</span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-black uppercase ${isInitial ? 'bg-indigo-100 text-indigo-800' : 'bg-amber-100 text-amber-800'}">
                            ${isInitial ? 'Modal Awal' : 'Top Up Kas'}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right font-black text-slate-900 font-mono">Rp ${Number(a.amount).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-xs text-slate-600 max-w-xs truncate">${a.notes || '<span class="text-slate-300 italic">Tanpa catatan</span>'}</td>
                    <td class="py-3 px-4 text-center">${statusBadge}</td>
                    <td class="py-3 px-4 text-right">${actionButtons}</td>
                </tr>
            `;
        }).join('');

        renderAdminPagination('advances', 'advancesPagination');
    }

    function showRejectionReasonModal(reason) {
        Swal.fire({
            title: 'Alasan Penolakan Modal',
            text: reason,
            icon: 'info',
            confirmButtonColor: '#4f46e5'
        });
    }

    async function approveCashAdvanceAdmin(id, amount, tellerName) {
        Swal.fire({
            title: 'Otorisasi Modal Awal Teller?',
            html: `
                <div class="text-left text-xs space-y-2 p-2">
                    <p>Apakah Anda menyetujui pengajuan modal kas sebesar <b class="text-emerald-700 text-sm">Rp ${Number(amount).toLocaleString('id-ID')}</b> untuk <b>${tellerName}</b>?</p>
                    <div class="p-2.5 bg-indigo-50 border border-indigo-200 rounded-xl text-indigo-900 space-y-1">
                        <div><b>Jurnal Akuntansi:</b></div>
                        <div>• Debet: 101 Kas Loket Teller (${tellerName})</div>
                        <div>• Kredit: 102 Kas Brankas Utama Bank Mini</div>
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Otorisasi & Setujui',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses Otorisasi...', didOpen: () => Swal.showLoading() });
                const res = await fetchAPI(`/admin/cash-advances/${id}/approve`, { method: 'POST' });
                if (res && res.data && res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Modal Berhasil Disetujui!',
                        text: res.data.message,
                        confirmButtonColor: '#10b981'
                    });
                    loadAdminCashAdvances();
                } else {
                    Swal.fire('Gagal Menyetujui', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem', 'error');
                }
            }
        });
    }

    async function rejectCashAdvanceAdmin(id, amount, tellerName) {
        Swal.fire({
            title: 'Tolak Pengajuan Modal?',
            html: `
                <div class="text-left text-xs space-y-2 p-2">
                    <p>Tolak pengajuan modal kas sebesar <b>Rp ${Number(amount).toLocaleString('id-ID')}</b> dari <b>${tellerName}</b>.</p>
                </div>
            `,
            input: 'textarea',
            inputPlaceholder: 'Tuliskan alasan penolakan agar teller mengetahuinya (wajib)...',
            inputValidator: (value) => {
                if (!value || !value.trim()) {
                    return 'Alasan penolakan wajib diisi!';
                }
            },
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Tolak Pengajuan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b'
        }).then(async (result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Memproses Penolakan...', didOpen: () => Swal.showLoading() });
                const res = await fetchAPI(`/admin/cash-advances/${id}/reject`, {
                    method: 'POST',
                    body: JSON.stringify({ rejection_reason: result.value.trim() })
                });
                if (res && res.data && res.data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Pengajuan Ditolak',
                        text: res.data.message,
                        confirmButtonColor: '#e11d48'
                    });
                    loadAdminCashAdvances();
                } else {
                    Swal.fire('Gagal Menolak', (res && res.data && res.data.message) || 'Terjadi kesalahan sistem', 'error');
                }
            }
        });
    }

    // Inisialisasi awal
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
        checkPendingAdvancesCount();
    });
</script>
@endsection