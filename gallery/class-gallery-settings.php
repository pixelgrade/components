<?php
/**
 * This is the main class of our Gallery component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see      https://pixelgrade.com
 * @author   Pixelgrade
 * @package  Components/Gallery
 * @version  1.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders extra controls in the Gallery Settings section of the new media UI.
 *
 * Code "borrowed" from Jetpack and extended
 */
class Pixelgrade_Gallery_Settings {
	public $component = 'gallery';
	public $_version = '1.1.3';
	public $_assets_version = '1.1.3';

	private static $_instance = null;

	public static $gallery_instance = 0;
	public static $atts = array();

	private $gallery_spacing_default = 'small';
	private $gallery_spacing_options = array();

	function __construct() {
		// Define the spacing select options
		$this->gallery_spacing_options = array(
			'none'   => esc_html__( 'None', 'components' ),
			'small'  => esc_html__( 'Small', 'components' ),
			'medium' => esc_html__( 'Medium', 'components' ),
			'large'  => esc_html__( 'Large', 'components' ),
			'xlarge' => esc_html__( 'X-Large', 'components' ),
		);

		// Register our actions and filters
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		// Load the Jetpack fallback functionality
		add_action( 'wp_loaded', array( $this, 'jetpack_fallback' ) );

		// Initialize everything when in admin area
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// We use this filter to only store each gallery's attributes since in the gallery style filter we are not getting them :(
		add_filter( 'post_gallery', array( $this, 'post_gallery' ), 10, 3 );

		// We make sure that the spacing attribute is in order and passed along
		add_filter( 'shortcode_atts_gallery', array( $this, 'gallery_default_atts' ), 10, 4 );

		// We add the spacing and masonry classes to the gallery div
		add_filter( 'gallery_style', array( $this, 'gallery_classes' ), 10, 1 );

		// Add the masonry and (maybe) the slideshow gallery types
		add_filter( 'jetpack_gallery_types', array( $this, 'add_masonry_gallery_type' ), 10, 1 );
		add_filter( 'jetpack_gallery_types', array( $this, 'maybe_add_slideshow_gallery_type' ), 10, 1 );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_gallery_registered_hooks' );
	}

	function jetpack_fallback() {
		//Make sure that the Jetpack fallback functionality is loaded
		require_once( 'jetpack-fallback/functions.gallery.php' );
	}

	function admin_init() {
		/**
		 * Filter the available gallery types
		 *
		 * @param array $value Array of the default thumbnail grid gallery spacing.
		 *
		 */
		$this->gallery_spacing_options = apply_filters( 'pixelgrade_gallery_spacing_options', $this->gallery_spacing_options );

		// Enqueue the media UI only if needed.
		if ( count( $this->gallery_spacing_options ) > 1 ) {
			add_action( 'wp_enqueue_media', array( $this, 'wp_enqueue_media' ) );
			add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
		}

		// Register the styles and scripts specific to this component
		wp_register_style( 'pixelgrade_gallery-admin-style', trailingslashit( get_template_directory_uri() ) . 'components/gallery/css/admin.css', array(), $this->_assets_version );
	}

	/**
	 * Registers/enqueues the gallery settings admin js and CSS.
	 */
	function wp_enqueue_media() {
		wp_enqueue_style( 'pixelgrade_gallery-admin-style' );

		$dependecies = array( 'media-views' );
		// Make sure our script comes after Jetpack's so we can overwrite it
		if ( wp_script_is( 'jetpack-gallery-settings', 'registered' ) ) {
			$dependecies[] = 'jetpack-gallery-settings';
		}

		if ( ! wp_script_is( 'pixelgrade-gallery-settings', 'registered' ) ) {
			wp_register_script( 'pixelgrade-gallery-settings', trailingslashit( get_template_directory_uri() ) . 'components/gallery/js/gallery-settings.js', $dependecies, $this->_assets_version );
		}

		// Enqueue our script
		wp_enqueue_script( 'pixelgrade-gallery-settings' );
	}

	/**
	 * Adds the masonry gallery type to the list, after the default gallery type, if present.
	 *
	 * @param array $types The current gallery types
	 *
	 * @return array
	 */
	function add_masonry_gallery_type( $types ) {
		$setting = array( 'masonry' => esc_html__( 'Masonry', 'components' ) );

		//we want to insert after the default Thumbnail Grid
		$key = array_search( 'default', array_keys( $types ) );
		if ( false === $key ) {
			//it means we haven't found the key
			// simply prepend the array
			$types = $setting + $types;
		} else {
			//insert it after the Thumbnail Grid option
			$types = array_slice( $types, 0, $key + 1, true ) +
			         $setting +
			         array_slice( $types, $key + 1, null, true );
		}

		return $types;
	}

