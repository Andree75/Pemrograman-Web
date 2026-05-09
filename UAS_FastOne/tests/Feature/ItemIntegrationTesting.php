<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Layanan;
use App\Models\PermintaanLayanan;

class ItemIntegrationTesting extends TestCase
{
    use RefreshDatabase;

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
}