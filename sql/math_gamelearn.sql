-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 19, 2024 at 07:18 AM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `math_gamelearn`
--
CREATE DATABASE IF NOT EXISTS `math_gamelearn` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `math_gamelearn`;

-- --------------------------------------------------------

--
-- Table structure for table `comment`
--

DROP TABLE IF EXISTS `comment`;
CREATE TABLE IF NOT EXISTS `comment` (
  `commentID` int NOT NULL AUTO_INCREMENT,
  `commentText` text COLLATE utf8mb4_general_ci NOT NULL,
  `commentMedia` blob NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `postID` int NOT NULL,
  `userID` int NOT NULL,
  PRIMARY KEY (`commentID`),
  KEY `postID` (`postID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

DROP TABLE IF EXISTS `course`;
CREATE TABLE IF NOT EXISTS `course` (
  `courseID` int NOT NULL AUTO_INCREMENT,
  `intro` text COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `lastUpdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `courseThumb` blob,
  PRIMARY KEY (`courseID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_enrolment`
--

DROP TABLE IF EXISTS `course_enrolment`;
CREATE TABLE IF NOT EXISTS `course_enrolment` (
  `userID` int NOT NULL,
  `courseID` int NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`courseID`,`userID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_feedback`
--

DROP TABLE IF EXISTS `course_feedback`;
CREATE TABLE IF NOT EXISTS `course_feedback` (
  `fbID` int NOT NULL AUTO_INCREMENT,
  `fbText` text COLLATE utf8mb4_general_ci,
  `ratings` int NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  `courseID` int NOT NULL,
  `fbImg` blob,
  PRIMARY KEY (`fbID`),
  KEY `courseID` (`courseID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gift`
--

DROP TABLE IF EXISTS `gift`;
CREATE TABLE IF NOT EXISTS `gift` (
  `giftID` int NOT NULL AUTO_INCREMENT,
  `giftName` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `giftPoints` int NOT NULL,
  `giftMedia` blob NOT NULL,
  PRIMARY KEY (`giftID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module`
--

DROP TABLE IF EXISTS `module`;
CREATE TABLE IF NOT EXISTS `module` (
  `moduleID` int NOT NULL AUTO_INCREMENT,
  `moduleDesc` text COLLATE utf8mb4_general_ci NOT NULL,
  `filename` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `file` blob NOT NULL,
  `courseID` int NOT NULL,
  `moduleTitle` varchar(50) NOT NULL,
  PRIMARY KEY (`moduleID`),
  KEY `courseID` (`courseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `module_enrolment`
--

DROP TABLE IF EXISTS `module_enrolment`;
CREATE TABLE IF NOT EXISTS `module_enrolment` (
  `userID` int NOT NULL,
  `moduleID` int NOT NULL,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`moduleID`,`userID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `option`
--

DROP TABLE IF EXISTS `option`;
CREATE TABLE IF NOT EXISTS `option` (
  `optID` int NOT NULL AUTO_INCREMENT,
  `optValue` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `IsAnswer` tinyint(1) NOT NULL,
  `questID` int NOT NULL,
  `optImg` blob,
  PRIMARY KEY (`optID`),
  KEY `questID` (`questID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
CREATE TABLE IF NOT EXISTS `post` (
  `postID` int NOT NULL AUTO_INCREMENT,
  `content` text COLLATE utf8mb4_general_ci NOT NULL,
  `postMedia` blob NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `userID` int NOT NULL,
  PRIMARY KEY (`postID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `profile`
--

DROP TABLE IF EXISTS `profile`;
CREATE TABLE IF NOT EXISTS `profile` (
  `userID` int NOT NULL,
  `profilePic` blob NOT NULL,
  `point` int NOT NULL,
  PRIMARY KEY (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question`
--

DROP TABLE IF EXISTS `question`;
CREATE TABLE IF NOT EXISTS `question` (
  `questID` int NOT NULL AUTO_INCREMENT,
  `questText` text COLLATE utf8mb4_general_ci NOT NULL,
  `award_pt` int NOT NULL,
  `courseID` int NOT NULL,
  `questImg` tinyblob,
  PRIMARY KEY (`questID`),
  KEY `courseID` (`courseID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz_enrolment`
--

DROP TABLE IF EXISTS `quiz_enrolment`;
CREATE TABLE IF NOT EXISTS `quiz_enrolment` (
  `userID` int NOT NULL,
  `questID` int NOT NULL,
  `isCorrect` tinyint(1) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`userID`,`questID`),
  KEY `questID` (`questID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_feedback`
--

DROP TABLE IF EXISTS `system_feedback`;
CREATE TABLE IF NOT EXISTS `system_feedback` (
  `sfID` int NOT NULL AUTO_INCREMENT,
  `sfContent` text COLLATE utf8mb4_general_ci NOT NULL,
  `sfMedia` blob NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reply` text COLLATE utf8mb4_general_ci,
  `userID` int NOT NULL,
  PRIMARY KEY (`sfID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_gift`
--

DROP TABLE IF EXISTS `user_gift`;
CREATE TABLE IF NOT EXISTS `user_gift` (
  `redemptionID` int NOT NULL,
  `userID` int NOT NULL,
  `giftID` int NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `isUsed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`redemptionID`,`userID`,`giftID`),
  KEY `userID` (`userID`),
  KEY `giftID` (`giftID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comment_ibfk_1` FOREIGN KEY (`postID`) REFERENCES `post` (`postID`),
  ADD CONSTRAINT `comment_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `course`
--
ALTER TABLE `course`
  ADD CONSTRAINT `course_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `course_enrolment`
--
ALTER TABLE `course_enrolment`
  ADD CONSTRAINT `course_enrolment_ibfk_1` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`),
  ADD CONSTRAINT `course_enrolment_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`),
  ADD CONSTRAINT `course_enrolment_ibfk_3` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`),
  ADD CONSTRAINT `courseID` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`);

--
-- Constraints for table `course_feedback`
--
ALTER TABLE `course_feedback`
  ADD CONSTRAINT `course_feedback_ibfk_1` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`),
  ADD CONSTRAINT `course_feedback_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `module`
--
ALTER TABLE `module`
  ADD CONSTRAINT `module_ibfk_1` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`);

--
-- Constraints for table `module_enrolment`
--
ALTER TABLE `module_enrolment`
  ADD CONSTRAINT `module_enrolment_ibfk_1` FOREIGN KEY (`moduleID`) REFERENCES `module` (`moduleID`),
  ADD CONSTRAINT `module_enrolment_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `option`
--
ALTER TABLE `option`
  ADD CONSTRAINT `option_ibfk_1` FOREIGN KEY (`questID`) REFERENCES `question` (`questID`);

--
-- Constraints for table `post`
--
ALTER TABLE `post`
  ADD CONSTRAINT `post_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `question`
--
ALTER TABLE `question`
  ADD CONSTRAINT `question_ibfk_1` FOREIGN KEY (`courseID`) REFERENCES `course` (`courseID`);

--
-- Constraints for table `quiz_enrolment`
--
ALTER TABLE `quiz_enrolment`
  ADD CONSTRAINT `quiz_enrolment_ibfk_1` FOREIGN KEY (`questID`) REFERENCES `question` (`questID`),
  ADD CONSTRAINT `quiz_enrolment_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `system_feedback`
--
ALTER TABLE `system_feedback`
  ADD CONSTRAINT `system_feedback_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`);

--
-- Constraints for table `user_gift`
--
ALTER TABLE `user_gift`
  ADD CONSTRAINT `user_gift_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `profile` (`userID`),
  ADD CONSTRAINT `user_gift_ibfk_2` FOREIGN KEY (`giftID`) REFERENCES `gift` (`giftID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
