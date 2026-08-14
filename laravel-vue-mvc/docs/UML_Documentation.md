# Dokumentasi UML — SI Pengelolaan Data Agenda

Dokumen ini berisi diagram UML (Use Case, Sequence, Activity, dan Class) untuk sistem **SI Pengelolaan Data Agenda**, aplikasi berbasis **Laravel 12 + Vue 3 (PWA)** untuk mengelola surat, disposisi, kegiatan, kehadiran, dan pengingat.

## Ringkasan Sistem

- **Login** menggunakan email + password (session-based).
- **Role**: `Admin`, `Staff`, `Asisten Daerah`, `OPD`.
- Saat **Staff** membuat **Surat**, sistem otomatis:
  1. Membuat **Disposisi** dengan keterangan `diterima` (tanpa perlu diedit), dan
  2. Membuat **Pengingat** untuk seluruh akun **Asisten Daerah**.
- Saat salah satu role menambah **Kegiatan**, sistem otomatis membuat **Pengingat** untuk **semua role**.
- Saat menambah **Kegiatan**, sistem otomatis melakukan **Cek Bentrok Jadwal**: jika sudah ada kegiatan lain pada tanggal dan jam yang sama, pembuatan kegiatan ditolak.
- Hanya **OPD** yang dapat melakukan **Konfirmasi Kehadiran** kegiatan (`hadir` / `tidak`), tercatat per akun OPD. Role lain dapat melihat rekap dan daftar OPD yang mengonfirmasi.
- **Disposisi** dapat **Diserahkan** atau **Ditolak** (wajib alasan) oleh Asisten Daerah.

## Aktor

| Aktor | Deskripsi |
|-------|-----------|
| **Admin** | Mengelola pengguna & role. |
| **Staff** | Mengelola surat, disposisi, kegiatan, dan pengingat pribadi. |
| **Asisten Daerah** | Meninjau disposisi (menyerahkan/menolak) dan mengelola pengingat pribadi. |
| **OPD** | Mengonfirmasi kehadiran kegiatan dan mengelola pengingat pribadi. |

---

## 1. Use Case Diagram

```mermaid
flowchart TD
    %% ==== Aktor ====
    Admin([Admin])
    Staff([Staff])
    Asisten([Asisten Daerah])
    OPD([OPD])

    subgraph Umum
        UC_LOGIN[Login]
        UC_DASH[Lihat Dashboard]
    end

    subgraph Surat & Disposisi
        UC_SURAT[Kelola Surat: buat / ubah / hapus]
        UC_DISP_EDIT[Kelola Disposisi: ubah / hapus]
        UC_DISP_SERAH[Menyerahkan Disposisi]
        UC_DISP_TOLAK[Menolak Disposisi dengan Alasan]
    end

    subgraph Auto-Proses
        UC_AUTO_DISP((Auto: Buat Disposisi status Diterima))
        UC_AUTO_NOTIF_ASIST((Auto: Pengingat ke Asisten Daerah))
        UC_AUTO_NOTIF_ALL((Auto: Pengingat ke Semua Role))
        UC_AUTO_CEK_BENTROK((Auto: Cek Bentrok Jadwal Kegiatan))
    end

    subgraph Kegiatan & Kehadiran
        UC_KEG_CRUD[Kelola Kegiatan: buat / ubah / hapus]
        UC_KONFIRMASI[Konfirmasi Kehadiran: Hadir / Tidak]
        UC_LIHAT_KEHADIRAN[Lihat Rekap & Daftar OPD]
    end

    subgraph Pengingat
        UC_PENGINGAT[Kelola Pengingat Pribadi]
    end

    subgraph Pengguna
        UC_USER[Kelola Pengguna & Role]
    end

    %% ==== Login & Dashboard semua role ====
    Admin --> UC_LOGIN
    Staff --> UC_LOGIN
    Asisten --> UC_LOGIN
    OPD --> UC_LOGIN

    Admin --> UC_DASH
    Staff --> UC_DASH
    Asisten --> UC_DASH
    OPD --> UC_DASH

    %% ==== Surat & Disposisi ====
    Staff --> UC_SURAT
    UC_SURAT -->|<<include>>| UC_AUTO_DISP
    UC_SURAT -->|<<include>>| UC_AUTO_NOTIF_ASIST

    Staff --> UC_DISP_EDIT
    Asisten --> UC_DISP_SERAH
    Asisten --> UC_DISP_TOLAK

    %% ==== Kegiatan & Kehadiran ====
    Staff --> UC_KEG_CRUD
    UC_KEG_CRUD -->|<<include>>| UC_AUTO_CEK_BENTROK
    UC_KEG_CRUD -->|<<include>>| UC_AUTO_NOTIF_ALL

    OPD --> UC_KONFIRMASI
    Staff --> UC_LIHAT_KEHADIRAN
    Asisten --> UC_LIHAT_KEHADIRAN

    %% ==== Pengingat & Pengguna ====
    Staff --> UC_PENGINGAT
    Asisten --> UC_PENGINGAT
    OPD --> UC_PENGINGAT

    Admin --> UC_USER
```

