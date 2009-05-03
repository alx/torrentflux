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
require_once("metaInfo.php");

$torrent = SecurityClean(getRequestVar('torrent'));
$displayName = $torrent;

if (!file_exists($cfg["torrent_file_path"].$torrent))
{
    echo $torrent." could not be found or does not exist.";
    die();
}

if(strlen($displayName) >= 55)
{
    $displayName = substr($displayName, 0, 52)."...";
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
    <title><?php echo _RUNTORRENT ?> - <?php echo $displayName ?></title>
    <LINK REL="StyleSheet" HREF="themes/<?php echo $cfg["theme"] ?>/style.css" TYPE="text/css" />
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache" charset="<?php echo _CHARSET ?>">

<script language="JavaScript">
function StartTorrent()
{

    if (ValidateValues())
    {
        document.theForm.submit();
    }
}

function ValidateValues()
{

    var rtnValue = true;
    var msg = "";
    if (isNumber(document.theForm.rate.value) == false)
    {
        msg = msg + "* Max Upload Rate must be a valid number.\n";
        document.theForm.rate.focus();
    }
    if (isNumber(document.theForm.drate.value) == false)
    {
        msg = msg + "* Max Download Rate must be a valid number.\n";
        document.theForm.drate.focus();
    }
    if (isNumber(document.theForm.maxuploads.value) == false)
    {
        msg = msg + "* Max # Uploads must be a valid number.\n";
        document.theForm.maxuploads.focus();
    }
    if ((isNumber(document.theForm.minport.value) == false) || (isNumber(document.theForm.maxport.value) == false))
    {
        msg = msg + "* Port Range must have valid numbers.\n";
        document.theForm.minport.focus();
    }
    if (isNumber(document.theForm.rerequest.value) == false)
    {
        msg = msg + "* Rerequest Interval must be a valid number.\n";
        document.theForm.rerequest.focus();
    }
    if (document.theForm.rerequest.value < 10)
    {
        msg = msg + "* Rerequest Interval must be 10 or greater.\n";
        document.theForm.rerequest.focus();
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

function CheckShareState()
{

    var obj = document.getElementById('sharekiller');
    if (document.theForm.runtime.value == "True")
    {
        obj.style.visibility = "hidden";
    }
    else
    {
        obj.style.visibility = "visible";
    }
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

</head>

<body bgcolor="<?php echo $cfg["body_data_bg"]; ?>">

<div align="center">
<strong><?php echo $displayName ?></strong><br>
<table width="98%" border="0" cellpadding="0" cellspacing="0">
<tr><form name="theForm" target="_parent" action="index.php" method="POST">
    <input type="hidden" name="closeme" value="true">
    <input type="hidden" name="torrent" value="<?php echo $torrent; ?>">
    <td>

        <table width="100%" cellpadding="2" cellspacing="0" border="0">
        <tr>
            <td align="right">Max Upload Rate:</td>
            <td><input type="Text" name="rate" maxlength="4" size="4" value="<?php echo $cfg["max_upload_rate"]; ?>"> kB/s</td>
            <td align="right">Max # Uploads:</td>
            <td><input type="Text" name="maxuploads" maxlength="2" size="2" value="<?php echo $cfg["max_uploads"]; ?>"></td>
        </tr>
        <tr>
            <td align="right" valign="top">Max Download Rate:</td>
            <td valign="top"><input type="Text" name="drate" maxlength="4" size="4" value="<?php echo $cfg["max_download_rate"]; ?>"> kB/s<font class="tiny"> (0 = max)</font></td>
            <td align="Left" colspan="2" valign="top"><input type="Checkbox" name="superseeder" value="1">Super Seeder<font class="tiny"> (dedicated seed only)</font></td>
        </tr>
        <tr>
            <td align="right" valign="top">Rerequest Interval:</td>
            <td valign="top"><input type="Text" name="rerequest" maxlength="5" size="5" value="<?php echo $cfg["rerequest_interval"]; ?>"></td>
            <td align="Left" colspan="2" valign="top">
<?php
    if($cfg["AllowQueing"] == true)
    {
        if ( IsAdmin() )
        {
            echo "<input type='Checkbox' name='queue' checked>Add to Queue";
        }
        else
        {
            // Force Queuing if not an admin.
            echo "<input type='hidden' name='queue' value=1>";
        }
    }
    else
    {
        echo "&nbsp;";
    }
    echo "</td>";
?>

        </tr>
        <tr>
            <td align="right">Completion:</td>
            <td>
                <?php
                    $selected = "";
                    if ($cfg["torrent_dies_when_done"] == "False")
                    {
                        $selected = "selected";
                    }
                ?>

                <select name="runtime" onchange="CheckShareState()">
                        <option value="True">Die When Done</option>
                        <option value="False" <?php echo $selected ?>>Keep Seeding</option>
                </select>
            </td>
            <td align="right">Port Range:</td>
            <td>
            <input type="Text" name="minport" maxlength="5" size="5" value="<?php echo $cfg["minport"]; ?>">
            -
            <input type="Text" name="maxport" maxlength="5" size="5" value="<?php echo $cfg["maxport"]; ?>">
            </td>
        </tr>
        <tr>
            <td colspan="4" align="center"><div ID="sharekiller" align="center" style="visibility:hidden;">Keep seeding until Sharing is: <input type="Text" name="sharekill" maxlength="4" size="4" value="<?php echo $cfg["sharekill"]; ?>">%<font class="tiny">  (0% will keep seeding)</font>&nbsp;</div></td>
        </tr>
        </table>
        <br>
        &nbsp;&nbsp;&nbsp;Torrent Meta Data / Priority Selection:
    </td>
</tr>
</table>
<div align="left" id="BodyLayer" name="BodyLayer" style="border: thin solid <?php echo $cfg["main_bgcolor"] ?>; background-color: <?php echo $cfg["bgLight"] ?>; position:relative; width:650; height:290; padding-left: 5px; padding-right: 5px; z-index:1; overflow: scroll; visibility: visible">
<?php
    showMetaInfo($torrent,false);
?>
</div>
<br>
<table border="0" cellpadding="0" cellspacing="0">
<tr>
    <td>
    <input type="button" id="startbtn" name="startbtn" value="<?php echo _RUNTORRENT ?>" onclick="StartTorrent();">&nbsp;&nbsp;
    <input type="button" value="Cancel" onclick="window.close()">
    </td>
</tr>
</table>
</form>
</div>
<script language="JavaScript">
    CheckShareState();
    document.getElementById('startbtn').focus();
</script>
</body>
</html>
