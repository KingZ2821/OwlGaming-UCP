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

$id = (isset($_GET['id']) and is_numeric($_GET['id']) and $_GET['id'] > 0) ? $_GET['id'] : 0;
$size = isset($_GET['size']) ? $_GET['size'] : "thumb";
if ($id > 0) {
    require_once './classes/Database.class.php';
    $db = new Database("FORUMS");
    $db->connect();
    $avatar = $db->query_first("SELECT pp_member_id, pp_main_photo, pp_thumb_photo FROM " . $db->pre . "profile_portal WHERE pp_member_id='" . $id . "' ");
    if ($avatar and $avatar['pp_member_id'] and is_numeric($avatar['pp_member_id'])) {
        if (!isset($avatar['pp_main_photo']) || $avatar['pp_main_photo'] == '') {
            $db->close();
            fetch_default_avatar();
        } else {
            $db->close();
            $url = "http://forums.owlgaming.net/uploads/" . ($size == "full" ? $avatar['pp_main_photo'] : $avatar['pp_thumb_photo']);
            //die($url);
            $file_data = file_get_contents($url);
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($file_info, $url);
            header("Content-Type: {$file_type}\n");
            echo $file_data;
        }
    } else {
        $db->close();
        fetch_default_avatar();
    }
} else {
    fetch_default_avatar();
}

function fetch_default_avatar() {
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    $default = "$root/images/default_avatar.png"; //specify upload directory 
    $file_data = file_get_contents($default);
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $file_type = finfo_file($file_info, $default);
    header("Content-Type: {$file_type}\n");
    echo $file_data;
}
