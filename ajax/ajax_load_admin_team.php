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
            require_once '../classes/Database.class.php';
            require_once '../functions/functions.php';
            $db = new Database("MTA");
            $db->connect();
            $query = $db->query("SELECT id, username, admin, supporter, vct, scripter, mapper FROM accounts WHERE admin > 0 OR supporter > 0 OR vct > 0 OR scripter > 0 OR mapper > 0 ORDER BY admin DESC, supporter DESC, vct DESC, scripter DESC, mapper DESC, id");
            $count = 1;
            echo '<table id="logtable" border="1" align=center width="100%">'
            . '<td align=center width=20%><b>Username</b></td><td align=center ><b>Current Positions</b></td><td align=center><b>Actions</b></td></tr>';
            while ($admin = $db->fetch_array($query)) { 
                echo "<tr><td>".$admin['username']."</td><td>".  getAllStaffTitlesFromIndexes($admin['admin'], $admin['supporter'], $admin['vct'], $admin['scripter'], $admin['mapper'])."</td><td width=10%><input type='button' value='View/Edit' style='margin-left: 0px; margin-top: 0px; width: 100%;' onclick='load_account(".$admin['id'].");'/></td></tr>";
                $count+=1;
            }
            echo '</table>';
            $db->free_result();
            $db->close();
        ?>

        <?php
        }
    }
}