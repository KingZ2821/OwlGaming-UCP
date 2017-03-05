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

$userID = $_SESSION['userid'];

if (!isset($userID) or ! $userID) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    require_once("../functions/functions.php");
    require_once("../classes/Database.class.php");
    $transferCost = 750;
    $db = new Database("MTA");
    $db->connect();
    $userRow = $db->query_first("SELECT `username`,`credits` FROM `accounts` WHERE id='" . $userID . "' LIMIT 1");
    $username = $userRow['username'];
    $gameCoins = $userRow['credits'];
    $transfers = floor($gameCoins / $transferCost);
    $charArr = array();
    $mQuery2 = $db->query("SELECT `id`,`charactername` FROM `characters` WHERE `account`='" . $userID . "' ORDER BY `charactername` ASC");
    while ($characterRow = $db->fetch_array($mQuery2)) {
        $charArr[$characterRow['id']] = $characterRow['charactername'];
    }
    $db->close();
    ?>
    <h2>Transfer assets between characters</h2>
    <div id="stat_transfer_gc_vs_times">You are currently having <b><?php echo number_format($gameCoins); ?> GCs</b> so that you will be able to do <b><?php echo number_format($transfers); ?> transfer(s).</b></div>
    You can get more GC by <a href="/donate.php" target=new>donating to our server</a>.<br><br>
    It costs <b><?php echo number_format($transferCost); ?> GCs for each time transferring</b> some or all assets(money, interiors, vehicles,...) from a character to an alternate character of yours. <BR /><BR />

    <table width="100%" border="0" class=nicetable>
        <tr>
            <td colspan="3" cellspacing="0" cellpadding="0" align="center">
                <div id="validateText">Please select the source and destination character for the transfer below.</div>
            </td>
        </tr>
        <tr>
            <td align="center" width="45%">
                From: 
                <select name="fromcharacter" id="fromcharacter" onchange="sFromCharChange()">
                    <option value="0">Select a character</option>
                    <?php
                    foreach ($charArr as $characterID => $characterName) {
                        echo"<option value=\"" . $characterID . "\">" . str_replace("_", " ", $characterName) . "</option>\r\n";
                    }
                    ?>												</select>
            </td>
            <td align="center" width="10%" rowspan="2">
                <img id="transfer_icon" src="/images/icons/transfer_icon_inactive.png" width="80%" onmouseover="mouseOverTransferIcon();" onmouseout="mouseOutTransferIcon();" onmousedown="mouseDownTransferIcon();" onmouseup="mouseUpTransferIcon();" onclick="mouseClickTransferIcon();"/>
            </td>
            <td align="center" width="45%">
                To:
                <select name="tocharacter" id="tocharacter" onchange="sToCharChange()">
                    <option value="0">Select a character</option>
                    <?php
                    foreach ($charArr as $characterID => $characterName) {
                        echo"													<option value=\"" . $characterID . "\">" . str_replace("_", " ", $characterName) . "</option>\r\n";
                    }
                    ?>												</select>
            </td>
        </tr>
        <tr>
            <td align="center">
                <div id="fromCharPreview"><input type="hidden" id="selectedFromCharId" value="0"/></div>
            </td>

            <td align="center">
                <div id="toCharPreview"><input type="hidden" id="selectedToCharId" value="0"/></div>
            </td>
        </tr>
        <tr>
            <td colspan="3" align="center">
                <div id="source_char_assets"></div>
            </td>
        </tr>
    </table>
    <?php
}

