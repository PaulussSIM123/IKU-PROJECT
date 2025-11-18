-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2025 at 05:36 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `simkampus1`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(10) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('hadir','izin','sakit','alpha') NOT NULL,
  `pertemuan_ke` int(11) NOT NULL,
  `keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `kode_mk`, `nim`, `tanggal`, `status`, `pertemuan_ke`, `keterangan`) VALUES
(1, 'TI101', '2021001', '2025-11-10', 'hadir', 1, NULL),
(2, 'TI101', '2021001', '2025-11-11', 'hadir', 2, NULL),
(3, 'TI101', '2021001', '2025-11-13', 'hadir', 3, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `dosen`
--

CREATE TABLE `dosen` (
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dosen`
--

INSERT INTO `dosen` (`nip`, `nama`, `jurusan`, `email`, `no_hp`, `user_id`) VALUES
('12345678', 'paulus', 'Teknik Informatika', 'paulus123@gmail.com', '081269224290', 5),
('1234567890', 'YANTOYANTI', 'Teknik Informatika', 'YANTOYANTI@gmail.com', '081269224290', 8),
('197001011998031001', 'Dr. Ahmad Yani, S.Kom., M.Kom.', 'Teknik Informatika', 'ahmad.yani@kampus.ac.id', '081234567890', 2);

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nama_kegiatan` varchar(200) NOT NULL,
  `jenis_kegiatan` enum('organisasi','lomba','seminar','workshop','penelitian','pengabdian','lainnya') NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `poin` int(11) DEFAULT 0,
  `status` enum('menunggu','disetujui','ditolak') DEFAULT 'menunggu',
  `bukti_kegiatan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `nim`, `nama_kegiatan`, `jenis_kegiatan`, `tanggal_mulai`, `tanggal_selesai`, `deskripsi`, `poin`, `status`, `bukti_kegiatan`, `created_at`) VALUES
(1, '2021001', 'Lomba Programming Competition', 'lomba', '2024-10-15', NULL, 'Juara 2 Lomba Programming tingkat nasional', 50, 'disetujui', NULL, '2025-11-06 07:47:04'),
(2, '1234567', 'test', 'seminar', '2025-06-11', '2025-07-11', 'test', 10, 'disetujui', NULL, '2025-11-07 04:15:14'),
(3, '1234567', 'test', 'organisasi', '2025-11-11', '2025-11-11', 'IKUT ORGANISASI TEST', 0, 'ditolak', NULL, '2025-11-11 03:57:57'),
(4, '2021001', 'test', 'penelitian', '2028-11-11', '2028-11-11', 'TEST', 0, 'menunggu', NULL, '2025-11-13 03:11:27');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `kode_mk` varchar(10) NOT NULL,
  `nim` varchar(20) NOT NULL,
  `nilai_tugas` decimal(5,2) DEFAULT 0.00,
  `nilai_uts` decimal(5,2) DEFAULT 0.00,
  `nilai_uas` decimal(5,2) DEFAULT 0.00,
  `nilai_akhir` decimal(5,2) DEFAULT 0.00,
  `grade` varchar(2) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `tahun_ajaran` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `kode_mk`, `nim`, `nilai_tugas`, `nilai_uts`, `nilai_uas`, `nilai_akhir`, `grade`, `semester`, `tahun_ajaran`) VALUES
(1, 'TI101', '2021001', 85.50, 87.00, 88.00, 86.95, 'A', 'Ganjil', '2021/2022'),
(7, 'TI102', '2021001', 0.00, 0.00, 0.00, 0.00, 'E', 'Ganjil', '2025/2026'),
(10, 'TI103', '2021001', 0.00, 0.00, 0.00, 0.00, '-', 'Ganjil', '2025/2026');

-- --------------------------------------------------------

--
-- Table structure for table `mahasiswa`
--

