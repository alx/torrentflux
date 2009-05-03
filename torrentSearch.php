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

include_once("config.php");
include_once("functions.php");
include_once("searchEngines/SearchEngineBase.php");

    // Go get the if this is a search request. go get the data and produce output.

    $hideSeedless = getRequestVar('hideSeedless');
    if(!empty($hideSeedless))
    {
       $_SESSION['hideSeedless'] = $hideSeedless;
    }

    if (!isset($_SESSION['hideSeedless']))
    {
        $_SESSION['hideSeedless'] = 'no';
    }

    $hideSeedless = $_SESSION['hideSeedless'];

    $pg = getRequestVar('pg');

    $searchEngine = getRequestVar('searchEngine');
    if (empty($searchEngine)) $searchEngine = $cfg["searchEngine"];

    $searchterm = getRequestVar('searchterm');
    if(empty($searchterm))
        $searchterm = getRequestVar('query');

    $searchterm = str_replace(" ", "+",$searchterm);

    // Check to see if there was a searchterm.
    // if not set the get latest flag.
    if (strlen($searchterm) == 0)
    {
        if (! array_key_exists("LATEST",$_REQUEST))
        {
            $_REQUEST["LATEST"] = "1";
        }
    }

    DisplayHead("TorrentSearch "._SEARCH);

    echo "<style>.tinyRow {font-size:2px;height:2px;}</style>";

    // Display the search box
    echo "<a name=\"top\"></a><div align=\"center\">";
    echo "<table border=1 cellspacing=0 width=\"760\" cellpadding=5><tr>";
    echo "<td bgcolor=\"".$cfg["table_header_bg"]."\">";
    echo "<form id=\"searchForm\" name=\"searchForm\" action=\"torrentSearch.php\" method=\"get\">";
    echo _SEARCH." Torrents:<br>";
    echo "<input type=\"text\" name=\"searchterm\" value=\"".str_replace("+", " ",$searchterm)."\" size=30  maxlength=50>&nbsp;";
    echo buildSearchEngineDDL($searchEngine);
    echo "&nbsp;<input type=\"Submit\" value=\""._SEARCH."\">&nbsp;&nbsp;";
    echo "\n<script language=\"JavaScript\">\n";
    echo "      function getLatest()\n";
    echo "      {\n";
    echo "          var selectedItem = document.searchForm.searchEngine.selectedIndex;\n";
    echo "          document.searchForm.searchterm.value = '';\n";
    echo "          document.location.href = 'torrentSearch.php?searchEngine='+document.searchForm.searchEngine.options[selectedItem].value+'&LATEST=1';\n";
    echo "          return true;\n";
    echo "      }\n";
    echo "</script>\n";

    echo "&nbsp;&nbsp;<a href=\"#\" onclick=\"javascript:getLatest()\");\"><img src=\"images/properties.png\" width=18 height=13 title=\"Show Latest Torrents\" align=\"absmiddle\" border=0>Show Latest Torrents</a>";

    echo "</form>";
    echo "* Click on Torrent Links to add them to the Torrent Download List";
    echo "</td>";

    echo "</td><td bgcolor=\"".$cfg["table_header_bg"]."\" align=right valign=top>Visit: &nbsp; &nbsp;".buildSearchEngineLinks($searchEngine). "</td></tr>";

    if (is_file('searchEngines/'.$searchEngine.'Engine.php'))
    {
        include_once('searchEngines/'.$searchEngine.'Engine.php');
        $sEngine = new SearchEngine(serialize($cfg));
        if ($sEngine->initialized)
        {
            echo "<div align=center valign=top>";

            $mainStart = true;

            $catLinks = '';
            $tmpCatLinks = '';
            $tmpLen = 0;
            foreach ($sEngine->getMainCategories() as $mainId => $mainName)
            {
                if (strlen($tmpCatLinks) >= 500 && $mainStart == false)
                {
                    $catLinks .= $tmpCatLinks . "<br>";
                    $tmpCatLinks = '';
                    $mainStart = true;
                }
                if ($mainStart == false) $tmpCatLinks .= " | ";
                $tmpCatLinks .=  "<a href=\"torrentSearch.php?searchEngine=".$searchEngine."&mainGenre=".$mainId."\">".$mainName."</a>";
                $mainStart = false;
            }

            echo $catLinks . $tmpCatLinks;

            if ($mainStart == false)
            {
                echo "<br><br>";
            }
            echo "</div>";
            echo "</td></tr>";

            $mainGenre = getRequestVar('mainGenre');

            if (!empty($mainGenre) && !array_key_exists("subGenre",$_REQUEST))
            {

                $subCats = $sEngine->getSubCategories($mainGenre);
                if (count($subCats) > 0)
                {
                    echo "<tr bgcolor=\"".$cfg["table_header_bg"]."\">";
                    echo "<td colspan=6><form method=get id=\"subLatest\" name=\"subLatest\" action=torrentSearch.php?>";
                    echo "<input type=hidden name=\"searchEngine\" value=\"".$searchEngine."\">";

                    $mainGenreName = $sEngine->GetMainCatName($mainGenre);

                    echo "Category: <b>".$mainGenreName."</a></b> -> ";
                    echo "<select name=subGenre>";

                    foreach ($subCats as $subId => $subName)
                    {
                        echo "<option value=".$subId.">".$subName."</option>\n";
                    }
                    echo "</select> ";
                    echo "<input type=submit value='Show Latest'>";
                    echo "</form>\n";
                }
                else
                {
                    echo "</td></tr></table></div>";
                    // Set the Sub to equal the main for groups that don't have subs.
                    $_REQUEST["subGenre"] = $mainGenre;
                    echo $sEngine->getLatest();
                }
            }
            else
            {
                echo "</td></tr></table></div>";

                if (array_key_exists("LATEST",$_REQUEST) && $_REQUEST["LATEST"] == "1")
                {
                    echo $sEngine->getLatest();
                }
                else
                {
                   echo $sEngine->performSearch($searchterm);
                }
            }
        }
        else
        {
            // there was an error connecting
            echo "</td></tr>";
            echo "<tr><td><br><br><div align=center><strong>".$sEngine->msg."</strong></div><br><br></td></tr>";
            echo "</table></div>";
        }
    }
    else
    {
        // there was an error connecting
        echo "</td></tr>";
        echo "<tr><td><br><br><div align=center><strong>Search Engine not installed.</strong></div><br><br></td></tr>";
        echo "</table></div>";
    }

    DisplayFoot();

?>
