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

$u = isset($_POST['username']) ? $_POST['username'] : null;
$p = isset($_POST['password']) ? $_POST['password'] : null;
if ($u and $p) {
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    require_once("$root/classes/User.class.php");
    $user = new User();
    $user->dbConnect();
    $login = $user->login($u, $p);
    $user->dbClose();
}




