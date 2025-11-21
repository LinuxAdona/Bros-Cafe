-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2025 at 06:37 AM
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
-- Table structure for table `sales_summary`
--


--
-- Dumping data for table `sales_summary`
--

INSERT INTO `sales_summary` (`date`, `total_orders`, `total_revenue`, `total_items_sold`, `created_at`) VALUES
('2025-11-12', 9, 3940.00, 30, '2025-11-12 09:24:05'),
('2025-11-13', 1, 370.00, 3, '2025-11-13 07:53:40'),
('2025-11-16', 8, 17330.00, 116, '2025-11-16 10:30:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sales_summary`
--