-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2026-01-10 08:55:01
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
-- 表的结构 `main_food`
--

CREATE TABLE `main_food` (
  `product_id` varchar(10) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `product_price` decimal(5,2) NOT NULL,
  `product_status` varchar(20) NOT NULL,
  `product_description` text DEFAULT NULL,
  `product_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 转存表中的数据 `main_food`
--

INSERT INTO `main_food` (`product_id`, `product_name`, `product_price`, `product_status`, `product_description`, `product_image`) VALUES
('A01', 'Chicken Chop & Aglio Olio', 15.90, 'Available', 'A tender grilled chicken chop served with fragrant aglio olio pasta, cooked with garlic and herbs for a light and flavorful meal.', 'uploads/A01.jpg'),
('A02', 'Chicken Chop & Bolognese Spaghetti', 15.90, 'Available', 'A juicy chicken chop paired with spaghetti in a rich, slow-cooked tomato and minced meat bolognese sauce.', 'uploads/A02.jpg'),
('A03', 'Chicken Chop & Creamy Carbonara', 15.90, 'Available', 'A succulent chicken chop served with creamy carbonara spaghetti, made with a rich cheese sauce and smoky flavors.', 'uploads/A03.jpg'),
('A04', 'Salmon Steak & Aglio Olio', 25.90, 'Available', 'A perfectly grilled salmon steak served with aglio olio pasta, infused with garlic and herbs for a refreshing taste.', 'uploads/A04.jpg'),
('A05', 'Salmon Steak & Bolognese Spaghetti', 25.90, 'Available', 'A tender salmon steak paired with spaghetti topped with hearty bolognese sauce for a satisfying combination.', 'uploads/A05.jpg'),
('A06', 'Salmon Steak & Creamy Carbonara', 25.90, 'Available', 'A juicy salmon steak served with creamy carbonara spaghetti, offering a smooth and indulgent flavor.', 'uploads/A06.jpg'),
('A07', 'Grilled Chicken', 19.90, 'Available', 'A tender grilled chicken marinated with herbs and spices, served juicy and full of flavor.', 'uploads/A07.jpg'),
('A08', 'Grilled Lamb Shoulder', 19.90, 'Available', 'A slow-grilled lamb shoulder seasoned with aromatic spices, tender and rich in flavor.', 'uploads/A08.jpg'),
('A09', 'Smoked Duck', 19.90, 'Available', 'Flavorful smoked duck with crispy skin and juicy meat, infused with a smoky aroma.', 'uploads/A09.jpg'),
('A10', 'Fried Chicken Chop', 20.90, 'Available', 'A crispy golden fried chicken chop, crunchy on the outside and tender inside.', 'uploads/A10.jpg'),
('A11', 'Hawaiian Style Chicken Chop', 23.90, 'Available', 'A grilled chicken chop topped with a sweet and tangy Hawaiian-style sauce for a tropical twist.', 'uploads/A11.jpg'),
('A12', 'Fish & Chips', 22.90, 'Available', 'Crispy battered fish served with golden fries, a classic and satisfying comfort dish.', 'uploads/A12.jpg'),
('A13', 'Salmon Steak', 30.90, 'Available', 'A perfectly grilled salmon steak, tender and juicy with a rich, natural flavor.', 'uploads/A13.jpg'),
('A14', 'Fried Chicken Chop & Rice', 14.90, 'Available', 'A crispy fried chicken chop served with steamed rice for a hearty and filling meal.', 'uploads/A14.jpg'),
('A15', 'Fish Chop & Rice', 14.90, 'Available', 'A pan-grilled fish chop served with steamed rice, light and delicious.', 'uploads/A15.jpg'),
('A16', 'Grilled Chicken & Rice', 16.90, 'Available', 'Juicy grilled chicken served with warm steamed rice for a simple and wholesome dish.', 'uploads/A16.jpg'),
('A17', 'Curry Chicken Chop Rice', 15.90, 'Available', 'A flavorful chicken chop served with fragrant curry sauce and steamed rice.', 'uploads/A17.jpg'),
('A18', 'Curry Fish Chop Rice', 15.90, 'Available', 'A tender fish chop paired with rich curry sauce and steamed rice for a bold taste.', 'uploads/A18.jpg'),
('A19', 'Japanese Chicken Donburi', 15.90, 'Available', 'Tender chicken simmered in a savory Japanese sauce, served over warm rice.', 'uploads/A19.jpg'),
('A20', 'Smoked Duck Donburi', 15.90, 'Available', 'Sliced smoked duck served over rice with a savory Japanese-style sauce.', 'uploads/A20.jpg');

--
-- 转储表的索引
--

--
-- 表的索引 `main_food`
--
ALTER TABLE `main_food`
  ADD PRIMARY KEY (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
