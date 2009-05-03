<?php

################################################################
# File to upgrade from TorrentFlux 1.0 or 1.1 to TorrentFlux 1.2
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

// is there a stat and torrent dir?  If not then it will create it.
checkTorrentPath();

if ($upgrade == "yes")
{
	// Messages Table Alteration
	mysql_query("ALTER TABLE tf_messages ADD force_read TINYINT(1) DEFAULT '0'");
	
	// Users Table Alteration
	mysql_query("ALTER TABLE tf_users ADD language_file VARCHAR(60) DEFAULT 'lang-english.php'");
	
	// Migrate all stat and torrent files to the hiddent .torrents directory
	echo "Moving stat files to: ".$cfg["torrent_file_path"]."<br><br>";
	shell_exec("mv ".$cfg["path"]."*.stat ".$cfg["torrent_file_path"].".");
	
	echo "Moving torrent files to: ".$cfg["torrent_file_path"]."<br><br>";
	shell_exec("mv ".$cfg["path"]."*.torrent ".$cfg["torrent_file_path"].".");
	
	echo "TorrentFlux upgrade to version 1.2 is complete!<br><br>"
	    ."You should now delete this upgrade file from your server.<br><br>";
		
	echo "<a href=\"index.php\">DONE</a>";
}
else
{
	echo "<ol>";
	echo "<li>Make sure all your torrent downloads are stopped before performing upgrade.</li>";
	echo "<li>Make sure you have copied over all the new PHP files.</li>";
	echo "<li>Make suer you have edited your new config.php</li>";
	echo "</ol>";
	echo "Are you ready to upgrade? <a href=\"".$_SERVER['PHP_SELF']."?upgrade=yes\">YES</a>";
}


// ***************************************************************************
// ***************************************************************************
// Checks for the location of the torrents 
// If it does not exist, then it creates it.
function checkTorrentPath()
{
	global $cfg;
	// is there a stat and torrent dir?
	if (!is_dir($cfg["torrent_file_path"]))
	{
		//Then create it
		mkdir($cfg["torrent_file_path"]);
	}
}


?>
