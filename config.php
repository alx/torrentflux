<?php

/*************************************************************
*  TorrentFlux PHP Torrent Manager
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


/**************************************************************************/
// YOUR DATABASE CONNECTION INFORMATION
/**************************************************************************/
// Check the adodb/drivers/ directory for support for your database
// you may choose from many (mysql is the default)
$cfg["db_type"] = "mysql";       // mysql, postgres7, postgres8 view adodb/drivers/
$cfg["db_host"] = "localhost";   // DB host computer name or IP
$cfg["db_name"] = "torrentflux"; // Name of the Database
$cfg["db_user"] = "root";        // username for your MySQL database
$cfg["db_pass"] = "";            // password for database
/**************************************************************************/


/*****************************************************************************
    TorrentFlux
    Torrent (n.) A violent or rapid flow; a strong current; a flood;
            as, a torrent vices; a torrent of eloquence.
    Flux    (n.) The act of flowing; a continuous moving on or passing by,
            as of a flowing stream; constant succession; change.
*****************************************************************************/



// ***************************************************************************
// ***************************************************************************
// DO NOT Edit below this line unless you know what you're doing.
// ***************************************************************************
// ***************************************************************************

$cfg["pagetitle"] = "TorrentFlux";

// TorrentFlux Version
$cfg["version"] = "2.4";

// CONSTANTS
$cfg["constants"] = array();
$cfg["constants"]["url_upload"] = "URL Upload";
$cfg["constants"]["reset_owner"] = "Reset Owner";
$cfg["constants"]["start_torrent"] = "Started Torrent";
$cfg["constants"]["queued_torrent"] = "Queued Torrent";
$cfg["constants"]["unqueued_torrent"] = "Removed from Queue";
$cfg["constants"]["QManager"] = "QManager";
$cfg["constants"]["access_denied"] = "ACCESS DENIED";
$cfg["constants"]["delete_torrent"] = "Delete Torrent";
$cfg["constants"]["fm_delete"] = "File Manager Delete";
$cfg["constants"]["fm_download"] = "File Download";
$cfg["constants"]["kill_torrent"] = "Kill Torrent";
$cfg["constants"]["file_upload"] = "File Upload";
$cfg["constants"]["error"] = "ERROR";
$cfg["constants"]["hit"] = "HIT";
$cfg["constants"]["update"] = "UPDATE";
$cfg["constants"]["admin"] = "ADMIN";

asort($cfg["constants"]);

// Add file extensions here that you will allow to be uploaded
$cfg["file_types_array"] = array("torrent");

// Capture username
$cfg["user"] = "";
// Capture ip
$cfg["ip"] = $_SERVER['REMOTE_ADDR'];

?>
