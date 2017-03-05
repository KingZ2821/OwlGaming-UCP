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

// if (!isset($_SESSION['captcha']) or ! isset($_POST['captcha']) or strtolower($_SESSION['captcha']) != strtolower($_POST['captcha'])) {
//     echo "*Captcha is not correct.";
if(false) {

} else {
    require_once "$root/classes/Database.class.php";
    $dbf = new Database("MTA");
    $dbf->connect();
    $checkUsername = $dbf->query_first("SELECT id, username, activated, email FROM accounts WHERE username = '" . $dbf->escape($_POST['username']) . "' ");
    if ($checkUsername and $checkUsername['username'] and strlen($checkUsername['username']) > 0) {
        if ($checkUsername['activated'] == 0) {
            $dbf->query("DELETE FROM tokens WHERE userid='".$checkUsername['id']."' ");
            $token = md5(uniqid(mt_rand(), true));
            $data = array();
            $data['userid'] = $checkUsername['id'];
            $data['token'] = $token;
            $dbf->query("INSERT into tokens (id, userid, action, token, date) VALUES ( DEFAULT, " . $data['userid'] . ", 'Activate Account', '" . $data['token'] . "', NOW());" );
            $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $protocol . '://' . $host;
            $emailContent = "Your OwlGaming MTA account for '" . $checkUsername['username'] . "' is almost ready for action!

Follow this link to activate your MTA account:
" . $currentUrl . "/activate.php?userid=" . $data['userid'] . "&token=" . $data['token'] . "

Sincerely,
OwlGaming Community
OwlGaming Development Team";
            $header = "From: ".EMAIL_DEFAULT_FROM_NAME." <".EMAIL_DEFAULT_FROM_ADDRESS.">\r\n"; 
            $header.= "MIME-Version: 1.0\r\n"; 
            $header.= "Content-Type: text/plain; charset=utf-8\r\n"; 
            $header.= "X-Priority: 1\r\n"; 
            mail($checkUsername['email'], "Account Activation at OwlGaming MTA Roleplay", $emailContent, $header);
            session_destroy();
            $dbf->close();
            echo "ok";
        } else {
            echo "*Username '" . $_POST['username'] . "' is already activated.";
        }
    } else {
        echo "*Username '" . $_POST['username'] . "' does not exist.";
    }
}

    