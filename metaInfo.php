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

class dir
{
    var $name;
    var $subdirs;
    var $files;
    var $num;
    var $prio;

    function dir($name,$num,$prio)
    {
        $this->name = $name;
        $this->num = $num;
        $this->prio = $prio;
        $this->files = array();
        $this->subdirs = array();
    }

    function &addFile($file)
    {
        $this->files[] =& $file;
        return $file;
    }

    function &addDir($dir)
    {
        $this->subdirs[] =& $dir;
        return $dir;
    }

    // code changed to support php4
    // thx to Mistar Muffin
    function &findDir($name)
    {
        foreach (array_keys($this->subdirs) as $v)
        {
            $dir =& $this->subdirs[$v];
            if($dir->name == $name)
            {
                return $dir;
            }
        }
        return false;
    }

    function draw($parent)
    {
        echo("d.add(".$this->num.",".$parent.",\"".$this->name."\",".$this->prio.",0);\n");

        foreach($this->subdirs as $v)
        {
            $v->draw($this->num);
        }

        foreach($this->files as $v)
        {
            if(is_object($v))
            {
              echo("d.add(".$v->num.",".$this->num.",\"".$v->name."\",".$v->prio.",".$v->size.");\n");
            }
        }
    }

}

class file {

    var $name;
    var $prio;
    var $size;
    var $num;

    function file($name,$num,$size,$prio)
    {
        $this->name = $name;
        $this->num  = $num;
        $this->size = $size;
        $this->prio = $prio;
    }
}

function showMetaInfo($torrent, $allowSave=false)
{
    global $cfg;

    if (empty($torrent) || !file_exists($cfg["torrent_file_path"].$torrent))
    {
        echo _NORECORDSFOUND;
    }
    elseif ($cfg["enable_file_priority"])
    {

        $prioFileName = $cfg["torrent_file_path"].getAliasName($torrent).".prio";

        require_once('BDecode.php');

        echo '<link rel="StyleSheet" href="dtree.css" type="text/css" /><script type="text/javascript" src="dtree.js"></script>';

        $ftorrent = $cfg["torrent_file_path"].$torrent;

        $fp = fopen($ftorrent, "rd");
        if (!$fp)
        {
            // Not able to open file
            echo _NORECORDSFOUND;
        }
        else
        {
            $alltorrent = fread($fp, filesize($ftorrent));
            fclose($fp);
    
            $btmeta = BDecode($alltorrent);
            $torrent_size = $btmeta["info"]["piece length"] * (strlen($btmeta["info"]["pieces"]) / 20);
    
            if (array_key_exists('files',$btmeta['info']))
            {
                $dirnum = count($btmeta['info']['files']);
            }
            else
            {
                $dirnum = 0;
            }
    
            if ( is_readable($prioFileName))
            {
                $prio = split(',',file_get_contents($prioFileName));
                $prio = array_splice($prio,1);
            }
            else
            {
                $prio = array();
                for($i=0;$i<$dirnum;$i++)
                {
                    $prio[$i] = -1;
                }
            }
    
            $tree = new dir("/",$dirnum,isset($prio[$dirnum])?$prio[$dirnum]:-1);
    
            if (array_key_exists('files',$btmeta['info']))
            {
                foreach( $btmeta['info']['files'] as $filenum => $file)
                {
    
                    $depth = count($file['path']);
                    $branch =& $tree;
    
                    for($i=0; $i < $depth; $i++)
                    {
                        if ($i != $depth-1)
                        {
                            $d =& $branch->findDir($file['path'][$i]);
    
                            if($d)
                            {
                                $branch =& $d;
                            }
                            else
                            {
                                $dirnum++;
                                $d =& $branch->addDir(new dir($file['path'][$i], $dirnum, (isset($prio[$dirnum])?$prio[$dirnum]:-1)));
                                $branch =& $d;
                            }
                        }
                        else
                        {
                            $branch->addFile(new file($file['path'][$i]." (".$file['length'].")",$filenum,$file['length'],$prio[$filenum]));
                        }
    
                    }
                }
            }
    
            echo "<table><tr>";
            echo "<tr><td width=\"110\">Metainfo File:</td><td>".$torrent."</td></tr>";
            echo "<tr><td>Directory Name:</td><td>".htmlentities($btmeta['info']['name'], ENT_QUOTES)."</td></tr>";
            echo "<tr><td>Announce URL:</td><td>".htmlentities($btmeta['announce'], ENT_QUOTES)."</td></tr>";
    
            if(array_key_exists('comment',$btmeta))
            {
                echo "<tr><td valign=\"top\">Comment:</td><td>".htmlentities($btmeta['comment'], ENT_QUOTES)."</td></tr>";
            }
    
            echo "<tr><td>Created:</td><td>".date("F j, Y, g:i a",$btmeta['creation date'])."</td></tr>";
            echo "<tr><td>Torrent Size:</td><td>".$torrent_size." (".formatBytesToKBMGGB($torrent_size).")</td></tr>";
            echo "<tr><td>Chunk size:</td><td>".$btmeta['info']['piece length']." (".formatBytesToKBMGGB($btmeta['info']['piece length']).")</td></tr>";
    
            if (array_key_exists('files',$btmeta['info']))
            {
    
                echo "<tr><td>Selected size:</td><td id=\"sel\">0</td></tr>";
                echo "</table><br>\n";
    
                if ($allowSave)
                {
                    echo "<form name=\"priority\" action=\"index.php\" method=\"POST\" >";
                    echo "<input type=\"hidden\" name=\"torrent\" value=\"".$torrent."\" >";
                    echo "<input type=\"hidden\" name=\"setPriorityOnly\" value=\"true\" >";
                }
    
                echo "<script type=\"text/javascript\">\n";
                echo "var sel = 0;\n";
                echo "d = new dTree('d');\n";
    
                $tree->draw(-1);
    
                echo "document.write(d);\n";
                echo "sel = getSizes();\n";
                echo "drawSel();\n";
                echo "</script>\n";
    
                echo "<input type=\"hidden\" name=\"filecount\" value=\"".count($btmeta['info']['files'])."\">";
                echo "<input type=\"hidden\" name=\"count\" value=\"".$dirnum."\">";
                echo "<br>";
                if ($allowSave)
                {
                    echo '<input type="submit" value="Save" >';
                    echo "<br>";
                }
                echo "</form>";
            }
            else
            {
                echo "</table><br>";
                echo htmlentities($btmeta['info']['name'].$torrent_size." (".formatBytesToKBMGGB($torrent_size).")", ENT_QUOTES);
            }
        }
    }
    else
    {
        $result = shell_exec("cd " . $cfg["torrent_file_path"]."; " . $cfg["pythonCmd"] . " -OO " . $cfg["btshowmetainfo"]." ".escapeshellarg($torrent));
        echo "<pre>";
        echo htmlentities($result, ENT_QUOTES);
        echo "</pre>";
    }
}
?>
