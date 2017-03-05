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

include("header.php");
?>
<div id="main-wrapper">
    <?php
    require_once './classes/Loa.class.php';
    $loa = new Loa();
    if ($loa->can_access_loa()) {
        ?>
        <div id="loa_top">
            <h2>Can I take leave of absence?</h2>
            <ul>
                <li>If you want to take some time off from your staff duty or if you can not be active in-game or on forums for 5 days or longer then you can apply for leave of absence.</li>
                <li>You are not able to fulfill one of your duties for over 5 days, i.e. cannot handle forum-work or cannot do a proper amount of reports in-game due to technical issues.</li>
                <li>You have to go inactive for a long period of time and wish to be able to reinstate when you come back another day.</li>
                <li>You can not apply for a leave of absence if you're still having unsolved tickets assigned to you on Support Center.</li>
            </ul>
            <p><i><u><b>Notice:</b></u></i></p>
            <i>
            <ul>
                <li>Inactivity reports above 21 days are to be handled as departure notices. A staff member cannot go inactive over 21 days. If you do plan to go inactive over 21 days, you are to depart from the team and file a reinstatement request when you have the proper amount of time.</li>
                <li>After applying, you won't be assigned new tickets on Support Center.</li>
                <li>If you're seeing this, it means you're listed as a staff member and you are required to report your inactivity.</li>
            </ul>
            </i>
            
        </div>
        <hr>
        <div id="loa_mid">
            <?php
            $effective_loas = $loa->get_loa();
            $my_loa = $loa->has_loa(null, $effective_loas);
            if ($my_loa) {
                // Show nothing, do nothing as well.
            } else {
                ?>
                <form action="./ajax/ajax_loa.php" method="post">
                    <input type="hidden" name="loa_action" value="set_loa"/>
                    <h2>Apply for a leave of absence</h2>
                    <table border="0" >
                        <tr>
                            <td><b>Length of inactivity: </b></td>
                            <td>
                                <input type="number" name="loa_length" max="21" min="5" step="1" required/> days
                            </td>
                        </tr>
                        <tr>
                            <td><b>Reason for leave: </b></td>
                            <td>
                                <input type="text" name="loa_reason" maxlength="500" required style="width:600px;"/>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="submit" value="Apply"/></td>
                            <td></td>
                        </tr>
                    </table>
                </form>
                <?php
            }
            ?>
        </div>
        <div id="loa_bot" >

        </div>
        <div id="loa_detail">
            <h2>Current effective Leaves of absence</h2>
            <?php
            $loa->output_effective_loas($effective_loas);
            ?>
            <h2>Previously</h2>
            <?php
            $loa->output_previous_loas();
            ?>
        </div>
        <?php
    } else {
        echo "<br><br><center>You must be logged in to access this area.</center>";
    }
    ?>
</div>
<div class="content_wrap">
    <div class="text_holder">
        <div class="features_box">

        </div>	
        <?php
        include("sub.php");
        include("footer.php");
        ?>
        <script type="text/javascript">
            function loa_return(loa_id) {
                if (confirm("Are you sure?")) {
                    $.post("./ajax/ajax_loa.php", {
                        loa_action: "return",
                        loa_id: loa_id,
                    }, function (stuff) {
                        if (stuff == "ok") {
                            if (confirm("You have successfully deleted your Leave of absence, you're now back to active state!\nDo you want to reload this page?")) {
                                location.reload();
                            }
                        } else {
                            alert(stuff);
                        }
                    });
                }
            }
            
            function loa_clean(){
                if (confirm("Are you sure?")) {
                    $.post("./ajax/ajax_loa.php", {
                        loa_action: "clean",
                    }, function (stuff) {
                        if (stuff == "ok") {
                            if (confirm("You have successfully cleaned up all previous Leave of absence\nDo you want to reload this page?")) {
                                location.reload();
                            }
                        } else {
                            alert(stuff);
                        }
                    });
                }
            }
        </script>


