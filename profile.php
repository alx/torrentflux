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
// showIndex -- main view
function showIndex()
{
    global $cfg, $db;

    $hideChecked = "";

    if ($cfg["hide_offline"] == 1)
    {
        $hideChecked = "checked";
    }

    DisplayHead($cfg["user"]."'s "._PROFILE);

    echo "<div align=\"center\">";
    echo "<table border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" width=\"760\">";
    echo "<tr><td colspan=6 bgcolor=\"".$cfg["table_data_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">".$cfg["user"]."'s "._PROFILE."</font>";
    echo "</td></tr><tr><td align=\"center\">";

    $total_activity = GetActivityCount();

    $sql= "SELECT user_id, hits, last_visit, time_created, user_level FROM tf_users WHERE user_id=".$db->qstr($cfg["user"]);
    list($user_id, $hits, $last_visit, $time_created, $user_level) = $db->GetRow($sql);

    $user_type = _NORMALUSER;
    if (IsAdmin())
    {
        $user_type = _ADMINISTRATOR;
    }
    if (IsSuperAdmin())
    {
        $user_type = _SUPERADMIN;
    }


    $user_activity = GetActivityCount($cfg["user"]);

    if ($user_activity == 0)
    {
        $user_percent = 0;
    }
    else
    {
        $user_percent = number_format(($user_activity/$total_activity)*100);
    }

?>

    <table width="100%" border="0" cellpadding="3" cellspacing="0">
    <tr>
        <td width="50%" bgcolor="<?php echo $cfg["table_data_bg"] ?>" valign="top">

        <div align="center">
        <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td align="right"><?php echo _JOINED ?>:&nbsp;</td>
            <td><strong><?php echo date(_DATETIMEFORMAT, $time_created) ?></strong></td>
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
            <td align="right"><?php echo _USERTYPE ?>:&nbsp;</td>
            <td><strong><?php echo $user_type ?></strong></td>
        </tr>
        <tr>
            <td colspan="2" align="center">
                <table>
                    <tr>
                        <td align="center">
                            <BR />[ <a href="?op=showCookies">Cookie Management</a> ]
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </table>
        </div>

        </td>
        <td valign="top">
        <div align="center">
        <table cellpadding="5" cellspacing="0" border="0">
        <form name="theForm" action="profile.php?op=updateProfile" method="post" onsubmit="return validateProfile()">
        <tr>
            <td align="right"><?php echo _USER ?>:</td>
            <td>
            <input readonly="true" type="Text" value="<?php echo $cfg["user"] ?>" size="15">
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
            <td align="right"><?php echo _THEME ?>:</td>
            <td>
            <select name="theme">
<?php
    $arThemes = GetThemes();
    for($inx = 0; $inx < sizeof($arThemes); $inx++)
    {
        $selected = "";
        if ($cfg["theme"] == $arThemes[$inx])
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
            <td align="right"><?php echo _LANGUAGE ?>:</td>
            <td>
            <select name="language">
<?php
    $arLanguage = GetLanguages();
    for($inx = 0; $inx < sizeof($arLanguage); $inx++)
    {
        $selected = "";
        if ($cfg["language_file"] == $arLanguage[$inx])
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
            <td colspan="2">
            <input name="hideOffline" type="Checkbox" value="1" <?php echo $hideChecked ?>> <?php echo _HIDEOFFLINEUSERS ?><br>
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
    function validateProfile()
    {
        var msg = ""
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
    echo "</table></div><br><br>";

    DisplayFoot();
}


//****************************************************************************
// updateProfile -- update profile
function updateProfile($pass1, $pass2, $hideOffline, $theme, $language)
{
    Global $cfg;

    if ($pass1 != "")
    {
        $_SESSION['user'] = md5($cfg["pagetitle"]);
    }

    UpdateUserProfile($cfg["user"], $pass1, $hideOffline, $theme, $language);

    DisplayHead($cfg["user"]."'s "._PROFILE);

    echo "<div align=\"center\">";
    echo "<table border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\" width=\"760\">";
    echo "<tr><td colspan=6 background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<font class=\"title\">".$cfg["user"]."'s "._PROFILE."</font>";
    echo "</td></tr><tr><td align=\"center\">";
?>
    <br>
    <?php echo _PROFILEUPDATEDFOR." ".$cfg["user"] ?>
    <br><br>
<?php
    echo "</td></tr>";
    echo "</table></div><br><br>";

    DisplayFoot();
}


//****************************************************************************
// ShowCookies -- show cookies for user
function ShowCookies()
{
    global $cfg, $db;
    DisplayHead($cfg["user"] . "'s "._PROFILE);

    $cid = getRequestVar("cid"); // Cookie ID

    // Used for when editing a cookie
    $hostvalue = $datavalue = "";
    if( !empty( $cid ) )
    {
        // Get cookie information from database
        $cookie = getCookie( $cid );
        $hostvalue = " value=\"" . $cookie['host'] . "\"";
        $datavalue = " value=\"" . $cookie['data'] . "\"";
    }

?>
<SCRIPT LANGUAGE="JavaScript">
    <!-- Begin
    function popUp(name_file)
    {
        window.open (name_file,'help','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=800,height=600')
    }
    // End -->
</script>
<div align="center">[<a href="?">Return to Profile</a>]</div>
<br />
<div align="center">
    <form action="?op=<?php echo ( !empty( $cid ) ) ? "modCookie" : "addCookie"; ?>"" method="post">
    <input type="hidden" name="cid" value="<?php echo $cid;?>" />
    <table border="1" bordercolor="<?php echo $cfg["table_admin_border"];?>" cellpadding="2" cellspacing="0" bgcolor="<?php echo $cfg["table_data_bg"];?>">
        <tr>
            <td colspan="3" bgcolor="<?php echo $cfg["table_header_bg"];?>" background="themes/<? echo $cfg["theme"] ?>/images/bar.gif">
                <img src="images/properties.png" width=18 height=13 border=0 align="absbottom">&nbsp;<font class="title">Cookie Management</font>
            </td>
        </tr>
        <tr>
            <td width="80" align="right">&nbsp;Host:</td>
            <td>
                <input type="Text" size="50" maxlength="255" name="host"<?php echo $hostvalue;?>><BR />
            </td>
            <td>
                www.host.com
            </td>
        </tr>
        <tr>
            <td width="80" align="right">&nbsp;Data:</td>
            <td>
                <input type="Text" size="50" maxlength="255" name="data"<?php echo $datavalue;?>><BR />
            </td>
            <td>
                uid=123456;pass=a1b2c3d4e5f6g7h8i9j1
            </td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td colspan="2">
                <input type="Submit" value="<?php echo ( !empty( $cid ) ) ? _UPDATE : "Add"; ?>">
            </td>
        </tr>
<?php
    // We are editing a cookie, so have a link back to cookie list
    if( !empty( $cid ) )
    {
?>
        <tr>
            <td colspan="3">
                <center>[ <a href="?op=editCookies">back</a> ]</center>
            </td>
        </tr>
<?php
    }
    else
    {
?>
        <tr>
            <td colspan="3">
                <table border="1" bordercolor="<?php echo $cfg["table_admin_border"];?>" cellpadding="2" cellspacing="0" bgcolor="<?php echo $cfg["table_data_bg"];?>" width="100%">
                    <tr>
                        <td style="font-weight: bold; padding-left: 3px;" width="50">Action</td>
                        <td style="font-weight: bold; padding-left: 3px;">Host</td>
                        <td style="font-weight: bold; padding-left: 3px;">Data</td>
                    </tr>
<?php
        // Output the list of cookies in the database
        $dat = getAllCookies($cfg["user"]);
        if( empty( $dat ) )
        {
?>
                <tr>
                    <td colspan="3">No cookie entries exist.</td>
                </tr>
<?php
        }
        else
        {
            foreach( $dat as $cookie )
            {
?>
                    <tr>
                        <td>
                            <a href="?op=deleteCookie&cid=<?php echo $cookie["cid"];?>"><img src="images/delete_on.gif" width=16 height=16 border=0 title="<?php echo _DELETE . " " . $cookie["host"]; ?>" align="absmiddle"></a>
                            <a href="?op=editCookies&cid=<?php echo $cookie["cid"];?>"><img src="images/properties.png" width=18 height=13 border=0 title="<?php echo _EDIT . " " . $cookie["host"]; ?>" align="absmiddle"></a>
                        </td>
                        <td><?php echo $cookie["host"];?></td>
                        <td><?php echo $cookie["data"];?></td>
                    </tr>
<?php
            }
        }
?>
                </table>
            </td>
        </tr>
<?php
    }
?>
        <tr>
            <td colspan="3">
                <br>
                <div align="center">
                <A HREF="javascript:popUp('cookiehelp.php')">How to get cookie information....</A>
                </div>
            </td>
        </tr>
        </table>
        </form>
    </div>
    <br />
    <br />
    <br />
<?php
    DisplayFoot();
}

//****************************************************************************
// addCookie -- adding a Cookie Host Information
//****************************************************************************
function addCookie( $newCookie )
{
    if( !empty( $newCookie ) )
    {
        global $cfg;
        AddCookieInfo( $newCookie );
        AuditAction( $cfg["constants"]["admin"], "New Cookie: " . $newCookie["host"] . " | " . $newCookie["data"] );
    }
    header( "location: profile.php?op=showCookies" );
}

//****************************************************************************
// deleteCookie -- delete a Cookie Host Information
//****************************************************************************
function deleteCookie($cid)
{
    global $cfg;
    $cookie = getCookie( $cid );
    deleteCookieInfo( $cid );
    AuditAction( $cfg["constants"]["admin"], _DELETE . " Cookie: " . $cookie["host"] );
    header( "location: profile.php?op=showCookies" );
}

//****************************************************************************
// modCookie -- edit a Cookie Host Information
//****************************************************************************
function modCookie($cid,$newCookie)
{
    global $cfg;
    modCookieInfo($cid,$newCookie);
    AuditAction($cfg["constants"]["admin"], "Modified Cookie: ".$newCookie["host"]." | ".$newCookie["data"]);
    header("location: profile.php?op=showCookies");
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
        showIndex();
        exit;
    break;

    case "updateProfile":
        $pass1 = getRequestVar('pass1');
        $pass2 = getRequestVar('pass2');
        $hideOffline = getRequestVar('hideOffline');
        $theme = getRequestVar('theme');
        $language = getRequestVar('language');

        updateProfile($pass1, $pass2, $hideOffline, $theme, $language);
    break;

    // Show main Cookie Management
    case "showCookies":
    case "editCookies":
        showCookies();
    break;

    // Add a new cookie to user
    case "addCookie":
        $newCookie["host"] = getRequestVar('host');
        $newCookie["data"] = getRequestVar('data');
        addCookie( $newCookie );
    break;

    // Modify an existing cookie from user
    case "modCookie":
        $newCookie["host"] = getRequestVar( 'host' );
        $newCookie["data"] = getRequestVar( 'data' );
        $cid = getRequestVar( 'cid' );
        modCookie( $cid, $newCookie );
    break;

    // Delete selected cookie from user
    case "deleteCookie":
        $cid = getRequestVar("cid");
        deleteCookie( $cid );
    break;

}
//****************************************************************************
//****************************************************************************
//****************************************************************************
//****************************************************************************

?>