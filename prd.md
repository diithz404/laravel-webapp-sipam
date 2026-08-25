# PRD — Sistem Pembayaran Air HIPPAM (SIMAIR)

**Versi:** 1.0
**Tanggal:** 24 Agustus 2026
**Tech Stack:** Laravel 12, Eloquent ORM, MySQL, Blade + Livewire/Alpine.js, Tailwind CSS
**Referensi data:** File rekap manual `8_Agustus.xlsx` (HIPPAM "Tirto Makmur", Desa Argosari, Kec. Jabung, Kab. Malang)

---

## 1. Latar Belakang & Tujuan

HIPPAM (Himpunan Penduduk Pemakai Air Minum) "Tirto Makmur" saat ini mencatat pemakaian air dan tagihan warga secara manual menggunakan Excel, dikelompokkan per "Hal" (halaman/wilayah RT). Proses ini rawan salah hitung, sulit dipantau oleh pengurus pusat, dan rekap per RT/warga memakan waktu.

**Tujuan produk:**
1. Digitalisasi pencatatan angka meter air bulanan oleh petugas RT (Pak RT / kader HIPPAM).
2. Otomatisasi perhitungan tagihan (tarif standar, progresif, biaya admin, tunggakan berjalan).
3. Memberi Admin HIPPAM pusat kendali penuh atas tarif dan visibilitas penuh atas seluruh RT & warga.
4. Menghasilkan laporan & kwitansi yang akurat, cepat, dan bisa diaudit.

---

## 2. Aktor / Role Pengguna

| Role | Deskripsi | Akses Utama |
|---|---|---|
| **Super Admin (Pengurus HIPPAM)** | Mengelola sistem secara keseluruhan | Kelola tarif, kelola RT & petugas, monitoring semua transaksi, laporan global, kelola user |
| **Petugas RT (Pak RT / Kader Pencatat)** | Mencatat angka meter tiap rumah di wilayahnya, mencatat pembayaran | Input meter, lihat tagihan wilayahnya, catat pembayaran, cetak kwitansi, rekap RT-nya sendiri |
| **Warga (opsional, fase 2)** | Pelanggan air | Login/cek tagihan & riwayat pemakaian miliknya sendiri (read-only) |

> Catatan: Petugas RT dibatasi datanya (scoped) hanya untuk pelanggan di RT/wilayah yang menjadi tanggung jawabnya. Super Admin melihat lintas RT.

---

## 3. Logika Perhitungan Tagihan (hasil analisis file Excel)

Ini adalah inti bisnis yang **wajib** direplikasi persis oleh sistem, diverifikasi dari data sample:

**Contoh (Saiful):** Angka lalu 595, angka ini 650, tarif standar Rp350/m³ (batas 20 m³), tarif progresif Rp400/m³, admin Rp2.000, tunggakan bln lalu Rp200 → Total tagihan **Rp23.200**. ✔ Cocok dengan rumus di bawah.

### 3.1 Variabel per pelanggan per periode (bulan)
> Nilai default seluruh parameter di bawah **mengikuti persis angka pada file Excel** (`batas_standar` 20 m³, `tarif_standar` Rp350, `tarif_progresif` Rp400, `biaya_admin` Rp2.000). Parameter ini berlaku **global untuk satu HIPPAM** (satu set tarif aktif untuk seluruh RT, bukan per RT/per pelanggan), dan dapat **dikustom oleh Admin** melalui halaman Pengaturan Tarif (lihat §4.1 & §4.6) kapan pun dibutuhkan, dengan histori tanggal berlaku agar tagihan lama tidak berubah.

