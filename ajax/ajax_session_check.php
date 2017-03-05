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

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/Session.class.php";
$session = new Session();
$session->start_session('_owlgaming');

if (!isset($_SESSION['username'])) { ?>
    <div class="login_text">
        <form name="login" action="" method="post" onSubmit="ajax_login_box(); return false;">
            <input class="textfield" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
            <input class="password textfield" name="password" type="password" placeholder="Password" id="password" maxlength="100"required/>
            <div class="hidden-submit"><input type="submit" tabindex="-1"/></div>
        </form>
    </div>
    <div class="login_btn" id="ajax_login_box_btn">
        <a href="" onClick="ajax_login_box(); return false;"><img src="images/login_btn.png" border="0" alt="" /></a>
    </div>
<div class="copyright">Session has timed out, please re-login!<br><a href="" onClick="alert('Registration on websites is currently disabled to ensure the consistence of accounts over multiple systems within OwlGaming Servers. \n\nPlease connect to Gameserver to register a new account. \n\n'); return false;" >Register new account</a> | <a href="lostpw.php" target="new">Recover lost password</a></div>
<?php } else { ?>
    <div class="login_text">
        <p>You're logged in as <?php echo $_SESSION['username']; ?><br>
        Email: <?php echo $_SESSION['email']; ?></p>
    </div>
    <div class="login_btn" id="ajax_logout_box_btn">
        <a href="" onClick="ajax_logout_box(); return false;"><img src="images/logout_btn.png" border="0" alt="" /></a>
    </div>
    <!--<div class="copyright"><a href="#" >Register new account</a> | <a href="">Recover lost password</a></div>-->
<?php } ?>