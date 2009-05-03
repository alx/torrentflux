-- phpMyAdmin SQL Dump
-- --------------------------------------------------------

-- 
-- Table structure for table `tf_links`
-- 

CREATE TABLE `tf_links` (
  `lid` int(10) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  `sitename` varchar(255) NOT NULL default 'Old Link',
  `sort_order` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`lid`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `tf_links`
-- 

INSERT INTO `tf_links` VALUES (1, 'http://www.torrentflux.com', 'TorrentFlux.com', 0);

-- --------------------------------------------------------

-- 
-- Table structure for table `tf_log`
-- 

CREATE TABLE `tf_log` (
  `cid` int(14) NOT NULL auto_increment,
  `user_id` varchar(32) NOT NULL default '',
  `file` varchar(200) NOT NULL default '',
  `action` varchar(200) NOT NULL default '',
  `ip` varchar(15) NOT NULL default '',
  `ip_resolved` varchar(200) NOT NULL default '',
  `user_agent` varchar(200) NOT NULL default '',
  `time` varchar(14) NOT NULL default '0',
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `tf_messages`
-- 

CREATE TABLE `tf_messages` (
  `mid` int(10) NOT NULL auto_increment,
  `to_user` varchar(32) NOT NULL default '',
  `from_user` varchar(32) NOT NULL default '',
  `message` text,
  `IsNew` int(11) default NULL,
  `ip` varchar(15) NOT NULL default '',
  `time` varchar(14) NOT NULL default '0',
  `force_read` tinyint(1) default '0',
  PRIMARY KEY  (`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


-- --------------------------------------------------------

-- 
-- Table structure for table `tf_rss`
-- 

CREATE TABLE `tf_rss` (
  `rid` int(10) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`rid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `tf_rss`
-- 



-- --------------------------------------------------------

-- 
-- Table structure for table `tf_settings`
-- 

CREATE TABLE `tf_settings` (
  `tf_key` varchar(255) NOT NULL default '',
  `tf_value` text NOT NULL,
  PRIMARY KEY  (`tf_key`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `tf_settings`
-- 

INSERT INTO `tf_settings` VALUES ('path', '/usr/local/torrent/');
INSERT INTO `tf_settings` VALUES ('btphpbin', '/var/www/TF_BitTornado/btphptornado.py');
INSERT INTO `tf_settings` VALUES ('btshowmetainfo', '/var/www/TF_BitTornado/btshowmetainfo.py');
INSERT INTO `tf_settings` VALUES ('advanced_start', '1');
INSERT INTO `tf_settings` VALUES ('max_upload_rate', '10');
INSERT INTO `tf_settings` VALUES ('max_download_rate', '0');
INSERT INTO `tf_settings` VALUES ('max_uploads', '4');
INSERT INTO `tf_settings` VALUES ('minport', '49160');
INSERT INTO `tf_settings` VALUES ('maxport', '49300');
INSERT INTO `tf_settings` VALUES ('rerequest_interval', '1800');
INSERT INTO `tf_settings` VALUES ('cmd_options', '');
INSERT INTO `tf_settings` VALUES ('enable_search', '1');
INSERT INTO `tf_settings` VALUES ('enable_file_download', '1');
INSERT INTO `tf_settings` VALUES ('enable_view_nfo', '1');
INSERT INTO `tf_settings` VALUES ('package_ENGINE', 'zip');
INSERT INTO `tf_settings` VALUES ('show_server_load', '1');
INSERT INTO `tf_settings` VALUES ('loadavg_path', '/proc/loadavg');
INSERT INTO `tf_settings` VALUES ('days_to_keep', '30');
INSERT INTO `tf_settings` VALUES ('minutes_to_keep', '3');
INSERT INTO `tf_settings` VALUES ('rss_cache_min', '20');
INSERT INTO `tf_settings` VALUES ('page_refresh', '60');
INSERT INTO `tf_settings` VALUES ('default_theme', 'matrix');
INSERT INTO `tf_settings` VALUES ('default_language', 'lang-english.php');
INSERT INTO `tf_settings` VALUES ('debug_sql', '1');
INSERT INTO `tf_settings` VALUES ('torrent_dies_when_done', 'False');
INSERT INTO `tf_settings` VALUES ('sharekill', '150');
INSERT INTO `tf_settings` VALUES ('tfQManager', '/var/www/TF_BitTornado/tfQManager.py');
INSERT INTO `tf_settings` VALUES ('AllowQueing', '0');
INSERT INTO `tf_settings` VALUES ('maxServerThreads', '5');
INSERT INTO `tf_settings` VALUES ('maxUserThreads', '2');
INSERT INTO `tf_settings` VALUES ('sleepInterval', '10');
INSERT INTO `tf_settings` VALUES ('debugTorrents', '0');
INSERT INTO `tf_settings` VALUES ('pythonCmd', '/usr/bin/python');
INSERT INTO `tf_settings` VALUES ('searchEngine', 'TorrentSpy');
INSERT INTO `tf_settings` VALUES ('TorrentSpyGenreFilter', 'a:3:{i:0;s:2:"11";i:1;s:1:"6";i:2;s:1:"7";}');
INSERT INTO `tf_settings` VALUES ('TorrentBoxGenreFilter', 'a:3:{i:0;s:1:"0";i:1;s:1:"9";i:2;s:2:"10";}');
INSERT INTO `tf_settings` VALUES ('TorrentPortalGenreFilter', 'a:3:{i:0;s:1:"0";i:1;s:1:"6";i:2;s:2:"10";}');
INSERT INTO `tf_settings` VALUES ('enable_maketorrent','0');
INSERT INTO `tf_settings` VALUES ('btmakemetafile','/var/www/TF_BitTornado/btmakemetafile.py');
INSERT INTO `tf_settings` VALUES ('enable_torrent_download','1');
INSERT INTO `tf_settings` VALUES ('enable_file_priority','1');
INSERT INTO `tf_settings` VALUES ('security_code','0');
INSERT INTO `tf_settings` VALUES ('crypto_allowed', '1');
INSERT INTO `tf_settings` VALUES ('crypto_only', '1');
INSERT INTO `tf_settings` VALUES ('crypto_stealth', '0');



-- --------------------------------------------------------

-- 
-- Table structure for table `tf_users`
-- 

CREATE TABLE `tf_users` (
  `uid` int(10) NOT NULL auto_increment,
  `user_id` varchar(32) NOT NULL default '',
  `password` varchar(34) NOT NULL default '',
  `hits` int(10) NOT NULL default '0',
  `last_visit` varchar(14) NOT NULL default '0',
  `time_created` varchar(14) NOT NULL default '0',
  `user_level` tinyint(1) NOT NULL default '0',
  `hide_offline` tinyint(1) NOT NULL default '0',
  `theme` varchar(100) NOT NULL default 'mint',
  `language_file` varchar(60) default 'lang-english.php',
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;



-- 
-- Table structure for table `tf_cookies`
-- 

CREATE TABLE `tf_cookies` (
  `cid` int(10) NOT NULL auto_increment,
  `uid` int(10) NOT NULL,
  `host` varchar(255) default NULL,
  `data` varchar(255) default NULL,
  PRIMARY KEY  (`cid`)
) ENGINE=MyISAM ;