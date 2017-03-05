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

require_once("$root/functions/functions.php");
if (!isset($_SESSION['userid']) or ! $_SESSION['userid'] and false) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {

    require_once("./functions/functions.php");
    $logTypes = array(
        '1' => array('Chat /h', isPlayerLeadAdmin($perms), false),
        '2' => array('Chat /l', isPlayerLeadAdmin($perms), false),
        '3' => array('Chat /a', isPlayerTrialAdmin($perms), false),
        '42' => array('Chat /st', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '4' => array('Admin commands', isPlayerTrialAdmin($perms), false),
        '38' => array('Admin Reports', isPlayerTrialAdmin($perms), true),
        '5' => array('Anticheat warnings', isPlayerTrialAdmin($perms), false),
        '6' => array('Vehicle related actions', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '37' => array('Interior related things', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), true),
        '7' => array('Player /say', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '8' => array('Player /b', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '9' => array('Player /r', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '10' => array('Player /d', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '11' => array('Player /f', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '12' => array('Player /me', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '40' => array('Player /ame', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '13' => array('Player /district', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '14' => array('Player /do', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '15' => array('Player /pm', isPlayerTrialAdmin($perms), false),
        '16' => array('Player /gov**', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), true),
        '17' => array('Player /don', isPlayerLeadAdmin($perms), false),
        '18' => array('Player /o', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '19' => array('Player /s', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '20' => array('Player /m', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '21' => array('Player /w', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '22' => array('Player /c', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '23' => array('Player /n**', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), true),
        '24' => array('Gamemaster chat', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '25' => array('Cash transfer', isPlayerTrialAdmin($perms), false),
        '27' => array('Connection/Charselect', isPlayerTrialAdmin($perms), false),
        '28' => array('Roadblock & Spikes**', isPlayerTrialAdmin($perms), true),
        '29' => array('Phone logs', isPlayerTrialAdmin($perms), false),
        '30' => array('SMS logs', isPlayerTrialAdmin($perms), false),
        '31' => array('veh/int locking/unlocking', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), false),
        '32' => array('UCP Logs', isPlayerLeadAdmin($perms), false),
        '33' => array('Stattransfers', isPlayerTrialAdmin($perms), false),
        '34' => array('Kill logs/Lost items', isPlayerTrialAdmin($perms), false),
        '35' => array('Faction actions', isPlayerTrialAdmin($perms), true),
        '36' => array('Ammunation logs', isPlayerTrialAdmin($perms), true),
        '39' => array('Item Movement', (isPlayerTrialAdmin($perms) || isPlayerSupporterManager($perms)), true),
    );

    $characterCache = array();

    function nameCache($id) {
        global $characterCache, $mySqlMTAConn;
        if (isset($characterCache[$id]))
            return $characterCache[$id];

        $pos = strpos($id, "ch");
        if ($pos === false) {
            $pos = strpos($id, "fa");
            if ($pos === false) {
                $pos = strpos($id, "ve");
                if ($pos === false) {
                    $pos = strpos($id, "ac");
                    if ($pos === false) {
                        $pos = strpos($id, "in");
                        if ($pos === false) {
                            $pos = strpos($id, "ph");
                            if ($pos === false) {
                                $characterCache[$id] = $id . '[unrec]';
                                return $id;
                            } else {
                                $tempid = substr($id, 2);
                                $characterCache[$id] = "phone " . $tempid;
                                return $id;
                            }
                        } else {
                            $tempid = substr($id, 2);
                            $characterCache[$id] = "interior " . $tempid;
                            return $id;
                        }
                    } else {
                        $tempid = substr($id, 2);
                        $awsQry = mysql_query("SELECT `username` FROM `accounts` WHERE `id`='" . $tempid . "'", $mySqlMTAConn);
                        if (mysql_num_rows($awsQry) == 1) {
                            $awsRow = mysql_fetch_assoc($awsQry);
                            $characterCache[$id] = $awsRow['username'];
                            return $awsRow['username'];
                        } else {
                            $characterCache[$id] = $id;
                            return $id;
                        }
                    }
                } else {
                    $tempid = substr($id, 2);
                    $characterCache[$id] = "vehicle " . $tempid;
                    return $characterCache[$id];
                }
            } else {
                $tempid = substr($id, 2);
                $awsQry = mysql_query("SELECT `name` FROM `factions` WHERE `id`='" . $tempid . "'", $mySqlMTAConn);
                if (mysql_num_rows($awsQry) == 1) {
                    $awsRow = mysql_fetch_assoc($awsQry);
                    $characterCache[$id] = '[F]' . $awsRow['name'];
                    return $awsRow['name'];
                } else {
                    $characterCache[$id] = $id;
                    return $id;
                }
            }
        } else {
            $tempid = substr($id, 2);
            $awsQry = mysql_query("SELECT `charactername` FROM `characters` WHERE `id`='" . $tempid . "'", $mySqlMTAConn);
            if (mysql_num_rows($awsQry) == 1) {
                $awsRow = mysql_fetch_assoc($awsQry);
                $characterCache[$id] = $awsRow['charactername'];
                return $awsRow['charactername'];
            } else {
                $characterCache[$id] = $id . '[' . $tempid . ']';
                return $id;
            }
        }
    }

    $selectarr = array();
}