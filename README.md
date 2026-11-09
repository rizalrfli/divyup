# DivyUp e-Reporting

Sistem informasi progres layanan digital marketing Agensi DivyUp, unit usaha PT PMI. Dibangun dengan Laravel 13, PostgreSQL (Supabase), Blade, dan CSS sendiri (tanpa npm atau build step).

## Menjalankan di mesin lokal

Butuh PHP 8.3+ dan Composer. Database dan penyimpanan berkas memakai Supabase, jadi tidak perlu server database lokal.

```powershell
cd C:\Users\rizal\Documents\PR_Kakmel\divyup
copy .env.example .env
php artisan key:generate

# Isi kredensial di .env:
#   DB_*   -> Supabase > Project Settings > Database > Session pooler
#   AWS_*  -> Supabase > Storage > S3 access keys (atau lihat catatan di bawah)

php artisan migrate:fresh --seed
php artisan serve
```

Buka `http://127.0.0.1:8000`.

### Kredensial Supabase

| Variabel | Diambil dari |
|---|---|
| `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD` | Project Settings → Database → **Session pooler** (bukan Direct connection, yang IPv6-only) |
| `AWS_ACCESS_KEY_ID` | Project ref |
| `AWS_SECRET_ACCESS_KEY` | Legacy `anon` key |
| `AWS_SESSION_TOKEN` | Legacy `service_role` key |
| `AWS_ENDPOINT` | `https://<ref>.storage.supabase.co/storage/v1/s3` |
| `AWS_URL` | `https://<ref>.supabase.co/storage/v1/object/public/assets` |

Bucket penyimpanan bernama `assets` dan harus publik.

## Deploy

Aplikasi berjalan di Vercel sebagai container (FrankenPHP) dengan database dan penyimpanan di Supabase.

```powershell
git push origin master
```

Vercel membangun `Dockerfile` di root. Semua variabel `.env` di atas wajib didaftarkan di Project Settings → Environment Variables, ditambah `APP_KEY`, `APP_ENV=production`, `APP_DEBUG=false`, dan `LOG_CHANNEL=stderr`.

Migrasi dijalankan dari mesin lokal (`php artisan migrate --force`), bukan saat container start, karena beberapa instance bisa start bersamaan.

## Akun contoh

Semua akun memakai password `password`.

| Peran | Email |
|---|---|
| Pimpinan (admin) | `melany@divyup.test` |
| Tim operasional | `dedy@divyup.test`, `luthfi@divyup.test`, `nimas@divyup.test`, `jamiatul@divyup.test` |
| Klien | `kafe@divyup.test`, `brandx@divyup.test` |

## Data contoh

Empat proyek supaya setiap keadaan punya wakil:

| Proyek | Klien | Tim | Keadaan |
|---|---|---|---|
| Social Media Management - Februari 2026 | Kafe Nusantara | Dedy | Berjalan, 3 aset menunggu persetujuan |
| Kampanye Ramadan 2026 | Kafe Nusantara | Luthfi | Selesai 100%, aset sudah disetujui |
| Social Media Management - Maret 2026 | Brand X | Nimas | Review, 1 disetujui dan 2 menunggu |
| Peluncuran Produk - April 2026 | Brand X | Dedy | Revisi terbuka dengan catatan klien |

Total 20 tahap dan 10 aset, tersebar Januari sampai April 2026, jadi keempat grafik dasbor punya data nyata untuk dibaca.

## Visualisasi data

Dasbor admin menampilkan empat grafik, semuanya dihitung dari database:

| Grafik | Isi |
|---|---|
| Aset masuk per bulan | Jumlah berkas yang diunggah tim per bulan |
| Distribusi status aset | Donat: menunggu, disetujui, diminta revisi |
| Progres tiap proyek | Batang mendatar per proyek aktif, diurutkan dari yang paling tertinggal |
| Sebaran proyek per tahap | Berapa proyek aktif yang sedang berada di tiap tahap |

## Alur singkat

1. Admin membuat proyek dan menugaskan anggota tim. Sistem otomatis membuat lima tahap: Briefing, Pengerjaan Desain, Menunggu Review, Revisi, Siap Publikasi.
2. Tim memperbarui status tahap dan mengunggah aset kreatif (PNG, JPG, PDF, MP4, maksimal 4 MB).
3. Klien membuka portal, melihat timeline dan galeri, lalu menyetujui aset atau meminta revisi dengan catatan wajib.
4. Setiap perubahan tahap tercatat di log aktivitas dan mengirim notifikasi di dalam aplikasi.

## Pengujian

```powershell
php artisan test
```

Test berjalan di SQLite in-memory, jadi tidak menyentuh database Supabase. Cakupan mengikuti 18 skenario blackbox pada Bab IV skripsi, ditambah kasus tepi: unggahan melebihi 4 MB, akses lintas klien, akun yang dinonaktifkan saat masih login, dan persetujuan ganda.

## Catatan teknis

- Tahap proyek diturunkan dari tabel `tasks`, bukan kolom terpisah, supaya tidak ada dua sumber kebenaran. Proyek berstatus `selesai` hanya ketika semua tahap tuntas dan tidak ada aset yang masih menunggu atau direvisi.
- Batas unggah 4 MB ditetapkan karena Vercel Functions menolak body request di atas 4,5 MB (`413 FUNCTION_PAYLOAD_TOO_LARGE`) sebelum permintaan sampai ke Laravel. Batas dijaga di dua tempat: validasi `max:4096` KB di aplikasi dan `upload_max_filesize=5M` / `post_max_size=6M` di `docker/php.ini`, supaya berkas 4,1 MB tetap sampai ke validasi dan ditolak dengan pesan Indonesia, bukan halaman error platform.
- File aset disimpan di bucket Supabase Storage `assets` lewat endpoint S3-nya. Disk `public` tetap bernama `public` agar kode dan test tidak berubah, tetapi drivernya `s3`. Filesystem container Vercel read-only, jadi berkas tidak mungkin disimpan di disk.
- View Blade dikompilasi saat build image (`php artisan view:cache`), bukan saat runtime, karena container tidak bisa menulis ke `storage/framework/views`.
- Akun dinonaktifkan lewat middleware `active`, jadi sesi yang masih terbuka langsung kehilangan akses ke halaman, unduhan, dan notifikasi.
