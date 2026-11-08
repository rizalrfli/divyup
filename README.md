# DivyUp e-Reporting

Sistem informasi progres layanan digital marketing Agensi DivyUp, unit usaha PT PMI. Dibangun dengan Laravel 13, MySQL, Blade, dan CSS sendiri (tanpa npm atau build step).

## Menjalankan di mesin lokal

```powershell
# 1. Nyalakan MySQL Laragon
Start-Process "C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe" `
  -ArgumentList '--defaults-file=C:\laragon\bin\mysql\mysql-8.4.3-winx64\my.ini'

# 2. Siapkan database dan data contoh
cd C:\Users\rizal\Documents\PR_Kakmel\divyup
php artisan migrate:fresh --seed
php artisan storage:link

# 3. Jalankan
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

- Tahap proyek diturunkan dari tabel `tasks`, bukan kolom terpisah, supaya tidak ada dua sumber kebenaran.
- Batas unggah dijaga di sisi aplikasi (`max:51200` KB). `upload_max_filesize` PHP di mesin ini sudah 2G, jadi batas 50 MB yang berlaku adalah batas aplikasi.
- File aset disimpan di `storage/app/public/assets/{project_id}` dan diakses lewat symlink `public/storage`.
