@extends('layouts.app')
@section('title', 'Panel Administrator - Bank Mini Sekolah')

@section('content')
<div class="space-y-6">

    <!-- Header Panel Admin -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <span class="text-xs font-black uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Sistem Utama & Konfigurasi</span>
            <h2 class="text-2xl font-black text-slate-800 tracking-tight mt-1 flex items-center">
                <i class="fa-solid fa-screwdriver-wrench text-indigo-600 mr-2.5"></i> Dashboard Administrator
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">Kelola Pengguna Internal, Master Rekening Nasabah, Cetak Kartu QR, dan Audit Buku Besar</p>
        </div>

        <!-- Tab Navigation Buttons -->
        <div class="flex flex-wrap gap-2 bg-slate-100 p-1.5 rounded-2xl border border-slate-200">
            <button onclick="switchAdminTab('users')" id="tabBtnUsers" class="px-4 py-2 rounded-xl text-xs font-bold transition bg-indigo-600 text-white shadow">
                <i class="fa-solid fa-users-gear mr-1.5"></i> Akun Petugas
            </button>
            <button onclick="switchAdminTab('customers')" id="tabBtnCustomers" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-id-card-clip mr-1.5"></i> Rekening Nasabah
            </button>
            <button onclick="switchAdminTab('journals')" id="tabBtnJournals" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-book-journal-whills mr-1.5"></i> Audit Jurnal
            </button>
            <button onclick="switchAdminTab('config')" id="tabBtnConfig" class="px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900">
                <i class="fa-solid fa-sliders mr-1.5"></i> Konfigurasi
            </button>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 1: MANAJEMEN AKUN PETUGAS (USERS CRUD) -->
    <!-- ======================================================== -->
    <div id="sectionUsers" class="space-y-6">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <h3 class="font-black text-slate-800 text-lg flex items-center">
                    <i class="fa-solid fa-user-shield text-indigo-600 mr-2"></i> Daftar Petugas Bank Mini
                </h3>
                <button onclick="openAddUserModal()" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center self-start">
                    <i class="fa-solid fa-user-plus mr-1.5"></i> Tambah Petugas Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-4">Nama Petugas</th>
                            <th class="py-3 px-4">Username</th>
                            <th class="py-3 px-4">Peran (Role)</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <tr><td colspan="5" class="text-center py-6 text-slate-400 text-xs">Memuat data petugas...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 2: MASTER REKENING & CETAK KARTU QR -->
    <!-- ======================================================== -->
    <div id="sectionCustomers" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <div>
                    <h3 class="font-black text-slate-800 text-lg flex items-center">
                        <i class="fa-solid fa-address-book text-emerald-600 mr-2"></i> Master Rekening Tabungan Siswa
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Penerbitan rekening otomatis & kartu QR nasabah</p>
                </div>
                <button onclick="openAddCustomerModal()" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center self-start">
                    <i class="fa-solid fa-plus mr-1.5"></i> Buka Rekening Siswa Baru
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b bg-slate-50 text-slate-600 text-xs uppercase font-bold">
                            <th class="py-3 px-4">No. Rekening</th>
                            <th class="py-3 px-4">NIS</th>
                            <th class="py-3 px-4">Nama Siswa</th>
                            <th class="py-3 px-4">Kelas</th>
                            <th class="py-3 px-4 text-right">Saldo Saat Ini</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Kartu QR</th>
                        </tr>
                    </thead>
                    <tbody id="customersTableBody">
                        <tr><td colspan="7" class="text-center py-6 text-slate-400 text-xs">Memuat data nasabah...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 3: AUDIT JURNAL AKUNTANSI (BACK-END) -->
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
                <i class="fa-solid fa-scale-balanced text-3xl text-emerald-600"></i>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 mb-4 border-b gap-3">
                <h3 class="font-black text-slate-800 text-lg flex items-center">
                    <i class="fa-solid fa-receipt text-indigo-600 mr-2"></i> Buku Besar Jurnal Akuntansi (Double-Entry)
                </h3>
                <input type="date" id="journalFilterDate" onchange="loadJournals()" class="px-3 py-2 rounded-xl border border-slate-300 text-xs font-bold focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- TAB 4: KONFIGURASI GLOBAL PERBANKAN -->
    <!-- ======================================================== -->
    <div id="sectionConfig" class="space-y-6 hidden">
        <div class="bg-white rounded-3xl shadow-sm border border-slate-200 p-6 max-w-2xl">
            <h3 class="font-black text-slate-800 text-lg flex items-center mb-4">
                <i class="fa-solid fa-sliders text-indigo-600 mr-2"></i> Parameter Inti Perbankan Mini
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

