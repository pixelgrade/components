<?php
/**
 * This is the class that handles the metaboxes of our Base component.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Base_Metaboxes {

	/**
	 * The main component object (the parent).
	 * @var     Pixelgrade_Base
	 * @access  public
	 * @since     1.0.0
	 */
	public $parent = null;

	/**
	 * @var Pixelgrade_Base_Metaboxes The single instance of the class
	 */
	private static $_instance = null;

	/**
	 * Pixelgrade_Base_Metaboxes constructor.
	 *
	 * @param Pixelgrade_Base $parent
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
		/**
		 * ================================
		 * Tackle the PixTypes awesomeness
		 */

		// Setup our metaboxes configuration
		add_filter( 'pixelgrade_filter_metaboxes', array( $this, 'metaboxes_config' ), 10, 1 );
		// Since WordPres 4.7 we need to do some trickery to show metaboxes on pages marked as Page for Posts since the page template control is removed for them
		add_filter( 'cmb_show_on', array( $this, 'pixtypes_show_on_metaboxes' ), 10, 2 );
		add_filter( 'pixtypes_cmb_metabox_show_on', array( $this, 'pixtypes_prevent_show_on_fields' ), 10, 2 );

		/**
		 * ================================
		 * Modify the Hero component
		 */

		// In general components, should hook in early to allow for the main theme to come later
		// Also to not leave the order of execution to chance :)
		add_filter( 'pixelgrade_hero_metaboxes_config', array( $this, 'display_hero_metaboxes_for_page_templates' ), 5, 1 );

		/*
		 * ================================
		 * Output the custom CSS if that is the case
		 */
		add_action( 'pixelgrade_before_loop_entry', 'pixelgrade_the_post_custom_css', 10, 1 );
	}

	/**
	 * Add our own metaboxes config to the list
	 *
	 * @param array $metaboxes
	 *
	 * @return array
	 */
	public function metaboxes_config( $metaboxes ) {
		$base_metaboxes = array(
			'base_custom_css_style'               => array(
				'id'         => 'base_custom_css_style',
				'title'      => esc_html__( 'Custom CSS Styles', 'components_txtd' ),
				'pages'      => array( 'page', ), // Post type
				'context'    => 'normal',
				'priority'   => 'low',
				'hidden'     => false,
				'show_names' => false, // Show field names on the left
				'fields'     => array(
					array(
						'name' => esc_html__( 'CSS Style', 'components_txtd' ),
						'desc' => esc_html__( 'Add CSS that will only be applied to this post.', 'components_txtd' ),
						'id'   => 'custom_css_style',
						'type' => 'textarea_code',
						'rows' => '12',
					),
				)
			),
		);

		// Allow others to make changes before we merge the config
		$base_metaboxes = apply_filters( 'pixelgrade_base_metaboxes_config', $base_metaboxes );

		// Now merge our metaboxes config to the global config
		if ( empty( $metaboxes ) ) {
			$metaboxes = array();
		}
		// We merge them so we allow for overwrite by our newer configurations when the same key has been used
		// http://php.net/manual/ro/function.array-merge.php
		$metaboxes = array_merge( $metaboxes, $base_metaboxes );

		// Return our modified metaboxes configuration
		return $metaboxes;
	}

	/**
	 * Force a metabox to be shown on the page for posts (the Home page set in WP Dashboard > Reading)
	 *
	 * @param bool $show
	 * @param array $metabox
	 *
	 * @return bool
	 */
	public function pixtypes_show_on_metaboxes( $show, $metabox ) {
		if ( ! empty( $metabox['show_on_page_for_posts'] ) ) {
			// Get the current ID
			if ( isset( $_GET['post'] ) ) {
				$post_id = absint( $_GET['post'] );
			} elseif ( isset( $_POST['post_ID'] ) ) {
				$post_id = absint( $_POST['post_ID'] );
			}

			// If this page is set as the Page for Posts
			if ( ! empty( $post_id ) && $post_id == get_option( 'page_for_posts' ) ) {
				return true;
			}
		}

		return $show;
	}

	/**
	 * This will prevent a metabox from outputting the hidden fields that handle the show logic.
	 * This way we prevent WordPress's core logic from wrongfully hiding them.
	 * We do this for metaboxes that need to be shown on the page for posts (that is missing the page template select starting with WP 4.7).
	 *
	 * @param bool $show
	 * @param array $metabox
	 *
	 * @return bool
	 */
	public function pixtypes_prevent_show_on_fields( $show, $metabox ) {
		if ( ! empty( $metabox['show_on_page_for_posts'] ) ) {
			// Get the current ID
			if ( isset( $_GET['post'] ) ) {
				$post_id = absint( $_GET['post'] );
			} elseif ( isset( $_POST['post_ID'] ) ) {
				$post_id = absint( $_POST['post_ID'] );
			}

			// If this page is set as the Page for Posts
			if ( ! empty( $post_id ) && $post_id == get_option( 'page_for_posts' ) ) {
				return false;
			}
		}

		return $show;
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
		// Setup the hero metaboxes for the Full Width Template - if the theme changed that template, it should also handle the metaboxes logic
		$fullwidth_page_template = trailingslashit( Pixelgrade_Base::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) . 'full-width.php';
		if ( Pixelgrade_Config::has_page_template( $fullwidth_page_template, $component_config ) ) {
			// Make sure that the hero background metabox is shown on the component's page template also
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
						$fullwidth_page_template,
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
						$fullwidth_page_template,
					) );
			}
		}

		return $hero_metaboxes;
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
	 * Main Pixelgrade_Base_Metaboxes Instance
	 *
	 * Ensures only one instance of Pixelgrade_Base_Metaboxes is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @param Pixelgrade_Base $parent
	 *
	 * @return Pixelgrade_Base_Metaboxes
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
