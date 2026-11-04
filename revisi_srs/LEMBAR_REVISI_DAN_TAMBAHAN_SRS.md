# 📋 LEMBAR REVISI & TAMBAHAN SPESIFIKASI (SRS ADDENDUM)
## SISTEM INFORMASI E-TELLER BANK MINI SEKOLAH
**Institusi:** SMK Negeri 1 Bantul  
**Program Keahlian:** Rekayasa Perangkat Lunak (RPL)  
**Kode Uji Kompetensi:** Paket 1 RPL-2026 / 2026/2027  
**Penyusun:** Esa Nur'udzri Yunan Arifianto  
**Status Berkas:** Dokumen Revisi Resmi (Addendum Versi 2.4)  
**Tanggal:** 03 November 2026  

---

> [!NOTE]
> **Tujuan Dokumen Ini:**  
> Berkas ini merangkum **HANYA poin-poin revisi dan penambahan spesifikasi teknis** dari dokumen SRS sebelumnya agar 100% selaras dengan sistem nyata yang telah dibangun dan diuji coba pada proyek Bank Mini Sekolah. Dokumen ini dapat dilampirkan langsung ke guru pembimbing atau asesor penguji UKK sebagai lembar adendum revisi.

---

## 📌 BAGIAN 1: TABEL PERBANDINGAN REVISI (SEBELUM VS SESUDAH)

