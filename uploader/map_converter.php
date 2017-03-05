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

// .map to database binary converter by Maxime
$password = isset($_GET['password']) ? $_GET['password'] : null;
if ($password == "sdjvnawdSdvnwerScskmvSdwv") { // for converter
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    $UploadDirectory = "$root/uploader/uploads/custom_interiors"; //specify upload directory 
    require_once '../classes/Database.class.php';
    $db = new Database("MTA");
    $db->connect();
    $maps = new DirectoryIterator($UploadDirectory);
    foreach ($maps as $map) {
        if ($map->isFile()) {
            //var_dump($fileinfo->getFilename());
            //echo $map;
            $file = $UploadDirectory . "/" . $map;
            $file_data = file_get_contents($file);
            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($file_info, $file);
            $file_size = filesize($file);
            $file_name = basename($map, ".map");
            $insert = array();
            $insert['desc'] = $map . "; Uploaded by SYSTEM";
            $insert['file'] = $file_data;
            $insert['file_type'] = $file_type;
            $insert['connected_interior'] = $file_name;
            $insert['file_size'] = $file_size;
            $qid = $db->query_insert("files", $insert);
            if ($qid) {
                echo $insert['desc'] . " - done - " . $qid . "<br>";
            }
        }
    }
    $db->close();
} else if ($password == "sdjvnawdS23dvnwersdScskmvSdwv") { // for dơnloader
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    $UploadDirectory = "$root/uploader/uploads/custom_interiors"; //specify upload directory 
    require_once '../classes/Database.class.php';
    $db = new Database("MTA");
    $db->connect();
    $file = $db->query_first("SELECT * FROM files WHERE id=1");
    header("Content-Type: {$file['file_type']}\n");
    header("Content-Disposition: attachment; filename=\"{$file['desc']}\"\n");
    header("Content-Disposition: attachment; filename=\"{$file['name']}\"\n");
    header("Content-Length: {$file['file_size']}\n");
    echo $file['file'];
    $db->close();
} else if ($password == "sdjvnds2awdS23dvnwersdScskmvSdwv") { // for migrating interior upload times
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    require_once '../classes/Database.class.php';
    $db = new Database("MTA");
    $db->connect();
    $ints = $db->query("SELECT id, uploaded_interior FROM interiors WHERE uploaded_interior IS NOT NULL");
    $i=0;
    while ($int = $db->fetch_array($ints)) {
        if ($db->query("UPDATE files SET dateline='".$int['uploaded_interior']."' WHERE connected_interior=".$int['id'])) {
            echo $int['id']." - ".$int['uploaded_interior']."<br>";
        }
    }
    $db->free_result($ints);
    $db->close();
} else {
    echo "Access denied.";
}




