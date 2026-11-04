# 📑 DOKUMEN SKENARIO USE CASE LENGKAP
## SISTEM INFORMASI E-TELLER BANK MINI SEKOLAH
**Institusi:** SMK Negeri 1 Bantul  
**Program Keahlian:** Rekayasa Perangkat Lunak (RPL)  
**Kode Uji Kompetensi:** Paket 1 RPL-2026 / 2026/2027  
**Penyusun:** Esa Nur'udzri Yunan Arifianto  
**Format Standar:** IEEE 830 & UML Software Requirements  
**Tanggal:** 03 November 2026  

---

## 📌 DAFTAR USE CASE BERDASARKAN ROLE / AKTOR

| Kode UC | Nama Use Case | Aktor Utama | Platform |
| :---: | :--- | :--- | :---: |
| **UC-01** | Autentikasi Staf Petugas Loket (Login Web) | Administrator, Teller | Web Desktop |
| **UC-02** | Manajemen Master Anggota & Rekening Penabung Siswa | Administrator | Web Desktop |
| **UC-03** | Pengajuan Kasbon Modal Awal Loket (Cash Advance) | Teller Loket | Web Desktop |
| **UC-04** | Otorisasi / Approval Kasbon Modal Awal Teller | Administrator | Web Desktop |
| **UC-05** | Identifikasi Nasabah Siswa (QR Scanner & Input No. Rek) | Teller Loket | Web Desktop |
| **UC-06** | Transaksi Setoran Tunai (Cash Deposit) & Cetak Struk | Teller Loket | Web Desktop |
| **UC-07** | Transaksi Penarikan Tunai Terverifikasi 6-Digit PIN | Teller Loket & Nasabah | Web Desktop |
| **UC-08** | Pengajuan Rekonsiliasi Tutup Kas Harian (Cash Closing) | Teller Loket | Web Desktop |
| **UC-09** | Otorisasi Closing Kas & Pemindahan Kas ke Brankas (Vault Transfer) | Administrator | Web Desktop |
| **UC-10** | Autentikasi Mobile Nasabah (Login Flutter via Kata Sandi) | Nasabah Siswa | Flutter Mobile |
| **UC-11** | Cek Saldo Mandiri & Mutasi Rekening Digital | Nasabah Siswa | Flutter Mobile |
| **UC-12** | Tampilkan Kartu QR Digital Nasabah Siswa | Nasabah Siswa | Flutter Mobile |
| **UC-13** | Pemulihan Kata Sandi & Reset PIN Mandiri via OTP Gmail | Nasabah Siswa | Flutter Mobile |
| **UC-14** | Audit Jurnal Akuntansi Back-End & Cetak Laporan Keuangan | Administrator | Web Desktop |
| **UC-15** | Pengarsipan Data Siswa Lulus & Kenaikan Kelas Otomatis | Administrator | Web Desktop |

---

## 📋 RINCIAN SKENARIO USE CASE LENGKAP

---

### 1. UC-01: Autentikasi Staf Petugas Loket (Login Web)

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-01** |
| **Nama Use Case** | Autentikasi Staf Petugas Loket (Login Web) |
| **Aktor Utama** | Administrator, Piket Teller |
| **Platform** | Web Desktop (Laravel Blade) |
| **Deskripsi** | Proses verifikasi identitas petugas bank (Admin atau Teller) untuk mengakses hak operasional sistem back-office. |
| **Kondisi Awal (Pre-condition)** | Petugas berada di halaman login web (`/login`) dan memiliki akun aktif. |
| **Kondisi Akhir (Post-condition)**| Petugas berhasil masuk ke sesi kerja dan diarahkan ke dashboard masing-masing. |
| **Pemicu (Trigger)** | Petugas membuka browser dan mengakses URL sistem Bank Mini. |

#### Alur Utama (Main Flow):
1. Petugas memasukkan `Username` dan `Password` pada form login web.
2. Petugas menekan tombol `[Masuk ke Sistem]`.
3. Sistem memvalidasi format inputan form.
4. Sistem memeriksa kecocokan username pada tabel `users`, memastikan status akun bernilai `active`, dan memverifikasi hash Bcrypt password.
5. Sistem meregenerasi ID sesi (`Session Regeneration`) guna mencegah serangan *session fixation*.
6. Sistem memeriksa `role` pengguna:
   - Jika role = `admin`, sistem mengarahkan ke URL `/admin`.
   - Jika role = `teller`, sistem mengarahkan ke URL `/teller`.
7. Sistem menampilkan dashboard operasional sesuai hak akses pengguna.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **3a. Form Kosong / Format Salah:**  
  Sistem menampilkan pesan validasi di bawah field input: *"Username dan password wajib diisi."*
- **4a. Kredensial Tidak Cocok:**  
  Sistem menampilkan notifikasi error: *"Username atau kata sandi yang Anda masukkan salah."* Petugas tetap di halaman login.
