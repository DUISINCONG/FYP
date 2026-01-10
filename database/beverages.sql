-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-01-10 08:54:52
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
-- 表的结构 `beverages`
--

CREATE TABLE `beverages` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `beverages`
--

INSERT INTO `beverages` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`, `product_image`) VALUES
('D01', 'Orange Juice', 6.90, 'Available', 'Fresh and tangy orange drink rich in vitamin C.', 'uploads/D01.jpg'),
('D02', 'Watermelon Juice', 6.90, 'Available', 'Light, sweet, and refreshing watermelon juice.', 'uploads/D02.jpg'),
('D03', 'Orange Yogurt', 7.90, 'Available', 'Creamy yogurt blended with fresh orange flavor.', 'uploads/D03.jpg'),
('D04', 'Watermelon Yogurt', 7.90, 'Available', 'Smooth yogurt mixed with sweet watermelon taste.', 'uploads/D04.jpg'),
('D05', 'Peach Tea', 5.90, 'Available', 'Fragrant tea infused with sweet peach flavor.', 'uploads/D05.jpg'),
('D06', 'Lemonade', 5.90, 'Available', 'Classic sweet and sour lemon drink, very refreshing.', 'uploads/D06.jpg'),
('D07', 'Honey Lemon', 6.90, 'Available', 'Cold lemon drink sweetened with natural honey.', 'uploads/D07.jpg'),
('D08', 'Skyjuice', 0.50, 'Available', 'Pure mineral water for a clean, refreshing hydration.', 'uploads/D08.jpg');

--
-- 转储表的索引
--

--
-- 表的索引 `beverages`
--
ALTER TABLE `beverages`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
