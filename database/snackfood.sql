-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-01-10 08:55:09
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
-- 表的结构 `snackfood`
--

CREATE TABLE `snackfood` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `snackfood`
--

INSERT INTO `snackfood` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`, `product_image`) VALUES
('E01', 'Garlic Bread', 3.90, 'Available', 'Crispy bread with buttery garlic flavor.', 'uploads/E01.jpg'),
('E02', 'Mushroom Soup', 6.90, 'Available', 'Creamy soup made with fresh mushrooms.', 'uploads/E02.jpg'),
('E03', 'Mashed Potato', 4.90, 'Available', 'Smooth, buttery mashed potatoes.', 'uploads/E03.jpg'),
('E04', 'Golden Mantou', 6.90, 'Available', 'Deep-fried mantou with a crispy golden crust.', 'uploads/E04.jpg'),
('E05', 'French Fries', 7.90, 'Available', 'Crispy fries, lightly salted.', 'uploads/E05.jpg'),
('E06', 'Cheese Wedges', 8.90, 'Available', 'Crunchy wedges with melted cheese inside.', 'uploads/E06.jpg'),
('E07', 'Tempura Nugget', 8.90, 'Available', 'Nuggets coated in light, crispy tempura batter.', 'uploads/E07.jpg'),
('E08', 'Jumbo Sausage', 9.90, 'Available', 'Juicy, grilled jumbo sausage.', 'uploads/E08.jpg'),
('E09', 'Fried Abalone Mushroom', 8.90, 'Available', 'Crispy fried abalone mushrooms with rich flavor.', 'uploads/E09.jpg'),
('E10', 'Chicken Popcorn', 12.90, 'Available', 'Bite-sized crispy chicken pieces.', 'uploads/E10.jpg');

--
-- 转储表的索引
--

--
-- 表的索引 `snackfood`
--
ALTER TABLE `snackfood`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
