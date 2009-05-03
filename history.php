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


//****************************************************************************
// showIndex -- default view
//****************************************************************************
function showIndex($min)
{
    DisplayHead(_UPLOADHISTORY);

    // Display Activity
    displayActivity($min);

    DisplayFoot();
}


//****************************************************************************
// displayActivity -- displays History
//****************************************************************************
function displayActivity($min=0)
{
    global $cfg, $db;

    $offset = 50;
    $inx = 0;
    $max = $min+$offset;
    $output = "";
    $morelink = "";

    $sql = "SELECT user_id, file, time FROM tf_log WHERE action=".$db->qstr($cfg["constants"]["url_upload"])." OR action=".$db->qstr($cfg["constants"]["file_upload"])." ORDER BY time desc";

    $result = $db->SelectLimit($sql, $offset, $min);
    while(list($user_id, $file, $time) = $result->FetchRow())
    {
        $user_icon = "images/user_offline.gif";
        if (IsOnline($user_id))
        {
            $user_icon = "images/user.gif";
        }

        $output .= "<tr>";
        $output .= "<td><a href=\"message.php?to_user=".$user_id."\"><img src=\"".$user_icon."\" width=17 height=14 title=\"".$user_id."\" border=0 align=\"bottom\">".$user_id."</a>&nbsp;&nbsp;</td>";
        $output .= "<td><div align=center><div class=\"tiny\" align=\"left\">";
        $output .= $file;
        $output .= "</div></td>";
        $output .= "<td><div class=\"tiny\" align=\"center\">".date(_DATETIMEFORMAT, $time)."</div></td>";
        $output .= "</tr>";

        $inx++;
    }

    if($inx == 0)
    {
        $output = "<tr><td colspan=6><center><strong>-- "._NORECORDSFOUND." --</strong></center></td></tr>";
    }

    $prev = ($min-$offset);
    if ($prev>=0)
    {
        $prevlink = "<a href=\"history.php?min=".$prev."\">";
        $prevlink .= "<font class=\"TinyWhite\">&lt;&lt;".$min." "._SHOWPREVIOUS."]</font></a> &nbsp;";
    }
    $next=$min+$offset;
    if ($inx>=$offset)
    {
        $morelink = "<a href=\"history.php?min=".$max."\">";
        $morelink .= "<font class=\"TinyWhite\">["._SHOWMORE."&gt;&gt;</font></a>";
    }

    echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr><td>";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<strong><font class=\"title\">"._UPLOADACTIVITY." (".$cfg["days_to_keep"]." "._DAYS.")</font></strong>";

    if(!empty($prevlink) && !empty($morelink))
    echo "</td><td align=\"right\">".$prevlink.$morelink."</td></tr></table>";
    elseif(!empty($prevlink))
        echo "</td><td align=\"right\">".$prevlink."</td></tr></table>";
    elseif(!empty($morelink))
        echo "</td><td align=\"right\">".$morelink."</td></tr></table>";
    else
        echo "</td><td align=\"right\"></td></tr></table>";

    echo "</td></tr>";
    echo "<tr>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._USER."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._FILE."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._TIMESTAMP."</div></td>";
    echo "</tr>";

    echo $output;

    if(!empty($prevlink) || !empty($morelink))
    {
        echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\">";
        echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr><td align=\"left\">";
        echo $prevlink;
        echo "</td><td align=\"right\">";
        echo $morelink;
        echo "</td></tr></table>";
        echo "</td></tr>";
    }

    echo "</table>";

}





//****************************************************************************
//****************************************************************************
//****************************************************************************
//****************************************************************************
// TRAFFIC CONTROLER
if(!isset($op)) $op =  "";

switch ($op) {

    default:
    if(!isset($min)) $min = 0;
        showIndex($min);
    break;

}
//****************************************************************************
//****************************************************************************
//****************************************************************************
//****************************************************************************

?>