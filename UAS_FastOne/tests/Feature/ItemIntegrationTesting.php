<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Layanan;
use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\PermintaanLayanan;

class ItemIntegrationTesting extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // PENGUJI 1: Andri Darmawan (Modul Integrasi Pelanggan & Admin)
    // =========================================================================

    /**
     * IT-01: TestIntegrasi Front-to-Back
     * Skenario: Pengiriman data dari Pelanggan ke Admin.
     */
    public function test_integrasi_front_to_back()
    {
        // 1. Persiapan Data Lengkap (Semua kolom NOT NULL diisi)
        $pelanggan = User::factory()->create(['name' => 'Andre Pelanggan', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        
        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Pasang Wifi Baru',
            'slug' => 'pasang-wifi-baru',
            'judul_halaman' => 'Layanan Pasang Wifi',
            'deskripsi_singkat' => '-',
            'konten_lengkap' => '-',
            'gambar' => 'default.jpg'
        ]);
        Layanan::reguard();

        // 2. Pelanggan mengirim data ke sistem
        $this->actingAs($pelanggan)->post('/permintaan-layanan', [
            'layanan_id' => $layanan->id,
            'telepon_pengguna' => '081234567890',
            'pesan' => 'Pesan Testing Integrasi'
        ]);

        // 3. Admin membuka halaman permintaan
        $response = $this->actingAs($admin)->get('/admin/permintaan');

        // 4. Verifikasi data pelanggan terintegrasi
        $response->assertStatus(200);
        $response->assertSee('Andre Pelanggan');
        
        // Memastikan data tersimpan di database (lebih akurat daripada assertSee jika kolom pesan tidak muncul di tabel)
        $this->assertDatabaseHas('permintaan_layanans', [
            'user_id' => $pelanggan->id,
            'pesan' => 'Pesan Testing Integrasi'
        ]);
    }

    /**
     * IT-03: TestIntegrasi Profil & Dashboard Pelanggan
     * Skenario: Update profil user → Data tetap sinkron di halaman Akun Saya.
     */
    public function test_integrasi_profil_dashboard_pelanggan()
    {
        // 1. Persiapan Data: User dengan data Pelanggan yang sudah terdaftar
        $user = User::factory()->create(['name' => 'Nama Lama', 'email_verified_at' => now()]);

        Paket::unguard();
        $paket = Paket::create([
            'nama' => 'Paket Basic',
            'slug' => 'paket-basic',
            'judul' => 'Paket Basic Internet',
            'harga' => 150000,
            'deskripsi' => 'Paket internet dasar',
            'gambar' => 'default.jpg',
            'fitur' => 'Internet 10 Mbps'
        ]);
        Paket::reguard();

        Pelanggan::unguard();
        Pelanggan::create([
            'nama_pelanggan' => $user->name,
            'email' => $user->email,
            'telepon' => '081234567890',
            'alamat' => 'Jl. Testing No. 1',
            'paket_id' => $paket->id,
            'tanggal_bergabung' => now()->toDateString(),
            'status' => 'Aktif'
        ]);
        Pelanggan::reguard();

        // 2. User update profil (ubah nama)
        $this->actingAs($user)->patch('/profile', [
            'name' => 'Nama Baru',
            'email' => $user->email,
        ]);

        // 3. User akses halaman Akun Saya
        $responseAkun = $this->actingAs($user->fresh())->get('/akun-saya');

        // 4. Verifikasi: nama berubah di users, akun-saya tetap bisa diakses, relasi pelanggan tidak terputus
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nama Baru'
        ]);
        $responseAkun->assertStatus(200);
        $this->assertDatabaseHas('pelanggans', [
            'email' => $user->email,
            'paket_id' => $paket->id
        ]);
    }


    // =========================================================================
    // PENGUJI 2: Muhammad Fakhrudin (Modul Integrasi Layanan & Admin)
    // =========================================================================

    /**
     * IT-02: TestIntegrasi Routing Admin
     * Skenario: Passing parameter ID antar halaman Admin.
     */
    public function test_integrasi_routing_admin()
    {
        // 1. Persiapan Data Lengkap (Menambahkan judul_halaman untuk menghindari error)
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Layanan Routing', 
            'slug' => 'layanan-routing', 
            'judul_halaman' => 'Judul Halaman Routing', // FIX: Kolom wajib diisi
            'deskripsi_singkat' => '-',
            'konten_lengkap' => '-',
            'gambar' => 'test.jpg'
        ]);
        Layanan::reguard();
        
        PermintaanLayanan::unguard();
        $permintaan = PermintaanLayanan::create([
            'user_id' => $user->id,
            'layanan_id' => $layanan->id,
            'telepon_pengguna' => '0888999000',
            'status' => 'Baru'
        ]);
        PermintaanLayanan::reguard();

        // 2. Admin mengakses rute edit
        $response = $this->actingAs($admin)->get('/admin/permintaan/' . $permintaan->id . '/edit');

        // 3. Verifikasi
        $response->assertStatus(200);
        $response->assertSee('0888999000');
    }

    /**
     * IT-04: TestIntegrasi Alur Langganan End-to-End
     * Skenario: Pelanggan pilih paket → subscribe → data muncul di panel Admin.
     */
    public function test_integrasi_alur_langganan()
    {
        // 1. Persiapan Data
        $pelanggan = User::factory()->create(['name' => 'Budi Pelanggan', 'email_verified_at' => now()]);
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        Paket::unguard();
        $paket = Paket::create([
            'nama' => 'Paket Premium',
            'slug' => 'paket-premium',
            'judul' => 'Paket Premium Internet',
            'harga' => 500000,
            'deskripsi' => 'Internet kecepatan tinggi',
            'gambar' => 'premium.jpg',
            'fitur' => 'Internet 100 Mbps, TV Kabel'
        ]);
        Paket::reguard();

        // 2. Pelanggan submit form berlangganan
        $this->actingAs($pelanggan)->post('/langganan', [
            'paket_id' => $paket->id,
            'telepon' => '089876543210',
            'alamat' => 'Jl. Integrasi No. 4'
        ]);

        // 3. Admin membuka halaman daftar pelanggan
        $response = $this->actingAs($admin)->get('/pelanggan');

        // 4. Verifikasi: data langganan tersimpan dan muncul di panel admin
        $this->assertDatabaseHas('pelanggans', [
            'email' => $pelanggan->email,
            'paket_id' => $paket->id
        ]);
        $response->assertStatus(200);
        $response->assertSee('Budi Pelanggan');
    }


    // =========================================================================
    // PENGUJI 3: Miftahul Ulum (Modul Integrasi Paket & Status Admin)
    // =========================================================================

    /**
     * IT-05: TestIntegrasi CRUD Paket & Halaman Publik
     * Skenario: Admin buat Paket baru → Halaman publik menampilkan paket.
     */
    public function test_integrasi_crud_paket_halaman_publik()
    {
        // 1. Admin membuat paket baru
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);

        $this->actingAs($admin)->post('/paket', [
            'nama' => 'Paket Fiber Optik',
            'slug' => 'paket-fiber-optik',
            'harga' => '750000',
            'gambar' => 'fiber.jpg',
            'judul' => 'Paket Fiber Optik Super Cepat',
            'deskripsi' => 'Internet tercepat untuk keluarga',
            'fitur' => "Internet 200 Mbps\nTV 100 Channel\nFree Router"
        ]);

        // 2. Pengunjung publik mengakses halaman detail paket via slug
        $response = $this->get('/paket/paket-fiber-optik');

        // 3. Verifikasi: data tersimpan di DB dan halaman publik bisa diakses
        $this->assertDatabaseHas('pakets', ['nama' => 'Paket Fiber Optik']);
        $response->assertStatus(200);
    }

    /**
     * IT-06: TestIntegrasi Histori Status Permintaan
     * Skenario: Admin ubah status permintaan → terverifikasi di database dan halaman admin.
     */
    public function test_integrasi_histori_status()
    {
        // 1. Persiapan Data
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create(['email_verified_at' => now()]);

        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Layanan Histori',
            'slug' => 'layanan-histori',
            'judul_halaman' => 'Layanan Histori Status',
            'deskripsi_singkat' => '-',
            'konten_lengkap' => '-',
            'gambar' => 'default.jpg'
        ]);
        Layanan::reguard();

        PermintaanLayanan::unguard();
        $permintaan = PermintaanLayanan::create([
            'user_id' => $user->id,
            'layanan_id' => $layanan->id,
            'telepon_pengguna' => '081111222333',
            'status' => 'Baru'
        ]);
        PermintaanLayanan::reguard();

        // 2. Admin mengubah status permintaan
        $this->actingAs($admin)->put('/admin/permintaan/' . $permintaan->id, [
            'status' => 'Sudah Dihubungi'
        ]);

        // 3. Admin membuka halaman daftar permintaan
        $response = $this->actingAs($admin)->get('/admin/permintaan');

        // 4. Verifikasi: status berubah di DB dan tampil di halaman admin
        $this->assertDatabaseHas('permintaan_layanans', [
            'id' => $permintaan->id,
            'status' => 'Sudah Dihubungi'
        ]);
        $response->assertStatus(200);
        $response->assertSee('Sudah Dihubungi');
    }
}