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

//CONFIGS
$max_allow_file_size = 2048000; //bytes
$max_h = 200;
$max_w = 200;
$validextensions = array("jpeg", "jpg", "png");

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/Session.class.php";
$session = new Session();
$session->start_session('_owlgaming');

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    echo "You must be logged in to access this content.";
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
            if (!$image->validate_size()){
                die("Image is not valid.");
            }
            
            //Now resize it
            $image->resizeImage($max_w, $max_h);
            
            //Remove uploaded temp file
            unlink($file);
            $file = $image->saveImage($file_extension);
            
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($file_info, $file);
            $file_size = filesize($file);
            $fileData = file_get_contents($file);
            if (!$fileData) {
                die("File is corrupted. Please try again.");
            }
            
            $userID = $_SESSION['userid']; //This make sure noone edit other char
            require_once("../classes/Database.class.php");
            $db = new Database("MTA");
            $db->connect();
            //Delete the previous avatar file if any.
            $db->query("DELETE FROM files WHERE avatar_for_account=" . $userID);
            //Start saving new  file.
            $insert = array();
            $insert['desc'] = "Avatar for account " . $_SESSION['username'] . " ; Uploaded by " . $_SESSION['username'];
            $insert['file'] = $fileData;
            $insert['file_type'] = $file_type;
            $insert['avatar_for_account'] = $userID;
            $insert['file_size'] = $file_size;
            $qid = $db->query_insert("files", $insert);
            if ($qid) {
                require_once '../classes/Mta.class.php';
                @$mtaServer = new mta();
                @$mtaServer->getResource("cache")->call("removeImage", "http://owlgaming.net/avatar.php?id=$userID" );
                echo('Avatar has been uploaded and saved.');
            } else {
                echo('Error occurred while saving your avatar file on server. Try again');
            }
            unlink($file);
            $db->close();
        } else {
            require_once '../functions/base_functions.php';
            die("Image extension is not supported or it has exceeded " . formatBytes($max_allow_file_size) . ".<br>");
        }
    } else {
        die('Nice try.');
    }
}