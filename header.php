<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
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

//date_default_timezone_set('UTC');
$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/Session.class.php";
$session = new Session();
$session->start_session('_owlgaming');
if ($_SERVER["REQUEST_URI"] != '/twofactor.php') {
    $_SESSION['lastpage'] = $_SERVER["REQUEST_URI"];
}
?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!--
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Maxime, 10-07-2015
 * ***********************************************************************************************************************
        -->
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="description" content="OwlGaming Community - Multi Theft Auto: Roleplay - As a Free to Play game, you will be able to experience core content without paying a single cent!" />
        <meta name="keywords" content="gtasa,sanandreas,grandtheftauto,community,mtarp,rp,roleplay,gta,mta,multitheftauto,vbulletin,forum,bbs,discussion,bulletin board, account,creation," />
        <meta name="author" content="Maxime" />

        <?php
        if (basename($_SERVER["PHP_SELF"]) != "support.php" ) {
            echo '<title>OwlGaming Community - Your World. Your Imagination</title>';
        }
        ?>
        <link href="css/dropdown.css" type="text/css" rel="stylesheet" />
        <link href="css/style.css" type="text/css" rel="stylesheet" />
        <link href="css/nivo-slider.css" type="text/css" rel="stylesheet" />
        <link href="css/pascal.css" type="text/css" rel="stylesheet" />
        <link rel="shortcut icon" href="/images/icons/favicon.png" type="image/x-icon" />
        <script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
        <script type="text/javascript" src="js/ajax_login_box.js"></script>
        <!--<script type="text/javascript" src="js/bootbox.min.js"></script>-->
        <script type="text/javascript" src="js/ajax_server_statistics.js"></script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <style>
            .logininput {
                width: 100px;
            }
        </style>
    </head>

    <body>
        <noscript>
            <div style="color: #D8000C;
                 background-color: #FFBABA;text-align: center;">
                We're sorry but our site <strong>requires</strong> JavaScript to run properly, please enable it.
            </div>    
        </noscript>
        <div id="header">
            <div class="head_wrap">
                <div class="clock" style="width:fit-content; color: rgba(255, 255, 255, 0.15); display:inline-block; margin-top: 5px;display:inline-block;">
                    <div id="Text" style="display:inline-block;">Servertime: </div>
                    <div class="clock-hours" style="display:inline-block;">--</div>
                    <div id="point" style="display:inline-block;">:</div>
                    <div class="clock-min" style="display:inline-block;">--</div>
                    <div id="point" style="display:inline-block;">:</div>
                    <div class="clock-sec" style="display:inline-block;">--</div>
                    <div>
                        <div class="clock-Date" style="display:inline-block;">--</div>

                    </div>
                </div>
                <div id="login_area" class="nav_login_holder" >
                    <?php
                    if (!isset($_SESSION['username']) or ! $_SESSION['username']) {
                        include './views/login.php';
                    } else {
                        include './views/logged-in.php';
                    }
                    ?>
                </div>

                <div class="nav_holder">
                    <ul>
                        <div id="main_menu_header"></div>
                    </ul>
                </div>
            </div>
        </div>