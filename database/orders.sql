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

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','gcash','other') DEFAULT 'cash',
  `status` enum('pending','preparing','ready','completed','cancelled') DEFAULT 'pending',
  `order_type` enum('dine-in','takeout','delivery') DEFAULT 'dine-in',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `employee_id`, `total_amount`, `payment_method`, `status`, `order_type`, `created_at`, `updated_at`) VALUES
(1, 'ORD-20251112-E4BAFF', NULL, 1, 300.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:05', '2025-11-12 09:24:05'),
(2, 'ORD-20251112-DA2C4E', NULL, 1, 390.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:38', '2025-11-12 09:24:38'),
(3, 'ORD-20251112-8D570D', NULL, 1, 340.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:52', '2025-11-12 09:24:52'),
(4, 'ORD-20251112-6253B8', NULL, 1, 200.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:24:59', '2025-11-12 09:24:59'),
(5, 'ORD-20251112-09E8F3', NULL, 1, 550.00, 'cash', 'pending', 'takeout', '2025-11-12 09:25:49', '2025-11-12 09:25:49'),
(6, 'ORD-20251112-7C8D5D', NULL, 1, 290.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:39:07', '2025-11-12 09:39:07'),
(7, 'ORD-20251112-70218A', NULL, 1, 100.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:45:43', '2025-11-12 09:45:43'),
(8, 'ORD-20251112-8C6675', NULL, 1, 550.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:46:01', '2025-11-12 09:46:01'),
(9, 'ORD-20251112-BBC41B', NULL, 1, 1220.00, 'cash', 'pending', 'dine-in', '2025-11-12 09:46:30', '2025-11-12 09:46:30'),
(10, 'ORD-20251113-B33D36', NULL, 1, 370.00, 'cash', 'pending', 'dine-in', '2025-11-13 07:53:39', '2025-11-13 07:53:39'),
(11, 'ORD-20251116-94FFD2', NULL, 1, 150.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:30:37', '2025-11-16 10:47:13'),
(12, 'ORD-20251116-D9BB7B', NULL, 1, 180.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:34:24', '2025-11-16 10:47:08'),
(13, 'ORD-20251116-E2BCCF', NULL, 1, 3150.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:43:55', '2025-11-16 10:46:38'),
(14, 'ORD-20251116-5B62BD', NULL, 1, 100.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:52:32', '2025-11-16 11:01:56'),
(15, 'ORD-20251116-237AC7', NULL, 1, 100.00, 'cash', 'completed', 'dine-in', '2025-11-16 10:52:40', '2025-11-16 11:01:44'),
(16, 'ORD-20251116-60A095', NULL, 1, 3000.00, 'cash', 'completed', 'dine-in', '2025-11-16 11:07:10', '2025-11-16 11:07:27'),
(17, 'ORD-20251116-E28F83', NULL, 1, 4350.00, 'cash', 'pending', 'dine-in', '2025-11-16 11:08:35', '2025-11-16 11:08:35'),
(18, 'ORD-20251116-E681F0', NULL, 1, 6300.00, 'cash', 'pending', 'dine-in', '2025-11-16 11:09:00', '2025-11-16 11:09:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
