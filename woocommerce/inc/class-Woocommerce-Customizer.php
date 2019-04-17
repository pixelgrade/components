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

define( 'DARK_PRIMARY', '#252525' );

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
		 * 'title'          => esc_html__( 'Title Section is required', '' ),
		 * 'description'    => '',
		 * 'type'           => 'default',
		 * 'description_hidden' => false, // If the description should be hidden behind a (?) bubble
		 *
		 * @see WP_Customize_Section for more details about each of them.
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
		add_filter( 'pixelgrade_button_selectors_array', array( $this, 'alterButtonsSelectors' ), 10, 2 );
	}

	public function alterContentOptions( $section_options, $options ) {

		$new_section_options = array(
			'main_content' => array(
				'options' => array(
					'main_content_heading_1_font'           => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_1_font']['selector'] . ',
							.cart_totals h2 
						'
					),
					'main_content_heading_2_font'           => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_2_font']['selector'] . ', 
							[id="order_review_heading"],
							.woocommerce-billing-fields > h3,
							.woocommerce-additional-fields > h3,
							.cart_totals .order-total .woocommerce-Price-amount,
							.comment-reply-title
						'
					),
					'main_content_heading_3_font'           => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_3_font']['selector'] . ',
							table.shop_table td.product-name,
							.woocommerce-checkout .order-total .woocommerce-Price-amount,
							.c-mini-cart[class] .cart_list a:not(.remove), 
							.c-mini-cart[class] .product_list_widget a:not(.remove),
							.product .entry-summary .price[class],
							.woocommerce-grouped-product-list-item__price,
							.woocommerce-grouped-product-list-item__label,
							.related.products h2,
							.woocommerce-Reviews-title,
							.woocommerce-Reviews .comment-reply-title
						'
					),
					'main_content_heading_4_font'           => array(
						'selector' => $section_options['main_content']['options']['main_content_heading_4_font']['selector'] . ',
							.woocommerce-checkout form .form-row label,
							.woocommerce-checkout-breadcrumbs,
							.woocommerce-mini-cart__empty-message,
							table.shop_table tr,
							[id="ship-to-different-address"],
							.c-mini-cart[class] .cart_list .quantity, 
							.c-mini-cart[class] .product_list_widget .quantity,
							.c-mini-cart .woocommerce-mini-cart__total,
							.wc_payment_method label,
							.woocommerce-result-count,
							.woocommerce-ordering select,
							.woocommerce-categories,
							.add_to_cart_inline del,
							.add_to_cart_inline ins,
							.variations .label,
							.comment-form label
						'
					),
					'main_content_body_text_color'          => array(
						'css' => array(
							'woocommerce-checkout-order-background' => array(
								'selector' => '.woocommerce-checkout .woocommerce-checkout:before',
								'property' => 'background-color',
							),
							'woocommerce-notice-background' => array(
								'selector' => '.woocommerce-store-notice[class]',
								'property' => 'background-color',
							),
						),
					),
					'main_content_content_background_color' => array(
						'css' => array(
							'woocommerce-menu-cart-color' => array(
								'selector' => '.cart-count',
								'property' => 'color',
							),
							'woocommerce-notice-text-color' => array(
								'selector' => '.woocommerce-store-notice[class]',
								'property' => 'color',
							),
						),
					),
					'main_content_body_link_active_color'   => array(
						'css' => array(
							'woocommerce-link-color'                 => array(
								'property' => 'color',
								'selector' => '
									.woocommerce-categories a:hover,
									.woocommerce-categories a:active,
									.woocommerce-categories .active,
									.wc-tabs > .active a,
									.star-rating,
									.woocommerce p.stars a::before,
									.woocommerce-checkout-breadcrumbs a
									'
							),
							'woocommerce-menu-cart-background-color' => array(
								'property' => 'background-color',
								'selector' => '.cart-count'
							),
							'woocommerce-radio-border-color'         => array(
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

	/**
	 * Add the component's Customify options to the rest.
	 *
	 * @param array $options
	 *
	 * @return array
	 */
	public function addCustomifyOptions( $options ) {

		$recommended_body_fonts = apply_filters(
			'customify_theme_recommended_body_fonts',
			array(
				'Roboto',
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
				'Pompiere',
			)
		);

		$card_choices = array(
			'none'          => esc_html__( 'None', '__components_txtd' ),
			'tag'           => esc_html__( 'Tag', '__components_txtd' ),
			'tag_list'      => esc_html__( 'Tag List', '__components_txtd' ),
			'category'      => esc_html__( 'Category', '__components_txtd' ),
			'category_list' => esc_html__( 'Category List', '__components_txtd' ),
			'excerpt'       => esc_html__( 'Excerpt', '__components_txtd' ),
			'read_more'     => esc_html__( 'Read More', '__components_txtd' ),
			'price'         => esc_html__( 'Price', '__components_txtd' ),
			'title'         => esc_html__( 'Title', '__components_txtd' ),
		);

		$woocommerce_section = array(
			'woocommerce_section' => array(
				'title'   => esc_html__( 'Woocommerce Grid Items', '__components_txtd' ),
				'options' => array(
					'woocommerce_grid_options_customizer_tabs'       => array(
						'type' => 'html',
						'html' => '<nav class="section-navigation  js-section-navigation">
								<a href="#section-title-portfolio-layout">' . esc_html__( 'Layout', '__components_txtd' ) . '</a>
								<a href="#section-title-portfolio-colors">' . esc_html__( 'Colors', '__components_txtd' ) . '</a>
								<a href="#section-title-portfolio-fonts">' . esc_html__( 'Fonts', '__components_txtd' ) . '</a>
								</nav>',
					),
					// [Section] Layout
					'woocommerce_grid_title_layout_section'          => array(
						'type' => 'html',
						'html' => '<span id="section-title-blog-layout" class="separator section label large">&#x1f4d0; ' . esc_html__( 'Layout', '__components_txtd' ) . '</span>',
					),
					'woocommerce_grid_width'                         => array(
						'type'        => 'range',
						'label'       => esc_html__( 'WooCommerce Grid Max Width', '__components_txtd' ),
						'desc'        => esc_html__( 'Adjust the max width of the blog area.', '__components_txtd' ),
						'live'        => true,
						'default'     => 1280,
						'input_attrs' => array(
							'min'          => 600,
							'max'          => 2600,
							'step'         => 10,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property' => 'max-width',
								'selector' => '.u-blog-grid-width',
								'unit'     => 'px',
							),
						),
					),
					'woocommerce_container_sides_spacing'            => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Container Sides Spacing', '__components_txtd' ),
						'desc'        => esc_html__( 'Adjust the space separating the site content and the sides of the browser.', '__components_txtd' ),
						'live'        => true,
						'default'     => 60,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 140,
							'step'         => 10,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => 'padding-left',
								'selector'        => '.u-blog-sides-spacing',
								'callback_filter' => 'typeline_spacing_cb',
								'unit'            => 'px',
							),
							array(
								'property'        => 'padding-right',
								'selector'        => '.u-blog-sides-spacing',
								'callback_filter' => 'typeline_spacing_cb',
								'unit'            => 'px',
							),
						),
					),

					// [Sub Section] Items Grid
					'woocommerce_grid_title_items_grid_section'      => array(
						'type' => 'html',
						'html' => '<span class="separator sub-section label large">' . esc_html__( 'Items Grid', '__components_txtd' ) . '</span>',
					),
					'woocommerce_grid_layout'                        => array(
						'type'    => 'radio',
						'label'   => esc_html__( 'Grid Layout', '__components_txtd' ),
						'desc'    => esc_html__( 'Choose whether the items display in a fixed height regular grid, or in a packed style layout.', '__components_txtd' ),
						'default' => 'regular',
						'choices' => array(
							'regular' => esc_html__( 'Regular Grid', '__components_txtd' ),
							'masonry' => esc_html__( 'Masonry', '__components_txtd' ),
							'mosaic'  => esc_html__( 'Mosaic', '__components_txtd' ),
							'packed'  => esc_html__( 'Packed', '__components_txtd' ),
						),
					),
					'woocommerce_items_aspect_ratio'                 => array(
						'type'            => 'range',
						'label'           => esc_html__( 'Items Aspect Ratio', '__components_txtd' ),
						'desc'            => esc_html__( 'Change the images ratio from landscape to portrait.', '__components_txtd' ),
						'live'            => true,
						'default'         => 100,
						'input_attrs'     => array(
							'min'          => 0,
							'max'          => 200,
							'step'         => 10,
							'data-preview' => true,
						),
						'css'             => array(
							array(
								'property'        => 'dummy',
								'selector'        => '.c-gallery--woocommerce.c-gallery--regular .c-card__frame',
								'callback_filter' => 'pixelgrade_aspect_ratio_cb',
								'unit'            => '%',
							),
						),
						'active_callback' => 'pixelgrade_woocommerce_items_aspect_ratio_control_show',
					),
					'woocommerce_items_per_row'                      => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Items per Row', '__components_txtd' ),
						'desc'        => esc_html__( 'Set the desktop-based number of columns you want and we automatically make it right for other screen sizes.', '__components_txtd' ),
						'live'        => false,
						'default'     => 3,
						'input_attrs' => array(
							'min'  => 1,
							'max'  => 6,
							'step' => 1,
						),
						'css'         => array(
							array(
								'property' => 'dummy',
								'selector' => '.dummy',
								'unit'     => 'px',
							),
						),
					),
					'woocommerce_items_vertical_spacing'             => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Items Vertical Spacing', '__components_txtd' ),
						'desc'        => esc_html__( 'Adjust the spacing between individual items in your grid.', '__components_txtd' ),
						'live'        => true,
						'default'     => 60,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 300,
							'step'         => 10,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => '',
								'selector'        => '.dummy',
								'callback_filter' => 'pixelgrade_woocommerce_grid_vertical_spacing_cb',
								'unit'            => 'px',
							),
						),
					),
					'woocommerce_items_horizontal_spacing'           => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Items Horizontal Spacing', '__components_txtd' ),
						'desc'        => esc_html__( 'Adjust the spacing between individual items in your grid.', '__components_txtd' ),
						'live'        => true,
						'default'     => 60,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 120,
							'step'         => 10,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property'        => '',
								'selector'        => '.dummy',
								'callback_filter' => 'pixelgrade_woocommerce_grid_horizontal_spacing_cb',
								'unit'            => 'px',
							),
						),
					),

					// [Sub Section] Items Title
					'woocommerce_grid_title_items_title_section'     => array(
						'type' => 'html',
						'html' => '<span class="separator sub-section label">' . esc_html__( 'Items Title', '__components_txtd' ) . '</span>',
					),
					'woocommerce_items_title_position'               => array(
						'type'    => 'radio',
						'label'   => esc_html__( 'Title Position', '__components_txtd' ),
						'desc'    => esc_html__( 'Choose whether the items titles are placed nearby the thumbnail or show as an overlay cover on  mouse over.', '__components_txtd' ),
						'default' => 'below',
						'choices' => array(
							'above'   => esc_html__( 'Above', '__components_txtd' ),
							'below'   => esc_html__( 'Below', '__components_txtd' ),
							'overlay' => esc_html__( 'Overlay', '__components_txtd' ),
						),
					),
					'woocommerce_items_title_alignment_nearby'       => array(
						'type'            => 'select',
						'label'           => esc_html__( 'Title Alignment (Above/Below)', '__components_txtd' ),
						'desc'            => esc_html__( 'Adjust the alignment of your title.', '__components_txtd' ),
						'default'         => 'left',
						'choices'         => array(
							'left'   => esc_html__( '← Left', '__components_txtd' ),
							'center' => esc_html__( '↔ Center', '__components_txtd' ),
							'right'  => esc_html__( '→ Right', '__components_txtd' ),
						),
						'active_callback' => 'pixelgrade_woocommerce_items_title_alignment_nearby_control_show',
					),
					'woocommerce_items_title_alignment_overlay'      => array(
						'type'            => 'select',
						'label'           => esc_html__( 'Title Alignment (Overlay)', '__components_txtd' ),
						'desc'            => esc_html__( 'Adjust the alignment of your hover title.', '__components_txtd' ),
						'default'         => 'bottom-left',
						// this should be set by the theme (previously middle-center)
						'choices'         => array(
							'top-left'   => esc_html__( '↑ Top     ← Left', '__components_txtd' ),
							'top-center' => esc_html__( '↑ Top     ↔ Center', '__components_txtd' ),
							'top-right'  => esc_html__( '↑ Top     → Right', '__components_txtd' ),

							'middle-left'   => esc_html__( '↕ Middle     ← Left', '__components_txtd' ),
							'middle-center' => esc_html__( '↕ Middle     ↔ Center', '__components_txtd' ),
							'middle-right'  => esc_html__( '↕ Middle     → Right', '__components_txtd' ),

							'bottom-left'   => esc_html__( '↓ bottom     ← Left', '__components_txtd' ),
							'bottom-center' => esc_html__( '↓ bottom     ↔ Center', '__components_txtd' ),
							'bottom-right'  => esc_html__( '↓ bottom     → Right', '__components_txtd' ),
						),
						'active_callback' => 'pixelgrade_woocommerce_items_title_alignment_overlay_control_show',
					),

					// Title Visiblity
					// Title + Checkbox
					'woocommerce_items_title_visibility_title'       => array(
						'type' => 'html',
						'html' => '<span class="customize-control-title">' . esc_html__( 'Title Visibility', '__components_txtd' ) . '</span><span class="description customize-control-description">' . esc_html__( 'Select whether to show or hide the summary.', '__components_txtd' ) . '</span>',
					),
					'woocommerce_items_title_visibility'             => array(
						'type'    => 'checkbox',
						'label'   => esc_html__( 'Show Title', '__components_txtd' ),
						'default' => 1, // this should be set by the theme (previously 1)
					),

					// [Sub Section] Items Excerpt
					'woocommerce_grid_title_items_excerpt_section'   => array(
						'type' => 'html',
						'html' => '<span class="separator sub-section label">' . esc_html__( 'Items Excerpt', '__components_txtd' ) . '</span>',
					),

					// Excerpt Visiblity
					// Title + Checkbox
					'woocommerce_items_excerpt_visibility_title'     => array(
						'type' => 'html',
						'html' => '<span class="customize-control-title">' . esc_html__( 'Excerpt Visibility', '__components_txtd' ) . '</span><span class="description customize-control-description">' . esc_html__( 'Select whether to show or hide the summary.', '__components_txtd' ) . '</span>',
					),
					'woocommerce_items_excerpt_visibility'           => array(
						'type'    => 'checkbox',
						'label'   => esc_html__( 'Show Excerpt Text', '__components_txtd' ),
						'default' => 0, // this should be set by the theme (previously 1)
					),

					// [Sub Section] Items Meta
					'woocommerce_grid_title_items_meta_section'      => array(
						'type' => 'html',
						'html' => '<span class="separator sub-section label">' . esc_html__( 'Card Content', '__components_txtd' ) . '</span>',
					),
					'woocommerce_items_primary_meta'                 => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Primary Meta Section', '__components_txtd' ),
						'desc'    => '',
						'default' => 'none',
						'choices' => $card_choices,
					),
					'woocommerce_items_secondary_meta'               => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Secondary Meta Section', '__components_txtd' ),
						'desc'    => '',
						'default' => 'none',
						'choices' => $card_choices,
					),
					'woocommerce_items_heading'                      => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Card Heading Source', '__components_txtd' ),
						'desc'    => '',
						'default' => 'title', // this should be set by the theme (previously date)
						'choices' => $card_choices,
					),
					'woocommerce_items_content'                      => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Card Content Source', '__components_txtd' ),
						'desc'    => '',
						'default' => 'tag_list', // this should be set by the theme (previously date)
						'choices' => $card_choices,
					),
					'woocommerce_items_footer'                       => array(
						'type'    => 'select',
						'label'   => esc_html__( 'Card Footer Source', '__components_txtd' ),
						'desc'    => '',
						'default' => 'price', // this should be set by the theme (previously date)
						'choices' => $card_choices,
					),

					// [Section] COLORS
					'woocommerce_grid_title_colors_section'          => array(
						'type' => 'html',
						'html' => '<span id="section-title-blog-colors" class="separator section label large">&#x1f3a8; ' . esc_html__( 'Colors', '__components_txtd' ) . '</span>',
					),
					'woocommerce_item_title_color'                   => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Item Title Color', '__components_txtd' ),
						'live'    => true,
						'default' => DARK_PRIMARY,
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-gallery--woocommerce .c-card__title',
							),
						),
					),
					'woocommerce_item_meta_primary_color'            => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Meta Primary Color', '__components_txtd' ),
						'live'    => true,
						'default' => DARK_PRIMARY,
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-gallery--woocommerce .c-meta__primary',
							),
						),
					),
					'woocommerce_item_meta_secondary_color'          => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Meta Secondary Color', '__components_txtd' ),
						'live'    => true,
						'default' => DARK_PRIMARY,
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-gallery--woocommerce .c-meta__secondary, .c-gallery--woocommerce .c-meta__separator',
							),
						),
					),
					'woocommerce_item_excerpt_color'                 => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Item Excerpt Color', '__components_txtd' ),
						'live'    => true,
						'default' => DARK_PRIMARY,
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-gallery--woocommerce .c-card__excerpt',
							),
						),
					),
					'woocommerce_item_footer_color'                 => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Item Footer Color', '__components_txtd' ),
						'live'    => true,
						'default' => DARK_PRIMARY,
						'css'     => array(
							array(
								'property' => 'color',
								'selector' => '.c-gallery--woocommerce .c-card__footer',
							),
						),
					),
					'woocommerce_item_thumbnail_background'          => array(
						'type'    => 'color',
						'label'   => esc_html__( 'Thumbnail Background', '__components_txtd' ),
						'live'    => true,
						'default' => DARK_PRIMARY,
						'css'     => array(
							array(
								'property' => 'background-color',
								'selector' => '.c-gallery--woocommerce .c-card__thumbnail-background',
							),
						),
					),

					// [Sub Section] Thumbnail Hover
					'woocommerce_grid_title_thumbnail_hover_section' => array(
						'type' => 'html',
						'html' => '<span class="separator sub-section label">' . esc_html__( 'Thumbnail Hover', '__components_txtd' ) . '</span><span class="description customize-control-description">' . esc_html__( 'Customize the mouse over effect for your thumbnails.', '__components_txtd' ) . '</span>',
					),
					'woocommerce_item_thumbnail_hover_opacity'       => array(
						'type'        => 'range',
						'label'       => esc_html__( 'Thumbnail Background Opacity', '__components_txtd' ),
						'desc'        => '',
						'live'        => true,
						'default'     => 1,
						'input_attrs' => array(
							'min'          => 0,
							'max'          => 1,
							'step'         => 0.1,
							'data-preview' => true,
						),
						'css'         => array(
							array(
								'property' => 'opacity',
								'selector' => '.c-gallery--woocommerce .c-card:hover .c-card__frame',
								'unit'     => '',
							),
						),
					),

					// [Section] FONTS
					'woocommerce_grid_title_fonts_section'           => array(
						'type' => 'html',
						'html' => '<span id="section-title-blog-fonts" class="separator section label large">&#x1f4dd;  ' . esc_html__( 'Fonts', '__components_txtd' ) . '</span>',
					),

					'woocommerce_item_title_font' => array(
						'type'     => 'font',
						'label'    => esc_html__( 'Item Title Font', '__components_txtd' ),
						'desc'     => '',
						'selector' => '.c-gallery--woocommerce .c-card__title, .c-gallery--woocommerce .c-card__letter',
						'callback' => 'typeline_font_cb',

						'default'     => array(
							'font-family'    => 'Roboto',
							'font-weight'    => 'regular',
							'font-size'      => 24,
							'line-height'    => 1.25,
							'letter-spacing' => 0,
							'text-transform' => 'none',
						),

						// List of recommended fonts defined by theme
						'recommended' => $recommended_body_fonts,

						// Sub Fields Configuration (optional)
						'fields'      => array(
							'font-size'       => array(                           // Set custom values for a range slider
								'min'  => 8,
								'max'  => 90,
								'step' => 1,
								'unit' => 'px',
							),
							'line-height'     => array( 0, 2, 0.1, '' ),
							// Short-hand version
							'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
							'text-align'      => false,
							// Disable sub-field (False by default)
							'text-transform'  => true,
							'text-decoration' => false,
						),
					),

					'woocommerce_item_meta_font' => array(
						'type'     => 'font',
						'label'    => esc_html__( 'Item Meta Font', '__components_txtd' ),
						'desc'     => '',
						'selector' => '.c-gallery--woocommerce .c-meta__primary, .c-gallery--woocommerce .c-meta__secondary',
						'callback' => 'typeline_font_cb',

						'default'     => array(
							'font-family'    => 'Roboto',
							'font-weight'    => 'regular',
							'font-size'      => 15,
							'line-height'    => 1.5,
							'letter-spacing' => 0,
							'text-transform' => 'none',
						),

						// List of recommended fonts defined by theme
						'recommended' => $recommended_body_fonts,

						// Sub Fields Configuration (optional)
						'fields'      => array(
							'font-size'       => array(                           // Set custom values for a range slider
								'min'  => 8,
								'max'  => 90,
								'step' => 1,
								'unit' => 'px',
							),
							'line-height'     => array( 0, 2, 0.1, '' ),
							// Short-hand version
							'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
							'text-align'      => false,
							// Disable sub-field (False by default)
							'text-transform'  => true,
							'text-decoration' => false,
						),
					),

					'woocommerce_item_excerpt_font' => array(
						'type'     => 'font',
						'label'    => esc_html__( 'Item Excerpt Font', '__components_txtd' ),
						'desc'     => '',
						'selector' => '.c-gallery--woocommerce .c-card__excerpt',
						'callback' => 'typeline_font_cb',

						'default' => array(
							'font-family'    => 'Roboto',
							'font-weight'    => 'regular',
							'font-size'      => 16,
							'line-height'    => 1.5,
							'letter-spacing' => 0,
							'text-transform' => 'none',
						),

						// Sub Fields Configuration (optional)
						'fields'  => array(
							'font-size'       => array(                           // Set custom values for a range slider
								'min'  => 8,
								'max'  => 90,
								'step' => 1,
								'unit' => 'px',
							),
							'line-height'     => array( 0, 2, 0.1, '' ), // Short-hand version
							'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
							'text-align'      => false, // Disable sub-field (False by default)
							'text-transform'  => true,
							'text-decoration' => false,
						),
					),

					'woocommerce_item_footer_font' => array(
						'type'     => 'font',
						'label'    => esc_html__( 'Item Footer Font', '__components_txtd' ),
						'desc'     => '',
						'selector' => '.c-gallery--woocommerce .c-card__footer',
						'callback' => 'typeline_font_cb',

						'default' => array(
							'font-family'    => 'Roboto',
							'font-weight'    => 'regular',
							'font-size'      => 16,
							'line-height'    => 1.5,
							'letter-spacing' => 0,
							'text-transform' => 'none',
						),

						// Sub Fields Configuration (optional)
						'fields'  => array(
							'font-size'       => array(                           // Set custom values for a range slider
								'min'  => 8,
								'max'  => 90,
								'step' => 1,
								'unit' => 'px',
							),
							'line-height'     => array( 0, 2, 0.1, '' ), // Short-hand version
							'letter-spacing'  => array( - 1, 2, 0.01, 'em' ),
							'text-align'      => false, // Disable sub-field (False by default)
							'text-transform'  => true,
							'text-decoration' => false,
						),
					),
				),
			),
		);

		// Allow others to make changes
		$modified_config = apply_filters( 'pixelgrade_customify_woocommerce_grid_section_options', $woocommerce_section, $options );

		// Validate the config
		// We will trigger _doing_it_wrong() errors, but in production we will let it pass.
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			Pixelgrade_Config::validateCustomizerSectionConfig( $modified_config, $woocommerce_section );
		}

		// Validate the default values
		// When we have defined in the original config 'default' => null, this means the theme (or someone) must define the value via the filter above.
		// We will trigger _doing_it_wrong() errors, but in production we will let it pass.
		if ( defined( 'WP_DEBUG' ) && true === WP_DEBUG ) {
			Pixelgrade_Config::validateCustomizerSectionConfigDefaults( $modified_config, $woocommerce_section, 'pixelgrade_customify_woocommerce_grid_section_options' );
		}

		// Assign the modified config
		$woocommerce_section = $modified_config;

		// Make sure we are in good working order
		if ( empty( $options['sections'] ) ) {
			$options['sections'] = array();
		}

		// append the portfolio grid section
		$options['sections'] = $options['sections'] + $woocommerce_section;

		return $options;
	}

	/**
	 * Change buttons selectors list.
	 *
	 * @param array $array
	 *
	 * @return array
	 */
	public function alterButtonsSelectors( $array = array() ) {
		$array[] = '.button[class][class][class][class][class]';
		$array[] = '.product .cart .qty[class][class][class]';
		$array[] = '#respond input#submit[id]';
		$array[] = '.added_to_cart';

		return $array;
	}
}
