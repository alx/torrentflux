<?php

/*************************************************************
*  TorrentFlux - PHP Torrent Manager
*  www.torrentflux.com
**************************************************************/
/*
    This file is part of TorrentFlux.

    TorrentFlux is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    TorrentFlux is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with TorrentFlux; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


include_once("config.php");

// Start Session and grab user
session_name("TorrentFlux");
session_start();
$cfg["user"] = strtolower($_SESSION['user']);

// 2004-12-09 PFM
include_once('db.php');

// Create Connection.
$db = getdb();

logoutUser();
session_destroy();
header('location: login.php');

// Remove history for user so they are logged off from screen
function logoutUser()
{
    global $cfg, $db;

    $sql = "DELETE FROM tf_log WHERE user_id=".$db->qstr($cfg["user"])." and action=".$db->qstr($cfg["constants"]["hit"]);

    // do the SQL
    $result = $db->Execute($sql);
    showError($db, $sql);
}
?>