- **4b. Akun Berstatus Non-Aktif (`inactive`):**  
  Sistem menolak login dan menampilkan pesan: *"Akun Anda telah dinonaktifkan oleh Administrator. Hubungi pihak pengelola."*
- **4c. Percobaan Login oleh Siswa di Web:**  
  Jika kredensial siswa dicoba di web portal, sistem menolak dengan proteksi HTTP 403 Forbidden (kebijakan Dual-Platform).

---

### 2. UC-02: Manajemen Master Anggota & Rekening Penabung Siswa

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-02** |
| **Nama Use Case** | Manajemen Master Anggota & Rekening Penabung Siswa |
| **Aktor Utama** | Administrator |
| **Platform** | Web Desktop |
| **Deskripsi** | Menambah, mengubah, mencari, dan mengelola rekening tabungan siswa baru. |
| **Kondisi Awal (Pre-condition)** | Admin telah login dan berada di halaman menu `[Data Siswa]` (`/admin/members`). |
| **Kondisi Akhir (Post-condition)**| Record siswa tersimpan di tabel `members` dan nomor rekening aktif di tabel `savers`. |
| **Pemicu (Trigger)** | Pendaftaran siswa baru sebagai nasabah penabung Bank Mini. |

#### Alur Utama (Main Flow):
1. Admin menekan tombol `[+ Tambah Siswa]`.
2. Sistem menampilkan modal / form pendaftaran nasabah.
3. Admin mengisi identitas: NIS, Nama Lengkap, Kelas, Jurusan, Nomor HP Orang Tua/Siswa, Alamat Email Gmail, dan Saldo Setoran Pertama (minimal Rp 10.000).
4. Admin menekan tombol `[Simpan Data]`.
5. Sistem memvalidasi keunikan NIS dan email.
6. Sistem menjalankan database transaction:
   - Menyimpan data diri siswa ke tabel `members`.
   - Mengenerate nomor rekening unik 12-digit (contoh: `260100000001`).
   - Menyimpan rekening ke tabel `savers` dengan PIN default `123456` (ter-hash) dan Password default `password123` (ter-hash).
   - Mencatat saldo awal siswa.
   - Jika ada setoran awal, mencatat transaksi awal dan entri jurnal pembukaan akun.
7. Sistem mengonfirmasi penyimpanan dan menampilkan kartu ringkasan rekening baru.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **5a. NIS Sudah Terdaftar:**  
  Sistem menampilkan notifikasi error: *"NIS sudah terdaftar dalam sistem."*
- **5b. Email Gmail Tidak Valid:**  
  Sistem menampilkan peringatan: *"Format email tidak valid. Gunakan alamat email aktif."*

---

### 3. UC-03: Pengajuan Kasbon Modal Awal Loket (Cash Advance)

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-03** |
| **Nama Use Case** | Pengajuan Kasbon Modal Awal Loket (Cash Advance) |
| **Aktor Utama** | Piket Teller |
| **Platform** | Web Desktop |
| **Deskripsi** | Teller mengajukan permintaan modal uang tunai laci loket pada awal sesi pelayanan. |
| **Kondisi Awal (Pre-condition)** | Teller telah login, laci kasir belum memiliki modal tunai untuk transaksi penarikan. |
| **Kondisi Akhir (Post-condition)**| Data pengajuan kasbon tersimpan di tabel `teller_cash_advances` berstatus `pending`. |
| **Pemicu (Trigger)** | Teller memulai jam kerja operasional loket bank. |

#### Alur Utama (Main Flow):
1. Teller membuka menu `[Kasbon Modal Awal]` di halaman teller (`/teller/advances`).
2. Teller memasukkan nominal kasbon modal laci yang dibutuhkan (misalnya: `Rp 500.000`).
3. Teller mengisi catatan keperluan (misalnya: *"Modal pecahan uang kecil operasional loket 1"*).
4. Teller menekan tombol `[Ajukan Modal Awal]`.
5. Sistem memvalidasi bahwa nominal lebih besar dari 0.
6. Sistem menyimpan permohonan ke tabel `teller_cash_advances` dengan status `pending` dan merekam ID teller.
7. Sistem menampilkan status *"Menunggu Otorisasi Admin"*.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **5a. Nominal Tidak Valid (<= 0):**  
  Sistem menolak dengan pesan: *"Nominal modal kasbon harus lebih dari Rp 0."*
- **6a. Teller Masih Memiliki Pengajuan Pending:**  
  Sistem memblokir pengajuan ganda dan menampilkan status pengajuan yang sedang berjalan.

---

