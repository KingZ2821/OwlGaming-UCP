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
$max_allow_file_size = 102400; //bytes
$normalPrice = 500;

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/Session.class.php";
$session = new Session();
$session->start_session('_owlgaming');

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    echo "You must be logged in to access this content.";
} else {
    require_once $root . '/classes/User.class.php';
    $user = new User();
    if ($user->is_banned()) {
        die('You can not upload custom interior at the moment because you are currently banned.\n');
    } else if (isset($_FILES["FileInput"]) && $_FILES["FileInput"]["error"] == UPLOAD_ERR_OK) {
        /*
          Note : You will run into errors or blank page if "memory_limit" or "upload_max_filesize" is set to low in "php.ini".
          Open "php.ini" file, and search for "memory_limit" or "upload_max_filesize" limit
          and set them adequately, also check "post_max_size".
         */

        //check if this is an ajax request
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            die();
        }


        //Is file size is less than allowed size.
        require_once '../functions/base_functions.php';
        if ($_FILES["FileInput"]["size"] > $max_allow_file_size) {
            die("Map file can not be larger than " . formatBytes($max_allow_file_size));
        }

        if (isset($_POST['intid']) and isset($_POST['intid'])) {
            $File_Name = strtolower($_FILES['FileInput']['name']);
            $File_Ext = substr($File_Name, strrpos($File_Name, '.')); //get file extention
            if ($File_Ext != ".map") {
                die("Unsupported File!");
            }

            $intid = $_POST['intid'];
            $charid = $_POST['charid'];
            $userID = $_SESSION['userid']; //This make sure noone edit other char

            require_once("../classes/Database.class.php");
            $db = new Database("MTA");
            $db->connect();
            $isFactionInt = false;
            $int = $db->query_first("SELECT i.*, DATEDIFF(NOW(), `lastused`) AS `datediff`, DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`,
            charactername AS ownername,
            (CASE WHEN f.dateline IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, f.dateline, NOW()) END) AS uploaded
            FROM interiors i LEFT JOIN characters c ON i.owner=c.id LEFT JOIN files f ON i.id=f.connected_interior
	WHERE i.id='" . $intid . "' AND i.owner=" . $charid);
            if (!$int or ! $int['id']) {
                $int = $db->query_first("SELECT i.*, DATEDIFF(NOW(), `lastused`) AS `datediff`, DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`,
            f.name AS ownername,
            (CASE WHEN files.dateline IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, files.dateline, NOW()) END) AS uploaded
            FROM interiors i
            LEFT JOIN factions f ON i.faction=f.id
            LEFT JOIN characters_faction cf ON cf.faction_id=f.id
            LEFT JOIN characters c ON cf.character_id=c.id
            LEFT JOIN files ON i.id=files.connected_interior
	WHERE i.id=" . $intid . " AND c.id=" . $charid . " AND f.id=cf.faction_id AND f.id IS NOT NULL AND f.free_custom_ints=1 AND cf.id IS NOT NULL ");
                if (!$int or ! $int['id']) {
                    $db->close();
                    die('This interior is no longer belonged to you or your faction or you\'re not a faction leader or your faction does not have this perk.');
                } else {
                    $isFactionInt = true;
                }
            }

            $uploadCost = $normalPrice;
            if ($isFactionInt) {
                $uploadCost = 0;
            } else {
                if ($int['uploaded'] > -1) {
                    if ($int['uploaded'] <= 8) {
                        $uploadCost = 0;
                    } else if ($int['uploaded'] <= 72) {
                        $uploadCost = ceil($normalPrice / 2);
                    }
                }
            }

            $file = $_FILES['FileInput']['tmp_name'];

            $file_info = finfo_open(FILEINFO_MIME_TYPE);
            $file_type = finfo_file($file_info, $file);
            $file_size = filesize($file);
            $fileData = file_get_contents($file); //file_get_contents($root = realpath($_SERVER["DOCUMENT_ROOT"]) . '\\uploader\\uploads\\custom_interiors\\' . $intid . '.map');

            if ($file_size > $max_allow_file_size or ! $fileData) {
                $db->close();
                die("File is corrupted or exceeded allowed size of " . formatBytes($max_allow_file_size) . ". Please try again.");
            }
            $comment = $_SESSION['username'] . " " . date("d-m-Y");
            // Cleanup old items
            $queries[] = "DELETE FROM `tempobjects` WHERE `dimension`='" . $intid . "'";
            $queries[] = "DELETE FROM `tempinteriors` WHERE `id`='" . $intid . "'";
            // Going to parse the map, try some checks here
            $error = false;

            // Going to parse the map, try some checks here
            $xml = @simplexml_load_string($fileData);
            foreach ($xml->object as $id => $value) {
                $model = $db->escape($value['model']);
                $posX = $db->escape($value['posX']);
                $posY = $db->escape($value['posY']);
                $posZ = $db->escape($value['posZ']);
                $rotX = $db->escape($value['rotX']);
                $rotY = $db->escape($value['rotY']);
                $rotZ = $db->escape($value['rotZ']);
                $alpha = $value['alpha'];
                if (!isset($alpha) or ! $alpha or strlen($alpha) < 1 or ! is_numeric($alpha) or $alpha > 255) {
                    $alpha = 255;
                } else {
                    $alpha = $db->escape($value['alpha']);
                }

                $interior = $int["interior"];

                if (isset($value['doublesided']) and ( $value['doublesided'] == 'true'))
                    $doublesided = 1;
                else
                    $doublesided = 0;

                //changed this from "solid" to "collisions" since map editor uses "collisions" argument, not "solid" --Exciter 11.06.2014
                if (isset($value['collisions']) and ( $value['collisions'] == 'false'))
                    $solid = 0;
                else
                    $solid = 1;

                //added support for scale and breakable --Exciter 11.06.2014
                if (isset($value['scale']))
                    $scale = $db->escape($value['scale']);
                else
                    $scale = 1;

                if (isset($value['breakable']) and ( $value['breakable'] == 'false'))
                    $breakable = 0;
                else
                    $breakable = 1;

                if ($posX > 3000 or $posX < -3000) {
                    $error = true;
                    echo 'Error: Object with model ID ' . $value['model'] . ' is placed outside the would boundaries on the X axis<BR />';
                }

                if ($posY > 3000 or $posY < -3000) {
                    $error = true;
                    echo 'Error: Object with model ID ' . $value['model'] . ' is placed outside the would boundaries on the Y axis<BR />';
                }

                if ($posZ > 3000 or $posZ < -3000) {
                    $error = true;
                    echo 'Error: Object with model ID ' . $value['model'] . ' is placed outside the would boundaries on the Z axis<BR />';
                }


                flush();
                $makequery = "INSERT INTO `tempobjects` (`model`, `posX`, `posY`, `posZ`, `rotX`, `rotY`, `rotZ`, `interior`, `dimension`, `doublesided`,`solid`,`scale`,`breakable`, `alpha`, `comment`) VALUES ('" . $model . "', '" . $posX . "', '" . $posY . "', '" . $posZ . "', '" . $rotX . "', '" . $rotY . "', '" . $rotZ . "', '" . $interior . "', '" . $intid . "', '" . $doublesided . "', '" . $solid . "', '" . $scale . "', '" . $breakable . "', '" . $alpha . "', '" . $comment . "')";
                $queries[] = $makequery;
            }
            $hasmarker = false;
            if (isset($xml->marker)) { // Update the interior spawn location
                foreach ($xml->marker as $id => $value) {
                    $spawnX = $db->escape($value["posX"]);
                    $spawnY = $db->escape($value["posY"]);
                    $spawnZ = $db->escape($value["posZ"]);
                    $spawnInterior = $int["interior"];
                    $queries[] = "INSERT into `tempinteriors` SET `posX`='" . $spawnX . "', `posY`='" . $spawnY . "', `posZ`='" . $spawnZ . "', `interior`='" . $spawnInterior . "', `id`='" . $intid . "', `uploaded_by`='" . $db->escape($userID) . "', `uploaded_at`=NOW(), `amount_paid`='" . $db->escape($uploadCost) . "'";

                    if ($spawnX > 3000 or $spawnX < -3000) {
                        $error = true;
                        echo 'Error: The entrance is placed outside the would boundaries on the X axis<BR />';
                    }

                    if ($spawnY > 3000 or $spawnY < -3000) {
                        $error = true;
                        echo 'Error: The entrance is placed outside the would boundaries on the Y axis<BR />';
                    }

                    if ($spawnZ > 3000 or $spawnZ < -3000) {
                        $error = true;
                        echo 'Error: The entrance is placed outside the would boundaries on the Z axis<BR />';
                    }


                    $hasmarker = true;
                    break; // just the first one, please.
                }
            }

            if (!$hasmarker) {
                $error = true;
                echo 'Error: The map does not have any cylinder(marker/spawnpoint for the exit of interior).<BR />';
            }


            $objectsLimitStandard = 251;
            $objectsLimit = $objectsLimitStandard;
            if (count($queries) > $objectsLimit) {
                $error = true;
                $objectsLimit = $objectsLimit - 1;
                $objectsLimitStandard = $objectsLimitStandard - 1;
                if ($objectsLimit != $objectsLimitStandard) {
                    echo("Error: The map has exceeded the maximum number of objects (" . $objectsLimitStandard . " standard, " . $objectsLimit . " for you (" . $objectsLimitSpecialReason . ")).<BR />");
                } else {
                    echo("Error: The map has exceeded the maximum number of objects (" . $objectsLimitStandard . ").<BR />");
                }
            } else {

            }

            if ($error) {
                $db->close();
                die();
            }

            //Now take GC
            $acc = $db->query_first("SELECT `username`,`credits` FROM `accounts` WHERE id='" . $userID . "' ");
            if ($acc['credits'] < $uploadCost) {
                $error = true;
                echo 'Map processing cancelled. Reason: You lack of GC(s) to use this feature. <br>'
                . 'Please <a href="donate.php">get more GC(s)</a> and then try again.';
            } else {
                $db->query("UPDATE `accounts` SET `credits`=`credits`-" . $uploadCost . " WHERE id='" . $userID . "' ");
                $data = array();
                $perkText = "Custom interior upload for ";
                if ($int['name']) {
                    $perkText .= $int['name'] . " (#" . $intid . ") ";
                } else {
                    $perkText .= "interior ID " . $intid . " ";
                }
                $data['name'] = $perkText;
                $data['cost'] = '-' . $uploadCost;
                $data['account'] = $userID;
                $db->query_insert("don_purchases", $data);
            }

            if ($error) {
                $db->close();
                die();
            }

            $counter = 0;
            foreach ($queries as $id => $query) {
                @$db->query($query);
                $counter = $counter + 1;
            }
            echo "Processed $counter mapping objects.<br>";
            require_once("../classes/Mta.class.php");
            @$mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
            @$mtaServer->getResource("object-system")->call("processCustomInterior", false, false, $intid, $_SESSION['username'], $userID);

            //$db->query("UPDATE interiors SET uploaded_interior=NOW() WHERE uploaded_interior IS NULL AND id=$intid");

            echo ('Map processing finished and interior is ready to use in game!<br>');
            //$db->close();
            //Delete old map file if exists
            /*
              if (file_exists($UploadDirectory . $NewFileName)) {
              chmod($UploadDirectory . $NewFileName, 0777);
              if(!unlink($UploadDirectory . $NewFileName)) {
              chmod($UploadDirectory . $NewFileName, 0000);
              }
              }
             *
             */

            //Delete the previous map file if any.
            $db->query("DELETE FROM files WHERE connected_interior=" . $intid);
            //Start saving new map file.
            $insert = array();
            $insert['desc'] = "Raw .map file for custom interior #$intid ; Uploaded by " . $_SESSION['username'];
            $insert['file'] = $fileData;
            $insert['file_type'] = $file_type;
            $insert['connected_interior'] = $intid;
            $insert['file_size'] = $file_size;
            $qid = $db->query_insert("files", $insert);
            if ($qid) {
                echo('Raw map file has been saved on server for later downloads!');
            } else {
                echo('Error occurred while saving raw map file on server. However this only effects the ability to download raw map file later on, do not reupload as it may charge you GC(s) again.');
            }
            unlink($file);
            $db->close();
        }
    } else {
        die('Nice try.');
    }
}
