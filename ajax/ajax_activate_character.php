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

require_once("$root/classes/Database.class.php");
$db = new Database("MTA");
$db->connect();

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    echo "Session has timed out.";
} else {
    $data['active'] = $_POST['active'];
    if (!isset($_POST['charid']) or ! isset($data['active'])) {
        if ($data['active'] == 0) {
            echo "Activated";
        } else {
            echo "Deactivated";
        }
    } else {
        $stmt = $db->query_first("SELECT account FROM characters WHERE id = " . $db->escape($_POST['charid']) . ";");
        if($stmt['account'] != $_SESSION['userid'])
        {
            die("Stop trying to exploit this.");
        }
        $db->query_update("characters", $data, "id='" . $_POST['charid'] . "'");
        if ($data['active'] == 1) {

            echo "Activated";
        } else {
            echo "Deactivated";
        }
        $db->close();
    }
}