</div>

<!-- ======================================================== -->
<!-- MODALS -->
<!-- ======================================================== -->

<!-- Modal Tambah Petugas -->
<div id="modalAddUser" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center">
            <i class="fa-solid fa-user-plus text-indigo-600 mr-2"></i> Tambah Petugas Baru
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
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kata Sandi (Password)</label>
                <input type="password" id="addUserPassword" required minlength="6" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Peran (Role)</label>
                <select id="addUserRole" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                    <option value="teller">Piket Teller (Operator Loket)</option>
                    <option value="supervisor">Supervisor (Kepala Bank Mini)</option>
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

<!-- Modal Tambah Rekening Nasabah Siswa -->
<div id="modalAddCustomer" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4">
        <h3 class="font-black text-slate-800 text-lg flex items-center">
            <i class="fa-solid fa-user-graduate text-emerald-600 mr-2"></i> Buka Rekening Siswa Baru
        </h3>
        <p class="text-xs text-slate-500">Nomor rekening akan dihasilkan secara otomatis oleh sistem.</p>
        <form onsubmit="submitAddCustomer(event)" class="space-y-3 text-sm">
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">NIS (Nomor Induk Siswa)</label>
                <input type="text" id="addCustNis" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Siswa</label>
                <input type="text" id="addCustName" required class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Kelas / Jurusan</label>
                <input type="text" id="addCustClass" required placeholder="Contoh: XII RPL 1" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase mb-1">PIN Keamanan (6-Digit)</label>
                <input type="password" id="addCustPin" required maxlength="6" minlength="6" placeholder="6 Digit Angka" class="w-full px-3 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none font-mono text-center tracking-widest">
            </div>
            <div class="flex justify-end space-x-2 pt-3">
                <button type="button" onclick="closeModal('modalAddCustomer')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow">Terbitkan Rekening</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Cetak Kartu Tabungan Siswa + QR Code -->
