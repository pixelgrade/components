<?php
/**
 * This is the main class of our Starter component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Starter
 * @version     0.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Load our component's template tags
pxg_load_component_file( 'starter', 'template-tags' );

class Pixelgrade_Starter {

	public $component = 'starter';
	public $_version  = '1.0.0';
	public $_assets_version = '1.0.0';

	private static $_instance = null;

	public function __construct() {
		// Register our actions and filters
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 *
	 * @return null
	 */
	public function register_hooks() {
		// Enqueue the frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Setup how things will behave in the WP admin area
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Enqueue assets for the admin
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Add some classes to the <article> for pages
		add_filter( 'post_class', array( $this, 'post_classes' ) );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_starter_registered_hooks' );
	}

	/**
	 * Enqueue styles and scripts on the frontend
	 */
	public function enqueue_scripts() {
		// Register the frontend styles and scripts specific to this component
		wp_register_script( 'pixelgrade_starter-scripts', trailingslashit( get_template_directory_uri() ) . 'components/starter/js/front.js', array( 'jquery' ), $this->_assets_version, true );

		// See if we need to enqueue something only on pages
		if ( is_page() ) {
			wp_enqueue_script( 'pixelgrade_starter-scripts' );
		}
	}

	/**
	 * Load on when the admin is initialized
	 */
	public function admin_init() {
		/* register the styles and scripts specific to heroes */
		wp_register_style( 'pixelgrade_starter-admin-style', trailingslashit( get_template_directory_uri() ) . 'components/starter/css/admin.css', array(), $this->_assets_version );
		wp_register_script( 'pixelgrade_starter-admin-scripts', trailingslashit( get_template_directory_uri() ) . 'components/starter/js/admin.js', array(), $this->_assets_version );

	}

	/**
	 * Enqueue scripts and styles for the admin area
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		/* enqueue the styles and scripts specific to heroes */
		if ( 'edit.php' != $hook ) {
			wp_enqueue_style( 'pixelgrade_starter-admin-style');
			wp_enqueue_script( 'pixelgrade_starter-admin-scripts' );
		}
	}

	/**
	 * Add custom classes for pages
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function post_classes( $classes ) {
		//we first need to know the bigger picture - the location this template part was loaded from
		$location = pixelgrade_get_location();

		if ( is_page() ) {
			$classes[] = 'article--page';
		}

		return $classes;
	}

	/**
	 * Main Pixelgrade_Starter Instance
	 *
	 * Ensures only one instance of Pixelgrade_Starter is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Starter()
	 * @return Pixelgrade_Starter
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  esc_html( $this->_version ) );
	} // End __wakeup ()
}