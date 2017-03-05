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
<link href="css/login-form.css" type="text/css" rel="stylesheet" />
<div id="main-wrapper">
    <?php
    $action = isset($_GET['action']) ? $_GET['action'] : null;
    if ($action and is_numeric($action)) {
        require_once './classes/Database.class.php';
        $db = new Database("MTA");
        $db->connect();
        $account = $db->query_first("SELECT * FROM accounts WHERE id=" . $db->escape($action) . " AND TIMESTAMPDIFF(SECOND, registerdate, NOW()) < 30 LIMIT 1");
        if ($account['id'] and is_numeric($account['id'])) {
            ?>  
            <div id="reg_top">
                <h2>Congratulations!</h2>
                <p>Your OwlGaming MTA account '<b><i><?php echo $account['username']; ?></i></b>' is almost ready!<br>
                    Please follow the activation link that we dispatched to your email address (<b><i><?php echo $account['email']; ?></i></b>) to finish the final step to activate your MTA account.</p>
                <hr>
                <h2>Download Clients</h2>
                <p>To play OwlGaming MTA Online, you need to download and install <b><i>GTA San Andreas</i></b> and <b><i>Multi Theft Auto Modification</i></b>.<br>
                    For optimal gameplay, please check the system requirements before running the game. </p>
                <center><a target='_blank' href='http://store.steampowered.com/app/12120/'><img src='./images/icons/download_gta.png'></a>&nbsp;&nbsp;&nbsp;<a href='http://community.multitheftauto.com/mirror/mtasa/main/mtasa-1.5.exe'><img src='./images/icons/download_mta.png'></a></center>

                <h3>System requirements</h3>
                Processor: 1Ghz Pentium or AMD Athlon<br> 
                RAM: 256 MB RAM <br> 
                Video Card: 64MB Directx9 compatible (GeForce3 or better) <br> 
                Soundcard: Directx9 compatible <br> 
                Hard D Version: DirectX 9 <br> 
                DVD-Rrive: 4.7 GB free hard disk space <br> 
                Peripherals: Keyboard and mouse
            </div>
            <?php
        } else {
            echo "<br><br><center><b>Opps, sorry! The content you requested does not exist.</b></center>";
        }
        $db->close();
    } else {
        ?>
        <div id="reg_top">

            <h2>Register a new account</h2>
            <p>We are glad you have considered to be a new member and a part of our community and we wish you a wonderful experience here!<br>
                Please enter the details for your new account below.</p>
            <p><i>Please note that, we do not allow multiple accounts per user and you are hereby creating an MTA game account.</i></p>
        </div>
        <div id="reg_mid">

            <form id="register_form" onsubmit="ajax_submit_register();
                    return false;" method="post" action="" onkeypress="resetError();">
                <br>
                <table align="center" class="login-form" style="width: 650px;" cellpadding="2">
                    <tr>
                        <td style="text-align: left;">
                            <b>Username: </b>
                        </td>
                        <td style="text-align: left;">
                            <input type="text" id="reg_username" placeholder="" maxlength="30" required/>
                        </td>
                        <td rowspan="5" valign="bottom" style="text-align: left;">
                            <div style="margin-left: 20px;">
                                <div class="g-recaptcha" data-sitekey="6LedoggUAAAAAE5ymwtwsFCE9kWmltfX-ylCI4eS"></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <b>Password: </b>
                        </td>
                        <td style="text-align: left;">
                            <input type="password" id="reg_password1" placeholder="" maxlength="50" min="6" required/>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <b>Re-type Password: </b>
                        </td >
                        <td style="text-align: left;">
                            <input type="password" id="reg_password2" placeholder="" maxlength="50" min="6" required/>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <b>Email Address: </b>
                        </td>
                        <td style="text-align: left;">
                            <input type="email" id="reg_email" placeholder="" maxlength="200" min="1" required/>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: left;">
                            <b>Referrer: </b>
                        </td>
                        <td style="text-align: left;">
                            <?php
                            $referrer = '';
                            if (isset($_GET['referrer'])) {
                                $referrer = strip_tags($_GET['referrer']);
                                if (!preg_match('/^[A-Za-z0-9_]+$/', $referrer)) {
                                    $referrer = '';
                                }
                            }
                            ?>
                            <input type="text" id="reg_referrer" placeholder="Person who invited you" maxlength="30" value="<?php echo $referrer; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                    <center>
                        <br>
                        <input type="checkbox" id="terms" required/> I have read and agreed to the <a href="http://forums.owlgaming.net/index.php?showforum=194" target="_blank">terms and conditions</a>.<br>
                        <input type="submit" id="submit_reg" value="Register" style="margin-top: 20px; "/> 
                    </center>
                    </td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <div id="error_reg" style="padding-top: 10px; color:red;font-style: italic;font-size: 11px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" colspan="3">
                            <div id="reg_status"></div>
                        </td>
                    </tr>
                </table>
            </form>

            <br><br>
            <h2>Download Clients</h2>
            <p>To play OwlGaming MTA Online, you need to download and install <b><i>GTA San Andreas</i></b> and <b><i>Multi Theft Auto Modification</i></b>.<br>
                For optimal gameplay, please check the system requirements before running the game. </p>
            <center><a target='_blank' href='http://store.steampowered.com/app/12120/'><img src='./images/icons/download_gta.png'></a>&nbsp;&nbsp;&nbsp;<a href='http://community.multitheftauto.com/mirror/mtasa/main/mtasa-1.5.exe'><img src='./images/icons/download_mta.png'></a></center>

            <h3>System requirements</h3>
            Processor: 1Ghz Pentium or AMD Athlon<br> 
            RAM: 256 MB RAM <br> 
            Video Card: 64MB Directx9 compatible (GeForce3 or better) <br> 
            Soundcard: Directx9 compatible <br> 
            Hard D Version: DirectX 9 <br> 
            DVD-Rrive: 4.7 GB free hard disk space <br> 
            Peripherals: Keyboard and mouse
        </div>
        <div id="reg_bot">

        </div>
        <?php
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
        <script type="text/javascript" src="js/ajax_register.js"/></script>

