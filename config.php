<?php
require_once 'vendor/autoload.php';
require_once 'class-db.php';

$config = [
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

$adapter = new Hybridauth\Provider\MicrosoftGraph( $config );