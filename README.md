# Sistem Peminjaman Ruang & Alat Kampus

Aplikasi web untuk mengajukan, menyetujui, dan memantau peminjaman ruang dan alat di kampus. Peminjam memilih waktu, ruang, dan alat lewat wizard. Ketersediaan dicek langsung, jadi jadwal bentrok dan stok yang habis ditolak sebelum dikirim. Petugas menyetujui pengajuan, mencatat pengembalian alat beserta denda keterlambatan, dan membuat laporan PDF atau Excel.

Semua aturan bisnis disimpan di database dan bisa diubah admin dari halaman Pengaturan tanpa mengubah kode. Aturan itu meliputi jam operasional, hari libur, durasi, batas pengajuan, tarif denda, teks notifikasi, serta peran dan hak akses.

> Tugas project mandiri mata kuliah **Pemrograman Web Lanjut**.

## Akun demo

Setelah `php artisan migrate --seed`, akun berikut bisa dipakai (semua memakai password `password`):

| Peran | Email | Untuk mencoba |
| --- | --- | --- |
| Admin | `admin@kampus.test` | Semua fitur, termasuk pengaturan dan laporan |
| Petugas | `petugas@kampus.test` | Persetujuan dan pengembalian saja |
| Peminjam | `mhs@kampus.test` | Peminjam biasa |
| Peminjam | `mhs2@kampus.test` | Peminjam kedua untuk uji jadwal bentrok |
| Peminjam | `denda@kampus.test` | Punya denda belum lunas, sehingga pengajuan baru diblokir (setelah `php artisan demo:isi` dijalankan) |

## Fitur

**Peminjam**
- Registrasi dan login.
- Pengajuan lewat wizard tiga langkah: waktu, ruang dan alat, lalu konfirmasi. Ketersediaan ruang dan sisa stok alat dicek saat waktu dipilih.
- Riwayat peminjaman sendiri, pembatalan, dan unduh bukti peminjaman (PDF).
- Kalender ketersediaan ruang.
- Notifikasi di lonceng navbar: disetujui, ditolak, pengingat sebelum mulai, dan peringatan terlambat.

**Petugas dan admin**
- Persetujuan atau penolakan pengajuan, dengan alasan penolakan.
- Pengembalian alat dengan kondisi, perhitungan denda otomatis, dan pelunasan denda.
- Kelola data ruang dan alat (foto, kapasitas, stok, tarif denda per alat).
- Laporan dengan filter periode, status, ruang, alat, dan peran, disertai grafik. Ekspor ke PDF dan Excel dengan kolom yang bisa dipilih.
- Pengaturan aturan bisnis, jam operasional, hari libur, label status, periode akademik, serta nama dan logo aplikasi.
- Template notifikasi: judul, isi dengan penanda seperti `{nama_peminjam}`, kanal (aplikasi atau email), dan penerima.
- Peran dan hak akses: buat peran baru dan centang hak aksesnya, lalu atur peran tiap pengguna.

**Lainnya**
- API dengan token Sanctum (`/api/login`, `/api/ruang`, `/api/alat`, `/api/peminjaman`).
- Perintah terjadwal `peminjaman:proses-otomatis`: menolak pengajuan kedaluwarsa, menyelesaikan peminjaman, dan mengirim pengingat.
- Interaksi tanpa reload halaman (fetch + DataTables), validasi langsung di bawah input, toast, dan konfirmasi SweetAlert2.

## Teknologi

| Bagian | Yang dipakai |
| --- | --- |
| Backend | PHP 8.3, Laravel 13, Laravel Fortify (login dan registrasi), Laravel Sanctum (API) |
| Database | PostgreSQL 16 dengan ekstensi `btree_gist` |
| Tampilan | Template Vuexy 9.0.1 versi HTML (Bootstrap 5), DataTables, Flatpickr, Select2, ApexCharts, FullCalendar, SweetAlert2 |
| Laporan | barryvdh/laravel-dompdf (PDF), maatwebsite/excel (Excel) |
| Testing | PHPUnit (feature test) |

## Cara kerja yang penting

**Anti-bentrok di level database.** Selain dicek di aplikasi, tabel `peminjaman` punya *exclusion constraint* PostgreSQL. Dua peminjaman aktif (menunggu atau disetujui) untuk ruang yang sama tidak bisa memiliki rentang waktu yang beririsan, walaupun dikirim pada detik yang sama.

```sql
EXCLUDE USING gist (ruang_id WITH =, tsrange(waktu_mulai, waktu_selesai, '[)') WITH &&)
WHERE (ruang_id IS NOT NULL AND status IN ('menunggu', 'disetujui'))
```

Rentang `[)` berarti jam selesai tidak ikut terhitung. Peminjaman 09.00–11.00 lalu 11.00–13.00 tetap boleh.

