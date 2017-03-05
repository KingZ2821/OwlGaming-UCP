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

require_once './config.inc.php';
$remote_hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
if (!in_array($remote_hostname, array(SDK_IP, 'localhost', '127.0.0.1', 'WIN-7Q43DQE7LL6'))) {
    header("HTTP/1.0 403 Forbidden");
    mail(WEBMASTER_EMAIL, 'Postback MTA - Error: Unknown IP', "Error: Unknown IP\n$remote_hostname", "From: system@owlgaming.net");
    die("Error: Unknown IP\n$remote_hostname");
}

$action = isset($_GET['action']) ? $_GET['action'] : null;
if ($action == "account_activation") {
    $data = isset($_GET['data']) ? $_GET['data'] : null;
    if ($data and is_numeric($data)) {
        require_once './classes/Database.class.php';
        $dbf = new Database("MTA");
        $dbf->connect();
        $checkUsername = $dbf->query_first("SELECT id, username, activated, email FROM accounts WHERE id = '" . $dbf->escape($data) . "' ");
        if ($checkUsername and $checkUsername['username'] and strlen($checkUsername['username']) > 0) {
            if (true or $checkUsername['activated'] == 0) {
                $dbf->query("DELETE FROM tokens WHERE userid='" . $checkUsername['id'] . "' ");
                $token = md5(uniqid(mt_rand(), true));
                $data = array();
                $data['userid'] = $checkUsername['id'];
                $data['token'] = $token;
                $dbf->query_insert("tokens", $data);
                $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
                $host = $_SERVER['HTTP_HOST'];
                $currentUrl = $protocol . '://' . $host;
                $emailContent = "Your OwlGaming MTA account for '" . $checkUsername['username'] . "' is almost ready for action!

Follow this link to activate your MTA account:
" . $currentUrl . "/activate.php?userid=" . $data['userid'] . "&token=" . $data['token'] . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
                $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                $header.= "MIME-Version: 1.0\r\n";
                $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                $header.= "X-Priority: 1\r\n";
                mail($checkUsername['email'], "Account Activation at OwlGaming MTA Roleplay", $emailContent, $header);
                $dbf->close();
                echo "ok";
            } else {
                echo "*Username '" . $_POST['username'] . "' is already activated.";
            }
        }
    }
} else if ($action == 'execute_token') {
    $id = isset($_GET['data']) ? $_GET['data'] : null;
    $mta_token = isset($_GET['token']) ? $_GET['token'] : null;
    if ($id and $mta_token) {
        require_once './classes/Database.class.php';
        $dbf = new Database("MTA");
        $dbf->connect();
        $token = $dbf->query_first("SELECT * FROM tokens WHERE id = '" . $dbf->escape($id) . "' AND token='" . $dbf->escape($mta_token) . "' ");
        if ($token and $dbf->affected_rows > 0) {
            if ($token['action'] == 'send_email') {
                $data = json_decode(substr($token['data'], 1, -1), true);
                $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                $header.= "MIME-Version: 1.0\r\n";
                $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                $header.= "X-Priority: 1\r\n";
                mail($data['to'], $data['subject'], $data['content'], $header);
            }
            $dbf->query("DELETE FROM tokens WHERE id='" . $dbf->escape($id) . "'");
        }
        $dbf->close();
    }
}
?>