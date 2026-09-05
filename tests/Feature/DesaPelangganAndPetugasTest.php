<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rt;
use App\Models\Pelanggan;
use App\Models\Tarif;
use App\Models\PeriodeTagihan;
use Illuminate\Support\Facades\Hash;

class DesaPelangganAndPetugasTest extends TestCase
{
    public function test_all_34_rts_exist_with_proper_dusun_and_status()
    {
        $this->assertEquals(34, Rt::count());

        // Check Dusun groupings
        $pateguhanCount = Rt::where('dusun', 'Pateguhan')->orWhere('wilayah', 'like', '%Pateguhan%')->count();
        $this->assertEquals(12, $pateguhanCount); // RT 01-12

        $gentongCount = Rt::where('dusun', 'Gentong')->orWhere('wilayah', 'like', '%Gentong%')->count();
        $this->assertEquals(7, $gentongCount); // RT 13-19

        $bendrongCount = Rt::where('dusun', 'Bendrong')->orWhere('wilayah', 'like', '%Bendrong%')->count();
        $this->assertEquals(15, $bendrongCount); // RT 20-34

        // All 34 RTs are now filled with status_data = lengkap
        $incompleteRts = Rt::where('status_data', '!=', 'lengkap')->count();
        $this->assertEquals(0, $incompleteRts, 'Semua 34 RT harus berstatus lengkap');

        // Check RT 15, 23, 25 are present and have status 'lengkap'
        $rt15 = Rt::where('kode_rt', 'RT 15')->orWhere('nomor_rt', 15)->first();
        $rt23 = Rt::where('kode_rt', 'RT 23')->orWhere('nomor_rt', 23)->first();
        $rt25 = Rt::where('kode_rt', 'RT 25')->orWhere('nomor_rt', 25)->first();

        $this->assertNotNull($rt15);
        $this->assertNotNull($rt23);
        $this->assertNotNull($rt25);

        $this->assertEquals('lengkap', $rt15->status_data);
        $this->assertEquals('lengkap', $rt23->status_data);
        $this->assertEquals('lengkap', $rt25->status_data);
    }

    public function test_petugas_accounts_created_for_all_34_rts()
    {
        // 34 Petugas + 1 Admin = 35 users minimum
        $petugasUsers = User::where('role', 'petugas')->get();
        $this->assertGreaterThanOrEqual(34, $petugasUsers->count());

        // Check sample petugas from each of the 3 Dusun + newly active RTs
        $petugas1 = User::where('email', 'petugas1@hippam.local')->first(); // Dusun Pateguhan
        $petugas13 = User::where('email', 'petugas13@hippam.local')->first(); // Dusun Gentong
        $petugas20 = User::where('email', 'petugas20@hippam.local')->first(); // Dusun Bendrong

        $petugas15 = User::where('email', 'petugas15@hippam.local')->first(); // RT 15 (Gentong)
        $petugas23 = User::where('email', 'petugas23@hippam.local')->first(); // RT 23 (Bendrong)
        $petugas25 = User::where('email', 'petugas25@hippam.local')->first(); // RT 25 (Bendrong)

        $this->assertNotNull($petugas1, 'Petugas RT 01 (Pateguhan) harus ada');
        $this->assertNotNull($petugas13, 'Petugas RT 13 (Gentong) harus ada');
        $this->assertNotNull($petugas20, 'Petugas RT 20 (Bendrong) harus ada');

        $this->assertNotNull($petugas15, 'Petugas RT 15 (Gentong) harus ada');
        $this->assertNotNull($petugas23, 'Petugas RT 23 (Bendrong) harus ada');
        $this->assertNotNull($petugas25, 'Petugas RT 25 (Bendrong) harus ada');

        // Password hash check
        $this->assertTrue(Hash::check('hippam', $petugas1->password));
        $this->assertTrue(Hash::check('hippam', $petugas15->password));
    }

    public function test_customer_data_anomalies_and_nomor_pelanggan_format()
    {
        $customers = Pelanggan::all();
        $this->assertGreaterThanOrEqual(640, $customers->count());

        // 1. Check nomor_pelanggan format: HPM-{RT 2 digit}-{no_urut 3 digit}
        foreach ($customers as $c) {
            $this->assertMatchesRegularExpression('/^HPM-\d{2}-\d{3}$/', $c->no_rekening);
        }

        // 2. Check exactly 15 non_rumah_tangga customers across 4 sub-categories
        $nonRt = Pelanggan::where('jenis_pelanggan', 'non_rumah_tangga')->get();
        $this->assertCount(15, $nonRt);

        $ibadah = Pelanggan::where('sub_kategori', 'Fasilitas Ibadah')->count();
        $ternak = Pelanggan::where('sub_kategori', 'Peternakan/Penampungan')->count();
        $pendidikan = Pelanggan::where('sub_kategori', 'Fasilitas Pendidikan')->count();
        $koperasi = Pelanggan::where('sub_kategori', 'Koperasi/Usaha')->count();

        $this->assertEquals(8, $ibadah);
        $this->assertEquals(5, $ternak);
        $this->assertEquals(1, $pendidikan);
        $this->assertEquals(1, $koperasi);

        // 3. Check 14 customers with catatan_nama (double names)
        $namaGanda = Pelanggan::whereNotNull('catatan_nama')->count();
        $this->assertEquals(14, $namaGanda);
    }

    public function test_petugas_authorization_scoping()
    {
        $petugas1 = User::where('email', 'petugas1@hippam.local')->first();
        $rt5 = Rt::where('kode_rt', 'RT 05')->orWhere('nomor_rt', 5)->first();

        // 1. Petugas RT 01 accessing RT 05 data directly -> 403 Forbidden
        $response = $this->actingAs($petugas1)->get(route('petugas.warga.index', ['rt_id' => $rt5->id]));
        $response->assertStatus(403);

        // 2. Petugas RT 01 accessing their own RT 01 data -> 200 OK
        $rt1 = Rt::where('kode_rt', 'RT 01')->orWhere('nomor_rt', 1)->first();
        $responseSuccess = $this->actingAs($petugas1)->get(route('petugas.warga.index', ['rt_id' => $rt1->id]));
        $responseSuccess->assertStatus(200);

        // 3. Admin can access all RTs without 403
        $admin = User::where('role', 'admin')->first();
        $adminResponse = $this->actingAs($admin)->get(route('admin.pelanggan.index', ['rt_id' => $rt5->id]));
        $adminResponse->assertStatus(200);
    }
}
