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

include("header.php");
?>
<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="main-wrapper">
    <div id="lib_top">
        <h2>Account Email Change Request</h2>
        <?php
        if (isset($_GET['userid']) and isset($_GET['token'])) {
            require_once './classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $db->escape($_GET['userid']) . "' ");
            if ($user and $user['id'] and is_numeric($user['id'])) {
                $tokenCheck = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_GET['userid']) . "' AND token='" . $db->escape($_GET['token']) . "' AND date >= NOW() - INTERVAL 10 MINUTE");
                if ($tokenCheck and $tokenCheck['userid'] and is_numeric($tokenCheck['userid'])) {
                    if ($tokenCheck['action'] == "change_email_step_1") {
                        $db->query("DELETE FROM tokens WHERE userid='" . $tokenCheck['userid'] . "' ");
                        $token = md5(uniqid(mt_rand(), true));
                        $insert = array();
                        $insert['userid'] = $tokenCheck['userid'];
                        $insert['token'] = $token;
                        $insert['data'] = $tokenCheck['data'];
                        $insert['action'] = "change_email_step_2";
                        $db->query_insert("tokens", $insert);
                        $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
                        $host = $_SERVER['HTTP_HOST'];
                        $currentUrl = $protocol . '://' . $host;
                        $emailContent = "You or someone has request an account email change from '" . $user['email'] . "' to '" . $tokenCheck['data'] . "' from the OwlGaming UCP. 

    Please click this link within 10 minutes to proceed to next step:
    " . $currentUrl . "/changemail.php?userid=" . $tokenCheck['userid'] . "&token=" . $token . "

    If you're the one who performed this action, just simply ignore this notice.

    Sincerely,
    OwlGaming Community
    OwlGaming Development Team";
                        $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                        $header.= "MIME-Version: 1.0\r\n";
                        $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                        $header.= "X-Priority: 1\r\n";
                        mail($tokenCheck['data'], "Account Email Change Request at OwlGaming MTA Roleplay", $emailContent, $header);
                        echo "<p>We have received a valid confirmation from your current email '" . $user['email'] . "'. "
                        . "<p>Now we need another confirmation from your new email so an other email has been dispatched to your new email address '" . $tokenCheck['data'] . "'.</p>"
                        . "<p>Please check your email's inbox for further instructions.</p>";
                    } else if ($tokenCheck['action'] == "change_email_step_2") {
                        $update = array();
                        $update['email'] = $tokenCheck['data'];
                        if ($db->query_update("accounts", $update, "id='" . $tokenCheck['userid'] . "'") and $db->query("DELETE FROM tokens WHERE userid='" . $tokenCheck['userid'] . "' ")) {
                            echo "<p>Congratulations! You have successfully changed your MTA account's email address from '" . $user['email'] . "' to '" . $tokenCheck['data'] . "'!</p>";
                        } else {
                            echo "<p>Opps, sorry. We couldn't continue to process the email change request.</p> "
                            . "<p>Please try again later.</p>";
                        }
                    } else {
                        echo "<p>Opps, sorry. We couldn't continue to process the email change request.</p> "
                        . "<p>It looked like this link is expired or invalid.</p>";
                    }
                } else {
                    echo "<p>Opps, sorry. We couldn't continue to process the email change request for your account '" . $user['username'] . "'.</p> "
                    . "<p>It looked like this link is expired or invalid.</p>";
                }
            } else {
                echo "<p>Opps, sorry. Account does not exist.</p>";
            }
            $db->close();
        } else {
            echo "<p>Opps, sorry. We couldn't continue to process the email change request.</p> "
            . "<p>It looked like this link is expired or invalid.</p>";
        }
        ?>
    </div>
    <div id="lib_mid" ></div>
    <div id="lib_bot"></div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        
