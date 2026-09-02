-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2026 at 11:31 AM
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
-- Database: `restaurant_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `customeraddresses`
--

CREATE TABLE `customeraddresses` (
  `address_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `address_label` varchar(50) DEFAULT NULL,
  `address_text` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customeraddresses`
--

INSERT INTO `customeraddresses` (`address_id`, `customer_id`, `address_label`, `address_text`, `city`) VALUES
(1, 10, 'Home', 'Street 40, Flowerporch, House 38', 'Blackstone'),
(2, 11, 'Home', 'Street 30, Block 7, House 30', 'New York'),
(3, 16, 'House 30', 'Street 50, area 123', 'Flowers'),
(4, 17, 'house 30', 'street 45, area 123', 'coconut '),
(5, 18, 'House 32', 'Street 30, area 40', 'Stone'),
(6, 19, 'house 1022', 'stree 50, area T', 'bloomsdale '),
(7, 22, 'home 30', 'street 30, area 30', 'blooms');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `discount_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `discount_type` enum('percentage','flat') DEFAULT NULL,
  `discount_value` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`discount_id`, `dish_id`, `discount_type`, `discount_value`) VALUES
(6, 1, 'percentage', 40.00),
(7, 132, 'percentage', 10.00),
(8, 129, 'percentage', 30.00),
(9, 7, 'percentage', 5.00),
(10, 130, 'percentage', 10.00),
(11, 12, 'percentage', 32.00),
(12, 128, 'percentage', 0.00),
(13, 4, 'percentage', 25.00),
(14, 133, 'percentage', 20.00),
(15, 135, 'percentage', 5.00),
(16, 137, 'percentage', 30.00),
(17, 159, 'percentage', 50.00),
(18, 161, 'flat', 5.00),
(19, 162, 'percentage', 50.00),
(20, 165, 'percentage', 20.00),
(21, 164, 'flat', 4.00),
(22, 168, 'flat', 3.00),
(23, 5, 'flat', 5.00),
(24, 175, 'percentage', 50.00),
(25, 174, 'flat', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `dish_id` int(11) NOT NULL,
  `dish_name` varchar(150) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `availability` enum('available','unavailable') DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dishes`
--

INSERT INTO `dishes` (`dish_id`, `dish_name`, `price`, `description`, `image_path`, `availability`) VALUES
(1, 'Pakistani Biryani', 18.99, 'Traditional Pakistani Rice and Chicken', 'biryani.jpg\r\n', 'available'),
(3, 'Egg Paratha', 7.99, 'A traditional flatbread filled with seasoned egg and veggies', 'Egg Paratha.jpg', 'available'),
(4, 'Pizza', 20.00, 'Delicious cheese pizza', 'pizza.jpg', 'available'),
(5, 'Burger', 13.99, 'Juicy beef burger', 'burger.jpg', 'available'),
(6, 'Pasta', 15.99, 'Creamy alfredo pasta', 'pasta.jpg', 'unavailable'),
(7, 'Chicken and Mushroom Dumplings', 8.99, 'Dumpling wrappers filled with delicious chicken and mushroom mix', 'Chicken and Mushroom Dumplings.jpg', 'available'),
(8, 'Steak Frites', 25.00, 'Grilled ribeye served with crispy golden fries', 'steak.jpg', 'available'),
(9, 'Garden Salad', 9.50, 'Fresh seasonal greens with balsamic vinaigrette', 'salad.jpg', 'available'),
(10, 'Taco Platter', 14.00, 'Soft corn tacos with spicy shredded beef', 'tacos.jpg', 'available'),
(11, 'Salmon Fillet', 20.00, 'Pan-seared salmon with roasted vegetables', 'salmon.jpg', 'unavailable'),
(12, 'Chocolate Cake', 10.00, 'Rich dark chocolate layer cake with ganache', 'cake.jpg', 'available'),
(128, 'Chicken Shawarma', 12.50, 'Thinly sliced marinated chicken with garlic sauce and pickles', 'shawarma.jpg', 'available'),
(129, 'Lamb Mansaf', 22.00, 'Tender lamb cooked in jameed served over turmeric rice', 'mansaf.jpg', 'available'),
(130, 'Falafel Platter', 10.00, 'Crispy chickpea balls served with tahini sauce and salad', 'falafel.jpg', 'available'),
(132, 'Kibbeh', 14.00, 'Fried cracked wheat shells stuffed with spiced minced meat', 'kibbeh.jpg', 'unavailable'),
(133, 'Red Sauce Pasta', 14.00, 'Delicious pasta wrapped in a warm tomato sauce. Perfect for cold weather.', 'red sauce pasta.jpeg', 'available'),
(135, 'Green summer salad', 10.00, 'Delicious yummy seasonal veggies in one bowl!', 'summer salad.jpeg', 'unavailable'),
(137, 'Poutine', 10.00, 'Delicious cheesy gravy fries!! Perfect for cold weather!', 'poutine.jpeg', 'unavailable'),
(159, 'Triple Hot Dogs', 9.00, 'Yummy flavorful hot dogs!', 'hot dogs.jpeg', 'available'),
(160, 'Coconut Curry', 13.00, 'Delicious warm and spicy coconut curry', 'curry.jpg', 'available'),
(161, 'Udon Noodles', 9.50, 'Warm delicious noodles with a kick of chili oil', 'udon.jpg', 'available'),
(162, 'Orange Chicken', 12.00, 'Delicious fried chicken in an orange zesty sauce', 'orange.jpeg', 'available'),
(163, 'Ramen', 11.50, 'Japanese noodle soup with rich broth and toppings', 'ramen.jpg', 'available'),
(164, 'Fufu with okra soup', 8.00, 'Traditional West African dish served with soup or stew', 'fufu.jpg', 'available'),
(165, 'Vanilla Icecream', 3.00, 'Delicious icecream for hot days', 'vanilla.jpeg', 'available'),
(166, 'Butter Chicken', 13.25, 'Creamy tomato-based curry with tender chicken pieces', 'butter.jpg', 'available'),
(167, 'Sushi Platter', 15.75, 'Assorted fresh sushi rolls with soy sauce and wasabi', 'sushi.jpg', 'available'),
(168, 'Mac and Cheese', 8.00, 'Yummy buttery pasta with cheese', 'mac and cheese.jpg', 'available'),
(169, 'Chicken loaf', 6.75, 'Soft baked loaf filled with chicken', 'loaf.jpg', 'available'),
(170, 'Chocolate Brownies', 6.00, 'Rich and fudgy chocolate brownies with a soft center', 'brownies.jpg', 'unavailable'),
(171, 'Honey Toast', 0.00, 'Hot honey bread with chili flakes', '', 'available'),
(172, 'Mint margarita', 5.00, 'Delicious summer drink with a lot of mint and lemon!', 'mint margarita.jpeg', 'available'),
(173, 'Olive Rosemary Focaccia', 9.75, 'Fluffy Italian bread baked with olives, rosemary, and olive oil', 'focaccia.jpg', 'available'),
(174, 'Cheesy Garlic Pull-Apart Bread', 8.90, 'Soft baked bread loaded with garlic butter and melted cheese', 'garlicbread.jpg', 'available'),
(175, 'Potato Jacket', 6.00, 'Delicious potato with butter and herbs', 'Potato Jacket.jpeg', 'available'),
(176, 'Tuna Melt Sandwich', 9.25, 'Toasted sandwich with tuna, melted cheese, and a hint of herbs', 'tuna.jpg', 'available'),
(177, 'Spicy Mango Chicken Bowl', 12.90, 'Grilled chicken served with sweet mango salsa and spicy rice', 'mango.jpg', 'available'),
(178, 'Choco cookies', 6.00, 'Delicious cookies', 'choco.jpeg', 'available'),
(179, 'Smoked Paprika Chickpea Bowl', 10.75, 'Roasted chickpeas with smoked paprika served over herbed rice', 'chickpea.jpg', 'available'),
(180, 'Caramelized Onion & Goat Cheese Tart', 11.50, 'Flaky pastry topped with sweet onions and creamy goat cheese', 'onion.jpg', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `orderitems`
--

CREATE TABLE `orderitems` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `dish_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `discount_applied` decimal(10,2) DEFAULT NULL,
  `line_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orderitems`
--

INSERT INTO `orderitems` (`order_item_id`, `order_id`, `dish_id`, `quantity`, `unit_price`, `discount_applied`, `line_total`) VALUES
(1, 1, 1, 1, 18.99, 0.00, 18.99),
(2, 1, 3, 1, 7.99, 0.00, 7.99),
(3, 2, 128, 1, 12.50, 0.88, 11.63),
(4, 2, 129, 3, 22.00, 6.60, 46.20),
(5, 2, 130, 5, 10.00, 1.00, 45.00),
(6, 2, 132, 3, 14.00, 1.40, 37.80),
(7, 3, 7, 3, 8.99, 0.45, 25.62),
(8, 3, 161, 2, 9.50, 3.00, 13.00),
(9, 4, 165, 3, 3.00, 0.60, 7.20),
(10, 4, 167, 3, 15.75, 0.00, 47.25),
(11, 5, 169, 2, 6.75, 0.00, 13.50),
(12, 5, 170, 5, 5.50, 0.00, 27.50),
(13, 6, 173, 2, 9.75, 0.00, 19.50),
(14, 6, 174, 2, 8.90, 0.00, 17.80),
(15, 7, 180, 3, 11.50, 0.00, 34.50);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rider_id` int(11) DEFAULT NULL,
  `order_status` enum('Pending','ReadyForPickup','OutForDelivery','Delivered','Cancelled') DEFAULT 'Pending',
  `address_id` int(11) NOT NULL,
  `order_timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_timestamp` timestamp NULL DEFAULT NULL,
  `rider_code_verified` tinyint(4) DEFAULT 0,
  `customer_code_verified` tinyint(4) DEFAULT 0,
  `customer_verify_code` int(4) DEFAULT NULL,
  `rider_verify_code` int(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `rider_id`, `order_status`, `address_id`, `order_timestamp`, `delivery_timestamp`, `rider_code_verified`, `customer_code_verified`, `customer_verify_code`, `rider_verify_code`) VALUES
(1, 10, 9, 'Delivered', 1, '2026-01-24 13:33:31', '2026-01-24 13:34:14', 1, 1, 5669, 2144),
(2, 11, 9, 'Delivered', 2, '2026-01-24 13:56:11', '2026-01-24 14:01:01', 1, 1, 2664, 5388),
(3, 10, 9, 'Delivered', 1, '2026-03-18 09:47:38', '2026-03-18 09:50:00', 1, 1, 9176, 7194),
(4, 10, 9, 'Delivered', 1, '2026-03-18 10:42:03', '2026-03-18 10:42:49', 1, 1, 6722, 4020),
(5, 10, 9, 'Delivered', 1, '2026-03-18 12:38:07', '2026-03-18 12:38:51', 1, 1, 3560, 9295),
(6, 10, 9, 'Delivered', 1, '2026-03-18 12:51:31', '2026-03-18 12:52:28', 1, 1, 9844, 8420),
(7, 10, 9, 'Delivered', 1, '2026-03-18 14:17:19', '2026-03-18 14:18:14', 1, 1, 1855, 2325);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `dish_id` int(11) DEFAULT NULL,
  `rating` int(11) NOT NULL,
  `review_text` text DEFAULT NULL,
  `review_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `order_id`, `customer_id`, `dish_id`, `rating`, `review_text`, `review_date`) VALUES
