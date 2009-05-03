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

if(!IsAdmin())
{
     // the user probably hit this page direct
    AuditAction($cfg["constants"]["access_denied"], $_SERVER['PHP_SELF']);
    header("location: index.php");
}

//****************************************************************************
function editLink($lid,$newLink,$newSite)
{
    global $cfg;

    if(!empty($newLink))
    {
        if(strpos($newLink, "http://" ) !== 0 && strpos($newLink, "https://" ) !== 0 && strpos($newLink, "ftp://" ) !== 0)
        {
            $newLink = "http://".$newLink;
        }
        empty($newSite) && $newSite = $newLink;

        $oldLink = getLink($lid);
        $oldSite = getSite($lid);
        alterLink($lid, $newLink, $newSite);
        AuditAction($cfg["constants"]["admin"], "Change Link: ".$oldSite." [".$oldLink."] -> ".$newSite." [".$newLink."]");
    }
    header("location: admin.php?op=editLinks");
}

//****************************************************************************
// addLink -- adding a link
//****************************************************************************
function addLink($newLink,$newSite)
{
    if(!empty($newLink))
    {
        if(strpos($newLink, "http://" ) !== 0 && strpos($newLink, "https://" ) !== 0 && strpos($newLink, "ftp://" ) !== 0)
        {
            $newLink = "http://".$newLink;
        }
        empty($newSite) && $newSite = $newLink;
        global $cfg;
        addNewLink($newLink, $newSite);
        AuditAction($cfg["constants"]["admin"], "New "._LINKS_MENU.": ".$newSite." [".$newLink."]");
    }
    header("location: admin.php?op=editLinks");
}

//****************************************************************************
// moveLink -- moving a link up or down in the list of links
//****************************************************************************
function moveLink($lid, $direction)
{
    global $db, $cfg;
    if  (!isset($lid) && !isset($direction)&& $direction !== "up" && $direction !== "down" )
    {
        header("location: admin.php?op=editLinks");
    }
    $idx = getLinkSortOrder($lid);
    $position = array("up"=>-1, "down"=>1);
    $new_idx = $idx+$position[$direction];
    $sql = "UPDATE tf_links SET sort_order=".$idx." WHERE sort_order=".$new_idx;
    $db->Execute($sql);
    showError($db, $sql);
    $sql = "UPDATE tf_links SET sort_order=".$new_idx." WHERE lid=".$lid;
    $db->Execute($sql);
    showError($db, $sql);
    header("Location: admin.php?op=editLinks");
}

//****************************************************************************
// addRSS -- adding a RSS link
//****************************************************************************
function addRSS($newRSS)
{
    if(!empty($newRSS)){
        global $cfg;
        addNewRSS($newRSS);
        AuditAction($cfg["constants"]["admin"], "New RSS: ".$newRSS);
    }
    header("location: admin.php?op=editRSS");
}

//****************************************************************************
// addUser -- adding a user
//****************************************************************************
function addUser($newUser, $pass1, $userType)
{
    global $cfg;
    $newUser = strtolower($newUser);
    if (IsUser($newUser))
    {
        DisplayHead(_ADMINISTRATION);

        // Admin Menu
        displayMenu();

        echo "<br><div align=\"center\">"._TRYDIFFERENTUSERID."<br><strong>".$newUser."</strong> "._HASBEENUSED."</div><br><br><br>";

        DisplayFoot();
    }
    else
    {
        addNewUser($newUser, $pass1, $userType);
        AuditAction($cfg["constants"]["admin"], _NEWUSER.": ".$newUser);
        header("location: admin.php?op=CreateUser");
    }
}

//****************************************************************************
// updateUser -- updating a user
//****************************************************************************
function updateUser($user_id, $org_user_id, $pass1, $userType, $hideOffline)
{
    global $cfg;
    $user_id = strtolower($user_id);
    if (IsUser($user_id) && ($user_id != $org_user_id))
    {
        DisplayHead(_ADMINISTRATION);

        // Admin Menu
        displayMenu();

        echo "<br><div align=\"center\">"._TRYDIFFERENTUSERID."<br><strong>".$user_id."</strong> "._HASBEENUSED."<br><br><br>";

        echo "[<a href=\"admin.php?op=editUser&user_id=".$org_user_id."\">"._RETURNTOEDIT." ".$org_user_id."</a>]</div><br><br><br>";

        DisplayFoot();
    }
    else
    {
        // Admin is changing id or password through edit screen
        if(($user_id == $cfg["user"] || $cfg["user"] == $org_user_id) && $pass1 != "")
        {
            // this will expire the user
            $_SESSION['user'] = md5($cfg["pagetitle"]);
        }
        updateThisUser($user_id, $org_user_id, $pass1, $userType, $hideOffline);
        AuditAction($cfg["constants"]["admin"], _EDITUSER.": ".$user_id);
        header("location: admin.php");
    }
}

//****************************************************************************
// deleteLink -- delete a link
//****************************************************************************
function deleteLink($lid)
{
    global $cfg;
    AuditAction($cfg["constants"]["admin"], _DELETE." Link: ".getSite($lid)." [".getLink($lid)."]");
    deleteOldLink($lid);
    header("location: admin.php?op=editLinks");
}

//****************************************************************************
// deleteRSS -- delete a RSS link
//****************************************************************************
function deleteRSS($rid)
{
    global $cfg;
    AuditAction($cfg["constants"]["admin"], _DELETE." RSS: ".getRSS($rid));
    deleteOldRSS($rid);
    header("location: admin.php?op=editRSS");
}

//****************************************************************************
// deleteUser -- delete a user (only non super admin)
//****************************************************************************
function deleteUser($user_id)
{
    global $cfg;
    if (!IsSuperAdmin($user_id))
    {
        DeleteThisUser($user_id);
        AuditAction($cfg["constants"]["admin"], _DELETE." "._USER.": ".$user_id);
    }
    header("location: admin.php");
}

//****************************************************************************
// showIndex -- default view
//****************************************************************************
function showIndex($min = 0)
{
    global $cfg;
    DisplayHead(_ADMINISTRATION);

    // Admin Menu
    displayMenu();

    // Show User Section
    displayUserSection();

    echo "<br>";

    // Display Activity
    displayActivity($min);

    DisplayFoot();

}


//****************************************************************************
// showUserActivity -- Activity for a user
//****************************************************************************
function showUserActivity($min=0, $user_id="", $srchFile="", $srchAction="")
{
    global $cfg;

    DisplayHead(_ADMINUSERACTIVITY);

    // Admin Menu
    displayMenu();

    // display Activity for user
    displayActivity($min, $user_id, $srchFile, $srchAction);

    DisplayFoot();

}



//****************************************************************************
// backupDatabase -- backup the database
//****************************************************************************
function backupDatabase()
{
    global $cfg;

    $file = $cfg["db_name"]."_".date("Ymd").".tar.gz";
    $back_file = $cfg["torrent_file_path"].$file;
    $sql_file = $cfg["torrent_file_path"].$cfg["db_name"].".sql";

    $sCommand = "";
    switch($cfg["db_type"])
    {
        case "mysql":
            $sCommand = "mysqldump -h ".$cfg["db_host"]." -u ".$cfg["db_user"]." --password=".$cfg["db_pass"]." --all -f ".$cfg["db_name"]." > ".$sql_file;
            break;
        default:
            // no support for backup-on-demand.
            $sCommand = "";
            break;
    }

    if($sCommand != "")
    {
        shell_exec($sCommand);
        shell_exec("tar -czvf ".$back_file." ".$sql_file);

        // Get the file size
        $file_size = filesize($back_file);

        // open the file to read
        $fo = fopen($back_file, 'r');
        $fr = fread($fo, $file_size);
        fclose($fo);

        // Set the headers
        header("Content-type: APPLICATION/OCTET-STREAM");
        header("Content-Length: ".$file_size.";");
        header("Content-Disposition: attachement; filename=".$file);

        // send the tar baby
        echo $fr;

        // Cleanup
        shell_exec("rm ".$sql_file);
        shell_exec("rm ".$back_file);
        AuditAction($cfg["constants"]["admin"], _BACKUP_MENU.": ".$file);
    }
}


//****************************************************************************
// displayMenu -- displays Admin Menu
//****************************************************************************
function displayMenu()
{
    global $cfg;
    echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\"><div align=\"center\">";
    echo "<a href=\"admin.php\"><font class=\"adminlink\">"._ADMIN_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=configSettings\"><font class=\"adminlink\">"._SETTINGS_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=queueSettings\"><font class=\"adminlink\">"._QMANAGER_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=searchSettings\"><font class=\"adminlink\">"._SEARCHSETTINGS_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=showUserActivity\"><font class=\"adminlink\">"._ACTIVITY_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=editLinks\"><font class=\"adminlink\">"._LINKS_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=editRSS\"><font class=\"adminlink\">rss</font></a> | ";
    echo "<a href=\"admin.php?op=CreateUser\"><font class=\"adminlink\">"._NEWUSER_MENU."</font></a> | ";
    echo "<a href=\"admin.php?op=backupDatabase\"><font class=\"adminlink\">"._BACKUP_MENU."</font></a>";
    echo "</div></td></tr>";
    echo "</table><br>";

}