### 4. UC-04: Otorisasi / Approval Kasbon Modal Awal Teller

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-04** |
| **Nama Use Case** | Otorisasi / Approval Kasbon Modal Awal Teller |
| **Aktor Utama** | Administrator |
| **Platform** | Web Desktop |
| **Deskripsi** | Admin meninjau dan memberikan persetujuan atau penolakan kasbon modal kasir. |
| **Kondisi Awal (Pre-condition)** | Terdapat pengajuan kasbon modal berstatus `pending` di antrean Admin. |
| **Kondisi Akhir (Post-condition)**| Status kasbon berubah menjadi `approved` dan modal masuk ke saldo awal sesi kasir teller. |
| **Pemicu (Trigger)** | Permintaan dana fisik dari loket teller kepada bagian keuangan/admin. |

#### Alur Utama (Main Flow):
1. Admin membuka menu `[Otorisasi Kasbon]` (`/admin/advances`).
2. Sistem menyajikan daftar permohonan kasbon yang berstatus `pending` (memuat nama teller, nominal, tanggal, dan catatan).
3. Admin meneliti kesesuaian fisik uang brankas yang akan diserahkan.
4. Admin menekan tombol `[Setujui Modal]`.
5. Sistem mengupdate record `teller_cash_advances`: status menjadi `approved`, mencatat `approved_by = Admin ID`, dan `approved_at = NOW()`.
6. Sistem mencatat penyerahan modal ke dalam buku jurnal kas.
7. Sistem mengirim pembaruan status ke sesi loket teller secara instan.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **4a. Admin Menolak Pengajuan:**  
  Admin menekan tombol `[Tolak]`, mengisikan alasan penolakan (misal: *"Kas laci shift sebelumnya masih mencukupi"*). Status berubah menjadi `rejected`.

---

### 5. UC-05: Identifikasi Nasabah Siswa (QR Scanner & Input No. Rek)

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-05** |
| **Nama Use Case** | Identifikasi Nasabah Siswa (QR Scanner & Input No. Rek) |
| **Aktor Utama** | Piket Teller |
| **Platform** | Web Desktop |
| **Deskripsi** | Mengidentifikasi profil nasabah secara cepat melalui kamera pemindai QR Code atau pencarian nomor rekening/NIS. |
| **Kondisi Awal (Pre-condition)** | Teller berada di layar utama loket transaksi (`/teller`). |
| **Kondisi Akhir (Post-condition)**| Informasi rekening nasabah terpilih dimuat ke panel formulir transaksi teller. |
| **Pemicu (Trigger)** | Nasabah siswa mendatangi loket teller untuk melakukan transaksi tabungan. |

#### Alur Utama (Main Flow):
1. Teller meminta siswa memperlihatkan Kartu QR Digital pada aplikasi Flutter HP-nya, atau menyebutkan NIS/Nomor Rekening.
2. Teller mengarahkan pemindai kamera ke layar HP siswa (atau mengetik nomor rekening pada field pencarian cepat).
3. Sistem membaca string nomor rekening hasil pemindaian secara otomatis.
4. Sistem melakukan pencarian asynchronous (`AJAX/Fetch`) ke endpoint `/api/teller/customers/search`.
5. Sistem menemukan data dan menampilkan kartu identitas nasabah:
   - Nama Lengkap Siswa & Foto Avatar
   - NIS & Kelas / Jurusan
   - Nomor Rekening Tabungan
   - Status Rekening (Aktif)
   - Saldo Berjalan (*Current Balance*)
6. Tab transaksi Setoran dan Penarikan aktif dan siap digunakan.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **4a. Data Rekening Tidak Ditemukan:**  
  Sistem menampilkan kotak notifikasi: *"Data nasabah tidak ditemukan. Periksa kembali NIS atau Nomor Rekening."*
- **4b. Rekening Dibekukan / Tidak Aktif:**  
  Sistem menampilkan badge peringatan *"REKENING TIDAK AKTIF"* dan mengunci tombol proses transaksi.

---

### 6. UC-06: Transaksi Setoran Tunai (Cash Deposit) & Cetak Struk

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-06** |
| **Nama Use Case** | Transaksi Setoran Tunai (Cash Deposit) & Cetak Struk |
| **Aktor Utama** | Piket Teller |
| **Platform** | Web Desktop |
| **Deskripsi** | Menyetorkan uang tunai siswa ke rekening tabungan dan mencatat jurnal akuntansi. |
| **Kondisi Awal (Pre-condition)** | Profil nasabah aktif telah terpilih di layar loket teller, uang fisik telah diterima di kasir. |
| **Kondisi Akhir (Post-condition)**| Saldo bertambah, jurnal kas debet/kredit tercatat, bukti transaksi dicetak. |
| **Pemicu (Trigger)** | Siswa menyerahkan uang tunai untuk ditabung. |

