<?php
/**
 * This is the class that handles the metaboxes of our Portfolio component.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Portfolio
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Portfolio_Metaboxes {

	/**
	 * The main component object (the parent).
	 *
	 * @var     Pixelgrade_Portfolio
	 * @access  public
	 * @since     1.0.0
	 */
	public $parent = null;

	/**
	 * The single instance of the class
	 *
	 * @var Pixelgrade_Portfolio_Metaboxes
	 */
	private static $_instance = null;

	/**
	 * Pixelgrade_Portfolio_Metaboxes constructor.
	 *
	 * @param Pixelgrade_Portfolio $parent
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
		 * Modify the Base component
		 */

		// In general components, should hook in early to allow for the main theme to come later
		// Also to not leave the order of execution to chance :)
		add_filter( 'pixelgrade_base_metaboxes_config', array( $this, 'display_custom_css_metabox_for_portfolio' ), 5, 1 );

		/*
		 * ================================
		 * Modify the Hero component
		 */

		// In general components, should hook in early to allow for the main theme to come later
		// Also to not leave the order of execution to chance :)

		// Add the featured projects fields to the needed hero metaboxes
		add_filter( 'pixelgrade_hero_metaboxes_config', array( $this, 'hero_featured_projects_metaboxes' ), 5, 1 );

		// Make sure that the hero metaboxes get shown for certain component page templates
		add_filter( 'pixelgrade_hero_metaboxes_config', array( $this, 'display_hero_metaboxes_for_page_templates' ), 6, 1 );

		// Add the portfolio grid fields to the needed hero metaboxes
		add_filter( 'pixelgrade_hero_metaboxes_config', array( $this, 'hero_portfolio_grid_metaboxes' ), 7, 1 );

		/*
		 * ================================
		 * Add classes to the portfolio
		 */
		add_filter( 'pixelgrade_portfolio_class', array( $this, 'add_classes_to_portfolio_wrapper' ), 5, 3 );

		// Handle the _portfolio_grid_show option when we are meant to not show any projects in the page custom loop
		add_filter( 'pixelgrade_skip_custom_loops_for_page', array( $this, 'skip_portfolio_custom_page_template_loop' ), 10, 2 );
	}

	/**
	 * Modify the Base component's metaboxes config.
	 *
	 * @param array $base_metaboxes
	 *
	 * @return array
	 */
	public function display_custom_css_metabox_for_portfolio( $base_metaboxes ) {
		// Make sure that the hero background metabox is shown on the component's page templates also
		if ( ! empty( $base_metaboxes['base_custom_css_style'] ) ) {

			if ( empty( $base_metaboxes['base_custom_css_style']['pages'] ) ) {
				// Standardize an empty argument
				$base_metaboxes['base_custom_css_style']['pages'] = array();
			}

			// Add our CPT to the list
			$base_metaboxes['base_custom_css_style']['pages'] = array_merge(
				$base_metaboxes['base_custom_css_style']['pages'],
				array(
					Jetpack_Portfolio::CUSTOM_POST_TYPE,
				) );
		}

		return $base_metaboxes;
	}

	/**
	 * Modify the Hero component's metaboxes config.
	 *
	 * @param array $hero_metaboxes
	 *
	 * @return array
	 */
	public function display_hero_metaboxes_for_page_templates( $hero_metaboxes ) {
		$component_config = $this->parent->get_config();
		// Setup the hero metaboxes for the Portfolio Template - if the theme changed that template, it should also handle the metaboxes logic
		$portfolio_page_template = trailingslashit( Pixelgrade_Portfolio::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) . 'portfolio-page.php';
		if ( Pixelgrade_Config::has_page_template( $portfolio_page_template, $component_config ) ) {
			// Make sure that the hero background metabox is shown on the page template also
			if ( ! empty( $hero_metaboxes['hero_area_background__page']['show_on']['key'] )
			     && 'page-template' === $hero_metaboxes['hero_area_background__page']['show_on']['key'] ) {

				// Make sure that we are dealing with an array, instead of a string
				if ( ! is_array( $hero_metaboxes['hero_area_background__page']['show_on']['value'] ) ) {
					$hero_metaboxes['hero_area_background__page']['show_on']['value'] = array( $hero_metaboxes['hero_area_background__page']['show_on']['value'] );
				}

				// Add our page templates
				$hero_metaboxes['hero_area_background__page']['show_on']['value'] = array_merge(
					$hero_metaboxes['hero_area_background__page']['show_on']['value'],
					array(
						$portfolio_page_template,
					) );
			}

			// Make sure that the hero content metabox is shown on the page template also
			if ( ! empty( $hero_metaboxes['hero_area_content__page']['show_on']['key'] )
			     && 'page-template' === $hero_metaboxes['hero_area_content__page']['show_on']['key'] ) {

				// Make sure that we are dealing with an array, instead of a string
				if ( ! is_array( $hero_metaboxes['hero_area_content__page']['show_on']['value'] ) ) {
					$hero_metaboxes['hero_area_content__page']['show_on']['value'] = array( $hero_metaboxes['hero_area_content__page']['show_on']['value'] );
				}

				// Add our page templates
				$hero_metaboxes['hero_area_content__page']['show_on']['value'] = array_merge(
					$hero_metaboxes['hero_area_content__page']['show_on']['value'],
					array(
						$portfolio_page_template,
					) );
			}
		}

		return $hero_metaboxes;
	}

	/**
	 * Modify the Hero component's metaboxes config and add the featured projects fields
	 *
	 * @param array $hero_metaboxes
	 *
	 * @return array
	 */
	public function hero_featured_projects_metaboxes( $hero_metaboxes ) {
		/*
		 * Add the Featured Projects fields
		 * By default we add them for all the pages that show the hero_area_content__page metabox
		 */
		$featured_projects_fields = array(
			array(
				'name'       => '&#x1F48E; ' . esc_html__( 'Featured Projects Options', 'components_txtd' ),
				'id'         => '_hero_featured_projects_title',
				'type'       => 'title',
			),
			array(
				'name'            => esc_html__( 'Selected Projects', 'components_txtd' ),
				'id'              => '_hero_featured_projects_ids',
				'desc'            => esc_html__( 'Choose the projects to be part of the Hero Slider.', 'components_txtd' ),
				'type'            => 'pw_multiselect_cpt_v2',
				'options'         => array(
					'args' => array(
						'post_type'   => Jetpack_Portfolio::CUSTOM_POST_TYPE,
						'post_status' => 'publish',
					),
				),
				'sanitization_cb' => 'pw_select2_v2_sanitise',
			),
			array(
				'name'       => esc_html__( '"View Project" Button Label', 'components_txtd' ),
				'id'         => '_hero_featured_projects_view_more_label',
				'desc'       => esc_html__( 'Adjust the label for the single project button, displayed on each slide. Empty it if you want to hide the button.', 'components_txtd' ),
				'type'       => 'text_medium',
				'std'        => esc_html__( 'View project', 'components_txtd' ),
			),
		);

		// First a little bit of sanity check
		// If by some weird chance there are no fields, put ours and bail
		if ( empty( $hero_metaboxes['hero_area_content__page']['fields'] ) || ! is_array( $hero_metaboxes['hero_area_content__page']['fields'] ) ) {
			$hero_metaboxes['hero_area_content__page']['fields'] = $featured_projects_fields;

			return $hero_metaboxes;
		}

		// Insert the featured projects fields at the end
		$hero_metaboxes['hero_area_content__page']['fields'] = array_merge(
			$hero_metaboxes['hero_area_content__page']['fields'],
			$featured_projects_fields
		);

		return $hero_metaboxes;
	}

	/**
	 * Modify the Hero component's metaboxes config and add the portfolio grid fields
	 *
	 * @param array $hero_metaboxes
	 *
	 * @return array
	 */
	public function hero_portfolio_grid_metaboxes( $hero_metaboxes ) {
		/*
		 * Add the Portfolio Grid fields
		 * By default we add them for the portfolio/page-templates/portfolio-page.php template, if it's available
		 */
		$portfolio_grid_fields = array(
			array(
				'name'       => '&#x1F3C1; ' . esc_html__( 'Portfolio Grid', 'components_txtd' ),
				'id'         => '_portfolio_grid_title',
				'type'       => 'title',
				'display_on' => array(
					'display' => true,
					'on'      => array(
						'field' => 'page_template',
						'value' => array( trailingslashit( Pixelgrade_Portfolio::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) . 'portfolio-page.php' ),
					),
				),
			),
			array(
				'name'    => esc_html__( 'Projects List Visibility', 'components_txtd' ),
				'id'      => '_portfolio_grid_show',
				'desc'    => esc_html__( 'Select which projects to be shown in the Portfolio Grid section.', 'components_txtd' ),
				'type'    => 'select',
				'options' => array(
					array(
						'name'  => esc_html__( 'Show All Projects', 'components_txtd' ),
						'value' => 'all',
					),
					array(
						'name'  => esc_html__( 'Show Only Projects That Are Not Featured', 'components_txtd' ),
						'value' => 'exclude_featured',
					),
					array(
						'name'  => esc_html__( 'Hide All Projects', 'components_txtd' ),
						'value' => 'none',
					),
				),
				'std'     => 'all',
				'display_on' => array(
					'display' => true,
					'on'      => array(
						'field' => 'page_template',
						'value' => array( trailingslashit( Pixelgrade_Portfolio::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) . 'portfolio-page.php' ),
					),
				),
			),
		);

		// First a little bit of sanity check
		// If by some weird chance there are no fields, put ours and bail
		if ( empty( $hero_metaboxes['hero_area_content__page']['fields'] ) || ! is_array( $hero_metaboxes['hero_area_content__page']['fields'] ) ) {
			$hero_metaboxes['hero_area_content__page']['fields'] = $portfolio_grid_fields;

			return $hero_metaboxes;
		}

		// Insert the featured projects fields at the end
		$hero_metaboxes['hero_area_content__page']['fields'] = array_merge(
			$hero_metaboxes['hero_area_content__page']['fields'],
			$portfolio_grid_fields
		);

		return $hero_metaboxes;
	}

	/**
	 * Filter the list of CSS classes for the portfolio wrapper.
	 *
	 * @param array $classes An array of header classes.
	 * @param array $class   An array of additional classes added to the portfolio wrapper.
	 * @param string|array $location   The place (template) where the classes are displayed.
	 *
	 * @return array
	 */
	public function add_classes_to_portfolio_wrapper( $classes, $class, $location ) {
		// None right now

		return $classes;
	}

	/**
	 * Forces the portfolio page template to skip the loop
	 *
	 * @param bool $skip
	 * @param int $page_ID
	 *
	 * @return bool
	 */
	public function skip_portfolio_custom_page_template_loop( $skip, $page_ID ) {
		if ( 'none' === get_post_meta( $page_ID, '_portfolio_grid_show', true ) ) {
			return true;
		}

		return $skip;
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
	 * Main Pixelgrade_Portfolio_Metaboxes Instance
	 *
	 * Ensures only one instance of Pixelgrade_Portfolio_Metaboxes is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @param Pixelgrade_Portfolio $parent
	 *
	 * @return Pixelgrade_Portfolio_Metaboxes
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
