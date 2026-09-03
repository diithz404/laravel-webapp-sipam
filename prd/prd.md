# AGENT_PRD.md — SIMPAM HIPPAM Bendrong
> Dokumen ini ditulis untuk dikonsumsi oleh AI coding agent (mis. Claude Code) sebagai
> instruksi kerja + konteks data. Bukan dokumen naratif — setiap bagian bersifat
> deklaratif dan actionable. Baca seluruh file sebelum mulai coding.

---

## 0. Referensi

- Dokumen produk lengkap (naratif, untuk manusia): `PRD.md` (sudah dibuat sebelumnya, jadi
  sumber kebenaran untuk fitur/flow/tech stack — dokumen ini hanya menambahkan **konteks data
  produksi nyata** dan **task breakdown** untuk implementasi awal).
- Tech stack: Laravel 12, Eloquent ORM, MySQL 8, Blade + Livewire 3 + Alpine.js + Tailwind CSS.
- Data seed real terlampir: `bendrong_pelanggan.csv`, `bendrong_pelanggan.json`.

---

## 1. Konteks Dataset Nyata (sumber: `HIMPAM_BENDRONG.xlsx`)

Ini bukan data contoh — ini data pelanggan sungguhan HIPPAM Dusun Bendrong yang HARUS
dipakai sebagai acuan desain skema dan seeder awal.

```yaml
dusun: Bendrong
total_pelanggan: 307
total_rt_terisi: 13
rt_kosong: [23, 25]          # RT terdaftar tapi belum ada data pelanggan — JANGAN dihapus
                              # dari master wilayah, tandai status "belum_ada_data"
distribusi_per_rt:
  RT_20: 29
  RT_21: 25
  RT_22: 34
  RT_23: 0     # kosong
  RT_24: 22
  RT_25: 0     # kosong
  RT_26: 22
  RT_27: 14
  RT_28: 21
  RT_29: 20
  RT_30: 29
  RT_31: 28
  RT_32: 23
  RT_33: 15
  RT_34: 25
struktur_sumber: >
  15 sheet Excel terpisah, satu sheet per RT (nama sheet = "RT 20".."RT 34").
  Kolom: No (urut lokal per RT), Nama. TIDAK ADA kolom alamat detail, nomor
  pelanggan resmi, angka meter, atau tarif di file ini.
```

### 1.1 Anomali data yang WAJIB ditangani di level skema/import, bukan diabaikan

