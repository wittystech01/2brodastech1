-- ============================================================
-- GadgetZone - Database Schema
-- Database: gadgetzone
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS `gadgetzone`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `gadgetzone`;

-- ============================================================
-- SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`        VARCHAR(100) NOT NULL UNIQUE,
    `value`      TEXT,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `settings` (`key`, `value`) VALUES
('site_name', 'GadgetZone'),
('site_email', 'info@gadgetzone.com'),
('currency', '₦'),
('currency_code', 'NGN'),
('free_shipping_threshold', '50000'),
('shipping_fee', '1500'),
('tax_rate', '0'),
('maintenance_mode', '0'),
('meta_title', 'GadgetZone - Your #1 Online Gadget Store'),
('meta_description', 'Shop the latest smartphones, laptops, accessories and gadgets at the best prices in Nigeria.');

-- ============================================================
-- ADMINS
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) NOT NULL UNIQUE,
    `password`   VARCHAR(255) NOT NULL,
    `role`       ENUM('superadmin','admin','manager') NOT NULL DEFAULT 'admin',
    `avatar`     VARCHAR(255) DEFAULT NULL,
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `last_login` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin: password = 'admin123' (change immediately)
INSERT INTO `admins` (`name`, `email`, `password`, `role`) VALUES
('Super Admin', 'admin@gadgetzone.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin');

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `first_name`       VARCHAR(80) NOT NULL,
    `last_name`        VARCHAR(80) NOT NULL,
    `email`            VARCHAR(150) NOT NULL UNIQUE,
    `phone`            VARCHAR(20) DEFAULT NULL,
    `password`         VARCHAR(255) NOT NULL,
    `avatar`           VARCHAR(255) DEFAULT NULL,
    `email_verified`   TINYINT(1) NOT NULL DEFAULT 0,
    `verify_token`     VARCHAR(100) DEFAULT NULL,
    `reset_token`      VARCHAR(100) DEFAULT NULL,
    `reset_expires`    DATETIME DEFAULT NULL,
    `status`           ENUM('active','inactive','banned') NOT NULL DEFAULT 'active',
    `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- USER ADDRESSES
