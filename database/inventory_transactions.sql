-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 06:35 AM
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
-- Table structure for table `inventory_transactions`
--

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`product_id`, `transaction_type`, `quantity`, `user_id`, `notes`, `created_at`) VALUES
(10, 'sale', -1, 1, NULL, '2025-11-12 09:24:38'),
(6, 'sale', -1, 1, NULL, '2025-11-12 09:24:38'),
(1, 'sale', -1, 1, NULL, '2025-11-12 09:24:38'),
(9, 'sale', -1, 1, NULL, '2025-11-12 09:24:52'),
(3, 'sale', -2, 1, NULL, '2025-11-12 09:24:52'),
(9, 'sale', -2, 1, NULL, '2025-11-12 09:24:59'),
(9, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(4, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(8, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(2, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(10, 'sale', -1, 1, NULL, '2025-11-12 09:39:07'),
(3, 'sale', -1, 1, NULL, '2025-11-12 09:39:07'),
(9, 'sale', -1, 1, NULL, '2025-11-12 09:45:43'),
(5, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(1, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(7, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(8, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(9, 'sale', -1, 1, NULL, '2025-11-12 09:46:30'),
(7, 'sale', -8, 1, NULL, '2025-11-12 09:46:30'),
(6, 'sale', -1, 1, NULL, '2025-11-13 07:53:39'),
(4, 'sale', -1, 1, NULL, '2025-11-13 07:53:39'),
(3, 'sale', -1, 1, NULL, '2025-11-13 07:53:40'),
(4, 'sale', -1, 1, NULL, '2025-11-16 10:30:37'),
(8, 'sale', -1, 1, NULL, '2025-11-16 10:34:24'),
(10, 'sale', -21, 1, NULL, '2025-11-16 10:43:55'),
(5, 'sale', -1, 1, NULL, '2025-11-16 10:52:32'),
(9, 'sale', -1, 1, NULL, '2025-11-16 10:52:40'),
(4, 'sale', -20, 1, NULL, '2025-11-16 11:07:10'),
(10, 'sale', -29, 1, NULL, '2025-11-16 11:08:35'),
(10, 'sale', -42, 1, NULL, '2025-11-16 11:09:00'),
(10, 'restock', 30, 1, '', '2025-11-16 11:10:09'),
(10, 'restock', 10, 1, '', '2025-11-16 11:10:35');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
