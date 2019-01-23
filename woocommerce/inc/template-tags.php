<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function is_woo_archive() {
	return ( function_exists('is_shop') && is_shop() ) || ( function_exists('is_product_taxonomy') && is_product_taxonomy() );
}
