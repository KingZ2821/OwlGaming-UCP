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

include( "../classes/Mta.class.php" );
$input = mta::getInput();
if ($input and $input[0]) {
    require_once '../classes/Database.class.php';
    $db = new Database("MTA");
    $db->connect();
    $checkToken = $db->query_first("SELECT * FROM tokens WHERE token='" . $db->escape($input[0]) . "' AND date >= NOW() - INTERVAL 1 MINUTE");
    if (true or $token and $token['id'] and is_numeric($token['id'])) {
        if (true or $checkToken['action'] == "INGAME_ACC_REGISTRATION") {
            $userid = $input[1];
            $username = $input[2];
            $email = $input[3];
            sendActivationEmail($userid, $username, $checkToken['token'], $email);
            mta::doReturn("ok");
        }
    } else {
        mta::doReturn("Security token is invalid. Report on bugs.owlgaming.net");
    }
    $db->close();
} else {
    mta::doReturn("Internal Error.");
}

