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

# what to do?
$action = getRequestVar("action");
if($action == "torrent" || $action == "data")
{
   $torrent = getRequestVar("torrent");
   foreach($torrent as $key => $element)
   {
      # since we only have the .torrent file, get the stat
      $alias = getAliasName($element).".stat";
      # this is acctualy a copy and paste from index.php since its not a function
      # and we need to call it serveral times in a row
      $element = urldecode($element);

      if (($cfg["user"] == getOwner($element)) || IsAdmin())
      {
           # the user is the owner of the torrent -> delete it
           # FIRST delete data, then the torrent
           if ($action == "data")
           {

               require_once('BDecode.php');
               $ftorrent=$cfg["torrent_file_path"].$element;
               $fd = fopen($ftorrent, "rd");
               $alltorrent = fread($fd, filesize($ftorrent));
               $btmeta = BDecode($alltorrent);
               $delete = $btmeta['info']['name'];
               if(trim($delete) != "")
               {
                   $delete = $cfg['user']."/".$delete;

                   # this is accutaly from dir.php - its not a function, and we need to call it serval times

                   $del = stripslashes(stripslashes($delete));

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
           }
           @unlink($cfg["torrent_file_path"].$element);
           @unlink($cfg["torrent_file_path"].$alias);
           @unlink($cfg["torrent_file_path"].getAliasName($element).".prio");
           AuditAction($cfg["constants"]["delete_torrent"], $element);

       }
       else
       {
           AuditAction($cfg["constants"]["error"], $cfg["user"]." attempted to delete ".$element);
       }
   }
}
else if ( $action == "fileDelete" )
{
    $file = getRequestVar("file");
    // Lets delete some files
    if(is_array($file))
    {
        foreach($file as $key => $element)
        {
            $element = urldecode($element);
            delFile($element);
        }
    }
}

if ( isset($_SERVER["HTTP_REFERER"]) )
{
    header("location: ".$_SERVER["HTTP_REFERER"]);
}
else
{
    header("location: index.php");
}


function delFile($del)
{
    global $cfg;

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
}

?>

