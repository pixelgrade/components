<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Header
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display the classes for the header element.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
 */
function pixelgrade_header_class( $class = '', $location = '' ) {
	// Separates classes with a single space, collates classes for header element
	echo 'class="' . join( ' ', pixelgrade_get_header_class( $class, $location ) ) . '"';
}

/**
 * Retrieve the classes for the header element as an array.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
 *
 * @return array Array of classes.
 */
function pixelgrade_get_header_class( $class = '', $location = '' ) {
	$classes = array();

	$classes[] = 'site-header';

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
	 * Filters the list of CSS header classes for the current post or page
	 *
	 * @param array $classes An array of header classes.
	 * @param array $class   An array of additional classes added to the header.
	 */
	$classes = apply_filters( 'pixelgrade_header_class', $classes, $class, $location );

	return array_unique( $classes );
}

/**
 * Displays the header.
 *
 * @param string $location Optional. This is a hint regarding the place/template where this header is being displayed
 */
function pixelgrade_the_header( $location = '' ) {
	pxg_load_component_file( 'header', 'templates/header', '', false );
}

/**
 * Get the markup for the left menu.
 *
 * @return false|object
 */
function pixelgrade_header_get_the_left_menu() {
	return wp_nav_menu( apply_filters( 'pixelgrade_header_primary_left_nav_args', array(
		'theme_location'  => 'primary-left',
		'menu_id'         => 'menu-1',
		'container'       => 'nav',
		'container_class' => '',
		'fallback_cb'     => false,
		'echo'            => false,
	) ) );
}

/**
 * Get the markup for the right menu.
 *
 * @return false|object
 */
function pixelgrade_header_get_the_right_menu() {
	return wp_nav_menu( apply_filters( 'pixelgrade_header_primary_right_nav_args', array(
		'theme_location'  => 'primary-right',
		'menu_id'         => 'menu-2',
		'container'       => 'nav',
		'container_class' => '',
		'fallback_cb'     => false,
		'echo'            => false,
	) ) );
}