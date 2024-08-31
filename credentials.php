<?php 

define('DB_HOST', '127.0.0.1');
define('DB_USERNAME', 'ug5djpeywmiji');
define('DB_PASSWORD', 'fuoigwpvnxfn');
define('DB_NAME', 'dblvwegkzspfgh');

// sanjay.gswmi@live.com or ssklogistics
// define('ONEDRIVE_CLIENT_ID', '32c0e7af-8d9f-4b0a-95db-5655470a1088');
// define('ONEDRIVE_CLIENT_SECRET', 'XKU8Q~izOMJSDlHJryeCC4o5-0P01O_U90o-ycsO');

// rodman@hotmail.com
// define('ONEDRIVE_CLIENT_ID', '3542cc5a-e29c-48f8-9630-d884eff97923');
// define('ONEDRIVE_CLIENT_SECRET', 'lxl8Q~yux6B~_ZBMHPnsu6b4GtXwSVI5FYBvnaTr');
// define( 'ONEDRIVE_SHEET_ID', '67B18E102DF3D977!117');


// rodman@tacticon.com
define('ONEDRIVE_CLIENT_ID', '9e64af91-0780-4ab2-b142-cdb15ca6776e');
define('ONEDRIVE_CLIENT_SECRET', '6v98Q~Blv6MqHQrlLBCxd.SXwyZhWGRemJ8s3aRK');
define('ONEDRIVE_TENANT_ID', '0d6484fe-a7a2-4e79-9e29-2abd1eedd660');

// Live
// define( 'ONEDRIVE_SHEET_ID', '01TCSVCQYA3WUP4TJFSFFK523UQT6WVELP');
// define( 'DRIVE_ID', 'b!FyMyogtfjUeZ_Lf_ahu60yac-Lu1voFGj5tVmIxT3oiHhwYxKV8RQLrKpYYXKYql');

// Staging
define( 'ONEDRIVE_SHEET_ID', '0132BB4QQWEQTXCQB64BBLEAYMYS2QKUSA');
define( 'DRIVE_ID', 'b!DiKcvhnC7EGCCkmxHYVCQbbVEVi2NY1Ek2BjtA5kGoPRNr0-EOQTTIiZ0os6ZZ0T');


// admin@hotnewapps.com
// define('ONEDRIVE_CLIENT_ID', '3426acaf-91e6-4c3c-9ec5-9faf7d2bbb0c');
// define('ONEDRIVE_CLIENT_SECRET', 'KT48Q~jMQtIce~URtn4KNizAvpPgTJaEBJdMWbBm');

define('ONEDRIVE_SCOPE', 'files.read files.read.all files.readwrite files.readwrite.all');
define('ONEDRIVE_CALLBACK_URL', 'https://developer.wholesale.tacticon.com/sync_product/callback.php'); 

// define( 'ONEDRIVE_SHEET_ID', 'F644D575EB89624C!232');
define( 'ONEDRIVE_SHEET_NAME', 'Price Sheet');

define('SKU_COLUMN', '0');
define('GOLD_COLUMN', '9');
define('STANDARD_COLUMN', '10');
define('MSRP_COLUMN', '12');


$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if($conn->connect_error){
	die("Failed to connect with MySQL: " . $conn->connect_error);
}else{
	$db = $conn;
}


function setup_table($db) {
	try{
		$sql = 'CREATE TABLE IF NOT EXISTS `ms_azure_token` ( `id` INT(11) NOT NULL AUTO_INCREMENT, `provider` varchar(255) NOT NULL, `token_type` varchar(50) NOT NULL, `scope` TEXT NULL, `expires_in` int(11) NOT NULL, `ext_expires_in` INT(11) NOT NULL, `access_token` TEXT NOT NULL, `refresh_token` TEXT NOT NULL, `status` INT NOT NULL, `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;';

		if ($db->query($sql) === TRUE) {
	        // echo "Table created successfully";
		} else {
	        // echo "Error creating table: " . $conn->error;
		}
	} catch( \Exception $e ) {
		echo $e->getMessage();
		echo $e->getTraceAsString();
		die;
	}
}

setup_table($db);