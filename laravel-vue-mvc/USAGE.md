# Panduan Penggunaan — SI Pengelolaan Data Agenda

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

Semua akun menggunakan password: **password**

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@example.com` | `password` |
| Staff | `staff@example.com` | `password` |
| Asisten Daerah | `asisten@example.com` | `password` |
| OPD | `opd@example.com` | `password` |

## Hak Akses per Role

| Menu | Admin | Staff | Asisten Daerah | OPD |
|------|:-----:|:-----:|:--------------:|:---:|
| Dashboard | ✅ | ✅ | ✅ | ✅ |
| Kegiatan — lihat | ✅ | ✅ | ✅ | ✅ |
| Kegiatan — tambah/edit/hapus | ❌ | ✅ | ❌ | ❌ |
| Surat | ❌ | ✅ | ❌ | ❌ |
| Disposisi | ❌ | ✅ | ❌ | ❌ |
| Pengguna (kelola akun) | ✅ | ❌ | ❌ | ❌ |

## Menu

### Dashboard
Ringkasan statistik keterangan disposisi: **Diterima**, **Ditolak**, dan **Disahkan**.

### Kegiatan
Data kegiatan dengan kolom:
- **Nama Kegiatan**, **Tempat Kegiatan**, **Tanggal Kegiatan**, **Uraian Kegiatan**
- **Realisasi Pelaksanaan**: `Terlaksana` / `Tidak`
- **Keterangan**: catatan tambahan (opsional)
- **Status**: `Pelaksanaan` / `Laporan`
- **Nama Penyusun** & **Tanda Tangan Penyusun**: wajib diisi hanya jika status = `Laporan`

Hanya **Staff** yang dapat menambah, mengedit, dan menghapus. Role lain hanya dapat melihat.

### Surat (Staff)
Kelola data surat. Saat surat baru dibuat, sistem otomatis:
1. Membuat data **Disposisi** (kolom Penerima/Dituju dikosongkan), dan
2. Mengarahkan Anda ke form edit Disposisi untuk melengkapi **Penerima** dan **Dituju**.

### Disposisi (Staff)
Data disposisi yang otomatis dibuat dari Surat. Dapat diedit untuk mengisi **Penerima**, **Dituju**, dan **Keterangan**:
- **Diterima** / **Disahkan**: tanpa syarat tambahan
- **Ditolak**: wajib mengisi **Alasan Ditolak**

Gunakan tombol **Disposisi** pada baris data Surat untuk melompat ke disposisi terkait.

### Pengguna (Admin)
Kelola akun pengguna: tambah, edit (nama, email, role, password), dan hapus. Akun sendiri tidak dapat dihapus.

## Lainnya
- **Mode Terang/Gelap**: tombol di sidebar atau header.
- **Logout**: ikon di pojok kanan atas header.
