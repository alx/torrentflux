<?php

################################################################
# File to upgrade from TorrentFlux 2.1 to TorrentFlux 2.2
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

	mysql_query("DROP TABLE IF EXISTS `tf_links`;");

    mysql_query("CREATE TABLE `tf_links` (
  `lid` int(10) NOT NULL auto_increment,
  `url` varchar(255) NOT NULL default '',
  `sitename` varchar(255) NOT NULL default 'Old Link',
  `sort_order` tinyint(3) unsigned default '0',
  PRIMARY KEY  (`lid`)
) TYPE=MyISAM;");

    mysql_query("INSERT INTO `tf_links` VALUES (1, 'http://www.torrentflux.com', 'TorrentFlux.com', 0);");

    mysql_query("INSERT INTO `tf_settings` VALUES ('security_code','0');");

    mysql_query("ALTER TABLE `tf_cookies` CHANGE `cid` `cid` INT( 10 ) NOT NULL AUTO_INCREMENT;");

	echo "TorrentFlux upgrade to version 2.2 is complete!<br><br>"
	    ."You should now delete this upgrade file from your server.<br><br>";

	echo "<a href=\"index.php\">DONE</a>";
}
else
{
	echo "THIS IS FOR MYSQL DATABASE UPGRADE ONLY!<br><br>";
	echo "Upgrade from TorrentFlux 2.1 to 2.2<br>";
	echo "<ol>";
	echo "<li>Make sure all your torrent downloads are stopped before performing upgrade.</li>";
	echo "<li>Make sure you have copied over all the new PHP files.</li>";
	echo "<li>Make sure you have edited your new config.php with your database settings.</li>";
	echo "</ol>";
	echo "Are you ready to upgrade? <a href=\"".$_SERVER['PHP_SELF']."?upgrade=yes\">YES</a>";
}


?>