**Stok alat aman saat diajukan bersamaan.** Pengajuan disimpan dalam transaksi, dan baris alat dikunci dengan `lockForUpdate()` sebelum sisa stok dihitung.

**Tanpa hardcode aturan.** Semua angka aturan dibaca lewat `Pengaturan::ambil('kunci')` dari tabel `pengaturan`. Data awal ditulis di `database/data/*.json`, bukan di kode PHP. Yang tetap ada di kode hanya *kode sistem*, misalnya kode status (`menunggu`) dan kode hak akses (`kelola-ruang`).

**Struktur tabel utama:** `pengguna`, `peran`, `hak_akses`, `ruang`, `alat`, `peminjaman`, `detail_peminjaman`, `pengaturan`, `jam_operasional`, `hari_libur`, `status_peminjaman`, `template_notifikasi`, `periode_akademik`.

## Menjalankan di komputer sendiri

### 1. Kebutuhan

- PHP 8.3 atau lebih baru, dengan ekstensi `pdo_pgsql`, `pgsql`, `gd`, dan `zip`
- Composer 2
- PostgreSQL 16
- Template **Vuexy 9.0.1 versi HTML**. Template ini berlisensi, jadi tidak disertakan di repo ini.

### 2. Ambil kode dan pasang paket

```bash
git clone https://github.com/anjua/peminjaman-kampus.git
cd peminjaman-kampus
composer install
cp .env.example .env
php artisan key:generate
```

### 3. Pasang aset Vuexy

Salin folder `assets` dari paket Vuexy ke `public/assets`.

```bash
# Windows
xcopy C:\lokasi\vuexy\assets public\assets /E /I

# Linux / macOS
cp -r /lokasi/vuexy/assets public/assets
```

Setelah disalin, harus ada file seperti `public/assets/vendor/css/rtl/core.css` dan `public/assets/js/main.js`. Folder ini sudah tercantum di `.gitignore`.

### 4. Siapkan database

Jalankan di `psql` sebagai user `postgres`:

```sql
CREATE USER peminjaman WITH PASSWORD 'ganti-password';
CREATE DATABASE peminjaman_kampus OWNER peminjaman;
CREATE DATABASE peminjaman_kampus_test OWNER peminjaman;
\c peminjaman_kampus
CREATE EXTENSION IF NOT EXISTS btree_gist;
\c peminjaman_kampus_test
CREATE EXTENSION IF NOT EXISTS btree_gist;
```

Isi bagian database di `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=peminjaman_kampus
DB_USERNAME=peminjaman
DB_PASSWORD=ganti-password
```

### 5. Buat tabel, isi data, dan jalankan

```bash
php artisan migrate --seed
php artisan storage:link
php artisan demo:isi --acak=2026
php artisan serve
```

Perintah `demo:isi` membuat peminjaman contoh untuk dashboard, kalender, dan laporan. Perintah ini juga memberi akun `denda@kampus.test` satu denda yang belum lunas. Tanpa perintah ini, akun tersebut tetap bisa login, tetapi tidak diblokir saat mengajukan peminjaman.

Buka http://127.0.0.1:8000 lalu login dengan salah satu akun demo di atas.

Agar notifikasi pengingat dan penolakan otomatis berjalan, buka terminal kedua lalu jalankan:

```bash
php artisan schedule:work
```

## Testing

```bash
php artisan test
```

Test memakai database `peminjaman_kampus_test` (lihat `phpunit.xml`) dan menguji hal-hal berikut:
- Jadwal bentrok ditolak, tetapi jadwal berurutan diterima.
- Slot bisa dipakai lagi setelah pengajuan ditolak atau dibatalkan.
- Stok alat tidak bisa terlampaui.
- Hari tutup ditolak.
- Batas pengajuan aktif dibaca dari pengaturan.
- Peminjam tidak bisa membuka halaman admin dan hanya melihat datanya sendiri.
- Notifikasi terkirim, dan template yang dimatikan tidak mengirim notifikasi.
- Tarif denda khusus per alat dipakai bila ada.

## Struktur folder penting

```
app/
  Console/Commands/      perintah demo:isi, demo:akun, peminjaman:proses-otomatis
  Http/Controllers/      controller web, Admin/, dan Api/
  Http/Requests/         validasi + aturan bisnis (PeminjamanRequest)
  Models/                model dengan nama tabel bahasa Indonesia
  Services/              PeminjamanService, DendaService, Notifikasi
database/
  data/                  data awal dalam JSON (peran, pengaturan, ruang, alat, ...)
  migrations/
public/js/               JavaScript per halaman (fetch, DataTables, wizard)
resources/views/         Blade: layouts/, admin/, peminjaman/, pdf/
tests/Feature/           feature test
```

## Lisensi

Kode aplikasi ini dibuat untuk keperluan tugas kuliah. Template Vuexy adalah milik Pixinvent dan memerlukan lisensi sendiri, sehingga tidak disertakan di repo ini.
