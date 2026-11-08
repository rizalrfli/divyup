# DivyUp e-Reporting

Sistem informasi progres layanan digital marketing Agensi DivyUp, unit usaha PT PMI. Dibangun dengan Laravel 13, MySQL, Blade, dan CSS sendiri (tanpa npm atau build step).

## Menjalankan di mesin lokal

Butuh PHP 8.3+, Composer, dan MySQL 8 (Laragon).

```powershell
# 1. Nyalakan MySQL Laragon
Start-Process "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" `
  -ArgumentList '--defaults-file=C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini'

# 2. Buat database kosong
& "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS divyup_ereporting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 3. Siapkan aplikasi
cd C:\Users\rizal\Documents\PR_Kakmel\divyup
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link

# 4. Jalankan
php artisan serve
```

Buka `http://127.0.0.1:8000`.

## Akun contoh

Semua akun memakai password `password`.

| Peran | Email |
|---|---|
| Pimpinan (admin) | `melany@divyup.test` |
| Tim operasional | `dedy@divyup.test`, `luthfi@divyup.test`, `nimas@divyup.test`, `jamiatul@divyup.test` |
| Klien | `kafe@divyup.test`, `brandx@divyup.test` |

## Alur singkat

1. Admin membuat proyek dan menugaskan anggota tim. Sistem otomatis membuat lima tahap: Briefing, Pengerjaan Desain, Menunggu Review, Revisi, Siap Publikasi.
2. Tim memperbarui status tahap dan mengunggah aset kreatif (PNG, JPG, PDF, MP4, maksimal 50 MB).
3. Klien membuka portal, melihat timeline dan galeri, lalu menyetujui aset atau meminta revisi dengan catatan wajib.
4. Setiap perubahan tahap tercatat di log aktivitas dan mengirim notifikasi di dalam aplikasi.

## Pengujian

```powershell
php artisan test
```

Test berjalan di SQLite in-memory, jadi tidak menyentuh database MySQL. Cakupan mengikuti 18 skenario blackbox pada Bab IV skripsi, ditambah kasus tepi: unggahan melebihi 50 MB, akses lintas klien, akun yang dinonaktifkan saat masih login, dan persetujuan ganda.

## Catatan teknis

- Tahap proyek diturunkan dari tabel `tasks`, bukan kolom terpisah, supaya tidak ada dua sumber kebenaran. Proyek berstatus `selesai` hanya ketika semua tahap tuntas dan tidak ada aset yang masih menunggu atau direvisi.
- Batas unggah dijaga di sisi aplikasi (`max:51200` KB). `upload_max_filesize` PHP di mesin ini sudah 2G, jadi batas 50 MB yang berlaku adalah batas aplikasi. Pada instalasi PHP standar (batas unggah 8 MB) berkas 60 MB akan ditolak server sebelum sampai ke aplikasi, jadi naikkan `upload_max_filesize` dan `post_max_size` ke minimal 60M bila memakai server lain.
- File aset disimpan di `storage/app/public/assets/{project_id}` dan diakses lewat symlink `public/storage`.
- Akun dinonaktifkan lewat middleware `active`, jadi sesi yang masih terbuka langsung kehilangan akses ke halaman, unduhan, dan notifikasi.
