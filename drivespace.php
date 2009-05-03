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
include_once("functions.php");


$result = shell_exec("df -h ".$cfg["path"]);
$result2 = shell_exec("du -sh ".$cfg["path"]."*");


DisplayHead(_DRIVESPACE);
echo "<table width=\"740\" border=0 cellpadding=0 cellspacing=0><tr><td>";
echo displayDriveSpaceBar(getDriveSpace($cfg["path"]));
echo "</td></tr></table>";
?>

<br>
<div align="left" id="BodyLayer" name="BodyLayer" style="border: thin solid <?php echo $cfg["main_bgcolor"] ?>; position:relative; width:740; height:500; padding-left: 5px; padding-right: 5px; z-index:1; overflow: scroll; visibility: visible">

<?php

echo "<pre>";
echo $result;
echo "<br><hr><br>";
echo $result2;
echo "</pre>";
echo "</div>";

DisplayFoot();

?>