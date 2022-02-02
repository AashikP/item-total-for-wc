<?php
/**
 * Plugin Name: Item total for WooCommerce
 * Description: Simple plugin to display Item total in the order details page.
 * Version: 1.0.1
 * Author: AashikP
 * Author URI: https://aashikp.com
 * Text Domain: item-total-for-wc
 * Requires at least: 4.9.14
 * Requires PHP: 7.3.5
 * WC requires at least: 3.5.0
 * WC tested up to: 5.3.0
 *
 * @package Item-Total-for-WC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	 * Display tax included price under order details page.
	 */
	function ap_item_total_header() {
		echo '<th width=100%;">' . esc_html__( 'Item Total', 'item-total-for-wc' ) . wc_help_tip( esc_html__( 'Item total = [Cost * Qty]+Tax total. This is the actual product total paid by the customer, and Cost = Price of the product minus Discounts (if any)', 'item-total-for-wc' ) ) . '</th>';
	}
	add_action( 'woocommerce_admin_order_item_headers', 'ap_item_total_header' );

	/**
	 * Display the total tax for a line item.
	 *
	 * @param object $product Product object.
	 */
	function ap_display_item_total( $product ) {
		global $post;
		if ( ! is_int( $thepostid ) ) {
			$thepostid = $post->ID;
		}
		if ( ! wp_doing_ajax() ) {
			$order       = wc_get_order( $thepostid );
			$order_items = $order->get_items();
			foreach ( $order->get_items() as $item_id => $item ) {
				if ( $item['product_id'] === $product->id ) {
					$price = $item['total_tax'] + $item['total'];
				}
			}
			$test  = '<td class="item_total_cost">';
			$test .= '<div class="view alignleft">';
			$test .= wc_price( $price, array( 'currency' => $order->get_currency() ) );
			$test .= '</span></div></td>';
			echo $test;
		}
	}
	add_action( 'woocommerce_admin_order_item_values', 'ap_display_item_total' );
}
