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
<div style='position: relative;margin-top:-10px'>
    <table border=0 margin=0>
        <tr>
            <td>
                <a href='ucp.php?action=settings' target='_self'><img width='30' height='30' class='avatar' src='../avatar.php?id=<?php echo $_SESSION['userid']; ?>' alt='Change Avatar' title="Change Avatar"/></a>
            </td>
            <td>
                <p> Logged in as <?php echo $_SESSION['username']; ?><br>
                    <a href='#' onClick='ajax_logout_box(); return false;'>Logout</a></p>
            </td>
        </tr>
    </table>
</div>
