# Panduan Penggunaan — SI Pengelolaan Data Agenda

## install mariadb-server on docker
docker run -d \
  --name mariadb-server \
  -p 3306:3306 \
  -e MARIADB_ROOT_PASSWORD=your_secure_password \
  -e MARIADB_DATABASE=my_database \
  -e MARIADB_USER=my_user \
  -e MARIADB_PASSWORD=my_user_password \
  -v mariadb_data:/var/lib/mysql \
  --restart always \
  mariadb:latest

## Menjalankan Aplikasi

1. Jalankan database (Docker):
   ```
   docker start mariadb-server
   ```
2. Jalankan server Laravel (dari folder `laravel-vue-mvc`):
   ```
   php artisan serve
   ```
3. Jalankan Vite dev server:
   ```
   npm run dev
   ```
4. Buka browser: **http://127.0.0.1:8000**

## Akun Login (per Role)

Semua akun menggunakan password: **Password@123**

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@example.com` | `Password@123` |
| Staff | `staff@example.com` | `Password@123` |
| Asisten Daerah | `asisten@example.com` | `Password@123` |
| OPD | `opd@example.com` | `Password@123` |

## Kebijakan Password

Password harus memenuhi semua aturan berikut:
- Minimal **8 karakter**
- Minimal **1 huruf kapital** (A-Z)
- Minimal **1 angka** (0-9)
- Minimal **1 karakter unik/simbol** (mis. `@`, `#`, `!`)

Saat mengelola pengguna, form menampilkan indikator kekuatan password secara real-time.

## Hak Akses per Role

| Menu | Admin | Staff | Asisten Daerah | OPD |
|------|:-----:|:-----:|:--------------:|:---:|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Kegiatan (menu) | ❌ | ✅ | ✅ | ✅ |
| Kegiatan — tambah/edit/hapus | ❌ | ✅ | ❌ | ❌ |
| Kegiatan — konfirmasi kehadiran | ❌ | ❌ | ❌ | ✅ |
| Surat | ❌ | ✅ | ❌ | ❌ |
| Disposisi | ❌ | ✅ | ✅ | ❌ |
| Pengingat | ❌ | ✅ | ✅ | ✅ |
| Pengguna (kelola akun) | ✅ | ❌ | ❌ | ❌ |

## Menu

### Dashboard
Ringkasan statistik yang sama untuk semua role:
- **Disposisi**: Total, Diterima, Ditolak, Diserahkan.
- **Kegiatan**: Total, Dilaksanakan (terlaksana), Tidak Dilaksanakan (tidak terlaksana).
- **Kegiatan per Periode**: tabel rekap jumlah kegiatan per bulan (Total, Dilaksanakan, Tidak Dilaksanakan).

### Kegiatan
Data kegiatan dengan kolom:
- **Nama Kegiatan**, **Tempat Kegiatan**, **Tanggal Kegiatan**, **Uraian Kegiatan**
- **Realisasi Pelaksanaan**: `Terlaksana` / `Tidak`
- **Keterangan**: catatan tambahan (opsional)
- **Status**: `Pelaksanaan` / `Laporan`
- **Nama Penyusun**: opsional (kolom `tanda_tangan_penyusun` telah dihapus dari sistem)

Hanya **Staff** yang dapat menambah, mengedit, dan menghapus. Role lain hanya dapat melihat.

**Cek jadwal bentrok**: saat menambah/mengedit kegiatan, sistem memeriksa tanggal dan jam kegiatan. Jika sudah ada kegiatan lain pada tanggal dan jam yang sama, muncul peringatan *"Jadwal bentrok: sudah ada kegiatan pada tanggal dan jam tersebut."* Pemeriksaan dilakukan saat mengetik (frontend) dan divalidasi ulang di server saat disimpan.

**Konfirmasi Kehadiran (OPD)**: hanya role **OPD** yang dapat mengonfirmasi kehadiran per kegiatan melalui tombol **Hadir** / **Tidak Hadir**. Konfirmasi tercatat per akun OPD dan dapat diubah. Role lain melihat rekap jumlah **hadir** dan **tidak hadir** serta dapat membuka **Daftar OPD** untuk melihat siapa saja yang mengonfirmasi hadir/tidak.

### Surat (Staff)
Kelola data surat. Saat surat baru dibuat, sistem otomatis:
1. Membuat data **Disposisi** (kolom Penerima/Dituju dikosongkan), dan
2. Menandai disposisi sebagai **Diterima** tanpa perlu diedit.

Setelah surat baru berhasil disimpan, aplikasi otomatis berpindah ke halaman **Disposisi** untuk surat tersebut.

### Disposisi (Staff)
Data disposisi yang otomatis dibuat dari Surat. Dapat diedit untuk mengisi **Penerima**, **Dituju**, dan **Keterangan**:
- **Diterima** / **Diserahkan**: tanpa syarat tambahan
- **Ditolak**: wajib mengisi **Alasan Ditolak**

Gunakan tombol **Disposisi** pada baris data Surat untuk melompat ke disposisi terkait.

Hak akses Disposisi:
- **Staff**: dapat mengedit seluruh data disposisi (termasuk keterangan, Penerima, Dituju) dan menghapus.
- **Asisten Daerah**: dapat melihat disposisi serta **menyerahkan** (diserahkan) atau **menolak** (ditolak, wajib alasan) surat. Tidak dapat mengubah data surat lain atau menghapus.

### Pengingat (Staff, Asisten Daerah, OPD)
Kelola pengingat pribadi. Setiap pengguna hanya melihat pengingat miliknya sendiri. Kolom:
- **Judul** (wajib) dan **Deskripsi** (opsional)
- **Tanggal Pengingat** (wajib)
- **Prioritas**: `Rendah` / `Sedang` / `Tinggi`
- **Status**: `Pending` / `Selesai`

### Pengguna (Admin)
Kelola akun pengguna: tambah, edit (nama, email, role, password), dan hapus. Akun sendiri tidak dapat dihapus.

## Fitur Umum

- **Validasi form saat mengetik**: semua form (Surat, Kegiatan, Pengguna, Pengingat, Disposisi, Tolak Disposisi) memvalidasi kolom wajib secara langsung saat pengguna mengetik. Field yang tidak valid ditandai dengan border merah dan pesan error muncul/berubah secara real-time; form tidak dapat disimpan sebelum semua kolom wajib valid.
- **Notifikasi hasil aksi (berhasil/gagal)**: semua operasi CRUD menampilkan notifikasi *toast* di pojok kanan atas:
  - **Berhasil** (hijau): data berhasil ditambahkan / diperbarui / dihapus, termasuk konfirmasi kehadiran, penyerahan, dan penolakan disposisi.
  - **Gagal** (merah): menampilkan alasan kegagalan saat menyimpan/menghapus data.
- **Konfirmasi penghapusan**: setiap aksi hapus (Surat, Kegiatan, Pengguna, Disposisi, Pengingat) memunculkan dialog konfirmasi sebelum data benar-benar dihapus. Tombol **Hapus** hanya menjalankan aksi setelah pengguna menekan tombol konfirmasi.

## Lainnya
- **Mode Terang/Gelap**: tombol di sidebar atau header.
- **Logout**: ikon di pojok kanan atas header.