CREATE TABLE `mahasiswa` (
  `nim` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `angkatan` year(4) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `no_hp` varchar(15) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mahasiswa`
--

INSERT INTO `mahasiswa` (`nim`, `nama`, `jurusan`, `angkatan`, `email`, `no_hp`, `user_id`) VALUES
('1234567', 'paulus', 'TEKNIK INFORMATIKA', '2021', 'simanjuntakpaulus0@gmail.com', '081269224290', 4),
('2021001', 'Budi Santoso', 'TEKNIK INFORMATIKA', '2021', 'budi@student.ac.id', '081234567891', 3);

-- --------------------------------------------------------

--
-- Table structure for table `mata_kuliah`
--

CREATE TABLE `mata_kuliah` (
  `kode_mk` varchar(10) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `semester` int(11) NOT NULL,
  `dosen_nip` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_kuliah`
--

INSERT INTO `mata_kuliah` (`kode_mk`, `nama_mk`, `sks`, `semester`, `dosen_nip`) VALUES
('TI101', 'Pemrograman Dasar', 3, 1, '197001011998031001'),
('TI102', 'Basis Data', 3, 2, '197001011998031001'),
('TI103', 'DATA MINING', 3, 1, '12345678'),
('TI104', 'KALKULUS', 3, 3, '1234567890');

-- --------------------------------------------------------

--
-- Table structure for table `pengumuman`
--

CREATE TABLE `pengumuman` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `isi` text NOT NULL,
  `tanggal` date NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengumuman`
--

INSERT INTO `pengumuman` (`id`, `judul`, `isi`, `tanggal`, `user_id`, `created_at`) VALUES
(1, 'LOMBA HACKTON', 'LOMBA HTML CSS', '2025-11-17', 1, '2025-11-17 02:16:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','dosen','mahasiswa') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'admin', '2025-11-06 07:47:04'),
(2, 'dosen1', 'd5bbfb47ac3160c31fa8c247827115aa', 'dosen', '2025-11-06 07:47:04'),
(3, 'mhs001', '39f55dd65ead9c938fa93a765983bff0', 'mahasiswa', '2025-11-06 07:47:04'),
(4, 'paulus', '962488411942f34adc83f1ea3de27cc5', 'mahasiswa', '2025-11-07 04:13:54'),
(5, 'paulus123', 'a6f3344154d0a79403419fa26828f951', 'dosen', '2025-11-07 07:41:58'),
(6, 'test', 'cc03e747a6afbbcbf8be7668acfebee5', 'mahasiswa', '2025-11-10 02:49:55'),
(8, 'YANTOYANTI', 'e52ce8dc6137d2eab868d64f794912bf', 'dosen', '2025-11-12 04:32:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_mk` (`kode_mk`),
  ADD KEY `nim` (`nim`);

--
-- Indexes for table `dosen`
--
ALTER TABLE `dosen`
  ADD PRIMARY KEY (`nip`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nim` (`nim`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_mk` (`kode_mk`),
  ADD KEY `nim` (`nim`);

--
-- Indexes for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD PRIMARY KEY (`nim`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD PRIMARY KEY (`kode_mk`),
  ADD KEY `dosen_nip` (`dosen_nip`);

--
-- Indexes for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pengumuman`
--
ALTER TABLE `pengumuman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_mk`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`) ON DELETE CASCADE;

--
-- Constraints for table `dosen`
--
ALTER TABLE `dosen`
  ADD CONSTRAINT `dosen_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD CONSTRAINT `kegiatan_ibfk_1` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`) ON DELETE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`kode_mk`) REFERENCES `mata_kuliah` (`kode_mk`) ON DELETE CASCADE,
  ADD CONSTRAINT `kelas_ibfk_2` FOREIGN KEY (`nim`) REFERENCES `mahasiswa` (`nim`) ON DELETE CASCADE;

--
-- Constraints for table `mahasiswa`
--
ALTER TABLE `mahasiswa`
  ADD CONSTRAINT `mahasiswa_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `mata_kuliah`
--
ALTER TABLE `mata_kuliah`
  ADD CONSTRAINT `mata_kuliah_ibfk_1` FOREIGN KEY (`dosen_nip`) REFERENCES `dosen` (`nip`) ON DELETE SET NULL;

--
-- Constraints for table `pengumuman`
--
ALTER TABLE `pengumuman`
  ADD CONSTRAINT `pengumuman_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
