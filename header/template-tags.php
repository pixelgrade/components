<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Header
 * @version     1.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display the classes for the header element.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
 * @param int|WP_Post $post    Optional. Post ID or WP_Post object. Defaults to current post.
 */
function pixelgrade_header_class( $class = '', $location = '', $post = null ) {
	// Separates classes with a single space, collates classes for header element
	echo 'class="' . join( ' ', pixelgrade_get_header_class( $class, $location, $post ) ) . '"';
}

/**
 * Retrieve the classes for the header element as an array.
 *
 * @param string|array $class One or more classes to add to the class list.
 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
 * @param int|WP_Post $post    Optional. Post ID or WP_Post object. Defaults to current post.
 *
 * @return array Array of classes.
 */
function pixelgrade_get_header_class( $class = '', $location = '', $post = null ) {
	// We might be on a page set as a page for posts and the $post will be the first post in the loop
	// So we check first
	if ( empty( $post ) && is_home() ) {
		// find the id of the page for posts
		$post = get_option( 'page_for_posts' );
	}

	// First make sure we have a post
	$post = get_post( $post );

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
	 * @param string $location The place (template) where the classes are displayed. This is a hint for filters.
	 * @param int|WP_Post $post    Optional. Post ID or WP_Post object. Defaults to current post.
	 */
	$classes = apply_filters( 'pixelgrade_header_class', $classes, $class, $location, $post );

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
 * Get the markup for a certain nav menu location.
 *
 * @param array $config An array with options for the wp_nav_menu() function.
 * @param string $menu_location Optional. The menu location id (slug) to process.
 *
 * @return false|object
 */
function pixelgrade_header_get_nav_menu( $args, $menu_location = '' ) {
	$defaults = array( 'container' => 'nav', 'echo' => false, );

	if ( ! empty( $menu_location ) ) {
		// Make sure we overwrite whatever is there
		$args['theme_location'] = $menu_location;
	}

	// We really don't want others to say to echo - You shall not echo!!! (for LOTR fans)
	if ( isset( $args['echo'] ) ) {
		unset( $args['echo'] );
	}

	// Parse the sent arguments
	$args = wp_parse_args( $args, $defaults );

	// Allow others to have a say
	$args = apply_filters( 'pixelgrade_header_nav_menu_args', $args, $menu_location );

	// Return the nav menu
	return wp_nav_menu( $args );
}

/**
 * Tests the default configuration and determines if we have the needed things to work with to produce markup.
 *
 * @return bool
 */
function pixelgrade_header_is_valid_config() {
	// Get the component's configuration
	$config = Pixelgrade_Header()->get_config();

	// Test if we have no zones or no menu locations to show, even bogus ones
	if ( empty( $config['zones'] ) || empty( $config['menu_locations'] ) ) {
		return false;
	}

	return true;
}

/**
 * We will take the Header component config, process it and then we want to end up with a series of nav menu locations to display.
 * This includes the config bogus menu locations - this is actually their purpose: knowing where and when to display a certain special thing.
 *
 * @return array
 */
function pixelgrade_header_get_zones() {
	// Get the component's configuration
	$config = Pixelgrade_Header()->get_config();

	// Initialize the zones array with the configuration - we will build on it
	$zones = $config['zones'];

	// Cycle through each zone and determine the nav menu locations that will be shown - with input from others
	foreach ( $zones as $zone_id => $zone_settings ) {
		$zones[ $zone_id ]['menu_locations'] = array();
		// Cycle through each defined nav menu location and determine if it is a part of the current zone
		foreach ( $config['menu_locations'] as $menu_id => $menu_location ) {
			// A little sanity check
			if ( empty( $menu_location['default_zone'] ) ) {
				$menu_location['default_zone'] = '';
			}

			/**
			 * Allow others to filter the default zone this nav menu location should be shown.
			 *
			 * @param string $default_zone The default zone for this nav menu location as configured.
			 * @param array $menu_location_config The whole configuration for the current nav menu location.
			 * @param array $menu_locations_config The whole configuration for all the nav menu locations.
			 *
			 * @return string
			 */
			if ( $zone_id == apply_filters( "pixelgrade_header_{$menu_id}_nav_menu_display_zone", $menu_location['default_zone'], $menu_location, $config['menu_locations'] ) ) {
				$zones[ $zone_id ]['menu_locations'][ $menu_id ] = $menu_location;
			}
		}

		// Also setup the classes for the zone
		if ( empty( $zones[ $zone_id ]['classes'] ) ) {
			$zones[ $zone_id ]['classes'] = array();
		}

		$default_classes = array( 'c-navbar__zone', 'c-navbar__zone--' . $zone_id );
		$zones[ $zone_id ]['classes'] = array_merge( $default_classes, $zone_settings['classes'] );
	}

	// Now allow others to have a final go, maybe some need a more global view to decide (CSS classes or special ordering maybe?)
	$zones = apply_filters( 'pixelgrade_header_final_zones_setup', $zones, $config );

	// It it time to wrap this puppy up
	// First order the zones, ascending by 'order'
	uasort( $zones, 'pixelgrade_header_order_cmp' );

	return $zones;
}

/**
 * Retrieve the nav menu locations of a certain zone.
 *
 * @param string $zone_id The zone's identifier.
 * @param array $zone The zone's configuration.
 *
 * @return bool|array
 */
function pixelgrade_header_get_zone_nav_menu_locations( $zone_id, $zone ) {
	// Bail if we have nothing to work with
	if ( empty( $zone['menu_locations'] ) ) {
		return false;
	}

	$menu_locations = $zone['menu_locations'];

	// Order the menu_locations in the current zone by 'order'
	uasort( $menu_locations, 'pixelgrade_header_order_cmp' );
	
	return $menu_locations;
}

/**
 * It will order a multidimensional associative array by the value of the 'order' entry.
 *
 * @param array $a
 * @param array $b
 *
 * @return int
 */
function pixelgrade_header_order_cmp( array $a, array $b ) {
	// If the order is missing, default to 0, else sanitize
	if ( ! isset( $a['order'] ) ) {
		$a['order'] = 0;
	} else {
		$a['order'] = (int) $a['order'];
	}

	if ( ! isset( $b['order'] ) ) {
		$b['order'] = 0;
	} else {
		$b['order'] = (int) $b['order'];
	}

	// Do the comparison
	if ( $a['order'] < $b['order'] ) {
		return -1;
	} else if ( $a['order'] > $b['order'] ) {
		return 1;
	} else {
		return 0;
	}
}