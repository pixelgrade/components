<?php
/**
 * This is the main class of our Footer component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Footer
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Load our component's template tags
pxg_load_component_file( 'footer', 'template-tags' );

class Pixelgrade_Footer {

	public $component = 'footer';

	public $_version  = '1.1.0';

	public $_assets_version = '1.0.1';

	private $config = array();

	private static $_instance = null;

	public function __construct() {
		// Register our actions and filters
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks(){
		/**
		 * Setup the menus
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 */
		add_action( 'after_setup_theme', array( $this, 'footer_setup' ) );

		// Register the widget areas
		// We use a priority of 20 to make sure that this sidebar will appear at the end in Appearance > Widgets
		add_action( 'widgets_init', array( $this, 'register_sidebars' ), 20 );

		// Setup our header Customify options
		add_filter( 'customify_filter_fields', array( $this, 'add_customify_options' ), 40, 1 );

		/* Hook-up to various places where we need to output things */

		//Output the primary footer markup
		add_action( 'pixelgrade_footer', 'pixelgrade_the_footer', 10, 1  );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_footer_registered_hooks' );
	}

	/**
	 * Setup the navigation menus
	 */
	public function footer_setup() {
		// Initialize the $config
		$this->config = array(
			'zones' => array(
				'top' => array( // the zone's id
					'order' => 10, // We will use this to establish the display order of the zones
					'classes' => array(), //by default we will add the classes 'c-footer__zone' and 'c-footer__zone--%zone_id%' to each zone
					'display_blank' => false, // determines if we output markup for an empty zone
				),
				'middle' => array( // the zone's id
					'order' => 20, // We will use this to establish the display order of the zones
					'classes' => array(), //by default we will add the classes 'c-footer__zone' and 'c-footer__zone--%zone_id%' to each zone
					'display_blank' => true, // determines if we output markup for an empty zone
				),
				'bottom' => array( // the zone's id
					'order' => 30, // We will use this to establish the display order of the zones
					'classes' => array(), //by default we will add the classes 'c-footer__zone' and 'c-footer__zone--%zone_id%' to each zone
					'display_blank' => true, // determines if we output markup for an empty zone
				),
			),
			// The bogus items can sit in either sidebars or menu_locations.
			// It doesn't matter as long as you set their zone and order properly
			'sidebars' => array(
				'sidebar-footer' => array(
					'default_zone' => 'middle',
					// This callback should always accept 3 parameters as documented in pixelgrade_footer_get_zones()
					'zone_callback' => false,
					'order' => 10, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'container_class' => array( 'c-gallery', 'c-footer__gallery', 'o-grid', 'o-grid--4col-@lap' ), // classes to be added to the sidebar <aside> wrapper
					'sidebar_args' => array( // skip 'id' arg as we will force that
						'name' => esc_html__( 'Footer', 'components' ),
						'description'   => esc_html__( 'Widgets displayed in the Footer Area of the website.', 'components' ),
						'class'         => 'c-gallery c-footer__gallery o-grid o-grid--4col-@lap', // in case you need some classes added to the sidebar - in the WP Admin only!!!
						'before_widget' => '<div id="%1$s" class="c-gallery__item  c-widget  c-footer__widget  %2$s"><div class="o-wrapper u-container-width">',
						'after_widget'  => '</div></div>',
						'before_title'  => '<h3 class="c-widget__title h3">',
						'after_title'   => '</h3>',
					),
				),
			),
			'menu_locations' => array(
				'footer-back-to-top-link' => array(
					'default_zone' => 'bottom',
					// This callback should always accept 3 parameters as documented in pixelgrade_footer_get_zones()
					'zone_callback' => false,
					'order' => 5, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'bogus' => true, // this tells the world that this is just a placeholder, not a real nav menu location
				),
				'footer' => array(
					'title' => esc_html__( 'Footer', 'components' ),
					'default_zone' => 'bottom',
					// This callback should always accept 3 parameters as documented in pixelgrade_footer_get_zones()
					'zone_callback' => false,
					'order' => 10, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'nav_menu_args' => array( // skip 'theme_location' and 'echo' args as we will force those
						'menu_id'         => 'menu-footer',
						'container'       => 'nav',
						'container_class' => '',
						'depth'           => -1, //by default we will flatten the menu hierarchy, if there is one
						'fallback_cb'     => false,
					),
				),
				'footer-copyright' => array(
					'default_zone' => 'bottom',
					// This callback should always accept 3 parameters as documented in pixelgrade_footer_get_zones()
					'zone_callback' => false,
					'order' => 20, // We will use this to establish the display order of nav menu locations, inside a certain zone
					'bogus' => true, // this tells the world that this is just a placeholder, not a real nav menu location
				),
			),
		);

		// Add theme support for Jetpack Social Menu, if we are allowed to
		if ( apply_filters( 'pixelgrade_footer_use_jetpack_social_menu', false ) ) {
			// Add it to the config
			$this->config['menu_locations']['jetpack-social-menu'] = array(
				'default_zone' => 'bottom',
				// This callback should always accept 3 parameters as documented in pixelgrade_footer_get_zones()
				'zone_callback' => false,
				'order' => 15, // We will use this to establish the display order of nav menu locations, inside a certain zone
				'bogus' => true, // this tells the world that this is just a placeholder, not a real nav menu location
			);

			// Add support for it
			add_theme_support( 'jetpack-social-menu' );
		}


		// Allow others to make changes to the config
		$this->config = apply_filters( 'pixelgrade_footer_config', $this->config );

		// We are done with the config. Lets ge to it

		// Register the config nav menu locations, if we have any
		$this->register_nav_menus();

		// The sidebars are registered via the 'widgets_init' hook in $this->register_hooks()

		// Register the config zone callbacks
		$this->register_zone_callbacks();
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

	public function register_sidebars() {
		$registered_some_sidebars = false;
		if ( ! empty( $this->config['sidebars'] ) ) {
			$menus = array();
			foreach ( $this->config['sidebars'] as $id => $settings ) {
				// Make sure that we ignore bogus sidebars
				if ( empty( $settings['bogus'] ) ) {
					if ( empty( $settings['sidebar_args']['id'] ) ) {
						$settings['sidebar_args']['id'] = $id;
					}

//					var_dump($settings['sidebar_args']);
//					die();

					// Register a new widget area
					register_sidebar( $settings['sidebar_args'] );

					// Remember what we've done last summer :)
					$registered_some_sidebars = true;
				}
			}
		}

		// Let others know what we did.
		return $registered_some_sidebars;
	}

	/**
	 * Register the needed zone callbacks for each widget area and nav menu location based on the current configuration.
	 */
	private function register_zone_callbacks() {
		if ( ! empty( $this->config['sidebars'] ) ) {
			foreach ( $this->config['sidebars'] as $id => $settings ) {
				if ( ! empty( $settings['zone_callback'] ) ) {
					// Add the filter
					add_filter( "pixelgrade_footer_{$id}_widget_area_display_zone", $settings['zone_callback'], 10, 3 );
				}
			}
		}

		if ( ! empty( $this->config['menu_locations'] ) ) {
			foreach ( $this->config['menu_locations'] as $menu_id => $settings ) {
				if ( ! empty( $settings['zone_callback'] ) ) {
					// Add the filter
					add_filter( "pixelgrade_footer_{$menu_id}_nav_menu_display_zone", $settings['zone_callback'], 10, 3 );
				}
			}
		}
	}

	public function add_customify_options( $options ) {
		$footer_section = array(
			// Footer
			'footer_section' => array(
				'title'   => esc_html__( 'Footer', 'components' ),
				'options' => array(
					'footer_options_customizer_tabs'    => array(
						'type' => 'html',
						'html' => '<nav class="section-navigation  js-section-navigation">
							<a href="#section-title-footer-layout">' . esc_html__( 'Layout', 'components' ) . '</a>
							<a href="#section-title-footer-colors">' . esc_html__( 'Colors', 'components' ) . '</a>
							</nav>',
					),
					// [Section] Layout
					'footer_title_layout_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-footer-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'components' ) . '</span>',
					),
					'copyright_text'               => array(
						'type'              => 'textarea',
						'label'             => esc_html__( 'Copyright Text', 'components' ),
						'desc'              => esc_html__( 'Set the text that will appear in the footer area. Use %year% to display the current year.', 'components' ),
						'default'           => __( '%year% &copy; Handcrafted with love by <a href="https://pixelgrade.com" target="_blank">Pixelgrade</a> Team', 'components' ),
						'sanitize_callback' => 'wp_kses_post',
						'live'              => array( '.copyright-text' ),
					),
					'footer_top_spacing'           => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Top Spacing', 'components' ),
						'desc'        => '',
						'live'        => true,
						'default'     => 0,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 200,
							'step'         => 1,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => 'padding-top',
								'selector'        => '.c-footer',
								'unit'            => 'px',
								'callback_filter' => 'typeline_spacing_cb',
							),
						),
					),
					'footer_bottom_spacing'        => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Bottom Spacing', 'components' ),
						'desc'        => '',
						'live'        => true,
						'default'     => 90,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 200,
							'step'         => 1,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => 'padding-bottom',
								'selector'        => '.c-footer',
								'unit'            => 'px',
								'callback_filter' => 'typeline_spacing_cb',
							),
						),
					),
					'footer_hide_back_to_top_link' => array(
						'type'    => 'checkbox',
						'label'   => esc_html__( 'Hide "Back To Top" Link', 'components' ),
						'default' => 1,
					),
					'footer_layout'                => array(
						'type'    => 'select',
						'label'   => esc_html__( '"Footer Area" Widgets Layout', 'components' ),
						'desc'    => esc_html__( 'Choose if you want the footer widgets stack into one column or spread to a row.', 'components' ),
						'default' => 'row',
						'choices' => array(
							'stacked' => esc_html__( 'Stacked', 'components' ),
							'row'     => esc_html__( 'Row', 'components' ),
						),
					),


					// [Section] COLORS
					'footer_title_colors_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-footer-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'components' ) . '</span>',
					),
					'footer_body_text_color'       => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Body Text Color', 'components' ),
						'live'    => true,
						'default' => '#757575',
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-footer',
							),
						),
					),
					'footer_links_color'           => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Links Color', 'components' ),
						'live'    => true,
						'default' => '#000000',
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-footer a',
							),
						),
					),
					'footer_background'            => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Footer Background', 'components' ),
						'live'    => true,
						'default' => '#FFFFFF',
						'css'     => array(
							array(
								'property' => 'background',
								'selector' => '.u-footer__background',
							),
						),
					),
				)
			),
		);

		//Allow others to make changes
		$footer_section = apply_filters( 'pixelgrade_footer_customify_section_options', $footer_section, $options );

		//make sure we are in good working order
		if ( empty( $options['sections'] ) ) {
			$options['sections'] = array();
		}

		//append the header section
		$options['sections'] = $options['sections'] + $footer_section;

		return $options;
	}

	/**
	 * Main Pixelgrade_Footer Instance
	 *
	 * Ensures only one instance of Pixelgrade_Footer is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Footer()
	 * @return Pixelgrade_Footer
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
