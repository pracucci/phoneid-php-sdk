<?php

require_once __DIR__ . '/../../src/PhoneId/autoload.php';

use PhoneId\PhoneId;
use PhoneId\Exceptions\PhoneIdException;


$clientId     = '<your client id>';
$clientSecret = '<your client secret>';
$phoneId      = new PhoneId($clientId, $clientSecret);

try {
    $phoneId->setAccessToken($_GET['access_token']);
    $me = $phoneId->getMe();

    echo 'User successfully authenticated: <br />';
    print_r($me);
} catch(PhoneIdException $exception) {
    echo $exception->getMessage();
}