- `angka_meter_lalu` — angka meter akhir bulan sebelumnya (auto-terisi dari `angka_meter_ini` bulan lalu)
- `angka_meter_ini` — diinput petugas RT bulan berjalan
- `pemakaian` = `angka_meter_ini` − `angka_meter_lalu`
- `batas_standar` = kuota m³ dengan tarif dasar (default **20 m³**, sesuai Excel; dikustom Admin secara global)
- `pemakaian_standar` = `MIN(pemakaian, batas_standar)`
- `pemakaian_progresif` = `MAX(pemakaian − batas_standar, 0)`
- `tarif_standar` (Rp/m³, default **350**, sesuai Excel; dikustom Admin secara global)
- `tarif_progresif` (Rp/m³, default **400**, sesuai Excel; dikustom Admin secara global)
- `biaya_admin` (Rp flat, default **2.000**, sesuai Excel; dikustom Admin secara global)
- `tunggakan_bulan_lalu` = sisa tagihan belum lunas periode sebelumnya (carry-over otomatis)

### 3.2 Rumus tagihan
```
biaya_pemakaian = (pemakaian_standar × tarif_standar) + (pemakaian_progresif × tarif_progresif)
total_tagihan   = biaya_pemakaian + biaya_admin + tunggakan_bulan_lalu
sisa_bayar      = total_tagihan − jumlah_dibayar
```
Jika `sisa_bayar > 0` pada saat periode ditutup → otomatis menjadi `tunggakan_bulan_lalu` untuk periode berikutnya (persis seperti pola "Kurang/Sisa (Rp.)" di sheet Hal8–Hal11).

### 3.3 Aturan tarif progresif berjenjang (opsional, disiapkan untuk masa depan)
Struktur data tarif dirancang mendukung **multi-tier** (bukan cuma 2 tingkat), sehingga Admin bisa menambah tingkatan tarif (mis. 0–10 m³, 11–20 m³, 21+ m³) tanpa ubah kode — lihat model `tarif_tier` di §6.

### 3.4 Fitur tambahan dari Excel yang perlu diakomodasi
- **Kwitansi/struk per pelanggan** (format ditemukan di kanan sheet Hal1: No. Rekening, Nama, Alamat, Angka bulan ini/lalu, pemakaian, rincian tarif, jumlah harus dibayar).
- **Rekap per wilayah/"Hal" (RT)**: total tagihan, admin, tunggakan per RT (sheet Hal7).
- **Riwayat angka meter multi-bulan** per pelanggan (sheet Hal8–Hal11: kolom per bulan).
- **Kupon undian** (nomor urut pelanggan dipakai sebagai nomor kupon) — fitur nice-to-have, bisa masuk fase 2.

---

## 4. Core Features

### 4.1 Modul Master Data (Admin)
- CRUD **RT/Wilayah** (nama RT, alamat, petugas penanggung jawab)
- CRUD **Pelanggan/Warga** (nomor rekening/ID unik, nama, alamat, RT, no. HP, status aktif/nonaktif, angka meter awal saat daftar)
- CRUD **Petugas RT** (akun user + RT yang diampu; satu petugas bisa pegang >1 RT)
- CRUD & versi **Tarif**: tarif standar, tarif progresif (multi-tier), batas kuota standar, biaya admin — dengan **histori berlaku efektif per tanggal** (supaya tagihan bulan lama tidak berubah saat tarif baru berlaku)

### 4.2 Modul Input Meter (Petugas RT)
- Daftar pelanggan per RT (urut nomor rumah/nomor rekening), dengan angka meter bulan lalu tampil otomatis (read-only, hasil input bulan sebelumnya)
- Input angka meter bulan ini (angka wajib ≥ angka bulan lalu; validasi anti-typo, misal lompatan > threshold akan diberi warning tapi tetap bisa disimpan dengan konfirmasi)
- Kalkulasi tagihan **real-time** langsung ditampilkan begitu angka diinput (pemakaian, standar, progresif, admin, tunggakan, total)
- Mode input cepat (list semua rumah dalam satu halaman scroll, seperti isi form berjajar) — dioptimalkan untuk **HP/tablet di lapangan**
- Simpan sebagian → bisa dilanjutkan nanti (draft periode belum ditutup)
- **Tutup periode** per RT (lock semua data bulan tsb setelah selesai input, generate `tunggakan` untuk bulan depan)

