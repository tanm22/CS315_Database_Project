
-- --------------------------------------------------------
-- Table structure for table `admins`
-- --------------------------------------------------------
CREATE TABLE `admins` (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(20) NOT NULL,
    `password` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

INSERT INTO `admins` (`id`, `name`, `password`)
VALUES
    (1, 'admin', '6216f8a75fd5bb3d5f22b6f9958cdede3fc086c2');

-- --------------------------------------------------------
-- Table structure for table `users_contact`
-- --------------------------------------------------------
CREATE TABLE `users_contact` (
    `email` VARCHAR(100) NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `number` VARCHAR(12) NOT NULL,
    `address` VARCHAR(500) NOT NULL,
    PRIMARY KEY (`email`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
CREATE TABLE `users` (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(20) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`email`) REFERENCES `users_contact` (`email`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
CREATE TABLE `products` (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `details` VARCHAR(500) NOT NULL,
    `price` INT(10) NOT NULL,
    `image_01` VARCHAR(100) NOT NULL,
    `image_02` VARCHAR(100) NOT NULL,
    `image_03` VARCHAR(100) NOT NULL,
    `quantity` INT(100) NOT NULL CHECK (`quantity` >= 0),
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `cart`
-- --------------------------------------------------------
CREATE TABLE `cart` (
    `user_id` INT(100) NOT NULL,
    `pid` INT(100) NOT NULL,
    `quantity` INT(10) NOT NULL,
    PRIMARY KEY (`user_id`, `pid`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`pid`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `messages`
-- --------------------------------------------------------
CREATE TABLE `messages` (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `user_id` INT(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `message` VARCHAR(500) NOT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_msg_user` (`user_id`),
    CONSTRAINT `fk_msg_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;


-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------
CREATE TABLE `orders` (
    `id` INT(100) NOT NULL AUTO_INCREMENT,
    `user_id` INT(100) NOT NULL,
    `method` VARCHAR(50) NOT NULL,
    `total_price` INT(100) NOT NULL,
    `placed_on` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    `payment_status` VARCHAR(20) NOT NULL DEFAULT 'pending',
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `order_items`
-- --------------------------------------------------------
CREATE TABLE `order_items` (
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL,
    PRIMARY KEY (`order_id`, `product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `wishlist`
-- --------------------------------------------------------
CREATE TABLE `wishlist` (
    `user_id` INT(100) NOT NULL,
    `pid` INT(100) NOT NULL,
    PRIMARY KEY (`user_id`, `pid`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`pid`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- Table structure for table `reviews`
-- --------------------------------------------------------
CREATE TABLE `reviews` (
    `user_id` INT(100) NOT NULL,
    `pid` INT(100) NOT NULL,
    `review` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`user_id`, `pid`),
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`pid`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
