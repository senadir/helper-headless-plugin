<?php

add_filter( 'woocommerce_payment_gateways', 'hhp_add_gateway_class' );
function hhp_add_gateway_class( $gateways ) {
	$gateways[] = 'Headless_Basic_Stripe';
	return $gateways;
}

add_action( 'plugins_loaded', 'hhp_init_gateway_class' );
function hhp_init_gateway_class() {

	class Headless_Basic_Stripe extends WC_Payment_Gateway {

		public function __construct() {

			$this->id = 'basic-stripe';
			$this->method_title = 'Basic Stripe';
			$this->method_description = 'Basic Stripe implementation for headless WooCommerce Demo';

			// We only support products for now.
			$this->supports = array(
				'products'
			);

			// Method with all the options fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();
			$this->enabled = $this->get_option( 'enabled' );
			$this->testmode = 'yes' === $this->get_option( 'testmode' );
			$this->private_key = $this->get_option( 'test_private_key' );
			$this->publishable_key =$this->get_option( 'test_publishable_key' );

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			add_action( 'woocommerce_rest_checkout_process_payment_with_context', array( $this, 'process_store_api_payment' ), 10, 2 );
		 }

		 public function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title'       => 'Enable/Disable',
					'label'       => 'Enable Basic Stripe',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no'
				),
				'testmode' => array(
					'title'       => 'Test mode',
					'label'       => 'Enable Test Mode',
					'type'        => 'checkbox',
					'disabled'    => true,
					'description' => 'Basic Stripe is hardcoded into test mode.',
					'default'     => 'yes',
					'desc_tip'    => true,
				),
				'test_publishable_key' => array(
					'title'       => 'Test Publishable Key',
					'type'        => 'text'
				),
				'test_private_key' => array(
					'title'       => 'Test Private Key',
					'type'        => 'password',
				),
			);
		}

		public function process_store_api_payment( $context, $result ) {
			if ('basic-stripe' === $context->payment_method ) {
				$data = $context->payment_data;
				$order = $context->order;
				$stripe = new \Stripe\StripeClient($this->private_key);
				$payment_intent = $stripe->paymentIntents->retrieve(
					$data['stripe_source'],
				);
				if (get_stripe_amount($order->get_total()) !== $payment_intent->amount )
				$payment_intent->update(
					$data['stripe_source'],
					[
						'amount' => get_stripe_amount($order->get_total())
					]
				);

					$order->payment_complete( $payment_intent->id );
					$order->save();
					WC()->cart->empty_cart();
					$result->set_status('success');

			}
	 	}
 	}
}