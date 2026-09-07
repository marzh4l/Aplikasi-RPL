-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 07, 2026 at 06:55 AM
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
-- Database: `db_rpl`
--

-- --------------------------------------------------------

--
-- Table structure for table `bahan_kajian`
--

CREATE TABLE `bahan_kajian` (
  `id` int(2) NOT NULL,
  `id_prodi` int(2) NOT NULL,
  `periode_tahun` varchar(9) NOT NULL,
  `bahan_kajian` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bahan_kajian`
--

INSERT INTO `bahan_kajian` (`id`, `id_prodi`, `periode_tahun`, `bahan_kajian`) VALUES
(1, 1, '2024/2025', 'farmasi_20242025.pdf'),
(2, 2, '2022/2023', 'ilmu_keperawatan_20222023.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `konversi_detail`
--

CREATE TABLE `konversi_detail` (
  `id` int(11) NOT NULL,
  `id_konversi` int(11) NOT NULL,
  `kode_mk_asal` varchar(20) DEFAULT NULL,
  `nama_mk_asal` varchar(100) DEFAULT NULL,
  `sks_asal` int(11) DEFAULT NULL,
  `nilai_indek_asal` decimal(3,2) DEFAULT NULL,
  `nilai_huruf_asal` varchar(2) DEFAULT NULL,
  `kode_mk_tujuan` varchar(20) DEFAULT NULL,
  `nama_mk_tujuan` varchar(100) DEFAULT NULL,
  `sks_tujuan` int(11) DEFAULT NULL,
  `nilai_indek_tujuan` decimal(3,2) DEFAULT NULL,
  `nilai_huruf_tujuan` varchar(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konversi_detail`
--

INSERT INTO `konversi_detail` (`id`, `id_konversi`, `kode_mk_asal`, `nama_mk_asal`, `sks_asal`, `nilai_indek_asal`, `nilai_huruf_asal`, `kode_mk_tujuan`, `nama_mk_tujuan`, `sks_tujuan`, `nilai_indek_tujuan`, `nilai_huruf_tujuan`) VALUES
(1, 1, 'F101', 'Pendidikan Agama', 2, 4.00, 'A', 'FPK 101-1', 'Pendidikan Agama Islam I (Religious education I)', 2, 4.00, 'A+'),
(2, 1, 'F408', 'Bahasa Indonesia', 2, 4.00, 'A', 'FPK 106', 'Bahasa Indonesia (Indonesian)', 2, 4.00, 'A+'),
(3, 1, 'F102', 'Pancasila', 2, 3.00, 'B', 'FPK 104', 'Pendidikan Pancasila & Kewarganegaraan', 2, 3.00, 'B'),
(4, 1, 'F203', 'Bahasa Inggris', 2, 4.00, 'A', 'FPK 102', 'Bahasa Inggris (English)', 2, 4.00, 'A+'),
(5, 1, 'F105', 'Farmasetika Dasar', 4, 3.00, 'B', 'FKB 303 T', 'Farmasetika Dasar', 2, 3.00, 'B'),
(6, 1, 'F105', 'Farmasetika Dasar', 4, 3.00, 'B', 'FKB 303 P', 'Praktikum Farmasetika Dasar', 1, 3.00, 'B'),
(7, 1, 'F104', 'Anatomi Fisiologi Manusia', 2, 4.00, 'A', 'FKK 209', 'Anatomi Fisiologi Manusia I (Human Anatomy and Physiology I)', 2, 4.00, 'A+'),
(8, 1, 'F106', 'Kimia Dasar', 2, 3.00, 'B', 'FKK 214 T', 'Kimia Dasar (Basic Chemistry)', 2, 3.00, 'B'),
(9, 1, 'F207', 'Farmasi Fisika', 3, 3.00, 'B', 'FKB 302 T', 'Fisika Farmasi (Pharmacy Physics)', 3, 3.00, 'B'),
(10, 1, 'F206', 'Farmakologi Dasar', 2, 4.00, 'A', 'FKB 311', 'Farmakologi Dasar', 2, 4.00, 'A+'),
(11, 1, 'F302', 'Kimia Farmasi I', 2, 4.00, 'A', 'FKB 215 T', 'Kimia Farmasi Kualitatif', 2, 4.00, 'A+'),
(12, 1, 'F105', 'Farmasetika Dasar', 4, 3.00, 'B', 'FKB 304 P', 'Praktikum Farmasetika Sediaan Farmasi', 1, 3.00, 'B'),
(13, 1, 'F304', 'Farmakologi I', 2, 3.00, 'B', 'FKB 312 T', 'Farmakologi I (Pharmacology I)', 2, 3.00, 'B'),
(14, 1, 'F204', 'Kimia Organik', 2, 3.00, 'B', 'FKK 205 P', 'Praktikum Kimia Organik (Organic Chemistry, Lab Work)', 1, 3.00, 'B'),
(15, 1, 'F401', 'Kimia Farmasi II', 2, 3.00, 'B', 'FKB 216 T', 'Kimia Farmasi Kuantitatif', 2, 3.00, 'B'),
(16, 1, 'F301', 'Biokimia', 2, 3.00, 'B', 'FKK 210 T', 'Biokimia (Biochemistry)', 2, 3.00, 'B'),
(17, 1, 'F209', 'Teknologi Sediaan Likuida dan Semi Solid', 3, 3.00, 'B', 'FKB 305 T', 'Teknologi Sediaan Farmasi', 3, 3.00, 'B'),
(18, 1, 'F402', 'Teknologi Sediaan Solid', 3, 3.00, 'B', 'FKB 305 P', 'Praktikum Teknologi Sediaan Farmasi', 1, 3.00, 'B'),
(19, 1, 'F305', 'Farmakognosi', 3, 3.00, 'B', 'FKB 330 T', 'Farmakognosi (Pharmacognosy)', 2, 3.00, 'B'),
(20, 1, 'F305', 'FarmakognosiFarmasi dan Akutansi', 3, 3.00, 'B', 'FKB 330 P', 'Praktikum Farmakognosi (Pharmacognosy, Lab Work)', 1, 3.00, 'B'),
(21, 1, 'F404', 'Manajemen II', 3, 3.00, 'B', 'FKB 328', 'Manajemen Farmasi (Pharmaceutical Management)', 2, 3.00, 'B'),
(22, 1, 'F403', 'Farmakologi II', 2, 3.00, 'B', 'FKB 313 T', 'Farmakologi II (Pharmacology II)', 2, 3.00, 'B'),
(23, 1, 'F405', 'Fitokimia', 2, 3.00, 'B', 'FKB 336 T', 'Analisis Fitokimia (Phytochemical Analysis)', 2, 3.00, 'B'),
(24, 1, 'F103', 'Kesehatan dan Keselamatan Kerja', 2, 3.00, 'B', 'FKK 233', 'Keselamatan dan Kesehatan Kerja (K3)', 2, 3.00, 'B'),
(25, 1, 'F504', 'Pemasaran Farmasi', 2, 3.00, 'B', 'FKB 321', 'Kewirausahaan (Entrepreneurship)', 2, 3.00, 'B'),
(26, 1, 'F202', 'Mikrobiologi dan Parasitologi', 2, 3.00, 'B', 'FKK 211 T', 'Mikrobilogi (Microbiology)', 2, 3.00, 'B'),
(27, 1, 'F208', 'Ilmu Perilaku dan Etika Profesi', 2, 4.00, 'A', 'FKB 322', 'Etika & Undang-Undang Farmasi (Ethics & Pharmaceutical Regulations)', 2, 4.00, 'A+'),
(28, 1, 'F107', 'Pendidikan dan Budaya Antikorupsi', 2, 3.00, 'B', 'FKK 218', 'Pendidikan Anti Korupsi (Anti-Corruption Education)', 2, 3.00, 'B'),
(29, 1, 'F306', 'Komunikasi Farmasi', 2, 3.00, 'B', 'FKB 334', 'Komunikasi Informasi dan Edukasi Promosi Kesehatan', 2, 3.00, 'B'),
(30, 1, 'F601', 'Praktik Kerja Lapangan', 9, 4.00, 'A', 'FBB 416', 'Praktek Kerja Lapangan (Field Work Practice)', 4, 4.00, 'A+'),
(31, 1, 'F503', 'Metodologi Penelitian', 2, 4.00, 'A', 'FKB 324', 'Metode Penelitian (Research Methodology)', 2, 4.00, 'A+'),
(32, 1, 'F506', 'Swamedikasi Obat Bebas', 2, 4.00, 'A', 'FKK 505', 'Perawatan Dan Pengobatan Sendiri (Swamedikasi)', 2, 4.00, 'A+'),
(33, 1, 'F310', 'Spesialite dan Terminologi', 2, 4.00, 'A', 'FKK 504', 'spesialit Obat', 2, 4.00, 'A+'),
(34, 1, 'F409', 'Biostatistika', 2, 4.00, 'A', 'FKK 413', 'Statistik Farmasi', 2, 4.00, 'A+'),
(35, 1, 'F303', 'Teknologi Sediaan Steril', 2, 3.00, 'B', 'FKB 419 T', 'Teknologi Sediaan Steril', 1, 3.00, 'B'),
(36, 1, 'F303', 'Teknologi Teknologi Sediaan Steril', 2, 3.00, 'B', 'FKB 419 P', 'Praktikum Teknologi Sediaan Steril', 1, 3.00, 'B'),
(37, 2, 'DFS102', 'Pendidikan Agama', 2, 4.00, 'A', 'FPK 101-1', 'Pendidikan Agama Islam I (Religious education I)', 2, 4.00, 'A+'),
(38, 2, 'DFS109', 'Bahasa Indonesia', 2, 4.00, 'A', 'FPK 106', 'Bahasa Indonesia (Indonesian)', 2, 4.00, 'A+'),
(39, 2, 'DFS102', 'Pancasila', 2, 4.00, 'A', 'FPK 104', 'Pendidikan Pancasila & Kewarganegaraan', 2, 4.00, 'A+'),
(40, 2, 'DFS108', 'Biologi Sel', 2, 3.00, 'B', 'FKK 202', 'Biologi Sel (Cell Biology)', 2, 3.00, 'B'),
(41, 2, 'DFS105', 'Praktikum Farmasetika I', 2, 4.00, 'A', 'FKB 303 P', 'Praktikum Farmasetika Dasar', 1, 4.00, 'A+'),
(42, 2, 'DFS205', 'Anatomi Fisiologi Manusia', 2, 3.00, 'B', 'FKK 209', 'Anatomi Fisiologi Manusia I (Human Anatomy and Physiology I)', 2, 3.00, 'B'),
(43, 2, 'DFS107', 'Pratikum Kimia Dasar Farmasi', 1, 4.00, 'A', 'FKK 214 P', 'Praktikum Kimia Dasar (Basic Chemistry, Lab Work)', 1, 4.00, 'A+'),
(44, 2, 'DFS208', 'Pratikum Farmasi Fisika', 1, 3.70, 'A-', 'FKB 302 P', 'Praktikum Fisika Farmasi (Pharmacy Physics, Lab Work)', 1, 3.50, 'B+'),
(45, 2, 'DFS309', 'Farmakologi Dasar', 2, 3.50, 'B+', 'FKB 311', 'Farmakologi Dasar', 2, 3.50, 'B+'),
(46, 2, 'DFS305', 'Pratikum Kimia Analisa Farmassi I (Kualitatif)', 2, 3.50, 'B+', 'FKB 215 P', 'Praktikum Kimia Farmasi Kualitatif', 1, 3.50, 'B+'),
(47, 2, 'DFS202', 'Pratikum Farmasetika II', 2, 4.00, 'A', 'FKB 304 P', 'Praktikum Farmasetika Sediaan Farmasi', 1, 4.00, 'A+'),
(48, 2, 'DFS405', 'Farmakologi I', 2, 3.50, 'B+', 'FKB 312 T', 'Farmakologi I (Pharmacology I)', 2, 3.50, 'B+'),
(49, 2, 'DFS406', 'Pratikum Farmakologi I', 1, 4.00, 'A', 'FKB 312 P', 'Praktikum Farmakologi I (Pharmacology, Lab Work I)', 1, 4.00, 'A+'),
(50, 2, 'DFS204', 'Pratikum Kimia Organik', 1, 4.00, 'A', 'FKK 205 P', 'Praktikum Kimia Organik (Organic Chemistry, Lab Work)', 1, 4.00, 'A+'),
(51, 2, 'DFS401', 'Praktikum Kimia Analisa Farmasi II (Kuantitatif)', 2, 3.75, 'A-', 'FKB 216 P', 'Praktikum Kimia Farmasi Kuantitatif', 1, 3.75, 'A'),
(52, 2, 'DFS303', 'Biokimia', 2, 2.75, 'B', 'FKK 210 T', 'Biokimia (Biochemistry)', 2, 2.50, 'C+'),
(53, 2, 'DFS302', 'Pratikum Teknologi Farmasi Sediaan Likuida&Semisolid', 2, 4.00, 'A', 'FKB 305 P', 'Praktikum Teknologi Sediaan Farmasi', 1, 4.00, 'A+'),
(54, 2, 'DFS307', 'Pratikum Farmakognosi', 2, 3.75, 'A-', 'FKB 330 P', 'Praktikum Farmakognosi (Pharmacognosy, Lab Work)', 1, 3.75, 'A'),
(55, 2, 'DFS407', 'Manajemen Farmasi dan Akutansi', 2, 4.00, 'A', 'FKB 328', 'Manajemen Farmasi (Pharmaceutical Management)', 2, 4.00, 'A+'),
(56, 2, 'DFS512', 'Farmakologi II', 2, 3.75, 'A', 'FKB 313 T', 'Farmakologi II (Pharmacology II)', 2, 3.75, 'A'),
(57, 2, 'DFS513', 'Pratikum Farmakologi II', 1, 4.00, 'A', 'FKB 313 P', 'Praktikum Farmakologi II (Pharmacology II, Lab Work)', 1, 4.00, 'A+'),
(58, 2, 'DFS410', 'Obat Tradisional', 2, 4.00, 'A', 'FKB 333', 'Teknologi Farmasi Herbal (Pharmaceutical Technology of Herbal Dosage Forms)', 2, 4.00, 'A+'),
(59, 2, 'DFS411', 'Fitokimia', 2, 4.00, 'A', 'FKB 336 T', 'Analisis Fitokimia (Phytochemical Analysis)', 2, 4.00, 'A+'),
(60, 2, 'DFS103', 'Kesehatan dan Keselamatan Kerja', 2, 3.70, 'A', 'FKK 233', 'Keselamatan dan Kesehatan Kerja (K3)', 2, 3.50, 'B+'),
(61, 2, 'DFS510', 'Pemasaran Farmasi', 2, 3.75, 'A-', 'FKB 321', 'Kewirausahaan (Entrepreneurship)', 2, 3.75, 'A'),
(62, 2, 'DFS210', 'Mikrobiologi Farmasi', 2, 3.00, 'B', 'FKK 211 T', 'Mikrobilogi (Microbiology)', 2, 3.00, 'B'),
(63, 2, 'DFS211', 'Pratikum Mikrobiologi Farmasi', 1, 3.30, 'B+', 'FKK 211 P', 'Praktikum Mikrobiologi (Microbiology, Lab Work)', 1, 3.00, 'B'),
(64, 2, 'DFS209', 'Perundang-undangan Kesehatan', 2, 3.30, 'B+', 'FKB 322', 'Etika & Undang-Undang Farmasi (Ethics & Pharmaceutical Regulations)', 2, 3.00, 'B'),
(65, 2, 'DFS112', 'Kewarganegaraan dan Pendidikan berbasis Antikorupsi  ', 2, 4.00, 'A', 'FKK 218', 'Pendidikan Anti Korupsi (Anti-Corruption Education)', 2, 4.00, 'A+'),
(66, 2, 'DFS308', 'Komunikasi Farmasi', 2, 4.00, 'A', 'FKB 334', 'Komunikasi Informasi dan Edukasi Promosi Kesehatan', 2, 4.00, 'A+'),
(67, 2, 'DFS601', 'Praktik Kerja Lapangan', 6, 4.00, 'A', 'FBB 416', 'Praktek Kerja Lapangan (Field Work Practice)', 4, 4.00, 'A+'),
(68, 2, 'DFS507', 'Metodologi Penelitian', 2, 4.00, 'A', 'FKB 324', 'Metode Penelitian (Research Methodology)', 2, 4.00, 'A+'),
(69, 2, 'DFS503', 'Pengantar Farmasi Klinik', 1, 4.00, 'A', 'FBB 412 P', 'Praktikum Farmasi Klinik (Clinical Pharmacy, Work Lab)', 1, 4.00, 'A+'),
(70, 2, 'DFS506', 'Biostatistika', 2, 3.75, 'A+', 'FKK 413', 'Statistik Farmasi', 2, 3.75, 'A'),
(71, 2, 'DFS508', 'Teknologi Farmasi Sediaan Steril', 1, 3.75, 'A-', 'FKB 419 T', 'Teknologi Sediaan Steril', 1, 3.75, 'A'),
(72, 2, 'DFS509', 'Pratikum Teknologi Farmasi Sediaan Steril', 2, 3.75, 'A-', 'FKB 419 P', 'Praktikum Teknologi Sediaan Steril', 1, 3.75, 'A');

-- --------------------------------------------------------

--
-- Table structure for table `konversi_header`
--

CREATE TABLE `konversi_header` (
  `id` int(11) NOT NULL,
  `id_mahasiswa` int(11) DEFAULT NULL,
  `periode_tahun` varchar(9) NOT NULL,
  `nama_asal` varchar(100) DEFAULT NULL,
  `nim_asal` varchar(50) DEFAULT NULL,
  `pt_asal` varchar(100) DEFAULT NULL,
  `prodi_asal` varchar(100) DEFAULT NULL,
  `nama_tujuan` varchar(100) DEFAULT NULL,
  `nim_tujuan` varchar(50) DEFAULT NULL,
  `pt_tujuan` varchar(100) DEFAULT NULL,
  `prodi_tujuan` varchar(100) DEFAULT NULL,
  `berkas_silabus` varchar(255) DEFAULT NULL,
  `berkas_transkrip` varchar(255) DEFAULT NULL,
  `status` enum('draft','menunggu','diverifikasi','ditolak') DEFAULT 'draft',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `konversi_header`
--

INSERT INTO `konversi_header` (`id`, `id_mahasiswa`, `periode_tahun`, `nama_asal`, `nim_asal`, `pt_asal`, `prodi_asal`, `nama_tujuan`, `nim_tujuan`, `pt_tujuan`, `prodi_tujuan`, `berkas_silabus`, `berkas_transkrip`, `status`, `created_at`) VALUES
(1, 5, '2024/2025', 'Aisyah Elsyifa Husna', 'PO7139122028', 'Politeknik Kesehatan Kementerian Kesehatan Palembang', 'D-III Farmasi', 'Aisyah Elsyifa Husna', '482012512036P', 'STIK SITI KHADIJAH', 'Farmasi', NULL, NULL, 'diverifikasi', '2026-05-17 05:46:06'),
(2, 6, '2024/2025', 'Alisha Mawaddah', '2101020002', 'Sekolah Tinggi Ilmu Farmasi Bhakti Pertiwi', 'D-III Farmasi', 'Alisha Mawaddah', '482012512015P', 'STIK SITI KHADIJAH', 'Farmasi', NULL, NULL, 'menunggu', '2026-05-26 02:55:55');

-- --------------------------------------------------------

--
-- Table structure for table `kurikulum`
--

CREATE TABLE `kurikulum` (
  `id` int(11) NOT NULL,
  `id_prodi` int(11) DEFAULT NULL,
  `periode_tahun` varchar(9) NOT NULL,
  `kode_mk` varchar(20) NOT NULL,
  `nama_mk` varchar(100) NOT NULL,
  `sks` int(11) NOT NULL,
  `semester` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kurikulum`
--

INSERT INTO `kurikulum` (`id`, `id_prodi`, `periode_tahun`, `kode_mk`, `nama_mk`, `sks`, `semester`) VALUES
(1, 1, '2024/2025', 'FPK 101-1', 'Pendidikan Agama Islam I (Religious education I)', 2, 'I'),
(2, 1, '2024/2025', 'FPK 106', 'Bahasa Indonesia (Indonesian)', 2, 'I'),
(3, 1, '2024/2025', 'FPK 104', 'Pendidikan Pancasila & Kewarganegaraan', 2, 'I'),
(4, 1, '2024/2025', 'FPK 105-1', 'Bahasa Arab (Arabic)', 2, 'I'),
(5, 1, '2024/2025', 'FKK 202', 'Biologi Sel (Cell Biology)', 2, 'I'),
(6, 1, '2024/2025', 'FPK 102', 'Bahasa Inggris (English)', 2, 'I'),
(7, 1, '2024/2025', 'FKB 303 T', 'Farmasetika Dasar', 2, 'I'),
(8, 1, '2024/2025', 'FKB 303 P', 'Praktikum Farmasetika Dasar', 1, 'I'),
(9, 1, '2024/2025', 'FKK 209', 'Anatomi Fisiologi Manusia I (Human Anatomy and Physiology I)', 2, 'I'),
(10, 1, '2024/2025', 'FKK 214 T', 'Kimia Dasar (Basic Chemistry)', 2, 'I'),
(11, 1, '2024/2025', 'FKK 214 P', 'Praktikum Kimia Dasar (Basic Chemistry, Lab Work)', 1, 'I'),
(12, 1, '2024/2025', 'FPK 101-2', 'Pendidikan Agama Islam II (Religious education II)', 2, 'II'),
(13, 1, '2024/2025', 'FKB 302 T', 'Fisika Farmasi (Pharmacy Physics)', 3, 'II'),
(14, 1, '2024/2025', 'FKB 302 P', 'Praktikum Fisika Farmasi (Pharmacy Physics, Lab Work)', 1, 'II'),
(15, 1, '2024/2025', 'FKB 311', 'Farmakologi Dasar', 2, 'II'),
(16, 1, '2024/2025', 'FKK 208 T', 'Botani Farmasi (Morfologi Anatomi Fisiologi Tumbuhan) (Pharmaceutical Botany (Anatomy Morfology of P', 2, 'II'),
(17, 1, '2024/2025', 'FKK 208 P', 'Praktikum Botani Farmasi (Morfologi Anatomi Fisiologi Tumbuhan) (Pharmaceutical Botany (Anatomy Morf', 1, 'II'),
(18, 1, '2024/2025', 'FKB 215 T', 'Kimia Farmasi Kualitatif', 2, 'II'),
(19, 1, '2024/2025', 'FKB 215 P', 'Praktikum Kimia Farmasi Kualitatif', 1, 'II'),
(20, 1, '2024/2025', 'FKB 304 T', 'Farmasetika Sediaan Farmasi', 2, 'II'),
(21, 1, '2024/2025', 'FKB 304 P', 'Praktikum Farmasetika Sediaan Farmasi', 1, 'II'),
(22, 1, '2024/2025', 'FKK 203 T', 'Biologi Molekuler', 2, 'II'),
(23, 1, '2024/2025', 'FKK 203 P', 'Praktikum Biologi Molekuler', 1, 'II'),
(24, 1, '2024/2025', 'FKB 312 T', 'Farmakologi I (Pharmacology I)', 2, 'III'),
(25, 1, '2024/2025', 'FKB 312 P', 'Praktikum Farmakologi I (Pharmacology, Lab Work I)', 1, 'III'),
(26, 1, '2024/2025', 'FKK 205 T', 'Kimia Organik (Organic Chemistry)', 3, 'III'),
(27, 1, '2024/2025', 'FKK 205 P', 'Praktikum Kimia Organik (Organic Chemistry, Lab Work)', 1, 'III'),
(28, 1, '2024/2025', 'FKB 216 T', 'Kimia Farmasi Kuantitatif', 2, 'III'),
(29, 1, '2024/2025', 'FKB 216 P', 'Praktikum Kimia Farmasi Kuantitatif', 1, 'III'),
(30, 1, '2024/2025', 'FKK 210 T', 'Biokimia (Biochemistry)', 2, 'III'),
(31, 1, '2024/2025', 'FKK 210 P', 'Praktikum Biokimia (Biochemistry, Lab Work)', 1, 'III'),
(32, 1, '2024/2025', 'FKB 305 T', 'Teknologi Sediaan Farmasi', 3, 'III'),
(33, 1, '2024/2025', 'FKB 305 P', 'Praktikum Teknologi Sediaan Farmasi', 1, 'III'),
(34, 1, '2024/2025', 'FKB 329', 'Farmakokinetika & Biofarmasetika (Pharmacokinetics & Biopharmaceutical)', 2, 'III'),
(35, 1, '2024/2025', 'FKK 213', 'Patofisiologi (Pathophysiology)', 2, 'III'),
(36, 1, '2024/2025', 'FKB 330 T', 'Farmakognosi (Pharmacognosy)', 2, 'III'),
(37, 1, '2024/2025', 'FKB 330 P', 'Praktikum Farmakognosi (Pharmacognosy, Lab Work)', 1, 'III'),
(38, 1, '2024/2025', 'FKB 306 T', 'Kosmetologi', 2, 'IV'),
(39, 1, '2024/2025', 'FKB 328', 'Manajemen Farmasi (Pharmaceutical Management)', 2, 'IV'),
(40, 1, '2024/2025', 'FKK 212', 'Imunologi (Immunology)', 2, 'IV'),
(41, 1, '2024/2025', 'FKB 313 T', 'Farmakologi II (Pharmacology II)', 2, 'IV'),
(42, 1, '2024/2025', 'FKB 313 P', 'Praktikum Farmakologi II (Pharmacology II, Lab Work)', 1, 'IV'),
(43, 1, '2024/2025', 'FKB 331 T', 'Farmasi Bahan Alam (Natural Pharmaceutical)', 2, 'IV'),
(44, 1, '2024/2025', 'FKB 331 P', 'Praktikum Farmasi Bahan Alam (Natural Pharmaceutical Practicum)', 1, 'IV'),
(45, 1, '2024/2025', 'FBB 410', 'Farmasi Komunitas (Pharmacy Community)', 2, 'IV'),
(46, 1, '2024/2025', 'FKB 319', 'Toksikologi (Toxicology)', 2, 'IV'),
(47, 1, '2024/2025', 'FKB 308 T', 'Farmasi Industri', 2, 'V'),
(48, 1, '2024/2025', 'FKB 308 P', 'Praktikum Analisis Makanan dan Kosmetik (Food and Cosmetic Analysis, Lab Work)', 1, 'IV'),
(49, 1, '2024/2025', 'FKB 309', 'Kimia Medisinal (Medicinal Chemistry)', 2, 'IV'),
(50, 1, '2024/2025', 'FKB 333', 'Teknologi Farmasi Herbal (Pharmaceutical Technology of Herbal Dosage Forms)', 2, 'IV'),
(51, 1, '2024/2025', 'FKB 336 T', 'Analisis Fitokimia (Phytochemical Analysis)', 2, 'V'),
(52, 1, '2024/2025', 'FKB 336 P', 'Praktikum Analisis Fitokimia (Phytochemical Analysis Practium)', 1, 'V'),
(53, 1, '2024/2025', 'FKB 307 T', 'Pengantar Produk Halal', 2, 'V'),
(55, 1, '2024/2025', 'FKK 217 T', 'Thibunnabawi (Thibunnabawi)', 2, 'V'),
(56, 1, '2024/2025', 'FKK 217 P', 'Praktikum Thibunnabawi (Thibunnabawi Practium)', 1, 'V'),
(57, 1, '2024/2025', 'FKK 233', 'Keselamatan dan Kesehatan Kerja (K3)', 2, 'V'),
(58, 1, '2024/2025', 'FKB 321', 'Kewirausahaan (Entrepreneurship)', 2, 'V'),
(59, 1, '2024/2025', 'FKB 332', 'Farmakoepidemiologi & Farmakoekonomi (Pharmacoepidemiology & Pharmacoeconomics)', 2, 'V'),
(60, 1, '2024/2025', 'FKK 211 T', 'Mikrobilogi (Microbiology)', 2, 'V'),
(61, 1, '2024/2025', 'FKK 211 P', 'Praktikum Mikrobiologi (Microbiology, Lab Work)', 1, 'V'),
(62, 1, '2024/2025', 'FKB 315', 'Farmakoterapi Penyakit Tidak Menular', 3, 'V'),
(63, 1, '2024/2025', 'FKB 323 T', 'Metode Pemisahan Obat (Drug Separation Methodology)', 2, 'VI'),
(64, 1, '2024/2025', 'FKB 323 P', 'Praktikum Metode Pemisahan Obat (Drug Separation Methodology, Lab Work)', 1, 'VI'),
(65, 1, '2024/2025', 'FKB 322', 'Etika & Undang-Undang Farmasi (Ethics & Pharmaceutical Regulations)', 2, 'VI'),
(66, 1, '2024/2025', 'FKB 340', 'Farmakoterapi Penyakit Menular', 2, 'VI'),
(67, 1, '2024/2025', 'FKK 218', 'Pendidikan Anti Korupsi (Anti-Corruption Education)', 2, 'VI'),
(68, 1, '2024/2025', 'FKB 334', 'Komunikasi Informasi dan Edukasi Promosi Kesehatan', 2, 'VI'),
(69, 1, '2024/2025', 'FBB 416', 'Praktek Kerja Lapangan (Field Work Practice)', 4, 'VI'),
(70, 1, '2024/2025', 'FKB 324', 'Metode Penelitian (Research Methodology)', 2, 'VI'),
(71, 1, '2024/2025', 'FBB 412 T', 'Farmasi Klinik (Clinical Pharmacy)', 2, 'VI'),
(72, 1, '2024/2025', 'FBB 412 P', 'Praktikum Farmasi Klinik (Clinical Pharmacy, Work Lab)', 1, 'VI'),
(73, 1, '2024/2025', 'FKB 335', 'Fitoterapi (Phytotherapy)', 2, 'VI'),
(75, 1, '2024/2025', 'FKK 505', 'Perawatan Dan Pengobatan Sendiri (Swamedikasi)', 2, 'PILIHAN'),
(76, 1, '2024/2025', 'FKB 502', 'Manajemen Supply Obat', 2, 'PILIHAN'),
(77, 1, '2024/2025', 'FKB 515', 'Nutraceutical', 2, 'PILIHAN'),
(78, 1, '2024/2025', 'FKK 504', 'spesialit Obat', 2, 'PILIHAN'),
(79, 1, '2024/2025', 'FKK 413', 'Statistik Farmasi', 2, 'PILIHAN'),
(80, 1, '2024/2025', 'FKB 419 T', 'Teknologi Sediaan Steril', 1, 'PILIHAN'),
(81, 1, '2024/2025', 'FKB 419 P', 'Praktikum Teknologi Sediaan Steril', 1, 'PILIHAN'),
(82, 1, '2024/2025', 'FBB 414', 'Pra Skripsi (PreThesis)', 3, 'VII'),
(83, 1, '2024/2025', 'FBB 415', 'Skripsi (Thesis)', 3, 'VIII'),
(87, 2, '2022/2023', 'IKP 201', 'Bahasa Indonesia (Indonesian)', 2, 'I'),
(88, 2, '2022/2023', 'IKU 101', 'Pemenuhan Kebutuhan Dasar Manusia (Fulfillment of Basic Human Needs)', 4, 'I'),
(89, 2, '2022/2023', 'IKU 102', 'Konsep Dasar Keperawatan I (Basic Nursing Concepts I)', 3, 'I'),
(90, 2, '2022/2023', 'IKU 103', 'Proses Keperawatan dan Berfikir Kritis (Nursing Process and Critical Thinking)', 3, 'I'),
(91, 2, '2022/2023', 'IKU 104', 'Ilmu Biomedik Dasar (Basic Biomedical Science)', 4, 'I'),
(92, 2, '2022/2023', 'IKU 105', 'Falsafah dan Teori Keperawatan (Nursing Philosophy and Theory)', 3, 'I'),
(93, 2, '2022/2023', 'IKL 301', 'Bahasa Arab I (Arabic I)', 2, 'I'),
(94, 2, '2022/2023', 'IKP 202', 'Pendidikan Anti Korupsi (Anti-Corruption Education)', 2, 'I'),
(95, 2, '2022/2023', 'IKU 106', 'Komunikasi Dasar Keperawatan (Basic Communication in Nursing)', 2, 'II'),
(96, 2, '2022/2023', 'IKP 203', 'Pancasila (Pancasila)', 2, 'II'),
(97, 2, '2022/2023', 'IKU 107', 'Keterampilan Dasar Keperawatan (Basic Nursing Skills)', 4, 'II'),
(98, 2, '2022/2023', 'IKP 204', 'Agama Islam (Islamic Religious Education )', 2, 'II'),
(99, 2, '2022/2023', 'IKU 108', 'Ilmu Dasar Keperawatan (Basic Nursing Science)', 3, 'II'),
(100, 2, '2022/2023', 'IKU 109', 'Farmakologi Keperawatan (Nursing Pharmacology)', 3, 'II'),
(101, 2, '2022/2023', 'IKP 205', 'Promosi Kesehatan dan Pendidikan Kesehatan I (Health Promotion and Health Education I)', 3, 'II'),
(102, 2, '2022/2023', 'IKP 206', 'Entrepreneurship (Entrepreneurship)', 2, 'II'),
(103, 2, '2022/2023', 'IKP 207', 'Sistem Informasi Keperawatan (Nursing Information System)', 2, 'III'),
(104, 2, '2022/2023', 'IKP 208', 'Kewarganegaraan (Civic Education)', 2, 'III'),
(105, 2, '2022/2023', 'IKU 110', 'Keperawatan Dewasa (Sistem Kardiovaskuler, Respiratori, Hematologi) Adult Nursing (Cardiovascular, R', 4, 'III'),
(106, 2, '2022/2023', 'IKU 111', 'Keperawatan Maternitas (Maternity Nursing)', 4, 'III'),
(107, 2, '2022/2023', 'IKU 112', 'Komunikasi Terapeutik Keperawatan (Therapeutic Communication in Nursing)', 3, 'III'),
(108, 2, '2022/2023', 'IKU 113', 'Psikososial dan Budaya Keperawatan (Psychosocial and Cultural Aspects in Nursing)', 2, 'III'),
(109, 2, '2022/2023', 'IKP 209', 'Bahasa Inggris Keperawatan (English for Nursing Purposes)', 2, 'III'),
(110, 2, '2022/2023', 'IKL 302', 'Bahasa Arab II (Arabic II)', 2, 'III'),
(111, 2, '2022/2023', 'IKU 114', 'Keperawatan Kesehatan Reproduksi (Reproductive Health Nursing)', 2, 'IV'),
(112, 2, '2022/2023', 'IKU 115', 'Keperawatan Dewasa (Sistem Endokrin, Imunologi Pencernaan, Perkemihan, Reproduksi Pria) Adult Nursin', 4, 'IV'),
(113, 2, '2022/2023', 'IKU 116', 'Keperawatan Anak Sehat dan Sakit Akut (Pediatric Nursing: Health and Acute Illness)', 4, 'IV'),
(114, 2, '2022/2023', 'IKU 117', 'Keperawatan Kesehatan Jiwa dan Psikososial (Mental Health and Psychosocial Nursing)', 3, 'IV'),
(115, 2, '2022/2023', 'IKU 118', 'Keselamatan Pasien dan Keselamatan Kesehatan Kerja (Patient Safety and Occupational Health and Safet', 3, 'IV'),
(116, 2, '2022/2023', 'IKU 125', 'Praklinik Keperawatan (Nursing Pre-clinical)', 2, 'IV'),
(117, 2, '2022/2023', 'IKU 119', 'Keperawatan Paliatif (Palliative Care Nursing)', 2, 'IV'),
(118, 2, '2022/2023', 'IKP 214', 'Promosi Kesehatan dan Pendidikan Kesehatan II (Health Promotion and Health Education II) (Health Pro', 3, 'IV'),
(119, 2, '2022/2023', 'IKU 121', 'Keperawatan Dewasa (Sistem Muskuloskeletal, Integumen, Persepsi Sensori, Persarafan)', 4, 'V'),
(120, 2, '2022/2023', 'IKP 210', 'Metodologi Penelitian (Research Methodology)', 4, 'V'),
(121, 2, '2022/2023', 'IKU 122', 'Keperawatan Anak Sakit Kronis dan Terminal (Pediatric Nursing: Chronic and Terminal Illness)', 2, 'V'),
(122, 2, '2022/2023', 'IKU 123', 'Keperawatan Psikiatri (Psychiatric Nursing)', 3, 'V'),
(123, 2, '2022/2023', 'IKU 124', 'Manajemen Keperawatan (Nursing Management)', 4, 'V'),
(124, 2, '2022/2023', 'IKU 120', 'Konsep Keperawatan Komunitas (Concepts of Community Health Nursing)', 3, 'V'),
(125, 2, '2022/2023', 'IKU 126', 'Praktik Klinik Keperawatan Dewasa (Adult Nursing Clinical Practice)', 3, 'V'),
(126, 2, '2022/2023', 'IKU 127', 'Keperawatan Agregat Komunitas (Community Aggregate Nursing)', 3, 'VI'),
(127, 2, '2022/2023', 'IKP 211', 'Biostatistik (Biostatistics)', 3, 'VI'),
(128, 2, '2022/2023', 'IKU 128', 'Keperawatan Gawat Darurat (Emergency Nursing)', 4, 'VI'),
(129, 2, '2022/2023', 'IKU 129', 'Keperawatan Keluarga (Family Nursing)', 4, 'VI'),
(130, 2, '2022/2023', 'IKU 215', 'Keperawatan Islami I(Islamic Nursing I)', 2, 'VI'),
(131, 2, '2022/2023', 'IKU 130', 'Praktik Keperawatan Jiwa (Psychiatric Nursing Practice)', 2, 'VI'),
(132, 2, '2022/2023', 'IKU 131', 'Praktik Keperawatan Komunitas (Community Nursing Practice)', 2, 'VI'),
(133, 2, '2022/2023', 'IKU 132', 'Keperawatan Kritis (Critical Care Nursing)', 3, 'VII'),
(134, 2, '2022/2023', 'IKU 133', 'Keperawatan Gerontik (Gerontological Nursing)', 4, 'VII'),
(135, 2, '2022/2023', 'IKU 134', 'Keperawatan Bencana (Disaster Nursing)', 2, 'VII'),
(136, 2, '2022/2023', 'IKP 216', 'Keperawatan Islami II(Islamic Nursing II)', 2, 'VII'),
(137, 2, '2022/2023', 'IKP 213', 'Skripsi (Thesis / Final Project)', 4, 'VII');

-- --------------------------------------------------------

--
-- Table structure for table `program_studi`
--

CREATE TABLE `program_studi` (
  `id` int(11) NOT NULL,
  `kode_prodi` varchar(20) NOT NULL,
  `nama_prodi` varchar(100) NOT NULL,
  `jenjang` enum('D3','S1','S2') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_studi`
--

INSERT INTO `program_studi` (`id`, `kode_prodi`, `nama_prodi`, `jenjang`) VALUES
(1, '48201', 'Farmasi', 'S1'),
(2, '14201', 'Ilmu Keperawatan', 'S1');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','rpl','mahasiswa') NOT NULL,
  `nama_lengkap` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `nama_lengkap`, `created_at`) VALUES
(1, 'admin', '$2y$10$HBGcoW8SWCHN27gbB/8p5.W0aYZo08JTzPTj9.55UPuTSI9Ikf95m', 'admin', 'STIK SITI KHADIJAH', '2026-05-05 01:35:51'),
(2, 'rpl', '$2y$10$snPh2tsqcUn1KbTaMtv44eijEYDeRoCyeYBdL2BQO5YlndMYi8sfm', 'rpl', 'RPL-STIK-SITI-KHADIJAH', '2026-05-05 01:36:54'),
(3, 'laila', '$2y$10$rZqi1I32EBoLO1dfot3eseXcwS9S28ybJPZ82wG1Bsk8CRCq4JaUq', 'rpl', 'Ns. Lela Aini, S.Kep., M.Bmd ', '2026-05-05 02:48:58'),
(5, '482012512036P', '$2y$10$p41E5/.MBJrJwsCv9g7ayO7x2HeR0NGntyUe5mXjT8wc9t1dmwmBm', 'mahasiswa', 'Aisyah Elsyifa Husna', '2026-05-05 03:08:20'),
(6, '482012512015P', '$2y$10$MAaW2NtAuZlUCOykmHOHWeHVIzs/174gv18l8XhMQPH0Dv0CfSeoC', 'mahasiswa', 'Alisha Mawaddah', '2026-05-05 03:09:33'),
(7, '482012512013P', '$2y$10$THjZCq3/22OXx6dOJFof7Od2mAR90hgbZyGNMaUtio9jo6D5EZNg6', 'mahasiswa', 'Alya Gita Octa Repsi', '2026-05-12 06:52:41'),
(8, '482012512001P', '$2y$10$E5vkKATGCs8k7UdGvUqMBe8NTn6NosoH.gO8qSV2p5VsX8bbTLf.O', 'mahasiswa', 'M. AL FASIH', '2026-05-12 06:52:41'),
(9, '482012512002P', '$2y$10$iFd74RAwqWj7Jlsf01v3zefptyvSjViEf/P2OfftGTEgOnhAvZmyO', 'mahasiswa', 'DINI FITRIANI', '2026-05-12 06:52:41'),
(10, '482012512003P', '$2y$10$.hRtcAtWeEAeHf107QZW2ucs494SgVpAbcEEVpl0zozluz2aIKmJi', 'mahasiswa', 'FITRI WULANDARI', '2026-05-12 06:52:41'),
(11, '482012512004P', '$2y$10$Be77CJOS1VplctVB94vVOO3MuxrmVPUNsML/6Ll0loaZzjmJs.GRq', 'mahasiswa', 'YUNI TRI UTAMI', '2026-05-12 06:52:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bahan_kajian`
--
ALTER TABLE `bahan_kajian`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_prodi` (`id_prodi`),
  ADD KEY `periode_tahun` (`periode_tahun`);

--
-- Indexes for table `konversi_detail`
--
ALTER TABLE `konversi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_konversi` (`id_konversi`);

--
-- Indexes for table `konversi_header`
--
ALTER TABLE `konversi_header`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_mahasiswa` (`id_mahasiswa`);

--
-- Indexes for table `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_kurikulum` (`id_prodi`,`periode_tahun`,`kode_mk`);

--
-- Indexes for table `program_studi`
--
ALTER TABLE `program_studi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_prodi` (`kode_prodi`);

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
-- AUTO_INCREMENT for table `bahan_kajian`
--
ALTER TABLE `bahan_kajian`
  MODIFY `id` int(2) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `konversi_detail`
--
ALTER TABLE `konversi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `konversi_header`
--
ALTER TABLE `konversi_header`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kurikulum`
--
ALTER TABLE `kurikulum`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

--
-- AUTO_INCREMENT for table `program_studi`
--
ALTER TABLE `program_studi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `konversi_detail`
--
ALTER TABLE `konversi_detail`
  ADD CONSTRAINT `konversi_detail_ibfk_1` FOREIGN KEY (`id_konversi`) REFERENCES `konversi_header` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `konversi_header`
--
ALTER TABLE `konversi_header`
  ADD CONSTRAINT `konversi_header_ibfk_1` FOREIGN KEY (`id_mahasiswa`) REFERENCES `users` (`id`);

--
-- Constraints for table `kurikulum`
--
ALTER TABLE `kurikulum`
  ADD CONSTRAINT `kurikulum_ibfk_1` FOREIGN KEY (`id_prodi`) REFERENCES `program_studi` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