| Jenis anomali | Contoh | Keputusan desain wajib |
|---|---|---|
| Kemungkinan pelanggan non-rumah tangga | `Rokim/kandang` (RT20#19), `Penampung` (RT21#16), `Sekolah SD` (RT34#20), `KUD` (RT34#21) | Field `customers.jenis_pelanggan` enum (`rumah_tangga`, `non_rumah_tangga`) — default `rumah_tangga`, 4 baris ini di-flag `non_rumah_tangga` saat seeding. Jenis ini yang nanti menentukan `tariff_class_id` (lihat §2). |
| Nama ganda dalam satu baris | `Saipul/sulfa`, `Darmaji/Jumad`, `Ngateri/Ketang`, `Seneri/Oyek`, `Ripin/Sakinah`, `Samsul/Nanik` | JANGAN dipecah otomatis jadi 2 pelanggan (satu sambungan air = satu tagihan). Simpan `nama` apa adanya sebagai string penuh, tambahkan field `customers.catatan_nama` (nullable) berisi keterangan "kemungkinan nama alternatif/pasangan — perlu verifikasi admin". |
| RT tanpa data pelanggan (23, 25) | sheet kosong | Tabel `rt_wilayah` tetap punya baris RT 23 & RT 25 (status `belum_ada_data`), supaya kalau nanti datanya ditemukan, tinggal input tanpa migrasi ulang struktur. |
| Tidak ada nomor pelanggan resmi | seluruh baris | Sistem HARUS generate `nomor_pelanggan` otomatis saat seeding, format: `BDR-{RT}-{no_urut_lokal_3digit}` → contoh `BDR-20-001`. Ini jadi primary business key, bukan `id` auto-increment. |
| Tidak ada angka meter awal | seluruh baris | `meter_readings` baseline TIDAK dibuat saat import pelanggan ini. Field `customers.angka_meter_awal` diisi `NULL` dan wajib diisi manual oleh Admin/Petugas sebelum pelanggan tsb bisa mulai dicatat pemakaian bulanannya. Tandai `customers.status_setup` = `belum_lengkap` sampai `angka_meter_awal` diisi. |

---

## 2. Skema Database (tambahan/penyesuaian dari `PRD.md` §5)

```php
Schema::create('rt_wilayah', function (Blueprint $table) {
    $table->id();
    $table->string('nama_wilayah');        // "RT 20"
    $table->string('dusun')->default('Bendrong');
    $table->enum('status_data', ['lengkap', 'belum_ada_data'])->default('lengkap');
    $table->timestamps();
});

Schema::create('tariff_classes', function (Blueprint $table) {
    $table->id();
    $table->string('nama_kelas');          // "Standar", "Non Rumah Tangga"
    $table->unsignedInteger('batas_standar_m3')->default(20);
    $table->unsignedInteger('tarif_standar');      // Rp/m3
    $table->unsignedInteger('tarif_progresif');    // Rp/m3
    $table->unsignedInteger('biaya_admin')->default(2000);
    $table->unsignedInteger('denda_per_hari')->default(500);
    $table->boolean('is_default')->default(false);
    $table->timestamps();
});

Schema::create('customers', function (Blueprint $table) {
    $table->id();
    $table->string('nomor_pelanggan')->unique();   // BDR-20-001
    $table->string('nama');
    $table->string('catatan_nama')->nullable();     // flag nama ganda
    $table->enum('jenis_pelanggan', ['rumah_tangga', 'non_rumah_tangga'])->default('rumah_tangga');
    $table->foreignId('rt_wilayah_id')->constrained('rt_wilayah');
    $table->foreignId('tariff_class_id')->constrained('tariff_classes');
    $table->unsignedInteger('no_urut_lokal');       // No asli dari Excel per-RT, buat audit trail sumber data
    $table->unsignedBigInteger('angka_meter_awal')->nullable();
    $table->enum('status_setup', ['belum_lengkap', 'lengkap'])->default('belum_lengkap');
    $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
    $table->timestamps();
});
```

> Tabel `meter_readings`, `bills`, `payments`, `activity_logs` mengikuti skema di `PRD.md` §5 —
> tidak berubah, hanya `customers` yang disesuaikan dengan realita dataset ini.

---

## 3. Seed Data

File terlampir (hasil ekstraksi bersih dari `HIMPAM_BENDRONG.xlsx`):
- `bendrong_pelanggan.csv`
- `bendrong_pelanggan.json`

Struktur tiap baris:
```json
{ "rt": 20, "no_urut": 19, "nama": "Rokim/kandang", "catatan": "non_rumah_tangga" }
```

`catatan` bernilai salah satu dari: `null`, `"non_rumah_tangga"`, `"nama_ganda_atau_keterangan"`.

### 3.1 Task: buat `BendrongCustomerSeeder`

```
[ ] Baca bendrong_pelanggan.json
[ ] Untuk tiap rt unik (20-34, termasuk 23 & 25) → firstOrCreate ke rt_wilayah
    - jika rt 23 atau 25 → status_data = 'belum_ada_data', skip pembuatan customer
[ ] Pastikan 2 baris tariff_classes sudah ada sebelum seeding customer:
    - "Standar" (is_default = true, tarif_standar 350, tarif_progresif 400)
    - "Non Rumah Tangga" (tarif lebih tinggi — PLACEHOLDER, minta konfirmasi admin
      nilai aktualnya, jangan hardcode angka final tanpa konfirmasi)
[ ] Untuk tiap baris di JSON (rt 20-34, exclude rt kosong):
    - nomor_pelanggan = "BDR-{rt}-{no_urut padded 3 digit}"
    - nama = nilai asli (termasuk yang ada "/")
    - jika catatan == "non_rumah_tangga" → jenis_pelanggan = non_rumah_tangga,
      tariff_class_id = kelas "Non Rumah Tangga"
    - jika catatan == "nama_ganda_atau_keterangan" → isi catatan_nama dengan
      "Kemungkinan nama alternatif/pasangan — perlu verifikasi admin"
    - angka_meter_awal = NULL, status_setup = belum_lengkap
[ ] Output log ringkasan setelah seed selesai: total baris diproses, berapa masuk
    kategori non_rumah_tangga, berapa nama_ganda, berapa RT diproses
```

---

## 4. Fitur Wajib Fase 1 yang Berubah Akibat Data Ini

Data ini mengungkap gap fitur yang HARUS ada di MVP, di luar yang sudah tertulis di `PRD.md`:

- [ ] **Halaman "Pelanggan Belum Setup"** (Admin) — daftar customer dengan `status_setup = belum_lengkap`
  (akan berjumlah 307 di awal), supaya admin/petugas tahu siapa saja yang perlu diisi
  `angka_meter_awal` sebelum bisa mulai ditagih. Tanpa halaman ini, 307 pelanggan awal
  akan "hilang" dari radar karena tidak lolos filter normal.
- [ ] **Guard di form pencatatan meter petugas**: pelanggan dengan `status_setup = belum_lengkap`
  TIDAK BOLEH langsung diinput pemakaian bulanan — harus lewat alur "Setup Awal" dulu
  (isi angka meter awal + opsional lengkapi alamat/no. HP) baru lanjut ke pencatatan normal.
- [ ] **Halaman "Verifikasi Data Pelanggan"** (Admin) — list semua customer dengan
  `catatan_nama` terisi (6 baris), supaya admin bisa klarifikasi ke petugas RT terkait
  apakah itu 1 sambungan gabungan atau perlu dipecah jadi 2 pelanggan.
- [ ] **RT 23 & RT 25 placeholder**: tampil di halaman kelola wilayah dengan badge
  "Belum Ada Data" — bukan disembunyikan, supaya admin ingat perlu follow-up ke petugas RT tsb.

---

## 5. Pertanyaan yang Harus Dijawab Sebelum Agent Lanjut ke Tahap Coding

Agent TIDAK BOLEH mengasumsikan nilai berikut secara sepihak — tandai sebagai
`TODO: konfirmasi user` di kode jika terpaksa lanjut tanpa jawaban:

1. Berapa `tarif_standar`/`tarif_progresif` untuk kelas **Non Rumah Tangga**? (data lama
   di `8_Agustus.xlsx` mengindikasikan sekitar Rp 1.000–1.500, tapi belum pasti berlaku
   untuk 4 entri spesifik ini: kandang, penampung, sekolah, KUD — kemungkinan tarifnya
   beda lagi antara satu sama lain).
2. Untuk 6 baris "nama ganda" — apakah ini benar 1 sambungan (1 tagihan), atau
   sebenarnya 2 KK berbeda yang berbagi 1 meteran (perlu split billing)?
3. Apakah RT 23 & RT 25 memang belum punya jaringan HIPPAM sama sekali, atau datanya
   cuma belum diinput ke Excel oleh petugas?
4. `angka_meter_awal` untuk 307 pelanggan ini — apakah akan diisi manual satu-satu oleh
   Admin di sistem baru, atau ada sumber data lain (kertas/buku catatan lapangan) yang
   perlu di-digitalisasi dulu?

---

## 6. Definition of Done — Tahap Import Data Bendrong

- [ ] Migration `rt_wilayah`, `tariff_classes`, `customers` (versi disesuaikan §2) berhasil dijalankan
- [ ] Seeder `BendrongCustomerSeeder` berhasil dijalankan, menghasilkan tepat 307 baris `customers`
- [ ] 15 baris `rt_wilayah` ada (RT 20–34), 2 di antaranya berstatus `belum_ada_data`
- [ ] 4 customer berstatus `jenis_pelanggan = non_rumah_tangga`
- [ ] 6 customer punya `catatan_nama` terisi
- [ ] Semua 307 customer punya `status_setup = belum_lengkap` dan `angka_meter_awal = NULL`
- [ ] Halaman Admin "Pelanggan Belum Setup" menampilkan 307 data ini dengan benar, bisa difilter per RT
- [ ] Unit test: `nomor_pelanggan` ter-generate unik dan sesuai format `BDR-{RT}-{3digit}` untuk seluruh baris