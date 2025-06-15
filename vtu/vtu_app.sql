-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 03:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `vtu_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `chatbot_intents`
--

CREATE TABLE `chatbot_intents` (
  `id` int(11) NOT NULL,
  `intent_name` varchar(100) NOT NULL,
  `training_phrases` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`training_phrases`)),
  `response_template` text NOT NULL,
  `requires_human` tinyint(1) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `sender` enum('user','bot','agent') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_sessions`
--

CREATE TABLE `chat_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_token` varchar(64) NOT NULL,
  `status` enum('active','resolved','transferred') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `crypto_payments`
--

CREATE TABLE `crypto_payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount_ngn` decimal(16,8) NOT NULL,
  `amount_crypto` decimal(16,8) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `wallet_address` varchar(100) NOT NULL,
  `tx_hash` varchar(100) DEFAULT NULL,
  `status` enum('pending','confirmed','failed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `crypto_payments`
--

INSERT INTO `crypto_payments` (`id`, `user_id`, `amount_ngn`, `amount_crypto`, `currency`, `wallet_address`, `tx_hash`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 10000.00000000, 0.00040000, 'BTC', '317c7622ab4963166a18ea03d07a6abd324060cfb', NULL, 'pending', '2025-06-11 03:20:04', '2025-06-11 03:20:04'),
(2, 1, 99999999.99999999, 8.00000000, 'BTC', '3928c089062ca73600f9dd3e8b5e3efffe1916050', NULL, 'pending', '2025-06-11 04:12:12', '2025-06-11 04:12:12'),
(3, 1, 90000.00000000, 0.00360000, 'BTC', '3641477dc6fe34bd389c66c6a4ea340c0887a5bf6', NULL, 'pending', '2025-06-12 11:40:19', '2025-06-12 11:40:19');

-- --------------------------------------------------------

--
-- Table structure for table `discount_rules`
--

CREATE TABLE `discount_rules` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `service_type` enum('airtime','data','electricity','cable') NOT NULL,
  `network` varchar(50) DEFAULT NULL,
  `min_amount` decimal(10,2) DEFAULT 0.00,
  `discount_type` enum('percentage','fixed') NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disputes`
--

CREATE TABLE `disputes` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('open','investigating','resolved','rejected') DEFAULT 'open',
  `resolution` text DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dispute_comments`
--

CREATE TABLE `dispute_comments` (
  `id` int(11) NOT NULL,
  `dispute_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `is_internal` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fraud_flags`
--

CREATE TABLE `fraud_flags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `metadata` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kyc_verifications`
--

CREATE TABLE `kyc_verifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `document_type` enum('nin','driver_license','voter_id','passport') NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `front_image` varchar(255) NOT NULL,
  `back_image` varchar(255) DEFAULT NULL,
  `selfie_image` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

CREATE TABLE `promo_codes` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `bonus_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_usages`
--

CREATE TABLE `promo_usages` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `promo_id` int(11) NOT NULL,
  `used_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `referrer_id` int(11) NOT NULL,
  `referred_id` int(11) NOT NULL,
  `bonus_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','completed') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `vtu_markup_percentage` decimal(5,2) DEFAULT 0.00,
  `min_wallet_fund` decimal(12,2) DEFAULT 100.00,
  `max_wallet_fund` decimal(12,2) DEFAULT 50000.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `wallet_balance` decimal(12,2) DEFAULT 0.00,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(255) DEFAULT NULL,
  `referral_code` varchar(10) DEFAULT NULL,
  `referral_balance` decimal(10,2) DEFAULT 0.00,
  `kyc_verified` tinyint(1) DEFAULT 0,
  `kyc_verified_at` timestamp NULL DEFAULT NULL,
  `transaction_limit` decimal(12,2) DEFAULT 50000.00,
  `email_verification_code` varchar(64) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `notify_email` tinyint(1) DEFAULT 1,
  `notify_sms` tinyint(1) DEFAULT 0,
  `dark_mode` tinyint(1) DEFAULT 0,
  `enable_2fa` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `wallet_balance`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`, `two_factor_enabled`, `two_factor_secret`, `referral_code`, `referral_balance`, `kyc_verified`, `kyc_verified_at`, `transaction_limit`, `email_verification_code`, `avatar`, `notify_email`, `notify_sms`, `dark_mode`, `enable_2fa`) VALUES
(1, 'Abdulrahman', 'kuyabatech@gmail.com', '07011632616', '$2y$10$uWfOGHK1o260PT9uE1ouSutuPaodXNlNwmbc3gCWzLFm29L.QAsBi', 0.00, NULL, '381855fea7902d4aa865ba3a2cd2dd1f4c8ca1244bb7a6f301c854c82da0a967', '2025-06-10 21:26:36', '2025-06-12 11:44:36', 0, NULL, 'XO8279A3', 0.00, 0, NULL, 50000.00, '2071eae3e90675d0c04afd84cef24e30', 'assets/images/avatars/avatar_1_1749728543.png', 1, 1, 0, 1),
(2, 'ABDULRAHMAN Yakubu shaba', 'kuyabetech1@gmail.com', '09063757333', '$2y$10$.YzColX82eF5sy3AbmofDOGPzotjr84VBTLmNlp/o3HYSxrObKgU2', 0.00, NULL, NULL, '2025-06-10 21:59:39', '2025-06-10 21:59:39', 0, NULL, 'B5KSXMTB', 0.00, 0, NULL, 50000.00, NULL, NULL, 1, 0, 0, 0),
(3, 'ABDULKARIM ABDULLAHI', 'kuyabe3232@gmail.com', '09127951634', '$2y$10$MvLhhMGqaTvOtHW6hoi.V.uMnLr6dZHgUCGU6j1l0LG.rhIDljLWy', 0.00, NULL, NULL, '2025-06-11 07:56:10', '2025-06-11 15:51:31', 0, NULL, '1SOBPUD1', 0.00, 0, NULL, 50000.00, 'c98f0d31ae8f01193ee08139cd8d0962', NULL, 1, 0, 0, 0),
(4, 'ABDULLAHI ABDULKARIM', 'kverifydigitalsolutions@gmail.com', '09115572510', '$2y$10$kPQFjxjE//ZbInJVsigLIuHuJ4v08voEE9SY0SKGkCDPuiwP10Qau', 0.00, NULL, NULL, '2025-06-12 05:19:27', '2025-06-12 05:19:27', 0, NULL, '435Z8G5M', 0.00, 0, NULL, 50000.00, NULL, NULL, 1, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_agent` varchar(255) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `first_seen` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vtu_transactions`
--

CREATE TABLE `vtu_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `service_type` enum('airtime','data','electricity','cable') NOT NULL,
  `network` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `status` enum('pending','successful','failed') DEFAULT 'pending',
  `api_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vtu_transactions`
--

INSERT INTO `vtu_transactions` (`id`, `user_id`, `service_type`, `network`, `phone`, `amount`, `reference`, `status`, `api_response`, `created_at`) VALUES
(1, 1, 'airtime', 'MTN', '09034095385', 100.00, 'VTU_1749608606_6848e89e7e81a', 'failed', 'null', '2025-06-11 02:23:26'),
(2, 1, 'airtime', 'MTN', '09034095385', 100.00, 'VTU_1749608630_6848e8b62a748', 'failed', 'null', '2025-06-11 02:23:50'),
(3, 1, 'airtime', 'MTN', '09034095385', 100.00, 'VTU_1749608720_6848e9106f9c1', 'failed', 'null', '2025-06-11 02:25:20'),
(4, 1, 'airtime', 'MTN', '09034095385', 100.00, 'VTU_1749608782_6848e94e2d067', 'failed', 'null', '2025-06-11 02:26:22'),
(5, 1, 'airtime', 'MTN', '09034095385', 100.00, 'VTU_1749608793_6848e9598fc14', 'failed', 'null', '2025-06-11 02:26:33'),
(6, 1, 'airtime', 'MTN', '09034095385', 1000.00, 'VTU_1749608930_6848e9e2c2db7', 'failed', 'null', '2025-06-11 02:28:50'),
(7, 1, 'airtime', 'MTN', '09034095385', 100.00, 'VTU_1749610335_6848ef5f1eb81', 'failed', 'null', '2025-06-11 02:52:15'),
(8, 1, 'airtime', 'GLO', '09034095385', 100.00, 'VTU_1749610761_6848f1092852b', 'failed', 'null', '2025-06-11 02:59:21'),
(9, 1, 'airtime', 'AIRTEL', '07011632616', 200.00, 'VTU_1749611723_6848f4cb598cb', 'failed', 'null', '2025-06-11 03:15:23'),
(10, 1, '', 'eko', '9030598555', 1000.00, 'BILL_1749615689_68490449eaf09', 'successful', NULL, '2025-06-11 04:21:30'),
(11, 1, '', 'eko', '9030598555', 1000.00, 'BILL_1749615700_68490454350d7', 'successful', NULL, '2025-06-11 04:21:40'),
(12, 1, 'airtime', '9MOBILE', '07044429281', 20000.00, 'VTU_1749627446_6849323613964', 'failed', 'null', '2025-06-11 07:37:26'),
(13, 1, 'airtime', '9MOBILE', '07044429281', 20000.00, 'VTU_1749627637_684932f536dac', 'failed', 'null', '2025-06-11 07:40:37'),
(14, 1, 'airtime', '9MOBILE', '07044429281', 20000.00, 'VTU_1749629248_68493940ae7cb', 'failed', 'null', '2025-06-11 08:07:28'),
(15, 1, 'airtime', 'MTN', '09063757333', 10000.00, 'VTU_1749629280_68493960c951d', 'failed', 'null', '2025-06-11 08:08:00'),
(16, 1, 'airtime', 'AIRTEL', '07044429281', 100.00, 'VTU_1749656857_6849a519cbe83', 'failed', 'null', '2025-06-11 15:47:38'),
(17, 1, 'airtime', 'AIRTEL', '07044429281', 100.00, 'VTU_1749657132_6849a62c6c363', 'failed', 'null', '2025-06-11 15:52:12'),
(18, 1, 'airtime', 'MTN', '07044429281', 100.00, 'VTU_1749712099_684a7ce382f58', 'failed', 'null', '2025-06-12 07:08:19'),
(19, 1, 'airtime', 'MTN', '07044429281', 100.00, 'VTU_1749712125_684a7cfd77896', 'failed', 'null', '2025-06-12 07:08:45'),
(20, 1, '', 'ikeja', '9030598555', 90000.00, 'BILL_1749712199_684a7d47f0dcc', 'successful', NULL, '2025-06-12 07:09:59'),
(21, 1, 'airtime', 'AIRTEL', '07044429281', 100.00, 'VTU_1749712424_684a7e281ff72', 'failed', 'null', '2025-06-12 07:13:44'),
(22, 1, 'airtime', 'MTN', '07044429281', 100.00, 'VTU_1749724456_684aad284b256', 'failed', 'null', '2025-06-12 10:34:16'),
(23, 1, 'airtime', 'MTN', '07044429281', 100.00, 'VTU_1749724545_684aad81d592a', 'failed', 'null', '2025-06-12 10:35:45'),
(24, 1, 'airtime', 'MTN', '09063757333', 100.00, 'VTU_1749728349_684abc5deae3a', 'failed', 'null', '2025-06-12 11:39:10'),
(25, 1, '', 'ikeja', '9030598555', 200.00, 'BILL_1749728676_684abda47eb24', 'successful', NULL, '2025-06-12 11:44:36');

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `reference` varchar(100) NOT NULL,
  `status` enum('pending','successful','failed') DEFAULT 'pending',
  `payment_method` varchar(50) NOT NULL,
  `payment_provider` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chatbot_intents`
--
ALTER TABLE `chatbot_intents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `crypto_payments`
--
ALTER TABLE `crypto_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `discount_rules`
--
ALTER TABLE `discount_rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disputes`
--
ALTER TABLE `disputes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `dispute_comments`
--
ALTER TABLE `dispute_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dispute_id` (`dispute_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fraud_flags`
--
ALTER TABLE `fraud_flags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `promo_usages`
--
ALTER TABLE `promo_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promo_id` (`promo_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referrer_id` (`referrer_id`),
  ADD KEY `referred_id` (`referred_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `vtu_transactions`
--
ALTER TABLE `vtu_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `chatbot_intents`
--
ALTER TABLE `chatbot_intents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `crypto_payments`
--
ALTER TABLE `crypto_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `discount_rules`
--
ALTER TABLE `discount_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disputes`
--
ALTER TABLE `disputes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dispute_comments`
--
ALTER TABLE `dispute_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fraud_flags`
--
ALTER TABLE `fraud_flags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_codes`
--
ALTER TABLE `promo_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promo_usages`
--
ALTER TABLE `promo_usages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vtu_transactions`
--
ALTER TABLE `vtu_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `chat_sessions` (`id`);

--
-- Constraints for table `chat_sessions`
--
ALTER TABLE `chat_sessions`
  ADD CONSTRAINT `chat_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `crypto_payments`
--
ALTER TABLE `crypto_payments`
  ADD CONSTRAINT `crypto_payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `disputes`
--
ALTER TABLE `disputes`
  ADD CONSTRAINT `disputes_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `vtu_transactions` (`id`),
  ADD CONSTRAINT `disputes_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `disputes_ibfk_3` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `dispute_comments`
--
ALTER TABLE `dispute_comments`
  ADD CONSTRAINT `dispute_comments_ibfk_1` FOREIGN KEY (`dispute_id`) REFERENCES `disputes` (`id`),
  ADD CONSTRAINT `dispute_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `fraud_flags`
--
ALTER TABLE `fraud_flags`
  ADD CONSTRAINT `fraud_flags_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fraud_flags_ibfk_2` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `kyc_verifications`
--
ALTER TABLE `kyc_verifications`
  ADD CONSTRAINT `kyc_verifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `kyc_verifications_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `promo_usages`
--
ALTER TABLE `promo_usages`
  ADD CONSTRAINT `promo_usages_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `promo_codes` (`id`);

--
-- Constraints for table `referrals`
--
ALTER TABLE `referrals`
  ADD CONSTRAINT `referrals_ibfk_1` FOREIGN KEY (`referrer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `referrals_ibfk_2` FOREIGN KEY (`referred_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD CONSTRAINT `user_devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `vtu_transactions`
--
ALTER TABLE `vtu_transactions`
  ADD CONSTRAINT `vtu_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
