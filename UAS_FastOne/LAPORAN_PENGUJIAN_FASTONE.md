# Laporan Hasil Pengujian Proyek FastOne

## 1. Profil Aplikasi
**FastOne** adalah platform manajemen layanan dan berlangganan berbasis web yang dirancang untuk mempermudah operasional bisnis penyedia jasa (seperti penyedia layanan internet atau konsultasi teknis).

**Fungsi Utama Aplikasi:**
- **Manajemen Pengguna:** Registrasi dan login pelanggan serta pengelolaan profil.
- **Katalog Layanan & Paket:** Menampilkan berbagai jenis layanan dan paket berlangganan kepada publik.
- **Permintaan Layanan:** Memungkinkan pelanggan yang terautentikasi untuk mengajukan permintaan layanan tertentu atau konsultasi.
- **Manajemen Langganan:** Pelanggan dapat memilih dan mendaftar paket layanan yang tersedia.
- **Panel Admin:** Dashboard khusus untuk administrator mengelola paket, data pelanggan, dan memproses status permintaan layanan.

---

## 2. Test Plan
### Strategi Pengujian
Pengujian dilakukan menggunakan pendekatan **Automated Testing** dan **Manual Testing**:
1. **Unit Testing:** Menguji fungsi-fungsi individual dan logika bisnis pada model dan controller.
2. **Integration Testing:** Menguji alur data antar komponen (misalnya dari Form ke Database ke Dashboard Admin).
3. **System Testing (Black Box):** Menguji fungsionalitas aplikasi secara menyeluruh dari perspektif pengguna tanpa melihat kode internal.

### Jadwal Pengujian
| Aktivitas | Tanggal |
| :--- | :--- |
| Perancangan Test Case | 1 Mei 2026 |
| Pelaksanaan Unit & Integration Testing | 3 Mei 2026 |
| Pelaksanaan System Testing | 4 Mei 2026 |
| Pelaporan Bug & Perbaikan | 5 Mei 2026 |
| Finalisasi Laporan | 5 Mei 2026 |

---

## 3. Pelaksanaan Pengujian

### A. Unit Testing (Total 17 Unit)
Dilakukan menggunakan PHPUnit pada file `tests/Feature/ItemUnitTesting.php`.

| ID | Fungsi yang Diuji | Skenario | Hasil |
| :--- | :--- | :--- | :--- |
| UT-01 | Registrasi User | Mendaftarkan akun baru melalui endpoint `/register`. | **PASS** |
| UT-02 | Pengajuan Konsultasi | Mengirim form permintaan layanan oleh pelanggan yang login. | **PASS** |
| UT-03 | Berlangganan Paket | Melakukan proses upgrade/pemilihan paket layanan. | **PASS** |
| UT-04 | Update Status Admin | Admin mengubah status permintaan layanan (misal: 'Baru' ke 'Selesai'). | **PASS** |
| UT-05 | Update Profil | Mengubah nama pengguna melalui halaman profil. | **PASS** |
| UT-06 | Login Valid | Masuk ke sistem dengan email & password yang benar. | **PASS** |
| UT-07 | Login Invalid | Masuk ke sistem dengan password yang salah. | **PASS** |
| UT-08 | Logout | Mengakhiri sesi pengguna secara aman. | **PASS** |
| UT-09 | Akses Beranda | Memastikan halaman utama publik dapat diakses. | **PASS** |
| UT-10 | Detail Layanan | Mengakses halaman detail salah satu layanan. | **PASS** |
| UT-11 | Detail Paket | Mengakses halaman detail salah satu paket. | **PASS** |
| UT-12 | Admin: Tambah Paket | Admin membuat data paket baru di database. | **PASS** |
| UT-13 | Admin: Update Paket | Admin mengubah informasi paket yang sudah ada. | **PASS** |
| UT-14 | Admin: Hapus Paket | Admin menghapus data paket dari database. | **PASS** |
| UT-15 | Admin: List User | Admin melihat daftar pelanggan yang terdaftar. | **PASS** |
| UT-16 | Admin: Edit Layanan | Admin mengubah konten/judul pada halaman layanan. | **PASS** |
| UT-17 | User: Akun Saya | Pelanggan mengakses dashboard pribadi mereka. | **PASS** |

