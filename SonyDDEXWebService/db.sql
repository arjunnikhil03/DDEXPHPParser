-- Adminer 4.2.1 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';

DROP TABLE IF EXISTS `iso_693_2_codes`;
CREATE TABLE `iso_693_2_codes` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `iso_693_2_5` varchar(10) DEFAULT NULL,
  `iso_693_1` varchar(10) DEFAULT NULL,
  `language_name` varchar(50) DEFAULT NULL,
  `scope` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `iso_693_3` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `songlist_kaltura`;
CREATE TABLE `songlist_kaltura` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `isrc_songlist` varchar(50) DEFAULT NULL,
  `isrc_kaltura_all` varchar(50) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `artist` varchar(200) DEFAULT NULL,
  `entry_id` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


DROP TABLE IF EXISTS `sony_albumlist`;
CREATE TABLE `sony_albumlist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `Image_DPID` varchar(100) DEFAULT NULL,
  `ImageType` varchar(30) DEFAULT NULL,
  `ImageCodecType` varchar(30) DEFAULT NULL,
  `ImageHeight` varchar(30) DEFAULT NULL,
  `ImageWidth` varchar(30) DEFAULT NULL,
  `ImageResolution` varchar(30) DEFAULT NULL,
  `ImageURL` text,
  `ImageHashSum` varchar(100) DEFAULT NULL,
  `Albumname` varchar(100) DEFAULT NULL,
  `Grid` varchar(100) DEFAULT NULL,
  `ICPN` varchar(100) DEFAULT NULL,
  `DPID` varchar(100) DEFAULT NULL,
  `IPID` varchar(100) DEFAULT NULL,
  `Process_status` char(5) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `Image_status` char(5) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `release_date` varchar(100) DEFAULT NULL,
  `year` varchar(100) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `movie_release_date` varchar(100) DEFAULT NULL,
  `albumtype` varchar(100) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `music_director` varchar(100) DEFAULT NULL,
  `release_type` varchar(100) DEFAULT NULL,
  `catalog_number` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`srno`),
  KEY `xmlid` (`xmlid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_deallist`;
CREATE TABLE `sony_deallist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `commercial_type` varchar(100) DEFAULT NULL,
  `use_type` varchar(100) DEFAULT NULL,
  `territory_code` varchar(100) DEFAULT NULL,
  `sales_start_date` varchar(100) DEFAULT NULL,
  `xmlid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_songlist`;
