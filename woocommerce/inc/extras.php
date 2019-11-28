<?php
/**
 * Custom functions that act independently of the component templates.
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

/**
 * Change or add blocks to the blog component.
 */
function pixelgrade_woocommerce_change_blog_component_config() {

	Pixelgrade_BlocksManager()->registerBlock( 'blog/page', array(
		'blocks' => array(
			'woocommerce/cart',
			'woocommerce/checkout',
			'woocommerce/page',
			'woocommerce/archive-product',
		),
	) );
}
