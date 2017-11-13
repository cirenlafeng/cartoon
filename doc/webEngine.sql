-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2017-06-20 13:43:25
-- 服务器版本： 5.6.29-log
-- PHP Version: 7.1.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `webEngine`
--
CREATE DATABASE IF NOT EXISTS `webEngine` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `webEngine`;

-- --------------------------------------------------------

--
-- 表的结构 `alert_log`
--

DROP TABLE IF EXISTS `alert_log`;
CREATE TABLE `alert_log` (
  `un_id` varchar(30) NOT NULL,
  `type` varchar(20) NOT NULL,
  `info` text NOT NULL,
  `add_time` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `articles`
--

DROP TABLE IF EXISTS `articles`;
CREATE TABLE `articles` (
  `urlID` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tag` tinyint(3) UNSIGNED NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `check` tinyint(3) DEFAULT '1',
  `domain` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `url` varchar(1000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keywords` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cate` smallint(5) UNSIGNED NOT NULL,
  `html` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `operatorID` smallint(5) UNSIGNED NOT NULL,
  `date` char(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `autoLog`
--

DROP TABLE IF EXISTS `autoLog`;
CREATE TABLE `autoLog` (
  `id` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `push_API_Log`
--

DROP TABLE IF EXISTS `push_API_Log`;
CREATE TABLE `push_API_Log` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `urlID` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` tinyint(3) UNSIGNED NOT NULL,
  `status` tinyint(3) UNSIGNED NOT NULL,
  `check` tinyint(3) DEFAULT '1',
  `url` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pushInfo` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `resultCode` tinyint(4) NOT NULL,
  `resultMsg` char(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operatorID` smallint(5) UNSIGNED NOT NULL,
  `date` char(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `push_Repeat`
--

DROP TABLE IF EXISTS `push_Repeat`;
CREATE TABLE `push_Repeat` (
  `titleID` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contentID` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `repeatTimes` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `dateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE `schedule` (
  `domain` char(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',
  `status` tinyint(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT '0未处理，1处理中，2处理完，3有坑',
  `operatorID` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `notes` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operationDate` date DEFAULT NULL,
  `dateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 表的结构 `setalert`
--

DROP TABLE IF EXISTS `setalert`;
CREATE TABLE `setalert` (
  `sms` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '短信开关',
  `sms_num` varchar(12) NOT NULL DEFAULT '0' COMMENT '短信号码',
  `email` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '邮件开关',
  `email_num` varchar(50) DEFAULT '0' COMMENT '邮箱号码',
  `bigerror` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '大量异常开关',
  `rule` text COMMENT '详细规则'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='预警设置';

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alert_log`
--
ALTER TABLE `alert_log`
  ADD UNIQUE KEY `sha1_id` (`un_id`);

--
-- Indexes for table `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`urlID`),
  ADD KEY `date` (`date`),
  ADD KEY `domain` (`domain`),
  ADD KEY `status` (`status`),
  ADD KEY `time` (`time`),
  ADD KEY `dateTime` (`dateTime`);

--
-- Indexes for table `autoLog`
--
ALTER TABLE `autoLog`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `push_API_Log`
--
ALTER TABLE `push_API_Log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `urlID` (`urlID`),
  ADD KEY `dateTime` (`dateTime`);

--
-- Indexes for table `push_Repeat`
--
ALTER TABLE `push_Repeat`
  ADD PRIMARY KEY (`titleID`);

--
-- Indexes for table `schedule`
--
ALTER TABLE `schedule`
  ADD PRIMARY KEY (`domain`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `autoLog`
--
ALTER TABLE `autoLog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `push_API_Log`
--
ALTER TABLE `push_API_Log`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;COMMIT;
