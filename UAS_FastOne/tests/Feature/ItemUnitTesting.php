<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Layanan;
use App\Models\Paket;
use App\Models\PermintaanLayanan;

class ItemUnitTesting extends TestCase
{
    use RefreshDatabase;

    /**
     * UT-01: Modul Autentikasi (/register) - SUDAH PASS
     */
    public function test_modul_autentikasi_register()
    {
        $response = $this->post('/register', [
            'name' => 'Andre Mahasiswa',
            'email' => 'andre.test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $this->assertDatabaseHas('users', ['email' => 'andre.test@example.com']);
        $response->assertStatus(302);
    }

    /**
     * UT-02: Pengajuan Form Konsultasi
     */
    public function test_pengajuan_form_konsultasi()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Konsultasi Teknis',
            'judul_halaman' => 'Konsultasi Teknis', 
            'slug' => 'konsultasi-teknis', 
            'deskripsi_singkat' => '-', 
            'konten_lengkap' => '-',
            'gambar' => 'default.jpg'
        ]);
        Layanan::reguard();

        $response = $this->actingAs($user)->post('/permintaan-layanan', [
            'layanan_id' => $layanan->id,
            'telepon_pengguna' => '08812345678',
            'pesan' => 'Ingin konsultasi wifi'
        ]); 

