-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Feb 10, 2013 at 12:08 PM
-- Server version: 5.5.27
-- PHP Version: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `movieaster`
--

-- --------------------------------------------------------

--
-- Table structure for table `movie`
--

CREATE TABLE IF NOT EXISTS `movie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path_id` int(11) DEFAULT NULL,
  `folder_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `updated` tinyint(1) NOT NULL,
  `found` tinyint(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alternative_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `released` date NOT NULL,
  `overview` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `imdb` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tmdb` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `homepage` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `trailer` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tmdb_rating` int(11) NOT NULL,
  `tmdb_votes` int(11) NOT NULL,
  `genres` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `directors` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `writers` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `actors` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `thumb_inline` longtext COLLATE utf8_unicode_ci NOT NULL,
  `poster` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `backdrop1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `backdrop2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `backdrop3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `watched` tinyint(1) NOT NULL,
  `favorites` tinyint(1) NOT NULL,
  `archived` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_DC9FDD6BD96C566B` (`path_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `path`
--

CREATE TABLE IF NOT EXISTS `path` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE IF NOT EXISTS `wishlist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `original_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alternative_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `released` date NOT NULL,
  `overview` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `imdb` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tmdb` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `homepage` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `trailer` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `tmdb_rating` int(11) NOT NULL,
  `tmdb_votes` int(11) NOT NULL,
  `genres` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `directors` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `writers` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `actors` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `thumb` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `thumb_inline` longtext COLLATE utf8_unicode_ci NOT NULL,
  `poster` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `backdrop1` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `backdrop2` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `backdrop3` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `movie`
--
ALTER TABLE `movie`
  ADD CONSTRAINT `FK_DC9FDD6BD96C566B` FOREIGN KEY (`path_id`) REFERENCES `path` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
