-- MySQL dump 10.13  Distrib 5.5.43, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: efront
-- ------------------------------------------------------
-- Server version	5.5.43-0ubuntu0.12.04.1

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
-- Table structure for table `module_vlabs_shoppingcart`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart` (
  `id` bigint(10) unsigned NOT NULL AUTO_INCREMENT,
  `course` bigint(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL DEFAULT '',
  `intro` mediumtext,
  `introformat` smallint(4) unsigned NOT NULL DEFAULT '0',
  `timecreated` bigint(10) unsigned NOT NULL DEFAULT '0',
  `timemodified` bigint(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Default comment for shoppingcart, please edit me';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_store_inventory`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_store_inventory`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_store_inventory` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '0',
  `description` varchar(100) DEFAULT NULL,
  `price` double unsigned NOT NULL,
  `quantity` bigint(10) unsigned DEFAULT NULL,
  `active` blob NOT NULL,
  `creationdate` datetime NOT NULL,
  `lastmodification` datetime NOT NULL,
  `unlimited` blob NOT NULL,
  `referenceid` varchar(100) DEFAULT NULL,
  `type` enum('ITEM','PACKAGE','PACKAGE ITEM') NOT NULL DEFAULT 'ITEM',
  `billable` blob NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=473 DEFAULT CHARSET=utf8 COMMENT='This table will contain all the items available in the shop';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_order`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_order`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_order` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `userid` mediumint(6) unsigned NOT NULL,
  `purchasedate` bigint(20) DEFAULT NULL,
  `lastmodification` bigint(20) DEFAULT NULL,
  `fulfillmentorderstate` varchar(100) NOT NULL DEFAULT '',
  `financialorderstate` varchar(100) NOT NULL DEFAULT '',
  `ordernumber` varchar(15) NOT NULL DEFAULT '',
  `total` double(15,0) unsigned NOT NULL,
  `cancelled` binary(1) NOT NULL,
  `payment` binary(1) NOT NULL,
  `refund` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2919 DEFAULT CHARSET=utf8 COMMENT='This table will contain the order information';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_payment_method`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_payment_method`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_payment_method` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL DEFAULT '0',
  `description` varchar(100) NOT NULL DEFAULT '',
  `merchantid` varchar(100) DEFAULT NULL,
  `merchantkey` varchar(15) DEFAULT NULL,
  `servertype` varchar(22) DEFAULT NULL,
  `currency` varchar(3) DEFAULT NULL,
  `type` enum('PAYMENT','NO PAYMENT') NOT NULL DEFAULT 'NO PAYMENT',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='This table will contain all the available payment methods';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_user_payment`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_user_payment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_user_payment` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL DEFAULT '0',
  `payment` bigint(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will contain the type of paymemt for every user';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_order_summary`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_order_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_order_summary` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `orderid` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `itemid` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `quantity` bigint(10) unsigned NOT NULL DEFAULT '0',
  `unitprice` double unsigned NOT NULL DEFAULT '0',
  `cancelled` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itemid` (`itemid`),
  KEY `orderid` (`orderid`),
  CONSTRAINT `module_vlabs_shoppingcart_order_summary_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `module_vlabs_shoppingcart_store_inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `module_vlabs_shoppingcart_order_summary_ibfk_2` FOREIGN KEY (`orderid`) REFERENCES `module_vlabs_shoppingcart_order` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4167 DEFAULT CHARSET=utf8 COMMENT='This table will contain the order description';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_package_summary`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_package_summary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_package_summary` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `packageid` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `itemid` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `quantity` bigint(10) unsigned NOT NULL DEFAULT '0',
  `price` double unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `packageid` (`packageid`),
  KEY `itemid` (`itemid`),
  CONSTRAINT `module_vlabs_shoppingcart_package_summary_ibfk_1` FOREIGN KEY (`packageid`) REFERENCES `module_vlabs_shoppingcart_store_inventory` (`id`) ON DELETE CASCADE,
  CONSTRAINT `module_vlabs_shoppingcart_package_summary_ibfk_2` FOREIGN KEY (`itemid`) REFERENCES `module_vlabs_shoppingcart_store_inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=242 DEFAULT CHARSET=utf8 COMMENT='This table will contain the packages items';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_preassignment`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_preassignment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_preassignment` (
  `id` varchar(20) NOT NULL DEFAULT '',
  `courseid` bigint(10) unsigned NOT NULL DEFAULT '0',
  `itemid` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `quantity` bigint(10) unsigned NOT NULL DEFAULT '0',
  `assignmentdate` datetime NOT NULL,
  `lastmodification` datetime NOT NULL,
  `active` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `itemid` (`itemid`),
  CONSTRAINT `module_vlabs_shoppingcart_preassignment_ibfk_1` FOREIGN KEY (`itemid`) REFERENCES `module_vlabs_shoppingcart_store_inventory` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will contain the assignments of items to courses';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_log`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_log` (
  `id` mediumint(6) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(300) NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table will contain the modification in the inventory';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `module_vlabs_shoppingcart_dbadmin`
--

DROP TABLE IF EXISTS `module_vlabs_shoppingcart_dbadmin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `module_vlabs_shoppingcart_dbadmin` (
  `id` int(11) NOT NULL,
  `module` varchar(15) NOT NULL,
  `description` varchar(30) NOT NULL,
  `moduleprefix` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-24  1:16:18
