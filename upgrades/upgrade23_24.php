<?php

################################################################
# File to upgrade from TorrentFlux 2.2 to TorrentFlux 2.3
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

    mysql_query("update tf_settings set tf_value = 'PirateBay' where tf_key = 'searchEngine' and tf_value = 'TorrentSpy';");

    mysql_query("INSERT INTO `tf_settings` VALUES ('crypto_allowed', 1)");
    mysql_query("INSERT INTO `tf_settings` VALUES ('crypto_only', 1)");
    mysql_query("INSERT INTO `tf_settings` VALUES ('crypto_stealth', 0)");

    

	echo "TorrentFlux upgrade to version 2.4 is complete!<br><br>"
	    ."You should now delete this upgrade file from your server.<br><br>";

	echo "<a href=\"index.php\">DONE</a>";
}
else
{
	echo "THIS IS FOR MYSQL DATABASE UPGRADE ONLY!<br><br>";
	echo "Upgrade from TorrentFlux 2.3 to 2.4<br>";
	echo "<ol>";
	echo "<li>Make sure all your torrent downloads are stopped before performing upgrade.</li>";
	echo "<li>Make sure you have copied over all the new PHP files.</li>";
	echo "<li>Make sure you have edited your new config.php with your database settings.</li>";
	echo "</ol>";
	echo "Are you ready to upgrade? <a href=\"".$_SERVER['PHP_SELF']."?upgrade=yes\">YES</a>";
}


?>
