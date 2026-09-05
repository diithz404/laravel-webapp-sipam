# AGENT_PRD.md — SIMPAM HIPPAM Desa Argosari (Dusun Pateguhan, Gentong, Bendrong)
> Dokumen ini ditulis untuk dikonsumsi oleh AI coding agent (mis. Claude Code) sebagai
> instruksi kerja + konteks data. Bukan dokumen naratif — setiap bagian bersifat
> deklaratif dan actionable. Baca seluruh file sebelum mulai coding.
>
> **Milestone di versi ini: DATA DESA SUDAH LENGKAP 100%.** Sebelumnya RT 15, 23, 25
> kosong (belum ada data pelanggan) — sekarang ketiganya sudah terisi, sehingga
> seluruh 34 RT di 3 dusun sudah punya data pelanggan. Tidak ada lagi RT berstatus
> `belum_ada_data` di dataset seed.

---

## 0. Referensi

- Dokumen produk lengkap (naratif, untuk manusia): `PRD.md` — sumber kebenaran untuk
  fitur/flow/tech stack.
- Tech stack: Laravel 12, Eloquent ORM, MySQL 8, Blade + Livewire 3 + Alpine.js + Tailwind CSS.
- Data seed real terlampir (MASTER, gabungan & terbaru, sudah final — pakai ini):
  - `pelanggan_seed_desa.csv`
  - `pelanggan_seed_desa.json`

---

## 1. Konteks Dataset Nyata

Ini bukan data contoh — ini data pelanggan sungguhan HIPPAM desa (3 dusun) yang HARUS
dipakai sebagai acuan desain skema dan seeder awal.

```yaml
total_pelanggan_desa: 708
total_rt: 34               # RT 01 - RT 34, berlanjut lintas dusun (bukan reset per dusun)
total_rt_terisi: 34         # SEMUA RT sudah punya data — tidak ada lagi yang kosong
rt_kosong: []

dusun:
  Pateguhan:
    rt_range: "01-12"
    total_pelanggan: 217
  Gentong:
    rt_range: "13-19"
    total_pelanggan: 144    # termasuk RT 15 yang baru terisi (20 pelanggan)
  Bendrong:
    rt_range: "20-34"
    total_pelanggan: 347    # termasuk RT 23 (18 pelanggan) & RT 25 (22 pelanggan) yang baru terisi

struktur_sumber: >
  3 file Excel per dusun (sudah diupdate 2x oleh user, versi terbaru dipakai), tiap
  file berisi banyak sheet (satu sheet per RT). Kolom: No (urut lokal per RT), Nama.
  TIDAK ADA kolom alamat detail, nomor pelanggan resmi, angka meter, atau tarif.
```

> **Catatan klarifikasi penting soal file update Gentong:** user meminta agar hanya
> "RT 13" dari file `HIMPAM_GENTONG__1_.xlsx` yang diproses. Setelah dibandingkan
> byte-per-byte dengan file Gentong versi sebelumnya, sheet **RT 13 ternyata identik
> (tidak berubah)** — yang benar-benar baru terisi datanya adalah **RT 15** (sebelumnya
> kosong, sekarang 20 pelanggan). Agent MENGASUMSIKAN ini salah ketik dari user dan
> memproses **RT 15**, bukan RT 13, karena itu yang secara faktual merupakan data baru.
> **Ini WAJIB dikonfirmasi ulang ke user** — jika ternyata user memang bermaksud RT 13
> untuk alasan lain (mis. koreksi data yang belum tercermin di file), beri tahu agent/user.

### 1.1 Anomali data yang WAJIB ditangani di level skema/import

Data baru (RT 23, 25, 15 — total 60 pelanggan) **tidak menambah anomali baru** — seluruh
60 nama bersih (tidak ada nama ganda "/" atau indikasi non-rumah-tangga). Tabel anomali
kumulatif desa (dari seluruh 708 baris) tetap sama seperti temuan sebelumnya:

