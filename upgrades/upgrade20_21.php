<?php

################################################################
# File to upgrade from TorrentFlux 2.0 to TorrentFlux 2.1
# THIS IS FOR MYSQL DATABASE UPGRADE ONLY!
# After you used this file, you should delete it.
################################################################
#                 -= WARNING: PLEASE READ =-
#
# THIS IS FOR MYSQL DATABASE UPGRADE ONLY!
#
# NOTE: This file uses config.php to retrieve needed
# variables values. So, to do the upgrade PLEASE copy
# this file in your server root directory (same location
# as your config.php) and execute it from your browser.
################################################################

include("config.php");

if ($_GET["upgrade"] == "yes")
{
    $connect = mysql_connect($cfg["db_host"], $cfg["db_user"], $cfg["db_pass"]) or die();
    $connect = mysql_select_db($cfg["db_name"]);

	// Insert New Settings Table
	$query = "CREATE TABLE `tf_settings` (`tf_key` varchar(255) NOT NULL default '',`tf_value` text NOT NULL,PRIMARY KEY  (`tf_key`)) TYPE=MyISAM;;
INSERT INTO `tf_settings` VALUES ('path', '/usr/local/torrent/');;
INSERT INTO `tf_settings` VALUES ('btphpbin', '/var/www/TF_BitTornado/btphptornado.py');;
INSERT INTO `tf_settings` VALUES ('btshowmetainfo', '/var/www/TF_BitTornado/btshowmetainfo.py');;
INSERT INTO `tf_settings` VALUES ('advanced_start', '1');;
INSERT INTO `tf_settings` VALUES ('max_upload_rate', '10');;
INSERT INTO `tf_settings` VALUES ('max_download_rate', '0');;
INSERT INTO `tf_settings` VALUES ('max_uploads', '4');;
INSERT INTO `tf_settings` VALUES ('minport', '49160');;
INSERT INTO `tf_settings` VALUES ('maxport', '49300');;
INSERT INTO `tf_settings` VALUES ('rerequest_interval', '1800');;
INSERT INTO `tf_settings` VALUES ('cmd_options', '');;
INSERT INTO `tf_settings` VALUES ('enable_search', '1');;
INSERT INTO `tf_settings` VALUES ('enable_file_download', '1');;
INSERT INTO `tf_settings` VALUES ('enable_view_nfo', '1');;
INSERT INTO `tf_settings` VALUES ('package_type', 'zip');;
INSERT INTO `tf_settings` VALUES ('show_server_load', '1');;
INSERT INTO `tf_settings` VALUES ('loadavg_path', '/proc/loadavg');;
INSERT INTO `tf_settings` VALUES ('days_to_keep', '30');;
INSERT INTO `tf_settings` VALUES ('minutes_to_keep', '3');;
INSERT INTO `tf_settings` VALUES ('rss_cache_min', '20');;
INSERT INTO `tf_settings` VALUES ('page_refresh', '60');;
INSERT INTO `tf_settings` VALUES ('default_theme', 'matrix');;
INSERT INTO `tf_settings` VALUES ('default_language', 'lang-english.php');;
INSERT INTO `tf_settings` VALUES ('debug_sql', '1');;
INSERT INTO `tf_settings` VALUES ('torrent_dies_when_done', 'False');;
INSERT INTO `tf_settings` VALUES ('sharekill', '150');;
INSERT INTO `tf_settings` VALUES ('tfQManager', '/var/www/TF_BitTornado/tfQManager.py');;
INSERT INTO `tf_settings` VALUES ('AllowQueing', '0');;
INSERT INTO `tf_settings` VALUES ('maxServerThreads', '5');;
INSERT INTO `tf_settings` VALUES ('maxUserThreads', '2');;
INSERT INTO `tf_settings` VALUES ('sleepInterval', '10');;
INSERT INTO `tf_settings` VALUES ('debugTorrents', '0');;
INSERT INTO `tf_settings` VALUES ('pythonCmd', '/usr/bin/python');;
INSERT INTO `tf_settings` VALUES ('searchEngine', 'TorrentSpy');;
INSERT INTO `tf_settings` VALUES ('enable_maketorrent','0');;
INSERT INTO `tf_settings` VALUES ('btmakemetafile','/var/www/TF_BitTornado/btmakemetafile.py');;
INSERT INTO `tf_settings` VALUES ('enable_torrent_download','1');;
INSERT INTO `tf_settings` VALUES ('enable_file_priority','1');;";

    $tok = strtok($query, ";;\n");
    while ($tok)
    {
        $result = mysql_query("$tok");
        $tok = strtok(";;\n");
    }

    $result = mysql_query("
    CREATE TABLE `tf_cookies` (
  `cid` tinyint(5) NOT NULL auto_increment,
  `uid` int(10) NOT NULL,
  `host` varchar(255) default NULL,
  `data` varchar(255) default NULL,
  PRIMARY KEY  (`cid`)
) TYPE=MyISAM ;");

	echo "TorrentFlux upgrade to version 2.1 is complete!<br><br>"
	    ."You should now delete this upgrade file from your server.<br><br>";

	echo "<a href=\"index.php\">DONE</a>";
}
else
{
	echo "THIS IS FOR MYSQL DATABASE UPGRADE ONLY!<br><br>";
	echo "Upgrade from TorrentFlux 2.0 to 2.1<br>";
	echo "<ol>";
	echo "<li>Make sure all your torrent downloads are stopped before performing upgrade.</li>";
	echo "<li>Make sure you have copied over all the new PHP files.</li>";
	echo "<li>Make sure you have edited your new config.php with your database settings.</li>";
	echo "</ol>";
	echo "Are you ready to upgrade? <a href=\"".$_SERVER['PHP_SELF']."?upgrade=yes\">YES</a>";
}


?>
