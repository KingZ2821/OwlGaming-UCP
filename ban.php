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
?>
<div id="main-wrapper">
    <div id="lib_top">
        <?php 
        require_once './classes/Ban.class.php';
        $ban = new Ban();
        $ban->outputIndex(true);
        
        ?>
        <input type="button" value="List all" onclick="load_all_bans();"><!--<input type="button" value="Add ban" onclick="add_ban_gui();">-->
    </div>
    <div id="lib_bot" >
        <?php 
        $ban->outputFilter();
        ?>
    </div>
    <div id="lib_mid">
        <?php 
        $ban->dbConnect();
        $ban->show(); //20, 0, 'lib_mid', 'id', '100'
        $ban->dbClose();
        ?>
    </div>
    <div id="ban_detail">
        
    </div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">
            
        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        <script type="text/javascript" src="js/ajax_ban.js"/></script>
        

