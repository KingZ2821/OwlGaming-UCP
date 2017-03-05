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

if ($_POST['step'] == "reset_change_password") {
    if (isset($_POST['newpass']) and isset($_POST['userid']) and isset($_POST['token'])) {
        require_once '../classes/Database.class.php';
        $db = new Database("MTA");
        $db->connect();
        $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $db->escape($_POST['userid']) . "' ");
        if ($user and $user['id'] and is_numeric($user['id'])) {
            $token = $db->query_first("SELECT * FROM tokens WHERE userid='" . $db->escape($_POST['userid']) . "' AND token='" . $db->escape($_POST['token']) . "' AND action='reset_password' AND date >= NOW() - INTERVAL 10 MINUTE");
            if ($token and $token['userid'] and is_numeric($token['userid'])) {
                $update = array();
                $update['password'] = md5(md5($_POST['newpass']) . $user['salt']);
                if ($db->query("DELETE FROM tokens WHERE id='" . $token['id'] . "'") and $db->query_update("accounts", $update, "id='" . $user['id'] . "'")) {
                    echo "You have successfully changed your MTA account password to '" . $_POST['newpass'] . "'.";
                } else {
                    echo "Internal Error!";
                }
            } else {
                echo "Opps, sorry. We couldn't continue to process the password reset for your account '" . $user['username'] . "'.\n\n "
                . "It looked like this link is expired or invalid.";
            }
        } else {
            echo "Internal Error!";
        }
        $db->close();
    } else {
        echo "Internal Error!";
    }
} else if ($_POST['step'] == "reset_password") {
    if (isset($_POST['clue'])) {
        require_once '../classes/Database.class.php';
        $db = new Database("MTA");
        $db->connect();
        $clue = $db->escape($_POST['clue']);
        $user = $db->query_first("SELECT * FROM accounts WHERE username='" . $clue . "' OR email='" . $clue . "' ");
        if ($user and $user['id'] and is_numeric($user['id'])) {
            $db->query("DELETE FROM tokens WHERE userid='" . $user['id'] . "' ");
            $token = md5(uniqid(mt_rand(), true));
            $insert = array();
            $insert['userid'] = $user['id'];
            $insert['token'] = $token;
            $insert['action'] = $_POST['step'];
            $db->query_insert("tokens", $insert);
            $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
            $host = $_SERVER['HTTP_HOST'];
            $currentUrl = $protocol . '://' . $host;
            $emailContent = "You or someone has requested for a password reset for your MTA account '" . $user['username'] . "' from the OwlGaming UCP.

Please click the link below within 10 minutes to reset password:
" . $currentUrl . "/lostpw.php?userid=" . $user['id'] . "&token=" . $token . "

If you didn't request for this, just simply ignore this email.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

            $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
            $header.= "MIME-Version: 1.0\r\n";
            $header.= "Content-Type: text/plain; charset=utf-8\r\n";
            $header.= "X-Priority: 1\r\n";
            mail($user['email'], "Account Password Reset at OwlGaming MTA Roleplay", $emailContent, $header);
            echo "An email contains a link to reset your password has been dispatched!\n";
        } else {
            echo "*Username or email address does not exist.";
        }
        $db->close();
    }
} else {
    $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
    require_once "$root/classes/Session.class.php";
    $session = new Session();
    $session->start_session('_owlgaming');

    if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
        echo "You must be logged in to access this content.";
    } else {
        if ($_POST['step'] == "load_acc_settings_gui") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            ?>
            <h2>Avatar</h2>
            <script type="text/javascript" src="../uploader/js/jquery-1.10.2.min.js"></script>
            <script type="text/javascript" src="../uploader/js/jquery.form.min.js"></script>

            <script type="text/javascript">
                $(document).ready(function () {
                    var options = {
                        target: '#output', // target element(s) to be updated with server response
                        beforeSubmit: beforeSubmit, // pre-submit callback
                        success: afterSuccess, // post-submit callback
                        uploadProgress: OnProgress, //upload progress callback
                        resetForm: true        // reset the form after successful submit

                    };

                    $('#MyUploadForm').submit(function () {
                        $(this).ajaxSubmit(options);
                        // always return false to prevent standard browser submit and page navigation
                        return false;
                    });
                    $("#process_btn").hide();

                    //function after succesful file upload (when server response)
                    function afterSuccess()
                    {
                        $('#submit-btn').show(); //hide submit button
                        $('#submit-btn').val('Re-upload');
                        $('#process_btn').show();
                        $('#loading-img').hide(); //hide submit button
                        $('#progressbox').delay(1000).fadeOut(); //hide progress bar
                        //$('#current_avatar').slideToggle(500,refresh_current_avatar());
                        d = new Date();
                        $("#current_avatar_img").attr("src", $("#current_avatar_img").attr("src") + "&d=" + d.getTime());
                    }

                    //function to check file size before uploading.
                    function beforeSubmit() {
                        //check whether browser fully supports all File API
                        if (window.File && window.FileReader && window.FileList && window.Blob)
                        {

                            if (!$('#FileInput').val()) //check empty input filed
                            {
                                $("#output").html("Please select a file first!");
                                return false
                            }

                            var fsize = $('#FileInput')[0].files[0].size; //get file size
                            var ftype = $('#FileInput')[0].files[0].type; // get file type


                            //allow file types
                            /*
                             switch (ftype)
                             {
                             case 'text/xml':
                             break;
                             default:
                             $("#output").html("<b>" + ftype + "</b> Unsupported file type!");
                             return false
                             }
                             */

                            //Allowed file size is less than 2 MiB (2048000)
                            if (fsize > 2048000)
                            {
                                $("#output").html("<b>" + bytesToSize(fsize) + "</b> Too big file! <br />File is too big, it should be less than 2 MiB");
                                return false
                            }

                            $('#submit-btn').hide(); //hide submit button
                            $('#loading-img').show(); //hide submit button
                            $("#output").html("");
                            $("#process_btn").hide();
                        }
                        else
                        {
                            //Output error to older unsupported browsers that doesn't support HTML5 File API
                            $("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
                            return false;
                        }
                    }

                    //progress bar function
                    function OnProgress(event, position, total, percentComplete)
                    {
                        //Progress bar
                        $("#process_btn").hide();
                        $('#progressbox').show();
                        $('#progressbar').width(percentComplete + '%') //update progressbar percent complete
                        $('#statustxt').html(percentComplete + '%'); //update status text
                        if (percentComplete > 50)
                        {
                            $('#statustxt').css('color', '#000'); //change status text to white after 50%
                        }
                    }

                    //function to format bites bit.ly/19yoIPO
                    function bytesToSize(bytes) {
                        var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                        if (bytes == 0)
                            return '0 Bytes';
                        var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                        return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
                    }

                });

            </script>
            <link href="../uploader/style/style.css" rel="stylesheet" type="text/css">
            <div id="upload-wrapper">
                <table border="0" width="100%">
                    <tr>
                        <td width="30%">
                            <div id="current_avatar"><img id="current_avatar_img" src="../avatar.php?id=<?php echo $_SESSION['userid']; ?>" width="100%"></div>
                        </td>
                        <td>
                            <div align="center">
                                <h3>Change Avatar</h3>
                                <form action="../uploader/avatar_uploader_processor.php" method="post" enctype="multipart/form-data" id="MyUploadForm">
                                    <input name="FileInput" id="FileInput" type="file" accept="image/*"/>
                                    <input type="submit"  id="submit-btn" value="Upload" />
                                    <img src="../uploader/images/ajax-loader.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
                                </form>
                                <div id="progressbox" ><div id="progressbar"></div ><div id="statustxt">0%</div></div>
                                <div id="output"></div>
                            </div>
                        </td>
                    </tr>
                </table>

            </div>
            <br>
            <?php
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $_SESSION['userid'] . "' ");
            ?>
            <h2>Password</h2>
            <p>This password is used to log into this UCP and the MTA Server. It's always a good idea to change it up every once in awhile..</p>
            <form onsubmit="ajax_change_password();
                                return false;">
                <table>
                    <tr>
                        <td>
                            <b>Current password:</b>
                        </td>
                        <td>
                            <input id="curpass" type="password" maxlength="100" required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>New password:</b>
                        </td>
                        <td>
                            <input id="newpass1" type="password" maxlength="100" required="true">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Retype password:</b>
                        </td>
                        <td>
                            <input id="newpass2" type="password" maxlength="100" required="true">
                        </td>
                    </tr>
                </table>
                <br>
                <input id="changepass" type="submit" value="Change password">
                <input id="resetpass" type="button" value="Reset password" onclick="ajax_reset_password('<?php echo $_SESSION['username']; ?>');">
            </form>
            <hr>
            <h2>Email Address</h2>
            <p>This email address is used to recovery your lost password, confirmations, validations and other important actions. It's very important to have a real and working email address. Changing account email address is possible, however it will require confirmations from both of your current and new email addresses.</p>
            <form onsubmit="ajax_change_email('<?php echo $user['email']; ?>');
                                return false;">
                <table>
                    <tr>
                        <td>
                            <b>Email:</b>
                        </td>
                        <td>
                            <input id="email" type="email" maxlength="100" required="true" value="<?php echo $user['email']; ?>">
                        </td>
                    </tr>
                </table>
                <br>
                <input id="changeemail" type="submit" value="Change email">
            </form>
            <hr>
            <h2>Serial Whitelist</h2>
            <p>Serials are used by MTA server and server administrators to reliably identify a PC that a player is using. They are bound to the software and hardware configuration. Serials are 32 characters long and cointain letters and numbers. </p>
            <p>Serials are the most accurate form of identifying players that MTA has. By default, you're allowed to connect to OwlGaming MTA server from any PC. However, allowing only connections from certain PC(s) by making a whitelist of serials can greatly improve your account security. Hacker won't be able to login to your account from a strange PC even when your password is completely exposed.</p>
            <p>It's always recommended to have at least one serial of your favorite PC added to the serial whitelist.<br>
                You can retrieve serial number from a PC by typing command "serial" in your MTA's console (press F8).</p>
            <p><font color='red'><i><small>*Serial whitelist is only optional for regular players. All MTA staff members are required to add at least one serial number to be able to login MTA server.*</small></i></font></p>
            <?php
            $query = $db->query("SELECT *, TIME_TO_SEC(TIMEDIFF(NOW(),last_login_date))/3600 AS 'hourdiff', DATE_FORMAT(last_login_date,'%b %d, %Y at %h:%i %p') AS 'last_login_date',DATE_FORMAT(creation_date,'%b %d, %Y at %h:%i %p') AS 'creation_date'  FROM serial_whitelist WHERE userid='" . $_SESSION['userid'] . "' ORDER BY id");
            $count = 1;
            ?>
            <p><b>Only allow connections from the following whitelist (<?php echo $db->affected_rows(); ?>/<?php echo $user['serial_whitelist_cap']; ?>):</b></p>
            <form onsubmit="ajax_add_new_serial();
                                return false;">
                <table id="logtable" border="1" align=center width="100%">
                    <tr>
                        <td align=center width=20><b>No.</b></td>
                        <td align=center ><b>Serial Number</b></td>
                        <td align=center ><b>Status</b></td>
                        <td align=center><b>Last Connection</b></td>
                        <td align=center><b>Creation Date</b></td>
                        <td align=center><b>Actions</b></td>
                    </tr>
                    <?php
                    if ($db->affected_rows() > 0) {
                        while ($serial = $db->fetch_array($query)) {
                            $last_login = 'Never';
                            if ($serial['last_login_date'] and $serial['last_login_ip']) {
                                $hoursAgo = round($serial['hourdiff']);
                                if ($hoursAgo < 1) {
                                    $hoursAgo = 'Less than an hour ago';
                                } else {
                                    $hoursAgo = 'About ' . $hoursAgo . ' hour(s) ago';
                                }
                                $last_login = $serial['last_login_date'] . " (" . $hoursAgo . ")<br>From " . $serial['last_login_ip'];
                            }
                            $status = "<font color='red'>Email activation required</font>";
                            if ($serial['status'] == 1) {
                                $status = "<font color='green'>Active</font>";
                            }
                            echo "<tr><td align=center>" . $count . "</td><td align=center>" . $serial['serial'] . "</td><td align=center>" . $status . "</td><td align=center>" . $last_login . "</td><td align=center>" . $serial['creation_date'] . "</td><td width=10% align=center><input id='remove_serial_btn_" . $serial['id'] . "' onclick='ajax_remove_serial(" . $serial['id'] . ");' type='button' value='Remove' style='margin-left: 0px; margin-top: 0px; width: 100%;' /></td></tr>";
                            $count+=1;
                        }
                    }
                    ?>
                    <tr>
                        <td align=center><?php echo $count; ?></td>
                        <td colspan='4' align=center><input id='new_serial' placeholder="Enter new serial number" required maxlength="32" style='width: 95%'></td>
                        <td align=center><input type='submit' value='Add' id='add_new_serial' style='margin-left: 0px; margin-top: 0px; width: 100%;'></td>
                    </tr>
                </table>
            </form>
            <br>
            <hr>
            <h2>Two-Factor Authentication</h2>
            <p>Two-Factor Authentication lets you ensure only trusted networks and PCs have access to your MTA account on both MTA server and UCP, by using your smartphone to validate login attempts from new IP addresses or PC's serials.</p>
            <p>Our two-factor authentication system adds another security layer by using Google Authenticator to pair a member's MTA account with their smartphone app. A "Recovery Key" shown on-screen during setup ensures that if a member should ever lose their phone, they can regain access to their account.</p>
            <div id='twofactor_details'>
                <?php
                $twofactor = $db->query_first("SELECT enabled FROM google_authenticator WHERE userid=" . $db->escape($_SESSION['userid']));
                if (!$twofactor or is_null($twofactor['enabled']) or ! is_numeric($twofactor['enabled']) or $twofactor['enabled'] == 0) {
                    ?>
                    <input id="enableTwoFactor" type="button" value="Enable Two-Factor Authentication" onclick="enable_two_factor('<?php echo $_SESSION['userid']; ?>', 1);">
                    <?php
                } else {
                    ?>
                    <input id="enableTwoFactor" type="button" value="Disable Two-Factor Authentication" onclick="enable_two_factor('<?php echo $_SESSION['userid']; ?>', 3);">
                    <?php
                }
                $db->close();
                ?>
            </div>
            <?php
        } else if ($_POST['step'] == "changepassword") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $_SESSION['userid'] . "'");
            if (md5(md5($_POST['curpass']) . $user['salt']) != $user['password']) {
                echo('*Current password is incorrect!*');
            } else {
                $update = array();
                $update['password'] = md5(md5($_POST['newpass']) . $user['salt']);
                //$update['activated'] = 0;
                if (!$db->query_update("accounts", $update, "id='" . $_SESSION['userid'] . "'")) {
                    echo $db->oops();
                } else {
                    $db->query("DELETE FROM tokens WHERE userid='" . $user['id'] . "' ");
                    $token = md5(uniqid(mt_rand(), true));
                    $insert = array();
                    $insert['userid'] = $_SESSION['userid'];
                    $insert['token'] = $token;
                    $insert['action'] = $_POST['step'];
                    $db->query_insert("tokens", $insert);
                    $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
                    $host = $_SERVER['HTTP_HOST'];
                    $currentUrl = $protocol . '://' . $host;
                    $emailContent = "You or someone has changed your account password from the OwlGaming UCP.

If you didn't perform this action, please click this link within 24 hour to deactivate your account for safety:
" . $currentUrl . "/deactivate.php?userid=" . $_SESSION['userid'] . "&token=" . $token . "
You can anytime re-activate your account by using password recovery feature on our UCP later on.

If you're the one who performed this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

                    $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                    $header.= "MIME-Version: 1.0\r\n";
                    $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                    $header.= "X-Priority: 1\r\n";
                    mail($user['email'], "Account Password Changed at OwlGaming MTA Roleplay", $emailContent, $header);
                    echo "You have successfully changed your MTA account password!\n";
                }
            }
            $db->close();
        } else if ($_POST['step'] == "change_email_step_1") {
            if (isset($_POST['curMail']) and isset($_POST['newMail'])) {
                require_once '../classes/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $user = $db->query_first("SELECT * FROM accounts WHERE id='" . $_SESSION['userid'] . "' AND email='" . $db->escape($_POST['curMail']) . "'");
                if ($user and $user['id'] and is_numeric($user['id'])) {
                    $mail = $db->query_first("SELECT id FROM accounts WHERE email='" . $db->escape($_POST['newMail']) . "' LIMIT 1");
                    if (!($mail and $mail['id'] and is_numeric($mail['id'])) and (intval($user['activated']) == 1)) {
                        $db->query("DELETE FROM tokens WHERE userid='" . $_SESSION['userid'] . "' ");
                        $token = md5(uniqid(mt_rand(), true));
                        $insert = array();
                        $insert['userid'] = $_SESSION['userid'];
                        $insert['token'] = $token;
                        $insert['data'] = $_POST['newMail'];
                        $insert['action'] = $_POST['step'];
                        $db->query_insert("tokens", $insert);
                        $protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
                        $host = $_SERVER['HTTP_HOST'];
                        $currentUrl = $protocol . '://' . $host;
                        $emailContent = "You or someone has request an account email change from '" . $_POST['curMail'] . "' to '" . $_POST['newMail'] . "' from the OwlGaming UCP.

Please click this link within 10 minutes to proceed to next step:
" . $currentUrl . "/changemail.php?userid=" . $_SESSION['userid'] . "&token=" . $token . "

If you're the one who performed this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

                        $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                        $header.= "MIME-Version: 1.0\r\n";
                        $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                        $header.= "X-Priority: 1\r\n";
                        mail($user['email'], "Account Email Change Request at OwlGaming MTA Roleplay", $emailContent, $header);
                        echo "An email has been dispatched to your current email address '" . $_POST['curMail'] . "'.\n\n"
                        . "Please check your email's inbox for further instructions.";
                    } elseif (!($mail and $mail['id'] and is_numeric($mail['id'])) and (intval($user['activated']) == 0)) {
                        $update = array();
                        $update['email'] = $_POST['newMail'];
                        if ($db->query_update("accounts", $update, "id='" . $_SESSION['userid'] . "'") and $db->query("DELETE FROM tokens WHERE userid='" . $_SESSION['userid'] . "' ")) {
                            $emailContent = "You or someone has changed an account email change from '" . $_POST['curMail'] . "' to '" . $update['email'] . "' from the OwlGaming UCP.

        If you aren't the one who performed this action, you should try to login to your MTA account immediately and submit a support ticket on our UCP.

        Sincerely,
        OwlGaming Community
        OwlGaming Development Team";
                            $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                            $header.= "MIME-Version: 1.0\r\n";
                            $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                            $header.= "X-Priority: 1\r\n";
                            mail($_POST['curMail'], "Account Email Changed at OwlGaming MTA Roleplay", $emailContent, $header);

                            echo "Congratulations! You have successfully changed your MTA account's email address from '" . $_POST['curMail'] . "' to '" . $update['email'] . "'!";
                        } else {
                            echo "Opps, sorry. We couldn't continue to process the email change request. "
                            . "Please try again later.";
                        }
                    } else {
                        echo "Opps, sorry. The email address '" . $_POST['newMail'] . "' is already in use.";
                    }
                } else {
                    echo 'Internal Error!';
                }
                $db->close();
            } else {
                echo 'Internal Error!';
            }
        } else if ($_POST['step'] == "add_new_serial") {
            if (isset($_POST['serial'])) {
                require_once '../classes/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $serial = $db->query_first("SELECT * FROM serial_whitelist WHERE serial='" . $db->escape($_POST['serial']) . "'");
                if ($serial and $serial['id'] and is_numeric($serial['id'])) {
                    if ($serial['userid'] == $_SESSION['userid']) {
                        echo "You have already added this serial number.";
                    } else {
                        echo "Sorry this serial number has been already in use to another account.";
                    }
                } else {
                    $addedSerials = $db->query_first("SELECT COUNT(*) AS 'total' FROM serial_whitelist WHERE userid='" . $_SESSION['userid'] . "'");
                    $user = $db->query_first("SELECT serial_whitelist_cap FROM accounts WHERE id='" . $_SESSION['userid'] . "'");
                    if ($addedSerials['total'] < $user['serial_whitelist_cap']) {
                        $insert = array();
                        $insert['serial'] = $_POST['serial'];
                        $insert['userid'] = $_SESSION['userid'];
                        if ($db->query_insert("serial_whitelist", $insert)) {
                            require_once '../functions/functions_account.php';
                            $token = makeToken($db, $_SESSION['userid'], $_POST['step'], $_POST['serial']);
                            if ($token) { // This UCP is shit doe
                                $emailContent = "You or someone has added new serial number '" . $_POST['serial'] . "' to your MTA account '" . $_SESSION['username'] . "' from the OwlGaming UCP.

Please click this link within 10 minutes to activate the serial:
http://owlgaming.net/serial.php?userid=" . $_SESSION['userid'] . "&token=" . $token[0] . "

If you didn't perform this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

                                $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                                $header.= "MIME-Version: 1.0\r\n";
                                $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                                $header.= "X-Priority: 1\r\n";
                                mail($_SESSION['email'], "Serial Whitelist Activation at OwlGaming MTA Roleplay", $emailContent, $header);
                                echo "ok";
                            } else {
                                echo "Error, token was not set";
                            }
                        } else {
                            echo "Error, could not insert whitelist";
                        }
                    } else {
                        echo ($user['serial_whitelist_cap'] * $user['serial_whitelist_cap']);
                    }
                }
                $db->close();
            } else {
                echo "Opps, sorry. We couldn't add new serial. \n\nConnection to server seemed broken, please try again later.";
            }
        } else if ($_POST['step'] == "remove_serial") {
            if (isset($_POST['serialid'])) {
                require_once '../classes/Database.class.php';
                $db = new Database("MTA");
                $db->connect();
                $serial = $db->query_first("SELECT * FROM serial_whitelist WHERE id='" . $db->escape($_POST['serialid']) . "'");
                if ($serial['status'] == 0) {
                    if ($db->query("DELETE FROM serial_whitelist WHERE id='" . $db->escape($_POST['serialid']) . "'")) {
                        echo "ok";
                    } else {
                        echo "Opps, sorry. We couldn't remove that serial. \n\nConnection to server seemed broken, please try again later.";
                    }
                } else {
                    require_once '../functions/functions_account.php';
                    $token = makeToken($db, $_SESSION['userid'], $_POST['step'], $serial['serial']);
                    if ($token) {
                        $emailContent = "You or someone has requested to remove an active serial number '" . $serial['serial'] . "' from your MTA account '" . $_SESSION['username'] . "' from the OwlGaming UCP.

Please click this link within 10 minutes to deactivate and remove the serial:
" . $token[1] . "/serial.php?userid=" . $_SESSION['userid'] . "&token=" . $token[0] . "

If you didn't perform this action, just simply ignore this notice.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

                        $header = "From: " . EMAIL_DEFAULT_FROM_NAME . " <" . EMAIL_DEFAULT_FROM_ADDRESS . ">\r\n";
                        $header.= "MIME-Version: 1.0\r\n";
                        $header.= "Content-Type: text/plain; charset=utf-8\r\n";
                        $header.= "X-Priority: 1\r\n";
                        mail($_SESSION['email'], "Serial Whitelist Deactivation at OwlGaming MTA Roleplay", $emailContent, $header);
                        echo "ok-email";
                    } else {
                        echo "Opps, sorry. We couldn't remove that serial. \n\nConnection to server seemed broken, please try again later.";
                    }
                }
                $db->close();
            }
        } else if ($_POST['step'] == "increase_serial_cap") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $curCap = $db->query_first("SELECT serial_whitelist_cap AS cap FROM accounts WHERE id=" . $_SESSION['userid'])['cap'];
            require_once '../functions/functions_account.php';
            $takeGC = takeGC($db, $_SESSION['userid'], ceil($curCap * $curCap * 50), "Additional serial whitelist capacity (" . ($curCap + 1) . ")");
            if (!$takeGC[0]) {
                if ($takeGC[1] == "You lack of GC(s) to purchase this item.") {
                    echo "lackGC";
                } else {
                    echo "Opps, sorry. We couldn't process that request. \n\nConnection to server seemed broken, please try again later.";
                }
            } else {
                if ($db->query("UPDATE accounts SET serial_whitelist_cap=serial_whitelist_cap+1 WHERE id='" . $_SESSION['userid'] . "'")) {
                    echo "ok";
                } else {
                    echo "Opps, sorry. We couldn't process that request. \n\nConnection to server seemed broken, please try again later.";
                }
            }
            $db->close();
        } else if ($_POST['step'] == "load_twofactor_setup") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $twofactor = $db->query_first("SELECT * FROM google_authenticator WHERE userid=" . $db->escape($_POST['userid']));
            $data = array();
            if (!$twofactor or is_null($twofactor['enabled']) or ! is_numeric($twofactor['enabled'])) { // if nothing in db, create one.
                require_once '../classes/TwoFactor.class.php';
                require_once '../functions/base_functions.php';
                $ga = new TwoFactor();
                $data['secret'] = $ga->createSecret();
                $data['userid'] = $_POST['userid'];
                $data['recovery_code'] = generate_key_string();
                $data['ip'] = get_client_ip();
                $data['qr_url'] = $ga->getQRCodeGoogleUrl('OwlGaming-UCP', $data['secret']);
                if (!is_numeric($db->query_insert("google_authenticator", $data))) {
                    $db->close();
                    die("Opps, something went wrong while enabling Two-Factor Authenticator.");
                }
            } else {
                if ($twofactor['enabled'] == 1) {
                    $db->close();
                    die("You have previously and already enabled Two-Factor Authentication.");
                } else {
                    $data['secret'] = $twofactor['secret'];
                    $data['userid'] = $twofactor['userid'];
                    $data['recovery_code'] = $twofactor['recovery_code'];
                    $data['ip'] = $twofactor['ip'];
                    $data['qr_url'] = $twofactor['qr_url'];
                }
            }
            $db->close();
            echo '<form onsubmit="enable_two_factor(' . $data['userid'] . ',2); return false;">
                <table id="logtable" border="1" align=center width="100%">
    <tr>
        <td><b>QR Code</b></td>
        <td>
            <img src="' . $data['qr_url'] . '" ><br>
            <i>Download and install Google Authenticator from your smartphone\'s app store (Supported Android, Blackberry and iPhone).<br>
                Scan this QR Code with your Google Authenticator smartphone app.</i>
        </td>
    </tr>
    <tr>
        <td><b>Recovery key</b></td>
        <td>
            <div style="text-align:center; font-size:36px; font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New; background-color:#EEEEEE; padding:20px; padding:20px; border: 1px solid #000000;">' . $data['recovery_code'] . '</div>
            <i><b>IMPORTANT!</b> Write down or print out your recovery key. <br>Should you lose access to your Google Account or lose your phone, you will be unable to deactivate this authenticator without this recovery key.</i>
        </td>
    </tr>
    <tr>
        <td><b>Code Validation</b></td>
        <td>
            <input id="twofactor_confirm_code" placeholder="" required maxlength="6" style="width: 50%"><br>
            <i>Enter the code generated by your Google Authenticator smartphone app to complete the setup process.</i>
        </td>
    </tr>
