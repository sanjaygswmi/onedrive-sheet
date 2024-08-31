<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

require_once 'config.php';

if ( ! class_exists('DB')) 
    die('There is no hope!');


$db = new DB();

$arr_token = (array) $db->get_access_token();
$accessToken = $arr_token['access_token'];



$excelFileId = ONEDRIVE_SHEET_ID;
$sheetName   = ONEDRIVE_SHEET_NAME; 

try {
	$client = new GuzzleHttp\Client([
        // Base URI is used with relative requests
        'base_uri' => 'https://graph.microsoft.com',
    ]);

    $response = $client->request("GET", "/v1.0/me/drive/items/{$excelFileId}/workbook/worksheets('$sheetName')/usedRange", [
        'headers' => [
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ],
        'verify' => false, // Disable SSL certificate verification (not recommended for production)
    ]);

    // Decode the JSON response
    $responseData = json_decode($response->getBody(), true);

    // Extract the values from the response
    $rows = $responseData['values'];

    echo "<pre>";
    print_r($rows);
    echo "</pre>";
    die;

    foreach($rows as $key => $row) {
    	$sku            = trim( $row[ SKU_COLUMN ] );
    	$standard_price = trim( $row[ STANDARD_COLUMN ] );
    	$gold_price     = trim( $row[ GOLD_COLUMN ] );
    	$gold_qty       = 25;
    	$msrp       	= trim( $row[ MSRP_COLUMN ] );


    	if( empty( $sku ) ) { 
    		continue;
    	}

    	$product_id = get_product_by_sku( $sku );
    	$product = get_post( $product_id );

		if( !empty( $product_id ) && !empty( $standard_price ) ) {
			
			//$product_id = $product;

	    	// Checking if the pricing is already added
			if($product_id != ''){
				$standard_price 	= round( str_replace( '$', '', $standard_price), 2);
				$gold_price 		= round( str_replace( '$', '', $gold_price), 2);
				$msrp 				= round( str_replace( '$', '', $msrp), 2);

				update_post_meta( $product_id, '_standard_wholesalers_price', esc_attr( $standard_price ) );
				update_post_meta( $product_id, '_gold_wholesalers_price', esc_attr( $gold_price ) );
				update_post_meta( $product_id, '_gold_wholesalers_min_qty', esc_attr( $gold_qty ) );
				update_post_meta( $product_id, '_regular_price', esc_attr( $msrp ) );
				update_post_meta( $product_id, '_price', esc_attr( $msrp ) );
				if($product->post_type == "product_variation"){						
					update_post_meta( $product->post_parent, '_price', esc_attr( $msrp ) );
				}

				$inserted_product[] = $product_id;
			}
		} else {
			$products[] = $sku;
		}
    }

} catch (Exception $e) {
	if( 401 == $e->getCode() ) {
        $refresh_token = $db->get_refersh_token();

        $client = new GuzzleHttp\Client(['base_uri' => 'https://login.microsoftonline.com']);

        $response = $client->request('POST', '/common/oauth2/v2.0/token', [
            'form_params' => [
                "grant_type" => "refresh_token",
                "refresh_token" => $refresh_token,
                "client_id" => ONEDRIVE_CLIENT_ID,
                "client_secret" => ONEDRIVE_CLIENT_SECRET,
                "scope" => ONEDRIVE_SCOPE,
                "redirect_uri" => ONEDRIVE_CALLBACK_URL,
            ],
        ]);

        $db->update_access_token($response->getBody());

        append_to_sheet($arr_data);
    } else {
        echo $e->getMessage(); //print the error just in case your video is not uploaded.
    }
    // Handle any exceptions or errors that occur during the request
    echo 'Error: ' . $e->getMessage();
}