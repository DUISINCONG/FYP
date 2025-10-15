-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 15, 2025 at 02:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fyp`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` varchar(10) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `price`, `status`) VALUES
('A01', 'Chicken Chop & Aglio Olio', 15.90, 'Available'),
('A02', 'Chicken Chop & Bolognese Spaghetti', 15.90, 'Available'),
('A03', 'Chicken Chop & Creamy Carbonara', 15.90, 'Available'),
('A04', 'Salmon Steak & Aglio Olio', 25.90, 'Available'),
('A05', 'Salmon Steak & Bolognese Spaghetti', 25.90, 'Available'),
('A06', 'Salmon Steak & Creamy Carbonara', 25.90, 'Available'),
('A07', 'Grilled Chicken', 19.90, 'Available'),
('A08', 'Grilled Lamb Shoulder', 19.90, 'Available'),
('A09', 'Smoked Duck', 19.90, 'Available'),
('A10', 'Fried Chicken Chop', 20.90, 'Available'),
('A11', 'Hawaiian Style Chicken Chop', 23.90, 'Available'),
('A12', 'Fish & Chips', 22.90, 'Available'),
('A13', 'Salmon Steak', 30.90, 'Available'),
('A14', 'Fried Chicken Chop & Rice', 14.90, 'Available'),
('A15', 'Fish Chop & Rice', 14.90, 'Available'),
('A16', 'Grilled Chicken & Rice', 16.90, 'Available'),
('A17', 'Curry Chicken Chop Rice', 15.90, 'Available'),
('A18', 'Curry Fish Chop Rice', 15.90, 'Available'),
('A19', 'Japanese Chicken Donburi', 15.90, 'Available'),
('A20', 'Smoked Duck Donburi', 15.90, 'Available'),
('B1', 'Hawaiian Chicken Pizza', 19.90, 'Available'),
('B2', 'Mushroom Pizza', 19.90, 'Available'),
('B3', 'Seafood & Cheese Pizza', 19.90, 'Available'),
('B4', 'Chicken & Mushroom Pizza', 19.90, 'Available'),
('B5', 'Triple Cheese Pizza', 19.90, 'Available'),
('B6', 'Beef & Chicken Pizza', 19.90, 'Available'),
('B7', 'BBQ Chicken Pizza', 19.90, 'Available'),
('C01', 'Americano', 6.90, 'Available'),
('C02', 'Caffe Latte', 7.90, 'Available'),
('C03', 'Matcha Latte', 8.90, 'Available'),
('C04', 'Mocha Latte', 9.90, 'Available'),
('C05', 'Caramel Latte', 9.90, 'Available'),
('D01', 'Orange Juice', 6.90, 'Available'),
('D02', 'Watermelon Juice', 6.90, 'Available'),
('D03', 'Orange Yogurt', 7.90, 'Available'),
('D04', 'Watermelon Yogurt', 7.90, 'Available'),
('D05', 'Peach Tea (H/C)', 5.90, 'Available'),
('D06', 'Lemonade (H/C)', 5.90, 'Available'),
('D07', 'Honey Lemon (H/C)', 6.90, 'Available'),
('D08', 'Skyjuice (H/C)', 0.50, 'Available'),
('E01', 'Garlic Bread', 3.90, 'Available'),
('E02', 'Mushroom Soup', 6.90, 'Available'),
('E03', 'Mashed Potato', 4.90, 'Available'),
('E04', 'Golden Mantou', 6.90, 'Available'),
('E05', 'French Fries', 7.90, 'Available'),
('E06', 'Cheese Wedges', 8.90, 'Available'),
('E07', 'Tempura Nugget', 8.90, 'Available'),
('E08', 'Jumbo Sausage', 9.90, 'Available'),
('E09', 'Fried Abalone Mushroom', 8.90, 'Available'),
('E10', 'Chicken Popcorn', 12.90, 'Available');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
