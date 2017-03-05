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

if (isset($_POST['step']) and $_POST['step'] == "validate_code") {
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    require_once "$root/classes/TwoFactor.class.php";
    $twofactor = new TwoFactor();
    if ($twofactor->verifyCode($_POST['code'])) {
        header('Location: ' . $_SESSION['lastpage']);
        exit();
    } else {
        header('Location: /twofactor.php');
        exit();
    }
} else {
    header('Location: /');
    exit();
}
