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

@require_once './config.inc.php';
if (SITE_MAINTENANCE) {
    include './maintenance.php';
} else {
    include("header.php");
    ?>
    <div id="main-wrapper">
        <div class="slide">
            <div class="slideholder">
                <div class="slider-wrapper theme-pascal">
                    <div id="slider" class="nivoSlider"> 
                        <img src="images/slides/1.gif" alt="" title="1" />
                        <img src="images/slides/2.gif" alt="" title="2" />
                        <img src="images/slides/3.gif" alt="" title="3" />
                        <img src="images/slides/4.gif" alt="" title="4" />
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="content_wrap">
        <div class="text_holder">
            <div class="features_box">

            </div>	
            <?php
            include("sub.php");
            include("footer.php");
        }
        ?>