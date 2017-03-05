<?php

/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */

class Rss {

    private $mta;
    private $ucp;
    private $root;
    private $filter_key;
    private $limit;
    private $cache;
    private $db;

    public function __construct() {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        $this->root = $root;
        require_once "$this->root/config.inc.php";
        $this->mta = FEED_MTA;
        $this->ucp = FEED_UCP;
        $this->filter_key = FEED_FILTER_KEY;
        $this->limit = FEED_LIMIT;
        $this->cache = FEED_CACHE_TIMEOUT;
    }

    function dbConnect($conn = false) {
        if ($conn) {
            $this->db = $conn;
        } else {
            require_once "$this->root/classes/Database.class.php";
            $this->db = new Database("MTA");
            $this->db->connect();
        }
    }

    function dbClose() {
        @$this->db->close();
    }

    public function load($url) {
        return @simplexml_load_file($url);
    }

    public function outputFeedDev() {
        // define the path and name of cached file
        $cachefile = "$this->root/cache/news_commits.php";
        $cachetime = $this->cache; //
        // Check if the cached file is still fresh. If it is, serve it up and exit.
        if ($cachetime > 0 and file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
            include($cachefile);
        } else {
            $this->dbConnect();
            
            @$this->fetchDevCommits($this->mta);
            @$this->fetchDevCommits($this->ucp);
            
            $table = $this->db->fetch_all_array("SELECT * FROM ucp_release_notes ORDER BY date DESC LIMIT " . $this->limit);
            $this->dbClose();
            
            // if there is either no file OR the file to too old, render the page and capture the HTML.
            ob_start();
            echo "<html>";
            foreach ($table as $rs) {
                echo "■ " . $this->formatDate($rs['date']) . ': ' . $rs['title'] . '<br>';
            }
            echo "</html>";
            // We're done! Save the cached content to a file
            $fp = fopen($cachefile, 'w');
            fwrite($fp, ob_get_contents());
            fclose($fp);
            // finally send browser output
            ob_end_flush();
        }
    }

    public function fetchDevCommits($feed) {
        if ($feed) {
            $dev = $this->load($feed);
            $this->replaceIntoDb($dev);
        }
    }

    private function replaceIntoDb($xml) {
        foreach ($xml->channel->item as $item) {
            $title = @$this->canShowThisFeed($item->title);
            if ($title and strlen($title) > 0) {
                $date = strtotime($item->pubDate, time());
                $title = $this->db->escape($title);
                $this->db->query("REPLACE INTO ucp_release_notes (date, title) VALUES ('$date', '$title') ");
            }
        }
    }

    private function formatDate($last_access) {
        if ($last_access >= strtotime("today"))
            return "Today";
        else if ($last_access >= strtotime("yesterday"))
            return "Yesterday";
        else {
            return date("M d, Y", $last_access);
        }
    }

    private function canShowThisFeed($title) {
        if ($this->filter_key and strlen($this->filter_key) > 0) {
            if (strpos($title, strtolower($this->filter_key)) !== false) {
                return str_replace(strtolower($this->filter_key), "", $title);
            } else if (strpos($title, strtoupper($this->filter_key)) !== false) {
                return str_replace(strtoupper($this->filter_key), "", $title);
            } else if (strpos($title, "[PUBLIC]") !== false) {
                return str_replace("[PUBLIC]", "", $title);
            } else if (strpos($title, "[Public]") !== false) {
                return str_replace("[Public]", "", $title);
            } else {
                return false;
            }
        } else {
            return $title;
        }
    }

    private function formatTitle($title) {
        $text = $title;
        $line = 150;
        if ($text and strlen($text) > $line) {
            $text1 = substr($text, 0, $line);
            $text2 = substr($text, $line, strlen($text) - 1);
            $text = str_replace("'", "", $text);
            $text = str_replace('"', "", $text);
            return $text1 . '.. <a title="Read More" href="#" onclick="alert(\'' . $text . '\'); return false;">☼</a>';
        } else {
            return $text;
        }
    }

}

?>