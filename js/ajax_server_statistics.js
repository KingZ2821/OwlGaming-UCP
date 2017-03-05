
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */

function ajax_load_server_statistics(){
    $.get("../ajax/ajax_server_stats.php", {
    }, function (data) {
        document.getElementById("server_stats").innerHTML = data;
    });
}