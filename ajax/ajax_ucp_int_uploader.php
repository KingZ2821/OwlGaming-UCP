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

if (!isset($_SESSION['userid'])) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    if (isset($_POST['intid']) and isset($_POST['intid'])) {
        require_once '../functions/base_functions.php';
        $userID = $_SESSION['userid']; //This make sure noone edit other char
        require_once("../classes/Database.class.php");
        $db = new Database("MTA");
        $db->connect();
        $intid = $db->escape($_POST['intid']);
        $charid = $db->escape($_POST['charid']);
        $isFactionInt = false;
        $int = $db->query_first("   SELECT  i.*, DATEDIFF(NOW(), `lastused`) AS `datediff`, DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`, 
                                            charactername AS ownername, 
                                            (CASE WHEN f.dateline IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, f.dateline, NOW()) END) AS uploaded, 
                                            f.file_size, f.file_type, DATE_FORMAT(f.dateline ,'%b %d, %Y %h:%i %p') AS last_uploaded  
                                    FROM interiors i LEFT JOIN characters c ON i.owner=c.id 
                                    LEFT JOIN files f ON i.id=f.connected_interior 
                                    WHERE i.id='" . $intid . "' AND i.owner=" . $charid);
        if (!$int or ! $int['id']) {
            $int = $db->query_first("   SELECT  i.*, DATEDIFF(NOW(), `lastused`) AS `datediff`, DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`, 
                                                f.name AS ownername, 
                                                (CASE WHEN files.dateline IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, files.dateline, NOW()) END) AS uploaded,
                                                files.file_size, files.file_type, DATE_FORMAT(files.dateline ,'%b %d, %Y %h:%i %p') AS last_uploaded 
                                        FROM interiors i 
                                        LEFT JOIN characters_faction cf ON i.faction=cf.faction_id
                                        LEFT JOIN factions f ON f.id=cf.faction_id
                                        LEFT JOIN characters c ON c.id=cf.character_id 
                                        LEFT JOIN files ON i.id=files.connected_interior  
                                        WHERE i.id=$intid AND c.id=$charid AND i.id IS NOT NULL AND cf.id IS NOT NULL AND f.id IS NOT NULL AND f.free_custom_ints=1 AND c.id IS NOT NULL");
            if (!$int or ! $int['id']) {
                $db->close();
                die('This interior is no longer belonged to you or your faction or you\'re not a faction leader or your faction does not have this perk.');
            } else {
                $isFactionInt = true;
            }
        }
        $db->close();
        ?>
        <h2>Custom interior upload for '<?php echo $int['name']; ?>'</h2>

        <table border="0" cellpadding="20">
            <tr>
                <td >
                    <table border="0" align=center class="nicetable" style="padding:10px;">
                        <tr><td colspan=3 align=center><img src="../images/interiordesign.png"/></td></tr>
                        <tr>
                            <td><b>Interior Name</td><td>:</td>
                            <td>  <?php echo $int['name']; ?></td>
                        </tr>
                        <tr>
                            <td><b>Cost</td><td>:</td>
                            <td>  $<?php echo number_format($int['cost']); ?></td>
                        </tr>

                        <tr>
                            <td><b>Owner</td><td>:</td>
                            <td>  <?php echo str_replace("_", " ", $int['ownername']); ?></td>
                        </tr>

                        <tr>
                            <td><b>Supplies</td><td>:</td>
                            <td>  <?php echo $int['supplies']; ?> Kg(s)</td>
                        </tr>

                        <tr>
                            <td><b>Last used</td><td>:</td>
                            <td>  <?php echo $int['lastused']; ?></td>
                        </tr>

                    </table>
                    <?php
                    if ($int['uploaded'] >= 0) {
                        ?>
                        <br>
                        <br>
                        <table border="0" align=center style="padding:10px;">
                            <tr>
                                <td align="center">
                                    <div class="button_dl">
                                        <a href="../uploader/ajax_downloader.php?intid=<?php echo $int['id']; ?>&charid=<?php echo $charid; ?>">Download Interior</a>
                                        <p class="top"><?php echo $int['last_uploaded']; ?></p>
                                        <p class="bottom"><?php echo formatBytes($int['file_size']) . " | " . $int['file_type']; ?></p>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    <?php } ?>
                </td>
                <td>
                    <br><?php
                    if ($isFactionInt) {
                        echo "<p>This is a faction interior so uploads for this interior is always <b>free of charge</b>.</p>";
                    } else {
                        if ($int['uploaded'] < 0) {
                            ?>
                            <p>You can spend <b><?php echo $normalPrice; ?> GC(s)</b> on getting a new custom interior for your property.</p>
                            <?php
                        } else {
                            if ($int['uploaded'] <= 8) {
                                ?>
                                <p>You have just uploaded a custom interior for this property just <?php echo $int['uploaded']; ?> hour(s) ago. So the re-uploading for this property is now <b>free of charge</b>.</p> <?php
                            } else if ($int['uploaded'] <= 72) {
                                ?><p>A custom interior for this property was uploaded <?php echo floor($int['uploaded']); ?> hour(s) ago. So the re-uploading fee for this property is now only a half (<b><?php echo ceil($normalPrice / 2); ?> GCs</b>).</p><?php
                            } else {
                                ?><p>A custom interior for this property was uploaded <?php echo floor($int['uploaded']); ?> hour(s) ago. So the re-uploading fee for this property is still (<b><?php echo $normalPrice; ?> GCs</b>).</p><?php
                            }
                        }
                    }
                    ?>
                    <p>Use a the MTA Map Editor to map the interior, and then upload the .map file here.<br>
                        The map must be in accordance to the following requirements to be uploaded:</p>
                    <ul>
                        <li>
                            You may upload a .map file only.
                        </li>
                        <li>
                            File size must be smaller than <?php echo formatBytes($max_allow_file_size); ?>.
                        </li>
                        <li>
                            Map file must contain no more than 250 objects.
                        </li>
                        <li>
                            Map file must contain 1 marker for the exit of the interior.
                        </li>
                        <li>
                            All objects must be placed inside the world boundaries on the X,Y,Z axis between -3000 and 3000.
                        </li>
                        <li>
                            Interior and dimension of your map doesn't matter.
                        </li>
                        <li>
                            The interior should fit the exterior of the building it is being applied to.
                        </li>
                        <li>
                            You must have created the map yourself or have permission from the one who did.
                        </li>
                    </ul>
                    <b><i><u>Notices:</u></i></b>
                    <ul><i>
                            <li>After the successful upload, your map should be processed and the interior should be set up and ready to use in the game instantly.</li>
                            <li>Uploads for Faction Interiors is always <b>free of charge</b>.</li><li>Once you have paid GC(s) to upload a custom interior to one property, future uploads by you to the same property will be <b>free of charge</b> within 8 hours and will <b>cost a half</b> within 72 hours for each re-uploads. This offer stays regardless of whether you will sell/loose the property to other players.</li>
                            <li>Special object settings from map editor: doublesided (bool), collisions (bool), breakable (bool, scale (float) and alpha (int, values are 140-255, where 255 is fully opaque and 140 is fully transparent).</li>
                        </i></ul>
                </td>
            </tr>
        </table>

        <?php //include("../uploader/interior_uploader.php");  ?>
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

                        //Allowed file size is less than 5 MB (1048576)
                        if (fsize > 100000)
                        {
                            $("#output").html("<b>" + bytesToSize(fsize) + "</b> Too big file! <br />File is too big, it should be less than 100kB");
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
            <div align="center">
                <h3>Interior Uploader</h3>
                <form action="../uploader/interior_uploader_processor.php" method="post" enctype="multipart/form-data" id="MyUploadForm">
                    <input type="hidden" name="intid" value="<?php echo $intid; ?>"/>
                    <input type="hidden" name="charid" value="<?php echo $charid; ?>"/>
                    <input name="FileInput" id="FileInput" type="file" accept=".map"/>
                    <input type="submit"  id="submit-btn" value="Upload" />
                    <img src="../uploader/images/ajax-loader.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
                </form>
                <div id="progressbox" ><div id="progressbar"></div ><div id="statustxt">0%</div></div>
                <div id="output"></div>
            </div>
        </div>

        <br>
        <center><a href="" onClick="$('#char_info_mid_top').slideUp(500);
                        return false;">Close Interior Uploader</a></center>
        <?php
    }
}