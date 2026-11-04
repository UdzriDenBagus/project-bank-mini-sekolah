@extends('layouts.app')
@section('title', 'Pintu Masuk Petugas Bank - Bank Mini Sekolah')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center py-6">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        
        <!-- Header Card Gradient dengan Logo Resmi SMK N 1 Bantul -->
        <div class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 p-7 text-white text-center relative overflow-hidden">
            <div class="flex justify-center mb-3">
                <div class="p-2.5 bg-white/10 rounded-2xl backdrop-blur border border-white/20 shadow-xl inline-flex items-center justify-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK Negeri 1 Bantul" class="h-16 w-16 object-contain drop-shadow">
                </div>
            </div>
            <h2 class="text-xl font-black tracking-wide">E-Teller Bank Mini Sekolah</h2>
            <p class="text-xs text-indigo-200 mt-1 font-medium">SMK Negeri 1 Bantul • Program Keahlian RPL</p>
            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full bg-indigo-950/80 border border-indigo-700/60 text-[11px] text-indigo-200 font-semibold">
                <span>Portal Masuk Petugas (Administrator & Teller)</span>
            </div>
        </div>

        <!-- Form Login Petugas -->
        <div class="p-7 space-y-5">
            <div class="flex items-center justify-between text-xs text-slate-500 font-semibold border-b border-slate-100 pb-2">
                <span>Autentikasi Petugas</span>
                <span class="text-indigo-600 font-mono font-bold">ADMIN / TELLER</span>
            </div>

            <form onsubmit="handleUnifiedLogin(event)" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Username Petugas
                    </label>
                    <input type="text" id="login_identifier" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition font-medium" placeholder="Masukkan username petugas...">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">Kata Sandi (Password)</label>
                        <button type="button" onclick="openForgotModal()" class="text-[11px] font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                            Lupa Kata Sandi?
                        </button>
                    </div>
                    <input type="password" id="login_password" required class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-sm transition font-medium" placeholder="••••••••">
                </div>

                <button type="submit" id="btnLoginSubmit" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition duration-200 text-sm flex items-center justify-center">
                    <span>Masuk ke Sistem</span>
                </button>
            </form>

            <div class="pt-3 border-t border-slate-100 text-center space-y-1">
                <p class="text-[11px] text-slate-400">
                    Akun Petugas: <span class="font-mono text-slate-600 font-bold">admin / teller1</span>
                </p>
                <p class="text-[10px] text-slate-400 italic">
                    Password default: <span class="font-mono text-slate-600">password123</span>
                </p>
                <div class="mt-2 p-2.5 rounded-xl bg-amber-50 border border-amber-200 text-[11px] text-amber-800 text-center font-medium">
                    Nasabah Siswa dapat mengakses layanan tabungan secara mandiri melalui <b>Aplikasi Mobile (Flutter)</b>.
                </div>
            </div>
        </div>

    </div>
</div>

