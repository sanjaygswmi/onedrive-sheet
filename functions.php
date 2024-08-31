<?php 

require_once 'class-db.php';


use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

// Create the logger
$logger = new Logger('OneDrive Sheet');
// Now add some handlers
$logger->pushHandler(new StreamHandler(__DIR__.'/debug.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());


function authenticate() {
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

	return $adapter = new Hybridauth\Provider\MicrosoftGraph( $config );
}

function get_access_token(){
	global $db;

	try{
		$sql = $db->query("SELECT * FROM `ms_azure_token` WHERE provider = 'AZURE_AD'");
		$result = $sql->fetch_assoc();

		if( !empty( $result ) && $result["status"] ) {
			if( ( time() - ( strtotime( $result["created_at"] ) + $result["expires_in"] ) ) > 60 ) {
				return generate_access_token();
			} else {
				return $result["access_token"];
			}
		} else{
			return generate_access_token();
		}

		return $result['refresh_token'];
	} catch( \Exception $e ) {
		echo $e->getMessage();
		echo $e->getTraceAsString();
		die;
	}
}

function generate_access_token() {
	global $db;

	$curl = curl_init();

	$data = array(
		'grant_type'    => 'refresh_token',
		'client_id'     => ONEDRIVE_CLIENT_ID,
		'client_secret' => ONEDRIVE_CLIENT_SECRET,
		'refresh_token' => get_refresh_token(),
		'scope'         => 'https://graph.microsoft.com/.default'
	);

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://login.microsoftonline.com/'. ONEDRIVE_TENANT_ID .'/oauth2/v2.0/token',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING       => '',
		CURLOPT_MAXREDIRS      => 10,
		CURLOPT_TIMEOUT        => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST  => 'POST',
		CURLOPT_POSTFIELDS     => http_build_query($data),
		CURLOPT_HTTPHEADER     => array(
			'Content-Type: application/x-www-form-urlencoded',
		),
	));

	$response = curl_exec($curl);

	if($response === false)
	{
		echo 'Curl error: ' . curl_error($curl);
		die;
	}

	$response = json_decode( $response, true);

	if( !isset( $response["error"] ) ){
		$sql = $db->query("SELECT * FROM `ms_azure_token` WHERE provider = 'AZURE_AD'");
		$result = $sql->fetch_assoc();

		if( !empty( $result ) ) {
			$sql = $db->query("UPDATE `ms_azure_token` 

				SET token_type = '". $response["token_type"] ."', 
				scope = '". $response["scope"] ."', 
				expires_in = '". $response["expires_in"] ."', 
				ext_expires_in = '". $response["ext_expires_in"] ."', 
				access_token = '". $response["access_token"] ."', 
				refresh_token = '". $response["refresh_token"] ."',
				status = '1'
				WHERE provider = 'AZURE_AD'");
		} else {
			$sql = $db->query("INSERT INTO `ms_azure_token` 

				SET token_type = '". $response["token_type"] ."', 
				scope = '". $response["scope"] ."', 
				expires_in = '". $response["expires_in"] ."', 
				ext_expires_in = '". $response["ext_expires_in"] ."', 
				access_token = '". $response["access_token"] ."', 
				refresh_token = '". $response["refresh_token"] ."', 
				status = '1'
				provider = 'AZURE_AD'");
		}
	} else {
		echo "Something went wrong!<pre>";
		print_r($response);
		echo "</pre>";
		die;
	}
	curl_close($curl);
	// echo $response;
	
	return $response["access_token"];
}

function get_refresh_token(){
	global $db;

	$sql = $db->query("SELECT * FROM `ms_azure_token` WHERE provider = 'AZURE_AD'");
	$result = $sql->fetch_assoc();

	if( !empty( $result ) ) {
		return $result['refresh_token'];
	} else {
		return false;
	}
}

function get_sheet_data( $count = 0) {
	global $db;

	if( $count > 2 ){
		echo "Issue with access token";
		die;
	}

	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://graph.microsoft.com/v1.0/drives/'. DRIVE_ID .'/items/'. ONEDRIVE_SHEET_ID .'/workbook/worksheets(\''. rawurlencode(ONEDRIVE_SHEET_NAME) .'\')/usedRange',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer '. get_access_token()
		),
	));

	$response = curl_exec($curl);

	if($response === false)
	{
		echo 'Curl error: ' . curl_error($curl);
		die;
	}

	$response = json_decode( $response, true );

	curl_close($curl);

	if( !empty( $response["error"] ) ){
		if( $response["error"]["code"] == 'InvalidAuthenticationToken' ) {
			$sql = $db->query("UPDATE `ms_azure_token` SET `status` = '0' WHERE `provider` = 'AZURE_AD'");

			// echo "InvalidAuthenticationToken"; //die;
			return get_sheet_data();
		} else {
			// $logger->info( $response );
		}
	} else {
		return $response["text"];
	}
}