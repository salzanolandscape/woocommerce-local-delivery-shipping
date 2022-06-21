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
	function change_shipping_to_delivery( $name, $i, $package )
	{
		return __( 'Delivery', 'woo-local-delivery-shipping' );
	}
	add_filter( 'woocommerce_shipping_package_name', 'change_shipping_to_delivery', 10, 3 );

	function change_shipping_suggestions_to_included( $msg )
	{
		return __( 'Included', 'woo-local-delivery-shipping' );
	}
	add_filter( 'woocommerce_shipping_not_enabled_on_cart_html', 'change_shipping_suggestions_to_included' );

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
