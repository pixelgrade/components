<?php
/**
 * This is the class that handles the metaboxes of our Featured Image component.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Featured-Image
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Featured_Image_Metaboxes {

	/**
	 * The main component object (the parent).
	 * @var     Pixelgrade_Featured_Image
	 * @access  public
	 * @since     1.0.0
	 */
	public $parent = null;

	/**
	 * @var Pixelgrade_Featured_Image_Metaboxes The single instance of the class
	 */
	private static $_instance = null;

	/**
	 * Pixelgrade_Featured_Image_Metaboxes constructor.
	 *
	 * @param Pixelgrade_Featured_Image $parent
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
		// We only do anything if the PixTypes plugin is active
		if ( class_exists( 'PixTypesPlugin' ) ) {

			// Setup how things will behave in the WP admin area
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			// Remove the Featured image metabox from the WordPress core
			add_action( 'add_meta_boxes', array( $this, 'remove_featured_image_metabox' ) );

			// Make sure that we save the featured image in the same way the core does - a value of -1 means delete the meta data, not saving it
			add_filter( 'cmb_validate_image', array( $this, 'save_featured_image_meta' ), 10, 3 );

			// Enqueue assets for the admin
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
			do_action( 'pixelgrade_featured_image_registered_hooks' );
		}

		// Setup our metaboxes configuration
		add_filter( 'pixelgrade_filter_metaboxes', array( $this, 'metaboxes_config' ), 10, 1 );
	}

	/**
	 * Add our own metaboxes config to the list
	 *
	 * @param array $metaboxes
	 *
	 * @return array
	 */
	public function metaboxes_config( $metaboxes ) {
		$component_config = $this->parent->get_config();
		// Some sanity check
		if ( empty( $component_config['post_types'] ) ) {
			$component_config['post_types'] = array();
		}

		// These are the PixTypes configs for the metaboxes for each post type
		$featured_image_metaboxes = array(
			//The Hero Background controls - For pages
			'enhanced_featured_image' => array(
				'id'         => 'enhanced_featured_image',
				'title'      => esc_html__( 'Thumbnail', 'components_txtd' )
				                . ' <span class="tooltip" title="<' . 'title>' // This is split is just to not get annoyed by Theme Check
				                . esc_html__( 'Thumbnail (Featured Image)', 'components_txtd' )
				                . '</title><p>'
				                . esc_html__( 'The  image will be displayed on the Portfolio Grid as a thumbnail for the current project.', 'components_txtd' )
				                . '</p><p>'
				                . '<strong>' . esc_html__( 'Thumbnail Hover', 'components_txtd' ) . '</strong>'
				                . '</p><p>'
				                . esc_html__( 'Set an alternative background image when the mouse hovers the thumbnail. It will fill the thumbnail area and it will be vertical and horizontal centered.', 'components_txtd' )
				                . '</p>"></span>',
				'pages'      => $component_config['post_types'], // Post types to display this metabox on
				'context'    => 'side',
				'priority'   => 'low',
				'show_names' => false, // Show field names on the left
				'fields'     => array(
					array(
						'name'        => esc_html__( 'Thumbnail Image', 'components_txtd' ),
						'id'          => '_thumbnail_id', //this is the same id of the featured image we are replacing
						'type'        => 'image',
						'button_text' => esc_html__( 'Add Thumbnail Image', 'components_txtd' ),
						'class'       => '',
						'validate_func' => 'pixelgrade_featured_image_validate_thumbnail_id_field',
					),
					array(
						'name'        => esc_html__( 'Thumbnail Hover Image', 'components_txtd' ),
						'id'          => '_thumbnail_hover_image',
						'type'        => 'image',
						'button_text' => esc_html__( 'Add Thumbnail Hover', 'components_txtd' ),
						'class'       => 'thumbnail-hover',
					),
				),
			),
		);

		//allow others to make changes
		$featured_image_metaboxes = apply_filters( 'pixelgrade_featured_image_metaboxes_config', $featured_image_metaboxes );

		// Now add our metaboxes to the config
		if ( empty( $metaboxes ) ) {
			$metaboxes = array();
		}
		// We merge them so we allow for overwrite by our newer configurations when the same key has been used
		// http://php.net/manual/ro/function.array-merge.php
		$metaboxes = array_merge( $metaboxes, $featured_image_metaboxes );

		// Return our modified metaboxes configuration
		return $metaboxes;
	}

	/**
	 * Get the current being edited post type
	 *
	 * @return string Post type
	 */
	public function get_post_type() {
		// If we are in an AJAX call we can only use the request post_id
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			if ( isset( $_REQUEST['post_id'] ) ) {
				$post = get_post( absint( $_REQUEST['post_id'] ) );

				return $post->post_type;
			}
		}

		// Get the current screen
		$screen = get_current_screen();

		return $screen->post_type;
	}

	/**
	 * Removes the WordPress core featured image metabox
	 */
	public function remove_featured_image_metabox() {
		$post_type = $this->get_post_type();
		$component_config = $this->parent->get_config();
		// Some sanity check
		if ( empty( $component_config['post_types'] ) ) {
			$component_config['post_types'] = array();
		}

		if ( in_array( $post_type, $component_config['post_types'] ) ) {
			//remove original featured image metabox
			remove_meta_box( 'postimagediv', $post_type, 'side' );
		}
	}

	/**
	 * Make sure that we save the featured image the same way the core does it, when it is empty.
	 *
	 * @param string $new
	 * @param int $post_id
	 * @param array $field
	 *
	 * @return string
	 */
	public function save_featured_image_meta( $new, $post_id, $field ) {
		if ( isset( $field['id'] ) && '_thumbnail_id' == $field['id'] ) {
			if ( '-1' == $new ) {
				// Our CMB deletes the meta data when it is an empty string, not on -1
				$new = '';
			}
		}

		return $new;
	}

	/**
	 * Load when the admin is initialized
	 */
	public function admin_init() {
		/* register the styles and scripts specific to this component */
		wp_register_style( 'pixelgrade_featured_image-admin-style', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( Pixelgrade_Featured_Image::COMPONENT_SLUG ) . 'css/admin.css' ), array(), $this->parent->_assets_version );
	}

	/**
	 * Enqueue scripts and styles for the admin area
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		/* enqueue the styles and scripts specific to this component */
		if ( 'edit.php' != $hook ) {
			wp_enqueue_style( 'pixelgrade_featured_image-admin-style' );
		}
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
	 * Main Pixelgrade_Featured_Image_Metaboxes Instance
	 *
	 * Ensures only one instance of Pixelgrade_Featured_Image_Metaboxes is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @param Pixelgrade_Featured_Image $parent
	 *
	 * @return Pixelgrade_Featured_Image_Metaboxes
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