#### Alur Utama (Main Flow):
1. Teller memilih tab `[+ Setoran Tunai]`.
2. Teller memasukkan nominal uang yang disetorkan (minimal `Rp 5.000`).
3. Teller menginput keterangan transaksi (opsional, default: *"Setoran tunai loket"*).
4. Teller menekan tombol `[Proses Setoran Tunai]`.
5. Sistem melakukan verifikasi batas minimum nominal setoran.
6. Sistem memulai transaksi atomik database (`DB::beginTransaction()`):
   - Menghasilkan kode unik transaksi: `TRX-DEP-YYYYMMDD-XXXX`.
   - Membuat record baru di tabel `transactions` dengan tipe `deposit`, nominal, saldo akhir baru, dan snapshot nama/no rekening.
   - Menambahkan nominal ke `savers.balance`.
   - Mencatat double-entry di tabel `accounting_journals`:
     - **Debet:** Akun 101 (Kas di Teller / Kas Laci Loket)
     - **Kredit:** Akun 201 (Simpanan Nasabah / Tabungan Siswa)
7. Sistem mengeksekusi `DB::commit()`.
8. Sistem menampilkan modal pop-up konfirmasi sukses setoran beserta rincian transaksi.
9. Teller menekan tombol `[Cetak Struk]` untuk mencetak bukti slip setoran dan menyerahkannya ke nasabah.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **5a. Nominal Kurang dari Batas Minimum (< Rp 5.000):**  
  Sistem menolak dengan peringatan: *"Nominal setoran minimal adalah Rp 5.000."*
- **6a. Kegagalan Koneksi / Database:**  
  Sistem memicu `DB::rollBack()`, membatalkan seluruh perubahan saldo dan jurnal, lalu menampilkan notifikasi: *"Terjadi kesalahan sistem, transaksi dibatalkan."*

---

### 7. UC-07: Transaksi Penarikan Tunai Terverifikasi 6-Digit PIN

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-07** |
| **Nama Use Case** | Transaksi Penarikan Tunai Terverifikasi 6-Digit PIN |
| **Aktor Utama** | Piket Teller & Nasabah Siswa |
| **Platform** | Web Desktop (Otorisasi oleh Siswa di Loket) |
| **Deskripsi** | Menarik saldo tabungan siswa dengan validasi PIN keamanan 6-digit dan batas saldo mengendap. |
| **Kondisi Awal (Pre-condition)** | Profil nasabah aktif telah terpilih di layar teller, kas laci teller mencukupi. |
| **Kondisi Akhir (Post-condition)**| Saldo berkurang, jurnal tercatat, uang fisik diserahkan, struk tercetak. |
| **Pemicu (Trigger)** | Siswa mengajukan penarikan uang tabungan di loket teller. |

#### Alur Utama (Main Flow):
1. Teller memilih tab `[- Penarikan Tunai]`.
2. Teller memasukkan nominal uang yang ingin ditarik siswa.
3. Teller mempersilakan siswa mengetikkan **6-Digit PIN Keamanan** (`security_pin`) pada kolom input terlindung (berbentuk bulatan sandi `••••••`).
4. Teller menekan tombol `[Proses Penarikan]`.
5. Sistem memverifikasi kecocokan PIN dengan hash Bcrypt di tabel `savers`.
6. Sistem memverifikasi aturan saldo mengendap:  
   $$\text{Sisa Saldo} = (\text{Saldo Berjalan} - \text{Nominal Tarik}) \ge \text{Rp } 10.000$$
7. Sistem memeriksa ketersediaan saldo fisik di kas laci teller saat ini.
8. Sistem memulai transaksi atomik database (`DB::beginTransaction()`):
   - Menghasilkan kode unik transaksi: `TRX-WDR-YYYYMMDD-XXXX`.
   - Membuat record baru di tabel `transactions` dengan tipe `withdraw`.
   - Mengurangi nominal dari `savers.balance`.
   - Mencatat double-entry di tabel `accounting_journals`:
     - **Debet:** Akun 201 (Simpanan Nasabah / Pengurangan Liabilitas)
     - **Kredit:** Akun 101 (Kas di Teller / Pengeluaran Uang Kasir)
9. Sistem mengeksekusi `DB::commit()`.
10. Sistem menampilkan status transaksi berhasil dan memunculkan tombol cetak struk.
11. Teller menyerahkan lembar struk dan uang fisik kepada siswa.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **5a. PIN Salah:**  
  Sistem menolak transaksi dengan notifikasi tegas: *"Otorisasi Gagal: PIN Keamanan 6-digit tidak valid!"* Saldo nasabah tidak berubah.
- **6a. Saldo Tidak Cukup / Melanggar Saldo Mengendap (< Rp 10.000):**  
  Sistem menolak penarikan: *"Saldo tidak mencukupi. Saldo mengendap minimal Rp 10.000 harus tetap tersisa."*
- **7a. Uang Kas Laci Loket Teller Tidak Cukup:**  
  Sistem memperingatkan: *"Kas tunai di loket teller tidak mencukupi untuk melakukan penarikan ini. Ajukan kasbon modal terlebih dahulu."*

---