        $this->assertDatabaseHas('permintaan_layanans', [
            'telepon_pengguna' => '08812345678'
        ]);
    }

    /**
     * UT-03: Pengajuan Form Upgrade Paket
     */
    public function test_pengajuan_form_upgrade_paket()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        Paket::unguard();
        $paket = Paket::create([
            'nama' => 'Paket Pro', 
            'judul' => 'Paket Pro Premium', // FIX: Tambahkan judul
            'slug' => 'paket-pro', 
            'harga' => 500000, 
            'deskripsi' => 'Test Upgrade',
            'gambar' => 'default.jpg',
            'fitur' => 'Koneksi Stabil, Support 24/7'
        ]);
        Paket::reguard();

        $response = $this->actingAs($user)->post('/langganan', [
            'paket_id' => $paket->id,
            'telepon' => '081234567890',
            'alamat' => 'Jl. Testing No. 1'
        ]); 

        $this->assertDatabaseHas('pelanggans', [
            'email' => $user->email,
            'paket_id' => $paket->id
        ]);
    }

    /**
     * UT-04: Update Status oleh Admin
     */
    public function test_update_status_oleh_admin()
    {
        $admin = User::factory()->create(['role' => 'admin', 'email_verified_at' => now()]);
        $user = User::factory()->create(['email_verified_at' => now()]);
        
        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Layanan Test', 
            'judul_halaman' => 'Layanan Test', 
            'slug' => 'test',
            'deskripsi_singkat' => '-',
            'konten_lengkap' => '-',
            'gambar' => 'default.jpg' // FIX: Tambahkan gambar
        ]); 
        Layanan::reguard();
        
        PermintaanLayanan::unguard();
        $permintaan = PermintaanLayanan::create([
            'user_id' => $user->id,
            'layanan_id' => $layanan->id,
            'telepon_pengguna' => '081234567890',
            'status' => 'Baru'
        ]);
        PermintaanLayanan::reguard();

        $response = $this->actingAs($admin)->put('/admin/permintaan/' . $permintaan->id, [
            'status' => 'Selesai'
        ]); 

        $this->assertDatabaseHas('permintaan_layanans', [
            'id' => $permintaan->id,
            'status' => 'Selesai'
        ]);
    }

    /**
     * UT-05: Update Profil Pengguna
     */
    public function test_update_profil_pengguna()
    {
        $user = User::factory()->create(['name' => 'User Lama', 'email_verified_at' => now()]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => 'User Baru',
            'email' => $user->email,
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'User Baru'
        ]);
        $response->assertSessionHasNoErrors();
    }

    /**
     * UT-06: Login dengan Kredensial Valid
     */
    public function test_login_valid()
    {
        $user = User::factory()->create([
            'password' => bcrypt($password = 'i-love-laravel'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    /**
     * UT-07: Login dengan Password Salah
     */
    public function test_login_invalid()
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    /**
     * UT-08: Logout Pengguna
     */
    public function test_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * UT-09: Akses Halaman Beranda
     */
    public function test_halaman_beranda()
    {
        $response = $this->get('/beranda');
        $response->assertStatus(200);
    }

    /**
     * UT-10: Akses Halaman Detail Layanan
     */
    public function test_halaman_detail_layanan()
    {
        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Test Layanan',
            'slug' => 'test-layanan',
            'judul_halaman' => 'Judul Test',
            'deskripsi_singkat' => 'Deskripsi',
            'konten_lengkap' => 'Konten',
            'gambar' => 'default.jpg'
        ]);
        Layanan::reguard();

        $response = $this->get('/layanan/test-layanan');
        $response->assertStatus(200);
    }

    /**
     * UT-11: Akses Halaman Detail Paket
     */
    public function test_halaman_detail_paket()
    {
        Paket::unguard();
        $paket = Paket::create([
            'nama' => 'Test Paket',
            'slug' => 'test-paket',
            'judul' => 'Judul Paket',
            'harga' => '1000',
            'deskripsi' => 'Deskripsi',
            'gambar' => 'default.jpg',
            'fitur' => ['Fitur 1', 'Fitur 2']
        ]);
        Paket::reguard();

        $response = $this->get('/paket/test-paket');
        $response->assertStatus(200);
    }

    /**
     * UT-12: Admin Menambah Paket Baru
     */
    public function test_admin_tambah_paket()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post('/paket', [
            'nama' => 'Paket Baru',
            'slug' => 'paket-baru',
            'harga' => '200000',
            'gambar' => 'new.jpg',
            'judul' => 'Judul Baru',
            'deskripsi' => 'Deskripsi Baru',
            'fitur' => "Fitur 1\nFitur 2"
        ]);

        $this->assertDatabaseHas('pakets', ['nama' => 'Paket Baru']);
    }

    /**
     * UT-13: Admin Mengupdate Paket
     */
    public function test_admin_update_paket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Paket::unguard();
        $paket = Paket::create([
            'nama' => 'Paket Lama',
            'slug' => 'paket-lama',
            'harga' => '1000',
            'judul' => 'Judul',
            'deskripsi' => 'Deskripsi',
            'gambar' => 'old.jpg',
            'fitur' => ['Fitur 1']
        ]);
        Paket::reguard();

        $response = $this->actingAs($admin)->put('/paket/' . $paket->id, [
            'nama' => 'Paket Terupdate',
            'slug' => 'paket-terupdate',
            'harga' => '300000',
            'gambar' => 'updated.jpg',
            'judul' => 'Judul Update',
            'deskripsi' => 'Deskripsi Update',
            'fitur' => 'Fitur Update'
        ]);

        $this->assertDatabaseHas('pakets', ['nama' => 'Paket Terupdate']);
    }

    /**
     * UT-14: Admin Menghapus Paket
     */
    public function test_admin_hapus_paket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Paket::unguard();
        $paket = Paket::create([
            'nama' => 'Paket Hapus',
            'slug' => 'paket-hapus',
            'harga' => '1000',
            'judul' => 'Judul',
            'deskripsi' => 'Deskripsi',
            'gambar' => 'delete.jpg',
            'fitur' => ['Fitur']
        ]);
        Paket::reguard();

        $response = $this->actingAs($admin)->delete('/paket/' . $paket->id);

        $this->assertDatabaseMissing('pakets', ['id' => $paket->id]);
    }

    /**
     * UT-15: Admin Melihat List Pelanggan
     */
    public function test_admin_list_pelanggan()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get('/pelanggan');
        $response->assertStatus(200);
    }

    /**
     * UT-16: Admin Update Detail Layanan
     */
    public function test_admin_update_layanan()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Layanan::unguard();
        $layanan = Layanan::create([
            'nama' => 'Layanan Lama',
            'slug' => 'layanan-lama',
            'judul_halaman' => 'Judul Lama',
            'deskripsi_singkat' => 'Lama',
            'konten_lengkap' => 'Lama',
            'gambar' => 'old.jpg'
        ]);
        Layanan::reguard();

        $response = $this->actingAs($admin)->put('/admin/layanan/' . $layanan->id, [
            'judul_halaman' => 'Judul Baru Banget',
            'deskripsi_singkat' => 'Deskripsi Baru Banget',
            'konten_lengkap' => 'Konten Baru Banget',
        ]);

        $this->assertDatabaseHas('layanans', ['judul_halaman' => 'Judul Baru Banget']);
    }

    /**
     * UT-17: User Akses Akun Saya
     */
    public function test_user_akses_akun_saya()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/akun-saya');
        $response->assertStatus(200);
    }
}