<?php

################################################################
# File to upgrade from TorrentFlux 1.5 to TorrentFlux 2.0
# THIS IS FOR MYSQL DATABASE UPGRADE ONLY!
# After you used this file, you can safely delete it.
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

$connect = mysql_connect($cfg["db_host"], $cfg["db_user"], $cfg["db_pass"]) or die();
$connect = mysql_select_db($cfg["db_name"]);


if ($upgrade == "yes")
{
	// Insert New Link and RSS Feed
	mysql_query("alter table tf_messages change new IsNew INTEGER");
	mysql_query("CREATE TABLE `tf_xfer` (
		`user` varchar(32) NOT NULL default '',
 		`date` date NOT NULL default '0000-00-00',
		`download` bigint(20) NOT NULL default '0',
		`upload` bigint(20) NOT NULL default '0',
		PRIMARY KEY  (`user`,`date`)
		) TYPE=MyISAM;");

	echo "TorrentFlux upgrade to version 2.0 is complete!<br><br>"
	    ."You should now delete this upgrade file from your server.<br><br>";

	echo "<a href=\"index.php\">DONE</a>";
}
else
{
	echo "THIS IS FOR MYSQL DATABASE UPGRADE ONLY!<br><br>";
	echo "Upgrade from TorrentFlux 1.5 to 2.0<br>";
	echo "<ol>";
	echo "<li>Make sure all your torrent downloads are stopped before performing upgrade.</li>";
	echo "<li>Make sure you have copied over all the new PHP files.</li>";
	echo "<li>Make sure you have edited your new config.php with your database settings.</li>";
	echo "</ol>";
	echo "Are you ready to upgrade? <a href=\"".$_SERVER['PHP_SELF']."?upgrade=yes\">YES</a>";
}


?>