### 8. UC-08: Pengajuan Rekonsiliasi Tutup Kas Harian (Cash Closing)

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-08** |
| **Nama Use Case** | Pengajuan Rekonsiliasi Tutup Kas Harian (Cash Closing) |
| **Aktor Utama** | Piket Teller |
| **Platform** | Web Desktop |
| **Deskripsi** | Rekonsiliasi antara total uang fisik di laci kasir dan catatan kalkulasi matematis sistem di akhir hari. |
| **Kondisi Awal (Pre-condition)** | Jam operasional pelayanan loket bank telah berakhir. |
| **Kondisi Akhir (Post-condition)**| Dokumen closing tersimpan di tabel `cash_closings` dengan status `pending` untuk diverifikasi Admin. |
| **Pemicu (Trigger)** | Teller menutup loket dan merapikan uang fisik laci kasir. |

#### Alur Utama (Main Flow):
1. Teller membuka menu `[Tutup Kas Harian]` (`/teller/closings`).
2. Sistem secara otomatis menghitung dan menampilkan rincian kalkulasi sistem:
   - Saldo Awal / Kasbon Modal Hari Ini (`opening_balance`)
   - Total Setoran Masuk Hari Ini (`total_deposit`)
   - Total Penarikan Keluar Hari Ini (`total_withdrawal`)
   - Saldo Akhir Berdasarkan Sistem (`theoretical_balance = opening + deposit - withdrawal`)
3. Teller menghitung lembaran uang fisik di laci loket secara manual.
4. Teller menginputkan jumlah fisik uang tunai ke kolom `Nominal Uang Fisik` (`actual_balance`).
5. Sistem secara otomatis menghitung selisih (`difference = actual_balance - theoretical_balance`).
6. Teller menambahkan catatan keterangan harian.
7. Teller menekan tombol `[Ajukan Tutup Kas]`.
8. Sistem menyimpan draf rekapitulasi ke tabel `cash_closings` dengan status `pending`.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **5a. Terjadi Selisih Kas (Minus / Plus):**  
  Sistem menampilkan indikator teks selisih berwarna merah kontras dan mewajibkan teller mengisi keterangan kronologi selisih pada kolom catatan sebelum tombol submit aktif.

---

### 9. UC-09: Otorisasi Closing Kas & Pemindahan Kas ke Brankas (Vault Transfer)

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-09** |
| **Nama Use Case** | Otorisasi Closing Kas & Pemindahan Kas ke Brankas (Vault Transfer) |
| **Aktor Utama** | Administrator |
| **Platform** | Web Desktop |
| **Deskripsi** | Admin memvalidasi fisik uang closing teller dan mengeksekusi pemindahan kas laci ke brankas utama (Vault Akun 102). |
| **Kondisi Awal (Pre-condition)**| Terdapat laporan closing kas berstatus `pending` dari petugas loket. |
| **Kondisi Akhir (Post-condition)**| Record closing berstatus `approved`, jurnal vault transfer tercatat, buku kas laci tereset. |
| **Pemicu (Trigger)** | Penyerahan fisik uang tunai laci teller ke brankas pusat sekolah. |

#### Alur Utama (Main Flow):
1. Admin membuka menu `[Verifikasi Closing Kas]` (`/admin/closings`).
2. Sistem menyajikan daftar pengajuan closing kas harian yang menunggu persetujuan.
3. Admin menerima uang fisik dari teller dan mencocokkannya dengan angka `actual_balance`.
4. Admin menekan tombol `[Setujui Closing & Transfer Brankas]`.
5. Sistem memulai database transaction:
   - Memperbarui status `cash_closings` menjadi `approved`, mencatat `approved_by = Admin ID`.
   - Mengenerate jurnal akuntansi pemindahan kas brankas (*Vault Transfer*):
     - **Debet:** Akun 102 (Kas di Brankas / Khazanah Bank Utama)
     - **Kredit:** Akun 101 (Kas di Teller / Kas Laci Kasir Loket)
6. Sistem mengunci sesi teller tersebut sehingga tidak bisa melakukan transaksi tambahan untuk tanggal tersebut.
7. Sistem mengonfirmasi bahwa dana fisik telah resmi masuk ke brankas sentral.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **4a. Ditemukan Selisih Fisik yang Tidak Sesuai Laporan:**  
  Admin menekan tombol `[Tolak Closing]` disertai catatan instruksi hitung ulang. Status pengajuan kembali menjadi `rejected` / `disputed`.

---

### 10. UC-10: Autentikasi Mobile Nasabah (Login Flutter via Kata Sandi)

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-10** |
| **Nama Use Case** | Autentikasi Mobile Nasabah (Login Flutter via Kata Sandi) |
| **Aktor Utama** | Nasabah Siswa |
| **Platform** | Flutter Mobile Android (`bank_mini_customer`) |
| **Deskripsi** | Siswa masuk ke akun tabungan pribadinya di aplikasi mobile menggunakan Nomor Rekening/NIS dan Kata Sandi Akun. |
| **Kondisi Awal (Pre-condition)**| Aplikasi Flutter terpasang di HP siswa dan perangkat terhubung ke server. |
| **Kondisi Akhir (Post-condition)**| Token sesi Sanctum tersimpan di memori HP, halaman dashboard tabungan ditampilkan. |
| **Pemicu (Trigger)** | Siswa membuka ikon aplikasi Bank Mini di HP Android. |