</table><br>
<input id="twofactor_finalize" type="submit" value="Save"> <input id="" type="button" value="Cancel" onclick="ajax_load_acc_settings();">
</form>';
        } else if ($_POST['step'] == "finalize_twofactor_setup") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $twofactor = $db->query_first("SELECT * FROM google_authenticator WHERE userid=" . $db->escape($_POST['userid']));
            $db->close();
            if (!$twofactor or is_null($twofactor['enabled']) or ! is_numeric($twofactor['enabled']) or $twofactor['enabled'] == 1) {
                $db->close();
                die("Opps, something went wrong while finalizing Two-Factor Authenticator.");
            }
            require_once '../classes/TwoFactor.class.php';
            $ga = new TwoFactor();
            $currentTimeSlice = floor(time() / 30);
            $discrepancy = 1;
            $done = false;
            for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
                $calculatedCode = $ga->getCode($twofactor['secret'], $currentTimeSlice + $i);
                if ($calculatedCode == $_POST['key']) {
                    //Now add new IP to whitelist.
                    $ga->add_ip(get_client_ip(), $twofactor['ip']);
                    echo "Two-Factor Authentication has been successfully enabled!";
                    $done = true;
                }
            }
            if (!$done) {
                echo "Opps, something went wrong while finalizing Two-Factor Authenticator.";
            }
        } else if ($_POST['step'] == "load_twofactor_removal") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $twofactor = $db->query_first("SELECT * FROM google_authenticator WHERE enabled=1 AND userid=" . $db->escape($_POST['userid']));
            if (!$twofactor or is_null($twofactor['enabled']) or ! is_numeric($twofactor['enabled']) or $twofactor['enabled'] == 0) { // if nothing in db
                $db->close();
                die("Opps, something went wrong while disabling Two-Factor Authenticator.");
            } else {
                echo '<form onsubmit="enable_two_factor(' . $_POST['userid'] . ',4); return false;">
                <table id="logtable" border="1" align=center width="100%">
    <tr>
        <td><b>Code Validation</b></td>
        <td>
            <input id="twofactor_confirm_code" placeholder="" required maxlength="20" style="width: 50%"><br>
            <i>Enter either your Recovery Key, or a valid code from your Google Authenticator smartphone app.</i>
        </td>
    </tr>
</table><br>
<input id="twofactor_remove" type="submit" value="Remove"> <input id="" type="button" value="Cancel" onclick="ajax_load_acc_settings();">
</form>';
            }
            $db->close();
        } else if ($_POST['step'] == "remove_twofactor") {
            require_once '../classes/Database.class.php';
            $db = new Database("MTA");
            $db->connect();
            $twofactor = $db->query_first("SELECT * FROM google_authenticator WHERE enabled=1 AND userid=" . $db->escape($_POST['userid']));
            if (!$twofactor or is_null($twofactor['enabled']) or ! is_numeric($twofactor['enabled']) or $twofactor['enabled'] == 0) {
                $db->close();
                die("Opps, something went wrong while disabling Two-Factor Authenticator.");
            }
            require_once '../classes/TwoFactor.class.php';
            $ga = new TwoFactor();
            if (($_POST['key'] == $ga->getCode($twofactor['secret'])) or ( strtoupper($_POST['key']) == $twofactor['recovery_code'])) {
                if ($db->query("DELETE FROM google_authenticator WHERE secret='" . $twofactor['secret'] . "'")) {
                    unset($_SESSION['ga_ip']);
                    echo "Two-Factor Authentication has been successfully disabled!";
                } else {
                    echo "Opps, something went wrong while disabling Two-Factor Authenticator.";
                }
            } else {
                echo "Code Validation was incorrect!";
            }
            $db->close();
        }
    }
}
