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

function canUserAccessTcBackEnd($text = "") {
    require_once 'functions.php';
    return isPlayerTrialAdmin($text) or isPlayerScripter($text) or isPlayerSupporter($text);
}

function getTicketStatus($id = false, $tcid, $showBtns = false, $type = false, $private = 0) {
    $tail = '';
    $text = '';
    $close = " | <a href='#' onclick='change_status(" . $tcid . ",4); return false;'>Close</a>";
    $open = " | <a href='#' onclick='change_status(" . $tcid . ",0); return false;'>Open</a>";
    $lock = " | <a href='#' onclick='change_status(" . $tcid . ",-1); return false;'>Lock & Archive</a>";
    $unlock = " | <a href='#' onclick='change_status(" . $tcid . ",4); return false;'>Unlock</a>";
    $private1 = " | <a href='#' onclick='toggle_private(" . $tcid . ",1); return false;'>Make private</a>";
    $unprivate = " | <a href='#' onclick='toggle_private(" . $tcid . ",0); return false;'>Make public</a>";
    if ($id == 0) {
        $text = '<div class="status_btn_green">Open</div>';
        $tail = $close;
    } else if ($id == 1) {
        $text = '<div class="status_btn_green">Assigned</div>';
        $tail = $close . $lock;
    } else if ($id == 2) {
        $text = '<div class="status_btn_orange">Answered</div>';
        $tail = $close . $lock;
    } else if ($id == 3) {
        $text = '<div class="status_btn_yellow">Responded</div>';
        $tail = $close . $lock;
    } else if ($id == 4) {
        $text = '<div class="status_btn_red">Closed</div>';
        $tail = $open . $lock;
    } else {
        $text = '<div class="status_btn_red">Locked</div>';
        $tail = $unlock;
    }

    if ($type == 6) {
        if ($private == 1) {
            $tail .= $unprivate;
        } else {
            $tail .= $private1;
        }
    }
    if ($showBtns and isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        return $text . $tail;
    } else {
        return $text;
    }
}

function getTicketTypes() {
    $ticketTypes = array(
        0 => "Misc",
        1 => "Account issue",
        2 => "Unban request",
        3 => "Refund request",
        4 => "Donation issue or question",
        5 => "General question",
        6 => "Bug report",
        7 => "Player report",
        8 => "History appeal",
        9 => "Staff report"
    );
    return $ticketTypes;
}

function getTicketType($type = 0) {
    $ticketTypes = getTicketTypes();
    if ($ticketTypes[$type])
        return $ticketTypes[$type];
    else
        return $ticketTypes[0];
}

function formatDays($days = 0) {
    if ($days <= 0)
        return "Today";
    else if ($days == 1)
        return "Yesterday";
    else if ($days > 1)
        return $days . " days ago";
    else
        return "Unknown";
}

function canUserViewTicket($userid, $ticket) {
    if (!is_numeric($ticket['creator'])) {
        return true;
    }
    if ($ticket['type'] == 6 and $ticket['private'] == 0) { //bug
        return true;
    }
    if (isset($userid)) {
        if ($userid == $ticket['creator'] or $userid == $ticket['assign_to']) {
            return true;
        } else {
            $subs = explode(",", $ticket['subcribers']);
            foreach ($subs as $sub) {
                if ($sub == $userid) {
                    return true;
                }
            }
            if (canUserAccessTcBackEnd($_SESSION['groups']) and isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                require_once 'functions.php';
                if ($ticket['type'] == 6 and $ticket['private'] == 1) { //bug
                    return isPlayerScripter($_SESSION['groups']);
                } else if ($ticket['type'] == 9) {
                    return isPlayerLeadAdmin($_SESSION['groups']);
                } else {
                    return true;
                }
            }
        }
    }
    return false;
}

function canUserCommentOnBugReport($userid, $ticket) {
    if (isset($userid) and $ticket['type'] == 6) { //bug
        require_once 'functions.php';
        if ($userid == $ticket['creator'] or isPlayerScripter($_SESSION['groups'])) {
            return true;
        }

        $subs = explode(",", $ticket['subcribers']);
        foreach ($subs as $sub) {
            if ($sub == $userid) {
                return true;
            }
        }
        if ($ticket['private'] == 0)
            return canUserAccessTcBackEnd($_SESSION['groups']);
        else {
            return isPlayerScripter($_SESSION['groups']);
        }
    }
    return false;
}