	/**
	 * Adds the slideshow gallery type, if it is not already present.
	 *
	 * @param array $types The current gallery types
	 *
	 * @return array
	 */
	function maybe_add_slideshow_gallery_type( $types ) {
		if ( ! isset( $types['slideshow'] ) ) {
			$types['slideshow'] = esc_html__( 'Slideshow', 'components' );
		}

		return $types;
	}

	/**
	 * We take advantage of the newly introduced $gallery_instance parameter so we can store each gallery's attributes for later use
	 *
	 * @param string $output The gallery output. Default empty.
	 * @param array $attr Attributes of the gallery shortcode.
	 * @param int $gallery_instance Unique numeric ID of this gallery shortcode instance.
	 *
	 * @return string
	 */
	function post_gallery( $output, $attr, $gallery_instance = 0 ) {
		// save the current instance and it's attributes
		self::$gallery_instance                = $gallery_instance;
		self::$atts[ self::$gallery_instance ] = $attr;

		return $output;
	}

	/**
	 * Add our spacing attribute to the list of default gallery attributes
	 *
	 * @param array $out The output array of shortcode attributes.
	 * @param array $pairs The supported attributes and their defaults.
	 * @param array $atts The user defined shortcode attributes.
	 * @param string $shortcode The shortcode name.
	 *
	 * @return array
	 */
	function gallery_default_atts( $out, $pairs, $atts, $shortcode ) {
		if ( empty( $atts['spacing'] ) ) {
			$out['spacing'] = $this->gallery_spacing_default;
		} else {
			$out['spacing'] = $atts['spacing'];
		}

		return $out;
	}

	/**
	 * We add the spacing and masonry classes to the gallery div
	 *
	 * @param string $out
	 *
	 * @return string
	 */
	function gallery_classes( $out ) {
		if ( empty( self::$atts[ self::$gallery_instance ]['spacing'] ) ) {
			self::$atts[ self::$gallery_instance ]['spacing'] = $this->gallery_spacing_default;
		}

		$out = str_replace( "class='gallery", "class='gallery  u-gallery-spacing--" . self::$atts[ self::$gallery_instance ]['spacing'], $out );

		//add also the type when it is a masonry gallery
		if ( ! empty( self::$atts[ self::$gallery_instance ]['type'] ) && 'masonry' == self::$atts[ self::$gallery_instance ]['type'] ) {
			$out = str_replace( "class='gallery", "class='gallery  u-gallery-type--" . self::$atts[ self::$gallery_instance ]['type'], $out );
		}

		// We may also need to add the slideshow class since we are using our Jetpack fallback
		if ( class_exists( 'Jetpack_Gallery_Settings_Fallback' ) ) {
			if ( ! empty( self::$atts[ self::$gallery_instance ]['type'] ) && 'slideshow' == self::$atts[ self::$gallery_instance ]['type'] ) {
				$out = str_replace( "class='gallery", "class='gallery  gallery--type-" . self::$atts[ self::$gallery_instance ]['type'], $out );
			}
		}

		return $out;
	}

	/**
	 * Outputs a view template which can be used with wp.media.template
	 */
	function print_media_templates() {
		/**
		 * Filter the default gallery spacing.
		 *
		 * @param string $value A string of the gallery spacing. Default is 'small'.
		 */
		$default_gallery_spacing = apply_filters( 'pixelgrade_default_gallery_spacing', $this->gallery_spacing_default );

		?>
			<script type="text/html" id="tmpl-pixelgrade-gallery-settings">
				<label class="setting">
					<span><?php esc_html_e( 'Spacing', 'components' ); ?></span>
					<select class="spacing" name="spacing" data-setting="spacing">

					<?php foreach ( $this->gallery_spacing_options as $value => $caption ) {
						echo '<option value="' . esc_attr( $value ) . '" ' . selected( $value, $default_gallery_spacing ) . '>' . esc_html( $caption ) . '</option>' . PHP_EOL;
					} ?>

					</select>
				</label>
			</script>
		<?php
	}

	/**
	 * Main Pixelgrade_Gallery_Settings Instance
	 *
	 * Ensures only one instance of Pixelgrade_Gallery_Settings is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Gallery_Settings()
	 * @return Pixelgrade_Gallery_Settings
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components' ), esc_html( $this->_version ) );
	} // End __wakeup ()
}
