<?php
/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 10-07-2015
 * ***********************************************************************************************************************
 */

include("header.php");
?>
<script src="./js/gallery.js" type="text/javascript"></script>
<style>
    #mainwrapper {
        //font: 10pt normal Arial, sans-serif;
        //height: auto;
        //margin: 0 auto 0 auto;
        //text-align: center;
        //width: auto;
    }
    #mainwrapper .box {
        border: 5px solid #fff;
        cursor: pointer;
        height: 155px;
        float: left;
        margin: 5px;
        position: relative;
        overflow: hidden;
        width: 200px;
        -webkit-box-shadow: 1px 1px 1px 1px #ccc;
        -moz-box-shadow: 1px 1px 1px 1px #ccc;
        box-shadow: 1px 1px 1px 1px #ccc;
    }

    #mainwrapper .box img {
        position: absolute;
        left: 0;
        -webkit-transition: all 300ms ease-out;
        -moz-transition: all 300ms ease-out;
        -o-transition: all 300ms ease-out;
        -ms-transition: all 300ms ease-out;
        transition: all 300ms ease-out;
    }
    #mainwrapper .box .caption {
        background-color: rgba(0,0,0,0.8);
        position: absolute;
        color: #fff;
        z-index: 100;
        -webkit-transition: all 300ms ease-out;
        -moz-transition: all 300ms ease-out;
        -o-transition: all 300ms ease-out;
        -ms-transition: all 300ms ease-out;
        transition: all 300ms ease-out;
        left: 0;
    }
    #mainwrapper .box .simple-caption {
        height: 30px;
        width: 200px;
        display: block;
        bottom: -30px;
        line-height: 25pt;
        text-align: center;
    }
    #mainwrapper .box:hover .simple-caption {
        -moz-transform: translateY(-100%);
        -o-transform: translateY(-100%);
        -webkit-transform: translateY(-100%);
        transform: translateY(-100%);
    }

</style>
<div id="main-wrapper">
    <div id="gar_detail">

    </div>
    <div id="gar_top">
        <h2>Top rated</h2>
        <div id="mainwrapper"><!-- This #mainwrapper section contains all of our images to make them center and look proper in the browser ->
            <!-- Image Caption 1 -->
            <div id="box-1" class="box">
                <img id="image-1" src="./images/MTA_skins/Skin_100.png" height="100%"/>
                <span class="caption simple-caption">
                    <p>Simple Caption</p>
                </span>
            </div>
        </div>
    </div>
    <div id="gar_mid" >
        <h2>Most recent</h2>
    </div>
    <div id="gar_bot" >
        <h2>My uploads</h2>
        Total likes: 0<br>
        Total GC(s) earned: 0<br>
    </div>
    <div id="gar_uploader">
        <?php
        require_once './classes/Gallery.class.php';
        $gar = new Gallery();
        //$gar->output_uploader();
        ?>
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
        <script type="text/javascript">
            function loa_return(loa_id) {
                if (confirm("Are you sure?")) {
                    $.post("./ajax/ajax_loa.php", {
                        loa_action: "return",
                        loa_id: loa_id,
                    }, function (stuff) {
                        if (stuff == "ok") {
                            if (confirm("You have successfully deleted your Leave of absence, you're now back to active state!\nDo you want to reload this page?")) {
                                location.reload();
                            }
                        } else {
                            alert(stuff);
                        }
                    });
                }
            }
        </script>


