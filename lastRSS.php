<?php
/*
 ======================================================================
 lastRSS 0.6

 Simple yet powerful PHP class to parse RSS files.

 by Vojtech Semecky, webmaster@webdot.cz

 Latest version, features, manual and examples:
     http://lastrss.webdot.cz/

 ----------------------------------------------------------------------
 TODO
 - Iconv nedavat na cely, ale jen na TITLE a DESCRIPTION (u item i celkove)
 ----------------------------------------------------------------------
 LICENSE

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License (GPL)
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.

 To read the license please visit http://www.gnu.org/copyleft/gpl.html
 ======================================================================
*/

class lastRSS {
    // -------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------
    var $channeltags = array ('title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'pubDate', 'lastBuildDate', 'rating', 'docs');
    var $itemtags = array('title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate', 'source');
    var $imagetags = array('title', 'url', 'link', 'width', 'height');
    var $textinputtags = array('title', 'description', 'name', 'link');

    var $strip_html = true;

    var $time_out = 5;
    var $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20040913 Firefox/0.10";

    // -------------------------------------------------------------------
    // Parse RSS file and returns associative array.
    // -------------------------------------------------------------------
    function Get ($rss_url) {
        // If CACHE ENABLED
        if ($this->cache_dir != '') {
            $cache_file = $this->cache_dir . '/rsscache_' . md5($rss_url);
            $timedif = @(time() - filemtime($cache_file));
            if ($timedif < $this->cache_time) {
                // cached file is fresh enough, return cached array
                $result = unserialize(join('', file($cache_file)));
                // set 'cached' to 1 only if cached file is correct
                if ($result) $result['cached'] = 1;
            } else {
                // cached file is too old, create new
                $result = $this->Parse($rss_url);
                $serialized = serialize($result);
                if ($f = @fopen($cache_file, 'w')) {
                    fwrite ($f, $serialized, strlen($serialized));
                    fclose($f);
                }
                if ($result) $result['cached'] = 0;
            }
        }
        // If CACHE DISABLED >> load and parse the file directly
        else {
            $result = $this->Parse($rss_url);
            if ($result) $result['cached'] = 0;
        }
        // return result
        return $result;
    }

    // -------------------------------------------------------------------
    // Modification of preg_match(); return trimmed field with index 1
    // from 'classic' preg_match() array output
    // -------------------------------------------------------------------
    function my_preg_match ($pattern, $subject) {
        preg_match($pattern, $subject, $out);
        return trim($out[1]);
    }

    // -------------------------------------------------------------------
    // Replace HTML entities &something; by real characters
    // -------------------------------------------------------------------
    function unhtmlentities ($string) {
        $trans_tbl = get_html_translation_table (HTML_ENTITIES);
        $trans_tbl = array_flip ($trans_tbl);
        return strtr ($string, $trans_tbl);
    }

    // -------------------------------------------------------------------
    // Encoding conversion function
    // -------------------------------------------------------------------
    function MyConvertEncoding($in_charset, $out_charset, $string) {
        // if substitute_character
        if ($this->subs_char) {
            // Iconv() to UTF-8. mb_convert_encoding() to $out_charset
            $utf = iconv($in_charset, 'UTF-8', $string);
            mb_substitute_character($this->subs_char);
            return mb_convert_encoding ($utf, $out_charset, 'UTF-8');
        } else {
            // Iconv() to $out_charset
            return iconv($in_charset, $out_charset, $string);
        }
    }

    // -------------------------------------------------------------------
    // Parse() is private method used by Get() to load and parse RSS file.
    // Don't use Parse() in your scripts - use Get($rss_file) instead.
    // -------------------------------------------------------------------
    function Parse ($rss_url) {
        include_once( "db.php" );
        include_once( "functions.php" );

        // Open and load RSS file
        $rss_content = fetchHTML( $rss_url );

        if( empty( $rss_content ) )
        {
            return false;
        }

        // Parse document encoding
        $result['encoding'] = $this->my_preg_match("'encoding=[\'\"](.*?)[\'\"]'si", $rss_content);

        // If code page is set convert character encoding to required
            if ($this->cp != '')
                $rss_content = $this->MyConvertEncoding($result['encoding'], $this->cp, $rss_content);

        // Parse CHANNEL info
        preg_match("'<channel.*?>(.*?)</channel>'si", $rss_content, $out_channel);
        foreach($this->channeltags as $channeltag)
        {
            $temp = $this->my_preg_match("'<$channeltag.*?>(.*?)</$channeltag>'si", $out_channel[1]);
            if ($temp != '') $result[$channeltag] = $temp; // Set only if not empty

        }

        // Parse TEXTINPUT info
        preg_match("'<textinput(|[^>]*[^/])>(.*?)</textinput>'si", $rss_content, $out_textinfo);
            // This a little strange regexp means:
            // Look for tag <textinput> with or without any attributes, but skip truncated version <textinput /> (it's not beginning tag)
        if ($out_textinfo[2]) {
            foreach($this->textinputtags as $textinputtag) {
                $temp = $this->my_preg_match("'<$textinputtag.*?>(.*?)</$textinputtag>'si", $out_textinfo[2]);
                if ($temp != '') $result['textinput_'.$textinputtag] = $temp; // Set only if not empty
            }
        }
        // Parse IMAGE info
        preg_match("'<image.*?>(.*?)</image>'si", $rss_content, $out_imageinfo);
        if ($out_imageinfo[1]) {
            foreach($this->imagetags as $imagetag) {
                $temp = $this->my_preg_match("'<$imagetag.*?>(.*?)</$imagetag>'si", $out_imageinfo[1]);
                if ($temp != '') $result['image_'.$imagetag] = $temp; // Set only if not empty
            }
        }
        // Parse ITEMS
        preg_match_all("'<item(| .*?)>(.*?)</item>'si", $rss_content, $items);
        $rss_items = $items[2];
        $result['items_count'] = count($items[1]);
        $i = 0;
        $result['items'] = array(); // create array even if there are no items
        foreach($rss_items as $rss_item) {
            // Parse one item
            foreach($this->itemtags as $itemtag)
            {
                $temp = $this->my_preg_match("'<$itemtag.*?>(.*?)</$itemtag>'si", $rss_item);
                if ($temp != '') $result[items][$i][$itemtag] = $temp; // Set only if not empty
            }
            // Strip HTML tags and other bullshit from DESCRIPTION (if description is presented)
            if ($result['items'][$i]['description'] && $this->strip_html == true)
            {
                $result['items'][$i]['description'] = strip_tags($this->unhtmlentities(strip_tags($result['items'][$i]['description'])));
            }
            // Item counter
            $i++;
        }
        return $result;
    }
}

?>