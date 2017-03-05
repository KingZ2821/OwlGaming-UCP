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
?>
<form name="login" action="" method="post" onSubmit="ajax_login_box();
        return false;">
    <input class="logininput" width="10" name="username" type="text" placeholder="Username" id="username" maxlength="100" required/>
    <input class="logininput" width="10" name="password" type="password" placeholder="Password" id="password" maxlength="100" required/>
    <input type="submit" id="btn_login" value="Login"/>
    <!--<a href="../register.php" ><input type="button" value="Register"/></a>-->
</form>