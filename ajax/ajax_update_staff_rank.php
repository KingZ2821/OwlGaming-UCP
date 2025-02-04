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

if (!isset($_SESSION['groups'])) {
    echo "Session has timed out.";
    exit();
} else {
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid'] and false) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        $perms = $_SESSION['groups'];
        require_once("../functions/functions_player.php");
        if (!canUserManageAdminTeam($perms)) {
            die("<center><h3>You don't access to this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
        } else {
            if (isset($_POST['userid'])) {
                require_once '../classes/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $updater = $db->query_first("SELECT id, username, admin, supporter, vct, scripter, mapper FROM accounts WHERE id=" . $_SESSION['userid']);
                $data = $db->query_first("SELECT id, username, admin, supporter, vct, scripter, mapper FROM accounts WHERE id=" . $db->escape($_POST['userid']));
                if (($updater['admin'] < $data['admin']) or ($updater['admin'] < $_POST['admin'])) {
                    echo("<center><h3>You don't have sufficient permissions to perform the action on this player.</h3></center>");
                } else if ($updater['admin'] != $_SESSION['admin']) {
                    echo ("<center><h3>Please relog and try again.</h3></center>");
                    session_destroy();
                } else {
                    $update = array();
                    $update['admin'] = $_POST['admin'];
                    $update['supporter'] = $_POST['supporter'];
                    $update['vct'] = $_POST['vct'];
                    $update['scripter'] = $_POST['scripter'];
                    $update['mapper'] = $_POST['mapper'];
                    if ($db->query_update("accounts", $update, "id='" . $_POST['userid'] . "'")) {
                        echo ("<center><h3>You have successfully updated ".$data['username']."'s staff position!</h3></center>");
                    }
                } 
                $db->close();
            } else {
                die("<center><h3>Internal Error!<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
            }
            ?>

            <?php

        }
    }
}