#### Alur Utama (Main Flow):
1. Siswa membuka aplikasi mobile `Bank Mini Siswa`.
2. Siswa memasukkan `Nomor Rekening` (atau NIS) dan `Kata Sandi Akun` (Password).
3. Siswa menekan tombol `[Masuk ke Rekening]`.
4. Aplikasi mobile mengirim payload JSON ke endpoint `POST /api/auth/login/customer`.
5. Backend Laravel memvalidasi kredensial, memeriksa hash password siswa di tabel `savers`, dan memastikan status rekening aktif.
6. Backend mengenerate token autentikasi *Personal Access Token* (Laravel Sanctum).
7. Backend mengembalikan response `200 OK` berisi token dan data profil siswa.
8. Aplikasi Flutter menyimpan token dan profil ke `SharedPreferences` lokal.
9. Aplikasi mengarahkan siswa ke Layar Utama (*Dashboard Rekening Digital*).

#### Alur Alternatif & Eksepsi (Exception Flow):
- **5a. Kredensial Salah:**  
  Backend mengembalikan error `401 Unauthorized`. Aplikasi Flutter menampilkan snackbar merah: *"Nomor rekening atau kata sandi tidak cocok."*
- **5b. Server Tidak Terjangkau (Masalah Jaringan/IP Wi-Fi):**  
  Aplikasi menangkap exception koneksi dan memunculkan pop-up opsi: *"Koneksi Gagal. Periksa IP Target Server"* dengan tombol buka dialog pengaturan host server.

---

### 11. UC-11: Cek Saldo Mandiri & Mutasi Rekening Digital

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-11** |
| **Nama Use Case** | Cek Saldo Mandiri & Mutasi Rekening Digital |
| **Aktor Utama** | Nasabah Siswa |
| **Platform** | Flutter Mobile Android |
| **Deskripsi** | Menampilkan informasi saldo rekening dan daftar riwayat mutasi transaksi buku tabungan digital secara mandiri. |
| **Kondisi Awal (Pre-condition)**| Siswa telah login dan memegang token otentikasi aktif di aplikasi Flutter. |
| **Kondisi Akhir (Post-condition)**| Rincian saldo dan daftar transaksi historis ditampilkan di layar HP. |
| **Pemicu (Trigger)** | Siswa mengakses dashboard aplikasi mobile. |

#### Alur Utama (Main Flow):
1. Aplikasi secara otomatis memanggil endpoint `GET /api/customer/balance` dan `GET /api/customer/mutations` dengan header `Bearer Token`.
2. Backend mengambil data saldo dan 20 transaksi terakhir milik siswa bersangkutan.
3. Layar aplikasi menampilkan:
   - Kartu ATM digital dengan border tegas, nomor rekening disamarkan (`2601 **** ****`).
   - Saldo Berjalan (misal: `Rp 245.000`).
   - Tombol toggle privasi saldo: `[Sembunyikan]` / `[Tampilkan]`.
   - Ringkasan harian: Total Masuk vs Total Keluar.
   - Daftar riwayat transaksi: Tanggal/jam, kode transaksi, label badge `[+ SETOR]` warna hijau atau `[- TARIK]` warna merah, dan nominal transaksi.
4. Siswa dapat melakukan gestur sentuh *tarik-ke-bawah (pull-to-refresh)* untuk memperbarui data saldo mutasi kapan saja.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **1a. Sesi Token Kedaluwarsa:**  
  Backend mengembalikan `401 Unauthenticated`. Aplikasi membersihkan data lokal dan mengembalikan siswa ke layar login.

---

### 12. UC-12: Tampilkan Kartu QR Digital Nasabah Siswa

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-12** |
| **Nama Use Case** | Tampilkan Kartu QR Digital Nasabah Siswa |
| **Aktor Utama** | Nasabah Siswa |
| **Platform** | Flutter Mobile Android |
| **Deskripsi** | Menghasilkan kode QR digital resmi berbasis nomor rekening siswa untuk dipindai oleh teller loket sekolah. |
| **Kondisi Awal (Pre-condition)**| Siswa berada di dalam aplikasi mobile. |
| **Kondisi Akhir (Post-condition)**| Kode QR 200x200px tampil jernih di layar HP siswa. |
| **Pemicu (Trigger)** | Siswa menekan menu navigasi cepat `[QR]` di dashboard mobile. |

#### Alur Utama (Main Flow):
1. Siswa menekan tombol menu `[QR]` pada navigasi aplikasi.
2. Aplikasi merender layar Kartu QR Digital:
   - Komponen generator QR Code (`qr_flutter`) merender matriks QR 2D yang berisi nomor rekening siswa.
   - Menampilkan Nama Siswa, NIS, Kelas, dan Nomor Rekening.
   - Menampilkan tombol aksi teks `[Salin Nomor Rekening]`.
   - Menampilkan catatan petunjuk: *"Arahkan kode QR ini ke kamera scanner loket Bank Mini."*
