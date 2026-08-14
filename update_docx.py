#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Script to update skripsi.docx with UML documentation content
"""

from docx import Document
from docx.shared import Pt, RGBColor, Inches
from docx.enum.text import WD_ALIGN_PARAGRAPH
from pathlib import Path

def add_heading_with_style(doc, text, level):
    """Add a heading with proper styling"""
    # Use level 1 for main title if level is 0
    if level == 0:
        level = 1
    heading = doc.add_heading(text, level=level)
    return heading

def add_table_from_markdown(doc, rows_data, header=None):
    """Add a table from markdown-style data"""
    num_cols = len(header) if header else len(rows_data[0])
    table = doc.add_table(rows=len(rows_data) + (1 if header else 0), cols=num_cols)
    table.style = 'Normal Table'
    
    row_idx = 0
    if header:
        header_cells = table.rows[0].cells
        for i, cell_text in enumerate(header):
            header_cells[i].text = cell_text
        row_idx = 1
    
    for row_data in rows_data:
        row_cells = table.rows[row_idx].cells
        for i, cell_text in enumerate(row_data):
            row_cells[i].text = str(cell_text)
        row_idx += 1
    
    return table

def main():
    # Create or open the document
    docx_path = Path("c:/Users/V14/SI_Pengelolaan_Data_Agenda/skripsi.docx")
    doc = Document(str(docx_path))
    
    # Clear existing content (keep it if needed, but we'll add to it)
    # For fresh content, uncomment below:
    # doc = Document()
    
    # Add Title
    add_heading_with_style(doc, "Dokumentasi UML — SI Pengelolaan Data Agenda", 0)
    
    # Add introduction
    doc.add_paragraph(
        "Dokumen ini berisi diagram UML (Use Case, Sequence, Activity, dan Class) untuk sistem "
        "SI Pengelolaan Data Agenda, aplikasi berbasis Laravel 12 + Vue 3 (PWA) untuk mengelola "
        "surat, disposisi, kegiatan, kehadiran, dan pengingat."
    )
    
    # Ringkasan Sistem
    add_heading_with_style(doc, "Ringkasan Sistem", 1)
    
    sistem_items = [
        "Login menggunakan email + password (session-based).",
        "Role: Admin, Staff, Asisten Daerah, OPD.",
        "Saat Staff membuat Surat, sistem otomatis:\n   1. Membuat Disposisi dengan keterangan diterima (tanpa perlu diedit), dan\n   2. Membuat Pengingat untuk seluruh akun Asisten Daerah.",
        "Saat salah satu role menambah Kegiatan, sistem otomatis membuat Pengingat untuk semua role.",
        "Saat menambah Kegiatan, sistem otomatis melakukan Cek Bentrok Jadwal: jika sudah ada kegiatan lain pada tanggal dan jam yang sama, pembuatan kegiatan ditolak.",
        "Hanya OPD yang dapat melakukan Konfirmasi Kehadiran kegiatan (hadir / tidak), tercatat per akun OPD. Role lain dapat melihat rekap dan daftar OPD yang mengonfirmasi.",
        "Disposisi dapat Diserahkan atau Ditolak (wajib alasan) oleh Asisten Daerah."
    ]
    
    for item in sistem_items:
        p = doc.add_paragraph(item, style='List Paragraph')
        p.paragraph_format.left_indent = Inches(0.25)
    
    # Aktor table
    add_heading_with_style(doc, "Aktor", 1)
    aktor_data = [
        ["Admin", "Mengelola pengguna & role."],
        ["Staff", "Mengelola surat, disposisi, kegiatan, dan pengingat pribadi."],
        ["Asisten Daerah", "Meninjau disposisi (menyerahkan/menolak) dan mengelola pengingat pribadi."],
        ["OPD", "Mengonfirmasi kehadiran kegiatan dan mengelola pengingat pribadi."]
    ]
    add_table_from_markdown(doc, aktor_data, header=["Aktor", "Deskripsi"])
    
    # Use Case Diagram
    add_heading_with_style(doc, "1. Use Case Diagram", 1)
    doc.add_paragraph(
        "Catatan: <<include>> menandai proses tambahan yang dijalankan otomatis oleh sistem setelah aksi utama."
    )
    doc.add_paragraph(
        "[USE CASE DIAGRAM: Lihat file UML_Documentation.md untuk diagram Mermaid - Admin, Staff, Asisten Daerah, OPD dengan berbagai use case termasuk Login, Lihat Dashboard, Kelola Surat, Disposisi, Kegiatan, Kehadiran, Pengingat, dan Pengguna]"
    )
    
    # Sequence Diagrams
    add_heading_with_style(doc, "2. Sequence Diagram", 1)
    
    # 2.1
    add_heading_with_style(doc, "2.1 Staff Membuat Surat → Auto Disposisi (Diterima) & Pengingat Asisten Daerah", 2)
    doc.add_paragraph(
        "[SEQUENCE DIAGRAM: Staff mengisi dan mengirim form Surat → Frontend mengirim POST /api/surat → "
        "API menyimpan ke Database → Auto: Buat Disposisi (keterangan = 'diterima') → "
        "Auto: Buat Pengingat untuk semua user Asisten Daerah → API mengembalikan 201 Surat tersimpan]"
    )
    
    # 2.2
    add_heading_with_style(doc, "2.2 Asisten Daerah Meninjau Disposisi (Serahkan / Tolak)", 2)
    doc.add_paragraph(
        "[SEQUENCE DIAGRAM: Asisten Daerah klik tombol 'Menyerahkan' atau 'Menolak' → "
        "Frontend kirim PATCH /api/disposisi/{id} dengan keterangan 'diserahkan' atau 'ditolak' + alasan → "
        "API validasi dan cek role → API update keterangan + alasan jika ditolak → "
        "API kembalikan data disposisi terbaru → UI perbarui status]"
    )
    
    # 2.3
    add_heading_with_style(doc, "2.3 Staff (atau Role Lain) Menambah Kegiatan → Auto Cek Jadwal & Pengingat", 2)
    doc.add_paragraph(
        "[SEQUENCE DIAGRAM: Staff isi dan kirim form Kegiatan → Frontend kirim POST /api/kegiatan → "
        "Auto: Cek bentrok jadwal (tanggal dan jam yang sama) → "
        "Jika bentrok: kembalikan 422 Jadwal bentrok → "
        "Jika tersedia: Simpan Kegiatan + Buat Pengingat untuk SEMUA user → 201 Kegiatan tersimpan]"
    )
    
    # 2.4
    add_heading_with_style(doc, "2.4 OPD Konfirmasi Kehadiran Kegiatan", 2)
    doc.add_paragraph(
        "[SEQUENCE DIAGRAM: OPD klik tombol 'Hadir' atau 'Tidak Hadir' → "
        "Frontend kirim POST /api/kegiatan/{id}/kehadiran dengan status → "
        "API validasi dan cek role OPD → API simpan/ubah kehadiran per OPD → "
        "Konfirmasi tersimpan → Status kehadiran diperbarui. Staff/Asisten dapat melihat rekap & daftar OPD]"
    )
    
    # Activity Diagrams
    add_heading_with_style(doc, "3. Activity Diagram", 1)
    
    # 3.1
    add_heading_with_style(doc, "3.1 Alur Pengelolaan Surat → Disposisi", 2)
    doc.add_paragraph(
        "[ACTIVITY DIAGRAM: Mulai → Staff menginput data Surat → Sistem menyimpan Surat → "
        "Auto: Membuat Disposisi status Diterima → Auto: Membuat Pengingat ke Asisten Daerah → "
        "Asisten Daerah meninjau Disposisi → (Ya) Status Diserahkan / (Tidak) Asisten mengisi Alasan → "
        "Status Ditolak + Alasan / Status Diserahkan → Selesai]"
    )
    
    # 3.2
    add_heading_with_style(doc, "3.2 Alur Konfirmasi Kehadiran Kegiatan (OPD)", 2)
    doc.add_paragraph(
        "[ACTIVITY DIAGRAM: Mulai → OPD membuka halaman Kegiatan → "
        "(Hadir / Tidak Hadir) → Kirim konfirmasi → Sistem menyimpan konfirmasi per OPD → "
        "Role lain dapat melihat rekap & daftar OPD → Selesai]"
    )
    
    # 3.3
    add_heading_with_style(doc, "3.3 Alur Menambah Kegiatan dengan Auto Cek Bentrok Jadwal", 2)
    doc.add_paragraph(
        "[ACTIVITY DIAGRAM: Mulai → Staff menginput data Kegiatan → "
        "Auto cek bentrok jadwal → (Bentrok pada tanggal dan jam yang sama) Tolak pembuatan → Pesan: Jadwal bentrok / "
        "(Tersedia) Simpan data Kegiatan + Auto Pengingat ke semua role → Selesai]"
    )
    
    # Class Diagram
    add_heading_with_style(doc, "4. Class Diagram", 1)
    doc.add_paragraph(
        "[CLASS DIAGRAM: Lihat file UML_Documentation.md untuk diagram Mermaid - "
        "Role, User, Surat, Disposisi, Kegiatan, KegiatanKehadiran, Pengingat dengan atribut dan relasi]"
    )
    
    # Add classes description
    add_heading_with_style(doc, "Class Descriptions", 2)
    
    classes = {
        "Role": ["id (int)", "name (string)", "slug (string)"],
        "User": ["id (int)", "name (string)", "email (string)", "role_id (int)", "password (string)", "role_slug() (method)"],
        "Surat": ["id (int)", "tanggal (datetime)", "nomor_surat (string)", "asal_surat (string)", "perihal (string)",
                  "kepada (string)", "tanggal_pelaksanaan (datetime)", "tempat_pelaksanaan (string)",
                  "pembawa_surat (string)", "tandatangan (string)", "disposisis() (method)"],
        "Disposisi": ["id (int)", "surat_id (int)", "tanggal (datetime)", "nomor_surat (string)", "asal_surat (string)",
                      "perihal (string)", "kepada (string)", "pembawa_surat (string)", "tandatangan_penerima (string)",
                      "tandatangan_dituju (string)", "keterangan (string)", "alasan (string)", "surat() (method)"],
        "Kegiatan": ["id (int)", "nama_kegiatan (string)", "tempat_kegiatan (string)", "tanggal_kegiatan (datetime)",
                     "uraian_kegiatan (string)", "realisasi_pelaksanaan (string)", "keterangan (string)",
                     "status (string)", "nama_penyusun (string)", "kehadiran() (method)"],
        "KegiatanKehadiran": ["id (int)", "kegiatan_id (int)", "user_id (int)", "status (string)",
                              "kegiatan() (method)", "user() (method)"],
        "Pengingat": ["id (int)", "user_id (int)", "judul (string)", "deskripsi (string)",
                      "tanggal_pengingat (datetime)", "prioritas (string)", "status (string)", "user() (method)"]
    }
    
    for class_name, attributes in classes.items():
        p = doc.add_paragraph(style='List Paragraph')
        run = p.add_run(f"{class_name}: ")
        run.bold = True
        p.add_run(", ".join(attributes))
        p.paragraph_format.left_indent = Inches(0.25)
    
    # Relationships
    add_heading_with_style(doc, "Relationships", 2)
    relationships = [
        "Role (1) → (0..*) User : role_id",
        "User (1) → (0..*) Pengingat : user_id",
        "Surat (1) → (0..*) Disposisi : surat_id",
        "Kegiatan (1) → (0..*) KegiatanKehadiran : kegiatan_id",
        "User (1) → (0..*) KegiatanKehadiran : user_id"
    ]
    for rel in relationships:
        p = doc.add_paragraph(rel, style='List Paragraph')
        p.paragraph_format.left_indent = Inches(0.25)
    
    # Enum / Status values
    add_heading_with_style(doc, "Nilai Enum / Status", 1)
    
    enum_data = [
        ["Disposisi.keterangan", "diterima, ditolak, diserahkan"],
        ["Kegiatan.realisasi_pelaksanaan", "terlaksana, tidak"],
        ["Kegiatan.status", "pelaksanaan, laporan"],
        ["KegiatanKehadiran.status", "hadir, tidak"],
        ["Pengingat.prioritas", "rendah, sedang, tinggi"],
        ["Pengingat.status", "pending, selesai"]
    ]
    add_table_from_markdown(doc, enum_data, header=["Atribut", "Nilai"])
    
    # Save the document
    doc.save(str(docx_path))
    print(f"✓ Successfully updated {docx_path}")
    print(f"  - Added comprehensive UML documentation")
    print(f"  - Included all system descriptions, actors, use cases, sequence, activity, and class diagrams")
    print(f"  - Added enum/status values table")

if __name__ == "__main__":
    main()
