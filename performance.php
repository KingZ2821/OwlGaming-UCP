<?php
/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * File created by Maxime, 07-10-2015
 * ***********************************************************************************************************************
 */

//error_reporting(0);
include("header.php");
?>
<script type="text/javascript" src="./js/charts/fusioncharts.js"></script>
<script type="text/javascript" src="./js/charts/themes/fusioncharts.theme.fint.js"></script>

<link href="./datetime_picker/css/bootstrap.css" rel="stylesheet" media="screen">
<link href="./datetime_picker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<script type="text/javascript" src="./datetime_picker/js/bootstrap.min.js"></script>
<script type="text/javascript" src="./datetime_picker/js/bootstrap-datetimepicker.min.js" charset="UTF-8"></script>
<!--<script type="text/javascript" src="./datetime_picker/js/locales/bootstrap-datetimepicker.fr.js" charset="UTF-8"></script>-->

<div id="main-wrapper" style="margin-bottom: 30px">
    <?php
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        ?>
        <center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>
    </div>
    <?php
} else {
    ?>
    <div id="logs_top">
        <?php
        require_once "./functions/functions.php";
        echo "<h2>Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . ". Welcome to Staff Performance!</h2>";
        echo "<ul><li>There are only a few groups of users who have access to this page, if you're seeing this, it means you're in one of those permission groups. The higher the rank, the more information you will be able to see.</li>"
        . "<li>All points of date/time in this system are used and displayed in server time (UTC +0).</li></ul>";
        ?>
        <div id="perf_nav">
        </div>
    </div>
    </div>
    <div id="charts-container" style="width: 100%; padding-top:10px;">
    </div>

    <div id="logs_mid">
        <center>
            <b>
                <div id="logs_loading"></div>
            </b>
        </center>    
    </div>
<?php } ?>

<div id="logs_result" style="width:100%; padding-bottom: 100px; text-align: center;"></div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">
        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        <script type="text/javascript" src="js/ajax_staff_performance.js"></script>
        <script>menu_switch('individual', 'perf_nav')</script>