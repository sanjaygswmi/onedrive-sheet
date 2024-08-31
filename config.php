<?php

require_once 'credentials.php';
require_once 'vendor/autoload.php';
require_once '../wp-load.php';
// require_once 'class-db.php';
require_once 'functions.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);


$adapter = authenticate();


/*$config = [
    'callback' => ONEDRIVE_CALLBACK_URL,
    'keys'     => [
        'id' => ONEDRIVE_CLIENT_ID,
        'secret' => ONEDRIVE_CLIENT_SECRET
    ],
    'scope'    => ONEDRIVE_SCOPE,
    'authorize_url_parameters' => [
        'approval_prompt' => 'force',
        'access_type' => 'offline'
    ]
];

$adapter = new Hybridauth\Provider\MicrosoftGraph( $config );*/


try {
    $adapter->authenticate();
    // $userProfile = $adapter->getUserProfile();
    // $tokens = $adapter->getAccessToken();
} catch (\Exception $e) {
    echo $e->getMessage() ;
}