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

checkUserPath();

// Setup some defaults if they are not set.
$del = getRequestVar('del');
$down = getRequestVar('down');
$tar = getRequestVar('tar');
$dir = stripslashes(urldecode(getRequestVar('dir')));
if (strpos(stripslashes($dir),"../")===false) {} else {echo "Can't go to parent directories!";exit;}

// Are we to delete something?
if ($del != "")
{
    $current = "";
    // The following lines of code were suggested by Jody Steele jmlsteele@stfu.ca
    // this is so only the owner of the file(s) or admin can delete
    if(IsAdmin($cfg["user"]) || preg_match("/^" . $cfg["user"] . "/",$del))
    {
        // Yes, then delete it

        // we need to strip slashes twice in some circumstances
        // Ex.  If we are trying to delete test/tester's file/test.txt
        //    $del will be "test/tester\\\'s file/test.txt"
        //    one strip will give us "test/tester\'s file/test.txt
        //    the second strip will give us the correct
        //        "test/tester's file/test.txt"

        $del = stripslashes(stripslashes($del));

        if (!ereg("(\.\.\/)", $del))
        {
            avddelete($cfg["path"].$del);

            $arTemp = explode("/", $del);
            if (count($arTemp) > 1)
            {
                array_pop($arTemp);
                $current = implode("/", $arTemp);
            }
            AuditAction($cfg["constants"]["fm_delete"], $del);
        }
        else
        {
            AuditAction($cfg["constants"]["error"], "ILLEGAL DELETE: ".$cfg['user']." tried to delete ".$del);
        }
    }
    else
    {
        AuditAction($cfg["constants"]["error"], "ILLEGAL DELETE: ".$cfg['user']." tried to delete ".$del);
    }

    header("Location: dir.php?dir=".urlencode($current));
}

// Are we to download something?
if ($down != "" && $cfg["enable_file_download"])
{
    $current = "";
    // Yes, then download it

    // we need to strip slashes twice in some circumstances
    // Ex.  If we are trying to download test/tester's file/test.txt
    // $down will be "test/tester\\\'s file/test.txt"
    // one strip will give us "test/tester\'s file/test.txt
    // the second strip will give us the correct
    //  "test/tester's file/test.txt"

    $down = stripslashes(stripslashes($down));

    if (!ereg("(\.\.\/)", $down))
    {
        $path = $cfg["path"].$down;

        $p = explode(".", $path);
        $pc = count($p);

        $f = explode("/", $path);
        $file = array_pop($f);
        $arTemp = explode("/", $down);
        if (count($arTemp) > 1)
        {
            array_pop($arTemp);
            $current = implode("/", $arTemp);
        }

        if (file_exists($path))
        {
            header("Content-type: application/octet-stream\n");
            header("Content-disposition: attachment; filename=\"".$file."\"\n");
            header("Content-transfer-encoding: binary\n");
            header("Content-length: " . file_size($path) . "\n");

            // write the session to close so you can continue to browse on the site.
            session_write_close();

            //$fp = fopen($path, "r");
            $fp = popen("cat \"$path\"", "r");
            fpassthru($fp);
            pclose($fp);

            AuditAction($cfg["constants"]["fm_download"], $down);
            exit();
        }
        else
        {
            AuditAction($cfg["constants"]["error"], "File Not found for download: ".$cfg['user']." tried to download ".$down);
        }
    }
    else
    {
        AuditAction($cfg["constants"]["error"], "ILLEGAL DOWNLOAD: ".$cfg['user']." tried to download ".$down);
    }
    header("Location: dir.php?dir=".urlencode($current));
}

