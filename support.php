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
ob_start();
include("header.php");
require_once './functions/functions_tickets.php';
?>
<div id="main-wrapper">
    <div id="lib_top" style="margin-bottom: 20px;">
        <h2>OwlGaming Support Center</h2>
        Welcome to OwlGaming Support Center. Raise a request using one of the options below.<!--<font color="red"><i>*The Support Center is still a work in progress and not be in use yet. For now, please visit <a href="http://forums.owlgaming.net/forms.php?do=forms">http://forums.owlgaming.net/forms.php?do=forms</a> instead.*</i></font>-->
        <div id="tc_switch" style="display: inline;margin:0;padding:0"><?php
            if (isset($_SESSION['userid']) and canUserAccessTcBackEnd($_SESSION['groups'])) {
                if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                    require_once './classes/TwoFactor.class.php';
                    $twofactor = new TwoFactor();
                    if ($twofactor->is_two_factor_enabled() and !$twofactor->is_two_factor_valid()) {
                        header('Location: /twofactor.php');
                        exit();
                    }
                    echo "<br>You're current on <b>Support Center Back-End</b>. Switch to <a href='#' onclick='tc_switch(0); return false;'>Front-End Interface</a>.";
                } else {
                    echo "<br>You're current on <b>Support Center Front-End</b>. Switch to <a href='#' onclick='tc_switch(1); return false;'>Back-End Interface</a>.";
                }
            }
            ?></div>


        <table border=0 width="100%">
            <tr>
                <td align="left">
                    <?php
                    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                        ?>
                        <h2>Ticket Look Up</h2>
                        <p><a href="#" onclick="ticket_search_load_results(0, 10, 'custom', 'all', 1, 1, 'lib_mid_top');
                                return false;"><b>All Tickets</b></a><br><i>Lists all kinds of tickets.</i></p>

                        <p><a href="#" onclick="ticket_search_load_results(0, 10, 'custom', 'active', 0, 0, 'lib_mid_top');
                                return false;"><b>Active Tickets</b></a><br><i>Lists all tickets those are not closed or locked.</i></p>

                        <p><a href="#" onclick="ticket_search_load_results(0, 10, 'custom', 'unassigned', 1, 1, 'lib_mid_top');
                                return false;"><b>Unassigned Tickets</b></a><br><i>Lists all unassigned tickets.</i></p>

                        <p><a href="#" onclick="ticket_search_load_results(0, 10, 'custom', 'assigned', 0, 0, 'lib_mid_top');
                                return false;"><b>Assigned Tickets</b></a><br><i>Lists all assigned tickets, except closed and locked.</i></p>

                        <p><a href="#" onclick="ticket_search_load_results(0, 10, 'custom', 'closed', 1, 0, 'lib_mid_top');
                                return false;"><b>Closed Tickets</b></a><br><i>Lists all closed tickets.</i></p>

                        <p><a href="#" onclick="ticket_search_load_results(0, 10, 'custom', 'locked', 0, 1, 'lib_mid_top');
                                return false;"><b>Locked Tickets</b></a><br><i>Lists all locked & archived tickets.</i></p>
                        <hr>
                        <table>
                            <tr>
                                <td width="100"><b>Search By:</b></td>
                                <td>
                                    <select id="search_ticket_by" onchange="load_form_search_ticket();
                                            " style="width:200px;">
                                        <option value="none" selected>None</option>
                                        <option value="id">Ticket ID</option>
                                        <option value="type">Type</option>
                                        <option value="status">Status</option>
                                        <option value="assign_to">Assignee</option>
                                        <option value="creator">Creator</option>
                                        <option value="subcriber">Subscriber</option>
                                        <option value="date">Creation Date</option>
                                        <option value="subject">Subject</option>
                                        <option value="content">Content</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <div id="gui_search_ticket"></div>
                    </td>
                    <td align="center">
                        <img src="./images/support_team_be.jpg">
                    </td>
                    <?php
                } else {
                    ?>
                <p><a href="#" onclick="load_submit_form(1);
                        return false;"><b>Account issue</b></a><br><i>Problems logging in, creating new account, serial, IP, etc..</i></p>
                <p><a href="#" onclick="load_submit_form(2);
                        return false;"><b>Unban request </b></a><br><i>Appeal yourself to be unbanned from MTA or forums.</i></p>
                <p><a href="#" onclick="load_submit_form(8);
                        return false;"><b>History appeal</b></a><br><i>Battle wrongly given jails, bans, or any other administrative history put on your account.</i></p>
                <p><a href="#" onclick="load_submit_form(3);
                        return false;"><b>Refund request</b></a><br><i>Request a refund for lost money, vehicles, interiors, items or other assets.</i></p>
                <p><a href="#" onclick="load_submit_form(4);
                        return false;"><b>Donation issue or question</b></a><br><i>Having issue donating to our servers or questions about GameCoins and perks.</i></p>
                <p><a href="#" onclick="load_submit_form(7);
                        return false;"><b>Player report</b></a><br><i>Our aim is to provide an environment where rule breakers are disciplined accordingly for their actions.</i></p>
                <p><a href="#" onclick="load_submit_form(9);
                        return false;"><b>Staff report</b></a><br><i>If you feel you do not agree with the decision, action or behavior of a staff member. </i></p>
                <p><a href="http://bugs.owlgaming.net" target="_blank" onclick="return true;
                        load_submit_form(6);
                        return false;"><b>Bug report</b></a><br><i>Your feedback goes a long way towards making OwlGaming even better.</i></p>
                <p><a href="#" onclick="load_submit_form(5);
                        return false;"><b>General question</b></a><br><i>Anything you have in doubt, our support team is always willing to clear it for you.</i></p>

                </td>
                <td align="center">
                    <img src="./images/support_team_fe.jpg">
                </td>
                <?php
            }
            ?>

            </tr>
        </table>

    </div>
    <div id="lib_mid_top" ></div>
    <div id="lib_mid" ></div>
    <div id="lib_bot"></div>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>

        <script type="text/javascript" src="js/ajax_support.js"/></script>

        <?php if (isset($_SESSION['userid'])) { ?>
            <script>load_my_tickets(10, 0, 'lib_mid');</script>
            <?php
        }
        if (isset($_GET['tcid'])) {
            $tcid = strip_tags($_GET['tcid']);
            if (is_numeric($tcid)) {
                ?>
                <script>load_ticket('<?php echo $tcid; ?>')</script>
            <?php
            }
        }
        ?>


