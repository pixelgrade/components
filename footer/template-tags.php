<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Footer
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display the classes for the footer element.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
 * @param int|WP_Post $post    Optional. Post ID or WP_Post object. Defaults to current post.
 */
function pixelgrade_footer_class( $class = '', $location = '', $post = null ) {
	// Separates classes with a single space, collates classes for footer element
	echo 'class="' . join( ' ', pixelgrade_get_footer_class( $class, $location, $post ) ) . '"';
}

/**
 * Retrieve the classes for the footer element as an array.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
 * @param int|WP_Post $post    Optional. Post ID or WP_Post object. Defaults to current post.
 *
 * @return array Array of classes.
 */
function pixelgrade_get_footer_class( $class = '', $location = '', $post = null ) {
	// We might be on a page set as a page for posts and the $post will be the first post in the loop
	// So we check first
	if ( empty( $post ) && is_home() ) {
		// find the id of the page for posts
		$post = get_option( 'page_for_posts' );
	}

	// First make sure we have a post
	$post = get_post( $post );

	$classes = array();

	$classes[] = 'c-footer';
	$classes[] = 'u-content__sides-spacing';
	$classes[] = 'u-content_container_margin_top';

	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}
		$classes = array_merge( $classes, $class );
	} else {
		// Ensure that we always coerce class to being an array.
		$class = array();
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS footer classes for the current post or page
	 *
	 * @param array $classes An array of footer classes.
	 * @param array $class   An array of additional classes added to the footer.
	 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
	 * @param int|WP_Post $post    Optional. Post ID or WP_Post object. Defaults to current post.
	 */
	$classes = apply_filters( 'pixelgrade_footer_class', $classes, $class, $location, $post );

	return array_unique( $classes );
}

/**
 * Displays the footer.
 *
 * @param string $location Optional. This is a hint regarding the place/template where this header is being displayed
 */
function pixelgrade_the_footer( $location = '' ) {
	pxg_load_component_file( 'footer', 'templates/footer', '', false );
}