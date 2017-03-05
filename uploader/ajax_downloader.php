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

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    die("Session has timed out.");
} else {
    if (isset($_GET['intid']) and isset($_GET['charid'])) {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        require_once $root . '/classes/Database.class.php';
        $db = new Database("MTA");
        $db->connect();

        $file = $db->query_first(" SELECT files.* 
                                    FROM files 
                                    LEFT JOIN interiors i ON files.connected_interior=i.id
                                    LEFT JOIN characters c ON c.id=i.owner
                                    LEFT JOIN accounts a ON a.id=c.account
                                    WHERE i.id=" . $db->escape($_GET['intid']) . " AND c.id=" . $db->escape($_GET['charid']) . " AND a.id=" . $db->escape($_SESSION['userid']));

        if (!$file or is_null($file['id'])) {
            $file = $db->query_first(" SELECT files.* 
                                        FROM files 
                                        LEFT JOIN interiors i ON files.connected_interior=i.id
                                        LEFT JOIN characters_faction cf ON cf.faction_id=i.faction
                                        LEFT JOIN factions f ON f.id=cf.faction_id
                                        LEFT JOIN characters c ON c.id=cf.character_id
                                        LEFT JOIN accounts a ON a.id=c.account
                                        WHERE i.id=" . $db->escape($_GET['intid']) . " AND c.id=" . $db->escape($_GET['charid']) . " AND a.id=" . $db->escape($_SESSION['userid']) . " AND cf.faction_leader=1 AND i.id IS NOT NULL AND cf.id IS NOT NULL AND f.id IS NOT NULL;");
        }

        if ($file and ! is_null($file['id'])) {
            header("Content-Type: {$file['file_type']}\n");
            require_once '../functions/base_functions.php';
            $filename = sanitize($file['desc'], false) . ".map";
            header("Content-Disposition: attachment; filename=\"{$filename}\"\n");
            header("Content-Length: {$file['file_size']}\n");
            echo $file['file'];
        } else {
            echo "Errors occurred while fetching data. Posible reasons:<br>";
            echo "* The file you requested is corrupted or has been prematurely removed!<br>";
            echo('* You lack of permissions to access this map file!<br>');
        }
        $db->close();
    } else {
        die('* Access denied!<br>');
    }
}