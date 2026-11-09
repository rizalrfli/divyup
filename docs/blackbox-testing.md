# Bukti pengujian blackbox

Skenario mengikuti Tabel 4.1 skripsi. Kolom "Output aktual" diisi dari hasil menjalankan aplikasi dan suite test pada mesin pengembangan (PHP 8.3.30, Laravel 13.40.0, MySQL 8.4.3), bukan dari contoh di skripsi.

Perintah: `php artisan test`

## Hasil suite

```
68 test, 166 assertion, seluruhnya lulus.
```

## Skenario utama (Tabel 4.1)

| No | Fungsi yang diuji | Input / kondisi | Output yang diharapkan | Output aktual | Status |
|---|---|---|---|---|---|
| 1 | Login data valid | Email dan password akun terdaftar | Diarahkan ke dasbor sesuai peran | Admin ke `/admin`, tim ke `/tim`, klien ke `/portal` | Berhasil |
| 2 | Login password salah | Email valid, password salah | Pesan kesalahan autentikasi | "Email atau password salah", sesi tidak dibuat | Berhasil |
| 3 | Form login kosong | Tombol Masuk tanpa isi | Validasi pada kedua kolom | "Email wajib diisi" dan "Password wajib diisi" | Berhasil |
| 4 | Buat proyek baru | Admin mengisi form proyek | Proyek tersimpan dan muncul di daftar | Proyek tersimpan, 5 tahap dibuat otomatis | Berhasil |
| 5 | Unggah aset kreatif | File PNG 2 MB | Tersimpan dan bisa diakses klien | Tersimpan di `storage/app/public/assets/{id}`, muncul di galeri portal | Berhasil |
| 6 | Unggah melebihi batas | File 5 MB | Ditolak dengan pesan batas ukuran | "Ukuran file melebihi batas maksimum 4 MB", tidak ada baris aset dibuat | Berhasil |
| 7 | Klien menyetujui aset | Klik Setujui | Status aset berubah, notifikasi ke tim | Status `disetujui`, satu baris `approval_logs`, notifikasi terkirim | Berhasil |
| 8 | Minta revisi tanpa catatan | Klik Minta Revisi, catatan kosong | Validasi catatan wajib | "Catatan revisi wajib diisi", status aset tidak berubah | Berhasil |
| 9 | Ubah status tahap | Tim mengubah tahap dari Belum ke Selesai | Status diperbarui dan tercatat di log | Status tersimpan, `completed_at` terisi, baris `activity_logs` dibuat | Berhasil |
| 10 | Logout | Klik Keluar | Sesi dihapus, kembali ke login | Diarahkan ke `/login`, `assertGuest` lulus | Berhasil |
| 11 | Format file tidak diizinkan | File `.exe` | Ditolak | "Format file harus PNG, JPG, PDF, atau MP4" | Berhasil |
| 12 | Unggah tanpa berkas | Form dikirim kosong | Ditolak | "Pilih berkas yang akan diunggah" | Berhasil |
| 13 | Tanggal target sebelum tanggal mulai | Due date lebih awal | Ditolak | "Tanggal target tidak boleh sebelum tanggal mulai" | Berhasil |
| 14 | Penanggung jawab bukan tim | Memilih akun klien sebagai tim | Ditolak | "Penanggung jawab harus anggota tim operasional" | Berhasil |
| 15 | Email akun ganda | Membuat akun dengan email terdaftar | Ditolak | "Email sudah terdaftar" | Berhasil |
| 16 | Admin menonaktifkan akun | Klik Nonaktifkan | Akun tidak bisa login | `is_active` menjadi 0, login berikutnya ditolak | Berhasil |
| 17 | Unduh rekap bulanan | Klik Unduh CSV | Berkas CSV terunduh | `text/csv`, header `proyek,klien,tim,tahap,progres,due_date`, hanya proyek pada bulan terpilih | Berhasil |
| 18 | Notifikasi tahap | Tim mengubah tahap | Klien menerima notifikasi | Notifikasi tersimpan, lonceng menampilkan jumlah belum dibaca | Berhasil |

## Kasus tepi di luar Tabel 4.1

| Kasus | Hasil |
|---|---|
| Klien A membuka proyek klien B | 403 |
| Klien A mengunduh aset klien B | 403 |
| Tim mengubah tahap proyek yang bukan tugasnya | 403 |
| Klien mencoba membuat proyek | 403 |
| Tim mencoba menyetujui asetnya sendiri | 403 |
| Akun dinonaktifkan saat masih login | Request berikutnya 403, bukan hanya gagal login |
| Menyetujui aset yang sama dua kali | Klik kedua ditolak, hanya satu `approval_logs` |
| Aset dengan berkas fisik hilang di server | Unduhan 404 dengan pesan "Berkas tidak ditemukan di server" |
| Proyek tanpa aset | Portal menampilkan "Belum ada aset" |
| Belum ada proyek sama sekali | Dasbor dan portal menampilkan keadaan kosong, grafik menyebut belum ada data |

## Catatan pengujian manual

Diperiksa langsung di browser pada lebar 1440, 1024, dan 390 piksel:

- Login, dasbor admin (termasuk grafik batang dan donat), daftar proyek, pengguna, rekap, log aktivitas.
- Dasbor tim dan halaman detail proyek (lima baris tahap, form unggah, daftar aset).
- Portal klien dan halaman detail proyek.

Tidak ditemukan gulir horizontal pada lebar 390 piksel; tabel lebar bergulir di dalam panelnya sendiri.
