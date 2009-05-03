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

$to_user = getRequestVar('to_user');
if(empty($to_user) or empty($cfg['user']))
{
     // the user probably hit this page direct
    header("location: index.php");
    exit;
}

$message = getRequestVar('message');
if (!empty($message))
{
    $to_all = getRequestVar('to_all');
    if(!empty($to_all))
    {
        $to_all = 1;
    }
    else
    {
        $to_all = 0;
    }

    $force_read = getRequestVar('force_read');
    if(!empty($force_read) && IsAdmin())
    {
        $force_read = 1;
    }
    else
    {
        $force_read = 0;
    }


    $message = check_html($message, "nohtml");
    SaveMessage($to_user, $cfg['user'], $message, $to_all, $force_read);

    header("location: readmsg.php");
}
else
{
    $rmid = getRequestVar('rmid');
    if(!empty($rmid))
    {
        list($from_user, $message, $ip, $time) = GetMessage($rmid);
        $message = _DATE.": ".date(_DATETIMEFORMAT, $time)."\n".$from_user." "._WROTE.":\n\n".$message;
        $message = ">".str_replace("\n", "\n>", $message);
        $message = "\n\n\n".$message;
    }

    DisplayHead(_SENDMESSAGETITLE);

?>

<form name="theForm" method="post" action="message.php">
<table border="0" cellpadding="3" cellspacing="2" width="100%">
<tr>
    <td bgcolor="<?php echo $cfg["table_data_bg"] ?>" align="right"><font size=2 face=Arial><?php echo _TO ?>:</font></td>
    <td bgcolor="<?php echo $cfg["table_data_bg"] ?>"><font size=2 face=Arial><input type="Text" name="to_user" value="<?php echo $to_user ?>" size="20" readonly="true"></font></td>
</tr>
<tr>
    <td bgcolor="<?php echo $cfg["table_data_bg"] ?>" align="right"><font size=2 face=Arial><?php echo _FROM ?>:</font></td>
    <td bgcolor="<?php echo $cfg["table_data_bg"] ?>"><font size=2 face=Arial><input type="Text" name="from_user" value="<?php echo $cfg['user'] ?>" size="20" readonly="true"></font></td>
</tr>
<tr>
    <td bgcolor="<?php echo $cfg["table_data_bg"] ?>" colspan="2">
    <div align="center">
    <table border="0" cellpadding="0" cellspacing="0">
    <tr>
        <td>
        <font size=2 face=Arial>
        <?php echo _YOURMESSAGE ?>:<br>
          <textarea cols="72" rows="10" name="message" wrap="hard" tabindex="1"><?php echo $message ?></textarea><br>
        <input type="Checkbox" name="to_all" value=1><?php echo _SENDTOALLUSERS ?>
<?php
    if (IsAdmin())
    {
        echo "&nbsp;&nbsp;&nbsp;&nbsp;";
        echo "<input type=\"Checkbox\" name=\"force_read\" value=1>"._FORCEUSERSTOREAD."";
    }
?>
        <br>
        <div align="center">
        <input type="Submit" name="Submit" value="<?php echo _SEND ?>">
        </div>
        </font>
        </td>
    </tr>
    </table>
    </div>
    </td>
</tr>
</table>
</form>
<script>document.theForm.message.focus();</script>

<?php

    DisplayFoot();

} // end the else

?>