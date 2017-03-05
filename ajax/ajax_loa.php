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

require_once "../classes/Loa.class.php";
$loa = new Loa();
if ($loa->can_access_loa()) {
    $action = isset($_POST['loa_action']) ? $_POST['loa_action'] : false;
    if ($action == "set_loa") {
        require_once '../classes/Supportcenter.class.php';
        $sc = new Supportcenter();
        if ($sc->can_access_sp_backend()) {
            $assigned_tcs = $sc->has_ongoing_ticket_assigned();
            if ($assigned_tcs and $assigned_tcs > 0) {
                //header("Location: /loa.php");
                echo("<script>alert('You are still having $assigned_tcs unsolved ticket(s) assigned to you on Support Center. You can not simply abandon them like that, please manage to solve all of them or reassign them to other appropriate staff members to deal with.');"
                        . "self.location='../support.php';</script>");
                exit();
            }
        }
        $length = isset($_POST['loa_length']) ? $_POST['loa_length'] : false;
        $reason = isset($_POST['loa_reason']) ? $_POST['loa_reason'] : false;
        $success = $loa->set_loa($length, $reason);
        if ($success) {
            header("Location: /loa.php");
        } else {
            echo "<script>alert('Errors occurred while creating a new LOA.');</script>";
        }
    } else if ($action == 'return') {
        if ($loa->return_from_loa($_POST['loa_id'])) {
            echo 'ok';
        } else {
            echo 'Errors occurred while deleting LOA.';
        }
    } else if ($action == 'clean') {
        if ($loa->clean()) {
            echo 'ok';
        } else {
            echo 'Errors occurred while deleting LOA.';
        }
    } else {
        header("Location: /loa.php");
    }
} else {
    header("Location: /loa.php");
}