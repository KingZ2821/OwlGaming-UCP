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

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once $root.'/config.inc.php';
class Database {

    var $server = ""; //database server
    var $user = ""; //database login name
    var $pass = ""; //database login password
    var $database = ""; //database name
    var $pre = ""; //table prefix
    var $showerror = false;
#######################
//internal info
    var $error = "";
    var $errno = 0;
//number of rows affected by SQL query
    var $affected_rows = 0;
    var $link_id = 0;
    var $query_id = 0;

#-#############################################
# desc: constructor

    function Database($database) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root.'/config.inc.php';
        if (isset($database) && ($database == "FORUMS")) {
            $this->server = DB_FORUMS_SERVER;
            $this->user = DB_FORUMS_USER;
            $this->pass = DB_FORUMS_PASS;
            $this->database = DB_FORUMS_DATABASE;
            $this->pre = DB_FORUMS_PREFIX;
        } elseif (isset($database) && $database == "LOGS") {
            $this->server = DB_LOGS_SERVER;
            $this->user = DB_LOGS_USER;
            $this->pass = DB_LOGS_PASS;
            $this->database = DB_LOGS_DATABASE;
            $this->pre = DB_LOGS_PREFIX;
        } elseif (isset($database) && $database == "MTA") {
            $this->server = DB_SERVER;
            $this->user = DB_USER;
            $this->pass = DB_PASS;
            $this->database = DB_DATABASE;
            $this->pre = DB_PREFIX;
        }
        $this->showerror = DB_SHOW_ERROR;
    }

#-#constructor()
#-#############################################
# desc: connect and select database using vars above
# Param: $new_link can force connect() to open a new link, even if mysql_connect() was called before with the same parameters

    function affected_rows() {
        return $this->affected_rows;
    }

    function connect($new_link = false) {
        // var_dump($this->server);die;
        $this->link_id = mysql_connect($this->server, $this->user, $this->pass, $new_link);

        if (!$this->link_id) {//open failed
            $this->oops("Could not connect to server: <b>$this->server</b>.");
        }

        if (!@mysql_select_db($this->database, $this->link_id)) {//no database
            $this->oops("Could not open database: <b>$this->database</b>.");
        }

        // Deprecated but will get us rolling with mysql 5.7 for now.

        $this->query("SET SESSION sql_mode = ''");

        // unset the data so it can't be dumped
        $this->server = '';
        $this->user = '';
        $this->pass = '';
        $this->database = '';
    }

#-#connect()
#-#############################################
# desc: close the connection

    function close() {
        if (!@mysql_close($this->link_id)) {
            $this->oops("Connection close failed.");
        }
    }

#-#close()
#-#############################################
# Desc: escapes characters to be mysql ready
# Param: string
# returns: string

    function escape($string) {
        if (get_magic_quotes_runtime())
            $string = stripslashes($string);
        return @mysql_real_escape_string($string, $this->link_id);
    }

#-#escape()
#-#############################################
# Desc: executes SQL query to an open connection
# Param: (MySQL query) to execute
# returns: (query_id) for fetching results etc

    function query($sql) {
        // do query
        $this->query_id = @mysql_query($sql, $this->link_id);

        if (!$this->query_id) {
            $this->oops("<b>MySQL Query fail:</b> $sql");
            return 0;
        }

        $this->affected_rows = mysql_affected_rows($this->link_id);

        return $this->query_id;
    }

#-#query()
#-#############################################
# desc: fetches and returns results one line at a time
# param: query_id for mysql run. if none specified, last used
# return: (array) fetched record(s)

    function fetch_array($query_id = -1) {
        // retrieve row
        if ($query_id != -1) {
            $this->query_id = $query_id;
        }

        if (isset($this->query_id)) {
            $record = @mysql_fetch_assoc($this->query_id);
        } else {
            $this->oops("Invalid query_id: <b>$this->query_id</b>. Records could not be fetched.");
        }

        return $record;
    }

#-#fetch_array()
#-#############################################
# desc: returns all the results (not one row)
# param: (MySQL query) the query to run on server
# returns: assoc array of ALL fetched results

    function fetch_all_array($sql) {
        $query_id = $this->query($sql);
        $out = array();

        while ($row = $this->fetch_array($query_id)) {
            $out[] = $row;
        }

        $this->free_result($query_id);
        return $out;
    }

