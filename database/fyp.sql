-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 10:45 AM
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
  `role` enum('superadmin','admin') NOT NULL,
  `admin_status` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `admin_name`, `admin_password`, `role`, `admin_status`) VALUES
(1, 'DuiSinCong', 'Abc12345?', 'superadmin', 'active'),
(2, 'Jerry', 'Jerry4141&', 'admin', 'active'),
(3, 'Keishav', 'Keishav5856!', 'admin', 'active');

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
('D05', 'Peach Tea', 5.90, 'Available', ''),
('D06', 'Lemonade', 5.90, 'Available', ''),
('D07', 'Honey Lemon', 6.90, 'Available', ''),
('D08', 'Skyjuice', 0.50, 'Available', '');

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
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_password` varchar(255) NOT NULL,
  `customer_status` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `customer_name`, `customer_email`, `customer_phone`, `customer_password`, `customer_status`) VALUES
(1, 'Ali', 'ali@gmail.com', '0128839921', 'Ali112233?', 'active'),
(2, 'Siti', 'siti@gmail.com', '0123456789', 'Siti2468@', 'active');

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
('A01', 'Chicken Chop & Aglio Olio', 15.90, 'Available', ''),
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
  `order_status` varchar(20) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00
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
  ADD UNIQUE KEY `email` (`customer_email`);

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