> Catatan: `<<include>>` menandai proses tambahan yang dijalankan otomatis oleh sistem setelah aksi utama.

---

## 2. Sequence Diagram

### 2.1 Staff Membuat Surat → Auto Disposisi (Diterima) & Pengingat Asisten Daerah

```mermaid
sequenceDiagram
    actor Staff
    participant UI as Frontend (Vue)
    participant API as API (Laravel)
    participant DB as Database

    Staff->>UI: Isi & kirim form Surat
    UI->>API: POST /api/surat
    API->>DB: Simpan data Surat

    rect rgb(230, 240, 255)
        Note over API,DB: Proses otomatis
        API->>DB: Buat Disposisi (keterangan = "diterima")
        API->>DB: Buat Pengingat untuk semua user Asisten Daerah
    end

    API-->>UI: 201 Surat tersimpan
    UI-->>Staff: Navigasi ke Disposisi (status Diterima otomatis)
```

### 2.2 Asisten Daerah Meninjau Disposisi (Serahkan / Tolak)

```mermaid
sequenceDiagram
    actor Asisten as Asisten Daerah
    participant UI as Frontend (Vue)
    participant API as API (Laravel)
    participant DB as Database

    Asisten->>UI: Klik tombol "Menyerahkan" atau "Menolak"
    alt Diserahkan
        UI->>API: PATCH /api/disposisi/{id} { keterangan: "diserahkan" }
    else Ditolak (wajib alasan)
        UI->>API: PATCH /api/disposisi/{id} { keterangan: "ditolak", alasan }
    end

    API->>API: Validasi & cek role (asisten_daerah)
    API->>DB: Update keterangan (+ alasan jika ditolak)
    API-->>UI: Data disposisi terbaru
    UI-->>Asisten: Status diperbarui
```

### 2.3 Staff (atau Role Lain) Menambah Kegiatan → Auto Cek Jadwal & Pengingat Semua Role

```mermaid
sequenceDiagram
    actor Staff
    participant UI as Frontend (Vue)
    participant API as API (Laravel)
    participant DB as Database

    Staff->>UI: Isi dan kirim form Kegiatan
    UI->>API: POST /api/kegiatan

    rect rgb(230, 240, 255)
        Note over API,DB: Proses otomatis
        API->>DB: Cek bentrok jadwal (tanggal dan jam yang sama)
        alt Jadwal bentrok (sudah ada kegiatan lain)
            API-->>UI: 422 Jadwal bentrok
            UI-->>Staff: Perlihatkan pesan error
        else Jadwal tersedia
            API->>DB: Simpan data Kegiatan
            API->>DB: Buat Pengingat untuk SEMUA user (semua role)
            API-->>UI: 201 Kegiatan tersimpan
            UI-->>Staff: Daftar kegiatan diperbarui
        end
    end
```

### 2.4 OPD Konfirmasi Kehadiran Kegiatan

```mermaid
sequenceDiagram
    actor OPD
    participant UI as Frontend (Vue)
    participant API as API (Laravel)
    participant DB as Database

    OPD->>UI: Klik tombol "Hadir" / "Tidak Hadir"
    UI->>API: POST /api/kegiatan/{id}/kehadiran { status: "hadir" | "tidak" }

    API->>API: Validasi status & cek role (opd)
    API->>DB: Simpan / ubah kehadiran per OPD (kegiatan_id, user_id, status)
    API-->>UI: Konfirmasi tersimpan
    UI-->>OPD: Status kehadiran diperbarui

    Note over UI: Staff / Asisten melihat rekap & daftar OPD<br/>(hadir_count, tidak_count, nama OPD)
```

---

## 3. Activity Diagram

### 3.1 Alur Pengelolaan Surat → Disposisi

