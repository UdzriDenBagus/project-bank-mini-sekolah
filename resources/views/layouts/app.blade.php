<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'E-Teller Bank Mini Sekolah')</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome Icons 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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

    <!-- Header Navbar Petugas & Nasabah -->
    <nav class="bg-gradient-to-r from-indigo-950 via-indigo-900 to-slate-900 text-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-3">
                    <div class="p-2.5 bg-indigo-600 rounded-xl shadow-md flex items-center justify-center">
                        <i class="fa-solid fa-building-columns text-xl text-yellow-400"></i>
                    </div>
                    <div>
                        <span class="font-black text-base sm:text-lg tracking-wider block leading-tight">BANK MINI SEKOLAH</span>
                        <span class="text-[11px] text-indigo-300 block font-medium">Sistem Informasi E-Teller & Financial Ledger</span>
                    </div>
                </div>

                <!-- Navigation Links & User Section -->
                <div id="navUserSection" class="flex items-center space-x-2 sm:space-x-3">
                    <!-- Navigasi Dinamis Sesuai Role -->
                    <div id="roleNavLinks" class="hidden md:flex items-center space-x-2"></div>

                    <!-- Badge Info Pengguna Aktif -->
                    <span id="userBadge" class="text-xs font-bold bg-indigo-800/90 px-3 py-1.5 rounded-xl border border-indigo-600 flex items-center shadow-inner">
                        <i class="fa-solid fa-circle-user mr-1.5 text-yellow-400 text-sm"></i>
                        <span id="userNameText">Petugas Bank</span>
                    </span>

                    <!-- Tombol Keluar / Logout -->
                    <button onclick="logoutApp()" class="text-xs bg-rose-600 hover:bg-rose-700 px-3 py-1.5 rounded-xl transition duration-150 shadow font-bold flex items-center">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-1"></i> Keluar
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
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500 font-medium">
        &copy; 2026 Uji Kompetensi Keahlian (UKK) Rekayasa Perangkat Lunak - Paket 1 • E-Teller Bank Mini Sekolah
    </footer>

    <!-- Global Client Helper API -->
    <script>
        const API_BASE = '/api';

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
                    localStorage.clear();
                    window.location.href = '/login';
                    return null;
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
            const token = localStorage.getItem('bank_token');
            const name = localStorage.getItem('user_name');
            const role = localStorage.getItem('user_role');

            if (!path.includes('/login')) {
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                if (name && role) {
                    document.getElementById('userNameText').innerText = `${name} [${role.toUpperCase()}]`;
                }

                // Render Quick Nav Links sesuai role
                const nav = document.getElementById('roleNavLinks');
                if (nav) {
                    if (role === 'admin') {
                        nav.innerHTML = `
                            <a href="/admin/dashboard" class="text-xs bg-indigo-800 hover:bg-indigo-700 px-3 py-1.5 rounded-lg font-bold transition flex items-center">
                                <i class="fa-solid fa-gauge mr-1.5"></i> Dashboard Admin
                            </a>
                        `;
                    } else if (role === 'teller') {
                        nav.innerHTML = `
                            <a href="/teller/loket" class="text-xs bg-indigo-800 hover:bg-indigo-700 px-3 py-1.5 rounded-lg font-bold transition flex items-center">
                                <i class="fa-solid fa-cash-register mr-1.5"></i> Loket Teller
                            </a>
                        `;
                    } else if (role === 'supervisor') {
                        nav.innerHTML = `
                            <a href="/supervisor/dashboard" class="text-xs bg-indigo-800 hover:bg-indigo-700 px-3 py-1.5 rounded-lg font-bold transition flex items-center">
                                <i class="fa-solid fa-user-tie mr-1.5"></i> Dashboard Supervisor
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

        // Logout Handler
        async function logoutApp() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin mengakhiri sesi kerja?',
                icon: 'question',
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