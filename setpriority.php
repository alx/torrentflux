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

include_once("settingsfunctions.php");

function getFile($var)
{
    if ($var < 65535)
        return true;
    else
        return false;
}

//*********************************************************
// setPriority()
function setPriority($torrent)
{
    global $cfg;

    // we will use this to determine if we should create a prio file.
    // if the user passes all 1's then they want the whole thing.
    // so we don't need to create a prio file.
    // if there is a -1 in the array then they are requesting
    // to skip a file. so we will need to create the prio file.

    $okToCreate = false;

    if(!empty($torrent))
    {

        $alias = getAliasName($torrent);
        $fileName = $cfg["torrent_file_path"].$alias.".prio";

        $result = array();
        $files = array();
        $files = getRequestVar('files');
        
        // if there are files to get then process and create a prio file.
        if (is_array($files) && count($files) > 0)
        {
            $files = array_filter($files,"getFile");
            for($i=0;$i<getRequestVar('count');$i++)
            {
                if(in_array($i,$files))
                {
                    array_push($result,1);
                }
                else
                {
                    $okToCreate = true;
                    array_push($result,-1);
                }
            }
            $alias = getAliasName($torrent);

            if ($okToCreate)
            {
                $fp = fopen($fileName, "w");
                fwrite($fp,getRequestVar('filecount').",");
                fwrite($fp,implode($result,','));
                fclose($fp);
            }
            else
            {
                // No files to skip so must be wanting them all.
                // So we will remove the prio file.
                @unlink($fileName);
            }
        }
        else
        {
            // No files selected so must be wanting them all.
            // So we will remove the prio file.
            @unlink($fileName);
        }
    }
}

?>
