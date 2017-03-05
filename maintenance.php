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

$notices = array(
    "We are now serving angles. We will be back to serve humans shortly.", 
    "Shhhh.. We are now sleeping and dreaming about new features to implement. Will be back soon!", 
    "In reality, we are now down. This error message is an illusion, you come back in a couple of hours and this message will be gone.", 
    "You may be addicted to our site. We care about your health and we want to give you a break for a few hours. Will be back soon!",
    "We are now testing error messages. Everytime you refresh the page, you will see a new error message. Thanks for participating!",
    "Yes, you are in the right place. We are not. We are trying to get there soon. Thank you!",
    "There is so much noise out there. Our solution is to keep only these messages for the entire screen for a couple of hours.",
    "Sorry for interupting you with ONLY these sentences. We will be back with information overload in next couple of hours.",
    "If you are able to read this message, you are normal and we are not. We will be back soon!",
    "OMG!!! We will be back soon!",
    "You are OK. We are NOT. We will be back soon!!!",
    "I have not taken a break for days and days. Now I really deserve one. Will be back in a couple of hours.",
    "Thank you for visiting. I am current undergoing my annual physical checkup. Will be back soon.",
    "This is a 'hide and seek' game. I plan to hide from you until next couple of hours. Thank you for playing with me.",
    "This is my 'loyaty test'. You can pass this test by coming back in a couple of hours. See you soon and goodluck with this.",
    "Glad you are here. But I think you are a few hours early. I will see you soon!!",
    "I know you are going to go away now. But remember to be back in a couple of hours.",
    "Did I miss the appointment OR you early I have you down in next couple of hours.. Sorry for the confusion!",
    "What?? Are you still seeing the error messages? I will get this fixed right away. See you in next couple of hours.",
    "There has been an incident on Hightway 101 and hence the delay. Will be back soon!",
    "This is the final test of your perserverence. Your final assignment is to come back soon and see me!!",
    "Close your eyes. Breath. Inhale. Exhale. That's what we are doing here. See you soon!",
    "If you are seeing this error message, please clear your cache in a couple of hours and hit the refresh button!",
    "This error message has been created for our VIP members. Please be back soon!",
    "Someone has kidnapped our site. We are negotiating ransom and will resolve this issue and be back soon!"
    );
?>

<head>
    <title>OwlGaming Community - Your World. Your Imagination</title>
    <link href="css/style.css" type="text/css" rel="stylesheet" />
    <link rel="shortcut icon" href="/images/icons/favicon.png" type="image/x-icon" />
</head>
<body>
<center>
    <br>
    <img src="images/logo.png"><br><br>
    <img src="images/maintenance/maintenance (<?php echo rand(1, 33); ?>).jpg">
    <br>
    <p><i>"<?php echo $notices[array_rand($notices)];?>"</i> - OwlGaming</p>
    <br><hr>
    <table border="0">
        <tr>
            <td>
                <iframe src="http://www.game-state.com/iframe.php?ip=167.114.119.145&port=22003&bgcolor=363636&bordercolor=26A8FF&fieldcolor=FFFFFF&valuecolor=EDEDED&oddrowscolor=4D4D4D&showgraph=true&showplayers=true&graphvalues=EDEDED&graphaxis=FFFFFF&width=300&graph_height=105&plist_height=101&font_size=9" frameborder="0" scrolling="no" style="width: 300px; height: 371px"></iframe>
            </td>
            <td>
                <iframe src="http://www.game-state.com/iframe.php?ip=91.121.137.31&port=9987&bgcolor=363636&bordercolor=26A8FF&fieldcolor=FFFFFF&valuecolor=EDEDED&oddrowscolor=4D4D4D&showgraph=true&showplayers=true&graphvalues=EDEDED&graphaxis=FFFFFF&width=300&graph_height=105&plist_height=101&font_size=9" frameborder="0" scrolling="no" style="width: 300px; height: 371px"></iframe>
            </td>
        </tr>
    </table>
    <br>
    <font size="1">
    Programmed by <a href="mailto:owlgaming.net@domainsbyproxy.com?Subject=Hello" target="_top">Maxime</a> <br>
    Copyright © <?php echo date("Y"); ?> OwlGaming Community. All rights reserved.
    </font>
</center>
</body>


