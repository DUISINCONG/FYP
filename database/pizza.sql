-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-01-10 08:55:05
-- 服务器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `fyp`
--

-- --------------------------------------------------------

--
-- 表的结构 `pizza`
--

CREATE TABLE `pizza` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `pizza`
--

INSERT INTO `pizza` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`, `product_image`) VALUES
('B01', 'Hawaiian Chicken Pizza', 19.90, 'Available', 'Juicy chicken with sweet pineapple and melted cheese.', 'uploads/B01.jpg'),
('B02', 'Mushroom Pizza', 19.90, 'Available', 'Savory mushrooms on a cheesy, flavorful base.', 'uploads/B02.jpg'),
('B03', 'Seafood & Cheese Pizza', 19.90, 'Available', 'A rich mix of seafood topped with creamy melted cheese.', 'uploads/B03.jpg'),
('B04', 'Chicken & Mushroom Pizza', 19.90, 'Available', 'Tender chicken and mushrooms in a classic combo.', 'uploads/B04.jpg'),
('B05', 'Triple Cheese Pizza', 19.90, 'Available', 'A cheesy delight with three types of melted cheese.', 'uploads/B05.jpg'),
('B06', 'Beef & Chicken Pizza', 19.90, 'Available', 'Hearty beef and chicken with bold savory flavors.', 'uploads/B06.jpg'),
('B07', 'BBQ Chicken Pizza', 19.90, 'Available', 'Smoky BBQ sauce with tender chicken and cheese.', 'uploads/B07.jpg');

--
-- 转储表的索引
--

--
-- 表的索引 `pizza`
--
ALTER TABLE `pizza`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
