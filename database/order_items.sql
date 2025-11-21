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

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` enum('dodici','sedici') DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `size`, `quantity`, `price`, `subtotal`) VALUES
(1, 1, 4, 'sedici', 2, 150.00, 300.00),
(2, 2, 10, 'dodici', 1, 150.00, 150.00),
(3, 2, 6, 'sedici', 1, 120.00, 120.00),
(4, 2, 1, 'dodici', 1, 120.00, 120.00),
(5, 3, 9, 'dodici', 1, 100.00, 100.00),
(6, 3, 3, 'dodici', 2, 120.00, 240.00),
(7, 4, 9, 'dodici', 2, 100.00, 200.00),
(8, 5, 9, 'dodici', 1, 100.00, 100.00),
(9, 5, 4, 'dodici', 1, 130.00, 130.00),
(10, 5, 8, 'dodici', 1, 180.00, 180.00),
(11, 5, 2, 'sedici', 1, 140.00, 140.00),
(12, 6, 10, 'dodici', 1, 150.00, 150.00),
(13, 6, 3, 'sedici', 1, 140.00, 140.00),
(14, 7, 9, 'dodici', 1, 100.00, 100.00),
(15, 8, 5, 'dodici', 1, 80.00, 80.00),
(16, 8, 1, 'sedici', 1, 150.00, 150.00),
(17, 8, 7, 'dodici', 1, 140.00, 140.00),
(18, 8, 8, 'dodici', 1, 180.00, 180.00),
(19, 9, 9, 'dodici', 1, 100.00, 100.00),
(20, 9, 7, 'dodici', 8, 140.00, 1120.00),
(21, 10, 6, 'sedici', 1, 120.00, 120.00),
(22, 10, 4, 'dodici', 1, 130.00, 130.00),
(23, 10, 3, 'dodici', 1, 120.00, 120.00),
(24, 11, 4, 'sedici', 1, 150.00, 150.00),
(25, 12, 8, 'dodici', 1, 180.00, 180.00),
(26, 13, 10, 'dodici', 21, 150.00, 3150.00),
(27, 14, 5, 'sedici', 1, 100.00, 100.00),
(28, 15, 9, 'dodici', 1, 100.00, 100.00),
(29, 16, 4, 'sedici', 20, 150.00, 3000.00),
(30, 17, 10, 'dodici', 29, 150.00, 4350.00),
(31, 18, 10, 'dodici', 42, 150.00, 6300.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
