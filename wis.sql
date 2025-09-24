-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 17, 2025 at 06:10 AM
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
-- Database: `wis`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `member_id`, `product_id`, `quantity`) VALUES
(59, 1, 7, 99),
(65, 4, 16, 1);

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`) VALUES
(1, 'Graphic comics'),
(2, 'light novel'),
(3, 'Reference Book'),
(7, 'Fiction'),
(8, 'Children’s Books'),
(9, 'Cookbooks'),
(10, 'Language');

-- --------------------------------------------------------

--
-- Table structure for table `chat`
--

CREATE TABLE `chat` (
  `id` int(11) NOT NULL,
  `from_user_id` int(11) NOT NULL,
  `to_user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chat`
--

INSERT INTO `chat` (`id`, `from_user_id`, `to_user_id`, `message`, `timestamp`) VALUES
(2, 4, 3, 'HELLO ,I AM WEI JIE', '2024-09-29 12:37:44'),
(3, 3, 4, 'HELLO ,I AM ADMIN', '2024-09-29 12:37:52'),
(4, 4, 3, 'I has a problem', '2024-09-30 03:44:20'),
(5, 3, 4, 'yes', '2024-09-30 03:44:49'),
(6, 3, 8, 'hi', '2024-10-01 03:34:38'),
(7, 8, 3, 'hi', '2024-10-01 03:35:54'),
(8, 1, 3, 'HELLO', '2024-10-01 03:40:09'),
(9, 1, 3, 'I AM PO YI', '2024-10-01 03:40:20'),
(10, 3, 1, 'HI I AM ADMIN ,MAY I HELP YOU', '2024-10-01 03:40:40'),
(11, 4, 3, '?', '2025-09-16 05:08:41'),
(12, 3, 4, '?', '2025-09-16 05:11:10');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorites`
--

INSERT INTO `favorites` (`id`, `member_id`, `product_id`) VALUES
(1, 1, 1),
(2, 1, 7),
(45, 4, 1),
(41, 4, 7);

-- --------------------------------------------------------

--
-- Table structure for table `orderproduct`
--

CREATE TABLE `orderproduct` (
  `id` int(11) NOT NULL,
  `ordersid` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderproduct`
--

INSERT INTO `orderproduct` (`id`, `ordersid`, `product_id`, `quantity`, `subtotal`) VALUES
(1, 1, 8, 1, 50),
(2, 4, 7, 2, 200),
(3, 4, 8, 1, 50),
(8, 8, 7, 7, 700),
(9, 9, 7, 3, 300),
(10, 10, 9, 1, 88),
(11, 11, 7, 10, 1000),
(12, 12, 8, 1, 50),
(13, 13, 1, 10, 150),
(14, 14, 8, 5, 250),
(15, 15, 7, 5, 500),
(24, 18, 1, 10, 150),
(25, 18, 7, 10, 1000),
(26, 19, 1, 10, 150),
(27, 20, 1, 5, 75),
(28, 20, 7, 5, 500),
(29, 20, 8, 5, 250),
(30, 20, 9, 5, 440),
(31, 21, 11, 10, 60),
(32, 21, 7, 1, 100),
(33, 21, 1, 3, 45),
(35, 24, 1, 5, 75),
(36, 25, 1, 3, 45),
(37, 25, 7, 4, 400),
(38, 26, 16, 2, 117.8),
(39, 27, 15, 2, 99.8),
(40, 27, 12, 2, 12),
(41, 27, 10, 2, 12),
(42, 28, 11, 1, 6),
(43, 29, 16, 1, 58.9);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `total_product` int(11) NOT NULL,
  `total_price` double NOT NULL,
  `address` varchar(255) NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `member_id`, `date`, `time`, `total_product`, `total_price`, `address`, `status`) VALUES
(1, 1, '2024-08-23', '09:00:00', 1, 50, '123 Kampung Ayer Tawar Perak', 'shipping'),
(4, 1, '2024-08-26', '19:25:00', 3, 250, '123 Kampung Ayer Tawar Perak', 'pending'),
(8, 1, '2024-09-17', '13:44:46', 1, 700, '123 Kampung Ayer Tawar Perak', 'pending'),
(9, 1, '2024-09-17', '20:13:43', 1, 300, '123 Kampung Ayer Tawar Perak', 'pending'),
(10, 4, '2024-09-17', '20:21:37', 1, 88, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'completed'),
(11, 4, '2024-09-17', '20:26:43', 1, 1000, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'cancelled'),
(12, 4, '2024-09-17', '20:36:01', 1, 50, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'pending'),
(13, 4, '2024-09-17', '22:17:29', 10, 150, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'completed'),
(14, 4, '2024-09-18', '22:03:51', 5, 250, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'completed'),
(15, 4, '2024-09-19', '18:17:54', 5, 500, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'pending'),
(18, 4, '2024-09-19', '21:00:26', 30, 1150, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'pending'),
(19, 4, '2024-09-19', '22:09:27', 10, 150, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'cancelled'),
(20, 4, '2024-09-28', '16:26:39', 20, 1265, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'pending'),
(21, 4, '2024-09-30', '15:14:59', 14, 205, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'cancelled'),
(22, 1, '2024-09-30', '17:24:42', 5, 75, '123 Kampung Ayer Tawar Perak', 'pending'),
(24, 1, '2024-09-30', '17:29:45', 5, 75, '123 Kampung Ayer Tawar Perak', 'completed'),
(25, 2, '2024-09-30', '17:33:04', 7, 445, '456 Kampung Ayer Tawar Perak', 'completed'),
(26, 1, '2024-09-30', '22:14:09', 2, 117.8, '123 Kampung Ayer Tawar Perak', 'pending'),
(27, 6, '2024-10-01', '16:37:31', 6, 123.8, '123 Kampung Beautiful ,Perak', 'completed'),
(28, 6, '2024-10-01', '16:38:19', 1, 6, '123 Kampung Beautiful ,Perak', 'cancelled'),
(29, 4, '2025-09-16', '13:06:23', 1, 58.9, '523 Kampung Baru,32400 Ayer Taawar,Perak', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `id` int(11) NOT NULL,
  `orders_id` int(11) NOT NULL,
  `amount` double NOT NULL,
  `payment_method` varchar(100) NOT NULL,
  `bankAccount` varchar(20) NOT NULL,
  `CVV` varchar(5) NOT NULL,
  `expired_date` date NOT NULL,
  `status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`id`, `orders_id`, `amount`, `payment_method`, `bankAccount`, `CVV`, `expired_date`, `status`) VALUES
