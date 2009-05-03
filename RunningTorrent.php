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

class RunningTorrent
{
    var $statFile = "";
    var $torrentFile = "";
    var $filePath = "";
    var $torrentOwner = "";
    var $processId = "";
    var $args = "";

    function RunningTorrent( $psLine )
    {
        global $cfg;
        if (strlen($psLine) > 0)
        {
            while (strpos($psLine,"  ") > 0)
            {
                $psLine = str_replace("  ",' ',trim($psLine));
            }

            $arr = split(' ',$psLine);

            $this->processId = $arr[0];

            foreach($arr as $key =>$value)
            {
                if ($key == 0)
                {
                    $startArgs = false;
                }
                if ($value == $cfg["btphpbin"])
                {
                    $offset = 2;
                    if(!strpos($arr[$key+$offset],"/",1) > 0)
                    {
                        $offset += 1;
                    }
                    if(!strpos($arr[$key+$offset],"/",1) > 0)
                    {
                        $offset += 1;
                    }
                    $this->filePath = substr($arr[$key+$offset],0,strrpos($arr[$key+$offset],"/")+1);
                    $this->statFile = str_replace($this->filePath,'',$arr[$key+$offset]);
                    $this->torrentOwner = $arr[$key+$offset+1];
                }
                if ($value == '--display_interval')
                {
                    $startArgs = true;
                }
                if ($startArgs)  
                {  
                    if (!empty($value))  
                    {  
                        if (strpos($value,"-",1) > 0)  
                        {  
                            if(array_key_exists($key+1,$arr))
                            {
                                if(strpos($value,"priority") > 0)
                                {
                                    $this->args .= "\n file ".$value." set";
                                }
                                else
                                {
                                    $this->args .= $value.":".$arr[$key+1].",";  
                                }
                            }
                            else
                            {
                                $this->args .= "";
                            }
                        }  
                    }  
                } 
                if ($value == '--responsefile')
                {
                    $this->torrentFile = str_replace($this->filePath,'',$arr[$key+1]);
                }
            }
            $this->args = str_replace("--","",$this->args);
            $this->args = substr($this->args,0,strlen($this->args));
        }
    }

    //----------------------------------------------------------------
    // Private Function to put the variables into a string for writing to file
    function BuildAdminOutput()
    {
        $output = "<tr>";
        $output .= "<td><div class=\"tiny\">";
        $output .= $this->torrentOwner;
        $output .= "</div></td>";
        $output .= "<td><div align=center><div class=\"tiny\" align=\"left\">";
        $output .= str_replace(array(".stat"),"",$this->statFile);
        $output .= "<br>".$this->args."</div></td>";
        $output .= "<td><a href=\"index.php?alias_file=".$this->statFile;
        $output .= "&kill=".$this->processId;
        $output .= "&kill_torrent=".urlencode($this->torrentFile);
        $output .= "&return=admin\">";
        $output .= "<img src=\"images/kill.gif\" width=16 height=16 title=\""._FORCESTOP."\" border=0></a></td>";
        $output .= "</tr>";
        $output .= "\n";

        return $output;

    }
}

?>
