# IKU-PROJECT

👥 AKUN DEMO
Role Username Password Akses Admin username:admin password:admin123 Kelola semua data Dosen username:dosen1 password:dosen123 Input nilai & validasi Mahasiswa username:mhs001 password:mhs123 Lihat nilai & input kegiatan

✨ FITUR LENGKAP PER ROLE
👨‍💼 ADMIN (5 Menu)
1. Dashboard

Statistik total mahasiswa, dosen, mata kuliah
Kegiatan pending validasi
Data mahasiswa terbaru
Kegiatan menunggu validasi

2. Data Mahasiswa

CRUD Mahasiswa (Create, Read, Update, Delete)
Search mahasiswa by NIM/Nama
Auto generate username & password
Validasi NIM unik

3. Data Dosen

CRUD Dosen
Search dosen by NIP/Nama
Lihat jumlah mata kuliah per dosen
Statistik dosen aktif/non-aktif

4. Mata Kuliah

CRUD Mata Kuliah
Assign dosen pengampu
Filter by semester
Search mata kuliah
Lihat jumlah mahasiswa per MK

5. Pengumuman

CRUD Pengumuman
Set tanggal pengumuman
Status aktif/expired
Preview untuk user
Search pengumuman


👨‍🏫 DOSEN (6 Menu)
1. Dashboard

Statistik mata kuliah diampu
Total mahasiswa
Kegiatan pending
List mata kuliah dengan aksi cepat
Kegiatan menunggu validasi

2. Input Nilai

Pilih mata kuliah
Input nilai: Tugas (30%), UTS (30%), UAS (40%)
Auto hitung nilai akhir & grade
Modal edit untuk setiap mahasiswa
Grade: A, A-, B+, B, B-, C+, C, D, E

3. Input Absensi

Pilih mata kuliah & pertemuan
Input status: Hadir, Izin, Sakit, Alpha
Rekap absensi per mahasiswa
Persentase kehadiran
History pertemuan

4. Validasi Kegiatan

Lihat kegiatan menunggu validasi
Approve/Reject kegiatan
Set poin kegiatan (sesuai jenis)
Saran poin otomatis
Filter by status & jenis

5. Kegiatan Mahasiswa ⭐ NEW!

Lihat semua kegiatan mahasiswa
Filter by status & jenis
Search mahasiswa/kegiatan
Top 10 mahasiswa aktif (🥇🥈🥉)
Chart per jenis kegiatan
Detail kegiatan per mahasiswa
Export ke CSV

6. Export Kegiatan ⭐ NEW!

Export data ke CSV
Excel-compatible format
Filter data sebelum export


👨‍🎓 MAHASISWA (5 Menu)
1. Dashboard

Statistik: Total MK, Kegiatan, Poin, IPK
5 Nilai terbaru
5 Kegiatan terbaru
Quick access menu

2. Nilai Kuliah

Lihat semua nilai mata kuliah
Filter by semester & tahun ajaran
Tampil detail: Tugas, UTS, UAS, Nilai Akhir, Grade
Hitung IPK otomatis
Keterangan grade

3. Input Kegiatan

Form input kegiatan baru
Jenis: Organisasi, Lomba, Seminar, Workshop, Penelitian, Pengabdian
Tanggal mulai & selesai
Deskripsi kegiatan
Status: Menunggu/Disetujui/Ditolak
Statistik kegiatan
History semua kegiatan

4. Rekap Absensi

Statistik kehadiran keseluruhan
Progress bar persentase
Rekap per mata kuliah
Status: Aman (≥75%), Perhatian (50-74%), Bahaya (<50%)
Detail absensi per pertemuan
Ketentuan kehadiran minimal

5. Profil ⭐ NEW!

Profil card dengan avatar
Edit email & no HP
Ganti password (validasi password lama)
Statistik: IPK, MK, Kegiatan, Poin
3 Nilai terbaik
3 Kegiatan terakhir
Ringkasan akademik


🔐 SISTEM REGISTRASI ⭐ NEW!
Fitur Register:

Multi-step form (Pilih Role → Isi Data)
Register untuk Mahasiswa & Dosen
Validasi lengkap:

Username unique
NIM/NIP unique
Password min 6 karakter
Password confirmation


Auto redirect ke login
Form responsive

Link:

Di halaman login: "Belum punya akun? Daftar di sini"
Direct: http://localhost/sim-kampus/register.php


🎨 TEKNOLOGI & FITUR TEKNIS
Backend:

PHP 7.4+ dengan PDO
MySQL 5.7+ database
Prepared Statements (SQL Injection proof)
Session Management
Multi-role authentication

Frontend:

HTML5 semantic markup
CSS3 dengan Flexbox & Grid
Vanilla JavaScript (no framework)
Responsive design (mobile-friendly)
Modern gradient UI

Security:

✅ Prepared Statements
✅ Session validation
✅ Role-based access control
✅ XSS prevention (htmlspecialchars)
✅ Password hashing (MD5 - upgrade to bcrypt recommended)

Features:

✅ CRUD operations
✅ Search & Filter
✅ Pagination ready
✅ Export to CSV
✅ Modal dialogs
✅ Alert notifications
✅ Auto calculations
✅ Progress bars
✅ Statistics dashboard
✅ Responsive tables