```mermaid
flowchart TD
    A([Mulai]) --> B[Staff menginput data Surat]
    B --> C[Sistem menyimpan Surat]
    C --> D[Auto: Membuat Disposisi status Diterima]
    D --> E[Auto: Membuat Pengingat ke Asisten Daerah]
    E --> F[Asisten Daerah meninjau Disposisi]
    F --> G{Apakah disetujui?}
    G -- Ya --> H[Status Disposisi: Diserahkan]
    G -- Tidak --> I[Asisten Daerah mengisi Alasan Penolakan]
    I --> J[Status Disposisi: Ditolak + Alasan]
    H --> K([Selesai])
    J --> K
```

### 3.2 Alur Konfirmasi Kehadiran Kegiatan (OPD)

```mermaid
flowchart TD
    A([Mulai]) --> B[OPD membuka halaman Kegiatan]
    B --> C{Memilih status kehadiran}
    C -- Hadir --> D[Kirim konfirmasi hadir]
    C -- Tidak Hadir --> E[Kirim konfirmasi tidak]
    D --> F[Sistem menyimpan konfirmasi per OPD]
    E --> F
    F --> G[Role lain dapat melihat rekap & daftar OPD]
    G --> H([Selesai])
```

### 3.3 Alur Menambah Kegiatan dengan Auto Cek Bentrok Jadwal

```mermaid
flowchart TD
    A([Mulai]) --> B[Staff menginput data Kegiatan]
    B --> D{Auto cek bentrok jadwal}
    D -- Bentrok pada tanggal dan jam yang sama --> E[Tolak pembuatan kegiatan]
    E --> E1[Pesan: Jadwal bentrok]
    D -- Tersedia --> F[Simpan data Kegiatan]
    F --> G[Auto: Pengingat ke semua role]
    E1 --> H([Selesai])
    G --> H
```

---

## 4. Class Diagram

```mermaid
classDiagram
    direction LR

    class Role {
        +int id
        +string name
        +string slug
    }

    class User {
        +int id
        +string name
        +string email
        +int role_id
        +string password
        +role_slug()
    }

    class Surat {
        +int id
        +datetime tanggal
        +string nomor_surat
        +string asal_surat
        +string perihal
        +string kepada
        +datetime tanggal_pelaksanaan
        +string tempat_pelaksanaan
        +string pembawa_surat
        +string tandatangan
        +disposisis()
    }

    class Disposisi {
        +int id
        +int surat_id
        +datetime tanggal
        +string nomor_surat
        +string asal_surat
        +string perihal
        +string kepada
        +string pembawa_surat
        +string tandatangan_penerima
        +string tandatangan_dituju
        +string keterangan
        +string alasan
        +surat()
    }

    class Kegiatan {
        +int id
        +string nama_kegiatan
        +string tempat_kegiatan
        +datetime tanggal_kegiatan
        +string uraian_kegiatan
        +string realisasi_pelaksanaan
        +string keterangan
        +string status
        +string nama_penyusun
        +kehadiran()
    }

    class KegiatanKehadiran {
        +int id
        +int kegiatan_id
        +int user_id
        +string status
        +kegiatan()
        +user()
    }

    class Pengingat {
        +int id
        +int user_id
        +string judul
        +string deskripsi
        +datetime tanggal_pengingat
        +string prioritas
        +string status
        +user()
    }

    Role "1" <-- "0..*" User : role_id
    User "1" <-- "0..*" Pengingat : user_id
    Surat "1" <-- "0..*" Disposisi : surat_id
    Kegiatan "1" <-- "0..*" KegiatanKehadiran : kegiatan_id
    User "1" <-- "0..*" KegiatanKehadiran : user_id
```

> Diagram dibuat dengan **Mermaid**. Untuk melihat hasilnya, buka file ini di GitHub, GitLab, VS Code (ekstensi Mermaid), atau tool Markdown yang mendukung Mermaid.

### Nilai Enum / Status

| Atribut | Nilai |
|---------|-------|
| `Disposisi.keterangan` | `diterima`, `ditolak`, `diserahkan` |
| `Kegiatan.realisasi_pelaksanaan` | `terlaksana`, `tidak` |
| `Kegiatan.status` | `pelaksanaan`, `laporan` |
| `KegiatanKehadiran.status` | `hadir`, `tidak` |
| `Pengingat.prioritas` | `rendah`, `sedang`, `tinggi` |
| `Pengingat.status` | `pending`, `selesai` |