-- ============================================================
CREATE TABLE IF NOT EXISTS `user_addresses` (
    `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`      INT UNSIGNED NOT NULL,
    `label`        VARCHAR(50) NOT NULL DEFAULT 'Home',
    `full_name`    VARCHAR(150) NOT NULL,
    `phone`        VARCHAR(20) NOT NULL,
    `address`      TEXT NOT NULL,
    `city`         VARCHAR(100) NOT NULL,
    `state`        VARCHAR(100) NOT NULL,
    `country`      VARCHAR(100) NOT NULL DEFAULT 'Nigeria',
    `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
    `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_addr_user` (`user_id`),
    CONSTRAINT `fk_addr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CATEGORIES
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(120) NOT NULL UNIQUE,
    `icon`        VARCHAR(10) DEFAULT '📦',
    `image`       VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `parent_id`   INT UNSIGNED DEFAULT NULL,
    `sort_order`  INT NOT NULL DEFAULT 0,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `fk_cat_parent` (`parent_id`),
    CONSTRAINT `fk_cat_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categories` (`name`, `slug`, `icon`, `sort_order`) VALUES
('Smartphones', 'smartphones', '📱', 1),
('Laptops', 'laptops', '💻', 2),
('Tablets', 'tablets', '📲', 3),
('Audio', 'audio', '🎧', 4),
('Smart Watches', 'smart-watches', '⌚', 5),
('Cameras', 'cameras', '📷', 6),
('Gaming', 'gaming', '🎮', 7),
('Accessories', 'accessories', '🔌', 8);

-- ============================================================
-- PRODUCTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `category_id`   INT UNSIGNED DEFAULT NULL,
    `name`          VARCHAR(255) NOT NULL,
    `slug`          VARCHAR(280) NOT NULL UNIQUE,
    `sku`           VARCHAR(80) DEFAULT NULL UNIQUE,
    `description`   LONGTEXT DEFAULT NULL,
    `short_desc`    TEXT DEFAULT NULL,
    `price`         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `sale_price`    DECIMAL(12,2) DEFAULT NULL,
    `cost_price`    DECIMAL(12,2) DEFAULT NULL,
    `stock`         INT NOT NULL DEFAULT 0,
    `low_stock_threshold` INT NOT NULL DEFAULT 5,
    `weight`        DECIMAL(8,2) DEFAULT NULL COMMENT 'kg',
    `image`         VARCHAR(255) DEFAULT NULL,
    `gallery`       JSON DEFAULT NULL,
    `specs`         JSON DEFAULT NULL,
    `brand`         VARCHAR(100) DEFAULT NULL,
    `featured`      TINYINT(1) NOT NULL DEFAULT 0,
    `is_new`        TINYINT(1) NOT NULL DEFAULT 0,
    `is_hot`        TINYINT(1) NOT NULL DEFAULT 0,
    `views`         INT UNSIGNED NOT NULL DEFAULT 0,
    `sold_count`    INT UNSIGNED NOT NULL DEFAULT 0,
    `rating`        DECIMAL(3,2) NOT NULL DEFAULT 0.00,
    `review_count`  INT UNSIGNED NOT NULL DEFAULT 0,
    `meta_title`    VARCHAR(255) DEFAULT NULL,
    `meta_desc`     TEXT DEFAULT NULL,
    `status`        ENUM('active','inactive','draft') NOT NULL DEFAULT 'active',
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `idx_status` (`status`),
    KEY `fk_prod_cat` (`category_id`),
    FULLTEXT KEY `ft_search` (`name`, `description`),
    CONSTRAINT `fk_prod_cat` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PRODUCT REVIEWS
-- ============================================================
CREATE TABLE IF NOT EXISTS `reviews` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED DEFAULT NULL,
    `name`       VARCHAR(100) NOT NULL,
    `email`      VARCHAR(150) DEFAULT NULL,
    `rating`     TINYINT UNSIGNED NOT NULL DEFAULT 5,
    `title`      VARCHAR(200) DEFAULT NULL,
    `body`       TEXT NOT NULL,
    `verified`   TINYINT(1) NOT NULL DEFAULT 0,
    `status`     ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_rev_product` (`product_id`),
    KEY `fk_rev_user` (`user_id`),
    CONSTRAINT `fk_rev_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rev_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BANNERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `banners` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `title`      VARCHAR(200) NOT NULL,
    `subtitle`   VARCHAR(300) DEFAULT NULL,
    `image`      VARCHAR(255) NOT NULL,
    `link`       VARCHAR(500) DEFAULT NULL,
    `position`   ENUM('home','shop','sidebar','popup') NOT NULL DEFAULT 'home',
    `sort_order` INT NOT NULL DEFAULT 0,
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `starts_at`  DATETIME DEFAULT NULL,
    `ends_at`    DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_position_status` (`position`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CHANNELS (for videos)
-- ============================================================
CREATE TABLE IF NOT EXISTS `channels` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150) NOT NULL,
    `slug`       VARCHAR(180) NOT NULL UNIQUE,
    `logo`       VARCHAR(255) DEFAULT NULL,
    `url`        VARCHAR(500) DEFAULT NULL,
    `status`     ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VIDEOS
-- ============================================================
CREATE TABLE IF NOT EXISTS `videos` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `channel_id`  INT UNSIGNED DEFAULT NULL,
    `title`       VARCHAR(255) NOT NULL,
    `slug`        VARCHAR(280) NOT NULL UNIQUE,
    `thumbnail`   VARCHAR(255) DEFAULT NULL,
    `video_url`   VARCHAR(500) NOT NULL,
    `embed_code`  TEXT DEFAULT NULL,
    `duration`    VARCHAR(20) DEFAULT NULL,
    `views`       INT UNSIGNED NOT NULL DEFAULT 0,
    `description` TEXT DEFAULT NULL,
    `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_vid_channel` (`channel_id`),
    CONSTRAINT `fk_vid_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- COUPONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `coupons` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`            VARCHAR(50) NOT NULL UNIQUE,
    `type`            ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage',
    `value`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `min_order`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `max_discount`    DECIMAL(12,2) DEFAULT NULL,
    `usage_limit`     INT DEFAULT NULL,
    `used_count`      INT NOT NULL DEFAULT 0,
    `per_user_limit`  INT NOT NULL DEFAULT 1,
    `starts_at`       DATETIME DEFAULT NULL,
    `expires_at`      DATETIME DEFAULT NULL,
    `status`          ENUM('active','inactive') NOT NULL DEFAULT 'active',
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ORDERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_number`    VARCHAR(30) NOT NULL UNIQUE,
    `user_id`         INT UNSIGNED DEFAULT NULL,
    `guest_email`     VARCHAR(150) DEFAULT NULL,
    `coupon_id`       INT UNSIGNED DEFAULT NULL,
    `coupon_code`     VARCHAR(50) DEFAULT NULL,
    `subtotal`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `discount`        DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `shipping_fee`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `tax`             DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `total`           DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    `currency`        VARCHAR(10) NOT NULL DEFAULT 'NGN',
    -- Shipping address snapshot
    `shipping_name`   VARCHAR(150) NOT NULL,
    `shipping_phone`  VARCHAR(20) NOT NULL,
    `shipping_email`  VARCHAR(150) DEFAULT NULL,
    `shipping_address` TEXT NOT NULL,
    `shipping_city`   VARCHAR(100) NOT NULL,
    `shipping_state`  VARCHAR(100) NOT NULL,
    `shipping_country` VARCHAR(100) NOT NULL DEFAULT 'Nigeria',
    -- Status
    `order_status`    ENUM('pending','confirmed','processing','shipped','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending',
    `payment_status`  ENUM('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
    `payment_method`  VARCHAR(50) DEFAULT NULL,
    `payment_ref`     VARCHAR(200) DEFAULT NULL,
    `tracking_number` VARCHAR(100) DEFAULT NULL,
    `notes`           TEXT DEFAULT NULL,
    `admin_notes`     TEXT DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_order_number` (`order_number`),
    KEY `fk_order_user` (`user_id`),
    CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ORDER ITEMS
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`    INT UNSIGNED NOT NULL,
    `product_id`  INT UNSIGNED DEFAULT NULL,
    `name`        VARCHAR(255) NOT NULL,
    `sku`         VARCHAR(80) DEFAULT NULL,
    `image`       VARCHAR(255) DEFAULT NULL,
    `price`       DECIMAL(12,2) NOT NULL,
    `quantity`    INT UNSIGNED NOT NULL DEFAULT 1,
    `subtotal`    DECIMAL(12,2) NOT NULL,
    `options`     JSON DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_item_order` (`order_id`),
    KEY `fk_item_product` (`product_id`),
    CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ORDER STATUS HISTORY
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_history` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`   INT UNSIGNED NOT NULL,
    `status`     VARCHAR(50) NOT NULL,
    `comment`    TEXT DEFAULT NULL,
    `notify`     TINYINT(1) NOT NULL DEFAULT 0,
    `created_by` INT UNSIGNED DEFAULT NULL COMMENT 'admin id',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_hist_order` (`order_id`),
    CONSTRAINT `fk_hist_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- WISHLIST
-- ============================================================
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED NOT NULL,
    `product_id` INT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
    KEY `fk_wish_product` (`product_id`),
    CONSTRAINT `fk_wish_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wish_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NEWSLETTER SUBSCRIBERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `newsletter` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email`       VARCHAR(150) NOT NULL UNIQUE,
    `name`        VARCHAR(100) DEFAULT NULL,
    `status`      ENUM('subscribed','unsubscribed') NOT NULL DEFAULT 'subscribed',
    `token`       VARCHAR(100) DEFAULT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CONTACT MESSAGES
-- ============================================================
CREATE TABLE IF NOT EXISTS `contact_messages` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(150) NOT NULL,
    `email`      VARCHAR(150) NOT NULL,
    `phone`      VARCHAR(20) DEFAULT NULL,
    `subject`    VARCHAR(255) DEFAULT NULL,
    `message`    TEXT NOT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `replied_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BLOG POSTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `blog_posts` (
    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `admin_id`    INT UNSIGNED DEFAULT NULL,
    `title`       VARCHAR(255) NOT NULL,
    `slug`        VARCHAR(280) NOT NULL UNIQUE,
    `excerpt`     TEXT DEFAULT NULL,
    `content`     LONGTEXT NOT NULL,
    `image`       VARCHAR(255) DEFAULT NULL,
    `category`    VARCHAR(100) DEFAULT NULL,
    `tags`        JSON DEFAULT NULL,
    `views`       INT UNSIGNED NOT NULL DEFAULT 0,
    `meta_title`  VARCHAR(255) DEFAULT NULL,
    `meta_desc`   TEXT DEFAULT NULL,
    `status`      ENUM('published','draft') NOT NULL DEFAULT 'draft',
    `published_at` DATETIME DEFAULT NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_slug` (`slug`),
    KEY `fk_blog_admin` (`admin_id`),
    CONSTRAINT `fk_blog_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PRODUCT VIEWS LOG (for analytics)
-- ============================================================
CREATE TABLE IF NOT EXISTS `product_views` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` INT UNSIGNED NOT NULL,
    `user_id`    INT UNSIGNED DEFAULT NULL,
    `ip`         VARCHAR(45) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_pv_product` (`product_id`),
    CONSTRAINT `fk_pv_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`    INT UNSIGNED DEFAULT NULL COMMENT 'NULL = admin notification',
    `type`       VARCHAR(80) NOT NULL,
    `title`      VARCHAR(200) NOT NULL,
    `body`       TEXT DEFAULT NULL,
    `link`       VARCHAR(500) DEFAULT NULL,
    `is_read`    TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `fk_notif_user` (`user_id`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
