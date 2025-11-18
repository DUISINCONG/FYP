-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 03:06 PM
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
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `admin_name` varchar(255) NOT NULL,
  `admin_password` varchar(255) NOT NULL,
  `role` enum('superadmin','admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `admin_password`, `role`) VALUES
(1, 'superadmin', '12345', 'superadmin'),
(3, 'admin1', '12345', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `beverages`
--

CREATE TABLE `beverages` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `beverages`
--

INSERT INTO `beverages` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`) VALUES
('D01', 'Orange Juice', 6.90, 'Available', NULL),
('D02', 'Watermelon Juice', 6.90, 'Available', NULL),
('D03', 'Orange Yogurt', 7.90, 'Available', NULL),
('D04', 'Watermelon Yogurt', 7.90, 'Available', NULL),
('D05', 'Peach Tea (H/C)', 5.90, 'Available', NULL),
('D06', 'Lemonade (H/C)', 5.90, 'Available', NULL),
('D07', 'Honey Lemon (H/C)', 6.90, 'Available', NULL),
('D08', 'Skyjuice (H/C)', 0.50, 'Available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coffee`
--

CREATE TABLE `coffee` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coffee`
--

INSERT INTO `coffee` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`) VALUES
('C01', 'Americano', 6.90, 'Available', NULL),
('C02', 'Caffe Latte', 7.90, 'Available', NULL),
('C03', 'Matcha Latte', 8.90, 'Available', NULL),
('C04', 'Mocha Latte', 9.90, 'Available', NULL),
('C05', 'Caramel Latte', 9.90, 'Available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `email`, `phone`, `password`) VALUES
(2, 'kelvin', 'kelvinchua@gmail.com', '0126657394', '21345678'),
(3, 'ivan', 'ivan@gmail.com', '0147562233', '32145678');

-- --------------------------------------------------------

--
-- Table structure for table `main_food`
--

CREATE TABLE `main_food` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_food`
--

INSERT INTO `main_food` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`) VALUES
('A01', 'Chicken Chop & Aglio Olio', 15.90, 'Available', NULL),
('A02', 'Chicken Chop & Bolognese Spaghetti', 15.90, 'Available', NULL),
('A03', 'Chicken Chop & Creamy Carbonara', 15.90, 'Available', NULL),
('A04', 'Salmon Steak & Aglio Olio', 25.90, 'Available', NULL),
('A05', 'Salmon Steak & Bolognese Spaghetti', 25.90, 'Available', NULL),
('A06', 'Salmon Steak & Creamy Carbonara', 25.90, 'Available', NULL),
('A07', 'Grilled Chicken', 19.90, 'Available', NULL),
('A08', 'Grilled Lamb Shoulder', 19.90, 'Available', NULL),
('A09', 'Smoked Duck', 19.90, 'Available', NULL),
('A10', 'Fried Chicken Chop', 20.90, 'Available', NULL),
('A11', 'Hawaiian Style Chicken Chop', 23.90, 'Available', NULL),
('A12', 'Fish & Chips', 22.90, 'Available', NULL),
('A13', 'Salmon Steak', 30.90, 'Available', NULL),
('A14', 'Fried Chicken Chop & Rice', 14.90, 'Available', NULL),
('A15', 'Fish Chop & Rice', 14.90, 'Available', NULL),
('A16', 'Grilled Chicken & Rice', 16.90, 'Available', NULL),
('A17', 'Curry Chicken Chop Rice', 15.90, 'Available', NULL),
('A18', 'Curry Fish Chop Rice', 15.90, 'Available', NULL),
('A19', 'Japanese Chicken Donburi', 15.90, 'Available', NULL),
('A20', 'Smoked Duck Donburi', 15.90, 'Available', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `status` enum('pending','delivery','complete') DEFAULT 'pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pizza`
--

CREATE TABLE `pizza` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pizza`
--

INSERT INTO `pizza` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`) VALUES
('B01', 'Hawaiian Chicken Pizza', 19.90, 'Available', NULL),
('B02', 'Mushroom Pizza', 19.90, 'Available', NULL),
('B03', 'Seafood & Cheese Pizza', 19.90, 'Available', NULL),
('B04', 'Chicken & Mushroom Pizza', 19.90, 'Available', NULL),
('B05', 'Triple Cheese Pizza', 19.90, 'Available', NULL),
('B06', 'Beef & Chicken Pizza', 19.90, 'Available', NULL),
('B07', 'BBQ Chicken Pizza', 19.90, 'Available', NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `snackfood`
--

CREATE TABLE `snackfood` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `snackfood`
--

INSERT INTO `snackfood` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`) VALUES
('E01', 'Garlic Bread', 3.90, 'Available', NULL),
('E02', 'Mushroom Soup', 6.90, 'Available', NULL),
('E03', 'Mashed Potato', 4.90, 'Available', NULL),
('E04', 'Golden Mantou', 6.90, 'Available', NULL),
('E05', 'French Fries', 7.90, 'Available', NULL),
('E06', 'Cheese Wedges', 8.90, 'Available', NULL),
('E07', 'Tempura Nugget', 8.90, 'Available', NULL),
('E08', 'Jumbo Sausage', 9.90, 'Available', NULL),
('E09', 'Fried Abalone Mushroom', 8.90, 'Available', NULL),
('E10', 'Chicken Popcorn', 12.90, 'Available', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `beverages`
--
ALTER TABLE `beverages`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `coffee`
--
ALTER TABLE `coffee`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `main_food`
--
ALTER TABLE `main_food`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `pizza`
--
ALTER TABLE `pizza`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `snackfood`
--
ALTER TABLE `snackfood`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
