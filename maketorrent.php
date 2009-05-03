<?php

/*************************************************************
*  TorrentFlux PHP Torrent Manager
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

   /*****
    Usage: btmakemetafile.py <trackerurl> <file> [file...] [params...]

    --announce_list <arg>
              a list of announce URLs - explained below (defaults to '')

    --httpseeds <arg>
              a list of http seed URLs - explained below (defaults to '')

    --piece_size_pow2 <arg>
              which power of 2 to set the piece size to (0 = automatic) (defaults
              to 0)

    --comment <arg>
              optional human-readable comment to put in .torrent (defaults to '')

    --filesystem_encoding <arg>
              optional specification for filesystem encoding (set automatically in
              recent Python versions) (defaults to '')

    --target <arg>
              optional target file for the torrent (defaults to '')


        announce_list = optional list of redundant/backup tracker URLs, in the format:
               url[,url...][|url[,url...]...]
                    where URLs separated by commas are all tried first
                    before the next group of URLs separated by the pipe is checked.
                    If none is given, it is assumed you don't want one in the metafile.
                    If announce_list is given, clients which support it
                    will ignore the <announce> value.
               Examples:
                    http://tracker1.com|http://tracker2.com|http://tracker3.com
                         (tries trackers 1-3 in order)
                    http://tracker1.com,http://tracker2.com,http://tracker3.com
                         (tries trackers 1-3 in a randomly selected order)
                    http://tracker1.com|http://backup1.com,http://backup2.com
                         (tries tracker 1 first, then tries between the 2 backups randomly)

        httpseeds = optional list of http-seed URLs, in the format:
                url[|url...]
    *****/

    include_once("config.php");
    include_once("functions.php");

    // Variable information
    $tpath    = $cfg["torrent_file_path"];
    $tfile    = getRequestVar('torrent');
    $file     = getRequestVar('path');
    $torrent  = cleanFileName(StripFolders( trim($file) )) . ".torrent";
    $announce = ( getRequestVar('announce') ) ? getRequestVar('announce') : "http://";
    $ancelist = getRequestVar('announcelist');
    $comment  = getRequestVar('comments');
    $peice    = getRequestVar('piecesize');
    $alert    = ( getRequestVar('alert') ) ? 1 : "''";
    $private  = ( getRequestVar('Private') == "Private" ) ? true : false;
    $dht      = ( getRequestVar('DHT') == "DHT" ) ? true : false;

    // Let's create the torrent
    if( !empty( $announce ) && $announce != "http://" )
    {
        // Create maketorrent directory if it doesn't exist
        if( !is_dir( $tpath ) )
        {
            @mkdir( $tpath );
        }

        // Clean up old files
        if( @file_exists( $tpath . $tfile ) )
        {
            @unlink( $tpath . $tfile );
        }

        // This is the command to execute
        $app = "nohup " . $cfg["pythonCmd"] . " -OO " . $cfg["btmakemetafile"] . " " . escapeshellarg($announce) . " " . escapeshellarg( $cfg['path'] . $file ) . " ";

        // Is there comments to add?
        if( !empty( $comment ) )
        {
            $app .= "--comment " . escapeshellarg( $comment ) . " ";
        }

        // Set the piece size
        if( !empty( $peice ) )
        {
            $app .= "--piece_size_pow2 " . escapeshellarg( $peice ) . " ";
        }

        if( !empty( $ancelist ) )
        {
            $check = "/" . str_replace( "/", "\/", quotemeta( $announce ) ) . "/i";
            // if they didn't add the primary tracker in, we will add it for them
            if( preg_match( $check, $ancelist, $result ) )
                $app .= "--announce_list " . escapeshellarg( $ancelist ) . " ";
            else
                $app .= "--announce_list " . escapeshellarg ( $announce . "," . $ancelist ) . " ";
        }

        // Set the target torrent fiel
        $app .= "--target " . escapeshellarg( $tpath . $tfile );

        // Set to never timeout for large torrents
        set_time_limit( 0 );

        // Let's see how long this takes...
        $time_start = microtime( true );

        // Execute the command -- w00t!
        exec( $app );

        // We want to check to make sure the file was successful
        $success = false;
        $raw = @file_get_contents( $tpath . $tfile );
        if( preg_match( "/6:pieces([^:]+):/i", $raw, $results ) )
        {
            // This means it is a valid torrent
            $success = true;

            // Make an entry for the owner
            AuditAction($cfg["constants"]["file_upload"], $tfile);

            // Check to see if one of the flags were set
            if( $private || $dht )
            {
                // Add private/dht Flags
                // e7:privatei1e
                // e17:dht_backup_enablei1e
                // e20:dht_backup_requestedi1e
                if( preg_match( "/6:pieces([^:]+):/i", $raw, $results ) )
                {
                    $pos = strpos( $raw, "6:pieces" ) + 9 + strlen( $results[1] ) + $results[1];
                    $fp = @fopen( $tpath . $tfile, "r+" );
                    @fseek( $fp, $pos, SEEK_SET );
                    if( $private )
                    {
                        @fwrite( $fp, "7:privatei1eee" );
                    }
                    else
                    {
                        @fwrite( $fp, "e7:privatei0e17:dht_backup_enablei1e20:dht_backup_requestedi1eee" );
                    }
                    @fclose( $fp );
                }
            }
        }
        else
        {
            // Something went wrong, clean up
            if( @file_exists( $tpath . $tfile ) )
            {
                @unlink( $tpath . $tfile );
            }
        }

        // We are done! how long did we take?
        $time_end = microtime( true );
        $diff = duration($time_end - $time_start);

        // make path URL friendly to support non-standard characters
        $downpath = urlencode( $tfile );

        // Depending if we were successful, display the required information
        if( $success )
        {
            $onLoad = "completed( '" . $downpath . "', " . $alert. ", '" . $diff . "' );";
        }
        else
        {
            $onLoad = "failed( '" . $downpath . "', " . $alert . " );";
        }
    }

    // This is the torrent download prompt
    if( isset( $_REQUEST["download"] ) )
    {
        $tfile = getRequestVar("download");

        // ../ is not allowed in the file name
        if (!ereg("(\.\.\/)", $tfile))
        {
            // Does the file exist?
            if (file_exists($tpath . $tfile))
            {
                // Prompt the user to download the new torrent file.
                header( "Content-type: application/octet-stream\n" );
                header( "Content-disposition: attachment; filename=\"" . $tfile . "\"\n" );
                header( "Content-transfer-encoding: binary\n");
                header( "Content-length: " . @filesize( $tpath . $tfile ) . "\n" );

                // Send the torrent file
                $fp = @fopen( $tpath . $tfile, "r" );
                @fpassthru( $fp );
                @fclose( $fp );

                AuditAction($cfg["constants"]["fm_download"], $tfile);
            }
            else
            {
                AuditAction($cfg["constants"]["error"], "File Not found for download: ".$cfg['user']." tried to download ".$tfile);
            }
        }
        else
        {
            AuditAction($cfg["constants"]["error"], "ILLEGAL DOWNLOAD: ".$cfg['user']." tried to download ".$tfile);
        }
        exit();
    }

    // Strip the folders from the path
    function StripFolders( $path )
    {
        $pos = strrpos( $path, "/" ) + 1;
        $path = substr( $path, $pos );
        return $path;
    }

    // Convert a timestamp to a duration string
    function duration( $timestamp )
    {

        $years = floor( $timestamp / ( 60 * 60 * 24 * 365 ) );
        $timestamp %= 60 * 60 * 24 * 365;

        $weeks = floor( $timestamp / ( 60 * 60 * 24 * 7 ) );
        $timestamp %= 60 * 60 * 24 * 7;

        $days = floor( $timestamp / ( 60 * 60 * 24 ) );
        $timestamp %= 60 * 60 * 24;

        $hrs = floor( $timestamp / ( 60 * 60 ) );
        $timestamp %= 60 * 60;

        $mins = floor( $timestamp / 60 );
        $secs = $timestamp % 60;

        $str = "";

        if( $years >= 1 )
            $str .= "{$years} years ";
        if( $weeks >= 1 )
            $str .= "{$weeks} weeks ";
        if( $days >= 1 )
            $str .= "{$days} days ";
        if( $hrs >= 1 )
            $str .= "{$hrs} hours ";
        if( $mins >= 1 )
            $str .= "{$mins} minutes ";
        if( $secs >= 1 )
            $str.="{$secs} seconds ";

        return $str;
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<HEAD>
    <META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1" />
    <META HTTP-EQUIV="Pragma" CONTENT="no-cache" charset="<?php echo _CHARSET ?>">
    <TITLE><?php echo $cfg["pagetitle"]; ?> - Torrent Maker</TITLE>
    <LINK REL="icon" HREF="images/favicon.ico" TYPE="image/x-icon" />
    <LINK REL="shortcut icon" HREF="images/favicon.ico" TYPE="image/x-icon" />
    <LINK REL="StyleSheet" HREF="themes/<?php echo $cfg["theme"]; ?>/style.css" TYPE="text/css" />
</HEAD>
<SCRIPT SRC="tooltip.js" TYPE="text/javascript"></SCRIPT>
<SCRIPT LANGUAGE="JavaScript">
    function doSubmit( obj )
    {
        // Basic check to see if maketorrent is already running
        if( obj.value === "Creating..." )
            return false;

        // Run some basic validation
        var valid = true;
        var tlength = document.maketorrent.torrent.value.length - 8;
        var torrent = document.maketorrent.torrent.value.substr( tlength );
        document.getElementById('output').innerHTML = "";
        document.getElementById('ttag').innerHTML   = "";
        document.getElementById('atag').innerHTML   = "";

        if( torrent !== ".torrent" )
        {
            document.getElementById('ttag').innerHTML    = "<b style=\"color: #990000;\">*</b>";
            document.getElementById('output').innerHTML += "<b style=\"color: #990000;\">* Torrent file must end in .torrent</b><BR />";
            valid = false;
        }

        if( document.maketorrent.announce.value === "http://" )
        {
            document.getElementById('atag').innerHTML    = "<b style=\"color: #990000;\">*</b>";
            document.getElementById('output').innerHTML += "<b style=\"color: #990000;\">* Please enter a valid announce URL.</b><BR />";
            valid = false;
        }

        // For saftely reason, let's force the property to false if it's disabled (private tracker)
        if( document.maketorrent.DHT.disabled )
        {
            document.maketorrent.DHT.checked = false;
        }

        // If validation passed, submit form
        if( valid === true )
        {
            disableForm();
            toggleLayer('progress');

            document.getElementById('output').innerHTML += "<b>Creating torrent...</b><BR /><BR />";
            document.getElementById('output').innerHTML += "<i>* Note that larger folder/files will take some time to process,</i><BR />";
            document.getElementById('output').innerHTML += "<i>&nbsp;&nbsp;&nbsp;do not close the window until it has been completed.</i><BR /><BR />";
            document.getElementById('output').innerHTML += "&nbsp;&nbsp;&nbsp;When completed, the torrent will show in your list<BR />";
            document.getElementById('output').innerHTML += "&nbsp;&nbsp;&nbsp;and a download link will be provided.<BR />";

            return true;
        }
        return false;
    }

    function disableForm()
    {
        // Because of IE issue of disabling the submit button,
        // we change the text and don't allow resubmitting
        document.maketorrent.tsubmit.value = "Creating...";
        document.maketorrent.torrent.readOnly = true;
        document.maketorrent.announce.readOnly = true;
    }

    function ToggleDHT( dhtstatus )
    {
        document.maketorrent.DHT.disabled = dhtstatus;
    }

    function toggleLayer( whichLayer )
    {
        if( document.getElementById )
        {
            // This is the way the standards work
            var style2 = document.getElementById(whichLayer).style;
            style2.display = style2.display ? "" : "block";
        }
        else if( document.all )
        {
            // This is the way old msie versions work
            var style2 = document.all[whichLayer].style;
            style2.display = style2.display ? "" : "block";
        }
        else if( document.layers )
        {
            // This is the way nn4 works
            var style2 = document.layers[whichLayer].style;
            style2.display = style2.display ? "" : "block";
        }
    }

    function completed( downpath, alertme, timetaken )
    {
        document.getElementById('output').innerHTML  = "<b style='color: #005500;'>Creation completed!</b><BR />";
        document.getElementById('output').innerHTML += "Time taken: <i>" + timetaken + "</i><BR />";
        document.getElementById('output').innerHTML += "The new torrent has been added to your list.<BR /><BR />"
        document.getElementById('output').innerHTML += "<img src='images/green.gif' border='0' title='Torrent Created' align='absmiddle'> You can download the <a style='font-weight: bold;' href='?download=" + downpath + "'>torrent here</a><BR />";
        if( alertme === 1 )
            alert( 'Creation of torrent completed!' );
    }

    function failed( downpath, alertme )
    {
        document.getElementById('output').innerHTML  = "<b style='color: #AA0000;'>Creation failed!</b><BR /><BR />";
        document.getElementById('output').innerHTML += "An error occured while trying to create the torrent.<BR />";
        if( alertme === 1 )
            alert( 'Creation of torrent failed!' );
    }

    var anlst  = "(optional) announce_list = list of tracker URLs<BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;<i>url[,url...][|url[,url...]...]</i><BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;URLs separated by commas are tried first<BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;before URLs separated by the pipe is checked.<BR />\n";
        anlst += "Examples:<BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;<i>http://a.com<strong>|</strong>http://b.com<strong>|</strong>http://c.com</i><BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(tries <b>a-c</b> in order)<BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;<i>http://a.com<strong>,</strong>http://b.com<strong>,</strong>http://c.com</i><BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(tries <b>a-c</b> in a randomly selected order)<BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;<i>http://a.com<strong>|</strong>http://b.com<strong>,</strong>http://c.com</i><BR />\n";
        anlst += "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(tries <b>a</b> first, then tries <b>b-c</b> randomly)<BR />\n";

    var annce  = "tracker announce URL.<BR /><BR />\n";
        annce += "Example:<BR />\n";
        annce += "&nbsp;&nbsp;&nbsp;&nbsp;<i>http://tracker.com/announce</i><BR />\n";

    var tornt  = "torrent name to be saved as<BR /><BR />\n";
        tornt += "Example:<BR />\n";
        tornt += "&nbsp;&nbsp;&nbsp;&nbsp;<i>gnome-livecd-2.10.torrent</i><BR />\n";

    var comnt  = "add a comment to your torrent file (optional)<BR />\n";
        comnt += "";

    var piece  = "data piece size for torrent<BR />\n";
        piece += "power of 2 value to set the piece size to<BR />\n";
        piece += "(0 = automatic) (0 only option in this version)<BR />\n";

    var prvte  = "private tracker support<BR />\n";
        prvte += "(disallows DHT if enabled)<BR />\n";

    var dhtbl  = "DHT (Distributed Hash Table)<BR /><BR />\n";
        dhtbl += "can only be set abled if private flag is not set true<BR />\n";
</SCRIPT>
<body topmargin="8" leftmargin="5" bgcolor="<?php echo $cfg["main_bgcolor"] ?>" style="font-family:Tahoma, 'Times New Roman'; font-size:12px;" onLoad="
<?php
    if( !empty( $private ) )
        echo "ToggleDHT(true);";
    else
        echo "ToggleDHT(false);";

    if( !empty( $onLoad ) )
        echo $onLoad;
?>">
    <div align="center">
    <table border="0" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <table border="1" bordercolor="<?php echo $cfg["table_border_dk"] ?>" cellpadding="4" cellspacing="0">
                    <tr>
                        <td bgcolor="<?php echo $cfg["main_bgcolor"] ?>" background="themes/<?php echo $cfg["theme"] ?>/images/bar.gif">
                            <?php DisplayTitleBar($cfg["pagetitle"]." - Torrent Maker", false); ?>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="<?php echo $cfg["table_header_bg"] ?>">
                            <div align="left">
                                <table width="100%" bgcolor="<?php echo $cfg["body_data_bg"] ?>">
                                    <tr>
                                        <td>
                                            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="maketorrent" name="maketorrent">
                                            <table>
                                                <tr>
                                                    <td><img align="absmiddle" src="images/info.gif" onmouseover="return escape(tornt);" hspace="1" />Torrent name:</td>
                                                    <td><input type="text" id="torrent" name="torrent" size="55" value="<?php echo $torrent; ?>" /> <label id="ttag"></label></td>
                                                </tr>
                                                <tr>
                                                    <td><img align="absmiddle" src="images/info.gif" onmouseover="return escape(annce);" hspace="1" />Announcement URL:</td>
                                                    <td><input type="text" id="announce" name="announce" size="55" value="<?php echo $announce; ?>" /> <label id="atag"></label></td>
                                                </tr>
                                                <tr>
                                                    <td><img align="absmiddle" src="images/info.gif" onmouseover="return escape(anlst);" hspace="1" />Announce List:</td>
                                                    <td><input type="text" id="announcelist" name="announcelist" size="55" value="<?php echo $ancelist; ?>" /> <label id="altag"></label></td>
                                                </tr>
                                                <tr>
                                                    <td><img align="absmiddle" src="images/info.gif" onmouseover="return escape(piece);" hspace="1" />Piece size:</td>
                                                    <td><select id="piecesize" name="piecesize">
                                                        <!-- <option id="0" value="0" selected>0 (Auto)</option> -->
                                                        <option id="0" value="0" selected>0 (Auto)</option>
                                                        <option id="256" value="18">256</option>
                                                        <option id="512" value="19">512</option>
                                                        <option id="1024" value="20">1024</option>
                                                        <option id="2048" value="21">2048</option>
                                                    </select> bytes</td>
                                                </tr>
                                                <tr>
                                                    <td valign="top"><img align="absmiddle" src="images/info.gif" onmouseover="return escape(comnt);" hspace="1" />Comments:</td>
                                                    <td><textarea cols="50" rows="3" id="comments" name="comments"><?php echo $comment; ?></textarea></td>
                                                </tr>
                                                <tr>
                                                    <td><img align="absmiddle" src="images/info.gif" onmouseover="return escape(prvte);" hspace="1" />Private Torrent:</td>
                                                    <td>
                                                        <input type="radio" id="Private" name="Private" value="Private" onClick="ToggleDHT(true);"<?php echo ( $private ) ? " checked" : ""; ?>>Yes</input>
                                                        <input type="radio" id="Private" name="Private" value="NotPrivate" onClick="ToggleDHT(false);"<?php echo ( !$private ) ? " checked" : ""; ?>>No</input>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><img align="absmiddle" src="images/info.gif" onmouseover="return escape(dhtbl);" hspace="1" />DHT Support:</td>
                                                    <td><input type="checkbox" id="DHT" name="DHT"<?php echo ( $dht ) ? " checked" : ""; ?> value="DHT"></input></td>
                                                </tr>
                                                <tr>
                                                    <td>&nbsp;</td>
                                                    <td>
                                                        <input type="submit" id="tsubmit" name="tsubmit" onClick="return doSubmit(this);" value="Create" />
                                                        <input type="button" id="Cancel" name="close" value="Close" onClick="window.close();" />
                                                        <label for="alert" title="Send alert message box when torrent has been completed.">
                                                            <input type="checkbox" id="alert" name="alert"<?php echo ( $alert != "''" ) ? " checked" : ""; ?> value="AlertMe" />
                                                            Notify me of completion
                                                        </label>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2">
                                                        <div id="progress" name="progress" align="center" style="display: none;"><img src="images/progress_bar.gif" width="200" height="20" /></div>
                                                        <label id="output"></label>
                                                    </td>
                                                </tr>
                                            </table>
                                            </form>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>
<?php
                DisplayTorrentFluxLink();
?>
            </td>
        </tr>
    </table>
    </div>
    <script language="javascript">tt_Init();</script>
</body>
</html>

