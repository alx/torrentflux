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

// contributed by NovaKing -- thanks duder!

include_once("config.php");
include_once("functions.php");

DisplayHead("View NFO");

$file = SecurityCleanPath(getRequestVar("path"));
$folder = htmlspecialchars( substr( $file, 0, strrpos( $file, "/" ) ) );

if( ( $output = @file_get_contents( $cfg["path"] . $file ) ) === false )
    $output = "Error opening NFO File.";
?>
<div align="center" style="width: 740px;">
<a href="<?php echo "viewnfo.php?path=$file&dos=1"; ?>">DOS Format</a> :-:
<a href="<?php echo "viewnfo.php?path=$file&win=1"; ?>">WIN Format</a> :-:
<a href="dir.php?dir=<?php echo $folder;?>">Back</a>
</div>
<pre style="font-size: 10pt; font-family: 'Courier New', monospace;">
<?php
    if( ( empty( $_REQUEST["dos"] ) && empty( $_REQUEST["win"] ) ) || !empty( $_REQUEST["dos"] ) )
        echo htmlentities( $output, ENT_COMPAT, "cp866" );
    else
        echo htmlentities( $output );
?>
</pre>
<?php
DisplayFoot();

//**************************************************************************
// SecurityCleanPath()
// Cleans the file name and restricts it to only txt and nfo
function SecurityCleanPath($string)
{
    global $cfg;
    
    if (empty($string))
    {
        return $string;
    }
    
    $array = array("<", ">", "\\", "//", "..", "'");
    foreach ($array as $char)
    {
        $string = str_replace($char, NULL, $string);
    }
        
    if( (strtolower( substr( $string, -4 ) ) == ".txt") || (strtolower( substr( $string, -4 ) ) == ".nfo") )
    {
        // we are good
    }
    else
    {
        AuditAction($cfg["constants"]["error"], "Not a text or NFO: " . $string);
        die("Invalid file specified.  Action has been logged.");
    }
    return $string;
}
?>