| Sub-kategori `non_rumah_tangga` | Jumlah (desa) | Contoh |
|---|---|---|
| Fasilitas Ibadah | 8 | `Masjid`, `Musholla R.J`, `Masjid B.M`, dll (Pateguhan & Gentong) |
| Peternakan/Penampungan | 5 | `Arif(kandang)`, `Kandang/Arif`, `Penampung`, `Rokim/kandang` (Pateguhan & Bendrong) |
| Fasilitas Pendidikan | 1 | `Sekolah SD` (Bendrong RT34#20) |
| Koperasi/Usaha | 1 | `KUD` (Bendrong RT34#21) |
| **Total non_rumah_tangga** | **15** | tidak berubah dari versi sebelumnya |

| Jenis anomali | Jumlah (desa) | Keputusan desain wajib |
|---|---|---|
| Nama ganda dalam satu baris | 14 | tidak berubah — semua dari batch data lama, batch baru (RT23/25/15) bersih |
| Tidak ada nomor pelanggan resmi | seluruh 708 baris | `nomor_pelanggan` = `HPM-{RT 2 digit}-{no_urut_lokal 3 digit}` → contoh `HPM-23-001`, `HPM-15-020` |
| Tidak ada angka meter awal | seluruh 708 baris | `customers.angka_meter_awal` = `NULL`, `status_setup` = `belum_lengkap` |

---

## 2. Skema Database (tidak berubah dari versi sebelumnya)

```php
Schema::create('rt_wilayah', function (Blueprint $table) {
    $table->id();
    $table->unsignedTinyInteger('nomor_rt');   // 1-34, unik desa-wide
    $table->string('nama_wilayah');            // "RT 01".."RT 34"
    $table->string('dusun');                   // "Pateguhan" | "Gentong" | "Bendrong"
    $table->enum('status_data', ['lengkap', 'belum_ada_data'])->default('lengkap');
    $table->timestamps();
    $table->unique('nomor_rt');
});

Schema::create('tariff_classes', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kelas');
    $table->unsignedInteger('batas_standar_m3')->default(20);
    $table->unsignedInteger('tarif_standar');
    $table->unsignedInteger('tarif_progresif');
    $table->unsignedInteger('biaya_admin')->default(2000);
    $table->unsignedInteger('denda_per_hari')->default(500);
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('nomor_pelanggan')->unique();
    $table->string('nama');
    $table->string('catatan_nama')->nullable();
    $table->enum('jenis_pelanggan', ['rumah_tangga', 'non_rumah_tangga'])->default('rumah_tangga');
    $table->string('sub_kategori')->nullable();
    $table->foreignId('rt_wilayah_id')->constrained('rt_wilayah');
    $table->foreignId('tariff_class_id')->constrained('tariff_classes');
    $table->unsignedInteger('no_urut_lokal');
    $table->unsignedBigInteger('angka_meter_awal')->nullable();
    $table->enum('status_setup', ['belum_lengkap', 'lengkap'])->default('belum_lengkap');
    $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
    $table->timestamps();
});

Schema::table('users', function (Blueprint $table) {
    $table->enum('role', ['super_admin', 'admin', 'petugas'])->default('petugas');
    $table->foreignId('rt_wilayah_id')->nullable()->constrained('rt_wilayah');
    $table->boolean('is_active')->default(true);
});
```

`meter_readings`, `bills`, `payments`, `activity_logs` mengikuti `PRD.md` §5, tidak berubah.

**Aturan integritas & scoping akses tetap seperti versi sebelumnya** (lihat riwayat
dokumen ini): petugas wajib punya `rt_wilayah_id`, admin tidak; scoping ditegakkan
lewat Laravel Policy + `Gate::authorize` di controller, bukan hanya filter query.

---

## 3. Seed Data

File terlampir (MASTER, 708 baris, SUDAH termasuk RT 15/23/25 yang baru):
- `pelanggan_seed_desa.csv`
- `pelanggan_seed_desa.json`

### 3.1 Task: buat `CustomerSeeder` (desa-wide)

```
[ ] Baca pelanggan_seed_desa.json (708 baris — SEMUA RT, tidak ada lagi yang di-skip
    karena kosong)
[ ] Untuk tiap rt unik 1-34 → firstOrCreate ke rt_wilayah, status_data = 'lengkap'
    untuk SEMUA RT (tidak ada lagi RT dengan status 'belum_ada_data' di batch ini)
[ ] Pastikan tariff_classes berikut sudah ada (masih PLACEHOLDER nilai Rupiah, lihat §5):
    - "Standar" (is_default = true, tarif_standar 350, tarif_progresif 400)
    - "Non Rumah Tangga - Ibadah"
    - "Non Rumah Tangga - Peternakan/Penampungan"
    - "Non Rumah Tangga - Pendidikan"
    - "Non Rumah Tangga - Koperasi/Usaha"
[ ] Untuk tiap baris di JSON:
    - nomor_pelanggan = "HPM-{rt 2 digit}-{no_urut 3 digit}"
    - nama = nilai asli
    - jika catatan == "non_rumah_tangga" → set jenis_pelanggan, sub_kategori,
      tariff_class_id sesuai sub_kategori
    - jika catatan == "nama_ganda_atau_keterangan" → isi catatan_nama
    - angka_meter_awal = NULL, status_setup = belum_lengkap
[ ] Output log ringkasan: total per dusun (Pateguhan 217, Gentong 144, Bendrong 347),
    total per sub_kategori, total nama_ganda
```

### 3.2 Task: buat `PetugasSeeder` — SEKARANG 34 AKUN (naik dari 31)

Karena RT 15, 23, 25 sudah terisi datanya, ketiganya sekarang JUGA dapat akun petugas
— tidak ada lagi RT yang dikecualikan.

```
[ ] Untuk SEMUA 34 rt_wilayah (RT 1-34, tanpa pengecualian) → buat 1 user:
    - name = "Petugas RT {rt, 2 digit}"
    - email = "petugas{rt}@hippam.local"   → petugas1@hippam.local .. petugas34@hippam.local
      (angka RT tanpa leading zero di email: RT 01 → petugas1@..., RT 15 → petugas15@...)
    - role = 'petugas'
    - rt_wilayah_id = id RT terkait
    - password = "password" (di-hash via Hash::make())
    - is_active = true
[ ] Tidak ada RT yang di-skip pada tahap ini
[ ] Output log ringkasan: 34 email yang dibuat, dikelompokkan per dusun (12 Pateguhan,
    7 Gentong, 15 Bendrong)
```

> ⚠️ **Catatan keamanan** (tidak berubah dari versi sebelumnya): password seragam
> `"password"` untuk 34 akun HANYA untuk development/testing. WAJIB ada mekanisme
> paksa ganti password sebelum go-live.

### 3.3 Task: halaman Admin "Kelola Petugas" (tidak berubah dari versi sebelumnya)

- [ ] List `role = petugas`, filter per dusun & RT
- [ ] Form edit kontak asli, tombol reset password
- [ ] Badge jumlah pelanggan per RT
- [ ] Indikator password default belum diganti

---

## 4. Fitur Wajib Fase 1

- [ ] **Halaman "Pelanggan Belum Setup"** (Admin) — 708 customer `status_setup = belum_lengkap`
      di awal, filterable per dusun & RT.
- [ ] **Guard di form pencatatan meter petugas** — customer `belum_lengkap` wajib lewat
      alur "Setup Awal" dulu.
- [ ] **Halaman "Verifikasi Data Pelanggan"** — 14 customer dengan `catatan_nama` terisi.
- [ ] **Halaman "Kelola Tarif Non-Rumah-Tangga"** — 4 sub-kategori dengan kemungkinan
      tarif berbeda.
- [ ] **Dashboard Admin: filter & rekap 2 level** (per dusun → drill-down per RT). Karena
      sekarang SEMUA RT terisi, dashboard tidak perlu lagi menampilkan badge "Belum Ada
      Data" untuk wilayah — cukup tampilkan progres setup/pencatatan seperti RT lainnya.
- [ ] ~~RT 15/23/25 placeholder "Belum Ada Data"~~ — **DIHAPUS dari scope**, karena
      ketiganya sudah punya data pelanggan penuh di versi ini.

---

## 5. Pertanyaan yang Harus Dijawab Sebelum Agent Lanjut ke Tahap Coding

1. **Konfirmasi RT 13 vs RT 15 untuk Gentong** (lihat catatan di §1) — apakah benar
   yang dimaksud RT 15, atau ada maksud lain terkait RT 13 yang perlu ditindaklanjuti
   terpisah?
2. Berapa `tarif_standar`/`tarif_progresif` untuk **masing-masing 4 sub-kategori** non
   rumah tangga (Ibadah, Peternakan/Penampungan, Pendidikan, Koperasi/Usaha)?
3. Untuk 14 baris "nama ganda" — 1 sambungan (1 tagihan) atau 2 KK berbagi 1 meteran?
4. `angka_meter_awal` untuk 708 pelanggan — diisi manual satu-satu, atau ada sumber
   data lain (buku catatan lapangan)?
5. Kapan mekanisme "paksa ganti password default" diimplementasikan?
6. Apakah fasilitas ibadah (masjid/musholla/langgar) dikenakan tagihan normal, atau
   dibebaskan biaya (kebijakan umum di banyak HIPPAM)?

---

## 6. Definition of Done — Tahap Import Data Desa (Lengkap 34 RT)

- [ ] Migration `rt_wilayah`, `tariff_classes`, `customers`, `users` (§2) berhasil dijalankan
- [ ] Seeder `CustomerSeeder` menghasilkan tepat 708 baris `customers`
- [ ] 34 baris `rt_wilayah` ada (RT 01–34), **SEMUA berstatus `lengkap`** — tidak ada
      yang `belum_ada_data`
- [ ] 15 customer `jenis_pelanggan = non_rumah_tangga` (4 sub-kategori)
- [ ] 14 customer punya `catatan_nama` terisi
- [ ] Semua 708 customer punya `status_setup = belum_lengkap` dan `angka_meter_awal = NULL`
- [ ] Halaman Admin "Pelanggan Belum Setup" menampilkan 708 data, filter per dusun & RT
- [ ] Unit test: `nomor_pelanggan` unik, format `HPM-{RT 2 digit}-{no urut 3 digit}`
- [ ] Seeder `PetugasSeeder` menghasilkan **34 akun petugas** (naik dari 31), email
      `petugas{rt}@hippam.local`, password ter-hash `"password"`, `is_active = true`
- [ ] Login manual dengan akun RT yang baru terisi (`petugas15`, `petugas23`, `petugas25`
      @hippam.local) berhasil dan hanya menampilkan pelanggan RT masing-masing
- [ ] Uji akses lintas-RT tetap ditolak (403) untuk ketiga akun baru ini juga
- [ ] Uji login Admin: bisa melihat seluruh 3 dusun/34 RT tanpa scope terbatas