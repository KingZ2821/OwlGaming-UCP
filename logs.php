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

ob_start();
include("header.php");
require_once './classes/TwoFactor.class.php';
$twofactor = new TwoFactor();
if ($twofactor->is_two_factor_enabled() and !$twofactor->is_two_factor_valid()){
    header('Location: /twofactor.php');
    exit();
}
?>
<div id="main-wrapper" style="margin-bottom: 30px">
    <?php

    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        ?>
        <div id="logs_top">
            <center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>
        </div>
        <div id="logs_mid">
            <center>
                <b>
                    <div id="logs_loading"></div>
                </b>
            </center>    
        </div>
    <?php } ?>
</div>
<div id="logs_result" style="width:100%; padding-bottom: 100px; text-align: center;"></div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">
        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        <script type="text/javascript" src="js/ajax_logs.js"></script>
        <script>
            ajax_load_logs_GUI();
        </script>