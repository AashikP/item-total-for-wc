<?php
/**
 * Plugin Name: Item total for WooCommerce
 * Description: Simple plugin to display Item total in the order details page.
 * Version: 1.2.0
 * Author: AashikP
 * Author URI: https://aashikp.com
 * Text Domain: item-total-for-wc
 * Requires at least: 5.0
 * Requires PHP: 7.4
 * WC requires at least: 3.5.0
 * WC tested up to: 6.2.0
 *
 * @package Item-Total-for-WC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {

	/**
	 * Display tax included price under order details page.
	 */
	function ap_item_total_header() {
		echo '<th class="item_total_cost sortable" data-sort="float" style="width:100%;text-align:right;">' . esc_html__( 'Item Total', 'item-total-for-wc' )
		. '</th>'; // phpcs:ignore
	}
	add_action( 'woocommerce_admin_order_item_headers', 'ap_item_total_header' );

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
		global $post;
		$order = wc_get_order( $post->ID );
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
	add_action( 'woocommerce_after_order_fee_item_name', 'ap_display_item_total' );
	add_action( 'woocommerce_after_order_itemmeta', 'ap_display_item_total' );
}
