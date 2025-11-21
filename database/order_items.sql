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
-- Table structure for table `order_items`
--


--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_id`, `product_id`, `size`, `quantity`, `price`, `subtotal`) VALUES
(1, 4, 'sedici', 2, 150.00, 300.00),
(2, 10, 'dodici', 1, 150.00, 150.00),
(2, 6, 'sedici', 1, 120.00, 120.00),
(2, 1, 'dodici', 1, 120.00, 120.00),
(3, 9, 'dodici', 1, 100.00, 100.00),
(3, 3, 'dodici', 2, 120.00, 240.00),
(4, 9, 'dodici', 2, 100.00, 200.00),
(5, 9, 'dodici', 1, 100.00, 100.00),
(5, 4, 'dodici', 1, 130.00, 130.00),
(5, 8, 'dodici', 1, 180.00, 180.00),
(5, 2, 'sedici', 1, 140.00, 140.00),
(6, 10, 'dodici', 1, 150.00, 150.00),
(6, 3, 'sedici', 1, 140.00, 140.00),
(7, 9, 'dodici', 1, 100.00, 100.00),
(8, 5, 'dodici', 1, 80.00, 80.00),
(8, 1, 'sedici', 1, 150.00, 150.00),
(8, 7, 'dodici', 1, 140.00, 140.00),
(8, 8, 'dodici', 1, 180.00, 180.00),
(9, 9, 'dodici', 1, 100.00, 100.00),
(9, 7, 'dodici', 8, 140.00, 1120.00),
(10, 6, 'sedici', 1, 120.00, 120.00),
(10, 4, 'dodici', 1, 130.00, 130.00),
(10, 3, 'dodici', 1, 120.00, 120.00),
(11, 4, 'sedici', 1, 150.00, 150.00),
(12, 8, 'dodici', 1, 180.00, 180.00),
(13, 10, 'dodici', 21, 150.00, 3150.00),
(14, 5, 'sedici', 1, 100.00, 100.00),
(15, 9, 'dodici', 1, 100.00, 100.00),
(16, 4, 'sedici', 20, 150.00, 3000.00),
(17, 10, 'dodici', 29, 150.00, 4350.00),
(18, 10, 'dodici', 42, 150.00, 6300.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_items`