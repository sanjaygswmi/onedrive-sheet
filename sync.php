<?php

set_time_limit(0);

// echo realpath('../wp-load.php'); die;

require_once 'credentials.php';
require_once 'vendor/autoload.php';
require_once( realpath('../wp-load.php'));
// require_once 'class-db.php';
require_once 'functions.php';

error_reporting(E_ALL);
ini_set("display_errors", 1);

$rows = get_sheet_data();
$inserted_product = $products = [];

try{

	if( !empty( $rows ) ) {
		foreach($rows as $key => $row) {

			$sku            = trim( $row[ SKU_COLUMN ] );
			$standard_price = trim( $row[ STANDARD_COLUMN ], " $" );
			$gold_price     = trim( $row[ GOLD_COLUMN ], " $" );
			$gold_qty       = 25;
			$msrp       	= trim( $row[ MSRP_COLUMN ], " $" );


			if( empty( $sku ) || $key < 4) { 
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

					$inserted_product[] = $sku;
				}
			} else {
				$products[] = $sku;
			}

		}
	}
	echo "Price updated to these prodcts";
	echo "<pre>";
	print_r($inserted_product);
	echo "</pre>";
	echo "Products having issues<pre>";
	print_r($products);
	// die;
} catch( \Exeception $e ) {
	$e->getMessage();
	$e->getTraceAsString();
}