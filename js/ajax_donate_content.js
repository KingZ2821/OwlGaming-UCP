
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */


var customAmount = false;
function showCustomOption() {
    $("#don_option_normal").slideUp(500);
    $("#don_option_custom").slideDown(500);
    customAmount = true;
}

function showDefaultOption() {
    $("#don_option_normal").slideDown(500);
    $("#don_option_custom").slideUp(500);
    customAmount = false;
}

function getGcFromDollar(dollar) {
    dollar = Math.floor(dollar);
    var rate = 3 * 50;
    var actualGC = dollar * rate;
    var benefit = actualGC * (0.01 / 7.5 * dollar);
    var finalGC = actualGC + benefit;
    var discount = (benefit / actualGC) * 100;

    if (benefit < 0) {
        discount = 0;
    }
    if (discount >= 50) {
        discount = 50;
        finalGC = actualGC + actualGC * 0.5;
        benefit = finalGC - actualGC;
    }
    var btc = 0;
    var rateBtc = $('#btcRate').val();
    if (rateBtc) {
        btc = rateBtc * dollar;
    }
    return [finalGC.toFixed(0), benefit.toFixed(0), discount.toFixed(2), actualGC.toFixed(0), btc];
}

function calculateGc() {
    var result = getGcFromDollar($("#custom_amount").val());
    document.getElementById("calculated_gc").innerHTML = result[3];
    document.getElementById("calculated_bonus").innerHTML = result[1];
    document.getElementById("calculated_discount").innerHTML = result[2];
    document.getElementById("calculated_total").innerHTML = result[0];
    document.getElementById("calculated_btc").innerHTML = result[4];
}

function format_money(n, currency) {
    return currency + n.toFixed(2).replace(/./g, function (c, i, a) {
        return i > 0 && c !== "." && (a.length - i) % 3 === 0 ? "," + c : c;
    });
}

function startDonation() {
    var button = $('input[type="submit"][name="I1"]');
    var customAmountBtn = $('input[id="custom_amount"]');
    var defaultAmountBtn = $('input[name=amount1]:checked');
    if (button.val() == "Processing..") {
        return true;
    }
    else if (button.val() == "Donate") {
        var donateTo = $('input[type="text"][name="custom"]').val();
        var donorId = $('input[type="hidden"][name="item_number"]').val(); //Logged in userid
        var donorUsername = $('input[type="hidden"][name="donor_username"]').val();
        if (!donorId || donorId.length == 0 || isNaN(donorId)) {
            donorUsername = donateTo;
        }
        $('input[type="hidden"][name="item_name"]').val('Donation from ' + donorUsername + ' to ' + donateTo + ' [NOT REFUNDABLE]');
        button.val("Validating..");
        $.post("../ajax/ajax_functions.php", {
            getUserIdFromUsername: donateTo
        }, function (data) {
            if (!data || data == 0) {
                button.val("Account is not existed");
                setTimeout(function () {
                    button.val("Donate");
                }, 3000);
            } else {
                if (!donorId || donorId.length == 0 || isNaN(donorId)) {
                    button.val("Querying donor data..");
                    $.post("../ajax/ajax_functions.php", {
                        getUserIdFromUsername: donateTo
                    }, function (data) {
                        if (!data || data.length == 0) {
                            button.val("Failed! Try again later.");
                            setTimeout(function () {
                                button.val("Donate");
                            }, 5000);
                        } else {
                            $('input[type="hidden"][name="item_number"]').val(data);
                            if (customAmount) {
                                $('input[id="final_donation_amount"]').val(customAmountBtn.val());
                            } else {
                                $('input[id="final_donation_amount"]').val(defaultAmountBtn.val());
                            }
                            var donate_amount = $('input[id="final_donation_amount"]').val();
                            var result = getGcFromDollar(donate_amount);
                            var go = confirm('You are about to donate $' + donate_amount + ' for ' + result[0] + ' GC(s) with account name "' + donorUsername + '" to account name "' + donateTo + '".\n\nBy donating to the OwlGaming Community, you do understand that a refund will not be possible.\n\nAre you sure you want to do that?');
                            if (go) {
                                button.val("Processing..");
                                $("#donation_form").submit();
                                return true;
                            } else {
                                button.val("Donate");
                                return false;
                            }
                        }
                    });
                } else {
                    if (customAmount) {
                        $('input[id="final_donation_amount"]').val(customAmountBtn.val());
                    } else {
                        $('input[id="final_donation_amount"]').val(defaultAmountBtn.val());
                    }
                    var donate_amount = $('input[id="final_donation_amount"]').val();
                    var result = getGcFromDollar(donate_amount);
                    var go = confirm('You are about to donate $' + donate_amount + ' for ' + result[0] + ' GC(s) with account name "' + donorUsername + '" to account name "' + donateTo + '".\n\nBy donating to the OwlGaming Community, you do understand that a refund will not be possible.\n\nAre you sure you want to do that?');
                    if (go) {
                        button.val("Processing..");
                        $("#donation_form").submit();
                        return true;
                    } else {
                        button.val("Donate");
                        return false;
                    }
                }
            }
        });
    }
    return false;
}

function hide_top_donors() {
    $('#char_info_mid').slideUp(500);
}

function ajax_load_top_donor() {
    //$("#char_info_mid").html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $('#char_info_mid').slideUp(500);
    $.get("../ajax/ajax_load_top_donor.php", {
    }, function (data) {
        $("#char_info_mid").html(data);
        $("#hide_top_donor").click(hide_top_donors);
        $('#char_info_mid').slideDown(500);
    });
}

function btc_create_invoice() {
    var dollar = $('#custom_amount').val();
    if (!dollar || isNaN(dollar)) {
        alert('Please enter a valid amount in USD.');
    } else {
        $.post("../ajax/ajax_donate.php", {
            action: 'btc_create_invoice',
            dollar: dollar
        }, function (data) {
            $('#btc_main').html(data);
        });
    }
}