function addTicketComment($connection = false, $tcid, $userid, $comment, $internal = 0, $ticket = false, $systemNote = false) {
    //echo "userid - $userid";
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }
    $result = false;
    if (!$ticket) {
        $ticket = $db->query_first("SELECT creator, assign_to, subcribers, status FROM tc_tickets WHERE status!=-1 AND id=" . $tcid);
    }
    if ($ticket['status'] != -1) { // not locked
        if ($internal == 0 and ! $systemNote) {
            if ($ticket['status'] == 0) { // open
                if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                    $ticket['assign_to'] = $userid;
                    assignTicket($db, $tcid, $userid, $ticket);
                    $db->query("UPDATE tc_tickets SET status=2 WHERE id=" . $tcid); //Answered
                } else {
                    //$db->query("UPDATE tc_tickets SET status=3 WHERE id=".$tcid); //Responded
                }
            } else if ($ticket['status'] == 1) { //assigned
                if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                    $db->query("UPDATE tc_tickets SET status=2 WHERE id=" . $tcid); //Answered
                } else {
                    $db->query("UPDATE tc_tickets SET status=3 WHERE id=" . $tcid); //Responded
                }
            } else if ($ticket['status'] == 2) { //Answered
                if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                    //$db->query("UPDATE tc_tickets SET status=3 WHERE id=".$tcid);
                } else {
                    $db->query("UPDATE tc_tickets SET status=3 WHERE id=" . $tcid); //Responded
                }
            } else if ($ticket['status'] == 3) { //Responded
                if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                    $db->query("UPDATE tc_tickets SET status=2 WHERE id=" . $tcid); //Answered
                } else {
                    //$db->query("UPDATE tc_tickets SET status=3 WHERE id=".$tcid); //Responded
                }
            } else if ($ticket['status'] == 4) { //Closed
                if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                    $db->query("UPDATE tc_tickets SET status=2 WHERE id=" . $tcid); //Answered
                } else {
                    $db->query("UPDATE tc_tickets SET status=3 WHERE id=" . $tcid); //Responded
                }
            }
        }
        $ins = array();
        $ins['poster'] = $userid;
        $ins['tcid'] = $tcid;
        $ins['comment'] = $comment;
        $ins['internal'] = $internal;
        $result = $db->query_insert("tc_comments", $ins);
        $db->query("UPDATE tc_tickets SET last_updated=NOW() WHERE id=" . $tcid);
        if ($result and $internal == 0) {

            $people = getInvolvedUsersToTicket($db, $tcid, $ticket);
            $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $protocol . '://' . $host;
            if ($userid != $people[0]['id']) {
                $emailContent = "Hello " . $people[0]['username'] . "!

Your ticket #" . $tcid . " has been answered on Support Center.
Brief content: '" . strip_tags($comment) . "'.

Follow this link to view details about your ticket:
" . $currentUrl . "/support.php?tcid=" . $tcid . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";

                notify($db, $people[0]['id'], $people[0]['email'], "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent, 'support_center');
            }
            if ($userid != $people[1]['id']) {
                $emailContent = "Hello " . $people[1]['username'] . "!

Your assigned ticket #" . $tcid . " has been responded on Support Center.
Brief content: '" . strip_tags($comment) . "'.

Follow this link to view details about the ticket:
" . $currentUrl . "/support.php?tcid=" . $tcid . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                notify($db, $people[1]['id'], $people[1]['email'], "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent, 'support_center');
            }
            foreach ($people[2] as $sub) {
                if ($userid != $sub['id']) {
                    $emailContent = "Hello " . $sub['username'] . "!

You received this because we think you are involved in an incident which requires your present to resolve.
So here by we would like to inform you that someone has replied on the ticket #" . $tcid . " on Support Center.
Brief content: '" . strip_tags($comment) . "'.

Follow this link to view details about the ticket:
" . $currentUrl . "/support.php?tcid=" . $tcid . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                    notify($db, $sub['id'], $sub['email'], "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent, 'support_center');
                }
            }
        }
    }
    if (!$connection)
        $db->close();
    return $result;
}

