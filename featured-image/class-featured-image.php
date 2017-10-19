<?php
/**
 * Replaces the featured image with a more advanced metabox that has a featured image and a hover image
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Featured-Image
 * @version    1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Featured_Image extends Pixelgrade_Component_Main {

	const COMPONENT_SLUG = 'featured-image';

	/**
	 * Setup the featured image config
	 */
	public function setup_config() {
		// By default we will not show the new featured image metabox for any post type.
		// It is up to other components (like the portfolio component) or the theme to "declare" (via the filters bellow)
		// what post types should use it.
		$this->config = array(
			//'post_types' => array( 'post', 'jetpack-portfolio', ),
		);

		// Allow others to make changes to the config
		// Make the hooks dynamic and standard
		$hook_slug = self::prepare_string_for_hooks( self::COMPONENT_SLUG );
		$modified_config = apply_filters( "pixelgrade_{$hook_slug}_initial_config", $this->config, self::COMPONENT_SLUG );

		// Check/validate the modified config
		if ( method_exists( $this, 'validate_config' ) && ! $this->validate_config( $modified_config ) ) {
			_doing_it_wrong( __METHOD__, sprintf( 'The component config  modified through the "pixelgrade_%1$s_initial_config" dynamic filter is invalid! Please check the modifications you are trying to do!', $hook_slug ), '1.0.0' );
			return;
		}

		// Change the component's config with the modified one
		$this->config = $modified_config;

		// Filter only the post types for legacy reasons
		// @todo Evaluate if these legacy reasons still stand
		if ( isset( $this->config['post_types'] ) ) {
			$this->config['post_types'] = apply_filters( "pixelgrade_{$hook_slug}_post_types", $this->config['post_types'] );
		}
	}

	/**
	 * Load, instantiate and hook up.
	 */
	public function fire_up() {
		/**
		 * Load and instantiate various classes
		 */

		// The class that handles the metaboxes
		pixelgrade_load_component_file( self::COMPONENT_SLUG, 'inc/class-metaboxes' );
		Pixelgrade_Featured_Image_Metaboxes::instance( $this );

		/**
		 * Register our actions and filters
		 */
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		// Nothing to do here right now

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( "pixelgrade_portfolio_registered_hooks" );
	}
}