CREATE TABLE `sony_songlist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `ISRC` varchar(20) DEFAULT NULL,
  `ResourceReference` char(5) DEFAULT NULL,
  `ReferenceTitle` varchar(200) DEFAULT NULL,
  `LanguageOfPerformance` varchar(20) DEFAULT NULL,
  `Duration` varchar(20) DEFAULT NULL,
  `Title` varchar(200) DEFAULT NULL,
  `Artist` varchar(100) DEFAULT NULL,
  `Producer` varchar(100) DEFAULT NULL,
  `Composer` varchar(100) DEFAULT NULL,
  `Lyricist` varchar(100) DEFAULT NULL,
  `Label` varchar(100) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `plinetext` varchar(100) DEFAULT NULL,
  `Genre` varchar(30) DEFAULT NULL,
  `ParentalWarningType` varchar(30) DEFAULT NULL,
  `AudioCodecType` varchar(30) DEFAULT NULL,
  `NumberOfChannels` char(5) DEFAULT NULL,
  `SamplingRate` varchar(30) DEFAULT NULL,
  `IsPreview` char(5) DEFAULT NULL,
  `FileURL` text,
  `File_status` char(5) DEFAULT NULL,
  `HashSum` varchar(100) DEFAULT NULL,
  `Grid` varchar(100) DEFAULT NULL,
  `ICPN` varchar(100) DEFAULT NULL,
  `DPID` varchar(100) DEFAULT NULL,
  `IPID` varchar(100) DEFAULT NULL,
  `Process_status` char(5) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `ImageURL` text,
  `ImageHashSum` varchar(100) DEFAULT NULL,
  `Image_status` char(5) DEFAULT NULL,
  `ImageCodecType` varchar(30) DEFAULT NULL,
  `ImageHeight` varchar(30) DEFAULT NULL,
  `ImageWidth` varchar(30) DEFAULT NULL,
  `ImageResolution` varchar(30) DEFAULT NULL,
  `wav_status` char(5) DEFAULT '0',
  `added_to_cms` int(11) DEFAULT '0',
  `Actor` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`srno`),
  KEY `xmlid` (`xmlid`),
  KEY `ISRC` (`ISRC`),
  KEY `File_status` (`File_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_ws_albumlist`;
CREATE TABLE `sony_ws_albumlist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `Albumname` varchar(100) DEFAULT NULL,
  `Grid` varchar(100) DEFAULT NULL,
  `ICPN` varchar(100) DEFAULT NULL,
  `DPID` varchar(100) DEFAULT NULL,
  `IPID` varchar(100) DEFAULT NULL,
  `Process_status` char(5) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `Image_status` char(5) DEFAULT NULL,
  `label` varchar(100) DEFAULT NULL,
  `release_date` varchar(100) DEFAULT NULL,
  `year` varchar(100) DEFAULT NULL,
  `language` varchar(100) DEFAULT NULL,
  `movie_release_date` varchar(100) DEFAULT NULL,
  `albumtype` varchar(100) DEFAULT NULL,
  `publisher` varchar(100) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `music_director` varchar(100) DEFAULT NULL,
  `release_type` varchar(100) DEFAULT NULL,
  `catalog_number` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`srno`),
  KEY `xmlid` (`xmlid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_ws_songlist`;
CREATE TABLE `sony_ws_songlist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `ISRC` varchar(20) DEFAULT NULL,
  `ResourceReference` char(5) DEFAULT NULL,
  `ReferenceTitle` varchar(200) DEFAULT NULL,
  `LanguageOfPerformance` varchar(20) DEFAULT NULL,
  `Duration` varchar(20) DEFAULT NULL,
  `Title` varchar(200) DEFAULT NULL,
  `Artist` varchar(100) DEFAULT NULL,
  `Producer` varchar(100) DEFAULT NULL,
  `Composer` varchar(100) DEFAULT NULL,
  `Lyricist` varchar(100) DEFAULT NULL,
  `Label` varchar(100) DEFAULT NULL,
  `Year` int(11) DEFAULT NULL,
  `plinetext` varchar(100) DEFAULT NULL,
  `Genre` varchar(30) DEFAULT NULL,
  `ParentalWarningType` varchar(30) DEFAULT NULL,
  `AudioCodecType` varchar(30) DEFAULT NULL,
  `NumberOfChannels` char(5) DEFAULT NULL,
  `SamplingRate` varchar(30) DEFAULT NULL,
  `IsPreview` char(5) DEFAULT NULL,
  `FileURL` text,
  `File_status` char(5) DEFAULT NULL,
  `HashSum` varchar(100) DEFAULT NULL,
  `Grid` varchar(100) DEFAULT NULL,
  `ICPN` varchar(100) DEFAULT NULL,
  `DPID` varchar(100) DEFAULT NULL,
  `IPID` varchar(100) DEFAULT NULL,
  `Process_status` char(5) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `ImageURL` text,
  `ImageHashSum` varchar(100) DEFAULT NULL,
  `Image_status` char(5) DEFAULT NULL,
  `ImageCodecType` varchar(30) DEFAULT NULL,
  `ImageHeight` varchar(30) DEFAULT NULL,
  `ImageWidth` varchar(30) DEFAULT NULL,
  `ImageResolution` varchar(30) DEFAULT NULL,
  `wav_status` char(5) DEFAULT '0',
  `added_to_cms` int(11) DEFAULT '0',
  `Actor` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`srno`),
  KEY `xmlid` (`xmlid`),
  KEY `ISRC` (`ISRC`),
  KEY `File_status` (`File_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_ws_xmllist`;
CREATE TABLE `sony_ws_xmllist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `MessageThreadId` varchar(100) DEFAULT NULL,
  `MessageId` varchar(100) DEFAULT NULL,
  `MessageSenderId` varchar(100) DEFAULT NULL,
  `MessageSenderName` varchar(100) DEFAULT NULL,
  `MessageRecipientId` varchar(100) DEFAULT NULL,
  `MessageRecipientName` varchar(100) DEFAULT NULL,
  `MessageCreatedDateTime` varchar(100) DEFAULT NULL,
  `Process_status` char(5) DEFAULT NULL,
  `Process_date` date DEFAULT NULL,
  `total_songs` int(11) DEFAULT '0',
  `download_songs` int(11) DEFAULT '0',
  `download_images` int(11) DEFAULT '0',
  `log_send` int(11) DEFAULT '0',
  `update_indicator` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_xmllist`;
CREATE TABLE `sony_xmllist` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `Filename` varchar(30) DEFAULT NULL,
  `MessageThreadId` varchar(100) DEFAULT NULL,
  `MessageId` varchar(100) DEFAULT NULL,
  `MessageSenderId` varchar(100) DEFAULT NULL,
  `MessageSenderName` varchar(100) DEFAULT NULL,
  `MessageRecipientId` varchar(100) DEFAULT NULL,
  `MessageRecipientName` varchar(100) DEFAULT NULL,
  `MessageCreatedDateTime` varchar(100) DEFAULT NULL,
  `XML_status` char(5) DEFAULT NULL,
  `Process_status` char(5) DEFAULT NULL,
  `Process_date` date DEFAULT NULL,
  `total_songs` int(11) DEFAULT '0',
  `download_songs` int(11) DEFAULT '0',
  `download_images` int(11) DEFAULT '0',
  `log_send` int(11) DEFAULT '0',
  `update_indicator` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_xml_output`;
CREATE TABLE `sony_xml_output` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `party_id` varchar(100) DEFAULT NULL,
  `catalog_number` varchar(100) DEFAULT NULL,
  `ipcn` varchar(100) DEFAULT NULL,
  `grid` varchar(100) DEFAULT NULL,
  `isrc` varchar(100) DEFAULT NULL,
  `artist` varchar(500) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `release_type` varchar(100) DEFAULT NULL,
  `commercial_type` varchar(100) DEFAULT NULL,
  `use_type` varchar(100) DEFAULT NULL,
  `territories` varchar(100) DEFAULT NULL,
  `price_value_code` varchar(100) DEFAULT NULL,
  `sales_start_date` varchar(100) DEFAULT NULL,
  `sales_end_date` varchar(100) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `inserted_date` datetime DEFAULT NULL,
  PRIMARY KEY (`srno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_xml_output_1`;
CREATE TABLE `sony_xml_output_1` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `party_id` varchar(100) DEFAULT NULL,
  `catalog_number` varchar(100) DEFAULT NULL,
  `ipcn` varchar(100) DEFAULT NULL,
  `grid` varchar(100) DEFAULT NULL,
  `isrc` varchar(100) DEFAULT NULL,
  `artist` varchar(500) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `release_type` varchar(100) DEFAULT NULL,
  `commercial_type` varchar(100) DEFAULT NULL,
  `use_type` varchar(100) DEFAULT NULL,
  `territories` varchar(100) DEFAULT NULL,
  `price_value_code` varchar(100) DEFAULT NULL,
  `sales_start_date` varchar(100) DEFAULT NULL,
  `sales_end_date` varchar(100) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `inserted_date` datetime DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  `addedjb` bit(1) DEFAULT b'0',
  PRIMARY KEY (`srno`),
  KEY `isrc` (`isrc`),
  KEY `catalog_number` (`catalog_number`),
  KEY `commercial_type` (`commercial_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `sony_xml_ws_output_1`;
CREATE TABLE `sony_xml_ws_output_1` (
  `srno` int(11) NOT NULL AUTO_INCREMENT,
  `party_id` varchar(100) DEFAULT NULL,
  `catalog_number` varchar(100) DEFAULT NULL,
  `ipcn` varchar(100) DEFAULT NULL,
  `grid` varchar(100) DEFAULT NULL,
  `isrc` varchar(100) DEFAULT NULL,
  `artist` varchar(500) DEFAULT NULL,
  `title` varchar(100) DEFAULT NULL,
  `release_type` varchar(100) DEFAULT NULL,
  `commercial_type` varchar(100) DEFAULT NULL,
  `use_type` varchar(100) DEFAULT NULL,
  `territories` varchar(100) DEFAULT NULL,
  `price_value_code` varchar(100) DEFAULT NULL,
  `sales_start_date` varchar(100) DEFAULT NULL,
  `sales_end_date` varchar(100) DEFAULT NULL,
  `xmlid` int(11) DEFAULT NULL,
  `inserted_date` datetime DEFAULT NULL,
  `updated_date` datetime DEFAULT NULL,
  PRIMARY KEY (`srno`),
  KEY `isrc` (`isrc`),
  KEY `catalog_number` (`catalog_number`),
  KEY `commercial_type` (`commercial_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `unprocessed_xml`;
CREATE TABLE `unprocessed_xml` (
  `xmlid` int(11) NOT NULL,
  `grid` varchar(50) DEFAULT NULL,
  `icpn` varchar(50) DEFAULT NULL,
  `Albumname` varchar(100) DEFAULT NULL,
  `ResourceReference` varchar(5) DEFAULT NULL,
  `ISRC` varchar(20) NOT NULL,
  `SongTitle` varchar(200) DEFAULT NULL,
  `Artist` varchar(100) DEFAULT NULL,
  `Duration` varchar(20) DEFAULT NULL,
  `LanguageOfPerformance` varchar(20) DEFAULT NULL,
  `Composer` varchar(100) DEFAULT NULL,
  `Lyricist` varchar(100) DEFAULT NULL,
  `HashSum` varchar(100) DEFAULT NULL,
  `FileUrl` varchar(100) DEFAULT NULL,
  `ImageHashSum` varchar(100) DEFAULT NULL,
  `ImageURL` text,
  `Label` varchar(100) DEFAULT NULL,
  `addedtojb` int(11) DEFAULT '0',
  `process_status` bit(1) DEFAULT b'0',
  `addeddate` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `Actor` varchar(200) DEFAULT NULL,
  `ReleaseReference` varchar(5) DEFAULT NULL,
  `alreadythere` int(11) DEFAULT '0',
  PRIMARY KEY (`xmlid`,`ISRC`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- 2017-09-19 10:34:26
