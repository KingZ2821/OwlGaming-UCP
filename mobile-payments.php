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
    <?php
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        require_once("./config.inc.php");
        require_once("./functions/base_functions.php");
        require_once("./functions/functions.php");

        $protocol = 'http'; //(empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        @$params = $_SERVER['QUERY_STRING'];
        $currentUrl = $protocol . '://' . $host . $script . '?' . $params;
        $currentSiteURL = $protocol . '://' . $host;
        ?>
        <div id="don_top">
            <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!<br>"; ?>Thank you for considering to purchase and to support the community work of this project!</h2>
            <p>Almost everyone has a mobile telephone these days. Payment by SMS is a low threshold method of payment. You can purchase Game Coins by simply sending or receiving a text message. This method of payment is ideal for ‘micropayments’ between $1 and $10,00. Perfect for offering an alternative payment method to target groups who do not have a credit card or their own bank account. All purchases are processed <b><i>immediately and automatically</b></i> after payment. </p>
            <center>
                <img src="/images/Pay-by-Mobile_Widget.png">
            </center>
            <!--<p>Mobile payments support mobile operator billing in over 88 countries, over 300 operators around the world and don't require paypal account, bank account or credit/debit cards.</p>-->
            <center>
                <script src="//fortumo.com/javascripts/fortumopay.js" type="text/javascript"></script>
                <a id="fmp-button" href="#" rel="7b7a25ec612ac6bbb863781f09efa930/<?php echo $_SESSION['userid']; ?>?test=notok"><img src="//fortumo.com/images/fmp/fortumopay_96x47.png" width="96" height="47" alt="Mobile Payments by Fortumo" border="0" /></a>
                <br>
                <b>Account to receive GCs: <?php echo $_SESSION['username'];?></b>
            </center>
            <p>However in exchange for this convenience, the payment service provider and your mobile operator may take 40% up to 80% of your payments before it processes to OwlGaming which means you may receive less Game Coins for the same amount of payment you make via paypal method. Thus far these payment methods are <u>NOT recommended</u>.</p>

        </div>
        <div id="char_info_mid">

        </div>
        <div id="char_info">

        </div>
    <?php } ?>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>

