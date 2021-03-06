<?php
/**
 * The template for displaying all single products.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/single-product.php` or in `/templates/woocommerce/single-product.php`.
 * @see pixelgrade_locate_component_template()
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Woocommerce
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

pixelgrade_get_header();

pixelgrade_render_block( 'woocommerce/single-product' );

pixelgrade_get_footer();