### 4.3 Modul Pembayaran
- Petugas RT / Admin mencatat pembayaran (tunai, tanggal bayar, jumlah dibayar — bisa cicil/sebagian)
- Status tagihan: `Belum Bayar` / `Sebagian` / `Lunas`
- Cetak/unduh **kwitansi** (PDF) per transaksi pembayaran, format meniru struk di Excel
- Riwayat pembayaran per pelanggan

### 4.4 Modul Monitoring & Dashboard (Admin)
- Dashboard ringkas: total tagihan bulan berjalan, total terbayar, total tunggakan, jumlah RT, jumlah pelanggan aktif
- Grafik tren pemakaian air & pendapatan per bulan
- Filter per RT / per rentang tanggal
- Daftar pelanggan dengan tunggakan terbesar (untuk penagihan prioritas)
- Log aktivitas petugas (siapa input apa, kapan) untuk audit

### 4.5 Modul Laporan & Rekap
- Rekap per RT (setara sheet "Hal7"): total tagihan, admin, tunggakan per RT per periode
- Rekap per pelanggan (riwayat meter & tagihan multi-bulan, setara Hal8–Hal11)
- Laporan keuangan bulanan HIPPAM (total pemasukan, total tunggakan berjalan)
- Export ke **Excel/CSV** dan **PDF**
- Cetak kwitansi massal per RT (untuk dibagikan petugas ke warga)

### 4.6 Modul Pengaturan Sistem (Admin)
- Kelola tarif & batas kuota (dengan tanggal efektif)
- Kelola biaya admin
- Kelola user & hak akses (role petugas RT ↔ RT yang diampu)
- Backup data manual (trigger export database)

### 4.7 (Fase 2 — opsional)
- Portal warga (cek tagihan sendiri via nomor rekening/HP, tanpa perlu akun kompleks — mis. OTP WhatsApp)
- Notifikasi WhatsApp/SMS tagihan & jatuh tempo
- Fitur kupon undian otomatis
- Pembayaran online (QRIS/Virtual Account)

---

## 5. User Flow

### 5.1 Flow Petugas RT — Input Meter Bulanan
```
Login → Pilih Periode Aktif (mis. Agustus 2026) → Pilih RT (jika pegang >1)
  → Tampil daftar pelanggan RT tsb (angka meter bulan lalu sudah terisi)
  → Untuk tiap rumah: input angka meter bulan ini
     → sistem otomatis hitung pemakaian, tarif, total tagihan (live)
  → Simpan (bisa per baris atau simpan semua)
  → Setelah semua rumah terisi → klik "Tutup Periode RT ini"
  → Sistem generate tagihan final + cetak/lihat rekap RT
```

### 5.2 Flow Petugas RT — Catat Pembayaran
```
Login → Cari pelanggan (nama/no. rekening) → Lihat tagihan berjalan
  → Input jumlah dibayar & tanggal → Simpan
  → Sistem update status (Lunas/Sebagian) & cetak kwitansi
```

### 5.3 Flow Admin — Setup Tarif Baru
```
Login → Pengaturan → Tarif → Tambah Tarif Baru
  → Isi tarif standar, tarif progresif (per tier), batas kuota, biaya admin, tanggal mulai berlaku
  → Simpan → Tarif lama otomatis nonaktif mulai tanggal efektif baru
  (Tagihan periode yang sudah ditutup tidak berubah — histori tetap terjaga)
```

### 5.4 Flow Admin — Monitoring & Laporan
```
Login → Dashboard (lihat ringkasan semua RT)
  → Pilih RT / Periode → Lihat detail rekap
  → Export Excel/PDF, atau Cetak kwitansi massal
```

---

## 6. Data Model (Ringkas — Eloquent)

