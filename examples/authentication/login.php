<?php

require_once __DIR__ . '/../../src/PhoneId/autoload.php';

use PhoneId\PhoneId;


$clientId     = '<your client id>';
$clientSecret = '<your client secret>';
$phoneId      = new PhoneId($clientId, $clientSecret, array('redirect_uri' => 'http://localhost/phoneid-php-sdk/examples/authentication/return.php'));

// Redirect to Phone.id authorize url
header('Location: ' . $phoneId->getAuthorizeUrl());
