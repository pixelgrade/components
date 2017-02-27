<?php
/**
 * This is the main class of our Footer component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Footer
 * @version     1.0.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Load our component's template tags
pxg_load_component_file( 'footer', 'template-tags' );

class Pixelgrade_Footer {

	public $component = 'footer';
	public $_version  = '1.0.3';
	public $_assets_version = '1.0.1';

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
	public function register_hooks(){
		// Register the widget areas
		add_action( 'widgets_init', array( $this, 'register_widget_areas' ) );

		// Setup our header Customify options
		add_filter( 'customify_filter_fields', array( $this, 'add_customify_options' ), 40, 1 );

		/* Hook-up to various places where we need to output things */

		//Output the primary footer markup
		add_action( 'pixelgrade_footer', 'pixelgrade_the_footer', 10, 1  );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_footer_registered_hooks' );
	}

	public function register_widget_areas() {
		register_sidebar( array(
			'name'          => esc_html__( 'Footer Area', 'components' ),
			'id'            => 'sidebar-footer',
			'description'   => esc_html__( 'Widgets displayed in the Footer Area of the website.', 'components' ),
			'before_widget' => '<div id="%1$s" class="c-gallery__item  c-widget  %2$s"><div class="o-wrapper u-container-width">',
			'after_widget'  => '</div></div>',
			'before_title'  => '<h3 class="c-widget__title h3">',
			'after_title'   => '</h3>',
		) );
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
						'default'           => __( '%year% &copy; Handcrafted with love by <a href="#">Pixelgrade</a> Team', 'components' ),
						'sanitize_callback' => 'wp_kses_post',
						'live'              => array( '.copyright-text' ),
					),
					'footer_top_spacing'           => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Top Spacing', 'components' ),
						'desc'        => esc_html__( '', 'components' ),
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
						'desc'        => esc_html__( '', 'components' ),
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
						'default' => '#252525',
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
								'selector' => '.c-footer',
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