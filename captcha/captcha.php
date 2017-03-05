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

// $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
// require_once "$root/classes/Session.class.php";
// $session = new Session();
// $session->start_session('_owlgaming');
// header("Expires: Tue, 01 Jan 2013 00:00:00 GMT");
// header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
// header("Cache-Control: no-store, no-cache, must-revalidate");
// header("Cache-Control: post-check=0, pre-check=0", false);
// header("Pragma: no-cache");
// $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
// $randomString = '';
// for ($i = 0; $i < 5; $i++) {
//     $randomString .= $chars[rand(0, strlen($chars) - 1)];
// }
// $_SESSION['captcha'] = strtolower($randomString);
// $im = @imagecreatefrompng("captcha_bg.png");
// imagettftext($im, 30, 0, 10, 38, imagecolorallocate($im, 0, 0, 0), 'larabiefont.ttf', $randomString);
// header('Content-type: image/png');
// imagepng($im, NULL, 0);
// imagedestroy($im);