// Are we to download something?
if ($tar != "" && $cfg["enable_file_download"])
{
    $current = "";
    // Yes, then tar and download it

    // we need to strip slashes twice in some circumstances
    // Ex.  If we are trying to download test/tester's file/test.txt
    // $down will be "test/tester\\\'s file/test.txt"
    // one strip will give us "test/tester\'s file/test.txt
    // the second strip will give us the correct
    //  "test/tester's file/test.txt"

    $tar = stripslashes(stripslashes($tar));

    if (!ereg("(\.\.\/)", $tar))
    {
        // This prevents the script from getting killed off when running lengthy tar jobs.
        ini_set("max_execution_time", 3600);
        $tar = $cfg["path"].$tar;

        $arTemp = explode("/", $tar);
        if (count($arTemp) > 1)
        {
            array_pop($arTemp);
            $current = implode("/", $arTemp);
        }

        // Find out if we're really trying to access a file within the
        // proper directory structure. Sadly, this way requires that $cfg["path"]
        // is a REAL path, not a symlinked one. Also check if $cfg["path"] is part
        // of the REAL path.
        if (is_dir($tar))
        {
            $sendname = basename($tar);

            switch ($cfg["package_type"])
            {
                Case "tar":
                    $command = "tar cf - \"".addslashes($sendname)."\"";
                    break;
                Case "zip":
                    $command = "zip -0r - \"".addslashes($sendname)."\"";
                    break;
                default:
                    $cfg["package_type"] = "tar";
                    $command = "tar cf - \"".addslashes($sendname)."\"";
                    break;
            }

            // HTTP/1.0
            header("Pragma: no-cache");
            header("Content-Description: File Transfer");
            header("Content-Type: application/force-download");
            header('Content-Disposition: attachment; filename="'.$sendname.'.'.$cfg["package_type"].'"');

            // write the session to close so you can continue to browse on the site.
            session_write_close();

            // Make it a bit easier for tar/zip.
            chdir(dirname($tar));
            passthru($command);

            AuditAction($cfg["constants"]["fm_download"], $sendname.".".$cfg["package_type"]);
            exit();
        }
        else
        {
            AuditAction($cfg["constants"]["error"], "Illegal download: ".$cfg['user']." tried to download ".$tar);
        }
    }
    else
    {
        AuditAction($cfg["constants"]["error"], "ILLEGAL TAR DOWNLOAD: ".$cfg['user']." tried to download ".$tar);
    }
    header("Location: dir.php?dir=".urlencode($current));
}

if ($dir == "")
{
    unset($dir);
}

if (isset($dir))
{
    if (ereg("(\.\.)", $dir))
    {
        unset($dir);
    }
    else
    {
        $dir = $dir."/";
    }
}

DisplayHead(_DIRECTORYLIST);
?>

<script language="JavaScript">
function MakeTorrent(name_file)
{
    window.open (name_file,'_blank','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=no,width=600,height=430')
}

function ConfirmDelete(file)
{
    return confirm("<?php echo _ABOUTTODELETE ?>: " + file)
}

function checkCheck(thisIn)
{
    var form = thisIn.form, i = 0;
    for(i=0; i < form.length; i++)
    {
        if(form[i].type == 'checkbox' && form[i].name != 'checkall')
        {
            form[i].checked = thisIn.checked;
        }
    }
}

</script>

<?php

displayDriveSpaceBar(getDriveSpace($cfg["path"]));
echo "<br>";

if(!isset($dir)) $dir = "";

if (!file_exists($cfg["path"].$dir))
{
    echo "<strong>".htmlentities($dir)."</strong> could not be found or is not valid.";
}
else
{
    ListDirectory($cfg["path"].$dir);
}

DisplayFoot();


