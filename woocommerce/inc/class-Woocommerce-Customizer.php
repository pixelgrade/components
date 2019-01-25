<?php
/**
 * This is the class that handles the Customizer behaviour of our Woocommerce component.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Woocommerce_Customizer extends Pixelgrade_Singleton {

	/**
	 * The main component object (the parent).
	 *
	 * @var     Pixelgrade_Woocommerce
	 * @access  public
	 * @since     1.0.0
	 */
	public $parent = null;

	/**
	 * Pixelgrade_Woocommerce_Customizer constructor.
	 *
	 * @param Pixelgrade_Woocommerce $parent
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		// The functions needed for the Customify config (like callbacks and such)
		pixelgrade_load_component_file( Pixelgrade_Woocommerce::COMPONENT_SLUG, 'inc/extras-customizer' );

		// Register our actions and filters
		$this->registerHooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function registerHooks() {
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
		 * Please note that due to the fact that right now Customify "holds" the setting and control configuration
		 * under the same array entry some deduction might be made upon fields registration
		 * (e.g. the 'type' refers to the control type, but not the setting 'type' - that is under 'setting_type').
		 */

		// Setup our WooCommerce Customify options
		add_filter( 'customify_filter_fields', array( $this, 'addCustomifyOptions' ), 60, 1 );

		add_filter( 'pixelgrade_customify_main_content_section_options', array( $this, 'alterContentOptions' ), 30, 2 );
		add_filter( 'pixelgrade_header_customify_section_options', array( $this, 'alterHeaderOptions' ), 10, 2 );
		add_filter( 'pixelgrade_button_selectors_array', array( $this, 'alterButtonsSelectors' ), 10, 2 );
	}

	public function alterContentOptions( $section_options, $options ) {

		$new_section_options = array(
			'main_content' => array(
				'options' => array(
					'main_content_heading_1_font' => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_1_font']['selector'] . ',
							.woocommerce-checkout .order-total .woocommerce-Price-amount,
							.cart_totals h2 
						'
					),
					'main_content_heading_2_font' => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_2_font']['selector'] . ', 
							[id="order_review_heading"],
							.woocommerce-billing-fields > h3,
							.cart_totals .order-total .woocommerce-Price-amount
						'
					),
					'main_content_heading_3_font' => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_3_font']['selector'] . ',
							table.shop_table td.product-name,
							.c-mini-cart[class] .cart_list a:not(.remove), 
							.c-mini-cart[class] .product_list_widget a:not(.remove)'
					),
					'main_content_heading_4_font' => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_4_font']['selector'] . ',
							.woocommerce-checkout form .form-row label,
							.woocommerce-mini-cart__empty-message,
							table.shop_table tr,
							[id="ship-to-different-address"],
							.c-mini-cart[class] .cart_list .quantity, 
							.c-mini-cart[class] .product_list_widget .quantity,
							.c-mini-cart .woocommerce-mini-cart__total,
							.wc_payment_method label,
							.woocommerce-result-count,
							.woocommerce-ordering select,
							.woocommerce-categories
						'
					),
					'main_content_body_text_color' => array(
						'css' => array(
							array(
								'selector' => '.woocommerce-checkout .woocommerce-checkout:before',
								'property' => 'background-color',
							),
						),
					),
					'main_content_body_link_active_color' => array(
						'css' => array(
							array(
								'property' => 'color',
								'selector' => '
									.woocommerce-categories a:hover,
									.woocommerce-categories a:active,
									.woocommerce-categories .active
									'
							),
							array(
								'selector' => 'input[type=radio]:checked',
								'property' => 'border-color',
							),
						),
					),
				),
			),
		);

		// Now we merge the modified config with the original one
		// Thus overwriting what we have changed
		$section_options = Pixelgrade_Config::merge( $section_options, $new_section_options );

		return $section_options;

	}

	public function alterHeaderOptions( $section_options, $options ) {

		$new_section_options = array(
			'header_section' => array(
				'options' => array(
					'header_background' => array(

					),
				),
			),
		);

		// Now we merge the modified config with the original one
		// Thus overwriting what we have changed
		$section_options = Pixelgrade_Config::merge( $section_options, $new_section_options );

		return $section_options;

	}

	/**
	 * Add the component's Customify options to the rest.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function addCustomifyOptions( $options ) {
		$woocommerce_section = array(
			'woocommerce_section' => array(
				'title'   => esc_html__( 'Woocommerce Grid Items', '__components_txtd' ),
				'options' => array(
					'woocommerce_grid_options_customizer_tabs' => array(
						'type' => 'html',
						'html' => '<nav class="section-navigation  js-section-navigation">
								<a href="#section-title-portfolio-layout">' . esc_html__( 'Layout', '__components_txtd' ) . '</a>
								<a href="#section-title-portfolio-colors">' . esc_html__( 'Colors', '__components_txtd' ) . '</a>
								<a href="#section-title-portfolio-fonts">' . esc_html__( 'Fonts', '__components_txtd' ) . '</a>
								</nav>',
					),
				),
			),
		);

		// append the portfolio grid section
		$options['sections'] = $options['sections'] + $woocommerce_section;

		return $options;
	}

	public function alterButtonsSelectors( $array = array() ) {
		$array[] = '.woocommerce-support .button[class][class][class]';
		return $array;
	}
}