(1, 1, 50, 'Cash on delivery', '', '', '2024-09-11', 'unpaid'),
(2, 4, 250, 'credit card', '1234567891478523', '123', '2024-08-30', 'paid'),
(5, 8, 700, 'credit card', '1234567891478523', '123', '1970-01-01', 'paid'),
(6, 9, 300, 'credit card', '1234567891478523', '123', '1970-01-01', 'paid'),
(7, 10, 88, 'credit card', '1234567891478523', '123', '1970-01-01', 'refunded'),
(8, 11, 1000, 'credit card', '1111111111111111', '123', '1970-01-01', 'paid'),
(9, 12, 50, 'credit card', '1111111111111111', '123', '1970-01-01', 'paid'),
(10, 13, 150, 'cash on delivery', '', '', '2024-09-04', 'refunded'),
(11, 14, 250, 'credit card', '1111111111111111', '123', '1970-01-01', 'paid'),
(12, 15, 500, 'credit card', '1111111111111111', '123', '1970-01-01', 'paid'),
(15, 18, 1150, 'credit card', '1234567891478523', '123', '1970-01-01', 'paid'),
(16, 19, 150, 'cash on delivery', '', '', '0000-00-00', 'unpaid'),
(17, 20, 1265, 'credit card', '1122334455667788', '123', '1970-01-01', 'paid'),
(18, 21, 205, 'cash on delivery', '', '', '0000-00-00', 'unpaid'),
(19, 22, 75, 'credit card', '1234567891478523', '123', '1970-01-01', 'paid'),
(21, 24, 75, 'credit card', '1111111111111111', '123', '1970-01-01', 'paid'),
(22, 25, 445, 'credit card', '1111111111111111', '123', '1970-01-01', 'paid'),
(23, 26, 117.8, 'cash on delivery', '', '', '0000-00-00', 'unpaid'),
(24, 27, 123.8, 'cash on delivery', '', '', '0000-00-00', 'paid'),
(25, 28, 6, 'cash on delivery', '', '', '0000-00-00', 'unpaid'),
(26, 29, 58.9, 'cash on delivery', '', '', '0000-00-00', 'unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `author` varchar(100) NOT NULL,
  `price` double NOT NULL,
  `description` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `stock` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `author`, `price`, `description`, `image`, `stock`, `category_id`) VALUES
