<?php
/**
 * Plugin Name: Item total for WooCommerce
 * Description: Simple plugin to display Item total in the order details page.
 * Version: 1.3.0
 * Author: AashikP
 * Author URI: https://aashikp.com
 * Text Domain: item-total-for-wc
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * WC requires at least: 7.0
 * WC tested up to: 8.4.0
 *
 * @package Item-Total-for-WC
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	// Declare compatibility for High performance order storage: https://github.com/woocommerce/woocommerce/wiki/High-Performance-Order-Storage-Upgrade-Recipe-Book.
	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
				\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			}
		}
	);

	// Add actions after WooCommerce has loaded.
	add_action(
		'woocommerce_loaded',
		function () {
			add_action( 'woocommerce_admin_order_item_headers', 'ap_item_total_header' );
			add_action( 'woocommerce_after_order_fee_item_name', 'ap_display_item_total' );
			add_action( 'woocommerce_after_order_itemmeta', 'ap_display_item_total' );
		}
	);
}


/**
 * Display tax included price on order details page.
 */
function ap_item_total_header() {
	echo '<th class="item_total_cost sortable" data-sort="float" style="width:100%;text-align:right;">' . esc_html__( 'Item Total', 'item-total-for-wc' )
	. '</th>'; // phpcs:ignore
}

/**
 * Calculate the total price for a line item.
 *
 * @param Object $order Order object.
 * @param String $item_type Type of line item. line_item, shipping, or fee.
 */
function ap_calculate_item_total( $order, $item_type = 'line_item' ) {
	$item_total = array();
	foreach ( $order->get_items( $item_type ) as $item_id => $item ) {
		$price                  = ( $item['total'] + $item['total_tax'] );
		$item_total[ $item_id ] = $price;
	}
	return $item_total;
}

/**
 * Display the total price for a line item.
 *
 * @param Int $item_id Order item id.
 */
function ap_display_item_total( $item_id ) {
	global $post, $theorder;
	if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
		$order = $theorder;
	} else {
		$order = wc_get_order( $post->ID ); // returns WP_Post object.
	}
	if ( ! wp_doing_ajax() ) {
		if ( isset( $order->get_items()[ $item_id ] ) ) {
			$item_total = ap_calculate_item_total( $order, $item_type = 'line_item' );
		}
		if ( ! empty( $order->get_items( 'fee' )[ $item_id ] ) ) {
			$item_total = ap_calculate_item_total( $order, $item_type = 'fee' );
		}
		if ( ! empty( $order->get_items( 'shipping' )[ $item_id ] ) ) {
			$item_total = ap_calculate_item_total( $order, $item_type = 'shipping' );
		}

		if ( isset( $item_total[ $item_id ] ) ) {
			echo '<td class="item_total_cost">
				<div class="view alignright">'
				. wc_price( $item_total[ $item_id ], array( 'currency' => $order->get_currency() ) ) // phpcs:ignore
				. '</div></td>';
		}
		return;
	}
}