//****************************************************************************
// displayActivity -- displays Activity
//****************************************************************************
function displayActivity($min=0, $user="", $srchFile="", $srchAction="")
{
    global $cfg, $db;

    $sqlForSearch = "";

    $userdisplay = $user;

    if($user != "")
    {
        $sqlForSearch .= "user_id='".$user."' AND ";
    }
    else
    {
        $userdisplay = _ALLUSERS;
    }

    if($srchFile != "")
    {
        $sqlForSearch .= "file like '%".$srchFile."%' AND ";
    }

    if($srchAction != "")
    {
        $sqlForSearch .= "action like '%".$srchAction."%' AND ";
    }

    $offset = 50;
    $inx = 0;
    if (!isset($min)) $min=0;
    $max = $min+$offset;
    $output = "";
    $morelink = "";

    $sql = "SELECT user_id, file, action, ip, ip_resolved, user_agent, time FROM tf_log WHERE ".$sqlForSearch."action!=".$db->qstr($cfg["constants"]["hit"])." ORDER BY time desc";

    $result = $db->SelectLimit($sql, $offset, $min);
    while(list($user_id, $file, $action, $ip, $ip_resolved, $user_agent, $time) = $result->FetchRow())
    {
        $user_icon = "images/user_offline.gif";
        if (IsOnline($user_id))
        {
            $user_icon = "images/user.gif";
        }

        $ip_info = htmlentities($ip_resolved, ENT_QUOTES)."<br>".htmlentities($user_agent, ENT_QUOTES);

        $output .= "<tr>";
        if (IsUser($user_id))
        {
            $output .= "<td><a href=\"message.php?to_user=".$user_id."\"><img src=\"".$user_icon."\" width=17 height=14 title=\""._SENDMESSAGETO." ".$user_id."\" border=0 align=\"bottom\">".$user_id."</a>&nbsp;&nbsp;</td>";
        }
        else
        {
            $output .= "<td><img src=\"".$user_icon."\" width=17 height=14 title=\"n/a\" border=0 align=\"bottom\">".$user_id."&nbsp;&nbsp;</td>";
        }
        $output .= "<td><div class=\"tiny\">".htmlentities($action, ENT_QUOTES)."</div></td>";
        $output .= "<td><div align=center><div class=\"tiny\" align=\"left\">";
        $output .= htmlentities($file, ENT_QUOTES);
        $output .= "</div></td>";
        $output .= "<td><div class=\"tiny\" align=\"left\"><a href=\"javascript:void(0)\" onclick=\"return overlib('".$ip_info."<br>', STICKY, CSSCLASS);\" onmouseover=\"return overlib('".$ip_info."<br>', CSSCLASS);\" onmouseout=\"return nd();\"><img src=\"images/properties.png\" width=\"18\" height=\"13\" border=\"0\"><font class=tiny>".htmlentities($ip, ENT_QUOTES)."</font></a></div></td>";
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
        $prevlink = "<a href=\"admin.php?op=showUserActivity&min=".$prev."&user_id=".$user."&srchFile=".$srchFile."&srchAction=".$srchAction."\">";
        $prevlink .= "<font class=\"TinyWhite\">&lt;&lt;".$min." "._SHOWPREVIOUS."]</font></a> &nbsp;";
    }
    if ($inx>=$offset)
    {
        $morelink = "<a href=\"admin.php?op=showUserActivity&min=".$max."&user_id=".$user."&srchFile=".$srchFile."&srchAction=".$srchAction."\">";
        $morelink .= "<font class=\"TinyWhite\">["._SHOWMORE."&gt;&gt;</font></a>";
    }
?>
    <div id="overDiv" style="position:absolute;visibility:hidden;z-index:1000;"></div>
    <script language="JavaScript">
        var ol_closeclick = "1";
        var ol_close = "<font color=#ffffff><b>X</b></font>";
        var ol_fgclass = "fg";
        var ol_bgclass = "bg";
        var ol_captionfontclass = "overCaption";
        var ol_closefontclass = "overClose";
        var ol_textfontclass = "overBody";
        var ol_cap = "&nbsp;IP Info";
    </script>
    <script src="overlib.js" type="text/javascript"></script>
    <div align="center">
    <table>
    <form action="admin.php?op=showUserActivity" name="searchForm" method="post">
    <tr>
        <td>
        <strong><?php echo _ACTIVITYSEARCH ?></strong>&nbsp;&nbsp;&nbsp;
        <?php echo _FILE ?>:
        <input type="Text" name="srchFile" value="<?php echo $srchFile ?>" width="30"> &nbsp;&nbsp;
        <?php echo _ACTION ?>:
        <select name="srchAction">
        <option value="">-- <?php echo _ALL ?> --</option>
<?php
        $selected = "";
        if(is_array($cfg["constants"] ))
        {
            foreach ($cfg["constants"] as $action)
            {
                $selected = "";
                if($action != $cfg["constants"]["hit"])
                {
                    if($srchAction == $action)
                    {
                        $selected = "selected";
                    }
                    echo "<option value=\"".htmlentities($action, ENT_QUOTES)."\" ".$selected.">".htmlentities($action, ENT_QUOTES)."</option>";
                }
            }
        }
?>
        </select>&nbsp;&nbsp;
        <?php echo _USER ?>:
        <select name="user_id">
        <option value="">-- <?php echo _ALL ?> --</option>
<?php
        $users = GetUsers();
        $selected = "";
        for($inx = 0; $inx < sizeof($users); $inx++)
        {
            $selected = "";
            if($user == $users[$inx])
            {
                $selected = "selected";
            }
            echo "<option value=\"".htmlentities($users[$inx], ENT_QUOTES)."\" ".$selected.">".htmlentities($users[$inx], ENT_QUOTES)."</option>";
        }
?>
        </select>
        <input type="Submit" value="<?php echo _SEARCH ?>">

        </td>
    </tr>
    </form>
    </table>
    </div>


<?php
    echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr><td>";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">"._ACTIVITYLOG." ".$cfg["days_to_keep"]." "._DAYS." (".$userdisplay.")</font>";
    if(!empty($prevlink) && !empty($morelink))
        echo "</td><td align=\"right\">".$prevlink.$morelink."</td></tr></table>";
    elseif(!empty($prevlink))
        echo "</td><td align=\"right\">".$prevlink."</td></tr></table>";
    elseif(!empty($prevlink))
        echo "</td><td align=\"right\">".$morelink."</td></tr></table>";
    else
        echo "</td><td align=\"right\"></td></tr></table>";
    echo "</td></tr>";
    echo "<tr>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._USER."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._ACTION."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._FILE."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"13%\"><div align=center class=\"title\">"._IP."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._TIMESTAMP."</div></td>";
    echo "</tr>";

    echo $output;

    if(!empty($prevlink) || !empty($morelink))
    {
        echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\">";
        echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr><td align=\"left\">";
        if(!empty($prevlink)) echo $prevlink;
        echo "</td><td align=\"right\">";
        if(!empty($morelink)) echo $morelink;
        echo "</td></tr></table>";
        echo "</td></tr>";
    }

    echo "</table>";

}



