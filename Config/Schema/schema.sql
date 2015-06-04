-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2015 at 05:57 PM
-- Server version: 5.6.17
-- PHP Version: 5.5.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `live_editor`
--

-- --------------------------------------------------------

--
-- Table structure for table `ftp_accounts`
--

CREATE TABLE IF NOT EXISTS `ftp_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `ftp_host` varchar(256) NOT NULL,
  `ftp_user` varchar(256) NOT NULL,
  `ftp_pass` varchar(256) NOT NULL,
  `ftp_path` varchar(256) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `ftp_accounts`
--

INSERT INTO `ftp_accounts` (`id`, `name`, `ftp_host`, `ftp_user`, `ftp_pass`, `ftp_path`) VALUES
(10, '127.0.0.1', '127.0.0.1', 'TEST', 'TEST', 'TEST');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE IF NOT EXISTS `jobs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `url` varchar(256) NOT NULL,
  `default_uri` text NOT NULL,
  `stylesheet_uri_root` varchar(255) NOT NULL,
  `stylesheet_uri` varchar(256) NOT NULL,
  `preprocessor_uri_root` varchar(255) NOT NULL,
  `preprocessor_uri` varchar(256) NOT NULL,
  `ftp_account_id` int(11) NOT NULL,
  `css_mode` int(11) NOT NULL DEFAULT '1',
  `codebase_config` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=16 ;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `project_id`, `name`, `description`, `url`, `default_uri`, `stylesheet_uri_root`, `stylesheet_uri`, `preprocessor_uri_root`, `preprocessor_uri`, `ftp_account_id`, `css_mode`, `codebase_config`) VALUES
(14, 1, 'TEST CSS', 'A test site using CSS', 'live_editor/bootstrap_tests/jumbotron/', 'index.htm', 'http://127.0.0.1/live_editor/bootstrap_tests/jumbotron/files', 'custom.css', '', '', 10, 1, ''),
(15, 1, 'TEST LESS', 'A test site using LESS', 'live_editor/bootstrap_tests/jumbotron_less/', 'index.htm', 'http://127.0.0.1/live_editor/bootstrap_tests/jumbotron_less/files', 'custom.css', 'http://127.0.0.1/live_editor/bootstrap_tests/jumbotron_less/files', 'custom.less', 10, 3, '');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE IF NOT EXISTS `projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL,
  `modified` datetime NOT NULL,
  `sort` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `created`, `modified`, `sort`, `user_id`) VALUES
(1, 'My first project', 'My first project description', '2014-10-22 00:00:00', '2014-10-22 00:00:00', 1, 1);
