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


if(empty($cfg['user']))
{
     // the user probably hit this page direct
    header("location: index.php");
    exit;
}

$delete = getRequestVar('delete');
if(!empty($delete))
{
    DeleteMessage($delete);
    header("location: ".$_SERVER['PHP_SELF']);
}

$mid = getRequestVar('mid');
if (!empty($mid) && is_numeric($mid))
{
    list($from_user, $message, $ip, $time, $isnew, $force_read) = GetMessage($mid);
    if(!empty($from_user) && $isnew == 1)
    {
        // We have a Message that is being seen
        // Mark it as NOT new.
        MarkMessageRead($mid);
    }

    DisplayHead(_MESSAGES);
    $message = check_html($message, "nohtml");
    $message = str_replace("\n", "<br>", $message);
    echo "<a href=\"".$_SERVER['PHP_SELF']."\"><img src=\"images/up_dir.gif\" width=16 height=16 title=\""._RETURNTOMESSAGES."\" border=0>"._RETURNTOMESSAGES."</a><br>";
    echo "<table width=\"740\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\"><tr>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" colspan=2>";
    echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr><td>";
    echo _FROM.": <strong>".$from_user."</strong></td><td align=\"right\">";
    if (IsUser($from_user))
    {
        echo "<a href=\"message.php?to_user=".$from_user."&rmid=".$mid."\"><img src=\"images/reply.gif\" width=16 height=16 title=\""._REPLY."\" border=0></a>";
    }
    echo "<a href=\"".$_SERVER['PHP_SELF']."?delete=".$mid."\"><img src=\"images/delete_on.gif\" width=16 height=16 title=\""._DELETE."\" border=0></a></td></tr></table>";
    echo "</td></tr>";
    echo "<tr><td colspan=2>"._DATE.":  <strong>".date(_DATETIMEFORMAT, $time)."</strong></td></tr>";
    echo "</tr><td colspan=2 bgcolor=\"".$cfg["table_data_bg"]."\">"._MESSAGE.":<blockquote><strong>".$message."</strong></blockquote></td></tr>";
    echo "</table>";
}
else
{
    DisplayHead(_MESSAGES);
    // read and display all messages in a list.
    $inx = 0;
    DisplayMessageList();
    echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\"><tr>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"20%\"><div align=center class=\"title\">"._FROM."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._MESSAGE."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"20%\"><div align=center class=\"title\">"._DATE."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"10%\"><div align=center class=\"title\">"._ADMIN."</div></td>";
    echo "</tr>";

    $sql = "SELECT mid, from_user, message, IsNew, ip, time, force_read FROM tf_messages WHERE to_user=".$db->qstr($cfg['user'])." ORDER BY time";
    $result = $db->Execute($sql);
    showError($db,$sql);

    while(list($mid, $from_user, $message, $new, $ip, $time, $force_read) = $result->FetchRow())
    {
        if($new == 1)
        {
            $mail_image = "images/new_message.gif";
        }
        else
        {
            $mail_image = "images/old_message.gif";
        }
        $display_message = check_html($message, "nohtml");
        if(strlen($display_message) >= 40) { // needs to be trimmed
            $display_message = substr($display_message, 0, 39);
            $display_message .= "...";
        }
        $link = $_SERVER['PHP_SELF']."?mid=".$mid;

        echo "<tr><td>&nbsp;&nbsp;<a href=\"".$link."\"><img src=\"".$mail_image."\" width=14 height=11 title=\"\" border=0 align=\"absmiddle\"></a>&nbsp;&nbsp; <a href=\"".$link."\">".$from_user."</a></td>";
        echo "<td><a href=\"".$link."\">".$display_message."</a></td>";
        echo "<td align=\"center\"><a href=\"".$link."\">".date(_DATETIMEFORMAT, $time)."</a></td>";
        echo "<td align=\"right\">";

        // Is this a force_read from an admin?
        if ($force_read == 1)
        {
            // Yes, then don't let them delete the message yet
            echo "<img src=\"images/delete_off.gif\" width=16 height=16 title=\"\" border=0>";
        }
        else
        {
            // No, let them reply or delete it
            if (IsUser($from_user))
            {
                echo "<a href=\"message.php?to_user=".$from_user."&rmid=".$mid."\"><img src=\"images/reply.gif\" width=16 height=16 title=\""._REPLY."\" border=0></a>";
            }
            echo "<a href=\"".$_SERVER['PHP_SELF']."?delete=".$mid."\"><img src=\"images/delete_on.gif\" width=16 height=16 title=\""._DELETE."\" border=0></a></td></tr>";
        }
        $inx++;
    } // End While
    echo "</table>";

    if($inx == 0)
    {
        echo "<div align=\"center\"><strong>-- "._NORECORDSFOUND." --</strong></div>";
    }

} // end the else

DisplayFoot();
?>