-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 06:36 AM
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
-- Database: `broscafe_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_number`, `customer_id`, `employee_id`, `total_amount`, `payment_method`, `status`, `order_type`, `created_at`, `updated_at`) VALUES
('ORD-20251112-E4BAFF', NULL, 1, 300.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:05', '2025-11-12 09:24:05'),
('ORD-20251112-DA2C4E', NULL, 1, 390.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:38', '2025-11-12 09:24:38'),
('ORD-20251112-8D570D', NULL, 1, 340.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:52', '2025-11-12 09:24:52'),
('ORD-20251112-6253B8', NULL, 1, 200.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:59', '2025-11-12 09:24:59'),
('ORD-20251112-09E8F3', NULL, 1, 550.00, 'cash', 'pending', 'takeout', '2025-11-12 09:25:49', '2025-11-12 09:25:49'),
('ORD-20251112-7C8D5D', NULL, 1, 290.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:39:07', '2025-11-12 09:39:07'),
('ORD-20251112-70218A', NULL, 1, 100.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:45:43', '2025-11-12 09:45:43'),
('ORD-20251112-8C6675', NULL, 1, 550.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:46:01', '2025-11-12 09:46:01'),
('ORD-20251112-BBC41B', NULL, 1, 1220.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:46:30', '2025-11-12 09:46:30'),
('ORD-20251113-B33D36', NULL, 1, 370.00, 'cash', 'pending', 'dine-in', '2025-11-13 07:53:39', '2025-11-13 07:53:39'),
('ORD-20251116-94FFD2', NULL, 1, 150.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:30:37', '2025-11-16 10:47:13'),
('ORD-20251116-D9BB7B', NULL, 1, 180.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:34:24', '2025-11-16 10:47:08'),
('ORD-20251116-E2BCCF', NULL, 1, 3150.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:43:55', '2025-11-16 10:46:38'),
('ORD-20251116-5B62BD', NULL, 1, 100.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:52:32', '2025-11-16 11:01:56'),
('ORD-20251116-237AC7', NULL, 1, 100.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:52:40', '2025-11-16 11:01:44'),
('ORD-20251116-60A095', NULL, 1, 3000.00, 'cash', 'completed', 'dine-in', '2025-11-16 11:07:10', '2025-11-16 11:07:27'),
('ORD-20251116-E28F83', NULL, 1, 4350.00, 'cash', 'pending', 'dine-in', '2025-11-16 11:08:35', '2025-11-16 11:08:35'),
('ORD-20251116-E681F0', NULL, 1, 6300.00, 'cash', 'pending', 'dine-in', '2025-11-16 11:09:00', '2025-11-16 11:09:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