(1, 'book1', 'author a', 15, 'This is book 1', 'book1.jpg', 1487, 1),
(7, 'book2', 'author b', 100, 'asdasd', 'book2.jpg', 91, 1),
(8, 'book3', 'author a', 50, 'dfgd', 'book3.jpg', 100, 2),
(9, 'book 4', 'author b', 88, 'hjohohk', 'book4.jpg', 97, 2),
(10, 'Tingkatan 5 - English Book SPM', 'Penerbitan Pelangi', 6, 'This is a nice english book', 'english_exercise_book_form_5_1679298311_1790240b.jpg', 198, 3),
(11, 'Tingkatan 5 - English Book SPM', 'Penerbitan Pelangi', 6, '', 'product_image.jpg', 200, 3),
(12, 'SPM MASTERCLASS BAHASA MELAYU (EDISI 2024)', 'SASBADI', 6, 'GOOD SPM BOOK', '9789837738539.jpg', 198, 3),
(15, 'Love Your Life', 'Kinsella, Sophie', 49.9, 'I love you . . . but what if I can\'t love your life?\r\n\r\nAva is sick of online dating. She\'s always trusted her own instincts over an algorithm, anyway, and she wants a break from it all. So when she signs up to a semi-silent, anonymous writing retreat in', '9781784165949_FC_mph (1).jpeg', 98, 7);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `rating` int(11) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`id`, `member_id`, `product_id`, `title`, `description`, `rating`, `date`) VALUES
(1, 1, 1, 'Very Good', 'The product very good ,i like it', 5, '2024-08-26'),
(2, 2, 1, 'Very nice', 'The is a nice product', 5, '2024-08-22'),
(14, 1, 1, 'My family say very nice book', 'My family say very nice book', 5, '2024-09-10'),
(15, 4, 1, 'VERY NICE', 'VERY NICE', 5, '2024-09-22'),
(16, 1, 1, 'Hahahahah', 'Very FUNNY', 4, '2024-09-30'),
(17, 2, 1, 'ASF', 'ASF', 5, '2024-09-30');

-- --------------------------------------------------------

--
-- Table structure for table `token`
--

CREATE TABLE `token` (
  `id` varchar(100) NOT NULL,
  `expire` datetime NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `image` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `gender` char(1) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `status` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `last_attempt` timestamp NULL DEFAULT NULL,
  `block_until` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `image`, `name`, `gender`, `dob`, `email`, `telephone`, `address`, `password`, `status`, `role`, `login_attempts`, `last_attempt`, `block_until`) VALUES
(1, 'poyi.jpeg', 'HO PO YI', 'F', '2010-08-01', 'abc@gmail.com', '011-14785236', '123 Kampung Ayer Tawar Perak', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'active', 'Member', 0, '2024-10-01 08:15:06', NULL),
(2, 'ali.jpeg', 'Ali', 'G', '2004-08-01', 'abc123@gmail.com', '011-5767653', '456 Kampung Ayer Tawar Perak', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'active', 'Member', 0, NULL, NULL),
(3, '66fb74a17e7fa.png', 'Admin', 'M', '2024-09-30', '1@gmail.com', '011-14785236', 'Admin ,kampung admin,perak', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'active', 'Admin', 0, '2024-09-30 11:01:34', NULL),
(4, 'weijie.jpeg', 'Wei Jie', 'M', '2004-10-03', 'dwj1003@gmail.com', '011-57676523', '523 Kampung Baru,32400 Ayer Taawar,Perak', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'active', 'Member', 0, '2025-09-16 04:56:53', NULL),
(5, '66fa63950a4cf.jpg', 'Beautiful Girl', 'F', '2000-06-21', 'beautiful@gmail.com', '011-14785236', '123 Kampung Beautiful ,Perak Malaysia', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'active', 'Member', 0, NULL, NULL),
(6, '66fa66a4015f4.jpg', 'Jang Won Young', 'F', '2004-08-31', 'Jang@gmail.com', '011-14785236', '123 Kampung Beautiful ,Perak', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'active', 'Member', 0, NULL, NULL),
(7, '66fa691f78021.jpg', 'Wang Yi Bo', 'M', '1997-08-15', 'wang@gmail.com', '011-12365478', '123 Kampung Handsome,Perak Malaysia', '7c4a8d09ca3762af61e59520943dc26494f8941b', 'blocked', 'Member', 0, NULL, NULL),
(8, '66fb6b6a2b5ad.jpg', 'Ho Xin Xin', 'F', '2006-10-20', 'hoxinyi@gmail.com', '011-12345678', '10, LALUAN MENGLEMBU TIMUR 1 31250 MENGLEMBU PERAK', '20eabe5d64b0e216796e834f52d61fd0b70332fc', 'active', 'Member', 0, '2024-10-01 07:06:38', NULL),
(11, '68c8b85a4a902.jpg', '?', 'M', '2000-10-10', 'abc@example.com', '011-23456789', '??', '21bd12dc183f740ee76f27b78eb39c8ad972a757', 'active', 'Member', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat`
--
ALTER TABLE `chat`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `member_id` (`member_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orderproduct`
--
ALTER TABLE `orderproduct`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ordersid` (`ordersid`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_id` (`orders_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `member_id_2` (`member_id`,`product_id`);

--
-- Indexes for table `token`
--
ALTER TABLE `token`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `chat`
--
ALTER TABLE `chat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `orderproduct`
--
ALTER TABLE `orderproduct`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `orderproduct`
--
ALTER TABLE `orderproduct`
  ADD CONSTRAINT `orderproduct_ibfk_1` FOREIGN KEY (`ordersid`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `orderproduct_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user` (`id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`orders_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `review_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`);

--
-- Constraints for table `token`
--
ALTER TABLE `token`
  ADD CONSTRAINT `token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
