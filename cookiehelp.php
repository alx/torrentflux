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

DisplayHead("Cookie Help", false);
?>
<script language="JavaScript">
    function closeme()
    {
        self.close();
    }
</script>
<BR />
<div align="center">[ <a href="#" onClick="closeme();">close</a> ]</div>
<BR />
<div align="left" id="BodyLayer" name="BodyLayer" style="border: thin solid <?php echo $cfg["main_bgcolor"] ?>; position:relative; width:580; padding-left: 5px; padding-right: 5px; z-index:1; visibility: visible">
<strong>How to get Cookie information....</strong>
<br>
<hr>
<br>
<strong>FireFox</strong>
<ul>
    <li>Tools => Options</li>
    <li>Cookies => View Cookies</li>
    <li>Locate the site you want to get cookie information from.</li>
    <li>Get the UID and PASS content fields</li>
</ul>

<hr>
<br>
<strong>Internet Explorer</strong>
<ul>
    <li>Tools => Internet Options</li>
    <li>General => Settings => View Files</li>
    <li>Locate cookie file for site (eg: Cookie:user@www.host.com/)</li>
    <li>Open the file in a text editor</li>
    <li>Grab the values below UID and PASS</li>
</ul>
The file will look something like this:
<pre>
------

userZone
-660
www.host.com/
1600
2148152320
29840330
125611120
29766905
*
uid
123456 <----------------------------
www.host.com/
1536
3567643008
32111902
4197448416
29766904
*
pass
0j9i8h7g6f5e4d3c2b1a <--------------
www.host.com/
1536
3567643008
32111902
4197448416
29766904
*

--------
</pre>
<BR />
<div align="center">[ <a href="#" onClick="closeme();">close</a> ]</div>
<BR />
</div>
<?php
DisplayFoot(false);
?>