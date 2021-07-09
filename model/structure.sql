-- phpMyAdmin SQL Dump
-- version 4.9.7
-- https://www.phpmyadmin.net/
--
-- Host: s334.loopia.se
-- Generation Time: Jul 09, 2021 at 02:52 PM
-- Server version: 10.3.29-MariaDB-log
-- PHP Version: 7.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reset_vidde_org`
--

-- --------------------------------------------------------

--
-- Table structure for table `timer`
--

CREATE TABLE IF NOT EXISTS `timer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created` timestamp NOT NULL DEFAULT current_timestamp(),
  `name` varchar(64) COLLATE utf8_bin NOT NULL,
  `description` mediumtext COLLATE utf8_bin DEFAULT NULL,
  `seconds_between` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Table structure for table `timer_press`
--

CREATE TABLE IF NOT EXISTS `timer_press` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timer` int(11) NOT NULL,
  `pressed_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timer_press_ibfk_1` (`timer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `timer_press`
--
ALTER TABLE `timer_press`
  ADD CONSTRAINT `timer_press_ibfk_1` FOREIGN KEY (`timer`) REFERENCES `timer` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
