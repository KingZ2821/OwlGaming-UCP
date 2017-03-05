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
    $thankYouPage = "
I want to express my appreciation for your generosity in support of OwlGaming Community. Your personal commitment was incredibly helpful and allowed us to reach our goal. Your assistance means so much to me but even more to the Community. Thank you from all of us!
<br><br>
In return, GameCoins should have been added into the donated account instantly. If for some reasons, you don't recieve GC(s) within next 2 hour(s) or have any other donation issue/question, please visit <a href='support.php'>Support Center</a> to submit a ticket under 'Donation issue or question'.
<br>
<br>
<i>Sincerely,<br>
OwlGaming Community<br>
OwlGaming Development Team</i><br>";

    ?>
    <div id="thank_top">
        <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . ucfirst($_SESSION['username']) . "! "; ?>Thank you for your donation!</h2>
        <p><?php
            echo $thankYouPage;
            ?></p>
    </div>
    
    <div id="char_info_mid">
        <br><br>
        <b><a href='ucp.php'>Check account information<a><br>
                    <a href='donate.php'>Make another donation</a></b>
                
        
    </div>
    <div id="char_info"></div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>