<!-- ======================================================== -->
<!-- MODAL: RESET KATA SANDI VIA EMAIL GMAIL (BEBAS ICON) -->
<!-- ======================================================== -->
<div id="modalForgot" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4 border border-slate-100 max-h-[92vh] overflow-y-auto">
        
        <!-- Modal Header (Bebas Icon) -->
        <div class="flex items-center justify-between pb-3 border-b">
            <div>
                <h3 class="font-black text-slate-800 text-base">Reset Kata Sandi Akun</h3>
                <p class="text-xs text-slate-500">Kode verifikasi OTP terkirim ke Gmail terdaftar di database</p>
            </div>
            <button type="button" onclick="closeForgotModal()" class="text-slate-400 hover:text-slate-700 text-xl font-bold p-1 rounded-lg transition">✕</button>
        </div>

        <!-- Stepper Indicator -->
        <div class="flex items-center justify-between px-2 py-1 text-xs font-bold">
            <div id="stepIndicator1" class="flex items-center space-x-2 text-indigo-600">
                <span class="w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[11px] font-black">1</span>
                <span>Permintaan Kode</span>
            </div>
            <div class="h-0.5 flex-1 mx-3 bg-slate-200" id="stepLine"></div>
            <div id="stepIndicator2" class="flex items-center space-x-2 text-slate-400">
                <span class="w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[11px] font-black" id="stepBadge2">2</span>
                <span>Verifikasi & Sandi Baru</span>
            </div>
        </div>

        <!-- Banner Info Email Sistem (Bebas Icon) -->
        <div class="p-3 bg-slate-50 border border-slate-200 rounded-2xl text-[11px] text-slate-600">
            <span>Sistem Bank Mini terhubung dengan Gmail SMTP (<strong class="text-slate-800">{{ config('mail.from.address', 'udzriqq@gmail.com') }}</strong>). Kode OTP resmi akan dikirim otomatis ke alamat Gmail yang tersimpan di database.</span>
        </div>

        <!-- ================= TAHAP 1: KIRIM KODE OTP ================= -->
        <form id="formSendResetCode" onsubmit="handleSendResetOtp(event)" class="space-y-3.5 text-xs">
            <div>
                <label id="resetIdentifierLabel" class="block font-bold text-slate-700 uppercase mb-1">
                    Username Petugas *
                </label>
                <input type="text" id="resetIdentifierInput" required placeholder="Contoh: admin atau teller1" class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs font-semibold">
            </div>

            <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-[11px] text-indigo-900 leading-relaxed">
                Kode verifikasi OTP 6-digit akan dikirimkan otomatis ke <b>alamat Gmail terdaftar</b> pada akun petugas.
            </div>

            <button type="submit" id="btnSendResetCode" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center">
                <span id="btnSendResetCodeText">Kirim Kode Verifikasi ke Gmail</span>
            </button>
        </form>

        <div class="text-center" id="toggleStepContainer">
            <button type="button" onclick="toggleOtpVerificationStep()" id="btnToggleOtpStep" class="text-[11px] font-bold text-indigo-600 hover:underline">
                Sudah menerima 6-digit kode verifikasi di Gmail? Masukkan kode di sini →
            </button>
        </div>

        <!-- ================= TAHAP 2: VERIFIKASI KODE & SET PASSWORD BARU ================= -->
        <form id="formVerifyAndReset" onsubmit="handleVerifyAndReset(event)" class="space-y-3.5 text-xs hidden pt-3 border-t">
            <div id="otpSentAlertBox" class="p-3 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs space-y-1">
                <span class="font-bold block" id="otpSentTitle">Kode Verifikasi Telah Dikirim!</span>
                <p class="text-[11px] text-emerald-800" id="otpSentMessage">Silakan periksa Kotak Masuk atau folder Spam pada Gmail Anda.</p>
                <div class="flex items-center justify-between text-[11px] pt-1 text-emerald-700 font-mono">
                    <span id="otpCountdownText">Berlaku: 30:00</span>
                    <button type="button" onclick="resendOtpCode()" class="text-indigo-600 hover:underline font-bold font-sans">Kirim Ulang</button>
                </div>
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Identitas Akun Terverifikasi</label>
                <input type="text" id="verifyIdentifierInput" readonly class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-700 font-mono font-bold text-xs cursor-not-allowed">
            </div>

            <div>
                <label class="block font-bold text-slate-700 uppercase mb-1">Kode Verifikasi OTP (6 Digit Angka) *</label>
                <input type="text" id="verifyResetCodeInput" required maxlength="6" pattern="[0-9]{6}" inputmode="numeric" placeholder="••••••" class="w-full px-3.5 py-3 rounded-xl border border-indigo-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-center font-mono tracking-[0.4em] text-xl font-black text-indigo-900 bg-indigo-50/30">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label id="newSecretLabel" class="block font-bold text-slate-700 uppercase">Kata Sandi Baru *</label>
                    <button type="button" onclick="togglePasswordVisibility('newSecretInput', 'btnToggleSecret1')" id="btnToggleSecret1" class="text-[11px] text-slate-400 hover:text-indigo-600">
                        Lihat
                    </button>
                </div>
                <input type="password" id="newSecretInput" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs font-medium">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label id="confirmSecretLabel" class="block font-bold text-slate-700 uppercase">Konfirmasi Kata Sandi Baru *</label>
                    <button type="button" onclick="togglePasswordVisibility('confirmSecretInput', 'btnToggleSecret2')" id="btnToggleSecret2" class="text-[11px] text-slate-400 hover:text-indigo-600">
                        Lihat
                    </button>
                </div>
                <input type="password" id="confirmSecretInput" required minlength="6" placeholder="Masukkan kembali untuk memastikan" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:outline-none text-xs font-medium">
            </div>

            <div class="flex items-center space-x-2 pt-1">
                <button type="button" onclick="backToStepOne()" class="w-1/3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                    ← Kembali
                </button>
                <button type="submit" id="btnVerifySubmit" class="w-2/3 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-md transition flex items-center justify-center">
                    <span id="btnVerifySubmitText">Simpan Kata Sandi Baru</span>
                </button>
            </div>
        </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
    // ========================================================
    // HANDLER LOGIN PETUGAS (ADMIN & TELLER)
    // ========================================================
    async function handleUnifiedLogin(e) {
        e.preventDefault();
        const username = document.getElementById('login_identifier').value.trim();
        const password = document.getElementById('login_password').value;
        const btn = document.getElementById('btnLoginSubmit');

        if (!username || !password) {
            Swal.fire('Input Tidak Lengkap', 'Silakan masukkan username dan kata sandi.', 'warning');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span>Memverifikasi Akun...</span>';

        try {
            const res = await fetch('/api/auth/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ username, password })
            });

            const result = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<span>Masuk ke Sistem</span>';

            if (res.ok && result.success) {
                const data = result.data;
                localStorage.setItem('bank_token', data.token);
                localStorage.setItem('token', data.token);
                localStorage.setItem('user_role', data.role);
                
                const displayName = data.user ? data.user.name : 'Petugas';
                localStorage.setItem('user_name', displayName);

                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: `Selamat datang, ${displayName} [${data.role.toUpperCase()}]`,
                    timer: 1200,
                    showConfirmButton: false
                }).then(() => {
                    window.location.href = data.redirect || '/';
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Autentikasi Gagal',
                    text: result.message || 'Identitas akun atau Kata Sandi salah.'
                });
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<span>Masuk ke Sistem</span>';
            Swal.fire('Koneksi Gagal', 'Gagal menghubungi server: ' + err.message, 'error');
        }
    }

    let currentResetRole = 'auto';
    let currentResetType = 'password';
    let otpCountdownInterval = null;
    let lastSentIdentifier = '';

    function openForgotModal() {
        document.getElementById('modalForgot').classList.remove('hidden');
        backToStepOne();
    }

    function closeForgotModal() {
        document.getElementById('modalForgot').classList.add('hidden');
        if (otpCountdownInterval) clearInterval(otpCountdownInterval);
    }

    function togglePasswordVisibility(inputId, btnId) {
        const input = document.getElementById(inputId);
        const btn = document.getElementById(btnId);
        if (input.type === 'password') {
            input.type = 'text';
            btn.innerText = 'Tutup';
        } else {
            input.type = 'password';
            btn.innerText = 'Lihat';
        }
    }

    function startOtpCountdown(durationSeconds = 1800) {
        if (otpCountdownInterval) clearInterval(otpCountdownInterval);
        let remaining = durationSeconds;
        const countdownEl = document.getElementById('otpCountdownText');

        function updateDisplay() {
            const minutes = Math.floor(remaining / 60);
            const seconds = remaining % 60;
            countdownEl.innerText = `Berlaku: ${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
            if (remaining <= 0) {
                clearInterval(otpCountdownInterval);
                countdownEl.innerText = 'Kode Kedaluwarsa, silakan kirim ulang.';
            }
            remaining--;
        }

        updateDisplay();
        otpCountdownInterval = setInterval(updateDisplay, 1000);
    }

    function goToStepTwo(identifier, maskedEmail) {
        lastSentIdentifier = identifier;
        document.getElementById('formSendResetCode').classList.add('hidden');
        document.getElementById('toggleStepContainer').classList.add('hidden');
        document.getElementById('formVerifyAndReset').classList.remove('hidden');

        document.getElementById('stepIndicator1').className = 'flex items-center space-x-2 text-emerald-600 font-bold';
        document.getElementById('stepLine').className = 'h-0.5 flex-1 mx-3 bg-emerald-500';
        document.getElementById('stepIndicator2').className = 'flex items-center space-x-2 text-indigo-600 font-bold';
        document.getElementById('stepBadge2').className = 'w-6 h-6 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[11px] font-black';

        document.getElementById('verifyIdentifierInput').value = identifier;
        if (maskedEmail) {
            document.getElementById('otpSentMessage').innerText = `Kode OTP 6-digit telah dikirim ke alamat Gmail [${maskedEmail}]. Silakan periksa Kotak Masuk atau folder Spam.`;
        }

        startOtpCountdown(1800);
        document.getElementById('verifyResetCodeInput').focus();
    }

    function backToStepOne() {
        if (otpCountdownInterval) clearInterval(otpCountdownInterval);
        document.getElementById('formSendResetCode').classList.remove('hidden');
        document.getElementById('toggleStepContainer').classList.remove('hidden');
        document.getElementById('formVerifyAndReset').classList.add('hidden');

        document.getElementById('stepIndicator1').className = 'flex items-center space-x-2 text-indigo-600 font-bold';
        document.getElementById('stepLine').className = 'h-0.5 flex-1 mx-3 bg-slate-200';
        document.getElementById('stepIndicator2').className = 'flex items-center space-x-2 text-slate-400 font-bold';
        document.getElementById('stepBadge2').className = 'w-6 h-6 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[11px] font-black';
    }

    function toggleOtpVerificationStep() {
        const idVal = document.getElementById('resetIdentifierInput').value.trim();
        goToStepTwo(idVal || 'Akun Anda', null);
    }

    function resendOtpCode() {
        handleSendResetOtp(new Event('submit'));
    }

    // Tahap 1: Kirim Kode OTP ke Email
    async function handleSendResetOtp(e) {
        if (e && e.preventDefault) e.preventDefault();
        const identifier = document.getElementById('resetIdentifierInput').value.trim();
        const btn = document.getElementById('btnSendResetCode');

        if (!identifier) {
            Swal.fire('Identitas Kosong', 'Silakan masukkan Username Petugas Anda.', 'warning');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span>Mengirim Kode ke Gmail...</span>';

        try {
            const res = await fetch('/api/auth/password/send-code', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    role: currentResetRole,
                    reset_type: currentResetType,
                    identifier: identifier
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<span>Kirim Kode Verifikasi ke Gmail</span>';

            if (res.ok && data.success) {
                goToStepTwo(identifier, data.masked_email);

                Swal.fire({
                    icon: 'success',
                    title: 'Kode OTP Terkirim!',
                    text: data.message,
                    confirmButtonColor: '#10b981'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Mengirim Kode',
                    text: data.message || 'Terjadi kesalahan saat memproses permintaan reset.'
                });
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<span>Kirim Kode Verifikasi ke Gmail</span>';
            Swal.fire('Koneksi Gagal', err.message, 'error');
        }
    }

    // Tahap 2: Verifikasi Kode & Set Kata Sandi Baru
    async function handleVerifyAndReset(e) {
        e.preventDefault();
        const identifier = document.getElementById('verifyIdentifierInput').value.trim();
        const reset_code = document.getElementById('verifyResetCodeInput').value.trim();
        const new_secret = document.getElementById('newSecretInput').value;
        const confirm_secret = document.getElementById('confirmSecretInput').value;

        if (!reset_code || reset_code.length !== 6) {
            Swal.fire('Kode OTP Tidak Lengkap', 'Masukkan tepat 6-digit kode verifikasi yang Anda terima di Gmail.', 'warning');
            return;
        }

        if (new_secret !== confirm_secret) {
            Swal.fire('Tidak Cocok', 'Kata Sandi konfirmasi tidak cocok!', 'warning');
            return;
        }

        if (new_secret.length < 6) {
            Swal.fire('Kata Sandi Kurang', 'Kata sandi baru minimal 6 karakter!', 'warning');
            return;
        }

        const btn = document.getElementById('btnVerifySubmit');
        btn.disabled = true;
        btn.innerHTML = '<span>Memproses Penggantian...</span>';

        try {
            const res = await fetch('/api/auth/password/verify-reset', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                    role: currentResetRole,
                    reset_type: currentResetType,
                    identifier: identifier,
                    reset_code: reset_code,
                    new_password: new_secret
                })
            });

            const data = await res.json();
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan Kata Sandi Baru</span>';

            if (res.ok && data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil Direset!',
                    text: data.message,
                    confirmButtonColor: '#10b981'
                }).then(() => {
                    closeForgotModal();
                    document.getElementById('login_identifier').value = identifier;
                    document.getElementById('login_password').value = new_secret;
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Gagal',
                    text: data.message || 'Kode verifikasi tidak cocok atau sudah kadaluarsa.'
                });
            }
        } catch (err) {
            btn.disabled = false;
            btn.innerHTML = '<span>Simpan</span>';
            Swal.fire('Koneksi Gagal', err.message, 'error');
        }
    }
</script>
@endsection
