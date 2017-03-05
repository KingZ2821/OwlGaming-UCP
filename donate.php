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
    require_once("./config.inc.php");
    if (!(USE_SANDBOX and ( !isset($_SESSION['userid']) or $_SESSION['userid'] != 1))) {
        require_once("./functions/base_functions.php");
        require_once("./functions/functions.php");

        $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
        $host = $_SERVER['HTTP_HOST'];
        $script = $_SERVER['SCRIPT_NAME'];
        @$params = $_SERVER['QUERY_STRING'];
        $currentUrl = $protocol . '://' . $host . $script . '?' . $params;
        $currentSiteURL = $protocol . '://' . $host;
        ?>
        <link href="css/login-form.css" type="text/css" rel="stylesheet" />
        <div id="don_top">
            <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!<br>"; ?>Thank you for considering to donate and to support the community work of this project!</h2>
            <p>By donating to our server, you'll be gifted with an amount of Game Coins, this is another currency beside money in game and is used to unlock premium features. <br>All donations are processed <b><i>immediately and automatically</b></i> after payment. </p>
            <p>Every single dollar we get is put directly back into the work of developing for the community and to pay expenses relating to the upkeep of the OwlGaming's servers!</p>
            <p>We would like to give very special thanks to <a href="stats.php" target="_blank">the players who has donated</a> for their generous financial support. We are so appreciative and grateful for each and every one of you to help our community get off the ground and to maintain our gaming servers!</p>
        </div>
        <div id="char_info_mid">

        </div>
        <div id="char_info">
            <div class="login-form">
                <h2><center>I want to donate..</center></h2>
                <form id="donation_form" action="<?php echo PAYPAL_URL; ?>" method="post" onSubmit="return startDonation();">
                    <input type="hidden" name="cmd" value="_xclick" />
                    <input type="hidden" name="business" value="<?php echo BUSINESS_EMAIL; ?>" />
                    <input type="hidden" name="item_name" value="Donation" />
                    <input type="hidden" name="item_number" value="<?php if (isset($_SESSION['userid'])) echo $_SESSION['userid']; ?>" />
                    <input type="hidden" name="notify_url" value="https://www.owlgaming.net/postback-pp.php" />
                    <input type="hidden" name="cancel_return" value="<?php echo $currentUrl; ?>" />
                    <input type="hidden" name="return" value="<?php echo $currentSiteURL; ?>/thankyou.php" />
                    <input type="hidden" name="rm" value="2" />
                    <input type="hidden" name="no_shipping" value="1" />
                    <input type="hidden" name="donor_username" value="<?php if (isset($_SESSION['username'])) echo $_SESSION['username']; ?>"/>
                    <?php $spaceTable = 60 ?>
                    <div id="don_option_normal" >
                        <table align="center">
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="3"/>
                                </td>
                                <td align=right><p>$3
                                </TD>
                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(3)[0]); ?> Game Coins
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="5" checked/>
                                </td>
                                <td align=right><p>$5
                                </TD>
                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(5)[0]); ?> Game Coins
                                </TD>

                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="10" /> 
                                </td>
                                <td align=right><p>$10
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(10)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(10)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="20" /> 
                                </td>
                                <td align=right><p>$20
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(20)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(20)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="50" /> 
                                </td>
                                <td align=right><p>$50
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(50)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(50)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="110" /> 
                                </td>
                                <td align=right><p>$110
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(110)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(110)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="175" /> 
                                </td>
                                <td align=right><p>$175
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(175)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(175)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="300" /> 
                                </td>
                                <td align=right><p>$300
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(300)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(300)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                            <TR>
                                <TD>
                                    <input type="radio" name="amount1" value="375" /> 
                                </td>
                                <td align=right><p>$375
                                </TD>

                                <td width=<?php echo $spaceTable; ?>>
                                </td>
                                <TD align=left>
                                    <p><?php echo number_format(getGsFromDollar(375)[0]); ?> Game Coins (<?php echo number_format(getGsFromDollar(375)[2]); ?>% OFF!)
                                </TD>
                            </TR>
                        </TABLE>
                        <center><input type="button" name="btn_don_custom_amount" value="Choose my own amount" onClick="showCustomOption()"/></center>
                    </div>

                    <div id="don_option_custom" class="hidden">
                        <table border="0" width="100%" cellpadding="5">
                            <tr>
                                <td align="right">

                                </td>
                                <td align="left" width="62%">
                                    <b>$<input id="custom_amount" type="number" value="5" min="1" max="100000" required step="1" onchange="calculateGc();"/></b><br>
                                    GC(s): <div id="calculated_gc" style="display: inline">750</div><br>
                                    Discount: <div id="calculated_discount" style="display: inline">0.67</div>%
                                    Bonus GC(s): +<div id="calculated_bonus" style="display: inline">5</div><br>
                                    <b>Total GC(s): <div id="calculated_total" style="display: inline">755</div></b><br>
                                </td>

                            </tr>
                        </table>
                        <center><input type="button" name="btn_don_normal_amount" value="Choose pre-defined amounts" onClick="showDefaultOption()"/></center>
                    </div>

                    <input type="hidden" name="currency_code" value="USD" />
                    <input type="hidden" name="lc" value="US" />
                    <input type="hidden" name="amount" value="5" id="final_donation_amount"/>
                    <br>
                    <b><p>Account to receive GCs: <input type="text" name="custom" value="<?php if (isset($_SESSION['username'])) echo $_SESSION['username']; ?>" maxlength="200" required/>

                            <input type="submit" name="I1" value="Donate" />
                        </p></b>

                    <!-- PayPal Logo -->

                    <center>
                        <table border="0" cellpadding="10" cellspacing="0" align="center"><tr><td align="center"></td></tr><tr><td align="center"><a href="https://www.paypal.com/vn/webapps/mpp/paypal-popup" title="How PayPal Works" onclick="javascript:window.open('https://www.paypal.com/vn/webapps/mpp/paypal-popup', 'WIPaypal', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700');
                                    return false;"><img src="/images/paypal1.png" border="0" height="150" alt="PayPal Acceptance Mark"></a></td></tr></table><!-- PayPal Logo -->

                    </center>
                </form>
            </div>
        </div>
        <script type="text/javascript" src="js/ajax_donate_content.js"/></script>
    <?php
} else {
    echo "<center><h2>Donation System or Payment Gateway is currently down for schedule maintenance.</h2>Check back soon!</center>";
}
?>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>