//****************************************************************************
// displayUserSection -- displays the user section
//****************************************************************************
function displayUserSection()
{
    global $cfg, $db;

    echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\"><img src=\"images/user_group.gif\" width=17 height=14 border=0>&nbsp;&nbsp;<font class=\"title\">"._USERDETAILS."</font></div></td></tr>";
    echo "<tr>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._USER."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"6%\"><div align=center class=\"title\">"._HITS."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._UPLOADACTIVITY." (".$cfg["days_to_keep"]." "._DAYS.")</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"6%\"><div align=center class=\"title\">"._JOINED."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._LASTVISIT."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"8%\"><div align=center class=\"title\">"._ADMIN."</div></td>";
    echo "</tr>";

    $total_activity = GetActivityCount();

    $sql= "SELECT user_id, hits, last_visit, time_created, user_level FROM tf_users ORDER BY user_id";
    $result = $db->Execute($sql);
    while(list($user_id, $hits, $last_visit, $time_created, $user_level) = $result->FetchRow())
    {
        $user_activity = GetActivityCount($user_id);

        if ($user_activity == 0)
        {
            $user_percent = 0;
        }
        else
        {
            $user_percent = number_format(($user_activity/$total_activity)*100);
        }
        $user_icon = "images/user_offline.gif";
        if (IsOnline($user_id))
        {
            $user_icon = "images/user.gif";
        }

        echo "<tr>";
        if (IsUser($user_id))
        {
            echo "<td><a href=\"message.php?to_user=".$user_id."\"><img src=\"".$user_icon."\" width=17 height=14 title=\""._SENDMESSAGETO." ".$user_id."\" border=0 align=\"bottom\">".$user_id."</a></td>";
        }
        else
        {
            echo "<td><img src=\"".$user_icon."\" width=17 height=14 title=\"n/a\" border=0 align=\"bottom\">".$user_id."</td>";
        }
        echo "<td><div class=\"tiny\" align=\"right\">".$hits."</div></td>";
        echo "<td><div align=center>";
?>
        <table width="310" border="0" cellpadding="0" cellspacing="0">
        <tr>
        <td width="200">
            <table width="200" border="0" cellpadding="0" cellspacing="0">
            <tr>
                <td background="themes/<?php echo $cfg["theme"] ?>/images/proglass.gif" width="<?php echo $user_percent*2 ?>"><img src="images/blank.gif" width="1" height="12" border="0"></td>
                <td background="themes/<?php echo $cfg["theme"] ?>/images/noglass.gif" width="<?php echo (200 - ($user_percent*2)) ?>"><img src="images/blank.gif" width="1" height="12" border="0"></td>
            </tr>
            </table>
        </td>
        <td align="right" width="40"><div class="tiny" align="right"><?php echo $user_activity ?></div></td>
        <td align="right" width="40"><div class="tiny" align="right"><?php echo $user_percent ?>%</div></td>
        <td align="right"><a href="admin.php?op=showUserActivity&user_id=<?php echo $user_id ?>"><img src="images/properties.png" width="18" height="13" title="<?php echo $user_id."'s "._USERSACTIVITY ?>" border="0"></a></td>
        </tr>
        </table>
<?php
        echo "</td>";
        echo "<td><div class=\"tiny\" align=\"center\">".date(_DATEFORMAT, $time_created)."</div></td>";
        echo "<td><div class=\"tiny\" align=\"center\">".date(_DATETIMEFORMAT, $last_visit)."</div></td>";
        echo "<td><div align=\"right\" class=\"tiny\">";
        $user_image = "images/user.gif";
        $type_user = _NORMALUSER;
        if ($user_level == 1)
        {
            $user_image = "images/admin_user.gif";
            $type_user = _ADMINISTRATOR;
        }
        if ($user_level == 2)
        {
            $user_image = "images/superadmin.gif";
            $type_user = _SUPERADMIN;
        }
        if ($user_level <= 1 || IsSuperAdmin())
        {
            echo "<a href=\"admin.php?op=editUser&user_id=".$user_id."\"><img src=\"images/edit.png\" width=12 height=13 title=\""._EDIT." ".$user_id."\" border=0></a>";
        }
        echo "<img src=\"".$user_image."\" title=\"".$user_id." - ".$type_user."\">";
        if ($user_level <= 1)
        {
            echo "<a href=\"admin.php?op=deleteUser&user_id=".$user_id."\"><img src=\"images/delete_on.gif\" border=0 width=16 height=16 title=\""._DELETE." ".$user_id."\" onclick=\"return ConfirmDeleteUser('".$user_id."')\"></a>";
        }
        else
        {
            echo "<img src=\"images/delete_off.gif\" width=16 height=16 title=\"n/a\">";
        }

        echo "</div></td>";
        echo "</tr>";
    }

    echo "</table>";
?>
    <script language="JavaScript">
    function ConfirmDeleteUser(user)
    {
        return confirm("<?php echo _WARNING.": "._ABOUTTODELETE ?>: " + user)
    }
    </script>
<?php
}


//****************************************************************************
// editUser -- edit a user
//****************************************************************************
function editUser($user_id)
{
    global $cfg, $db;
    DisplayHead(_USERADMIN);

    $editUserImage = "images/user.gif";
    $selected_n = "selected";
    $selected_a = "";

    $hide_checked = "";

    // Admin Menu
    displayMenu();

    $total_activity = GetActivityCount();

    $sql= "SELECT user_id, hits, last_visit, time_created, user_level, hide_offline, theme, language_file FROM tf_users WHERE user_id=".$db->qstr($user_id);

    list($user_id, $hits, $last_visit, $time_created, $user_level, $hide_offline, $theme, $language_file) = $db->GetRow($sql);

    $user_type = _NORMALUSER;
    if ($user_level == 1)
    {
        $user_type = _ADMINISTRATOR;
        $selected_n = "";
        $selected_a = "selected";
        $editUserImage = "images/admin_user.gif";
    }
    if ($user_level >= 2)
    {
        $user_type = _SUPERADMIN;
        $editUserImage = "images/superadmin.gif";
    }

    if ($hide_offline == 1)
    {
        $hide_checked = "checked";
    }


    $user_activity = GetActivityCount($user_id);

    if ($user_activity == 0)
    {
        $user_percent = 0;
    }
    else
    {
        $user_percent = number_format(($user_activity/$total_activity)*100);
    }

    echo "<div align=\"center\">";
    echo "<table width=\"100%\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"".$editUserImage."\" width=17 height=14 border=0>&nbsp;&nbsp;&nbsp;<font class=\"title\">"._EDITUSER.": ".$user_id."</font>";
    echo "</td></tr><tr><td align=\"center\">";
?>

    <table width="100%" border="0" cellpadding="3" cellspacing="0">
    <tr>
        <td width="50%" bgcolor="<?php echo $cfg["table_data_bg"]?>" valign="top">

        <div align="center">
        <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="right"><?php echo $user_id." "._JOINED ?>:&nbsp;</td>
            <td><strong><?php echo date(_DATETIMEFORMAT, $time_created) ?></strong></td>
        </tr>
        <tr>
            <td align="right"><?php echo _LASTVISIT ?>:&nbsp;</td>
            <td><strong><?php echo date(_DATETIMEFORMAT, $last_visit) ?></strong></td>
        </tr>
        <tr>
            <td colspan="2" align="center">&nbsp;</td>
        </tr>
        <tr>
            <td align="right"><?php echo _UPLOADPARTICIPATION ?>:&nbsp;</td>
            <td>
                <table width="200" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td background="themes/<?php echo $cfg["theme"] ?>/images/proglass.gif" width="<?php echo $user_percent*2 ?>"><img src="images/blank.gif" width="1" height="12" border="0"></td>
                    <td background="themes/<?php echo $cfg["theme"] ?>/images/noglass.gif" width="<?php echo (200 - ($user_percent*2)) ?>"><img src="images/blank.gif" width="1" height="12" border="0"></td>
                </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _UPLOADS ?>:&nbsp;</td>
            <td><strong><?php echo $user_activity ?></strong></td>
        </tr>
        <tr>
            <td align="right"><?php echo _PERCENTPARTICIPATION ?>:&nbsp;</td>
            <td><strong><?php echo $user_percent ?>%</strong></td>
        </tr>
        <tr>
            <td colspan="2" align="center"><div align="center" class="tiny">(<?php echo _PARTICIPATIONSTATEMENT. " ".$cfg['days_to_keep']." "._DAYS ?>)</div><br></td>
        </tr>
        <tr>
            <td align="right"><?php echo _TOTALPAGEVIEWS ?>:&nbsp;</td>
            <td><strong><?php echo $hits ?></strong></td>
        </tr>
        <tr>
            <td align="right" valign="top"><?php echo _THEME ?>:&nbsp;</td>
            <td valign="top"><strong><?php echo $theme ?></strong><br></td>
        </tr>
        <tr>
            <td align="right" valign="top"><?php echo _LANGUAGE ?>:&nbsp;</td>
            <td valign="top"><strong><?php echo GetLanguageFromFile($language_file) ?></strong><br><br></td>
        </tr>
        <tr>
            <td align="right" valign="top"><?php echo _USERTYPE ?>:&nbsp;</td>
            <td valign="top"><strong><?php echo $user_type ?></strong><br></td>
        </tr>
        <tr>
            <td colspan="2" align="center"><div align="center">[<a href="admin.php?op=showUserActivity&user_id=<?php echo $user_id ?>"><?php echo _USERSACTIVITY ?></a>]</div></td>
        </tr>
        </table>
        </div>

        </td>
        <td valign="top" bgcolor="<?php echo $cfg["body_data_bg"] ?>">
        <div align="center">
        <table cellpadding="5" cellspacing="0" border="0">
        <form name="theForm" action="admin.php?op=updateUser" method="post" onsubmit="return validateUser()">
        <tr>
            <td align="right"><?php echo _USER ?>:</td>
            <td>
            <input name="user_id" type="Text" value="<?php echo $user_id ?>" size="15">
            <input name="org_user_id" type="Hidden" value="<?php echo $user_id ?>">
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _NEWPASSWORD ?>:</td>
            <td>
            <input name="pass1" type="Password" value="" size="15">
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _CONFIRMPASSWORD ?>:</td>
            <td>
            <input name="pass2" type="Password" value="" size="15">
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _USERTYPE ?>:</td>
            <td>
<?php if ($user_level <= 1) { ?>
            <select name="userType">
                <option value="0" <?php echo $selected_n ?>><?php echo _NORMALUSER ?></option>
                <option value="1" <?php echo $selected_a ?>><?php echo _ADMINISTRATOR ?></option>
            </select>
<?php } else { ?>
            <strong><?php echo _SUPERADMIN ?></strong>
            <input type="Hidden" name="userType" value="2">
<?php } ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
            <input name="hideOffline" type="Checkbox" value="1" <?php echo $hide_checked ?>> <?php echo _HIDEOFFLINEUSERS ?><br>
            </td>
        </tr>
        <tr>
            <td align="center" colspan="2">
            <input type="Submit" value="<?php echo _UPDATE ?>">
            </td>
        </tr>
        </form>
        </table>
        </div>
        </td>
    </tr>
    </table>


    <script language="JavaScript">
    function validateUser()
    {
        var msg = ""
        if (theForm.user_id.value == "")
        {
            msg = msg + "* <?php echo _USERIDREQUIRED ?>\n";
            theForm.user_id.focus();
        }

        if (theForm.pass1.value != "" || theForm.pass2.value != "")
        {
            if (theForm.pass1.value.length <= 5 || theForm.pass2.value.length <= 5)
            {
                msg = msg + "* <?php echo _PASSWORDLENGTH ?>\n";
                theForm.pass1.focus();
            }
            if (theForm.pass1.value != theForm.pass2.value)
            {
                msg = msg + "* <?php echo _PASSWORDNOTMATCH ?>\n";
                theForm.pass1.value = "";
                theForm.pass2.value = "";
                theForm.pass1.focus();
            }
        }

        if (msg != "")
        {
            alert("<?php echo _PLEASECHECKFOLLOWING ?>:\n\n" + msg);
            return false;
        }
        else
        {
            return true;
        }
    }
    </script>

<?php
    echo "</td></tr>";
    echo "</table></div>";
    echo "<br><br>";

    // Show User Section
    displayUserSection();

    echo "<br><br>";

    DisplayFoot();
}


