CREATE DATABASE IF NOT EXISTS `shortener`;
USE `shortener`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- 생성 시간: 25-10-12 20:08
-- 서버 버전: 10.11.6-MariaDB
-- PHP 버전: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `shortener`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `links`
--

CREATE TABLE `links` (
  `id` int(10) UNSIGNED NOT NULL,
  `long_url` text NOT NULL,
  `code` varchar(64) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  `click_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `last_clicked` datetime DEFAULT NULL,
  `creator_ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- 테이블의 덤프 데이터 `links`
--

INSERT INTO `links` (`id`, `long_url`, `code`, `title`, `created_at`, `expires_at`, `click_count`, `last_clicked`, `creator_ip`, `user_agent`) VALUES
(125050, 'https://www.youtube.com/watch?v=Gasp-nba1es', 'DRVFG', NULL, '2025-09-28 01:50:58', NULL, 1, '2025-09-28 02:26:01', '192.168.50.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; ko-KR) WindowsPowerShell/5.1.26100.6584'),
(125051, 'https://torrentqq388.com/torrent/utl.html', 'ejIKT', NULL, '2025-10-05 13:23:32', NULL, 0, NULL, '192.168.50.1', 'Mozilla/5.0 (Windows NT; Windows NT 10.0; ko-KR) WindowsPowerShell/5.1.22621.5909');

--
-- 덤프된 테이블의 인덱스
--

--
-- 테이블의 인덱스 `links`
--
ALTER TABLE `links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `u_code` (`code`),
  ADD KEY `idx_created` (`created_at`);

--
-- 덤프된 테이블의 AUTO_INCREMENT
--

--
-- 테이블의 AUTO_INCREMENT `links`
--
ALTER TABLE `links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125052;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;