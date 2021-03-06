<?php
/**
 * The template for displaying all product archives.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/archive-product.php` or in `/templates/woocommerce/archive-product.php`.
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

// Let the template parts know about our location
$location = pixelgrade_set_location( 'product');

pixelgrade_get_header();

pixelgrade_render_block( 'woocommerce/archive-product' );

pixelgrade_get_footer();
