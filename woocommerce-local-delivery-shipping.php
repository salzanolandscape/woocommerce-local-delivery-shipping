<?php
defined( 'ABSPATH' ) or exit;

/**
 * Plugin Name: WooCommerce Local Delivery Shipping Method
 * Plugin URI: https://breakfastco.xyz
 * Description: Creates a local delivery shipping method. Assumes delivery costs are built into product prices. Enables both shipping and billing addresses in checkout without enabling shipping costs.
 * Version: 1.0.0
 * Author: Corey Salzano
 * Author URI: https://github.com/csalzano
 * Text Domain: woo-local-delivery-shipping
 * Domain Path: /languages
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
{
	/**
	 * change_string_shipping_may_be_available
	 * 
	 * Changes "Enter your address to view shipping options." to say delivery.
	 *
	 * @param  string $str
	 * @return string
	 */
	function change_string_shipping_may_be_available( $str )
	{
		return __( 'Enter your address to view delivery options.', 'woo-local-delivery-shipping' );
	}
	add_filter( 'woocommerce_shipping_may_be_available_html', 'change_string_shipping_may_be_available' );

	function change_string_shipping_not_available( $str )
	{
		//There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.
		return __( 'You might be outside our usual delivery area. Please contact us and to find out if we can make it work.', 'woo-local-delivery-shipping' );
	}
	add_filter( 'woocommerce_no_shipping_available_html', 'change_string_shipping_not_available' );

	/**
	 * change_shipping_suggestions_to_included
	 * 
	 * Changes "Shipping costs are calculated during checkout." to "Included".
	 *
	 * @param  mixed $msg
	 * @return void
	 */
	function change_string_shipping_suggestions( $msg )
	{
		//$msg = "Shipping costs are calculated during checkout."
		return __( 'Included', 'woo-local-delivery-shipping' );
	}
	add_filter( 'woocommerce_shipping_not_enabled_on_cart_html', 'change_string_shipping_suggestions' );

	/**
	 * change_shipping_to_delivery
	 * 
	 * Changes "Shipping Method #" to "Delivery. Remove this function and filter
	 * if your site is using other shipping methods in addition to delivery.
	 *
	 * @param  mixed $name
	 * @param  mixed $i
	 * @param  mixed $package
	 * @return void
	 */
	function change_string_shipping_to_delivery( $name, $i, $package )
	{
		//$name = "Shipping Method #"
		return __( 'Delivery', 'woo-local-delivery-shipping' );
	}
	add_filter( 'woocommerce_shipping_package_name', 'change_string_shipping_to_delivery', 10, 3 );

	function local_delivery_init()
	{
		if ( ! class_exists( 'WC_Shipping_Method_Local_Delivery' ) )
		{
			/**
			 * WC_Shipping_Method_Local_Delivery
			 * 
			 * A shipping method class that is free local delivery
			 * 
			 * @author Corey Salzano
			 */
			class WC_Shipping_Method_Local_Delivery extends WC_Shipping_Method
			{
				public function __construct( $instance_id = 0 )
				{
					$this->id                 = 'wc_shipping_method_local_delivery';
					$this->instance_id        = absint( $instance_id );
					$this->method_title       = __( 'Local Delivery', 'woo-local-delivery-shipping' );
					$this->method_description = __( 'Assumes delivery costs are built into product prices.', 'woo-local-delivery-shipping' );

					$this->title = __( 'Local Delivery', 'woo-local-delivery-shipping' );

					//Default is just settings. shipping-zones gets us in the list when adding methods to zones
					$this->supports = array( 
						'settings',
						'shipping-zones',
					);
				}

				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package = array() )
				{
					/*
						Example $package array

						$package = array(7)
							contents: array(1)
								f3f27a324736617f20abbf2ffd806f6d: array(12)
									key: "f3f27a324736617f20abbf2ffd806f6d"
									product_id: 516
									variation_id: 0
									variation: array(0)
									quantity: 4
									data_hash: "b5c1d5ca8bae6d4896cf1807cdf763f0"
									line_tax_data: array(2)
										subtotal: array(0)
										total: array(0)
									line_subtotal: 180
									line_subtotal_tax: 0
									line_total: 180
									line_tax: 0
									data: WC_Product_Simple
										object_type: "product"
										post_type: "product"
										cache_group: "products"
										data: array(50)
										supports: array(1)
										id: 516
										changes: array(0)
										object_read: true
										extra_data: array(0)
										default_data: array(50)
										data_store: WC_Data_Store
										meta_data: null
							contents_cost: 180
							applied_coupons: array(0)
							user: array(1)
								ID: 0
							destination: array(7)
								country: "US"
								state: "PA"
								postcode: "16101"
								city: "New Castle"
								address: "1020 N Croton Ave"
								address_1: "1020 N Croton Ave"
								address_2: ""
							cart_subtotal: "180"
							rates: array(0)
					*/

					/*
						WC_Shipping_Method::add_rate() accepts an array like...

						$rate = array(
							'id'             => $this->get_rate_id(), // ID for the rate. If not passed, this id:instance default will be used. Cannot be zero. Must be unique.
							'label'          => '', // Label for the rate.
							'cost'           => '0', // Amount or array of costs (per item shipping).
							'taxes'          => '', // Pass taxes, or leave empty to have it calculated for you, or 'false' to disable calculations.
							'calc_tax'       => 'per_order', // Calc tax per_order or per_item. Per item needs an array of costs.
							'meta_data'      => array(), // Array of misc meta data to store along with this rate - key value pairs.
							'package'        => false, // Package array this rate was generated for @since 2.6.0.
							'price_decimals' => wc_get_price_decimals(),
						);
					*/
					$this->add_rate(
						array(
							'id'      => 1,
							'label'   => __( 'Included', '' ),
							'cost'    => '0',
							'taxes'   => '0',
							'package' => $package,
						),
					);
				}
			}
		}
	}
	add_action( 'woocommerce_shipping_init', 'local_delivery_init' );

	function local_delivery_add_method( $methods )
	{
		$methods['wc_shipping_method_local_delivery'] = 'WC_Shipping_Method_Local_Delivery';
		return $methods;
	}
	add_filter( 'woocommerce_shipping_methods', 'local_delivery_add_method' );
}
