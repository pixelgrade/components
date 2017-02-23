<?php
/**
 * This is the main class of our Header component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Header
 * @version     1.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Load our component's template tags
pxg_load_component_file( 'header', 'template-tags' );

class Pixelgrade_Header {

	public $component = 'header';
	public $_version  = '1.2.0';
	public $_assets_version = '1.0.3';

	private $config = array();

	private static $_instance = null;

	public function __construct() {
		// Register our actions and filters
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		/**
		 * Setup the menus
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 */
		add_action( 'after_setup_theme', array( $this, 'header_setup' ) );
		/*
		 * All the filters bellow and the ones as 'zone_callback' follow the logic outlined in the component's guides as default behaviour.
		 * @link http://pixelgrade.github.io/guides/components/header
		 * They try to automatically adapt to the existence or non-existence of navbar components: the menus and the logo.
		 */
		//Conditional zone classes
		add_filter( 'pixelgrade_css_class', array( $this, 'nav_menu_zone_classes' ), 10, 3 );

		// Setup our header Customify options
		add_filter( 'customify_filter_fields', array( $this, 'add_customify_options' ), 20, 1 );

		/* Hook-up to various places where we need to output things */

		//Output the primary header markup
		add_action( 'pixelgrade_header', 'pixelgrade_the_header', 10, 1  );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_header_registered_hooks' );
	}

	/**
	 * Setup the navigation menus
	 */
	public function header_setup() {
		// Initialize the $config
		$this->config = array(
			'zones' => array(
				'left' => array( // the zone's id
					'order' => 10, // We will use this to establish the display order of the zones
					'classes' => array(), //by default we will add the classes 'c-navbar__zone' and 'c-navbar__zone--%zone_id%' to each zone
					'display_blank' => true, // determines if we output markup for an empty zone
				),
				'middle' => array( // the zone's id
					'order' => 20, // We will use this to establish the display order of the zones
					'classes' => array(), //by default we will add the classes 'c-navbar__zone' and 'c-navbar__zone--%zone_id%' to each zone
					'display_blank' => true, // determines if we output markup for an empty zone
				),
				'right' => array( // the zone's id
					'order' => 30, // We will use this to establish the display order of the zones
					'classes' => array(), //by default we will add the classes 'c-navbar__zone' and 'c-navbar__zone--%zone_id%' to each zone
					'display_blank' => true, // determines if we output markup for an empty zone
				),
			),
			'menu_locations' => array(
				'primary-left' => array(
					'title' => esc_html__( 'Header Left', 'components' ),
					'default_zone' => 'left',
					// This callback should always accept 3 parameters as documented in pixelgrade_header_get_zones()
					'zone_callback' => false,
					'order' => 10, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'nav_menu_args' => array( // skip 'theme_location' and 'echo' args as we will force those
						'menu_id'         => 'menu-1',
						'container'       => 'nav',
						'container_class' => '',
						'fallback_cb'     => false,
					),
				),
				'header-branding' => array(
					'default_zone' => 'middle',
					// This callback should always accept 3 parameters as documented in pixelgrade_header_get_zones()
					'zone_callback' => array( $this, 'header_branding_zone' ),
					'order' => 10, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'bogus' => true, // this tells the world that this is just a placeholder, not a real nav menu location
				),
				'primary-right' => array(
					'title' => esc_html__( 'Header Right', 'components' ),
					'default_zone' => 'right',
					// This callback should always accept 3 parameters as documented in pixelgrade_header_get_zones()
					'zone_callback' => array( $this, 'primary_right_nav_menu_zone' ),
					'order' => 10, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'nav_menu_args' => array( // skip 'theme_location' and 'echo' args as we will force those
						'menu_id'         => 'menu-2',
						'container'       => 'nav',
						'container_class' => '',
						'fallback_cb'     => false,
					),
				),
			),
		);

		// Add theme support for Jetpack Social Menu, if we are allowed to
		if ( apply_filters( 'pixelgrade_header_use_jetpack_social_menu', true ) ) {
			// Add it to the config
			$this->config['menu_locations']['jetpack-social-menu'] = array(
					'default_zone' => 'right',
					// This callback should always accept 3 parameters as documented in pixelgrade_header_get_zones()
					'zone_callback' => false,
					'order' => 20, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'bogus' => true, // this tells the world that this is just a placeholder, not a real nav menu location
				);

			// Add support for it
			add_theme_support( 'jetpack-social-menu' );
		}


		// Allow others to make changes to the config
		$this->config = apply_filters( 'pixelgrade_header_config', $this->config );

		// We are done with the config. Lets ge to it

		// Register the config menu locations
		$this->register_nav_menus();

		// Register the config zone callbacks
		$this->register_zone_callbacks();

		/**
		 * Add theme support for site logo, if we are allowed to
		 *
		 * First, it's the image size we want to use for the logo thumbnails
		 * Second, the 2 classes we want to use for the "Display Header Text" Customizer logic
		 */
		if ( apply_filters( 'pixelgrade_header_use_custom_logo', true ) ) {
			add_theme_support( 'custom-logo', apply_filters( 'pixelgrade_header_site_logo', array(
				'height'      => 600,
				'width'       => 1360,
				'flex-height' => true,
				'flex-width'  => true,
				'header-text' => array(
					'site-title',
					'site-description-text',
				)
			) ) );
		}
	}

	public function get_config() {
		return $this->config;
	}

	/**
	 * Register the needed menu locations based on the current configuration.
	 *
	 * @return bool
	 */
	private function register_nav_menus() {
		if ( ! empty( $this->config['menu_locations'] ) ) {
			$menus = array();
			foreach ( $this->config['menu_locations'] as $id => $settings ) {
				// Make sure that we ignore bogus menu locations
				if ( empty( $settings['bogus'] ) ) {
					if ( ! empty( $settings['title'] ) ) {
						$menus[ $id ] = $settings['title'];
					} else {
						$menus[ $id ] = $id;
					}
				}
			}

			if ( ! empty( $menus ) ) {
				register_nav_menus( $menus );

				// We registered some menu locations. Life is good. Share it.
				return true;
			}
		}

		// It seems that we didn't do anything. Let others know
		return false;
	}

	/**
	 * Register the needed zone callbacks for each nav menu location based on the current configuration.
	 */
	private function register_zone_callbacks() {
		if ( ! empty( $this->config['menu_locations'] ) ) {
			foreach ( $this->config['menu_locations'] as $menu_id => $settings ) {
				if ( ! empty( $settings['zone_callback'] ) ) {
					// Add the filter
					add_filter( "pixelgrade_header_{$menu_id}_nav_menu_display_zone", $settings['zone_callback'], 10, 3 );
				}
			}
		}
	}

	/**
	 * Change the primary-right nav menu's zone depending on the other nav menus.
	 *
	 * @param string $default_zone
	 * @param array $menu_location_config
	 * @param array $menu_locations_config
	 *
	 * @return string
	 */
	public function primary_right_nav_menu_zone( $default_zone, $menu_location_config, $menu_locations_config ) {
		// if there is no left zone menu we will show the right menu in the middle zone, not the right zone
		if ( ! has_nav_menu( 'primary-left' ) ) {
			$default_zone = 'middle';
		}

		return $default_zone;
	}

	/**
	 * Change the branding's zone depending on the other nav menus.
	 *
	 * @param string $default_zone
	 * @param array $menu_location_config
	 * @param array $menu_locations_config
	 *
	 * @return string
	 */
	public function header_branding_zone( $default_zone, $menu_location_config, $menu_locations_config ) {
		// the branding goes to the left zone when there is no left menu, but there is a right menu
		if ( ! has_nav_menu( 'primary-left' ) && has_nav_menu( 'primary-right' ) ) {
			$default_zone = 'left';
		}

		return $default_zone;
	}

	/**
	 * Change the zone classes depending on the other nav menus.
	 *
	 * @param array $classes An array of header classes.
	 * @param array $class   An array of additional classes added to the header.
	 * @param string|array $location   The place (template) where the classes are displayed.
	 *
	 * @return array
	 */
	public function nav_menu_zone_classes( $classes, $class, $location ) {
		$has_left_menu   = has_nav_menu( 'primary-left' );
		$has_right_menu  = has_nav_menu( 'primary-right' );

		if ( pixelgrade_in_location( 'left', $location ) ) {
			if ( $has_left_menu && $has_right_menu ) {
				$classes[] = 'c-navbar__zone--push-right';
			}
		}

		if ( pixelgrade_in_location( 'right', $location ) ) {
			if ( ! $has_right_menu || ( ! $has_left_menu && $has_right_menu ) ) {
				$classes[] = 'c-navbar__zone--push-right';
			}
		}

		return $classes;
	}

	public function add_customify_options( $options ) {
		// Recommended Fonts List - Headings
		$recommended_headings_fonts = apply_filters( 'pixelgrade_header_customify_recommended_headings_fonts',
			array(
				'Playfair Display',
				'Oswald',
				'Lato',
				'Open Sans',
				'Exo',
				'PT Sans',
				'Ubuntu',
				'Vollkorn',
				'Lora',
				'Arvo',
				'Josefin Slab',
				'Crete Round',
				'Kreon',
				'Bubblegum Sans',
				'The Girl Next Door',
				'Pacifico',
				'Handlee',
				'Satify',
				'Pompiere'
			)
		);

		$header_section = array(
			// Header
			'header_section' => array(
				'title'   => esc_html__( 'Header', 'components' ),
				'options' => array(
					'header_options_customizer_tabs'        => array(
						'type' => 'html',
						'html' => '<nav class="section-navigation  js-section-navigation">
							<a href="#section-title-header-layout">' . esc_html__( 'Layout', 'components' ) . '</a>
							<a href="#section-title-header-colors">' . esc_html__( 'Colors', 'components' ) . '</a>
							<a href="#section-title-header-fonts">' . esc_html__( 'Fonts', 'components' ) . '</a>
							</nav>',
					),
					// [Section] Layout
					'header_title_layout_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-header-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'components' ) . '</span>',
					),
					'header_logo_height'              => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Logo Height', 'components' ),
						'desc'        => esc_html__( 'Adjust the max height of your logo container.', 'components' ),
						'live'        => true,
						'default'     => 23,
						'input_attrs' => array(
							'min'          => 20,
							'max'          => 200,
							'step'         => 1,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property' => 'max-height',
								'selector' => '.site-logo img, .custom-logo-link img',
								'unit'     => 'px',
							),
							array(
								'property' => 'font-size',
								'selector' => '.site-title',
								'unit'     => 'px',
							),
						),
					),
					'header_height' => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Header Height', 'components' ),
						'desc'        => esc_html__( 'Adjust the header and navigation bar height.', 'components' ),
						'live'        => true,
						'default'     => 100,
						'input_attrs' => array(
							'min'          => 40,
							'max'          => 200,
							'step'         => 10,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property' => 'height',
								'selector' => '.c-navbar',
								'unit'     => 'px',
							),
							array(
								'property' => 'padding-top',
								'selector' => 'body.u-header-sticky',
								'unit'     => 'px',
							),
						),
					),
					'header_navigation_links_spacing' => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Navigation Link Spacing', 'components' ),
						'desc'        => esc_html__( 'Adjust the spacing between individual items in your navigation.', 'components' ),
						'live'        => true,
						'default'     => 40,
						'input_attrs' => array(
							'min'          => 12,
							'max'          => 75,
							'step'         => 1,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => 'margin-left',
								'selector'        => '.c-navbar ul',
								'unit'            => 'px',
								'callback_filter' => 'typeline_spacing_cb',
							),
							array(
								'property'        => 'margin-right',
								'selector'        => '.c-navbar li',
								'unit'            => 'px',
								'callback_filter' => 'typeline_spacing_cb',
							),
						),
					),

					'header_position' => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Header Position', 'components' ),
						'desc'    => esc_html__( 'Choose if you want a static menu or a fixed (sticky) one that stays visible no matter how much you scroll the page.', 'components' ),
						'default' => 'sticky',
						'choices' => array(
							'static' => esc_html__( 'Static', 'components' ),
							'sticky' => esc_html__( 'Sticky (fixed)', 'components' ),
						),
					),
					'header_width'    => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Header Width', 'components' ),
						'desc'    => esc_html__( 'Choose if you want the header span to the full-browser or stay aligned with the site container width.', 'components' ),
						'default' => 'full',
						'choices' => array(
							'full'      => esc_html__( 'Full Browser Width', 'components' ),
							'container' => esc_html__( 'Container Width', 'components' ),
						),
					),

					'header_sides_spacing'          => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Header Sides Spacing', 'components' ),
						'desc'        => esc_html__( 'Adjust the space separating the header and the sides of the browser.', 'components' ),
						'live'        => true,
						'default'     => 50,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 140,
							'step'         => 1,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => 'padding-left',
								'selector'        => '.u-header_sides_spacing',
								'unit'            => 'px',
								'callback_filter' => 'typeline_spacing_cb',
							),
							array(
								'property'        => 'padding-right',
								'selector'        => '.u-header_sides_spacing',
								'unit'            => 'px',
								'callback_filter' => 'typeline_spacing_cb',
							),
						),
					),

					// [Section] COLORS
					'header_title_colors_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-header-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'components' ) . '</span>',
					),
					'header_navigation_links_color' => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Navigation Links Color', 'components' ),
						'live'    => true,
						'default' => '#252525',
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-navbar',
							),
						),
					),
					'header_links_active_color'     => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Links Active Color', 'components' ),
						'live'    => true,
						'default' => '#bf493d',
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '
								.c-navbar [class*="current-menu"],
								.c-navbar li:hover',
							),
							array(
								'property' => 'border-top-color',
								'selector' => '.c-navbar [class*="children"]:hover:after',
							),
						),
					),
					'header_links_active_style'     => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Links Active Style', 'components' ),
						'desc'    => esc_html__( '', 'components' ),
						'default' => 'active',
						'choices' => array(
							'active'    => esc_html__( 'Active', 'components' ),
							'underline' => esc_html__( 'Underline', 'components' ),
						),
					),
					'header_background'             => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Header Background', 'components' ),
						'live'    => true,
						'default' => '#FFFFFF',
						'css'     => array(
							array(
								'property' => 'background-color',
								'selector' => '.site-header',
							),
						),
					),

					// [Section] FONTS
					'header_title_fonts_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-header-fonts" class="separator section label large">&#x1f4dd;  ' . esc_html__( 'Fonts', 'components' ) . '</span>',
					),

					'header_page_title_font2' => array(
						'type'     			=> 'font',
						'label'            => esc_html__( 'Navigation Text', 'components' ),
						'desc'             => esc_html__( '', 'components' ),
						'selector'         => '.c-navbar.c-navbar',
						'callback' => 'typeline_font_cb',

						// Set the defaults
						'default'  => array(
							'font-family'    => 'Arca Majora',
							'font-weight'    => '400',
							'font-size'      => 11,
							'line-height'    => 1.181,
							'letter-spacing' => 0.154,
							'text-transform' => 'uppercase'
						),

						// List of recommended fonts defined by theme
						'recommended' => $recommended_headings_fonts,
						// Sub Fields Configuration (optional)
						'fields'   => array(
							'font-size'       => array(                           // Set custom values for a range slider
								'min'  => 8,
								'max'  => 60,
								'step' => 1,
								'unit' => 'px',
							),
							'line-height'     => array( 0, 2, 0.1, '' ),           // Short-hand version
							'letter-spacing'  => array( -1, 2, 0.01, 'em' ),
							'text-align'      => false,                           // Disable sub-field (False by default)
							'text-transform'  => true,
							'text-decoration' => false
						)
					),
				),
			),
		);

		//Allow others to make changes
		$header_section = apply_filters( 'pixelgrade_header_customify_section_options', $header_section, $options );

		//make sure we are in good working order
		if ( empty( $options['sections'] ) ) {
			$options['sections'] = array();
		}

		//append the header section
		$options['sections'] = $options['sections'] + $header_section;

		return $options;
	}

	/**
	 * Main Pixelgrade_Header Instance
	 *
	 * Ensures only one instance of Pixelgrade_Header is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Header()
	 * @return Pixelgrade_Header
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components' ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components' ),  esc_html( $this->_version ) );
	} // End __wakeup ()
}