# Arah desain

Dokumen ini mencatat alasan tiap keputusan visual, supaya bisa dipertanggungjawabkan saat sidang dan tidak berubah-ubah tanpa sebab.

Reading this as: dashboard operasional agensi untuk tiga peran, bahasa visual SaaS bersih dengan identitas teal DivyUp, dial ENERGY 2 / RHYTHM 2 / MOTION 1.

## Warna

| Nilai | Pemakaian | Alasan |
|---|---|---|
| `#0E7C7B` teal | navigasi aktif, tombol utama, batang grafik, penanda tahap selesai | Diambil dari warna kartu layanan DivyUp pada Gambar 2.2 skripsi, jadi warna brand nyata, bukan karangan |
| `#E4732B` oranye | tombol minta revisi, penanda lewat tenggat, irisan donat "menunggu" | Satu warna untuk hal yang menuntut tindakan; tidak dipakai sebagai dekorasi |
| `#16233A` navy | teks utama | Kontras 12,6:1 di atas putih, jauh di atas ambang WCAG AA |
| `#F6F6F3` gading | latar halaman | Tabel padat tidak menyilaukan seperti di atas putih murni |
| `#56637A` abu | teks sekunder | 5,9:1 di atas gading, masih lolos AA untuk teks normal |

Palet aktif: dua warna brand plus satu aksen. Netral tidak dihitung.

## Tipografi

- **Plus Jakarta Sans** untuk seluruh antarmuka. Huruf ini dibuat untuk identitas Jakarta dan tegas pada ukuran kecil, cocok untuk agensi yang berbasis di Malang dan tabel berisi nama proyek panjang.
- **Newsreader** hanya untuk judul halaman login dan angka besar pada kartu statistik. Alasan: angka ringkasan ingin dibaca sebagai laporan, bukan sebagai tombol.

## Tata letak

- **Rail kiri 236px dengan label grup** ("Kelola", "Pekerjaan", "Proyek"). Tiga peran punya menu berbeda, dan satu rail lebih mudah dipindai daripada navbar atas bertumpuk. Label grup memisahkan navigasi utama dari bantuan.
- **Strip angka di atas tabel, bukan empat kartu besar.** Admin datang untuk melihat proyek mana yang macet, bukan untuk mengagumi kartu.
- **Tabel padat dengan label status berwarna**, bukan ikon. Lima tahapan produksi lebih jelas sebagai kata daripada simbol.
- **Radius 12px untuk panel, 8px untuk kontrol, 20px untuk lencana.** Variasi radius dipakai sebagai penanda hierarki, bukan semua elemen dibuat kapsul.
- **Tanpa bayangan besar.** Satu-satunya bayangan ada di kartu login, sebagai penanda bahwa halaman itu satu-satunya hal di layar.

## Grafik

Batang dan donat dibuat dengan CSS (`grid` dan `conic-gradient`), tanpa library chart. Alasannya: datanya sederhana, library akan menambah 100 KB JavaScript untuk dua grafik, dan angka tetap terbaca pembaca layar lewat `aria-label`.

Angka grafik dihitung dari database: batang = jumlah aset per bulan, donat = distribusi status aset. Tidak ada angka tetap di template.

## Yang sengaja dihindari

- Gradien biru-ungu, orb, dan glow.
- Avatar atau logo karangan; wordmark "DIVYUP" berupa teks.
- Angka contoh di template. Bila data kosong, tampil keadaan kosong yang menyebut apa yang belum ada.
- Animasi selain transisi hover dan buka-tutup.
