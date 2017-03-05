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

    // define the path and name of cached file
    $cachefile = 'cache/home_stats/'.date('M-d-Y').'.php';
    $cachetime = 60*60; // 1 hour
    // Check if the cached file is still fresh. If it is, serve it up and exit.
    if (file_exists($cachefile) && time() - $cachetime < filemtime($cachefile)) {
    include($cachefile);
        exit;
    }
    // if there is either no file OR the file to too old, render the page and capture the HTML.
    ob_start();
?>
    <html>
        output all your html here.
    </html>
<?php
    // We're done! Save the cached content to a file
    $fp = fopen($cachefile, 'w');
    fwrite($fp, ob_get_contents());
    fclose($fp);
    // finally send browser output
    ob_end_flush();
?>