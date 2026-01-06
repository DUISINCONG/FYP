-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-01-06 16:42:34
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
-- 表的结构 `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` varchar(10) NOT NULL,
  `product_name` text NOT NULL,
  `product_image` varchar(255) NOT NULL,
  `order_status` varchar(20) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(10) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `paymenttime` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `product_id`, `product_name`, `product_image`, `order_status`, `order_date`, `quantity`, `total_amount`, `paymenttime`) VALUES
(63, 2, 'A02', 'Chicken Chop & Bolognese Spaghetti', 'uploads/A02.jpg', 'unactive', '2026-01-05 19:21:09', 1, 15.90, '2026-01-06 14:21:10'),
(64, 2, 'C02', 'Caffe Latte', 'uploads/C02.jpg', 'unactive', '2026-01-05 19:21:14', 1, 7.90, '2026-01-06 14:21:10'),
(65, 2, 'A03', 'Chicken Chop & Creamy Carbonara', 'uploads/A03.jpg', 'active', '2026-01-05 19:31:59', 1, 15.90, NULL),
(66, 1, 'A02', 'Chicken Chop & Bolognese Spaghetti', 'uploads/A02.jpg', 'active', '2026-01-05 19:33:20', 1, 15.90, NULL),
(67, 1, 'D02', 'Watermelon Juice', 'uploads/D02.jpg', 'active', '2026-01-05 19:33:29', 1, 6.90, NULL),
(68, 1, 'E06', 'Cheese Wedges', 'uploads/E06.jpg', 'active', '2026-01-05 19:33:35', 2, 17.80, NULL);

--
-- 转储表的索引
--

--
-- 表的索引 `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- 限制导出的表
--

--
-- 限制表 `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
