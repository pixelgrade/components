<?php
/**
 * This is the class that handles the Customizer behaviour of our Footer component.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Footer
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Footer_Customizer {

	/**
	 * The main component object (the parent).
	 * @var     Pixelgrade_Footer
	 * @access  public
	 * @since     1.0.0
	 */
	public $parent = null;

	/**
	 * @var Pixelgrade_Footer_Customizer The single instance of the class
	 */
	private static $_instance = null;

	/**
	 * Pixelgrade_Footer_Customizer constructor.
	 *
	 * @param Pixelgrade_Footer $parent
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		// Register our actions and filters
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		/*
		 * ================================
		 * Tackle the Customify sections and fields
		 */

		/**
		 * A few important notes regarding the capabilities that are at hand when configuring the Customizer sections:
		 *
		 * Each section, besides the 'options' array entry (aka the section fields), has a series of configurable attributes.
		 * These are the defaults being used:
		 *
		 * 'priority'       => 10, // This controls the order of each section (lower priority means earlier - towards the top)
		 * 'panel'          => $panel_id,
		 * 'capability'     => 'edit_theme_options', // what capabilities the current logged in user needs to be able to see this section
		 * 'theme_supports' => '', // if the theme needs to declare some theme-supports for this section to be shown
		 * 'title'          => __( 'Title Section is required', '' ),
		 * 'description'    => '',
		 * 'type'           => 'default',
		 * 'description_hidden' => false, // If the description should be hidden behind a (?) bubble
		 *
		 *  @see WP_Customize_Section for more details about each of them.
		 *
		 * A few important notes regarding the capabilities that are at hand when configuring the 'options' (aka the fields):
		 *
		 * The array key of each option is the field ID.
		 * Each option (aka field) has a series of configurable attributes.
		 * These are the defaults being used:
		 *  'type'              => 'text',  // The field type
		 *  'label'             => '',      // The field label
		 *  'priority'          => 10,      // This controls the order of each field (lower priority means earlier - towards the top)
		 *  'desc'              => '',      // The field description
		 *  'choices'           => array(), // Used for radio, select, select2, preset, and radio_image types
		 *  'input_attrs'       => array(), // Used for range types
		 *  'default'           => '',      // The default value of the field (numeric or string)
		 *  'capability'        => 'edit_theme_options', // What capabilities the current user needs to be able to see this field
		 *  'active_callback'   => '',      // A callback function to determine if the field should be shown or not
		 *  'sanitize_callback' => '',      // A callback function to sanitize the field value on save
		 *  'live'              => false,   // Whether to live refresh on option change
		 *
		 * There are our custom field types that support further attributes.
		 * For details
		 * @see PixCustomifyPlugin::register_field()
		 * A look at these core classes (that are used by Customify) might also reveal valuable insights
		 * @see WP_Customize_Setting
		 * @see WP_Customize_Control
		 */

		// Setup our footer Customify options
		add_filter( 'customify_filter_fields', array( $this, 'add_customify_options' ), 40, 1 );

		/*
		 * ================================
		 * Tackle the consequences of those Customify fields
		 * Meaning adding classes, data attributes, etc here and there
		 */

		// Add classes to the body element
		add_filter( 'body_class', array( $this, 'body_classes' ), 10, 1 );
	}

	/**
	 * Add the Customizer Footer section configuration, via Customify
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function add_customify_options( $options ) {
		$footer_section = array(
			// Footer
			'footer_section' => array(
				'title'   => esc_html__( 'Footer', 'components_txtd' ),
				'options' => array(
					'footer_options_customizer_tabs'    => array(
						'type' => 'html',
						'html' => '<nav class="section-navigation  js-section-navigation">
							<a href="#section-title-footer-layout">' . esc_html__( 'Layout', 'components_txtd' ) . '</a>
							<a href="#section-title-footer-colors">' . esc_html__( 'Colors', 'components_txtd' ) . '</a>
							</nav>',
					),

					// [Section] Layout
					'footer_title_layout_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-footer-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', 'components_txtd' ) . '</span>',
					),
					'copyright_text'               => array(
						'type'              => 'textarea',
						'label'             => esc_html__( 'Copyright Text', 'components_txtd' ),
						'desc'              => esc_html__( 'Set the text that will appear in the footer area. Use %year% to display the current year.', 'components_txtd' ),
						// This should be defined by the theme
						// Previously: sprintf( esc_html__( '%%year%% &copy; Handcrafted with love by the %1$s Team', 'components_txtd' ), '<a href="https://pixelgrade.com/" rel="designer">Pixelgrade</a>' ),
						'default'           => null,
						'sanitize_callback' => 'wp_kses_post',
						'live'              => array( '.c-footer__copyright-text' ),
					),
					'footer_top_spacing'           => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Top Spacing', 'components_txtd' ),
						'desc'        => '',
						'live'        => true,
						'default'     => null, // this should be set by the theme (previously 84)
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
						'label'       => esc_html__( 'bottom Spacing', 'components_txtd' ),
						'desc'        => '',
						'live'        => true,
						'default'     => null, // this should be set by the theme (previously 84)
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
						'label'   => esc_html__( 'Hide "Back To Top" Link', 'components_txtd' ),
						'default' => null, // this should be set by the theme (previously 1)
					),
					'footer_layout'                => array(
						'type'    => 'select',
						'label'   => esc_html__( '"Footer Area" Widgets Layout', 'components_txtd' ),
						'desc'    => esc_html__( 'Choose if you want the footer widgets stack into one column or spread to a row.', 'components_txtd' ),
						'default' => null, // this should be set by the theme (previously row)
						'choices' => array(
							'stacked' => esc_html__( 'Stacked', 'components_txtd' ),
							'row'     => esc_html__( 'Row', 'components_txtd' ),
						),
					),

					// [Section] COLORS
					'footer_title_colors_section'    => array(
						'type' => 'html',
						'html' => '<span id="section-title-footer-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', 'components_txtd' ) . '</span>',
					),
					'footer_body_text_color'       => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Body Text Color', 'components_txtd' ),
						'live'    => true,
						'default' => null, // this should be set by the theme (previously #757575)
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-footer',
							),
						),
					),
					'footer_links_color'           => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Links Color', 'components_txtd' ),
						'live'    => true,
						'default' => null, // this should be set by the theme (previously #000000)
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-footer a',
							),
						),
					),
					'footer_background'            => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Footer Background', 'components_txtd' ),
						'live'    => true,
						'default' => null, // this should be set by the theme (previously #F5FBFE)
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
		$modified_config = apply_filters( 'pixelgrade_footer_customify_section_options', $footer_section, $options );

		// Validate the default values
		// When we have defined in the original config 'default' => null, this means the theme (or someone) must define the value via the filter above.
		// We will trigger _doing_it_wrong() errors, but in production we will let it pass.
		Pixelgrade_Config::validate_customizer_section_config_defaults( $modified_config, $footer_section, 'pixelgrade_footer_customify_section_options' );

		// Assign the modified config
		$footer_section = $modified_config;

		//make sure we are in good working order
		if ( empty( $options['sections'] ) ) {
			$options['sections'] = array();
		}

		//append the footer section
		$options['sections'] = $options['sections'] + $footer_section;

		return $options;
	}

	/**
	 * Add the body classes according to component's Customify options
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		// Bail if we are in the admin area
		if ( is_admin() ) {
			return $classes;
		}

		if ( 'stacked' == pixelgrade_option( 'footer_layout' ) ) {
			$classes[] = 'u-footer-layout-stacked';
		}

		return $classes;
	}

	/**
	 * Check if the class has been instantiated.
	 *
	 * @return bool
	 */
	public static function is_active() {
		if ( ! is_null( self::$_instance ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Main Pixelgrade_Footer_Customizer Instance
	 *
	 * Ensures only one instance of Pixelgrade_Footer_Customizer is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @param Pixelgrade_Footer $parent
	 *
	 * @return Pixelgrade_Footer_Customizer
	 */
	public static function instance( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}

		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components_txtd' ), esc_html( $this->parent->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components_txtd' ),  esc_html( $this->parent->_version ) );
	} // End __wakeup ()
}