3. Siswa mengarahkan layar ponsel ke hadapan kamera scanner teller untuk identifikasi tanpa sentuh.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **2a. Tombol Salin Ditekan:**  
  Nomor rekening tersalin ke clipboard sistem Android dan muncul notifikasi: *"Nomor rekening berhasil disalin."*

---

### 13. UC-13: Pemulihan Kata Sandi & Reset PIN Mandiri via OTP Gmail

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-13** |
| **Nama Use Case** | Pemulihan Kata Sandi & Reset PIN Mandiri via OTP Gmail |
| **Aktor Utama** | Nasabah Siswa |
| **Platform** | Flutter Mobile Android & Mail Server |
| **Deskripsi** | Memulihkan akses akun siswa yang lupa kata sandi atau mereset PIN transaksi 6-digit menggunakan verifikasi One-Time Password (OTP) ke Gmail. |
| **Kondisi Awal (Pre-condition)**| Siswa lupa kata sandi atau ingin mengubah PIN, dan memiliki alamat Gmail terdaftar. |
| **Kondisi Akhir (Post-condition)**| Kata sandi atau PIN baru ter-update di database setelah kode OTP 6-digit terverifikasi. |
| **Pemicu (Trigger)** | Siswa menekan link `[Lupa Sandi?]` pada layar login atau menu `[Reset PIN]` di profil. |

#### Alur Utama (Main Flow):
1. Siswa membuka menu `[Lupa Kata Sandi / Reset PIN]` di aplikasi Flutter.
2. Siswa menginputkan `Nomor Rekening / NIS` dan alamat `Email Gmail` terdaftar.
3. Siswa menekan tombol `[Kirim Kode OTP]`.
4. Sistem memeriksa kecocokan data rekening dan email.
5. Sistem mengenerate kode angka acak 6-digit OTP, menyimpannya di tabel `password_reset_requests` dengan masa berlaku 15 menit (`expires_at`), dan mengirimkannya ke inbox Gmail siswa.
6. Siswa membuka email Gmail-nya dan melihat 6-digit kode OTP.
7. Siswa memasukkan kode OTP 6-digit, diikuti Kata Sandi Baru atau PIN Baru pada form aplikasi Flutter.
8. Siswa menekan tombol `[Konfirmasi Perubahan]`.
9. Sistem memverifikasi kode OTP:
   - Memastikan kode cocok, belum kedaluwarsa (`expires_at > NOW()`), dan belum terpakai (`is_used = 0`).
   - Melakukan hash kredensial baru dengan Bcrypt.
   - Memperbarui kredensial di tabel `savers`.
   - Menandai kode OTP menjadi `is_used = 1`.
10. Aplikasi menampilkan pesan sukses dan meminta siswa melakukan login kembali menggunakan kredensial baru.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **4a. Email Tidak Cocok dengan Data Nasabah:**  
  Sistem menampilkan notifikasi: *"Alamat email tidak sesuai dengan data rekening siswa."*
- **9a. Kode OTP Salah atau Kedaluwarsa (> 15 Menit):**  
  Sistem menolak update dan menampilkan error: *"Kode OTP tidak valid atau telah kedaluwarsa. Silakan minta kode baru."*

---

### 14. UC-14: Audit Jurnal Akuntansi Back-End & Cetak Laporan Keuangan

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-14** |
| **Nama Use Case** | Audit Jurnal Akuntansi Back-End & Cetak Laporan Keuangan |
| **Aktor Utama** | Administrator |
| **Platform** | Web Desktop |
| **Deskripsi** | Meninjau buku besar jurnal double-entry akuntansi perbankan dan mencetak laporan transaksi resmi per periode. |
| **Kondisi Awal (Pre-condition)**| Admin telah login di sistem web. |
| **Kondisi Akhir (Post-condition)**| Laporan audit tampil seimbang (*balance*) dan siap diekspor/dicetak format A4. |
| **Pemicu (Trigger)** | Kebutuhan pemeriksaan berkala atau pelaporan pertanggungjawaban operasional bank mini. |

#### Alur Utama (Main Flow):
1. Admin membuka menu `[Jurnal Akuntansi]` (`/admin/journals`) atau `[Laporan]` (`/admin/reports`).
2. Admin menentukan parameter filter: rentang tanggal (*Date Range*), filter petugas teller, atau filter jenis transaksi.
3. Admin menekan tombol `[Terapkan Filter]`.
4. Sistem memuat tabel jurnal akuntansi double-entry yang memuat:
   - Waktu & Kode Transaksi
   - Kode Akun (101: Kas Teller, 102: Kas Brankas, 201: Simpanan Nasabah)
   - Kolom Nominal Debet dan Kredit
   - Rekapitulasi Total Debet dan Total Kredit (Status: *Balance*)