| Tabel | Kolom Kunci |
|---|---|
| `users` | id, name, phone, email, password, role (`admin`/`petugas`) |
| `rts` | id, nama_rt, alamat_wilayah, petugas_id (nullable, bisa multi via pivot) |
| `rt_petugas` (pivot) | rt_id, user_id |
| `pelanggans` | id, no_rekening (unik), nama, alamat, rt_id, no_hp, angka_meter_awal, status (aktif/nonaktif) |
| `tarifs` | id, tarif_standar, batas_kuota_standar, biaya_admin, tanggal_berlaku, created_by |
| `tarif_tiers` | id, tarif_id, urutan, batas_bawah, batas_atas (nullable = tak terhingga), harga_per_m3 |
| `periode_tagihan` | id, bulan, tahun, status (`draft`/`ditutup`) |
| `catatan_meter` | id, pelanggan_id, periode_id, angka_lalu, angka_ini, pemakaian, tarif_id (snapshot), biaya_pemakaian, biaya_admin, tunggakan_lalu, total_tagihan, input_by, input_at |
| `pembayarans` | id, catatan_meter_id, jumlah_bayar, tanggal_bayar, metode, dicatat_oleh |
| `activity_logs` | id, user_id, aksi, deskripsi, created_at |

**Catatan penting desain:** `tarif_id`, `tarif_standar`, `tarif_progresif`, dsb **di-snapshot** ke tabel `catatan_meter` saat perhitungan, bukan cuma referensi FK — supaya kalau tarif berubah di kemudian hari, tagihan historis tidak ikut berubah.

---

## 7. Desain / Frontend

### 7.1 Prinsip Desain
- **Mobile-first untuk Petugas RT** — mereka input data sambil keliling ke rumah warga, jaringan bisa lambat, layar HP kecil.
- **Desktop-first untuk Admin** — kebutuhan tabel data besar, grafik, filter kompleks.
- UI sederhana, minim klik, angka besar & jelas terbaca, warna status (merah=belum bayar, kuning=sebagian, hijau=lunas).
- Mobile-first untuk petugas.
- Desktop-first untuk admin.
- Navigasi sederhana.
- Angka tagihan sangat menonjol.
- Minim typing.
- Status menggunakan badge yang konsisten.
- Semua form memiliki validation message yang jelas.
- Hindari tabel terlalu lebar pada HP.

### 7.2 Visual style

Rekomendasi:

- Primary: biru/toska yang diasosiasikan dengan air.
- Background: gray/neutral.
- Card: putih dengan border tipis.
- Radius: 10–14px.
- Button utama: solid.
- Status:	&#x20; - success = lunas.
		&#x20; - warning = perlu review/belum lunas.
		&#x20; - danger = tunggakan/denda/error.
		&#x20; - neutral = draft.

### Typography
- Font: Inter atau system sans.
- Heading: semibold.
- Nominal rupiah: font-weight tinggi agar mudah dibaca.

### 7.3 Halaman Utama

**Petugas RT:**
1. Login
2. Dashboard petugas (ringkasan RT-nya: progres input bulan ini, jumlah tunggakan)
3. Halaman Input Meter (list pelanggan + form cepat)
4. Halaman Detail Pelanggan (riwayat meter, riwayat bayar, tombol catat bayar)
5. Halaman Rekap RT (bulan berjalan)
6. Cetak Kwitansi (preview PDF)

**Admin:**
1. Login
2. Dashboard global (kartu statistik + grafik tren)
3. Kelola RT & Petugas
4. Kelola Pelanggan (semua RT, dengan filter)
5. Kelola Tarif (histori tarif + form tier progresif)
6. Laporan & Rekap (filter RT/periode, export)
7. Kelola User & Role
8. Log Aktivitas

### 7.4 Komponen UI kunci
- **Kalkulator tagihan live** saat input angka meter (JS/Livewire reactive, tanpa reload halaman)
- **Tabel rekap** dengan sort/filter/search per RT
- **Kartu statistik** dashboard (total tagihan, total lunas, total tunggakan)
- **Badge status** pembayaran berwarna
- **Form tarif tier** dinamis (tambah/hapus baris tingkatan tarif)

---

## 8. Non-Functional Requirements

