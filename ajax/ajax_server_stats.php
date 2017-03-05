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

// define the path and name of cached file
$cachefile = '../cache/ajax_server_stats.php';
$cachetime = 60; // 1 min
// Check if the cached file is still fresh. If it is, serve it up and exit.
if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
    include($cachefile);
    exit;
}
// if there is either no file OR the file to too old, render the page and capture the HTML.
ob_start();
?>
<html>
    <?php
    require_once("../config.inc.php");
    require_once("../classes/Mta.class.php");

    $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
    $mtaServerStats = $mtaServer->getResource("usercontrolpanel")->call("getServerStats");
    if (!isset($mtaServerStats) or ( !$mtaServerStats) or ! isset($mtaServerStats[1]) or ( !$mtaServerStats[1])) {
        echo "<br><b>Gameserver is <div class='status_btn_red' style='margin-top:-3px;'>OFFLINE</div></b><br>"
        . "<p>We're sorry, the game server is currently down for scheduled maintenance. Please check back soon.</p>";
        exit();
    } else {
        echo "<br><b>Gameserver is <div class='status_btn_green' style='margin-top:-3px;'>ONLINE</div></b><br>"
        . "<p>Server IP: <a href='mtasa://" . SDK_IP . ":" . $mtaServerStats[1] . "'>" . SDK_IP . ":" . $mtaServerStats[1] . "</a><br>"
        . "Online Roleplayers: " . $mtaServerStats[3] . "/" . $mtaServerStats[4] . "<br>"
        . "Map: " . $mtaServerStats[6] . "<br>"
        . "Gamemode: " . $mtaServerStats[7] . "<br>"
        . "FPS Limit: " . $mtaServerStats[5] . "<br>"
        . "MTA Version: " . $mtaServerStats[2]['tag'] . "<br>"
        . "Script Version: v" . $mtaServerStats[8] . "<br>"
        . '<div id="Text" style="display:inline-block;">Servertime: </div>
                    <div class="clock-hours" style="display:inline-block;">--</div>
                    <div id="point" style="display:inline-block;">:</div>
                    <div class="clock-min" style="display:inline-block;">--</div>
                    <div id="point" style="display:inline-block;">:</div>
                    <div class="clock-sec" style="display:inline-block;">--</div>
                    <div>
                        <div class="clock-Date" style="display:inline-block;">--
                    </div>';
    }
    ?>
</html>
<?php
// We're done! Save the cached content to a file
$fp = fopen($cachefile, 'w');
fwrite($fp, ob_get_contents());
fclose($fp);
// finally send browser output
ob_end_flush();
?>

