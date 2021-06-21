<?php
/*
 * Plugin Name: Headless WooCommerce Helper Plugin
 * Description: Add some extra functionality to enable headless WooCommerce
 * Author: Nadir Seghir
 * Author URI: https://nadir.blog
 * Version: 0.0.1
 */

function get_stripe_amount( $total, $currency = '' ) {
	if ( ! $currency ) {
		$currency = get_woocommerce_currency();
	}
	return absint( wc_format_decimal( ( (float) $total * 100 ), wc_get_price_decimals() ) );
}

require 'vendor/autoload.php';
require 'includes/basic-stripe.php';
require 'includes/cors.php';
require 'includes/store-api.php';


