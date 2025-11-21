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

CREATE TABLE `inventory_transactions` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_type` enum('restock','sale','adjustment','waste') NOT NULL,
  `quantity` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_transactions`
--

INSERT INTO `inventory_transactions` (`id`, `product_id`, `transaction_type`, `quantity`, `user_id`, `notes`, `created_at`) VALUES
(1, 4, 'sale', -2, 1, NULL, '2025-11-12 09:24:05'),
(2, 10, 'sale', -1, 1, NULL, '2025-11-12 09:24:38'),
(3, 6, 'sale', -1, 1, NULL, '2025-11-12 09:24:38'),
(4, 1, 'sale', -1, 1, NULL, '2025-11-12 09:24:38'),
(5, 9, 'sale', -1, 1, NULL, '2025-11-12 09:24:52'),
(6, 3, 'sale', -2, 1, NULL, '2025-11-12 09:24:52'),
(7, 9, 'sale', -2, 1, NULL, '2025-11-12 09:24:59'),
(8, 9, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(9, 4, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(10, 8, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(11, 2, 'sale', -1, 1, NULL, '2025-11-12 09:25:49'),
(12, 10, 'sale', -1, 1, NULL, '2025-11-12 09:39:07'),
(13, 3, 'sale', -1, 1, NULL, '2025-11-12 09:39:07'),
(14, 9, 'sale', -1, 1, NULL, '2025-11-12 09:45:43'),
(15, 5, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(16, 1, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(17, 7, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(18, 8, 'sale', -1, 1, NULL, '2025-11-12 09:46:01'),
(19, 9, 'sale', -1, 1, NULL, '2025-11-12 09:46:30'),
(20, 7, 'sale', -8, 1, NULL, '2025-11-12 09:46:30'),
(21, 6, 'sale', -1, 1, NULL, '2025-11-13 07:53:39'),
(22, 4, 'sale', -1, 1, NULL, '2025-11-13 07:53:39'),
(23, 3, 'sale', -1, 1, NULL, '2025-11-13 07:53:40'),
(24, 4, 'sale', -1, 1, NULL, '2025-11-16 10:30:37'),
(25, 8, 'sale', -1, 1, NULL, '2025-11-16 10:34:24'),
(26, 10, 'sale', -21, 1, NULL, '2025-11-16 10:43:55'),
(27, 5, 'sale', -1, 1, NULL, '2025-11-16 10:52:32'),
(28, 9, 'sale', -1, 1, NULL, '2025-11-16 10:52:40'),
(29, 4, 'sale', -20, 1, NULL, '2025-11-16 11:07:10'),
(30, 10, 'sale', -29, 1, NULL, '2025-11-16 11:08:35'),
(31, 10, 'sale', -42, 1, NULL, '2025-11-16 11:09:00'),
(32, 10, 'restock', 30, 1, '', '2025-11-16 11:10:09'),
(33, 10, 'restock', 10, 1, '', '2025-11-16 11:10:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_transactions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
