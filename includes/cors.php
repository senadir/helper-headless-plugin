<?php

function hhp_disable_cors() {
	add_filter( 'rest_pre_serve_request', function( $value ) {
		header( 'Access-Control-Allow-Origin: ' . DETACHED_STORE_DOMAIN );
		header( 'Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTION' );
		header( 'Access-Control-Allow-Credentials: true' );
		header( 'Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, X-WC-Store-API-Nonce' );
		header( 'Access-Control-Expose-Headers: X-WC-Store-API-Nonce, X-WC-Store-API-Nonce-Timestamp');

		return $value;
	} );
}

add_action( 'rest_api_init', 'hhp_disable_cors', 15 );