//****************************************************************************
// CreateUser -- Create a user
//****************************************************************************
function CreateUser()
{
    global $cfg;
    DisplayHead(_USERADMIN);

    // Admin Menu
    displayMenu();
    echo "<div align=\"center\">";
    echo "<table border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/user.gif\" width=17 height=14 border=0>&nbsp;&nbsp;&nbsp;<font class=\"title\">"._NEWUSER."</font>";
    echo "</td></tr><tr><td align=\"center\">";
?>
    <div align="center">
        <table cellpadding="5" cellspacing="0" border="0">
        <form name="theForm" action="admin.php?op=addUser" method="post" onsubmit="return validateProfile()">
        <tr>
            <td align="right"><?php echo _USER ?>:</td>
            <td>
            <input name="newUser" type="Text" value="" size="15">
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _PASSWORD ?>:</td>
            <td>
            <input name="pass1" type="Password" value="" size="15">
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _CONFIRMPASSWORD ?>:</td>
            <td>
            <input name="pass2" type="Password" value="" size="15">
            </td>
        </tr>
        <tr>
            <td align="right"><?php echo _USERTYPE ?>:</td>
            <td>
            <select name="userType">
                <option value="0"><?php echo _NORMALUSER ?></option>
                <option value="1"><?php echo _ADMINISTRATOR ?></option>
            </select>
            </td>
        </tr>
        <tr>
            <td align="center" colspan="2">
            <input type="Submit" value="<?php echo _CREATE ?>">
            </td>
        </tr>
        </form>
        </table>
        </div>

    <br>

    <script language="JavaScript">
    function validateProfile()
    {
        var msg = ""
        if (theForm.newUser.value == "")
        {
            msg = msg + "* <?php echo _USERIDREQUIRED ?>\n";
            theForm.newUser.focus();
        }
        if (theForm.pass1.value != "" || theForm.pass2.value != "")
        {
            if (theForm.pass1.value.length <= 5 || theForm.pass2.value.length <= 5)
            {
                msg = msg + "* <?php echo _PASSWORDLENGTH ?>\n";
                theForm.pass1.focus();
            }
            if (theForm.pass1.value != theForm.pass2.value)
            {
                msg = msg + "* <?php echo _PASSWORDNOTMATCH ?>\n";
                theForm.pass1.value = "";
                theForm.pass2.value = "";
                theForm.pass1.focus();
            }
        }
        else
        {
            msg = msg + "* <?php echo _PASSWORDLENGTH ?>\n";
            theForm.pass1.focus();
        }

        if (msg != "")
        {
            alert("<?php echo _PLEASECHECKFOLLOWING ?>:\n\n" + msg);
            return false;
        }
        else
        {
            return true;
        }
    }
    </script>

<?php
    echo "</td></tr>";
    echo "</table></div>";
    echo "<br><br>";

    // Show User Section
    displayUserSection();

    echo "<br><br>";

    DisplayFoot();
}

//****************************************************************************
// editLinks -- Edit Links
//****************************************************************************
function editLinks()
{
    global $cfg;
    DisplayHead(_ADMINEDITLINKS);

    // Admin Menu
    displayMenu();
    echo "<div align=\"center\">";
    echo "<table border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=\"2\" bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">"._ADMINEDITLINKS."</font>";
    echo "</td></tr><tr><td colspan=2 align=\"center\">";
?>
    <form action="admin.php?op=addLink" method="post">
    <?php echo _FULLURLLINK ?>:
    <input type="Text" size="30" maxlength="255" name="newLink">
    Site Name:
    <input type="Text" size="30" maxlength="255" name="newSite">
    <input type="Submit" value="<?php echo _UPDATE ?>"><br>
    </form>
<?php
    echo "</td></tr>";
    $arLinks = GetLinks();

    if (is_array($arLinks))
    {
        $arLid = Array_Keys($arLinks);
        $inx = 0;
        $link_count = count($arLinks);

        foreach($arLinks as $link)
        {
            $lid = $arLid[$inx++];
            $ed = getRequestVar("edit");
            if (!empty($ed) && $ed == $link['lid'])
            {
                echo "<tr><td colspan=\"2\">";
    ?>
      <form action="admin.php?op=editLink" method="post">
      <?php echo _FULLURLLINK ?>:
      <input type="Text" size="30" maxlength="255" name="editLink" value="<?php echo $link['url'] ?>">
      Site Name:
      <input type="Text" size="30" maxlength="255" name="editSite" value="<?php echo $link['sitename'] ?>">
      <input type="hidden" name="lid" value="<?php echo $lid ?>">
      <input type="Submit" value="<?php echo _UPDATE ?>"><br>
      </form>
    <?php
            }
            else
            {
                echo "<tr><td>";
                echo "<a href=\"admin.php?op=deleteLink&lid=".$lid."\"><img src=\"images/delete_on.gif\" width=16 height=16 border=0 title=\""._DELETE." ".$lid."\" align=\"absmiddle\"></a>&nbsp;";
                echo "<a href=\"admin.php?op=editLinks&edit=".$lid."\"><img src=\"images/properties.png\" width=18 height=13 border=0 title=\""._EDIT." ".$lid."\" align=\"absmiddle\"></a>&nbsp;";
                echo "<a href=\"".$link['url']."\" target=\"_blank\">".$link['sitename']."</a></td>\n";
                echo "<td align=center width='36'>";

                if ($inx > 1 ){
                    // Only put an 'up' arrow if this isn't the first entry:
                    echo "<a href='admin.php?op=moveLink&amp;direction=up&amp;lid=".$lid."'>";
                    echo "<img src='images/uparrow.png' width='16' height='16' ";
                    echo "border='0' title='Move link up' align='absmiddle' alt='Up'></a>";
                }

                if ($inx != count($arLinks)) {
                    // Only put a 'down' arrow if this isn't the last item:
                    echo "<a href='admin.php?op=moveLink&amp;direction=down&amp;lid=".$lid."'>";
                    echo "<img src='images/downarrow.png' width='16' height='16' ";
                    echo "border='0' title='Move link down' align='absmiddle' alt='Down'></a>";
                }
                echo "</td></tr>";
            }
        }
    }
    echo "</table></div><br><br><br>";

    DisplayFoot();

}