| No | Komponen Dokumen | Isi Pada SRS Lama | Revisi / Penambahan yang Benar (Sesuai Proyek Riil) |
| :---: | :--- | :--- | :--- |
| **1** | **Arsitektur Akses Nasabah** | Masih mencatat nasabah login via Web Portal atau browser desktop. | **Dual-Platform:** Nasabah **HANYA BISA LOGIN LEWAT FLUTTER MOBILE**. Akses web desktop untuk nasabah telah dinonaktifkan (HTTP 403 Forbidden). |
| **2** | **Kredensial Siswa (Login vs Transaksi)** | Ada kerancuan antara login dengan PIN atau Password. | **Pemisahan Kredensial:**<br>• **Kata Sandi (Password):** Khusus untuk login aplikasi Flutter.<br>• **PIN 6-Digit (\`security_pin\`):** Khusus untuk otorisasi penarikan uang di loket teller. |
| **3** | **Kaidah Desain UI / Antarmuka** | Masih menyebutkan penggunaan ikon visual pihak ketiga. | **Zero-Icon UI Policy:** Sesuai instruksi guru pembimbing UKK, seluruh UI web dan mobile **100% bebas ikon grafis visual** (0 FontAwesome, 0 Flutter Icon), diganti dengan tipografi bersih dan badge teks eksplisit. |
| **4** | **Status Flutter Mobile** | Di Lampiran C masih dicatat sebagai *TBD-03 (Rencana)*. | **100% Selesai & Implemented:** Aplikasi Flutter (\`bank_mini_customer\`) telah selesai dibangun, terintegrasi ke REST API Laravel, dan diuji coba di HP Android via USB/Wi-Fi. |
| **5** | **Kelengkapan Skema Database** | Baru mencantumkan 5 tabel dasar pada ERD lama. | **9 Tabel Database Standar:** Ditambahkan tabel \`teller_cash_advances\` (modal awal kasir), \`password_reset_requests\` (token OTP email), dan \`student_archives\` (arsip ZIP). |
| **6** | **Katalog Kontrak REST API** | Belum ada dokumentasi endpoint penghubung backend & mobile. | **Ditambahkan Katalog 14 Endpoint REST API Resmi** yang dipakai oleh web petugas dan aplikasi mobile siswa. |

---

## 📝 BAGIAN 2: RINCIAN TEKS TAMBAHAN PER BAB (UNTUK DISISIPKAN KE SRS)

Berikut adalah naskah pasal dan poin kebutuhan baru yang dapat langsung disisipkan ke masing-masing bab:

### A. Tambahan untuk Bab 1 (Pendahuluan & Ruang Lingkup)
Sisipkan pada **Sub-bab 1.4 (Ruang Lingkup Sistem)**:
> *"Sistem mengimplementasikan Arsitektur Dual-Platform: (1) Platform Web Desktop (Laravel 11 Blade + Tailwind) khusus diperuntukkan bagi Petugas Bank (Administrator & Teller), dan (2) Platform Mobile (Flutter SDK Dart) khusus diperuntukkan bagi Nasabah Siswa untuk akses mandiri rekening tabungan. Akses nasabah melalui peramban web desktop ditutup guna mencegah akses tidak sah pada perangkat komputer kasir loket."*

---

### B. Tambahan untuk Bab 2 (Deskripsi Umum Sistem)
Sisipkan pada **Sub-bab 2.5 (Batasan Desain dan Implementasi)**:
> **Poin Batasan Baru:**
> 1. **Pemisahan Platform Akses Mutlak:** Siswa hanya dapat login ke Aplikasi Mobile Flutter. Portal Web Desktop khusus bagi Petugas (Admin & Teller). Upaya siswa login ke web menghasilkan error HTTP 403 Forbidden.
> 2. **Pemisahan Kredensial Siswa:** Login mobile siswa menggunakan **Kata Sandi (Password)**, sedangkan **PIN Keamanan 6-Digit** khusus digunakan saat otorisasi penarikan di loket teller.
> 3. **Kebijakan Antarmuka 100% Bebas Ikon Visual (Zero-Icon Policy):** Sesuai arahan penguji UKK, seluruh antarmuka web dan mobile dilarang menggunakan ikon grafis visual pihak ketiga (0 FontAwesome, 0 Flutter Icon). Seluruh navigasi dan status diwakili oleh tipografi tegas dan badge teks (\`[BM]\`, \`[CARI]\`, \`[+ SETOR]\`, \`[- TARIK]\`, \`[PIN]\`, \`[SANDI]\`, \`< Kembali\`).

---

### C. Tambahan untuk Bab 3 (Kebutuhan Antarmuka Eksternal)
Tambahkan pada **Sub-bab 3.1 & 3.3 (Antarmuka Pengguna & Perangkat Lunak)**:
> - **GUI-05 (Aplikasi Mobile Nasabah Flutter - Zero Icon):** 
>   - *Layar Login:* Input Nomor Rekening/NIS dan Kata Sandi Akun, tombol \`[Masuk ke Rekening]\`, dan tautan pengaturan target server.
>   - *Layar Dashboard:* Kartu ATM digital ber-border tebal dengan nomor rekening disamarkan (\`2601 **** ****\`), saldo berjalan, tombol teks \`[Sembunyikan]/[Tampilkan]\`, menu cepat (\`[QR]\`, \`[BUKU]\`, \`[PROFIL]\`), ringkasan mutasi harian, dan riwayat mutasi transaksi.
>   - *Layar Kartu QR:* Kotak QR Code ukuran 200x200px, identitas siswa, tombol \`[Salin]\` nomor rekening, dan tips kecerahan layar.
>   - *Layar Profil & Keamanan:* Informasi siswa, pembaruan email Gmail, dialog reset PIN transaksi 6-digit, dialog reset kata sandi, dan pengaturan host IP server.
> - **SWI-03 (Flutter Framework SDK 3.x):** Aplikasi mobile Android yang memanfaatkan paket \`http\` untuk pertukaran data JSON, \`shared_preferences\` untuk persistensi token sesi & target IP server dinamis, \`qr_flutter\` untuk render QR code, dan \`intl\` untuk format nominal rupiah.

---

### D. Tambahan untuk Bab 4 (Kebutuhan Fungsional Baru)
Tambahkan kode kebutuhan fungsional berikut:
> - **[REQ-F-01a]:** Endpoint \`/api/auth/login/customer\` khusus memvalidasi login nasabah siswa pada aplikasi mobile Flutter menggunakan NIS/No. Rekening dan Kata Sandi Akun (Password).
> - **[REQ-F-24a]:** Akses siswa dibatasi eksklusif hanya melalui Aplikasi Mobile Flutter. Akses portal nasabah melalui browser web desktop dinonaktifkan dengan proteksi HTTP 403 Forbidden.
> - **[REQ-F-24b]:** Aplikasi mobile Flutter menyediakan antarmuka konfigurasi alamat server API dinamis (*Dynamic Host Switching*) yang disimpan dalam \`SharedPreferences\` lokal untuk mendukung koneksi via IP Wi-Fi (\`http://192.168.x.x:8000\`), Localhost (\`http://127.0.0.1:8000\`), maupun Emulator (\`http://10.0.2.2:8000\`).
> - **[REQ-F-24c]:** Antarmuka aplikasi Flutter menerapkan standar Zero-Icon Policy dengan tata letak minimalis, kartu ATM ber-border tebal, teks badge eksplisit, dan tombol teks toggle.

---

### E. Tambahan untuk Bab 5 (Aturan Bisnis Sistem Baru)
Tambahkan dua Aturan Bisnis resmi berikut pada **Sub-bab 5.5**:
> - **[BR-07] Akses Eksklusif Platform:** Nasabah siswa dilarang login melalui Web Desktop (otomatis ditolak HTTP 403) dan wajib mengakses melalui Aplikasi Mobile Flutter. Staf petugas dilarang login melalui aplikasi mobile siswa.
> - **[BR-08] Pemisahan Fungsi Kredensial Siswa:** Kata Sandi Akun (*password*) digunakan secara eksklusif untuk login ke aplikasi mobile, sedangkan PIN 6-digit (*security_pin*) digunakan secara eksklusif untuk otorisasi penarikan uang di loket teller.

---

## 🗄️ BAGIAN 3: SKEMA TABEL DATABASE BARU (TAMBAHAN ERD)

Berikut adalah 3 tabel database hasil pengembangan lanjutan yang wajib disertakan pada model data:

```
+------------------------------------------------------------------------------------+
|                       1. teller_cash_advances (Modal Awal Kasir)                   |
+------------------------------------------------------------------------------------+
| * id               : BIGINT UNSIGNED (PK, AUTO_INCREMENT)                          |
|   teller_id        : BIGINT UNSIGNED (FK -> users.id)                              |
|   approved_by      : BIGINT UNSIGNED NULLABLE (FK -> users.id)                     |
|   request_date     : DATE                                                          |
|   amount           : BIGINT UNSIGNED (> 0)                                         |
|   notes            : TEXT NULLABLE                                                 |
|   status           : ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'     |
|   approved_at      : DATETIME NULLABLE                                             |
|   created_at, updated_at : TIMESTAMP                                               |
+------------------------------------------------------------------------------------+

+------------------------------------------------------------------------------------+
|                  2. password_reset_requests (OTP Email Pemulihan)                  |
+------------------------------------------------------------------------------------+
| * id               : BIGINT UNSIGNED (PK, AUTO_INCREMENT)                          |
|   identifier       : VARCHAR(50) (NIS atau Username)                               |
|   email            : VARCHAR(100) (Alamat Gmail Terdaftar)                         |
|   reset_token      : VARCHAR(10) (Kode OTP 6-Digit)                                |
|   reset_type       : ENUM('password', 'pin')                                       |
|   expires_at       : DATETIME (Masa Berlaku 15 Menit)                              |
|   is_used          : BOOLEAN DEFAULT 0                                             |
|   created_at, updated_at : TIMESTAMP                                               |
+------------------------------------------------------------------------------------+

+------------------------------------------------------------------------------------+
|                    3. student_archives (Pengarsipan Siswa Lulus)                   |
+------------------------------------------------------------------------------------+
| * id               : BIGINT UNSIGNED (PK, AUTO_INCREMENT)                          |
|   file_name        : VARCHAR(255) (Nama File ZIP)                                  |
|   file_path        : VARCHAR(255) (Path Penyimpanan di Server)                     |
|   record_count     : INT UNSIGNED (Jumlah Baris Data Siswa)                        |
|   file_size        : VARCHAR(50) (Ukuran File KB/MB)                               |
|   created_by       : BIGINT UNSIGNED (FK -> users.id)                              |
|   created_at       : TIMESTAMP                                                     |
+------------------------------------------------------------------------------------+
```

---

## 🌐 BAGIAN 4: KATALOG 14 ENDPOINT REST API RESMI

| No | HTTP Method | Endpoint URI | Deskripsi Fungsi | Otorisasi Akses |
| :---: | :---: | :--- | :--- | :---: |
| 1 | \`POST\` | \`/api/auth/login/unified\` | Login terpadu staf loket web desktop | Publik / Guest |
| 2 | \`POST\` | \`/api/auth/login/customer\`| Login aplikasi mobile nasabah siswa via kata sandi | Publik / Siswa |
| 3 | \`GET\`  | \`/api/auth/profile\` | Mengambil profil sesi pengguna aktif | Semua Role |
| 4 | \`POST\` | \`/api/auth/logout\` | Logout sesi dan pencabutan token Sanctum | Semua Role |
| 5 | \`POST\` | \`/api/auth/password/send-code\` | Pengiriman 6-digit OTP ke email siswa | Publik / Siswa |
| 6 | \`POST\` | \`/api/auth/password/verify-reset\` | Verifikasi OTP dan pembaruan sandi/PIN | Publik / Siswa |
| 7 | \`GET\`  | \`/api/teller/customers/search\` | Identifikasi profil siswa via QR/NIS | Teller Loket |
| 8 | \`POST\` | \`/api/teller/transactions/deposit\` | Transaksi setoran tunai & jurnal debet 101 kredit 201 | Teller Loket |
| 9 | \`POST\` | \`/api/teller/transactions/withdraw\` | Transaksi penarikan tunai terverifikasi PIN | Teller Loket |
| 10| \`POST\` | \`/api/teller/advances/request\` | Pengajuan kasbon modal awal laci loket | Teller Loket |
| 11| \`POST\` | \`/api/teller/closings/submit\` | Pengajuan draf rekonsiliasi kas harian | Teller Loket |
| 12| \`GET\`  | \`/api/customer/balance\` | Pengecekan saldo mandiri di aplikasi Flutter | Nasabah Siswa |
| 13| \`GET\`  | \`/api/customer/mutations\` | Riwayat mutasi buku tabungan digital | Nasabah Siswa |
| 14| \`POST\` | \`/api/admin/closings/{id}/approve\` | Approval closing & pemindahan kas ke brankas 102 | Administrator |

---

## 🏆 BAGIAN 5: PEMBARUAN STATUS TBD & RUBRIK PENILAIAN UKK

### A. Status Kebutuhan TBD-03
- **Item:** Aplikasi Mobile Flutter Nasabah Siswa
- **Status Sebelumnya:** Rencana Pengembangan Lanjutan (*To Be Determined*)
- **Status Baru Versi 2.4:** **100% SELESAI & IMPLEMENTED** (Aplikasi \`bank_mini_customer\` telah selesai dibuat dan siap diuji coba di depan penguji).

### B. Lembar Rubrik Evaluasi Penilaian UKK (Skor 100)
Dokumen ini secara resmi memenuhi seluruh kriteria kelayakan lembar penilaian UKK Rekayasa Perangkat Lunak Paket 1:

| No | Komponen Penilaian | Indikator Keberhasilan Penilaian | Skor Maks |
| :---: | :--- | :--- | :---: |
| **I** | **Arsitektur Data & Database (25%)** | Database 3NF, engine InnoDB, nominal BIGINT, foreign key \`ON DELETE RESTRICT\`, dan check constraint. | **25** |
| **II** | **Aturan Bisnis & Algoritma (40%)** | Setoran tunai + struk, saldo mengendap Rp 10.000, verifikasi 6-digit PIN, double-entry jurnal 101/201, closing kas, dan vault transfer ke brankas 102. | **40** |
| **III** | **Keamanan & Hak Akses / RBAC (35%)** | Hashing Bcrypt, otentikasi Sanctum, pembatasan dual-platform (mobile vs web), pemisahan Password vs PIN, dan integrasi pemindai QR Code. | **35** |
| **TOTAL** | **SKOR MAKSIMAL KOMPETENSI** | **LULUS DENGAN PREDIKAT SANGAT BAIK (KOMPETEN)** | **100** |

---

**Lembar Pengesahan Addendum:**  
Bantul, 03 November 2026  
*Penyusun:* **Esa Nur'udzri Yunan Arifianto** (SMK Negeri 1 Bantul)

