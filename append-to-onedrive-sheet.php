<?php
require_once 'config.php';
 
// here you can pass as many data as a separate array element
$arr_data = [
    ['John Doe', 'john@test.com', '8888888888']
];
 
append_to_sheet($arr_data);
 
function append_to_sheet($arr_data = array()) {
    $item_id = 'YOUR_SHEET_ID';
    $table = 'Table1'; //in your case it can be Table2, Table3, ...
 
    $db = new DB();
 
    $arr_token = (array) $db->get_access_token();
    $accessToken = $arr_token['access_token'];
 
    try {
 
        $client = new GuzzleHttp\Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://graph.microsoft.com',
        ]);
 
        $response = $client->request("POST", "/v1.0/me/drive/items/$item_id/workbook/tables/$table/rows/add", [
            'json' => [
                'values' => $arr_data
            ],
            'headers' => [
                'Authorization' => 'Bearer '. $accessToken
            ],
            'verify' => false,
        ]);
    } catch(Exception $e) {
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
    }
}