function assignTicket($connection = false, $tcid, $assignto, $ticket = false) {
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }
    $status = 1;
    if ($assignto == 0) {
        $status = 0;
    }
    $result = $db->query("UPDATE tc_tickets SET assign_to=" . $assignto . ", status=$status WHERE id=" . $tcid);
    if ($result) {
        if (!$ticket) {
            $ticket = $db->query_first("SELECT creator, assign_to, subcribers, status FROM tc_tickets WHERE id=" . $tcid);
        }
        $ticket['assign_to'] = $assignto;
        $people = getInvolvedUsersToTicket($db, $tcid, $ticket);

        $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $currentUrl = $protocol . '://' . $host;
        $emailContent = "Hello " . $people[0]['username'] . "!

Your ticket #" . $tcid . " has been assigned to " . $people[1]['username'] . " on Support Center.

Follow this link to view details about your ticket:
" . $currentUrl . "/support.php?tcid=" . $tcid . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";

        notify($db, $people[0]['id'], $people[0]['email'], "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent, 'support_center');
        //@mail("ducchu@live.com", "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent);
        $emailContent = "Hello " . $people[1]['username'] . "!

Ticket #" . $tcid . " has been assigned to you on Support Center.

Follow this link to view details about the ticket:
" . $currentUrl . "/support.php?tcid=" . $tcid . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
        notify($db, $people[1]['id'], $people[1]['email'], "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent, 'support_center');
        //@mail("ducchu@live.com", "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent);
        foreach ($people[2] as $sub) {
            $emailContent = "Hello " . $sub['username'] . "!

You received this email because we think you are involved in an incident which requires your present to resolve.
So here by we would like to inform you that the ticket #" . $tcid . " has been assigned to " . $people[1]['username'] . " on Support Center.

Follow this link to view details about the ticket:
" . $currentUrl . "/support.php?tcid=" . $tcid . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
            notify($db, $sub['id'], $sub['email'], "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent, 'support_center');
            //@mail("ducchu@live.com", "OwlGaming MTA Roleplay - Support Center - Ticket #" . $tcid . " Status Updated!", $emailContent);
        }
    }
    if (!$connection)
        $db->close();
    return $result;
}

function getInvolvedUsersToTicket($connection = false, $tcid, $ticket = false) {
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }
    $creator = false;
    $assignee = false;
    if (!$ticket) {
        $ticket = $db->query_first("SELECT creator, assign_to, subcribers, status FROM tc_tickets WHERE id=" . $tcid);
    }
    if ($ticket['creator'] > 0) {
        if (is_numeric($ticket['creator'])) {
            $creator = $db->query_first("SELECT id, username, email FROM accounts WHERE id=" . $ticket['creator']);
        } else {
            $creator = array();
            $creator['username'] = 'Someone';
            $creator['email'] = $ticket['creator'];
        }
    }

    if ($ticket['assign_to'] > 0) {
        if (is_numeric($ticket['assign_to'])) {
            $assignee = $db->query_first("SELECT id, username, email FROM accounts WHERE id=" . $ticket['assign_to']);
        } else {
            $assignee = array();
            $assignee['username'] = 'Someone';
            $assignee['email'] = $ticket['assign_to'];
        }
    }
    $subs = explode(",", $ticket['subcribers']);
    $tail = ' 1=2 ';
    foreach ($subs as $sub) {
        if ($sub and is_numeric($sub) and $sub > 0) {
            $tail .= " OR id=" . $sub . " ";
        }
    }
    $subcribers = $db->fetch_all_array("SELECT id, username, email FROM accounts WHERE " . $tail);
    if (!$connection)
        $db->close();
    return [$creator, $assignee, $subcribers];
}

function getLaziestStaff($connection = false, $type = 0, $preferredStaff = 0) {
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }
    $laziestStaff = false;
    if ($preferredStaff and is_numeric($preferredStaff) and $preferredStaff > 0) {
        $laziestStaff = $db->query_first("SELECT id, username, email, admin, supporter, vct, scripter, mapper FROM accounts WHERE id=" . $preferredStaff . " AND (admin>0 OR supporter>0 OR scripter>0) LIMIT 1");
    }
    if (!$laziestStaff or ! $laziestStaff['id'] or ! is_numeric($laziestStaff['id'])) {
        $condition = " WHERE (t.status IS NULL OR (t.status!=-1 AND t.status!=4)) AND NOT(l.effective=1 and l.`from` IS NOT NULL AND l.`to` IS NOT NULL AND l.`from`<=NOW() AND l.`to`>=NOW() AND l.loa_id IS NOT NULL) AND ";
        if ($type == 1) // account
            $condition .= " (admin>0 OR scripter>2) "; //admins and scripters.
        else if ($type == 2) // Unban
            $condition .= " admin>0 ";
        else if ($type == 3) // Refund
            $condition .= " admin>0 ";
        else if ($type == 4) // Donation
            $condition .= " (admin>0 OR (scripter>2)) "; // admins and scripters
        else if ($type == 5) // General questions
            $condition .= " supporter>0 "; // supporter
        else if ($type == 6) // Bug reports
            $condition .= " scripter>2 "; // scripters
        else if ($type == 7) // Player reports
            $condition .= " admin>0 "; // supporter or admin
        else if ($type == 8) // history appeals
            $condition .= " (supporter>0 OR admin>0) "; // supporter or admin
        else if ($type == 9) // staff report
            $condition .= " (1=0) "; // No-one, suppose to be not auto-assigning.




            /* die("SELECT a.id, username, count(t.id) AS ticket_count, email, admin, supporter, vct, scripter, mapper "
              . "FROM accounts a LEFT JOIN tc_tickets t ON a.id=t.assign_to "
              . "LEFT JOIN account_loa l ON a.id=l.user_id "
              . $condition . " GROUP BY a.id ORDER BY ticket_count, RAND() LIMIT 1");
             *
             */
        $laziestStaff = $db->query_first("SELECT a.id, username, count(t.id) AS ticket_count, email, admin, supporter, vct, scripter, mapper "
                . "FROM accounts a LEFT JOIN tc_tickets t ON a.id=t.assign_to "
                . "LEFT JOIN account_loa l ON a.id=l.user_id "
                . $condition . " GROUP BY a.id ORDER BY ticket_count, RAND() LIMIT 1");
    }
    if (!$connection)
        $db->close();
    if ($laziestStaff and $laziestStaff['id'] and is_numeric($laziestStaff['id'])) {
        return $laziestStaff;
    } else
        return false;
}