(1, 1, 10, 1, 5, 'This biryani was the best I\'ve ever had!!', '2026-01-24 13:35:04'),
(2, 1, 10, 3, 5, 'Delicious!!', '2026-01-24 13:35:04'),
(3, 1, 10, NULL, 5, 'Delivery was on time!', '2026-01-24 13:35:04'),
(4, 2, 11, 128, 5, 'It was so good!!', '2026-01-24 14:03:03'),
(5, 2, 11, NULL, 5, 'Delivery was on time!!', '2026-01-24 14:03:03'),
(6, 3, 10, 7, 5, 'Very good', '2026-03-18 09:50:35'),
(7, 3, 10, 161, 5, 'Yummy', '2026-03-18 09:50:35'),
(8, 3, 10, NULL, 5, 'Perfect delivery!!', '2026-03-18 09:50:35'),
(9, 4, 10, 165, 5, 'delicious', '2026-03-18 10:43:10'),
(10, 4, 10, 167, 5, 'yummy', '2026-03-18 10:43:10'),
(11, 4, 10, NULL, 5, 'great', '2026-03-18 10:43:10'),
(12, 6, 10, 173, 5, 'delicious', '2026-03-18 12:52:50'),
(13, 6, 10, 174, 4, 'a bit too salty', '2026-03-18 12:52:50'),
(14, 6, 10, NULL, 5, 'great', '2026-03-18 12:52:50'),
(15, 7, 10, 180, 4, 'delicious', '2026-03-18 14:18:36'),
(16, 7, 10, NULL, 5, 'great', '2026-03-18 14:18:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('admin','customer','rider') DEFAULT NULL,
  `rider_status` enum('active','inactive') DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password_hash`, `phone`, `role`, `rider_status`, `address`) VALUES
(8, 'Admin1', 'admin1@gmail.com', '$2y$10$o5g5grSbHKHPdZZXEMv1VOJ5fcDfwcpUiYIE82G5B2O4XhccWnLva', NULL, 'admin', NULL, NULL),
(9, 'rider1', 'rider1@gmail.com', '$2y$10$kjCK6jRMpHgVlZmwlKNcNOyGKDMm.Z04stQRa0aNvHgps1zm1N6ce', NULL, 'rider', NULL, NULL),
(10, 'user1', 'user1@gmail.com', '$2y$10$O9STXSeZgGzMEHSMzDATyub6A9RRh/WmwJOjh7eH7OvZfOa6XjV6S', NULL, 'customer', NULL, NULL),
(11, 'User2', 'user2@gmail.com', '$2y$10$yPaPJEar64c9lV4pEH91vOepsBtUu33a5uk.aN2EjG.3YRnd6B2/G', NULL, 'customer', NULL, NULL),
(16, 'user5', 'user5@gmail.com', '$2y$10$HARkZ1Hv2juZrXa/2P7QxOaSOCBqtI8uG6eQXCHcArryWAWTULAP2', NULL, 'customer', NULL, NULL),
(17, 'user3', 'user3@gmail.com', '$2y$10$0l.FimAzXRaGLA.i5eXsVOmVXGii29vThCY/y/pHS1rn.cMxdVTlu', NULL, 'customer', NULL, NULL),
(18, 'user6', 'user6@gmail.com', '$2y$10$UuMoiZRmPtXB73grNBwvwu0EXNnjbum5kjto1kncuwsdgreKJr6cG', NULL, 'customer', NULL, NULL),
(19, 'user10', 'user10@gmail.com', '$2y$10$s6JRUi6TnRprg8gziRzsNuawWV8twOS48kVvE92nlk3UHfFAGRLuW', NULL, 'customer', NULL, NULL),
(21, 'user4', 'user4@gmail.com', '$2y$10$67XgSK7WDf66q0Vpb2XsGuxYqOX3yQBSk4zvA/LvYGl/R9bX6y73e', NULL, 'customer', NULL, NULL),
(22, 'user11', 'user11@gmail.com', '$2y$10$uF8.abVqR4BqsuuMJzkmi.h3TRyo934jEBHjlxY/AjAUxnvXEOehO', NULL, 'customer', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customeraddresses`
--
ALTER TABLE `customeraddresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`discount_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`dish_id`);

--
-- Indexes for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `rider_id` (`rider_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `dish_id` (`dish_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customeraddresses`
--
ALTER TABLE `customeraddresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `discount_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `dish_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=181;

--
-- AUTO_INCREMENT for table `orderitems`
--
ALTER TABLE `orderitems`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customeraddresses`
--
ALTER TABLE `customeraddresses`
  ADD CONSTRAINT `customeraddresses_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `discounts`
--
ALTER TABLE `discounts`
  ADD CONSTRAINT `discounts_ibfk_1` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`);

--
-- Constraints for table `orderitems`
--
ALTER TABLE `orderitems`
  ADD CONSTRAINT `orderitems_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `orderitems_ibfk_2` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`rider_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`address_id`) REFERENCES `customeraddresses` (`address_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`dish_id`) REFERENCES `dishes` (`dish_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