//****************************************************************************
// editRSS -- Edit RSS Feeds
//****************************************************************************
function editRSS()
{
    global $cfg;
    DisplayHead("Administration - RSS");

    // Admin Menu
    displayMenu();
    echo "<div align=\"center\">";
    echo "<table border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">RSS Feeds</font>";
    echo "</td></tr><tr><td align=\"center\">";
?>
    <form action="admin.php?op=addRSS" method="post">
    <?php echo _FULLURLLINK ?>:
    <input type="Text" size="50" maxlength="255" name="newRSS">
    <input type="Submit" value="<?php echo _UPDATE ?>"><br>
    </form>
<?php
    echo "</td></tr>";
    $arLinks = GetRSSLinks();
    $arRid = Array_Keys($arLinks);
    $inx = 0;
    if(is_array($arLinks))
    {
        foreach($arLinks as $link)
        {
            $rid = $arRid[$inx++];
            echo "<tr><td><a href=\"admin.php?op=deleteRSS&rid=".$rid."\"><img src=\"images/delete_on.gif\" width=16 height=16 border=0 title=\""._DELETE." ".$rid."\" align=\"absmiddle\"></a>&nbsp;";
            echo "<a href=\"".$link."\" target=\"_blank\">".$link."</a></td></tr>\n";
        }
    }
    echo "</table></div><br><br><br>";

    DisplayFoot();

}

//****************************************************************************
// validateFile -- Validates the existance of a file and returns the status image
//****************************************************************************
function validateFile($the_file)
{
    $msg = "<img src=\"images/red.gif\" align=\"absmiddle\" title=\"Path is not Valid\"><br><font color=\"#ff0000\">Path is not Valid</font>";
    if (isFile($the_file))
    {
        $msg = "<img src=\"images/green.gif\" align=\"absmiddle\" title=\"Valid\">";
    }
    return $msg;
}

//****************************************************************************
// validatePath -- Validates TF Path and Permissions
//****************************************************************************
function validatePath($path)
{
    $msg = "<img src=\"images/red.gif\" align=\"absmiddle\" title=\"Path is not Valid\"><br><font color=\"#ff0000\">Path is not Valid</font>";
    if (is_dir($path))
    {
        if (is_writable($path))
        {
            $msg = "<img src=\"images/green.gif\" align=\"absmiddle\" title=\"Valid\">";
        }
        else
        {
            $msg = "<img src=\"images/red.gif\" align=\"absmiddle\" title=\"Path is not Writable\"><br><font color=\"#ff0000\">Path is not Writable -- make sure you chmod +w this path</font>";
        }
    }
    return $msg;
}