### B. Integration Testing (Minimal 2 Integrasi)
Dilakukan pada file `tests/Feature/ItemIntegrationTesting.php`.

| ID | Integrasi | Skenario | Hasil |
| :--- | :--- | :--- | :--- |
| IT-01 | Front-to-Back Flow | Data yang dikirim pelanggan muncul di dashboard list permintaan admin. | **PASS** |
| IT-02 | Admin Routing | Passing ID permintaan dari list ke halaman edit status tanpa error. | **PASS** |

### C. System Testing (10 Test Case Functional - Black Box)
Sesuai dengan instruksi panduan tugas besar:

| ID | Fitur | Langkah Pengujian | Ekspektasi | Hasil |
| :--- | :--- | :--- | :--- | :--- |
| **ST-01** | **Registrasi** | Masukkan Nama, Email, Password pada form daftar. | Akun tersimpan & dialihkan ke halaman 'Akun Saya'. | **PASS** |
| **ST-02** | **Login** | Masukkan Email & Password yang sudah terdaftar. | Masuk ke dashboard sesuai role (Admin/User). | **PASS** |
| **ST-03** | **Logout** | Klik tombol 'Keluar' pada navigasi. | Sesi dihapus & kembali ke halaman landing page. | **PASS** |
| **ST-04** | **Pesan Layanan** | Pilih layanan, isi form telepon & pesan, klik kirim. | Muncul notifikasi sukses & data masuk ke admin. | **PASS** |
| **ST-05** | **Langganan Paket** | Klik tombol 'Berlangganan' pada paket tertentu. | Dialihkan ke form langganan & data tercatat. | **PASS** |
| **ST-06** | **Proteksi Rute** | Pelanggan mencoba akses URL `/dashboard` (admin). | Sistem menolak akses & menampilkan error 403. | **PASS** |
| **ST-07** | **Manajemen Paket** | Admin menambah paket baru melalui panel admin. | Paket baru muncul di daftar paket publik. | **PASS** |
| **ST-08** | **Update Status** | Admin mengubah status pesanan menjadi 'Selesai'. | Status diperbarui di database & tampilan admin. | **PASS** |
| **ST-09** | **Edit Profil** | User mengubah Nama Lengkap di pengaturan profil. | Perubahan tersimpan & tampil di header aplikasi. | **PASS** |
| **ST-10** | **Validasi Form** | Kirim form registrasi tanpa mengisi email. | Muncul pesan peringatan 'The email field is required'. | **PASS** |

---

## 4. Bug Report
Dokumentasi masalah yang ditemukan selama proses pengujian.

| ID | Deskripsi Bug | Severity | Status |
| :--- | :--- | :--- | :--- |
| BUG-01 | Gambar default tidak muncul jika kolom `gambar` di database diisi manual tanpa file fisik. | Medium | Fixed |
| BUG-02 | Validasi nomor telepon belum membatasi karakter non-angka (masih bisa input huruf). | Low | Open |
| BUG-03 | Tombol "Hapus" di daftar pelanggan tidak memiliki konfirmasi (langsung hapus). | Medium | Fixed |
| BUG-04 | View [profile.edit] tidak ditemukan saat mengakses rute `/profile`. | High | Open |

---

## 5. Kesimpulan
Berdasarkan hasil pengujian yang telah dilakukan, aplikasi **FastOne** telah memenuhi sebagian besar kriteria fungsionalitas utama. Unit testing dan Integration testing pada modul krusial (registrasi, layanan, admin) menunjukkan skor kelulusan 100%. Namun, ditemukan kendala pada fitur pengelolaan profil.

**Analisis:** Aplikasi **LAYAK RILIS DENGAN PERBAIKAN (BETA)**. Alur utama bisnis (pemesanan layanan) sudah berjalan dengan baik, namun BUG-04 (halaman profil) harus diperbaiki sebelum rilis publik sepenuhnya agar pengguna dapat mengelola akun mereka dengan baik.
