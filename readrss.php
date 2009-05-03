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
include_once("lastRSS.php");

// check http://varchars.com/rss/ for feeds

// The following is for PHP < 4.3
if (!function_exists('html_entity_decode'))
{
    function html_entity_decode($string, $opt = ENT_COMPAT)
    {
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);

        if ($opt & 1)
        {
            // Translating single quotes
            // Add single quote to translation table;
            // doesn't appear to be there by default
            $trans_tbl["&apos;"] = "'";
        }

        if (!($opt & 2))
        {
            // Not translating double quotes
            // Remove double quote from translation table
            unset($trans_tbl["&quot;"]);
        }

        return strtr ($string, $trans_tbl);
    }
}

// Just to be safe ;o)
if (!defined("ENT_COMPAT")) define("ENT_COMPAT", 2);
if (!defined("ENT_NOQUOTES")) define("ENT_NOQUOTES", 0);
if (!defined("ENT_QUOTES")) define("ENT_QUOTES", 3);

DisplayHead("RSS Torrents");

// Get RSS feeds from Database
$arURL = GetRSSLinks();

// create lastRSS object
$rss = new lastRSS();

// setup transparent cache
$rss->cache_dir = $cfg["torrent_file_path"];
$rss->cache_time = $cfg["rss_cache_min"] * 60; // 1200 = 20 min.  3600 = 1 hour
$rss->strip_html = false; // don't remove HTML from the description

echo "<a name=\"top\"></a><div align=\"center\">";
echo "<table border=1 cellspacing=0 width=\"760\" cellpadding=5><tr>";
echo "<td bgcolor=\"".$cfg["table_header_bg"]."\">RSS Feeds (jump list):";
echo "<ul>";

$jumpCount = 0;
$rssfeed = array();

if (is_array($arURL))
{
    // Loop through each RSS feed
    foreach( $arURL as $rid => $url )
    {
        if( $rs = $rss->get( $url ) )
        {
            if( !empty( $rs["items"] ) )
            {
                // Cache rss feed so we don't have to call it again
                $rssfeed[] = $rs;
                echo "<li><a href=\"#".$jumpCount."\">".$rs["title"]."</a></li>\n";
            }
            else
            {
                $rssfeed[] = "";
                echo "<li>* RSS timed out * (<a href=\"#".$rid."\">".$url."</a>)</li>\n";
            }
        }
        else
        {
            // Unable to grab RSS feed, must of timed out
            $rssfeed[] = "";
            echo "<li>* RSS timed out * (<a href=\"#".$jumpCount."\">".$url."</a>)</li>\n";
        }
        $jumpCount++;
    }
    echo "</ul>* Click on Torrent Links below to add them to the Torrent Download List</td>";
    echo "</tr></table>";
    echo "</div>";

    if(is_array($rssfeed))
    {
        // Parse through cache RSS feed
        foreach( $rssfeed as $rid => $rs )
        {
            $title = "";
            $content = "";
            $pageUrl = "";

            if( !empty( $rs["items"] ) )
            {
                // get Site title and Page Link
                $title = $rs["title"];
                $pageUrl = $rs["link"];

                $content = "";

                for ($i=0; $i < count($rs["items"]); $i++)
                {
                    $link = $rs["items"][$i]["link"];
                    $title2 = $rs["items"][$i]["title"];
                    $pubDate = (!empty($rs["items"][$i]["pubDate"])) ? $rs["items"][$i]["pubDate"] : "Unknown";

                    // RSS entry needs to have a link, otherwise pointless
                    if( empty( $link ) )
                        continue;

                    if($link != "" && $title2 !="")
                    {
                        $content .= "<tr><td><img src=\"images/download_owner.gif\" width=\"16\" height=\"16\" title=\"".$link."\"><a href=\"index.php?url_upload=".$link."\">".$title2."</a></td><td> ".$pubDate."</td></tr>\n";
                    }
                    else
                    {
                        $content .= "<tr><td  class=\"tiny\"><img src=\"images/download_owner.gif\" width=\"16\" height=\"16\">".ScrubDescription(str_replace("Torrent: <a href=\"", "Torrent: <a href=\"index.php?url_upload=", html_entity_decode($rs["items"][$i]["description"])), $title2)."</td><td valign=\"top\">".$pubDate."</td></tr>";
                    }
                }
            }
            else
            {
                // Request timed out, display timeout message
                echo "<br>**** RSS timed out: <a href=\"".$url."\" target=\"_blank\">".$url."</a>";
            }

            if ($content != "") { // Close the content and add a line break
                $content .= "<br>";
            }
            displayNews($title, $pageUrl, $content, $rid);
        }
    }
}

DisplayFoot();

function displayNews($title, $pageUrl, $content, $rid) {
    global $cfg;
    // Draw the Table
    echo "<a name=\"".$rid."\"></a><table width=\"760\" border=1 bordercolor=\"".$cfg["table_admin_border"]."\" cellpadding=\"2\" cellspacing=\"0\" bgcolor=\"".$cfg["table_data_bg"]."\">";
    echo "<tr><td colspan=2 bgcolor=\"".$cfg["table_header_bg"]."\" background=\"themes/".$cfg["theme"]."/images/bar.gif\">";
    echo "<img src=\"images/properties.png\" width=18 height=13 border=0>&nbsp;&nbsp;<strong><a href=\"".$pageUrl."\" target=\"_blank\"><font class=\"adminlink\">".$title."</font></a>&nbsp;&nbsp;<font class=\"tinywhite\">[<a href=\"#\"><font class=\"tinywhite\">top</font></a>]</font></strong>";
    echo "</td></tr>";
    echo "<tr><td bgcolor=\"".$cfg["table_header_bg"]."\"><div align=center class=\"title\">"._TORRENTFILE."</div></td>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\" width=\"33%\"><div align=center class=\"title\">"._TIMESTAMP."</div></td>";

    echo $content;

    echo "</table>";
}

// Scrub the description to take out the ugly long URLs
function ScrubDescription($desc, $title)
{
    $rtnValue = "";

    $parts = explode("</a>", $desc);

    $replace = ereg_replace('">.*$', '">'.$title."</a>", $parts[0]);

    if (strpos($parts[1], "Search:") !== false)
    {
        $parts[1] = $parts[1]."</a>\n";
    }

    for($inx = 2; $inx < count($parts); $inx++)
    {
        if (strpos($parts[$inx], "Info: <a ") !== false)
        {
            // We have an Info: and URL to clean
            $parts[$inx] = ereg_replace('">.*$', '" target="_blank">Read More...</a>', $parts[$inx]);
        }
    }

    $rtnValue = $replace;
    for ($inx = 1; $inx < count($parts); $inx++)
    {
        $rtnValue .= $parts[$inx];
    }

    return $rtnValue;
}

?>
