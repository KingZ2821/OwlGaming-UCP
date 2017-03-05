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

$system_max_results = 200;
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
        require_once("../functions/functions_logs.php");
        if (!canUserAccessLogs($perms)) {
            die("<center><h3>You don't access to the logging system.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
        }
        ?>

        <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!"; ?> Welcome to OwlGaming Logging System!</h2>
        <p>There are only a few groups of users who have access to this page, if you're seeing this, it means you're in one of those permission groups. However, based on the permission group / staff rank you're currently in, you can only see and search for certain type of logs displaying below:</p>
        <!--
        <p>
            <b><u>Cautions:</u></b><br>
            <i>
                - To ensure an acceptable system performance and for an optimal logs querying speed, despite of your "Max Results" settings, the logging system only returns maximum <?php echo $system_max_results; ?> results per query and only returns logs from 6 months ago until present. <br>
                - Try to navigate "Start Point", "End Point" and use options/filters wisely. <br>
                - Server now writes new logs <b>in real-time</b>.<br>
                - To allow an acceptable system performance and for an optimal logs querying speed, server only keeps hold of logs 6 months ago until present.<br>
                - If you've already known how many results you're gonna need, specify it to speed up the querying speed! <br>
                
            </i>
        </p>
        -->
        
        <form name='logs_form' action='#' method='POST' onsubmit="onLogsSubmit(); return false;">
            <table id="logtable" border="1" align=center width="100%">
                <tr>
                    <td colspan="3">
                        <b>What type of logs are you searching for?</b> <div id="logtype_reminder" style="display: inline; font-size: 12px;font-style: italic;color: red;">(Choose at least 1 <!--and at most 5 -->options)</div><br>
                        <table border="0" cellspacing="0" cellpadding="0" align="left" >
                            <tr>

                                <?php
                                $count = 0;
                                $itemsPerCols = 7;
                                $needCloseTd = false;
                                foreach ($logTypes as $id => $detailarr)
                                    if ($detailarr[1]) {
                                        if ($count == 0) {
                                            echo '<td valign="center">';
                                        }
                                        $count = $count + 1;
                                        ?>
                                    <input type="checkbox" name=logtype[] value="<?php echo $id; ?>" > <?php echo $detailarr[0]; ?><BR />
                                    <?php
                                    if ($count == $itemsPerCols) {
                                        echo '</td><td valign="center">';
                                    }
                                    if ($count >= $itemsPerCols) {
                                        $count = 0;
                                    }
                                }
                            if ($count == 0) {
                                echo "- None";
                            }
                            ?>

                </tr>
            </table>
        </td>  
        </tr>
        <tr>
            <td width="15%">
                <b>Search Keyword: </b>
            </td>
            <td colspan="2">
                <input type="input" id="keyword" required maxlength="80" minlength="1" style="width: 99%;">
            </td>
        </tr>
        <tr>
            <td valign="center">
                <b>Type of Keyword:</b>
            </td>
            <td colspan="2"> 
                <select id="keyword_type" style="width: 100%;">
                    <option value="account">Account name</option>
                    <option value="character">Character name</option>
                    <option value="vehicle ID">Vehicle ID</option>
                    <option value="interior ID">Interior ID</option>
                    <option value="phonenumber">Phone number</option>
                    <option value="logtext">Log text</option>
                </select>
            </td>
        </tr>
        <tr>
            <td valign="center">
                <b>Time Limit (Until present):</b> 
            </td>
            <td > 
                <select id="start_point" style="width: 100%;">
                    <option value="1h">1 hour ago</option>
                    <option value="8h">8 hours ago</option>
                    <option value="12h">12 hours ago</option>
                    <option value="1d">1 day ago</option>
                    <option value="3d">3 days ago</option>
                    <option value="1w">1 week ago</option>
                    <option value="2w">2 weeks ago</option>
                    <option value="1m">1 month ago</option>
                    <option value="3m">3 months ago</option>
                    <option value="6m">6 months ago</option>
                    <option value="1y">1 year ago</option>
                    <option value="2y">2 years ago</option>
                    <option value="anytime">Anytime</option>
                    
                </select>
            </td>
            
            <td align="center" valign="center" width="162">
                <input type="submit" id="search_btn" value="Search" style="margin-left: 0px; margin-top: 0px; width: 98%"/>
            </td>
        </tr>
        </table>
        </form>
        <?php
    }
}