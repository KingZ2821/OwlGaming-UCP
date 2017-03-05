
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */

function ajax_load_int_top(){
    $('#lib_mid').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.get("../ajax/ajax_lib_ints.php", {
        part:'top',
    }, function (data) {
        document.getElementById("lib_mid").innerHTML = data;
    });
}

function ajax_load_int_content(content1){
    $('#lib_bot').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.get("../ajax/ajax_lib_ints.php", {
        part: 'content',
        content:content1,
    }, function (data) {
        $('#lib_bot').html(data);
    });
}

function ajax_load_veh(){
    $('#lib_bot').html('');
    $('#lib_mid').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.get("../ajax/ajax_lib_vehs.php", {

    }, function (data) {
        $('#lib_mid').html(data);
    });
}

function ajax_load_skin_top(){
    $('#lib_mid').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.get("../ajax/ajax_lib_skins.php", {
        part:'top',
    }, function (data) {
        document.getElementById("lib_mid").innerHTML = data;
    });
}

function ajax_load_skin_content(size1){
    $('#lib_bot').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.get("../ajax/ajax_lib_skins.php", {
        part:'content',
        size: size1,
    }, function (data) {
        $('#lib_bot').html(data);
    });
}