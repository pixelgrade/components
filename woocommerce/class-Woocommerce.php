<?php
/**
 * This is the main class of our Woocommerce component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pixelgrade_Woocommerce extends Pixelgrade_Component {

	const COMPONENT_SLUG = 'woocommerce';

	/**
	 * Pixelgrade_Woocommerce constructor.
	 *
	 * @param string $version Optional. The current component version.
	 * @param array  $args    Optional. Various arguments for the component initialization (like different priorities for the init hooks).
	 *
	 * @throws Exception
	 */
	public function __construct( $version = '1.0.0', $args = array() ) {
		// We want the component fire_up to happen before the init action in PixTypesPlugin that has a priority of 15,
		// but quite late because of Jetpack and it's (rather good) lateness in loading the Woocommerce CPT
		if ( ! isset( $args['init']['priorities']['fire_up'] ) ) {
			$args['init']['priorities']['fire_up'] = 14;
		}

		parent::__construct( $version, $args );

		$this->assets_version = '1.0.0';
	}

	/**
	 * Setup the woocommerce component config
	 */
	public function setupConfig() {

		$this->config['theme_support'] = array(
			'woocommerce',
			'wc-product-gallery-zoom',
			'wc-product-gallery-lightbox',
			'wc-product-gallery-slider',
		);

		$this->config['templates'] = array(
			'single-product' => array(
				'type'      => 'single',
				'checks'    => array(
					'function' => 'is_singular',
					'args' => array( array( 'product' ) ),
				),
				'templates' => array(
					array(
						'slug' => 'single-product',
						'name' => '',
					),
				),
			),
		);

		// Allow others to make changes to the config
		// Make the hooks dynamic and standard
		$hook_slug       = self::prepareStringForHooks( self::COMPONENT_SLUG );
		$modified_config = apply_filters( "pixelgrade_{$hook_slug}_initial_config", $this->config, self::COMPONENT_SLUG );

		// Check/validate the modified config
		if ( method_exists( $this, 'validate_config' ) && ! $this->validate_config( $modified_config ) ) {
			/* translators: 1: the component slug  */
			_doing_it_wrong( __METHOD__, sprintf( 'The component config  modified through the "pixelgrade_%1$s_initial_config" dynamic filter is invalid! Please check the modifications you are trying to do!', esc_html( $hook_slug ) ), null );
			return;
		}

		// Change the component's config with the modified one
		$this->config = $modified_config;
	}

	/**
	 * Load, instantiate and hook up.
	 */
	public function fireUp() {
		// We need to make sure that WooCommerce is all good.
		// There is no point in continuing if it is not.
		// Also, we will not fire up the component if the theme doesn't explicitly declare support for it.
		if ( ! current_theme_supports( $this->getThemeSupportsKey() ) || ! self::siteSupportsWoocommerce() ) {
			return;
		}

		/**
		 * Load and instantiate various classes
		 */
		// The class that handles the Customizer experience
		pixelgrade_load_component_file( self::COMPONENT_SLUG, 'inc/class-Woocommerce-Customizer' );
		Pixelgrade_Woocommerce_Customizer::instance( $this );

		// The class that handles markup and layout changes
		pixelgrade_load_component_file( self::COMPONENT_SLUG, 'inc/class-Woocommerce-Layout' );
		Pixelgrade_Woocommerce_Layout::instance( $this );

		/**
		 * Register our actions and filters
		 */
		$this->registerHooks();

		// Setup the component's custom page templates
		if ( ! empty( $this->config['page_templates'] ) ) {
			$this->page_templater = self::setupPageTemplates( $this->config['page_templates'], self::COMPONENT_SLUG );

			// Setup the custom loop for the page templates - if there are any
			add_action( 'parse_query', array( $this, 'setupPageTemplatesCustomLoopQuery' ) );
		}

		/**
		 * Setup the component's custom templates
		 */
		// We use a priority of 50 to make sure that we are pretty late (i.e. higher priority), but also leave room for other components to come in earlier or latter
		// For example the base template comes earlier at priority 20. This way our woocommerce templates take priority over the base ones.
		if ( ! empty( $this->config['templates'] ) ) {
			$this->templater = self::setupCustomTemplates( $this->config['templates'], self::COMPONENT_SLUG, 50 );
		}
	}

	/**
	 * Check if the CPT is in good working order before checking if the class is instantiated.
	 *
	 * @return bool
	 */
	public static function isActive() {
		// We need to make sure that the woocommerce CPT is all good
		// There is no point in continuing if it is not
		if ( ! self::siteSupportsWoocommerce() ) {
			return false;
		}

		return parent::isActive();
	}

	/**
	 * Determine if there is actual support for Woocommerce.
	 *
	 * @return bool
	 */
	public static function siteSupportsWoocommerce() {
		// For now, we will do just a simple test.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Register our actions and filters
	 */
	public function registerHooks() {
		// Enqueue the frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_woocommerce_registered_hooks' );

		// add classes
		add_filter( 'body_class', array( $this, 'bodyClasses' ) );
	}

	public function bodyClasses( $classes ) {
		$classes[] = 'woocommerce-support';
		return $classes;
	}

	public function enqueueScripts() {
		wp_enqueue_script( 'pixelgrade-woocommerce-component-scripts', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( self::COMPONENT_SLUG ) . 'js/scripts.js' ), array( 'jquery' ), $this->assets_version );
		wp_enqueue_style( 'pixelgrade-woocommerce-component-styles', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( self::COMPONENT_SLUG ) . 'css/style.css' ), array(), $this->assets_version );

		wp_localize_script( 'pixelgrade-woocommerce-component-scripts', 'pixelgradeWooCommerceStrings', array(
			'adding_to_cart' => esc_html__( 'Adding...', '__components_txtd' ),
			'added_to_cart' => esc_html__( 'Added!', '__components_txtd' ),
		) );
	}
}
