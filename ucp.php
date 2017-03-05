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
require_once './classes/TwoFactor.class.php';
$twofactor = new TwoFactor();
if ($twofactor->is_two_factor_enabled() and ! $twofactor->is_two_factor_valid(true)) {
    
}
?>
<div id="main-wrapper">
    <div id="acc_info">
        <center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>
    </div>
    <div id="char_info_mid_top"></div>
    <div id="char_info_mid"></div>
    <div id="char_info">
        <center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>
    </div>
    <script type="text/javascript" src="js/ajax_ucp_content.js"/></script>
<script>
    $(document).ready(function () {
        ajax_load_acc_info();
    });
</script>
<?php
if (isset($_GET['action']) and $_GET['action'] == 'settings') {
    ?>
    <script>
        $(document).ready(function () {
            ajax_load_acc_settings();
        });
    </script>
    <?php
} else {
    ?>
    <script>
        $(document).ready(function () {
            ajax_load_char_info();
        });
    </script>
    <?php
}
if (isset($_GET['action']) and $_GET['action'] == 'transfer') {
    ?>
    <script>
        ajax_stat_transfer();
    </script>
<?php } ?>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">
        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        