//**************************************************************************
// ListDirectory()
// This method reads files and directories in the specified path and
// displayes them.
function ListDirectory($dirName)
{
    global $dir, $cfg;
    $bgLight = $cfg["bgLight"];
    $bgDark = $cfg["bgDark"];
    $entrys = array();

    $bg = $bgLight;

    $dirName = stripslashes($dirName);

    if (isset($dir))
    {
        //setup default parent directory URL
        $parentURL = "dir.php";

        //get the real parentURL
        if (preg_match("/^(.+)\/.+$/",$dir,$matches) == 1)
        {
            $parentURL="dir.php?dir=" . urlencode($matches[1]);
        }

        echo "<table cellpadding=2 width=740>";
        echo '<form action="multi.php" method="post" name="multidir">';
        echo '<input type="hidden" name="action" value="fileDelete" />';
        echo '<tr><td colspan="2">';
        echo "<a href=\"" . $parentURL . "\"><img src=\"images/up_dir.gif\" width=16 height=16 title=\""._BACKTOPARRENT."\" border=0>["._BACKTOPARRENT."]</a>";
        echo '</td>';
        echo '<td align="right">Multi-Delete-&gt;</td>';
        echo '<td align="right"><a href="javascript:document.multidir.submit()" onclick="return ConfirmDelete(\'Multiple Files\')">';
        echo '<img src="images/delete_on.gif" title="Delete Multiple Files" border="0" height="16" width="16"></a>';
        echo '<input type="checkbox" onclick="checkCheck(this);" /></td></tr>';
    }

    $handle = opendir($dirName);
    while($entry = readdir($handle))
    {
        $entrys[] = $entry;
    }
    natsort($entrys);

    foreach($entrys as $entry)
    {
        if ($entry != "." && $entry != ".." && substr($entry, 0, 1) != ".")
        {
            if (@is_dir($dirName.$entry))
            {
                echo "<tr bgcolor=\"".$bg."\"><td><a href=\"dir.php?dir=".urlencode($dir.$entry)."\"><img src=\"images/folder2.gif\" width=\"16\" height=\"16\" title=\"".$entry."\" border=\"0\" align=\"absmiddle\">".$entry."</a></td>";
                echo "<td>&nbsp;</td>";
                echo "<td>&nbsp;</td>";
                echo "<td align=\"right\">";

                if ($cfg["enable_maketorrent"])
                {
                    echo "<a href=\"JavaScript:MakeTorrent('maketorrent.php?path=".urlencode($dir.$entry)."')\"><img src=\"images/make.gif\" width=16 height=16 title=\"Make Torrent\" border=0></a>";
                }

                if ($cfg["enable_file_download"])
                {
                    echo "<a href=\"dir.php?tar=".urlencode($dir.$entry)."\"><img src=\"images/tar_down.gif\" width=16 height=16 title=\"Download as ".$cfg["package_type"]."\" border=0></a>";
                }

                // The following lines of code were suggested by Jody Steele jmlsteele@stfu.ca
                // this is so only the owner of the file(s) or admin can delete
                // only give admins and users who "own" this directory
                // the ability to delete sub directories
                if(IsAdmin($cfg["user"]) || preg_match("/^" . $cfg["user"] . "/",$dir))
                {
                    echo "<a href=\"dir.php?del=".urlencode($dir.$entry)."\" onclick=\"return ConfirmDelete('".addslashes($entry)."')\"><img src=\"images/delete_on.gif\" width=16 height=16 title=\""._DELETE."\" border=0></a>";
                    echo "<input type=\"checkbox\" name=\"file[]\" value=\"".urlencode($dir.$entry)."\">";
                }
                else
                {
                    echo "&nbsp;";
                }
                echo "</td></tr>\n";
                if ($bg == $bgLight)
                {
                    $bg = $bgDark;
                }
                else
                {
                    $bg = $bgLight;
                }
            }
            else
            {
                // Do nothing
            }
        }
    }
    closedir($handle);

    $entrys = array();
    $handle = opendir($dirName);
    while($entry = readdir($handle))
    {
        $entrys[] = $entry;
    }
    natsort($entrys);

    foreach($entrys as $entry)
    {
        if ($entry != "." && $entry != "..")
        {
            if (@is_dir($dirName.$entry))
            {
                // Do nothing
            }
            else
            {
                $arStat = @lstat($dirName.$entry);
                $arStat[7] = ( $arStat[7] == 0 )? file_size( $dirName . $entry ) : $arStat[7];
                if (array_key_exists(10,$arStat))
                {
                        $timeStamp = $arStat[10];
                }
                else
                {
                    $timeStamp = "";
                }
                $fileSize = number_format(($arStat[7])/1024);
                // Code added by Remko Jantzen to assign an icon per file-type. But when not
                // available all stays the same.
                $image="images/time.gif";
                $imageOption="images/files/".getExtension($entry).".png";
                if (file_exists("./".$imageOption))
                {
                    $image = $imageOption;
                }

                echo "<tr bgcolor=\"".$bg."\">";
                echo "<td>";

                // Can users download files?
                if ($cfg["enable_file_download"])
                {
                    // Yes, let them download
                    echo "<a href=\"dir.php?down=".urlencode($dir.$entry)."\" >";
                    echo "<img src=\"".$image."\" width=\"16\" height=\"16\" alt=\"".$entry."\" border=\"0\"></a>";
                    echo "<a href=\"dir.php?down=".urlencode($dir.$entry)."\" >".$entry."</a>";
                }
                else
                {
                    // No, just show the name
                    echo "<img src=\"".$image."\" width=\"16\" height=\"16\" alt=\"".$entry."\" border=\"0\">";
                    echo $entry;
                }

                echo "</td>";
                echo "<td align=\"right\">".$fileSize." KB</td>";
                echo "<td>".date("m-d-Y g:i a", $timeStamp)."</td>";
                echo "<td align=\"right\">";

                if( $cfg["enable_view_nfo"] && (( substr( strtolower($entry), -4 ) == ".nfo" ) || ( substr( strtolower($entry), -4 ) == ".txt" ))  )
                {
                    echo "<a href=\"viewnfo.php?path=".urlencode(addslashes($dir.$entry))."\"><img src=\"images/view_nfo.gif\" width=16 height=16 title=\"View '$entry'\" border=0></a>";
                }

                if ($cfg["enable_maketorrent"])
                {
                    echo "<a href=\"JavaScript:MakeTorrent('maketorrent.php?path=".urlencode($dir.$entry)."')\"><img src=\"images/make.gif\" width=16 height=16 title=\"Make Torrent\" border=0></a>";
                }

                if ($cfg["enable_file_download"])
                {
                    // Show the download button
                    echo "<a href=\"dir.php?down=".urlencode($dir.$entry)."\" >";
                    echo "<img src=\"images/download_owner.gif\" width=16 height=16 title=\"Download\" border=0>";
                    echo "</a>";
                }

                // The following lines of code were suggested by Jody Steele jmlsteele@stfu.ca
                // this is so only the owner of the file(s) or admin can delete
                // only give admins and users who "own" this directory
                // the ability to delete files
                if(IsAdmin($cfg["user"]) || preg_match("/^" . $cfg["user"] . "/",$dir))
                {
                    echo "<a href=\"dir.php?del=".urlencode($dir.$entry)."\" onclick=\"return ConfirmDelete('".addslashes($entry)."')\"><img src=\"images/delete_on.gif\" width=16 height=16 title=\""._DELETE."\" border=0></a>";
                    echo "<input type=\"checkbox\" name=\"file[]\" value=\"".urlencode($dir.$entry)."\">";
                }
                else
                {
                    echo "&nbsp;";
                }
                echo "</td></tr>\n";

                if ($bg == $bgLight)
                {
                    $bg = $bgDark;
                }
                else
                {
                    $bg = $bgLight;
                }
            }
        }
    }
    closedir($handle);
    echo "</table>";
}

// ***************************************************************************
// ***************************************************************************
// Checks for the location of the users directory
// If it does not exist, then it creates it.
function checkUserPath()
{
    global $cfg;
    // is there a user dir?
    if (!is_dir($cfg["path"].$cfg["user"]))
    {
        //Then create it
        mkdir($cfg["path"].$cfg["user"], 0777);
    }
}


// This function returns the extension of a given file.
// Where the extension is the part after the last dot.
// When no dot is found the noExtensionFile string is
// returned. This should point to a 'unknown-type' image
// time by default. This string is also returned when the
// file starts with an dot.
function getExtension($fileName)
{
    $noExtensionFile="unknown"; // The return when no extension is found

    //Prepare the loop to find an extension
    $length = -1*(strlen($fileName)); // The maximum negative value for $i
    $i=-1; //The counter which counts back to $length

    //Find the last dot in an string
    while (substr($fileName,$i,1) != "." && $i > $length) {$i -= 1; }

    //Get the extension (with dot)
    $ext = substr($fileName,$i);

    //Decide what to return.
    if (substr($ext,0,1)==".") {$ext = substr($ext,((-1 * strlen($ext))+1)); } else {$ext = $noExtensionFile;}

    //Return the extension
    return strtolower($ext);
}

?>
