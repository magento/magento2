-- phpMyAdmin SQL Dump
-- version 2.11.6
-- http://www.phpmyadmin.net
--
-- Host: localhost:3306
-- Generation Time: Jun 19, 2008 at 08:09 PM
-- Server version: 5.0.51
-- PHP Version: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*
Sample grant for MySQL

GRANT DELETE, INSERT, SELECT, UPDATE ON queue.* TO 'queue'@'127.0.0.1' IDENTIFIED BY '[CHANGE ME]';
mysql -u queue -h 127.0.0.1 -p queue

mysqlaccess queue queue --superuser=root -H 127.0.0.1


*/


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `queue`
--

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

DROP TABLE IF EXISTS `message`;
CREATE TABLE IF NOT EXISTS `message` (
  `message_id` bigint(20) unsigned NOT NULL auto_increment,
  `queue_id` int(10) unsigned NOT NULL,
  `handle` char(32) default NULL,
  `body` varchar(8192) NOT NULL,
  `md5` char(32) NOT NULL,
  `timeout` decimal(14,4) unsigned default NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`message_id`),
  UNIQUE KEY `message_handle` (`handle`),
  KEY `message_queueid` (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
CREATE TABLE IF NOT EXISTS `queue` (
  `queue_id` int(10) unsigned NOT NULL auto_increment,
  `queue_name` varchar(100) NOT NULL,
  `timeout` smallint(5) unsigned NOT NULL default '30',
  PRIMARY KEY  (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `queue` (`queue_id`) ON DELETE CASCADE ON UPDATE CASCADE;