<div id="modalCardQR" class="fixed inset-0 bg-black/60 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 text-center">
        <h3 class="font-black text-slate-800 text-base flex items-center justify-center">
            <i class="fa-solid fa-id-card text-indigo-600 mr-2"></i> Kartu Tabungan Digital Siswa
        </h3>
        
        <div id="printableArea" class="p-5 rounded-2xl bg-gradient-to-br from-indigo-950 to-slate-900 text-white shadow-xl text-left border border-indigo-700/50 space-y-4">
            <div class="flex items-center justify-between border-b border-indigo-700/50 pb-2">
                <span class="text-[10px] font-black uppercase tracking-wider text-yellow-400">BANK MINI SEKOLAH</span>
                <span class="text-[10px] text-indigo-300 font-mono">TABUNGAN SISWA</span>
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
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs shadow flex items-center">
                <i class="fa-solid fa-print mr-1.5"></i> Cetak Kartu
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Tab Controller
    function switchAdminTab(tab) {
        const sections = ['Users', 'Customers', 'Journals', 'Config'];
        sections.forEach(s => {
            document.getElementById(`section${s}`).classList.add('hidden');
            const btn = document.getElementById(`tabBtn${s}`);
            btn.className = 'px-4 py-2 rounded-xl text-xs font-bold transition text-slate-600 hover:text-slate-900';
        });

        const activeSec = tab.charAt(0).toUpperCase() + tab.slice(1);
        document.getElementById(`section${activeSec}`).classList.remove('hidden');
        document.getElementById(`tabBtn${activeSec}`).className = 'px-4 py-2 rounded-xl text-xs font-bold transition bg-indigo-600 text-white shadow';

        if (tab === 'users') loadUsers();
        if (tab === 'customers') loadCustomers();
        if (tab === 'journals') loadJournals();
    }

    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }

    // ================= LOAD USERS =================
    async function loadUsers() {
        const res = await fetchAPI('/admin/users');
        const tbody = document.getElementById('usersTableBody');
        if (res && res.data && res.data.success) {
            const users = res.data.data;
            tbody.innerHTML = users.map(u => `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-4 font-bold text-slate-800">${u.name}</td>
                    <td class="py-3 px-4 font-mono text-xs text-slate-600">${u.username}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase ${
                            u.role === 'admin' ? 'bg-purple-100 text-purple-800' :
                            u.role === 'teller' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800'
                        }">
                            ${u.role}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase ${u.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                            ${u.status}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right space-x-1">
                        <button onclick="toggleUserStatus(${u.id}, '${u.username}', '${u.name}', '${u.role}', '${u.status}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">
                            ${u.status === 'active' ? '<i class="fa-solid fa-ban text-rose-600"></i> Nonaktifkan' : '<i class="fa-solid fa-check text-emerald-600"></i> Aktifkan'}
                        </button>
                        <button onclick="deleteUser(${u.id})" class="px-2.5 py-1 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }

    function openAddUserModal() {
        document.getElementById('modalAddUser').classList.remove('hidden');
    }

    async function submitAddUser(e) {
        e.preventDefault();
        const payload = {
            name: document.getElementById('addUserName').value,
            username: document.getElementById('addUserUsername').value,
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

    async function toggleUserStatus(id, username, name, role, currentStatus) {
        const nextStatus = (currentStatus === 'active') ? 'inactive' : 'active';
        const res = await fetchAPI(`/admin/users/${id}`, {
            method: 'PUT',
            body: JSON.stringify({ username, name, role, status: nextStatus })
        });
        if (res && res.data && res.data.success) {
            Swal.fire({ icon: 'success', title: 'Status Diperbarui', text: res.data.message, timer: 1000, showConfirmButton: false });
            loadUsers();
        }
    }

    async function deleteUser(id) {
        Swal.fire({
            title: 'Hapus Petugas?',
            text: 'Tindakan ini dilindungi aturan ON DELETE RESTRICT.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then(async (result) => {
            if (result.isConfirmed) {
                const res = await fetchAPI(`/admin/users/${id}`, { method: 'DELETE' });
                if (res && res.data && res.data.success) {
                    Swal.fire('Terhapus!', res.data.message, 'success');
                    loadUsers();
                } else {
                    Swal.fire('Ditolak Restrict!', (res && res.data && res.data.message) || 'Gagal menghapus user', 'error');
                }
            }
        });
    }

    // ================= LOAD CUSTOMERS =================
    async function loadCustomers() {
        const res = await fetchAPI('/admin/customers');
        const tbody = document.getElementById('customersTableBody');
        if (res && res.data && res.data.success) {
            const custs = res.data.data;
            tbody.innerHTML = custs.map(c => `
                <tr class="border-b hover:bg-slate-50 transition">
                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">${c.account_number}</td>
                    <td class="py-3 px-4 font-mono text-xs text-slate-600">${c.nis}</td>
                    <td class="py-3 px-4 font-bold text-slate-800">${c.name}</td>
                    <td class="py-3 px-4 text-xs text-slate-600">${c.class_name}</td>
                    <td class="py-3 px-4 text-right font-black text-emerald-700">Rp ${Number(c.balance).toLocaleString('id-ID')}</td>
                    <td class="py-3 px-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold uppercase ${c.status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'}">
                            ${c.status}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <button onclick="showCardQR('${c.account_number}', '${c.nis}', '${c.name}', '${c.class_name}')" class="px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-bold transition flex items-center inline-flex">
                            <i class="fa-solid fa-qrcode mr-1"></i> Cetak Kartu
                        </button>
                    </td>
                </tr>
            `).join('');
        }
    }

    function openAddCustomerModal() {
        document.getElementById('modalAddCustomer').classList.remove('hidden');
    }

    async function submitAddCustomer(e) {
        e.preventDefault();
        const payload = {
            nis: document.getElementById('addCustNis').value,
            name: document.getElementById('addCustName').value,
            class_name: document.getElementById('addCustClass').value,
            security_pin: document.getElementById('addCustPin').value,
        };

        const res = await fetchAPI('/admin/customers', { method: 'POST', body: JSON.stringify(payload) });
        if (res && res.data && res.data.success) {
            Swal.fire({ icon: 'success', title: 'Rekening Diterbitkan!', text: `No. Rekening: ${res.data.data.customer.account_number}`, timer: 1500, showConfirmButton: false });
            closeModal('modalAddCustomer');
            loadCustomers();
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal Menerbitkan', text: (res && res.data && res.data.errors) ? Object.values(res.data.errors)[0][0] : 'Gagal membuat rekening' });
        }
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

    // ================= LOAD JOURNALS =================
    async function loadJournals() {
        const filterDate = document.getElementById('journalFilterDate').value;
        const endpoint = filterDate ? `/admin/audit-journals?date=${filterDate}` : '/admin/audit-journals';
        const res = await fetchAPI(endpoint);

        if (res && res.data && res.data.success) {
            const summary = res.data.summary;
            const journals = res.data.data.data || res.data.data;

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

            const tbody = document.getElementById('journalsTableBody');
            tbody.innerHTML = journals.map(j => `
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
        }
    }

    // Inisialisasi awal
    document.addEventListener('DOMContentLoaded', () => {
        loadUsers();
    });
</script>
@endsection