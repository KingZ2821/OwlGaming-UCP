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

function takeGC($connection, $userid, $amount, $reason) {
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }
    $credits = $db->query_first("SELECT credits FROM accounts WHERE id=" . $userid);
    $credits = $credits['credits'];
    $return = null;
    if ($credits >= $amount) {
        if ($db->query("UPDATE accounts SET credits=credits-" . $amount . " WHERE id=" . $userid)) {
            $return = [true];
            $insert = array();
            $insert['name'] = $reason;
            $insert['cost'] = -$amount;
            $insert['account'] = $userid;
            $db->query_insert("don_purchases", $insert);
        } else {
            $return = [false, "Internal Error!"];
        }
    } else {
        $return = [false, "You lack of GC(s) to purchase this item."];
    }
    if (!$connection) {
        $db->close();
    }
    return $return;
}

function makeToken($connection, $userid, $action = false, $data = false) {
    if ($userid and is_numeric($userid)) {
        $db = $connection;
        if (!$db) {
            $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
            require_once $root . '/classes/Database.class.php';
            $db = new Database($database);
            $db->connect();
        }
        $tail = "";
        if ($action) {
            $tail.= " AND action='" . $action . "'";
        }
        $db->query("DELETE FROM tokens WHERE userid='" . $userid . "'" . $tail);
        $token = md5(uniqid(mt_rand(), true));
        $insert = array();
        $insert['userid'] = $userid;
        $insert['token'] = $token;
        if ($data) {
            $insert['data'] = $data;
        }
        if ($action) {
            $insert['action'] = $action;
        }
        if ($db->query_insert("tokens", $insert)) {
            $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $protocol . '://' . $host;
            return [$token, $currentUrl];
        }
        if (!$connection) {
            $db->close();
        }
    }
}

function sendActivationEmail($userid, $username, $token, $email) {
    $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
    $host = $_SERVER['HTTP_HOST'];
    $currentUrl = $protocol . '://' . $host;
    $emailContent = "Your OwlGaming MTA account for '" . $username . "' is almost ready for action!

Follow this link to activate your MTA account:
" . $currentUrl . "/activate.php?userid=" . $userid . "&token=" . $token . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    require_once $root.'/config.inc.php';
    $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
    $header.= "MIME-Version: 1.0\r\n";
    $header.= "Content-Type: text/plain; charset=utf-8\r\n";
    $header.= "X-Priority: 1\r\n";
    return mail($email, "Account Activation at OwlGaming MTA Roleplay", $emailContent, $header);
}

function makeAdminHistory($connection, $userid, $reason, $action = 6, $duration = 0, $user_char = 0, $admin = 0) {
    $db = $connection;
    if (!$db) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database($database);
        $db->connect();
    }
    $data12 = array();
    $data12['user'] = $userid;
    $data12['user_char'] = $user_char;
    $data12['admin'] = $admin;
    $data12['action'] = $action;
    $data12['duration'] = $duration;
    $data12['reason'] = $reason;
    $result = $db->query_insert("adminhistory", $data12);

    if (!$connection) {
        $db->close();
    }
    return $result;
}