5. Admin menekan tombol `[Cetak Laporan PDF / A4]`.
6. Sistem memformat layout cetak resmi ber-kop instansi SMK Negeri 1 Bantul lengkap dengan kolom tanda tangan Admin dan Penguji/Kepala Program.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **4a. Ditemukan Ketidakseimbangan (Out of Balance):**  
  Sistem menandai baris jurnal dengan peringatan warna merah untuk investigasi transaksi terkait.

---

### 15. UC-15: Pengarsipan Data Siswa Lulus & Kenaikan Kelas Otomatis

| Atribut | Deskripsi Spesifikasi |
| :--- | :--- |
| **Kode Use Case** | **UC-15** |
| **Nama Use Case** | Pengarsipan Data Siswa Lulus & Kenaikan Kelas Otomatis |
| **Aktor Utama** | Administrator |
| **Platform** | Web Desktop |
| **Deskripsi** | Memproses kenaikan tingkat kelas siswa aktif dan memaketkan data alumni kelas XII yang telah lulus ke dalam arsip file ZIP terenkripsi. |
| **Kondisi Awal (Pre-condition)**| Pergantian tahun ajaran akademik persekolahan. |
| **Kondisi Akhir (Post-condition)**| Kelas siswa aktif diperbarui, data siswa lulus diarsipkan ke tabel `student_archives`. |
| **Pemicu (Trigger)** | Rapat pleno kenaikan kelas dan kelulusan siswa sekolah. |

#### Alur Utama (Main Flow):
1. Admin membuka menu `[Manajemen Akademik]` (`/admin/academic`).
2. Admin memilih tombol `[Proses Kenaikan Kelas Otomatis]`.
3. Sistem memperbarui tingkat kelas: Siswa kelas X naik ke kelas XI, siswa kelas XI naik ke kelas XII.
4. Untuk rombongan belajar kelas XII yang berstatus lulus, Admin memilih opsi `[Arsipkan Data Alumni]`.
5. Sistem mengekspor rekap profil nasabah dan mutasi historis siswa bersangkutan ke file arsip format `.ZIP`.
6. Sistem menyimpan file di storage server dan mencatat metadata ke tabel `student_archives` (nama file, path, jumlah record, ukuran file, user pembuat).
7. Admin mengunduh berkas arsip ZIP tersebut sebagai data cadangan legalitas sekolah.

#### Alur Alternatif & Eksepsi (Exception Flow):
- **4a. Masih Terdapat Saldo Positif pada Siswa Lulus:**  
  Sistem menampilkan konfirmasi peringatan nasabah yang belum menarik saldo tabungan akhir sebelum proses pengarsipan final dieksekusi.

---

## 🏛️ HUBUNGAN USE CASE DENGAN TABEL BASIS DATA

| Kode UC | Nama Use Case | Tabel Database yang Terlibat |
| :---: | :--- | :--- |
| **UC-01** | Autentikasi Staf Petugas Web | `users` |
| **UC-02** | Manajemen Master Anggota & Rekening | `members`, `savers`, `transactions`, `accounting_journals` |
| **UC-03** | Pengajuan Kasbon Modal Awal | `teller_cash_advances`, `users` |
| **UC-04** | Otorisasi Kasbon Modal Awal | `teller_cash_advances`, `accounting_journals`, `users` |
| **UC-05** | Identifikasi Nasabah Siswa | `members`, `savers` |
| **UC-06** | Transaksi Setoran Tunai | `savers`, `transactions`, `accounting_journals`, `users` |
| **UC-07** | Transaksi Penarikan Tunai PIN | `savers`, `transactions`, `accounting_journals`, `users` |
| **UC-08** | Pengajuan Closing Kas Harian | `cash_closings`, `transactions`, `users` |
| **UC-09** | Otorisasi Closing & Brankas 102 | `cash_closings`, `accounting_journals`, `users` |
| **UC-10** | Autentikasi Mobile Flutter Nasabah | `savers`, `members`, `personal_access_tokens` |
| **UC-11** | Cek Saldo & Mutasi Digital | `savers`, `transactions` |
| **UC-12** | Tampilkan Kartu QR Digital | `savers`, `members` |
| **UC-13** | Reset Sandi & PIN via OTP Gmail | `password_reset_requests`, `savers` |
| **UC-14** | Audit Jurnal & Cetak Laporan | `accounting_journals`, `transactions`, `users` |
| **UC-15** | Arsip Siswa Lulus & Kenaikan | `student_archives`, `members`, `savers`, `users` |

---

**Lembar Pengesahan Dokumen Skenario Use Case:**  
Bantul, 03 November 2026  
*Penyusun Proyek:* **Esa Nur'udzri Yunan Arifianto**  
*Program Keahlian:* Rekayasa Perangkat Lunak (RPL) - SMK Negeri 1 Bantul

