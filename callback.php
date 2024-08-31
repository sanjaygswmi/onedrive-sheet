<?php
require_once 'config.php';
 
try {
    $adapter->authenticate();
    $token = $adapter->getAccessToken();

    echo "<pre>"; print_r($token);
    $logger->info( print_r($token, true) );


    $db = new DB();
    $db->update_access_token(json_encode($token));
    echo "Access token inserted successfully.";
}
catch( Exception $e ){
    // echo $e->getMessage();
    // echo "<pre>";
    // print_r($e->getTrace());
    // echo "</pre>";
    // die;
    $logger->error(  $e->getMessage() ) ;
}


