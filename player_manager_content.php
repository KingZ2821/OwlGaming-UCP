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

?>

<div id="acc_info" style="margin-bottom: 0px;"> <?php
    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
    } else {
        $perms = $_SESSION['groups'];
        require_once("./functions/functions_player.php");
        if (!canUserManageAdminTeam($perms)) {
            die("<center><h3>You don't access to the OwlGaming MTA Player Manager.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>");
        } else {
            ?>
            <h2><?php if (isset($_SESSION['username'])) echo "Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "!"; ?> Welcome to the OwlGaming MTA Player Manager!</h2>
            <p>There are only a few groups of users who have access to this page, if you're see this, it means you're in one of those permission groups. However, based on the permission group / staff rank you're currently in, you can only view certain information and perform certain actions on other players displaying below:</p>
            <?php if (canUserManageAdminTeam($perms)) { ?>
                <input type="button" id="btn_list_admins" value="All Staff" style="margin-left: 0px; margin-top: 0px; width: auto;" onclick="load_admin_team();"/>
                <?php
            }
        }
    }
    ?>
</div>
<div id="char_info_mid_top" style="margin-bottom: 0px;"></div>
<div id="char_info_mid"></div>
<div id="char_info">

</div>
<script type="text/javascript" src="./js/ajax_player_manager.js"></script>
<?php
?>


