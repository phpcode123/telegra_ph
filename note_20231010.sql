-- MySQL dump 10.13  Distrib 5.7.37, for Linux (x86_64)
--
-- Host: localhost    Database: 
-- ------------------------------------------------------
-- Server version	5.7.37-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `tp_click_analysis`
--

DROP TABLE IF EXISTS `tp_click_analysis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_click_analysis` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `date_time` varchar(255) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `all_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `pc_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `m_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `middle_page_clicks` int(10) unsigned NOT NULL DEFAULT '0',
  `short_url` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `date_time` (`date_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_contact`
--

DROP TABLE IF EXISTS `tp_contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_contact` (
  `itemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `remote_ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `message` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_domain`
--

DROP TABLE IF EXISTS `tp_domain`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_domain` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `http_prefix` varchar(255) NOT NULL DEFAULT '',
  `domain_url` varchar(255) NOT NULL DEFAULT '',
  `site_name` varchar(255) NOT NULL DEFAULT '',
  `index_title` varchar(255) NOT NULL DEFAULT '',
  `index_keyword` varchar(255) NOT NULL DEFAULT '',
  `index_description` varchar(255) NOT NULL DEFAULT '',
  `display_ad` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `sitemap` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `template_num` int(3) unsigned NOT NULL DEFAULT '1',
  `note` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`itemid`),
  KEY `domain_url` (`domain_url`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_http_referer`
--

DROP TABLE IF EXISTS `tp_http_referer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_http_referer` (
  `itemid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `short_str` varchar(255) NOT NULL DEFAULT '',
  `http_referer` varchar(255) NOT NULL DEFAULT '',
  `remote_ip` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `user_language` varchar(255) NOT NULL DEFAULT '',
  `timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `short_str` (`short_str`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_report_malicious_url`
--

DROP TABLE IF EXISTS `tp_report_malicious_url`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_report_malicious_url` (
  `itemid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `remote_ip` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `user_language` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `comment` varchar(10000) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT '',
  `timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_text`
--

DROP TABLE IF EXISTS `tp_text`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_text` (
  `itemid` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `short_str` varchar(255) NOT NULL DEFAULT '',
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `title` varchar(255) NOT NULL DEFAULT '',
  `tags` varchar(255) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `author` varchar(255) NOT NULL DEFAULT '',
  `edit_password` varchar(255) NOT NULL DEFAULT '',
  `visit_password` varchar(255) NOT NULL DEFAULT '',
  `addtime` int(9) unsigned NOT NULL DEFAULT '0',
  `expiration` varchar(20) NOT NULL DEFAULT 'N',
  `last_visit` int(9) unsigned NOT NULL DEFAULT '0',
  `update_time` int(9) unsigned NOT NULL DEFAULT '0',
  `hits` int(10) unsigned NOT NULL DEFAULT '0',
  `is_pc` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `remote_ip` varchar(255) NOT NULL DEFAULT '',
  `country` varchar(255) NOT NULL DEFAULT '',
  `user_agent` varchar(255) NOT NULL DEFAULT '',
  `user_language` varchar(255) NOT NULL DEFAULT '',
  `display_ad` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `is_404` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `redis_index` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_malicious` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`itemid`),
  KEY `short_str` (`short_str`),
  KEY `user_name` (`user_name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tp_text_content`
--

DROP TABLE IF EXISTS `tp_text_content`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tp_text_content` (
  `itemid` bigint(10) unsigned NOT NULL,
  `content` longtext NOT NULL,
  PRIMARY KEY (`itemid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2023-10-10 20:59:55
