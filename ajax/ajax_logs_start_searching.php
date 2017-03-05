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
require_once "$root/classes/Session.class.php";
$session = new Session();
$session->start_session('_owlgaming');

$userID = $_SESSION['userid'];

if (!isset($userID) or ! $userID) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    die();
}

if (!isset($_SESSION['groups'])) {
    echo "Session has timed out.";
    exit();
} else {

    $log_Types = (isset($_POST['logTypes']) ? $_POST['logTypes'] : '');
    if (count($log_Types) < 1) {
        exit('<center>Connection to server was broken.</center>');
    }
    require_once "$root/classes/Database.class.php";

    $dbMTACache = new Database("MTA");
    $dbMTACache->connect();

    $perms = $dbMTACache->query_first("SELECT `admin`, `supporter`, `vct`, `mapper`, `scripter`, `fmt` FROM `accounts` WHERE `id`='" . $userID . "'");
    $canSee = false;
    foreach($perms as $perm => $rank) {
        if ($rank > 0) {
            $canSee = true;
            break;
        }
    }

    if(!$canSee) {
        die('Permission Denied.');
    }

    $now = new DateTime();
    echo '<h3>-> logs query made @ server time ' . $now->format('d/m/Y H:i:s') . '</h3>';

    $characterCache = array();

    function nameCache($id) {
        global $characterCache, $dbMTACache;
        if (isset($characterCache[$id])) {
            return $characterCache[$id];
        }

        $pos = strpos($id, "ch");
        if ($pos === false) {
            $pos = strpos($id, "fa");
            if ($pos === false) {
                $pos = strpos($id, "ve");
                if ($pos === false) {
                    $pos = strpos($id, "ac");
                    if ($pos === false) {
                        $pos = strpos($id, "in");
                        if ($pos === false) {
                            $pos = strpos($id, "ph");
                            if ($pos === false) {
                                $characterCache[$id] = $id . '[unknown]';
                                return $id;
                            } else {
                                $tempid = substr($id, 2);
                                $characterCache[$id] = "phone " . $tempid;
                                return $id;
                            }
                        } else {
                            $tempid = substr($id, 2);
                            $characterCache[$id] = "interior " . $tempid;
                            return $id;
                        }
                    } else {
                        $tempid = substr($id, 2);
                        $awsQry = $dbMTACache->query_first("SELECT `username` FROM `accounts` WHERE `id`='" . $tempid . "'");
                        if ($awsQry and $awsQry['username'] and strlen($awsQry['username']) > 0) {
                            $characterCache[$id] = $awsQry['username'];
                            return $awsQry['username'];
                        } else {
                            $characterCache[$id] = $id;
                            return $id;
                        }
                    }
                } else {
                    $tempid = substr($id, 2);
                    $characterCache[$id] = "vehicle " . $tempid;
                    return $characterCache[$id];
                }
            } else {
                $tempid = substr($id, 2);
                $awsQry = $dbMTACache->query_first("SELECT `name` FROM `factions` WHERE `id`='" . $tempid . "'");
                if ($awsQry and $awsQry['name'] and strlen($awsQry['name']) > 0) {
                    $characterCache[$id] = '[F]' . $awsQry['name'];
                    return $awsQry['name'];
                } else {
                    $characterCache[$id] = $id;
                    return $id;
                }
            }
        } else {
            $tempid = substr($id, 2);
            $awsQry = $dbMTACache->query_first("SELECT `charactername` FROM `characters` WHERE `id`='" . $tempid . "'");
            if ($awsQry and $awsQry['charactername'] and strlen($awsQry['charactername']) > 0) {
                $characterCache[$id] = str_replace("_", " ", $awsQry['charactername']);
                return $characterCache[$id];
            } else {
                $characterCache[$id] = $id . '[' . $tempid . ']';
                return $id;
            }
        }
    }

    $tableText = '<center><table id="newspaper-a" border="0" align="center" >
    <tr>
        <th>Time</th>
        <th>Action</th>
        <th>Player</th>
        <th>Data</th>
        <th>Affected Elements</th>
    </tr>';

    function getExactKeywordIfAny($text) {
        $first = substr($text, 0, 1);
        $last = substr($text, -1, 1);
        if ($first == '[' and $last == ']') {
            return substr($text, 1, -1);
        }
        return false;
    }

    $foundElement = "none";
    $error = 'none';

    $dbMTA = new Database("MTA");
    $dbMTA->connect(true);
    
    $keyword_type = (isset($_POST['keyword_type']) ? $_POST['keyword_type'] : null);
    $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : null;
    
    if ($keyword_type == 'character') {
        $exactKeyword = getExactKeywordIfAny($keyword);
        if ($exactKeyword) {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`charactername` FROM `characters` WHERE `charactername` = '" . $dbMTA->escape(str_replace(" ", "_", $exactKeyword)) . "' LIMIT 1");
        } else {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`charactername` FROM `characters` WHERE `charactername` LIKE '%" . $dbMTA->escape(str_replace(" ", "_", $keyword)) . "%' ORDER BY charactername LIMIT 20");
        }
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'ch' . $sqlRow['id'];
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No character name matched your query. Try again with another keyword.';
        } else {
            $error = 'The following character name matched your query:<BR />';
            $count = array();
            while ($sqlRow = $dbMTA->fetch_array($fetchIDquery)) {
                $error .= ' ' . htmlspecialchars($sqlRow['charactername']) . '<BR />';
                array_push($count, $sqlRow['charactername']);
            }
            if (count($count) >= 20) {
                $error .= '<i>- (And more..)</i><BR />';
            }
            $error .= "<br>Please be more specific or use [" . $keyword . "] to find exact. <br>For example: Use [" . $keyword . "] to find '" . $keyword . "' among '" . $count[0] . "', '" . $count[1] . "' and '" . $keyword . "'. ";
        }
        $dbMTA->free_result();
    } else if ($keyword_type == 'vehicle ID') {
        $fetchIDquery = $dbMTA->query("SELECT `id` FROM `vehicles` WHERE `id`='" . $dbMTA->escape($keyword) . "' LIMIT 1");
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 've' . $sqlRow['id'];
        } else {
            $error = 'No vehicle or too many vehicles were found with that ID.';
        }
        $dbMTA->free_result();
    } elseif ($keyword_type == 'interior ID') {
        $fetchIDquery = $dbMTA->query("SELECT `id` FROM `interiors` WHERE `id`='" . $dbMTA->escape($keyword) . "' LIMIT 1");
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'in' . $sqlRow['id'];
            $dbMTA->free_result();
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No interior found with that ID.';
        }
    } elseif ($keyword_type == 'account') {
        $exactKeyword = getExactKeywordIfAny($keyword);
        if ($exactKeyword) {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`username` FROM `accounts` WHERE `username` = '" . $dbMTA->escape(str_replace(" ", "_", $exactKeyword)) . "' LIMIT 1");
        } else {
            $fetchIDquery = $dbMTA->query("SELECT `id`,`username` FROM `accounts` WHERE `username` LIKE '%" . $dbMTA->escape(str_replace(" ", "_", $keyword)) . "%' ORDER BY username LIMIT 20");
        }
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'ac' . $sqlRow['id'];
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No account name matched your query. Try again with another keyword.';
        } else {
            $error = 'The following accounts matched your query:<BR />';
            $count = array();
            while ($sqlRow = $dbMTA->fetch_array($fetchIDquery)) {
                $error .= ' ' . htmlspecialchars($sqlRow['username']) . '<BR />';
                array_push($count, $sqlRow['username']);
            }
            if (count($count) >= 20) {
                $error .= '<i>- (And more..)</i><BR />';
            }
            $error .= "<br>Please be more specific or use [" . $keyword . "] to find exact. <br>For example: Use [" . $keyword . "] to find '" . $keyword . "' among '" . $count[0] . "', '" . $count[1] . "' and '" . $keyword . "'. ";
        }
        $dbMTA->free_result();
    } elseif ($keyword_type == 'phonenumber') {
        $fetchIDquery = $dbMTA->query("SELECT `phonenumber` FROM `phones` WHERE `phonenumber`='" . $dbMTA->escape($keyword) . "' LIMIT 1");
        if ($dbMTA->affected_rows() == 1) {
            $sqlRow = $dbMTA->fetch_array($fetchIDquery);
            $foundElement = 'ph' . $sqlRow['phonenumber'];
        } elseif ($dbMTA->affected_rows() == 0) {
            $error = 'No phone or too many phones were found with that number. ';
        } else {
            $dbMTA->free_result();
        }
    }

    $dbMTA->close();
    if ($error != 'none') {
        die($error);
    }

    $selecterror = false;
    $queryLogTypes = '( (1=2) ';
    foreach ($log_Types as $logtype) {
        $queryLogTypes .= " OR (`action`='" . $logtype . "') ";
    }
    $queryLogTypes .= ')';

    $dbLogs = new Database("LOGS");
    $dbLogs->connect(true);

    $awesomeQuery = "SELECT *, DATE_FORMAT(`time`,'%b %d, %Y %h:%i %p') AS `time` FROM `owl_logs` WHERE ";
    $order = " ORDER BY time DESC";
    $time = (isset($_POST['start_point']) ? $_POST['start_point'] : null);
    //echo convertTimeInterval($time);

    $queryTail = convertTimeInterval($time) . " AND " . $queryLogTypes . " ";

    $timeStringForLog = date ( 'y-m-d H:i:s' , time() );
    $searcherUsername = "ac" . $_SESSION['userid'];
    $dbLogs->query("INSERT INTO owl_logs VALUES ('" . $timeStringForLog . "', '32', '" . $searcherUsername . "', '" . $searcherUsername . "', '" . "Log Types: " . rtrim(implode(',', $log_Types), ',')  . " - Keyword Type: " . $keyword_type . " - Keyword: " . $keyword . " - Time: " . $time . "') ");
    //$awesomeQuery .= $queryTail;
    if ($keyword_type == 'logtext') {
        $queryTail .= " AND (`content` LIKE '%" . $dbLogs->escape($keyword) . "%') ";
    } else {
        if ($foundElement == 'none') {
            $dbLogs->close();
            die($error);
        }
        $queryTail .= " AND (`source`='" . $dbLogs->escape($foundElement) . "' OR `affected` LIKE '%" . $dbLogs->escape($foundElement) . ";%') ";
    }
    //$queryTail .= $order;

    $start = (isset($_POST['start']) ? $_POST['start'] : 0);
    $limit = (isset($_POST['limit']) ? $_POST['limit'] : 100);
    $loadto = (isset($_POST['loadto']) ? $_POST['loadto'] : "logs_result");

    $eu = ($start - 0);                               // No of records to be shown per page.
    $this1 = $eu + $limit;
    $back = $eu - $limit;
    $next = $eu + $limit;

    $nume = 0;
    $nume = $dbLogs->query_first("SELECT COUNT(time) AS count FROM owl_logs WHERE $queryTail")['count'];

    if ($nume > 0) {
        echo $tableText;
        $awesomeQryExe = $dbLogs->query($awesomeQuery . $queryTail . $order . " LIMIT $eu, $limit ");
        $i = 0;
        require_once '../functions/functions_logs.php';
        while ($row = $dbLogs->fetch_array($awesomeQryExe)) {
            $i = $i + 1;   //  increment for alternate color of rows
            $explodedArr = explode(';', $row['affected']);
            $explodedStr = ""; //"Affected: <BR />";
            foreach ($explodedArr as $objectid) {
                if ($objectid != '') {
                    $explodedStr .= htmlspecialchars(nameCache($objectid)) . ", ";
                }
            }
            echo "<tr><td style='overflow: hidden;white-space: nowrap;'>" . htmlspecialchars($row['time']) . "</td><td style='overflow: hidden;white-space: nowrap;'>" . $logTypes[$row['action']][0] . "</td><td style='overflow: hidden;white-space: nowrap;'>" . htmlspecialchars(nameCache($row['source'])) . "</td><td>" . htmlspecialchars($row['content']) . "</td><td>" . $explodedStr . "</td></tr>\r\n";
        }
        $dbLogs->free_result();

        if ($nume > $limit) {
            echo "<br><table align = 'center' width='100%'><tr><td  align='left' width=5%>";
            if ($back >= 0) {
                ?>
                <a href='#' onclick="load_server_logs('<?php echo $limit ?>', '<?php echo $back ?>', '<?php echo $loadto ?>');
                        return false;"><b>PREV</b></a>
                   <?php
               }
               echo "</td><td align=center width=90%>";
               $i = 0;
               $l = 1;
               for ($i = 0; $i < $nume; $i = $i + $limit) {
                   if ($i <> $eu) {
                       ?>
                    <a href='#' onclick="load_server_logs('<?php echo $limit ?>', '<?php echo $i ?>', '<?php echo $loadto ?>');
                            return false;"><b><?php echo $l; ?></b></a>
                       <?php
                   } else {
                       echo "<b>$l</b>";
                   }        /// Current page is not displayed as link and given font color red
                   $l = $l + 1;
               }
               echo "</td><td  align='right' width=5%>";
               if ($this1 < $nume) {
                   ?>
                <a href='#' onclick="load_server_logs('<?php echo $limit ?>', '<?php echo $next ?>', '<?php echo $loadto ?>');
                        return false;"><b>NEXT</b></a>
                <?php
            }
            echo "</td></tr></table></td></tr>";
            echo "</table>";
        }
    } else {
        echo "Nothing has found in logs database.";
    }

    //Delete logs older than 6 months.
    //$dbLogs->query("DELETE FROM owl_logs WHERE `time` < (NOW() - INTERVAL 6 MONTH)");
    $dbLogs->close();
    $dbMTACache->close();
}

