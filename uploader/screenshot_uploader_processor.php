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

//CONFIGS
$max_allow_file_size = 2048000; //bytes
$max_h = 1080;
$max_w = 1920;
$validextensions = array("jpeg", "jpg", "png");
$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
$UploadDirectory = "$root/gallery/";

require_once "$root/classes/Gallery.class.php";
$gallery = new Gallery();

if (!$gallery->can_access_uploader()) {
    echo "You don't have permission to access this area.";
} else {
    if (isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"] == UPLOAD_ERR_OK) {
        /*
          Note : You will run into errors or blank page if "memory_limit" or "upload_max_filesize" is set to low in "php.ini".
          Open "php.ini" file, and search for "memory_limit" or "upload_max_filesize" limit
          and set them adequately, also check "post_max_size".
         */

        //check if this is an ajax request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            die();
        }

        $temporary = explode(".", $_FILES["FileInput"]["name"]);
        $file_extension = end($temporary);
        $file_type = isset($_FILES["FileInput"]["type"]) ? $_FILES["FileInput"]["type"] : "not_found";
        $file_size = isset($_FILES["FileInput"]["size"]) ? $_FILES["FileInput"]["size"] : "not_found";
        $file_error = $_FILES["FileInput"]["error"];
        if ((($file_type == "image/png") || ($file_type == "image/jpg") || ($file_type == "image/jpeg")) && ($file_size < $max_allow_file_size) && in_array($file_extension, $validextensions)) {
            if ($file_error > 0) {
                die("Error Code: " . $file_error);
            }
            $file = $_FILES['FileInput']['tmp_name'];

            // Validate if it's an actual image or a script with renamed extension.
            require_once "$root/classes/Image.class.php";
            $image = new Image($file, $file_extension);
            if (!$image->validate_size($max_w, $max_h)) {
                die("Image is not valid or exceeded maximum allowed dimension of 1920x1080");
            }
            $UploadDirectory = $UploadDirectory . $_SESSION['userid'];
            if (!file_exists($UploadDirectory)) {
                mkdir($UploadDirectory, 664, true);
            }
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $insert = array();
            $insert['uploader_id'] = $_SESSION['userid'];
            $insert['title'] = 'Hi';
            $insert['desc'] = 'hi';
            $insert['extension'] = $file_extension;
            $id = $db->query_insert("ucp_gallery", $insert);
            if (move_uploaded_file($file, $UploadDirectory.'/'.$id.'.'.$file_extension)) {
                echo "Saved!";
            } else {
                echo "Errors occurred while saving your screenshot.";
            }
            $db->close();
        } else {
            require_once '../functions/base_functions.php';
            die("Image extension is not supported or it has exceeded " . formatBytes($max_allow_file_size) . ".<br>");
        }
    } else {
        die('Nice try.');
    }
}