//****************************************************************************
// configSettings -- Config the Application Settings
//****************************************************************************
function configSettings()
{
    global $cfg;
    include_once("AliasFile.php");
    include_once("RunningTorrent.php");

    DisplayHead("Administration - Settings");

    // Admin Menu
    displayMenu();

    // Main Settings Section
    echo "<div align=\"center\">";
    echo "<table width=\"100%\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">TorrentFlux Settings</font>";
    echo "</td></tr><tr><td align=\"center\">";

?>

    <script language="JavaScript">
    function validateSettings()
    {
        var rtnValue = true;
        var msg = "";
        if (isNumber(document.theForm.max_upload_rate.value) == false)
        {
            msg = msg + "* Max Upload Rate must be a valid number.\n";
            document.theForm.max_upload_rate.focus();
        }
        if (isNumber(document.theForm.max_download_rate.value) == false)
        {
            msg = msg + "* Max Download Rate must be a valid number.\n";
            document.theForm.max_download_rate.focus();
        }
        if (isNumber(document.theForm.max_uploads.value) == false)
        {
            msg = msg + "* Max # Uploads must be a valid number.\n";
            document.theForm.max_uploads.focus();
        }
        if ((isNumber(document.theForm.minport.value) == false) || (isNumber(document.theForm.maxport.value) == false))
        {
            msg = msg + "* Port Range must have valid numbers.\n";
            document.theForm.minport.focus();
        }
        if (isNumber(document.theForm.rerequest_interval.value) == false)
        {
            msg = msg + "* Rerequest Interval must have a valid number.\n";
            document.theForm.rerequest_interval.focus();
        }
        if (document.theForm.rerequest_interval.value < 10)
        {
            msg = msg + "* Rerequest Interval must 10 or greater.\n";
            document.theForm.rerequest_interval.focus();
        }
        if (isNumber(document.theForm.days_to_keep.value) == false)
        {
            msg = msg + "* Days to keep Audit Actions must be a valid number.\n";
            document.theForm.days_to_keep.focus();
        }
        if (isNumber(document.theForm.minutes_to_keep.value) == false)
        {
            msg = msg + "* Minutes to keep user online must be a valid number.\n";
            document.theForm.minutes_to_keep.focus();
        }
        if (isNumber(document.theForm.rss_cache_min.value) == false)
        {
            msg = msg + "* Minutes to Cache RSS Feeds must be a valid number.\n";
            document.theForm.rss_cache_min.focus();
        }
        if (isNumber(document.theForm.page_refresh.value) == false)
        {
            msg = msg + "* Page Refresh must be a valid number.\n";
            document.theForm.page_refresh.focus();
        }
        if (isNumber(document.theForm.sharekill.value) == false)
        {
            msg = msg + "* Keep seeding until Sharing % must be a valid number.\n";
            document.theForm.sharekill.focus();
        }
        if ((document.theForm.maxport.value > 65535) || (document.theForm.minport.value > 65535))
        {
            msg = msg + "* Port can not be higher than 65535.\n";
            document.theForm.minport.focus();
        }
        if ((document.theForm.maxport.value < 0) || (document.theForm.minport.value < 0))
        {
            msg = msg + "* Can not have a negative number for port value.\n";
            document.theForm.minport.focus();
        }
        if (document.theForm.maxport.value < document.theForm.minport.value)
        {
            msg = msg + "* Port Range is not valid.\n";
            document.theForm.minport.focus();
        }

        if (msg != "")
        {
            rtnValue = false;
            alert("Please check the following:\n\n" + msg);
        }

        return rtnValue;
    }

    function isNumber(sText)
    {
        var ValidChars = "0123456789";
        var IsNumber = true;
        var Char;

        for (i = 0; i < sText.length && IsNumber == true; i++)
        {
            Char = sText.charAt(i);
            if (ValidChars.indexOf(Char) == -1)
            {
                IsNumber = false;
            }
        }

        return IsNumber;
    }
    </script>

    <div align="center">

        <table cellpadding="5" cellspacing="0" border="0" width="100%">
        <form name="theForm" action="admin.php?op=updateConfigSettings" method="post" onsubmit="return validateSettings()">
        <input type="Hidden" name="continue" value="configSettings">
        <tr>
            <td align="left" width="350" valign="top"><strong>Path</strong><br>
            Define the PATH where the downloads will go <br>(make sure it ends with a / [slash]).
            It must be chmod'd to 777:
            </td>
            <td valign="top">
                <input name="path" type="Text" maxlength="254" value="<?php    echo($cfg["path"]); ?>" size="55"><?php echo validatePath($cfg["path"]) ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Python Path</strong><br>
            Specify the path to the Python binary (usually /usr/bin/python or /usr/local/bin/python):
            </td>
            <td valign="top">
                <input name="pythonCmd" type="Text" maxlength="254" value="<?php    echo($cfg["pythonCmd"]); ?>" size="55"><?php echo validateFile($cfg["pythonCmd"]) ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>btphptornado Path</strong><br>
            Specify the path to the btphptornado python script:
            </td>
            <td valign="top">
                <input name="btphpbin" type="Text" maxlength="254" value="<?php    echo($cfg["btphpbin"]); ?>" size="55"><?php echo validateFile($cfg["btphpbin"]) ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>btshowmetainfo Path</strong><br>
            Specify the path to the btshowmetainfo python script:
            </td>
            <td valign="top">
                <input name="btshowmetainfo" type="Text" maxlength="254" value="<?php    echo($cfg["btshowmetainfo"]); ?>" size="55"><?php echo validateFile($cfg["btshowmetainfo"]) ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Use Advanced Start Dialog</strong><br>
            When enabled, users will be given the advanced start dialog popup when starting a torrent:
            </td>
            <td valign="top">
                <select name="advanced_start">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["advanced_start"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable File Priority</strong><br>
            When enabled, users will be allowed to select particular files from the torrent to download:
            </td>
            <td valign="top">
                <select name="enable_file_priority">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["enable_file_priority"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Max Upload Rate</strong><br>
            Set the default value for the max upload rate per torrent:
            </td>
            <td valign="top">
                <input name="max_upload_rate" type="Text" maxlength="5" value="<?php    echo($cfg["max_upload_rate"]); ?>" size="5"> KB/second
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Max Download Rate</strong><br>
            Set the default value for the max download rate per torrent (0 for no limit):
            </td>
            <td valign="top">
                <input name="max_download_rate" type="Text" maxlength="5" value="<?php    echo($cfg["max_download_rate"]); ?>" size="5"> KB/second
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Max Upload Connections</strong><br>
            Set the default value for the max number of upload connections per torrent:
            </td>
            <td valign="top">
                <input name="max_uploads" type="Text" maxlength="5" value="<?php    echo($cfg["max_uploads"]); ?>" size="5">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Port Range</strong><br>
            Set the default values for the for port range (Min - Max):
            </td>
            <td valign="top">
                <input name="minport" type="Text" maxlength="5" value="<?php    echo($cfg["minport"]); ?>" size="5"> -
                <input name="maxport" type="Text" maxlength="5" value="<?php    echo($cfg["maxport"]); ?>" size="5">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Rerequest Interval</strong><br>
            Set the default value for the rerequest interval to the tracker (default 1800 seconds):
            </td>
            <td valign="top">
                <input name="rerequest_interval" type="Text" maxlength="5" value="<?php    echo($cfg["rerequest_interval"]); ?>" size="5">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Allow encrypted connections</strong><br>
            Check to allow the client to accept encrypted connections.
            </td>
            <td valign="top">
                <select name="crypto_allowed">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["crypto_allowed"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Only allow encrypted connections</strong><br>
            Check to force the client to only create and accept encrypted connections.
            </td>
            <td valign="top">
                <select name="crypto_only">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["crypto_only"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Stealth crypto</strong><br>
	    Prevent all non-encrypted connection attempts.  (Note: will result in an effectively firewalled state on older trackers.)
            <td valign="top">
                <select name="crypto_stealth">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["crypto_stealth"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Extra BitTornado Commandline Options</strong><br>
            DO NOT include --max_upload_rate, --minport, --maxport, --max_uploads, --crypto_allowed, --crypto_only, --crypto_stealth here as they are included by TorrentFlux settings above:
            </td>
            <td valign="top">
                <input name="cmd_options" type="Text" maxlength="254" value="<?php    echo($cfg["cmd_options"]); ?>" size="55">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable Torrent Search</strong><br>
            When enabled, users will be allowed to perform torrent searches from the home page:
            </td>
            <td valign="top">
                <select name="enable_search">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["enable_search"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Default Torrent Search Engine</strong><br>
            Select the default search engine for torrent searches:
            </td>
            <td valign="top">
<?php
                echo buildSearchEngineDDL($cfg["searchEngine"]);
?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable Make Torrent</strong><br>
            When enabled, users will be allowed make torrent files from the directory view:
            </td>
            <td valign="top">
                <select name="enable_maketorrent">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["enable_maketorrent"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>btmakemetafile.py Path</strong><br>
            Specify the path to the btmakemetafile.py python script (used for making torrents):
            </td>
            <td valign="top">
                <input name="btmakemetafile" type="Text" maxlength="254" value="<?php echo($cfg["btmakemetafile"]); ?>" size="55"><?php echo validateFile($cfg["btmakemetafile"]); ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable Torrent File Download</strong><br>
            When enabled, users will be allowed download the torrent meta file from the torrent list view:
            </td>
            <td valign="top">
                <select name="enable_torrent_download">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["enable_torrent_download"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable File Download</strong><br>
            When enabled, users will be allowed download from the directory view:
            </td>
            <td valign="top">
                <select name="enable_file_download">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["enable_file_download"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable Text/NFO Viewer</strong><br>
            When enabled, users will be allowed to view Text/NFO files from the directory listing:
            </td>
            <td valign="top">
                <select name="enable_view_nfo">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["enable_view_nfo"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Download Package Type</strong><br>
            When File Download is enabled, users will be allowed download from the directory view using
            a packaging system.  Make sure your server supports the package type you select:
            </td>
            <td valign="top">
                <select name="package_type">
                        <option value="tar" selected>tar</option>
                        <option value="zip" <?php
                        if ($cfg["package_type"] == "zip")
                        {
                            echo "selected";
                        }
                        ?>>zip</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Show Server Load</strong><br>
            Enable showing the average server load over the last 15 minutes from <? echo $cfg["loadavg_path"] ?> file:
            </td>
            <td valign="top">
                <select name="show_server_load">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["show_server_load"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>loadavg Path</strong><br>
            Path to the loadavg file:
            </td>
            <td valign="top">
                <input name="loadavg_path" type="Text" maxlength="254" value="<?php    echo($cfg["loadavg_path"]); ?>" size="55"><?php echo validateFile($cfg["loadavg_path"]) ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Days to keep Audit Actions in the Log</strong><br>
            Number of days that audit actions will be held in the database:
            </td>
            <td valign="top">
                <input name="days_to_keep" type="Text" maxlength="3" value="<?php    echo($cfg["days_to_keep"]); ?>" size="3">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Minutes to Keep User Online Status</strong><br>
            Number of minutes before a user status changes to offline after leaving TorrentFlux:
            </td>
            <td valign="top">
                <input name="minutes_to_keep" type="Text" maxlength="2" value="<?php    echo($cfg["minutes_to_keep"]); ?>" size="2">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Minutes to Cache RSS Feeds</strong><br>
            Number of minutes to cache the RSS XML feed on server (speeds up reload):
            </td>
            <td valign="top">
                <input name="rss_cache_min" type="Text" maxlength="3" value="<?php    echo($cfg["rss_cache_min"]); ?>" size="3">
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Page Refresh (in seconds)</strong><br>
            Number of seconds before the torrent list page refreshes:
            </td>
            <td valign="top">
                <input name="page_refresh" type="Text" maxlength="3" value="<?php    echo($cfg["page_refresh"]); ?>" size="3">
            </td>
        </tr>
<?php
    if (!defined("IMG_JPG")) define("IMG_JPG", 2);
    // Check gd is loaded AND that jpeg image type is supported:
    if (extension_loaded('gd') && (imagetypes() & IMG_JPG))
    {
?>
        <tr>
            <td align="left" width="350" valign="top"><strong>Enable Security Code Login</strong><br>
            Requires users to enter a security code from a generated graphic to login (if enabled automated logins will NOT work):
            </td>
            <td valign="top">
                <select name="security_code">
                        <option value="1">true</option>
                        <option value="0" <?php
                            if (!$cfg["security_code"])
                            {
                                echo "selected";
                            }
                        ?>>false</option>
                </select>
            </td>
        </tr>
<?php
    }
?>
        <tr>
            <td align="left" width="350" valign="top"><strong>Default Theme</strong><br>
            Select the default theme that users will have (including login screen):
            </td>
            <td valign="top">
                <select name="default_theme">
<?php
    $arThemes = GetThemes();
    for($inx = 0; $inx < sizeof($arThemes); $inx++)
    {
        $selected = "";
        if ($cfg["default_theme"] == $arThemes[$inx])
        {
            $selected = "selected";
        }
        echo "<option value=\"".$arThemes[$inx]."\" ".$selected.">".$arThemes[$inx]."</option>";
    }
?>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Default Language</strong><br>
            Select the default language that users will have:
            </td>
            <td valign="top">
                <select name="default_language">
<?php
    $arLanguage = GetLanguages();
    for($inx = 0; $inx < sizeof($arLanguage); $inx++)
    {
        $selected = "";
        if ($cfg["default_language"] == $arLanguage[$inx])
        {
            $selected = "selected";
        }
        echo "<option value=\"".$arLanguage[$inx]."\" ".$selected.">".GetLanguageFromFile($arLanguage[$inx])."</option>";
    }
?>
            </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Show SQL Debug Statements</strong><br>
            SQL Errors will always be displayed but when this feature is enabled the SQL Statement
            that caused the error will be displayed as well:
            </td>
            <td valign="top">
                <select name="debug_sql">
                        <option value="1">true</option>
                        <option value="0" <?php
                        if (!$cfg["debug_sql"])
                        {
                            echo "selected";
                        }
                        ?>>false</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Default Torrent Completion Activity</strong><br>
            Select whether or not a torrent should keep seeding when download is complete
            (please seed your torrents):
            </td>
            <td valign="top">
                <select name="torrent_dies_when_done">
                        <option value="True">Die When Done</option>
                        <option value="False" <?php
                        if ($cfg["torrent_dies_when_done"] == "False")
                        {
                            echo "selected";
                        }
                        ?>>Keep Seeding</option>
                </select>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Default Percentage When Seeding should Stop</strong><br>
            Set the default share pecentage where torrents will shutoff
            when running torrents that do not die when done.
            Value '0' will seed forever.
            </td>
            <td valign="top">
                <input name="sharekill" type="Text" maxlength="3" value="<?php    echo($cfg["sharekill"]); ?>" size="3">%
            </td>
        </tr>
        </table>
        <br>
        <input type="Submit" value="Update Settings">
        </form>
    </div>
    <br>
<?php
    echo "</td></tr>";
    echo "</table></div>";

    DisplayFoot();
}

//****************************************************************************
// updateConfigSettings -- updating App Settings
//****************************************************************************
function updateConfigSettings()
{
    global $cfg;

    $tmpPath = getRequestVar("path");
    
    if (!empty($tmpPath) && substr( $tmpPath, -1 )  != "/")
    {
        // path requires a / on the end
        $_POST["path"] = $_POST["path"] . "/";
    }
    
    if ((array_key_exists("AllowQueing",$_POST) && $_POST["AllowQueing"] != $cfg["AllowQueing"]) ||
        (array_key_exists("maxServerThreads",$_POST) && $_POST["maxServerThreads"] != $cfg["maxServerThreads"]) ||
        (array_key_exists("maxUserThreads",$_POST) && $_POST["maxUserThreads"] != $cfg["maxUserThreads"]) ||
        (array_key_exists("sleepInterval",$_POST) && $_POST["sleepInterval"] != $cfg["sleepInterval"]) ||
        (array_key_exists("debugTorrents",$_POST) && $_POST["debugTorrents"] != $cfg["debugTorrents"]) ||
        (array_key_exists("tfQManager",$_POST) && $_POST["tfQManager"] != $cfg["tfQManager"]) ||
        (array_key_exists("btphpbin",$_POST) && $_POST["btphpbin"] != $cfg["btphpbin"])
        )
    {
        // kill QManager process;
        if(getQManagerPID() != "")
        {
            stopQManager();
        }

            $settings = $_POST;

            saveSettings($settings);
            AuditAction($cfg["constants"]["admin"], " Updating TorrentFlux Settings");

        // if enabling Start QManager
        if($cfg["AllowQueing"])
        {
            sleep(2);
            startQManager($cfg["maxServerThreads"], $cfg["maxUserThreads"], $cfg["sleepInterval"]);
            sleep(1);
        }
    }
    else
    {
         $settings = $_POST;

             saveSettings($settings);
             AuditAction($cfg["constants"]["admin"], " Updating TorrentFlux Settings");
    }

    $continue = getRequestVar('continue');
    header("Location: admin.php?op=".$continue);
}

//****************************************************************************
// queueSettings -- Config the Queue Settings
//****************************************************************************
function queueSettings()
{
    global $cfg;
    include_once("AliasFile.php");
    include_once("RunningTorrent.php");

    DisplayHead("Administration - Search Settings");

    // Admin Menu
    displayMenu();

        // Queue Manager Section
    echo "<div align=\"center\">";
    echo "<a name=\"QManager\" id=\"QManager\"></a>";
    echo "<table width=\"100%\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<font class=\"title\">";
    if(checkQManager() > 0)
    {
         echo "&nbsp;&nbsp;<img src=\"images/green.gif\" align=\"absmiddle\" align=\"absmiddle\"> Queue Manager Running [PID=".getQManagerPID()." with ".strval(getRunningTorrentCount())." torrent(s)]";
    }
    else
    {
        echo "&nbsp;&nbsp;<img src=\"images/black.gif\" align=\"absmiddle\"> Queue Manager Off";
    }
    echo "</font>";
    echo "</td></tr><tr><td align=\"center\">";
?>
    <script language="JavaScript">
    function validateSettings()
    {
        var rtnValue = true;
        var msg = "";
        if (isNumber(document.theForm.maxServerThreads.value) == false)
        {
            msg = msg + "* Max Server Threads must be a valid number.\n";
            document.theForm.maxServerThreads.focus();
        }
        if (isNumber(document.theForm.maxUserThreads.value) == false)
        {
            msg = msg + "* Max User Threads must be a valid number.\n";
            document.theForm.maxUserThreads.focus();
        }
        if (isNumber(document.theForm.sleepInterval.value) == false)
        {
            msg = msg + "* Sleep Interval must be a valid number.\n";
            document.theForm.sleepInterval.focus();
        }

        if (msg != "")
        {
            rtnValue = false;
            alert("Please check the following:\n\n" + msg);
        }

        return rtnValue;
    }

    function isNumber(sText)
    {
        var ValidChars = "0123456789.";
        var IsNumber = true;
        var Char;

        for (i = 0; i < sText.length && IsNumber == true; i++)
        {
            Char = sText.charAt(i);
            if (ValidChars.indexOf(Char) == -1)
            {
                IsNumber = false;
            }
        }

        return IsNumber;
    }
    </script>

    <div align="center">

         <table cellpadding="5" cellspacing="0" border="0" width="100%">
            <form name="theForm" action="admin.php?op=updateConfigSettings" method="post" onsubmit="return validateSettings()">
            <input type="Hidden" name="continue" value="queueSettings">
            <tr>
                <td align="left" width="350" valign="top"><strong>Enable Queue Manager</strong><br>
                Enable the Queue Manager to allow users to queue torrents:
                </td>
                <td>
                    <select name="AllowQueing">
                            <option value="1">true</option>
                            <option value="0" <?php
                            if (!$cfg["AllowQueing"])
                            {
                                echo "selected";
                            }
                            ?>>false</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td align="left" width="350" valign="top"><strong>tfQManager Path</strong><br>
                Specify the path to the tfQManager python script:
                </td>
                <td valign="top">
                    <input name="tfQManager" type="Text" maxlength="254" value="<?php    echo($cfg["tfQManager"]); ?>" size="55"><?php echo validateFile($cfg["tfQManager"]) ?>
                </td>
            </tr>
<!-- Only used for develpment or if you really really know what you are doing
            <tr>
                <td align="left" width="350" valign="top"><strong>Enable Queue Manager Debugging</strong><br>
                Creates huge log files only for debugging.  DO NOT KEEP THIS MODE ON:
                </td>
                <td>
                    <select name="debugTorrents">
                        <option value="1">true</option>
                        <option value="0" <?php
            if (array_key_exists("debugTorrents",$cfg))
            {
                if (!$cfg["debugTorrents"])
                {
                    echo "selected";
                }
            }
            else
            {
                insertSetting("debugTorrents",false);
                echo "selected";
            }
                        ?>>false</option>
                    </select>
                </td>
            </tr>
-->
            <tr>
                <td align="left" width="350" valign="top"><strong>Max Server Threads</strong><br>
                Specify the maximum number of torrents the server will allow to run at
                one time (admins may override this):
                </td>
                <td valign="top">
                    <input name="maxServerThreads" type="Text" maxlength="3" value="<?php    echo($cfg["maxServerThreads"]); ?>" size="3">
                </td>
            </tr>
            <tr>
                <td align="left" width="350" valign="top"><strong>Max User Threads</strong><br>
                Specify the maximum number of torrents a single user may run at
                one time:
                </td>
                <td valign="top">
                    <input name="maxUserThreads" type="Text" maxlength="3" value="<?php    echo($cfg["maxUserThreads"]); ?>" size="3">
                </td>
            </tr>
            <tr>
                <td align="left" width="350" valign="top"><strong>Polling Interval</strong><br>
                Number of seconds the Queue Manager will sleep before checking for new torrents to run:
                </td>
                <td valign="top">
                    <input name="sleepInterval" type="Text" maxlength="3" value="<?php    echo($cfg["sleepInterval"]); ?>" size="3">
                </td>
            </tr>
            <tr>
                <td align="center" colspan="2">
                <br><br>
                <input type="Submit" value="Update Settings">
                </td>
            </tr>
            </form>
        </table>


        </div>
    <br>
<?php
    echo "</td></tr>";
    echo "</table></div>";

    $displayQueue = True;
    $displayRunningTorrents = True;

    // Its a timming thing.
    if ($displayRunningTorrents)
    {
          // get Running Torrents.
        $runningTorrents = getRunningTorrents();
    }

    if ($displayQueue)
    {
        $output = "";

        echo "\n";
        echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
        echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
        echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr>";
        echo "<td><img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\"> Queued Items </font></td>";
        echo "</tr></table>";
        echo "</td></tr>";
        echo "<tr>";
        echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._USER."</div></td>";
        echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._FILE."</div></td>";
        echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._TIMESTAMP."</div></td>";
        echo "</tr>";
        echo "\n";

        $qDir = $cfg["torrent_file_path"]."queue/";
        if (is_dir($cfg["torrent_file_path"]))
        {
            if (is_writable($cfg["torrent_file_path"]) && !is_dir($qDir))
            {
                @mkdir($qDir, 0777);
            }

            // get Queued Items and List them out.
            $output .= "";
            $handle = @opendir($qDir);
            while($filename = readdir($handle))
            {
                if ($filename != "tfQManager.log")
                {
                    if ($filename != "." && $filename != ".." && strpos($filename,".pid") == 0)
                    {
                    $output .= "<tr>";
                    $output .= "<td><div class=\"tiny\">";
                    $af = new AliasFile(str_replace("queue/","",$qDir).str_replace(".Qinfo","",$filename), "");
                    $output .= $af->torrentowner;
                    $output .= "</div></td>";
                    $output .= "<td><div align=center><div class=\"tiny\" align=\"left\">".str_replace(array(".Qinfo",".stat"),"",$filename)."</div></td>";
                    $output .= "<td><div class=\"tiny\" align=\"center\">".date(_DATETIMEFORMAT, strval(filectime($qDir.$filename)))."</div></td>";
                    $output .= "</tr>";
                    $output .= "\n";
                    }
                }
            }
            closedir($handle);
        }

        if( strlen($output) == 0 )
        {
            $output = "<tr><td colspan=3><div class=\"tiny\" align=center>Queue is Empty</div></td></tr>";
        }
        echo $output;

        echo "</table>";
    }

    if ($displayRunningTorrents)
    {
        $output = "";

        echo "\n";
        echo "<table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
        echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
        echo "<table width=\"100%\" cellpadding=0 cellspacing=0 border=0><tr>";
        echo "<td><img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\"> Running Items </font></td>";
        echo "</tr></table>";
        echo "</td></tr>";
        echo "<tr>";
        echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"15%\"><div align=center class=\"title\">"._USER."</div></td>";
        echo "<td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._FILE."</div></td>";
        echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"1%\"><div align=center class=\"title\">".str_replace(" ","<br>",_FORCESTOP)."</div></td>";
        echo "</tr>";
        echo "\n";

        // get running torrents and List them out.
        $runningTorrents = getRunningTorrents();
        if(is_array($runningTorrents))
        {
            foreach ($runningTorrents as $key => $value)
            {
                $rt = new RunningTorrent($value);
                $output .= $rt->BuildAdminOutput();
            }
        }
        if( strlen($output) == 0 )
        {
            $output = "<tr><td colspan=3><div class=\"tiny\" align=center>No Running Torrents</div></td></tr>";
        }
        echo $output;

        echo "</table>";

    }

    DisplayFoot();
}


//****************************************************************************
// searchSettings -- Config the Search Engine Settings
//****************************************************************************
function searchSettings()
{
    global $cfg;
    include_once("AliasFile.php");
    include_once("RunningTorrent.php");
    include_once("searchEngines/SearchEngineBase.php");

    DisplayHead("Administration - Search Settings");

    // Admin Menu
    displayMenu();

    // Main Settings Section
    echo "<div align=\"center\">";
    echo "<table width=\"100%\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">Search Settings</font>";
    echo "</td></tr><tr><td align=\"center\">";

?>
    <div align="center">

        <table cellpadding="5" cellspacing="0" border="0" width="100%">
        <form name="theForm" action="admin.php?op=searchSettings" method="post">
        <tr>
            <td align="right" width="350" valign="top"><strong>Select Search Engine</strong><br>
            </td>
            <td valign="top">
<?php
                $searchEngine = getRequestVar('searchEngine');
                if (empty($searchEngine)) $searchEngine = $cfg["searchEngine"];
                echo buildSearchEngineDDL($searchEngine,true)
?>
            </td>
        </tr>
        </form>
        </table>

        <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr><td>
<?php
    if (is_file('searchEngines/'.$searchEngine.'Engine.php'))
    {
        include_once('searchEngines/'.$searchEngine.'Engine.php');
        $sEngine = new SearchEngine(serialize($cfg));
        if ($sEngine->initialized)
        {
            echo "<table width=\"100%\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\"><tr>";
            echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\"><img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">".$sEngine->mainTitle." Search Settings</font></td>";
            echo "</tr></table></td>";
            echo "<form name=\"theSearchEngineSettings\" action=\"admin.php?op=updateSearchSettings\" method=\"post\">\n";
            echo "<input type=\"hidden\" name=\"searchEngine\" value=\"".$searchEngine."\">";
?>
            </td>
        </tr>
        <tr>
            <td>

        <table cellpadding="5" cellspacing="0" border="0" width="100%">
        <tr>
            <td align="left" width="350" valign="top"><strong>Search Engine URL:</strong></td>
            <td valign="top">
                <?php echo "<a href=\"http://".$sEngine->mainURL."\" target=\"_blank\">".$sEngine->mainTitle."</a>"; ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Search Module Author:</strong></td>
            <td valign="top">
                <?php echo $sEngine->author; ?>
            </td>
        </tr>
        <tr>
            <td align="left" width="350" valign="top"><strong>Version:</strong></td>
            <td valign="top">
                <?php echo $sEngine->version; ?>
            </td>
        </tr>
<?php
        if(strlen($sEngine->updateURL)>0)
        {
?>
        <tr>
            <td align="left" width="350" valign="top"><strong>Update Location:</strong></td>
            <td valign="top">
                <?php echo "<a href=\"".$sEngine->updateURL."\" target=\"_blank\">Check for Update</a>"; ?>
            </td>
        </tr>
<?php
        }
            if (! $sEngine->catFilterName == '')
            {
?>
        <tr>
            <td align="left" width="350" valign="top"><strong>Search Filter:</strong><br>
            Select the items that you DO NOT want to show in the torrent search:
            </td>
            <td valign="top">
<?php
                echo "<select multiple name=\"".$sEngine->catFilterName."[]\" size=\"8\" STYLE=\"width: 125px\">";
                echo "<option value=\"-1\">[NO FILTER]</option>";
                foreach ($sEngine->getMainCategories(false) as $mainId => $mainName)
                {
                    echo "<option value=\"".$mainId."\" ";
                    if (@in_array($mainId, $sEngine->catFilter))
                    {
                        echo " selected";
                    }
                    echo ">".$mainName."</option>";
                }
                echo "</select>";
                echo "            </td>\n";
                echo "        </tr>\n";
            }
        }
    }

    echo "        </table>\n";
    echo "         </td></tr></table>";
    echo "        <br>\n";
    echo "        <input type=\"Submit\" value=\"Update Settings\">";
    echo "        </form>\n";
    echo "    </div>\n";
    echo "    <br>\n";
    echo "</td></tr>";
    echo "</table></div>";

    DisplayFoot();
}

//****************************************************************************
// updateSearchSettings -- updating Search Engine Settings
//****************************************************************************
function updateSearchSettings()
{
    global $cfg;

    foreach ($_POST as $key => $value)
    {
        if ($key != "searchEngine")
        {
            $settings[$key] = $value;
        }
    }

    saveSettings($settings);
    AuditAction($cfg["constants"]["admin"], " Updating TorrentFlux Search Settings");

    $searchEngine = getRequestVar('searchEngine');
    if (empty($searchEngine)) $searchEngine = $cfg["searchEngine"];
    header("location: admin.php?op=searchSettings&searchEngine=".$searchEngine);
}

//****************************************************************************
//****************************************************************************
//****************************************************************************
//****************************************************************************
// TRAFFIC CONTROLER
$op = getRequestVar('op');

switch ($op)
{

    default:
        $min = getRequestVar('min');
        if(empty($min)) $min=0;
        showIndex($min);
    break;

    case "showUserActivity":
        $min = getRequestVar('min');
        if(empty($min)) $min=0;
        $user_id = getRequestVar('user_id');
        $srchFile = getRequestVar('srchFile');
        $srchAction = getRequestVar('srchAction');
        showUserActivity($min, $user_id, $srchFile, $srchAction);
    break;

    case "backupDatabase":
        backupDatabase();
    break;

    case "editRSS":
        editRSS();
    break;

    case "addRSS":
        $newRSS = getRequestVar('newRSS');
        addRSS($newRSS);
    break;

    case "deleteRSS":
        $rid = getRequestVar('rid');
        deleteRSS($rid);
    break;

    case "editLink":
        $lid = getRequestVar('lid');
        $editLink = getRequestVar('editLink');
        $editSite = getRequestVar('editSite');
        editLink($lid, $editLink, $editSite);
    break;

    case "editLinks":
        editLinks();
    break;

    case "addLink":
        $newLink = getRequestVar('newLink');
        $newSite = getRequestVar('newSite');
        addLink($newLink,$newSite);
    break;

    case "moveLink":
        $lid = getRequestVar('lid');
        $direction = getRequestVar('direction');
        moveLink($lid, $direction);
    break;

    case "deleteLink":
        $lid = getRequestVar('lid');
        deleteLink($lid);
    break;

    case "CreateUser":
        CreateUser();
    break;

    case "addUser":
        $newUser = getRequestVar('newUser');
        $pass1 = getRequestVar('pass1');
        $userType = getRequestVar('userType');
        addUser($newUser, $pass1, $userType);
    break;

    case "deleteUser":
        $user_id = getRequestVar('user_id');
        deleteUser($user_id);
    break;

    case "editUser":
        $user_id = getRequestVar('user_id');
        editUser($user_id);
    break;

    case "updateUser":
        $user_id = getRequestVar('user_id');
        $org_user_id = getRequestVar('org_user_id');
        $pass1 = getRequestVar('pass1');
        $userType = getRequestVar('userType');
        $hideOffline = getRequestVar('hideOffline');
        updateUser($user_id, $org_user_id, $pass1, $userType, $hideOffline);
    break;

    case "configSettings":
        configSettings();
    break;

    case "updateConfigSettings":
        if (! array_key_exists("debugTorrents", $_REQUEST))
        {
            $_REQUEST["debugTorrents"] = false;
        }
        updateConfigSettings();
    break;

    case "queueSettings":
        queueSettings();
    break;

    case "searchSettings":
        searchSettings();
    break;

    case "updateSearchSettings":
        updateSearchSettings();
    break;

}
//****************************************************************************
//****************************************************************************
//****************************************************************************
//****************************************************************************

?>
