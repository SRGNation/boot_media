-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2019 at 08:20 PM
-- Server version: 10.1.29-MariaDB
-- PHP Version: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `boot_media_dev`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `type` tinyint(2) NOT NULL COMMENT '0. Post deletion, 1. Post deletion by admin, 2. User deletion, 3. User ban, 4. User purge',
  `target` int(11) DEFAULT NULL,
  `source` int(11) DEFAULT NULL,
  `purge_values` varchar(16) DEFAULT NULL,
  `community` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `comment_body` varchar(2000) NOT NULL,
  `comment_image` varchar(200) NOT NULL,
  `comment_post` int(11) NOT NULL,
  `date_time` datetime(4) DEFAULT CURRENT_TIMESTAMP(4),
  `comment_type` tinyint(3) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `id` int(11) NOT NULL,
  `community_name` varchar(64) DEFAULT NULL,
  `community_desc` varchar(2000) DEFAULT NULL,
  `community_owner` int(11) NOT NULL,
  `community_icon` varchar(200) DEFAULT NULL,
  `community_banner` varchar(200) DEFAULT NULL,
  `is_recommend` tinyint(1) NOT NULL,
  `is_hidden` tinyint(1) NOT NULL,
  `is_nsfw` tinyint(1) NOT NULL,
  `join_perms` tinyint(1) NOT NULL,
  `view_perms` tinyint(1) NOT NULL,
  `post_perms` tinyint(4) NOT NULL,
  `date_created` datetime(4) NOT NULL DEFAULT CURRENT_TIMESTAMP(4)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `community_joins`
--

CREATE TABLE `community_joins` (
  `id` int(11) NOT NULL,
  `community` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `date_joined` datetime(4) NOT NULL DEFAULT CURRENT_TIMESTAMP(4)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `post_like` int(11) NOT NULL,
  `like_type` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `creator` int(11) NOT NULL,
  `post_body` varchar(2000) DEFAULT NULL,
  `post_image` varchar(200) DEFAULT NULL,
  `post_community` int(11) NOT NULL,
  `date_time` datetime(4) NOT NULL DEFAULT CURRENT_TIMESTAMP(4),
  `post_type` tinyint(3) NOT NULL,
  `is_pinned` tinyint(1) NOT NULL,
  `uses_html` tinyint(1) NOT NULL,
  `is_deleted` tinyint(1) NOT NULL,
  `announce_interval` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(256) NOT NULL,
  `website` varchar(32) DEFAULT NULL,
  `date_time` datetime(4) NOT NULL DEFAULT CURRENT_TIMESTAMP(4),
  `ip` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(32) NOT NULL,
  `user_pass` varchar(60) NOT NULL,
  `nick_name` varchar(32) NOT NULL,
  `email_address` varchar(200) DEFAULT NULL,
  `user_avatar` varchar(400) DEFAULT NULL,
  `user_login_ip` varchar(32) DEFAULT NULL,
  `date_created` datetime(4) NOT NULL DEFAULT CURRENT_TIMESTAMP(4),
  `user_type` tinyint(3) NOT NULL,
  `admin_level` tinyint(3) NOT NULL,
  `user_bio` varchar(2000) DEFAULT NULL,
  `hide_liked_posts` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `websites`
--

CREATE TABLE `websites` (
  `id` int(11) NOT NULL,
  `title` varchar(32) DEFAULT NULL,
  `name` varchar(64) DEFAULT NULL,
  `url` varchar(256) DEFAULT NULL,
  `token` varchar(60) DEFAULT NULL,
  `owner` int(11) NOT NULL,
  `date_created` datetime(4) NOT NULL DEFAULT CURRENT_TIMESTAMP(4),
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `community_joins`
--
ALTER TABLE `community_joins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `websites`
--
ALTER TABLE `websites`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `community_joins`
--
ALTER TABLE `community_joins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `websites`
--
ALTER TABLE `websites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