function convertTimeInterval($text = null) {
    if ($text == "1h")
        return " (  (`time` > (NOW() - INTERVAL 1 HOUR)) ) ";
    else if ($text == "8h")
        return " (  (`time` > (NOW() - INTERVAL 8 HOUR)) ) ";
    else if ($text == "12h")
        return " (  (`time` > (NOW() - INTERVAL 12 HOUR)) ) ";
    else if ($text == "1d")
        return " (  (`time` > (NOW() - INTERVAL 1 DAY)) ) ";
    else if ($text == "3d")
        return " (  (`time` > (NOW() - INTERVAL 3 DAY)) ) ";
    else if ($text == "1w")
        return " (  (`time` > (NOW() - INTERVAL 1 WEEK)) ) ";
    else if ($text == "2w")
        return " (  (`time` > (NOW() - INTERVAL 2 WEEK)) ) ";
    else if ($text == "1m")
        return " (  (`time` > (NOW() - INTERVAL 1 MONTH)) ) ";
    else if ($text == "3m")
        return " (  (`time` > (NOW() - INTERVAL 3 MONTH)) ) ";
    else if ($text == "6m")
        return " (  (`time` > (NOW() - INTERVAL 6 MONTH)) ) ";
    else if ($text == "1y")
        return " (  (`time` > (NOW() - INTERVAL 1 YEAR)) ) ";
    else if ($text == "2y")
        return " (  (`time` > (NOW() - INTERVAL 2 YEAR)) ) ";
    else
        return " (  `time` < NOW() ) ";
}
