<?php
/**
 * The main zones of the navigation.
 *
 * This template can be overridden by copying it to a child theme in /components/header/templates/content-navbar.php
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Header
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Get the component's configuration
$config = Pixelgrade_Header()->get_config();

// bail if we have no zones or no menu locations to show, even bogus ones
if ( empty( $config['zones'] ) || empty( $config['menu_locations'] ) ) {
	return;
}
?>

<div class="c-navbar__content">

	<?php
	// We will take the Header component config, process it and then we want to end up with a series of nav menu locations to display
	// This includes the config bogus menu locations - this is actually their purpose: knowing where and when to display a certain special thing.

	// Initialize the zones array
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

			// Allow others to filter the default zone this nav menu location should be shown
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
	uasort( $zones, 'order_cmp' );
	function order_cmp( array $a, array $b ) {
		if ( $a['order'] < $b['order'] ) {
			return -1;
		} else if ( $a['order'] > $b['order'] ) {
			return 1;
		} else {
			return 0;
		}
	}

	// Cycle through each zone and display the nav menus or other "bogus" things
	foreach ( $zones as $zone_id => $zone ) {
		if ( empty( $zone['menu_locations'] ) && empty( $zone['display_blank'] ) ) {
			continue;
		}

		/**
		 * Do note that you can make use of the fact that we've used the pixelgrade_css_class() function to
		 * output the classes for each zone. You can use the `pixelgrade_css_class` filter and depending on
		 * the location received act accordingly.
		 */
		?>

		<div <?php pixelgrade_css_class( $zone['classes'], array( 'header', 'navbar', 'zone', $zone_id ) ); ?>>
			<?php
			// Order the menu_locations
			$menu_locations = $zone['menu_locations'];
			uasort( $menu_locations, 'order_cmp' );

			foreach ( $menu_locations as $menu_id => $menu_location ) {
				if ( ! empty( $menu_location['bogus'] ) ) {
					// We have something special to show
					if ( 'header-branding' == $menu_id ) {
						pxg_load_component_file( 'header', 'templates/branding', '', false );
					} elseif ( 'jetpack-social-menu' == $menu_id && function_exists( 'jetpack_social_menu' ) ) {
						jetpack_social_menu();
					}
				} else {
					// We have a nav menu location that we need to show
					// Make sure we have some nav_menu args
					if ( empty( $menu_location['nav_menu_args'] ) ) {
						$menu_location['nav_menu_args'] = array();
					}
					$nav_menu = pixelgrade_header_get_nav_menu( $menu_location['nav_menu_args'], $menu_id );

					if ( ! empty( $nav_menu ) ) {
						echo $nav_menu;
					}
				}
			}
			?>
		</div><!-- .c-navbar__zone -->

	<?php } ?>

</div><!-- .c-navbar__content -->