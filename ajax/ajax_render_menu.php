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
?>
<nav id="primary_nav_wrap">
    <ul>
        <li class="current-menu-item"><a class="hover" href="index.php" target="_self">Home</a></li>
        <li class="current-menu-item"><a class="hover" href="http://forums.owlgaming.net" target="_blank">Forums</a>
            <ul>
                <li><a href="http://forums.owlgaming.net" target="_blank">Community Forums</a></li>
                <li><a href="http://findbook.owlgaming.net" target="_blank">Findbook Social Network</a></li>
                <li><a href="http://gov.owlgaming.net" target="_blank">Government of Los Santos</a></li>
                <li><a href="http://pd.owlgaming.net" target="_blank">Los Santos Police Department</a></li>
                <li><a href="http://fd.owlgaming.net" target="_blank">Los Santos Fire Department</a></li>
                <li><a href="http://hp.owlgaming.net" target="_blank">San Andreas Highway Patrol</a></li>
                <li><a href="http://bank.owlgaming.net" target="_blank">Bank of Los Santos</a></li>
                <li><a href="http://usmcr.owlgaming.net" target="_blank">US Marine Corps Reserve</a></li>
                <li><a href="http://san.owlgaming.net" target="_blank">San Andreas Networks</a></li>
                <li><a href="http://rt.owlgaming.net" target="_blank">Rapid Towing</a></li>
                <li><a href="http://whetstonenp.owlgaming.net" target="_blank">Whetstone National Park</a></li>
                <li><a href="http://email.owlgaming.net" target="_blank">FindMail Service</a></li>
            </ul>
        </li>
        <li class="current-menu-item"><a class="hover" href="refer-a-friend.php" target="_self">Refer friends</a></li>
        <?php if (isset($_SESSION['username'])) { ?>
            <li class="current-menu-item"><a class="hover" href="ucp.php" target="_self">UCP</a>
                <ul>    
                    <li><a href="ucp.php" target="_self">Manage Characters</a></li>
                    <li><a href="ucp.php?action=transfer" target="_self">Stats Transfer</a></li>
                    <li><a href="#" target="_self" onclick="alert('1. Go to UCP -> Manage Characters.\n2. Click on the character that owns the interior or the character who is leader of the faction that the interior belongs to.\n3. Click Upload/Change Custom Interior.');
                                return false;">Upload Custom Interior</a></li>
                    <li><a href="ucp.php?action=settings" target="_self">Account Settings</a></li>
                </ul>
            </li>
            <?php
        }
        $groups = isset($_SESSION['groups'])? $_SESSION['groups'] : false;
        if ($groups) {
            require_once '../functions/functions.php';
            if (isPlayerSupporter($groups) or isPlayerTrialAdmin($groups) or isPlayerVCT($groups) or isPlayerScripter($groups) or isPlayerMappingTeamMember($groups)) {
                echo '<li class="current-menu-item"><a class="hover" href="#" target="_self">SysTools</a>';
                echo '<ul>';
                require_once("../functions/functions_logs.php");
                if (canUserAccessLogs($groups)) {
                    echo '<li><a href="logs.php" target="_self">Server Logger</a></li>';
                }
                require_once '../classes/Ban.class.php';
                $ban = new Ban();
                if ($ban->canAccess(false, true)) {
                    echo '<li><a href="ban.php" target="_self">Ban Manager</a></li>';
                }
                echo '<li><a href="performance.php" target="_self">Staff Performance</a></li>';
                echo '<li><a href="loa.php" target="_self">Leave of Absence</a></li>';
                echo '</ul>';
                echo '</li>';
            }
        }
        ?>
        <li class="current-menu-item"><a class="hover" href="library.php" target="_self">Library</a></li>
        <li class="current-menu-item"><a class="hover" href="stats.php" target="_self">Statistics</a></li>
        <li class="current-menu-item"><a class="hover" href="support.php" target="_self">Support</a>
            <ul>
                <li><a href="support.php" target="_self">Support Center</a></li>
                <li><a href="lostpw.php" target="_self">Password Recovery</a></li>
                <li><a href="activate.php" target="_self">Account Activation</a></li>
            </ul>
        </li>
        <li class="current-menu-item"><a class="hover" href="premium.php" target="_self">Premium Features</a>
            <ul>
                <li><a href="premium.php" target="_self">Purchasable Perks</a></li>
                <li><a href="donate.php" target="_self">Pay by PayPal</a></li>
                <li><a href="bitcoin.php" target="_self">Pay by Bitcoin</a></li>
                <li><a href="mobile-payments.php" target="_self">Pay by Mobile</a></li>
            </ul>
        </li>
        <?php if (!isset($_SESSION['username']) or ! $_SESSION['username']) {
            ?>
            <li class="current-menu-item"><a class="hover" href="register.php" target="_self">Register</a></li>
            <?php } ?>
    </ul>
</nav>