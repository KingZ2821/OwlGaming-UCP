<?php

/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * File created by ducch, 20-10-2015
 * ***********************************************************************************************************************
 */

$secret = 'ZzsMLGKe162CsdsdwfA5EcG326j';

$my_address = '1KYUVVgosRXfP5ag2VmhTrLFFm9aW8qSLd';

$my_callback_url = 'https://www.owlgaming.net/postback-pp.php?invoice_id=058921123&secret='.$secret;

$root_url = 'https://blockchain.info/api/receive';

$parameters = 'method=create&address=' . $my_address .'&callback='. urlencode($my_callback_url);

$response = file_get_contents($root_url . '?' . $parameters);

$object = json_decode($response);

echo 'Send Payment To : ' . $object->input_address;