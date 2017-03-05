<?php
/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * File created by Maxime, 20-10-2015
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
        $btcRate = file_get_contents(BITCOIN_ROOT . "tobtc?currency=USD&value=1");
        ?>
        <script type="text/javascript" src="<?php echo BITCOIN_ROOT ?>Resources/wallet/pay-now-button-v2.js"></script>
        <link href="css/login-form.css" type="text/css" rel="stylesheet" />
        <div id="don_top">
            <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!<br>"; ?>Thank you for considering to purchase and to support the community work of this project!</h2>
            <p>Bitcoin is an innovative payment network and a new kind of money. Bitcoin uses peer-to-peer technology to operate with no central authority or banks. Using Bitcoin to pay is easy and accessible to everyone.</p>
            <center>
                <iframe height="360" width="640" src=https://www.youtube.com/embed/Gc2en3nHxA4?autoplay=0&showinfo=0&controls=0&version=3&loop=0&rel=0&showsearch=0&cc_load_policy=0" frameborder="0"></iframe>
            </center>
            <div id="btc_main" class="login-form" style="width:598px; ">
                <center>
                    <h2>I want to donate...</h2>
                    <table border="0" width="100%" cellpadding="5">
                        <tr>
                            <td align="right">

                            </td>
                            <td align="left" width="70%">
                                <b>$ <input id="custom_amount" type="number" value="5" min="1" max="100000" required step="1" onchange="calculateGc();"/> = Ƀ <div id="calculated_btc" style="display: inline"><?php echo $btcRate * 5; ?></div></b><br>
                                GC(s): <div id="calculated_gc" style="display: inline">750</div><br>
                                Discount: <div id="calculated_discount" style="display: inline">0.67</div>%
                                Bonus GC(s): +<div id="calculated_bonus" style="display: inline">5</div><br>
                                <b>Total GC(s): <div id="calculated_total" style="display: inline">755</div></b><br>
                                <input id="btcRate" value="<?php echo $btcRate; ?>" type="hidden">
                            </td>
                        </tr>
                    </table>
                    <input type="button" id="btc_create_invoice" value="Next" onclick="btc_create_invoice();">
                </center>
            </div>
            <center>
                <br>
                <br>
                <img src="/images/bitcoin_1.png">
                <br>
                <br>
                <p>Your donation will be processed immediately and automatically after payment.</p>
            </center>
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
        <script type="text/javascript" src="js/ajax_donate_content.js"/></script>