- **Keamanan:** hashing password (bcrypt/Argon2 bawaan Laravel), role-based access control (Laravel Policy/Gate), scoping data petugas hanya ke RT-nya, CSRF protection bawaan Laravel.
- **Audit trail:** semua input meter & pembayaran tercatat siapa & kapan (kolom `input_by`, `dicatat_oleh`, tabel `activity_logs`).
- **Integritas data:** periode yang sudah "ditutup" tidak bisa diubah petugas biasa (hanya Admin dengan alasan/log khusus bisa reopen).
- **Performa:** desain untuk skala ratusan–ribuan pelanggan per HIPPAM; pakai pagination & index database yang tepat (index di `no_rekening`, `rt_id`, `periode_id`).
- **Backup:** backup database terjadwal (cron Laravel scheduler → dump ke storage/S3-compatible atau email ke Admin).
- **Ketersediaan offline terbatas (opsional lanjutan):** karena sinyal di lapangan bisa lemah, pertimbangkan PWA dengan cache form input agar bisa disimpan lokal lalu sinkron saat online (fase lanjutan, bukan MVP).

---

## 9. Tech Stack & Arsitektur

- **Backend:** Laravel 12 (PHP 8.3+), Eloquent ORM
- **Database:** MySQL 8
- **Frontend:** Blade templating + **Livewire 3** (untuk interaktivitas real-time kalkulasi tagihan tanpa perlu SPA terpisah) + Alpine.js untuk interaksi ringan + Tailwind CSS untuk styling cepat & konsisten
- **PDF:** `barryvdh/laravel-dompdf` atau `spatie/laravel-pdf` untuk cetak kwitansi & laporan
- **Export Excel:** `maatwebsite/excel`
- **Autentikasi:** Laravel Breeze/Fortify (role via kolom `role` + middleware/Gate, tanpa perlu package berat seperti Spatie kalau role sederhana; bisa upgrade ke `spatie/laravel-permission` jika role makin kompleks)
- **Queue (opsional fase 2):** untuk kirim notifikasi WA/SMS async

### Rekomendasi Hosting
Karena stack-nya Laravel + MySQL standar (bukan aplikasi berat/real-time), berikut pertimbangan:

- **Hostinger (shared/Business hosting):** bisa dipakai untuk MVP dengan jumlah pelanggan kecil–menengah (ratusan–beberapa ribu baris data). Pastikan paket mendukung **PHP 8.3+, MySQL, SSH access, dan Composer** (biasanya tersedia di paket Business/Cloud Hostinger, bukan paket shared paling murah). Cek juga apakah cron job custom (untuk scheduler Laravel) diizinkan.
- **Alternatif yang lebih ramah Laravel (jika butuh kontrol lebih):** VPS Hostinger, atau layanan seperti DigitalOcean/Vultr + panel seperti Laravel Forge/RunCloud — memberi kontrol penuh (queue worker, scheduler, storage) yang kadang terbatas di shared hosting.
- **Rekomendasi praktis untuk kasus HIPPAM (skala kecil, budget terbatas):** mulai dari **Hostinger Business Hosting** (murah, cukup untuk kebutuhan RT/desa) sambil pastikan versi PHP & akses SSH/cron tersedia; jika ke depan data & traffic bertambah (multi-HIPPAM/multi-desa), migrasi ke VPS akan lebih leluasa untuk queue & scheduler.

> Saya belum verifikasi harga/paket Hostinger terbaru — kalau mau, saya bisa cari info paket & harga terkini sebelum Anda memutuskan.

---

## 10. Roadmap (Saran Fase Pengembangan)

**MVP (Fase 1):**
- Master data (RT, Pelanggan, Tarif dengan histori)
- Input meter oleh petugas RT + kalkulasi otomatis
- Catat pembayaran + cetak kwitansi PDF
- Dashboard & rekap dasar per RT
- Export Excel/PDF

**Fase 2:**
- Portal warga (cek tagihan mandiri)
- Notifikasi WhatsApp tagihan/tunggakan
- Fitur kupon undian otomatis
- Grafik analitik lebih lengkap

**Fase 3:**
- Pembayaran online (QRIS)
- Multi-HIPPAM (jika ingin dijual sebagai produk SaaS ke desa lain)
- PWA offline-first untuk petugas RT

---
