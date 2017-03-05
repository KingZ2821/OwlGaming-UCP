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

include("header.php");
?>
<div id="main-wrapper">
    <div id="lib_top" style="margin-bottom: 20px;">
        <h2>OwlGaming Premium Perks</h2>
        

    </div>
    <div id="lib_mid_top" >
        <p><a href="#" onclick="load_perks_gui('forums_groups'); return false;"><b>Forums Premium Ranks</b></a><br><i>Comes with 4 tiers Bronze, Silver, Gold & Diamond - Grant access to the "Premium Hub" forum section!</i></p>
        <?php 
        require_once './config.inc.php';
        require_once './classes/Mta.class.php';
        $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
        $serverOnline = $mtaServer->getResource("usercontrolpanel")->call("isServerOnline");
        if (!$serverOnline or $serverOnline[0] != 1) {
            
        } else {
            echo '<p><a href="#" onclick="return false;\'); return false;"><b>In-Game Perks</b></a><br><i>The following perks are to be activated In-Game or in another section of the website.</i></p>';
            echo '<ul>';
            $perks = $mtaServer->getResource("donators")->call("getPerks");
            foreach($perks[0] as $perk) {
                echo "<li>".$perk[0]."</li>";
            }
            echo "</ul>";
        }
        ?>
    </div>
    <div id="lib_mid" >
    </div>
    <div id="lib_bot"></div>
    <hr>
    <h2>Charge GameCoins, Keep OwlGaming alive!</h2>
    <table border="0" align="center">
        <tr>
            <td align="center">
                <a href="donate.php"><img src="images/opt_pp.png" height="70"></a><br>
                <i>(Process immediately and automatically, recommended)</i>
            </td>
            <td align="center">
                <a href="bitcoin.php"><img src="images/opt_btc.png" height="70"></a><br>
                <i>(Process immediately and automatically, recommended)</i>
            </td>
            <td align="center">
                <a href="mobile-payments.php"><img src="images/opt_mobile.png" height="70"></a><br>
                <i>(Charge on your phone bill, process immediately and automatically)</i>
            </td>
        </tr>
    </table>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <script type="text/javascript" src="js/ajax_perks.js"/></script>
        <?php
        include("sub.php");
        include("footer.php");
        ?>

