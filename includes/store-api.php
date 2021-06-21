<?php

use Automattic\WooCommerce\Blocks\Package;
use Automattic\WooCommerce\Blocks\Domain\Services\ExtendRestApi;
use Automattic\WooCommerce\Blocks\StoreApi\Schemas\CheckoutSchema;

// Disabled nonces
add_filter( 'woocommerce_store_api_disable_nonce_check', '__return_true' );

add_action('woocommerce_blocks_loaded', function() {
 // ExtendRestApi is stored in the container as a shared instance between the API and consumers.
 // You shouldn't initiate your own ExtendRestApi instance using `new ExtendRestApi` but should always use the shared instance from the Package dependency injection container.
 $extend = Package::container()->get( ExtendRestApi::class );

 $extend->register_endpoint_data(
	array(
		'endpoint' => CheckoutSchema::IDENTIFIER,
		'namespace' => 'basic-stripe',
		'data_callback' => 'basic_stripe_data_callback',
		'schema_callback' => 'basic_stripe_schema_callback',
		)
	);
});


function basic_stripe_data_callback() {
	$order_id = wc()->session->get( 'store_api_draft_order', 0 );
	$order    = wc_get_order( $order_id );
	$options = get_option( 'woocommerce_basic-stripe_settings', [] );
	$private_key = $options['test_private_key'];
	$stripe = new \Stripe\StripeClient( $private_key );

		if ( $order->get_meta( 'stripe-intent' ) ) {
			$payment_intent = $stripe->paymentIntents->retrieve(
				$order->get_meta_data( 'stripe-intent' ),
				[]
			);
		} else {
			$payment_intent = $stripe->paymentIntents->create(
				[
					'currency' => strtolower( $order->get_currency() ),
					'amount'   => get_stripe_amount( $order->get_total(), strtolower( $order->get_currency() ) ),
					'metadata' => [
						'order_id' => $order->get_id(),
					],
				]
			);
			$order->update_meta_data( 'stripe-intent', $payment_intent->client_secret );
		}

		return [
			'client_secret' => $payment_intent->client_secret,

		];
}

function basic_stripe_schema_callback() {
	return [
		'client_secret' => [
			'description' => __( 'Client secret', 'basic-stripe' ),
			'type' => 'string',
			'readonly' => true,
		],
	];
}
