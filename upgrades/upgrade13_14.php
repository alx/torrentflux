<?php

################################################################
# File to upgrade from TorrentFlux 1.3 to TorrentFlux 1.4
# After you used this file, you can safely delete it.
################################################################
#                 -= WARNING: PLEASE READ =-
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
	mysql_query("INSERT INTO tf_links VALUES (NULL, 'http://www.litezone.com/')");
	mysql_query("INSERT INTO tf_rss VALUES (NULL, 'http://www.tvtorrents.net/rss.php')");

	echo "TorrentFlux upgrade to version 1.4 is complete!<br><br>"
	    ."You should now delete this upgrade file from your server.<br><br>";

	echo "<a href=\"index.php\">DONE</a>";
}
else
{
	echo "<ol>";
	echo "<li>Make sure all your torrent downloads are stopped before performing upgrade.</li>";
	echo "<li>Make sure you have copied over all the new PHP files.</li>";
	echo "<li>Make sure you have edited your new config.php</li>";
	echo "</ol>";
	echo "Are you ready to upgrade? <a href=\"".$_SERVER['PHP_SELF']."?upgrade=yes\">YES</a>";
}


?>