#-#fetch_all_array()
#-#############################################
# desc: frees the resultset
# param: query_id for mysql run. if none specified, last used

    function free_result($query_id = -1) {
        if ($query_id != -1) {
            $this->query_id = $query_id;
        }
        if ($this->query_id != 0 && !@mysql_free_result($this->query_id)) {
            $this->oops("Result ID: <b>$this->query_id</b> could not be freed.");
        }
    }

#-#free_result()
#-#############################################
# desc: does a query, fetches the first row only, frees resultset
# param: (MySQL query) the query to run on server
# returns: array of fetched results

    function query_first($query_string) {
        $query_id = $this->query($query_string);
        $out = $this->fetch_array($query_id);
        $this->free_result($query_id);
        return $out;
    }

#-#query_first()
#-#############################################
# desc: does an update query with an array
# param: table (no prefix), assoc array with data (doesn't need escaped), where condition
# returns: (query_id) for fetching results etc

    function query_update($table, $data, $where = '1') {
        $q = "UPDATE `" . $this->pre . $table . "` SET ";

        foreach ($data as $key => $val) {
            if (strtolower($val) == 'null')
                $q.= "`$key` = NULL, ";
            elseif (strtolower($val) == 'now()')
                $q.= "`$key` = NOW(), ";
            elseif (preg_match("/^increment\((\-?\d+)\)$/i", $val, $m))
                $q.= "`$key` = `$key` + $m[1], ";
            else
                $q.= "`$key`='" . $this->escape($val) . "', ";
        }

        $q = rtrim($q, ', ') . ' WHERE ' . $where . ';';

        return $this->query($q);
    }

#-#query_update()
#-#############################################
# desc: does an insert query with an array
# param: table (no prefix), assoc array with data
# returns: id of inserted record, false if error

    function query_insert($table, $data) {
        $q = "INSERT INTO `" . $this->pre . $table . "` ";
        $v = '';
        $n = '';
        foreach ($data as $key => $val) {
            $n.="`$key`, ";
            if (strtolower($val) == 'null')
                $v.="NULL, ";
            elseif (strtolower($val) == 'now()')
                $v.="NOW(), ";
            else
                $v.= "'" . $this->escape($val) . "', ";
        }

        $q .= "(" . rtrim($n, ', ') . ") VALUES (" . rtrim($v, ', ') . ");";
        if ($this->query($q)) {
            //$this->free_result();
            return mysql_insert_id($this->link_id);
        } else {
            return false;
        }
    }

#-#query_insert()
#-#############################################
# desc: throw an error message
# param: [optional] any custom error to display

    function oops($msg = '') {
        if ($this->showerror) {
            if ($this->link_id > 0 and $this->link_id != 5) {
                $this->error = mysql_error($this->link_id);
                $this->errno = mysql_errno($this->link_id);
            } else {
                $this->error = mysql_error();
                $this->errno = mysql_errno();
            }
            ?>
            <table align="center" border="1" cellspacing="0" style="background:white;color:black;width:80%;">
                <tr><th colspan=2>Database Error</th></tr>
                <tr><td align="right" valign="top">Message:</td><td><?php echo $msg; ?></td></tr>
            <?php if (!empty($this->error)) echo '<tr><td align="right" valign="top" nowrap>MySQL Error:</td><td>' . $this->error . '</td></tr>'; ?>
                <tr><td align="right">Date:</td><td><?php echo date("l, F j, Y \a\\t g:i:s A"); ?></td></tr>
                <?php if (!empty($_SERVER['REQUEST_URI'])) echo '<tr><td align="right">Script:</td><td><a href="' . $_SERVER['REQUEST_URI'] . '">' . $_SERVER['REQUEST_URI'] . '</a></td></tr>'; ?>
                <?php if (!empty($_SERVER['HTTP_REFERER'])) echo '<tr><td align="right">Referer:</td><td><a href="' . $_SERVER['HTTP_REFERER'] . '">' . $_SERVER['HTTP_REFERER'] . '</a></td></tr>'; ?>
            </table>
                <?php
            }
        }

#-#oops()
    }

//CLASS Database
###################################################################################################
    ?>