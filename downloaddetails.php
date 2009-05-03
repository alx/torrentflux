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
include_once("AliasFile.php");

$torrent = getRequestVar('torrent');
$error = "";
$torrentowner = getOwner($torrent);
$graph_width = "";
$background = "#000000";
$alias = SecurityClean(getRequestVar('alias'));
if (!empty($alias))
{
    // read the alias file
    // create AliasFile object
    $af = new AliasFile($cfg["torrent_file_path"].$alias, $torrentowner);

    for ($inx = 0; $inx < sizeof($af->errors); $inx++)
    {
        $error .= "<li style=\"font-size:10px;color:#ff0000;\">".$af->errors[$inx]."</li>";
    }

    if ($af->seedlimit <= 0)
    {
        $af->seedlimit = "none";
    }
    else
    {
        $af->seedlimit .= "%";
    }
}
else
{
    die("fatal error torrent file not specified");
}

if ($af->percent_done < 0)
{
    $af->percent_done = round(($af->percent_done*-1)-100,1);
    $af->time_left = _INCOMPLETE;
}

if($af->percent_done < 1)
{
    $graph_width = "1";
}
else
{
    $graph_width = $af->percent_done;
}

if($af->percent_done >= 100)
{
    $af->percent_done = 100;
    $background = "#0000ff";
}

if(strlen($torrent) >= 39)
{
    $torrent = substr($torrent, 0, 35)."...";
}

$hd = getStatusImage($af);

DisplayHead(_DOWNLOADDETAILS, false, "5", $af->percent_done."% ");

?>
    <div align="center">
    <table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="center">
<?php
    if ($error != "")
    {
        echo "<img src=\"images/error.gif\" width=16 height=16 border=0 title=\"ERROR\" align=\"absmiddle\">";
    }
    echo $torrent."<font class=\"tiny\"> (".formatBytesToKBMGGB($af->size).")</font>";
?>
        </td>
        <td align="right" width="16"><img src="images/<?php echo $hd->image ?>" width=16 height=16 border=0 title="<?php echo $hd->title ?>"></td>
    </tr>
    </table>
    <table bgcolor="<?php echo $cfg["table_header_bg"] ?>" width=352 cellpadding=1>
     <tr>
         <td>
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td background="themes/<?php echo $cfg["theme"] ?>/images/proglass.gif"><img src="images/blank.gif" width="<?php echo $graph_width * 3.5 ?>" height="13" border="0"></td>
            <td background="themes/<?php echo $cfg["theme"] ?>/images/noglass.gif" bgcolor="<?php echo $background ?>"><img src="images/blank.gif" width="<?php echo (100 - $graph_width) * 3.5 ?>" height="13" border="0"></td>
        </tr>
        </table>
        </td>
     </tr>
     <tr><td>
        <div align="center">
        <table border="0" cellpadding="2" cellspacing="2" width="90%">
        <tr>
            <td align="right"><div class="tiny"><?php echo _ESTIMATEDTIME ?>:</div></td>
            <td colspan="3" bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->time_left."</strong>" ?></div></td>
        </tr>
        <tr>
            <td align="right"><div class="tiny"><?php echo _PERCENTDONE ?>:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->percent_done."%</strong>" ?></div></td>
            <td align="right"><div class="tiny"><?php echo _USER ?>:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$torrentowner."</strong>" ?></div></td>
        </tr>
        <tr>
            <td align="right"><div class="tiny"><?php echo _DOWNLOADSPEED ?>:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->down_speed."</strong>" ?></div></td>
            <td align="right"><div class="tiny"><?php echo _UPLOADSPEED ?>:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->up_speed."</strong>" ?></div></td>
        </tr>
        <tr>
            <td align="right"><div class="tiny">Down:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".formatFreeSpace($af->GetRealDownloadTotal())."</strong>" ?></div></td>
            <td align="right"><div class="tiny">Up:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".formatFreeSpace($af->uptotal/(1024*1024))."</strong>" ?></div></td>
        </tr>
        <tr>
            <td align="right"><div class="tiny">Seeds:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->seeds."</strong>" ?></div></td>
            <td align="right"><div class="tiny">Peers:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->peers."</strong>" ?></div></td>
        </tr>
        <tr>
            <td align="right"><div class="tiny"><?php echo _SHARING ?>:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->sharing."%</strong>" ?></div></td>
            <td align="right"><div class="tiny">Seed Until:</div></td>
            <td bgcolor="<?php echo $cfg["body_data_bg"] ?>"><div class="tiny"><?php echo "<strong>".$af->seedlimit."</strong>" ?></div></td>
        </tr>
<?php
    if ($error != "")
    {
?>
        <tr>
            <td align="right" valign="top"><div class="tiny">Error(s):</div></td>
            <td colspan="3" width="66%"><div class="tiny"><?php echo "<strong class=\"tiny\">".$error."</strong>" ?></div></td>
        </tr>
<?php
    }
?>
        </table>
    </div>
</td></tr></table>
<?php

DisplayFoot(false);

?>