function getAllStaffs($connection = false, $ignore_loa = false) {
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database("MTA");
        $db->connect();
    }
    $staffs = $db->fetch_all_array("SELECT DISTINCT a.id, username, admin, supporter, vct, scripter, mapper FROM accounts a " . ($ignore_loa ? "" : "LEFT JOIN account_loa l ON a.id=l.user_id") . " WHERE (admin >0 OR supporter>0 OR scripter>0 OR vct>0 OR mapper>0) " . ($ignore_loa ? "" : "AND NOT (l.`from` IS NOT NULL AND l.`to` IS NOT NULL AND l.`from`<=NOW() AND l.`to`>=NOW() AND l.effective=1)") . " ORDER BY admin DESC, supporter DESC, scripter DESC, vct DESC, mapper DESC, id");
    if (!$connection)
        $db->close();
    return $staffs;
}

function notify($connection = false, $userid, $email = false, $subject, $content = '', $type = 'other', $format = "Content-Type: text/plain; charset=utf-8\r\n") {
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    require_once $root . '/config.inc.php';
    if ($email) {
        $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
        $header.= "MIME-Version: 1.0\r\n";
        $header.= $format;
        $header.= "X-Priority: 1\r\n";
        mail($email, $subject, $content, $header);
    }
    require_once("$root/classes/Mta.class.php");
    $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
    $serverOnline = $mtaServer->getResource("usercontrolpanel")->call("isServerOnline");
    if ($serverOnline and $serverOnline[0] == 1) {
        $mtaServer->getResource("announcement")->call("makePlayerNotification", $userid, $subject, strip_tags(html_entity_decode($content)), $type);
    }
    /*
      $db = $conn;
      if (!$db) {
      $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);        require_once $root.'/classes/Database.class.php';
      $db = new Database($database);
      $db->connect();
      }
      if (!$db)
      $db->close();
     */
}
