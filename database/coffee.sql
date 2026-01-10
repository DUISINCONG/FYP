-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-01-10 08:54:57
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
-- 表的结构 `coffee`
--

CREATE TABLE `coffee` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `coffee`
--

INSERT INTO `coffee` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`, `product_image`) VALUES
('C01', 'Americano', 6.90, 'Available', 'Smooth black coffee made with espresso and hot water.', 'uploads/C01.jpg'),
('C02', 'Caffe Latte', 7.90, 'Available', 'Rich espresso blended with steamed milk.', 'uploads/C02.jpg'),
('C03', 'Matcha Latte', 8.90, 'Available', 'Creamy milk mixed with premium matcha green tea.', 'uploads/C03.jpg'),
('C04', 'Mocha Latte', 9.90, 'Available', 'Espresso with chocolate flavor and steamed milk.', 'uploads/C04.jpg'),
('C05', 'Caramel Latte', 9.90, 'Available', 'Sweet caramel combined with espresso and milk.', 'uploads/C05.jpg');

--
-- 转储表的索引
--

--
-- 表的索引 `coffee`
--
ALTER TABLE `coffee`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
