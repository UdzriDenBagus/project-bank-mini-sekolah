<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Teller Bank Mini Sekolah')</title>
    
    <!-- Favicon Logo SMK Negeri 1 Bantul -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- HTML5 QR Code Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <!-- QR Code Generator Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }

        /* Mode Cetak Struk Thermal */
        @media print {
            body * { visibility: hidden !important; }
            #printableArea, #printableArea * { visibility: visible !important; }
            #printableArea {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 80mm !important;
                padding: 10px !important;
                margin: 0 !important;
                color: #000 !important;
                background: #fff !important;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col antialiased">

    <!-- Header Navbar Petugas (Admin & Teller) -->
    <nav class="bg-gradient-to-r from-indigo-950 via-indigo-900 to-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo SMK N 1 Bantul -->
                <div class="flex items-center space-x-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" class="h-10 w-10 object-contain drop-shadow">
                    <div>
                        <span class="font-black text-base sm:text-lg tracking-wider block leading-tight">BANK MINI SEKOLAH</span>
                        <span class="text-[11px] text-indigo-300 block font-medium">SMK Negeri 1 Bantul • E-Teller & Financial Ledger</span>
                    </div>
                </div>

                <!-- Navigation Links & User Section -->
                <div id="navUserSection" class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Navigasi Dinamis Sesuai Role (Bebas Icon) -->
                    <div id="roleNavLinks" class="hidden md:flex items-center space-x-2"></div>

                    <!-- Badge Info Pengguna Aktif & Menu Profil/Email -->
                    <button type="button" onclick="openProfileEmailModal()" id="userBadge" class="text-xs font-bold bg-indigo-800/90 hover:bg-indigo-700 px-3 py-1.5 rounded-xl border border-indigo-600 flex items-center shadow-inner cursor-pointer transition" title="Klik untuk mengelola profil & alamat Gmail">
                        <span id="userNameText">Petugas Bank</span>
                    </button>

                    <!-- Tombol Keluar / Logout -->
                    <button onclick="logoutApp()" class="text-xs bg-rose-600 hover:bg-rose-700 px-3 py-1.5 rounded-xl transition duration-150 shadow font-bold flex items-center">
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Dynamic Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer Standar UKK -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 font-medium flex flex-col sm:flex-row items-center justify-center gap-2">
        <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Bantul" class="h-5 w-5 object-contain opacity-80 inline-block">
        <span>&copy; 2026 Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak - Paket 1 • SMK Negeri 1 Bantul</span>
    </footer>

    <!-- Global Client Helper API -->
    <script>
        const API_BASE = '/api';

        function formatRupiahNumber(val) {
            let num = (val ?? '').toString().replace(/\D/g, '');
            if (!num) return '';
            return new Intl.NumberFormat('id-ID').format(num);
        }

        function unformatRupiahNumber(val) {
            return parseInt((val ?? '').toString().replace(/\D/g, ''), 10) || 0;
        }

        function attachRupiahMask(inputEl) {
            if (!inputEl) return;
            inputEl.type = 'text';
            inputEl.inputMode = 'numeric';
            inputEl.addEventListener('input', function() {
                const raw = this.value.replace(/\D/g, '');
                if (!raw) {
                    this.value = '';
                } else {
                    this.value = new Intl.NumberFormat('id-ID').format(raw);
                }
            });
        }

        async function fetchAPI(endpoint, options = {}) {
            const token = localStorage.getItem('bank_token');
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
                ...(options.headers || {})
            };

            try {
                const response = await fetch(API_BASE + endpoint, {
                    ...options,
                    headers
                });

                const data = await response.json().catch(() => ({}));
                
                // Jika Token Expired atau Belum Login, lempar ke halaman login
                if (response.status === 401 && !window.location.pathname.includes('/login')) {
                    if (!data || data.message === 'Unauthenticated.' || !data.message) {
                        localStorage.clear();
                        window.location.href = '/login';
                        return null;
                    }
                }

                return { status: response.status, data };
            } catch (err) {
                console.error('Fetch Error:', err);
                return { status: 500, data: { success: false, message: 'Koneksi ke server gagal atau terputus.' } };
            }
        }

        // Cek Sesi Pengguna di Setiap Halaman
        document.addEventListener('DOMContentLoaded', () => {
            const path = window.location.pathname;
            const token = localStorage.getItem('bank_token') || localStorage.getItem('token');
            const name = localStorage.getItem('user_name');
            const role = localStorage.getItem('user_role');

            if (token) {
                // Sinkronisasi kunci token agar kompatibel
                if (!localStorage.getItem('token')) localStorage.setItem('token', token);
                if (!localStorage.getItem('bank_token')) localStorage.setItem('bank_token', token);
            }

            if (!path.includes('/login')) {
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                // Nasabah dibatasi hanya boleh menggunakan aplikasi mobile
                if (role === 'customer') {
                    localStorage.clear();
                    alert('Layanan Nasabah Siswa hanya dapat diakses melalui Aplikasi Mobile. Portal Web ini khusus untuk Petugas.');
                    window.location.href = '/login';
                    return;
                }

                if (name && role) {
                    document.getElementById('userNameText').innerText = `${name} [${role.toUpperCase()}]`;
                }

                // Render Quick Nav Links sesuai role (Bebas Icon)
                const nav = document.getElementById('roleNavLinks');
                if (nav) {
                    if (role === 'admin' || role === 'supervisor') {
                        nav.innerHTML = `
                            <a href="/admin/dashboard" class="text-xs bg-indigo-800 hover:bg-indigo-700 px-3 py-1.5 rounded-lg font-bold transition flex items-center">
                                Dashboard Admin
                            </a>
                        `;
                    } else if (role === 'teller') {
                        nav.innerHTML = `
                            <a href="/teller/loket" class="text-xs bg-indigo-800 hover:bg-indigo-700 px-3 py-1.5 rounded-lg font-bold transition flex items-center">
                                Loket Teller
                            </a>
                        `;
                    }
                }
            } else {
                // Sembunyikan navigasi user di halaman login
                const navUser = document.getElementById('navUserSection');
                if (navUser) navUser.style.display = 'none';
            }
        });

        // Modal Pengaturan Profil & Kelola Email Akun Setelah Login (Untuk Petugas)
        async function openProfileEmailModal() {
            Swal.fire({
                title: 'Memuat Profil Akun...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const res = await fetchAPI('/auth/profile');
            if (!res || !res.data || !res.data.success) {
                Swal.fire('Gagal', 'Gagal memuat profil akun.', 'error');
                return;
            }

            const profile = res.data.data.profile;
            const role = res.data.data.role;
            const currentEmail = profile.email || (profile.member ? profile.member.email : '') || '';
            const accountId = profile.username || profile.nis || profile.account_number || '-';
            const name = profile.name || '-';

            const { value: newEmail, isConfirmed } = await Swal.fire({
                title: 'Pengaturan Profil & Gmail',
                html: `
                    <div class="text-left text-xs space-y-3 p-3 bg-slate-50 border border-slate-200 rounded-2xl">
                        <div class="space-y-1 pb-2 border-b border-slate-200">
                            <div><b>Nama:</b> <span class="text-slate-800">${name}</span></div>
                            <div><b>Identitas Akun:</b> <span class="font-mono text-indigo-600 font-bold">${accountId}</span></div>
                            <div><b>Peran (Role):</b> <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase bg-indigo-100 text-indigo-800">${role}</span></div>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">
                                Alamat Gmail Akun Anda <span class="text-rose-500">*</span>
                            </label>
                            <input type="email" id="swalProfileEmailInput" value="${currentEmail}" placeholder="contoh@gmail.com" class="w-full px-3 py-2 border rounded-xl text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                            <p class="text-[11px] text-slate-500 mt-1.5 leading-relaxed">
                                Alamat Gmail ini digunakan untuk otorisasi pengiriman kode OTP saat Anda melakukan reset Kata Sandi.
                            </p>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Simpan Alamat Gmail',
                cancelButtonText: 'Tutup',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#64748b',
                preConfirm: () => {
                    const emailVal = document.getElementById('swalProfileEmailInput').value.trim();
                    if (!emailVal) {
                        Swal.showValidationMessage('Alamat Gmail wajib diisi!');
                        return false;
                    }
                    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                        Swal.showValidationMessage('Format alamat Gmail tidak valid!');
                        return false;
                    }
                    return emailVal;
                }
            });

            if (!isConfirmed || !newEmail) return;

            Swal.fire({
                title: 'Menyimpan Email...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading()
            });

            const updateRes = await fetchAPI('/auth/profile/email', {
                method: 'PUT',
                body: JSON.stringify({ email: newEmail })
            });

            if (updateRes && updateRes.data && updateRes.data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Disimpan!',
                    text: updateRes.data.message,
                    confirmButtonColor: '#10b981'
                });
            } else {
                Swal.fire('Gagal Menyimpan', (updateRes && updateRes.data && updateRes.data.message) || 'Terjadi kesalahan sistem.', 'error');
            }
        }

        // Logout Handler
        async function logoutApp() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin mengakhiri sesi kerja?',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Keluar',
                cancelButtonText: 'Batal'
            }).then(async (result) => {
                if (result.isConfirmed) {
                    await fetchAPI('/auth/logout', { method: 'POST' });
                    localStorage.clear();
                    window.location.href = '/login';
                }
            });
        }
    </script>
    @yield('scripts')
</body>
</html>
