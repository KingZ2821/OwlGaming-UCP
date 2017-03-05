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

require_once "$root/classes/Database.class.php";
$step = (isset($_POST['step']) ? $_POST['step'] : false);
$userID = (isset($_SESSION['userid']) ? $_SESSION['userid'] : false);

if ($step == "forums_groups") {
    $tierOrder = array(12 => 1, 48 => 2, 49 => 3, 50 => 4);
    $tierPrice = array(12 => 150, 48 => 750, 49 => 3000, 50 => 15000);
    $tierName = array(12 => "Bronze", 48 => "Silver", 49 => "Gold", 50 => "Diamond");

    $tier = (isset($_POST['tier']) ? $_POST['tier'] : false);
    if (!$tier) {
        ?>
        <hr>
        <form action="../ajax/ajax_perks.php" method="post" onsubmit="activate_perk('<?php echo $step; ?>');
                return false;">
            <p>Please select a tier: 
                <select id="tier" >
                    <option value="12" selected><?php echo $tierName[12].' Donator - '.$tierPrice[12].' GC(s)' ?></option>
                    <option value="48" ><?php echo $tierName[48].' Donator - '.$tierPrice[48].' GC(s)' ?></option>
                    <option value="49" ><?php echo $tierName[49].' Donator - '.$tierPrice[49].' GC(s)' ?></option>
                    <option value="50" ><?php echo $tierName[50].' Donator - '.$tierPrice[50].' GC(s)' ?></option>
                </select>
                <input id="fname" type="text" maxlength="100" minlength="3" required="true" placeholder="Enter your forums username">
                <input id="step" type="hidden"  value="<?php echo $step; ?>">
                <input id="btnActivate" type="submit" value="Activate">
            </p>
        </form>
        <?php
    } else if ($tier) {
        if (!$userID) {
            die("You must be logged in to access this content.");
        } else {
            $fname = $_POST['fname'];
            $tier = $_POST['tier'];
            $dbf = new Database("FORUMS");
            $dbf->connect();

            $fnameCheck = $dbf->query_first("SELECT member_id, name, member_group_id, mgroup_others FROM DsCdf_members WHERE name='" . $dbf->escape($fname) . "'");
            if (is_numeric($fnameCheck['member_id'])) {
                $usergroupidFound = false;
                $membergroupidFound = false;
                if (isset($tierOrder[$fnameCheck['member_group_id']])) {
                    $usergroupidFound = $fnameCheck['member_group_id'];
                }
                $groups = false;
                if (!$usergroupidFound) {
                    $groups = explode(",", $fnameCheck['mgroup_others']);
                    foreach ($groups as $group) {
                        if (isset($tierOrder[$group])) {
                            $membergroupidFound = $group;
                            break;
                        }
                    }
                }
                $existing = ($usergroupidFound or $membergroupidFound);
                if (($usergroupidFound and ( $tierOrder[$usergroupidFound] >= $tierOrder[$tier])) or ( $membergroupidFound and ( $tierOrder[$membergroupidFound] >= $tierOrder[$tier]))) {
                    $dbf->close();
                    die("Forums username '$fname' is already granted with ".($usergroupidFound ? $tierName[$usergroupidFound] : $tierName[$membergroupidFound]). " Tier.");
                } else {
                    $db = new Database("MTA");
                    $db->connect(true);
                    require_once '../functions/functions_account.php';
                    $takeGC = takeGC($db, $userID, $tierPrice[$tier], "Forums Premium Ranks - ".$tierName[$tier]." Donator");
                    if (!$takeGC[0]) {
                        $db->close();
                        $dbf->close();
                        die($takeGC[1]);
                    } else {
                        if ($usergroupidFound) {
                            $dbf->query("UPDATE DsCdf_members SET member_group_id=2 WHERE member_id=" . $fnameCheck['member_id'] . " AND member_group_id=$usergroupidFound");
                        } else if ($membergroupidFound) {
                            if (($key = array_search($membergroupidFound, $groups)) !== false) {
                                unset($groups[$key]);
                            }
                        }
                        array_push($groups, $tier);
                        $dbf->query("UPDATE DsCdf_members SET mgroup_others='" . $dbf->escape(implode(",", $groups)) . "' WHERE member_id=" . $fnameCheck['member_id']);
                        $dbf->close();
                        die('Congratulations! You have successfully activated Forums Account '.$fnameCheck['name'].'\'s Premium Ranks - '.$tierName[$tier].' Donator for '. $tierPrice[$tier]. ' GC(s)!');
                    }
                } 
            } else {
                    $dbf->close();
                    die("Forums username '$fname' does not exist.");
                }
            }
        }
    }    