<?php
/**
 * This is the main class of our Hero component.
 * (maybe this inspires you https://www.youtube.com/watch?v=-nbq6Ur103Q )
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Hero
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Load our component's template tags
pxg_load_component_file( 'hero', 'template-tags' );

class Pixelgrade_Hero {

	public $component = 'hero';
	public $_version  = '1.0.5';
	public $_assets_version = '1.0.0';

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
	public function register_hooks() {
		// Setup how things will behave in the WP admin area
		add_action( 'admin_init', array( $this, 'admin_init' ) );

		// Enqueue assets for the admin
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		// Setup our heroish PixTypes configuration
		add_filter( 'pixtypes_theme_activation_config', array( $this, 'pixtypes_config' ), 10, 1 );
		// Since WordPres 4.7 we need to do some trickery to show metaboxes on pages marked as Page for Posts since the page template control is removed for them
		add_filter( 'cmb_show_on', array( $this, 'pixtypes_show_on_metaboxes' ), 10, 2 );
		add_filter( 'pixtypes_cmb_metabox_show_on', array( $this, 'pixtypes_prevent_show_on_fields' ), 10, 2 );

		/* Hook-up to various places where we need to output things */

		// Add a class to the <body> to let the whole world know if there is a hero on not
		add_filter( 'body_class', array( $this, 'body_classes' ) );

		// Output the primary hero markup
		// We use a template tag
		add_action( 'pixelgrade_before_entry_title', 'pixelgrade_the_hero', 10, 1  );

		//Prevent the entry header from appearing in certain places
		add_filter( 'pixelgrade_display_entry_header', array( $this, 'prevent_entry_header' ), 10, 2 );

		// Add a data attribute to the menu items depending on the background color
		add_filter('nav_menu_link_attributes', array( $this, 'menu_item_color' ), 10, 4);

		// Add custom fields to attachments
		add_action( 'init', array( $this, '_register_attachments_custom_fields' ) );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_hero_registered_hooks' );
	}

	/**
	 * Change the PixTypes theme config depending on our needs
	 *
	 * @param array $config
	 *
	 * @return array
	 */
	public function pixtypes_config( $config ) {
		// These are the PixTypes configs for the metaboxes for each post type
		$hero_metaboxes = array(
			//The Hero Background controls - For pages
			'hero_area_background__page'       => array(
				'id'         => 'hero_area_background__page',
				'title'      => esc_html__( 'Hero Area » Background', 'components' ),
				'pages'      => array( 'page' ), // Post type
				'context'    => 'side',
				'priority'   => 'low',
				'hidden'     => false, //we need this set to false so the metaboxes will work for the page for posts also
				'show_names' => false, // Show field names on the left
				'show_on'    => array(
					'key'   => 'page-template',
					'value' => array( 'default', ), //the page templates to show on ie. 'page-templates/page-builder.php'
				),
				'show_on_page_for_posts' => true, //this is a special entry of our's to force things
				'fields'     => array(
					array(
						'name' => esc_html__( 'Gallery Image', 'components' ),
						'id'   => '_hero_background_gallery',
						'type' => 'gallery',
					),
					array(
						'name' => esc_html__( 'Playlist', 'components' ),
						'id'   => '_hero_background_videos',
						'type' => 'playlist',
					),
					array(
						'name'      => esc_html__( 'Image Opacity', 'components' ),
						'desc'	 	=> '<strong>' . esc_html__( 'Image Opacity', 'components' ) . '</strong>',
						'id'        => '_hero_image_opacity',
						'type'      => 'text_range',
						'std'   => '100',
						'html_args' => array(
							'min' => 1,
							'max' => 100
						)
					),
					array(
						'name' => esc_html__( 'Background Color', 'components' ),
						'desc' => '<strong>' . esc_html__( 'Background Color', 'components' ) . '</strong> <span class="tooltip" title="<p>' . esc_html__( 'Used as a background color during page transitions.', 'components' ) . '</p><p>' . esc_html__( 'Tip: It helps if the color matches the background color of the Hero image.', 'components' ) . '</p>"></span>',
						'id'   => '_hero_background_color',
						'type' => 'colorpicker',
						'std' => '#333333'
					),
				)
			),

			// The Hero Content controls - For pages
			'hero_area_content__page'     => array(
				'id'         => 'hero_area_content__page',
				'title'      => '&#x1f535; ' . esc_html__( 'Hero Area » Content', 'components' )
				                . ' <span class="tooltip" title="<' . 'title>'
				                . __( 'Hero Area » Content', 'components' )
				                . '</title><p>'
				                . __( 'Use this section to add a <strong>Title</strong> or a summary for this page. Get creative and add different elements like buttons, logos or other headings.', 'components')
				                . '</p><p>'
				                . __( 'You can insert a title using a <strong>Heading 1</strong> element, either on the Hero Area or using a <b>Text Block</b> within the above content area.', 'components')
				                . '</p><p>'
				                . __('* Note that the <strong>Page Title</strong> written above will <u>not</u> be included automatically on the page, so you have complete freedom in choosing where you place or how it looks.', 'components')
				                . "</p><p><a href='#'>"
				                . __('Learn more about Managing the Hero Area', 'components')
				                . '</a></p>"></span>',
				'pages'      => array( 'page' ), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'hidden'     => false, //we need this set to false so the metaboxes will work for the page for posts also
				'show_names' => true, // Show field names on the left
				'show_on'    => array(
					'key'   => 'page-template',
					'value' => array( 'default' ), //the page templates to show on ie. 'page-templates/page-builder.php'
				),
				'show_on_page_for_posts' => true, //this is a special entry of our's to force things
				'fields'     => array(
					array(
						'name'       => esc_html__( 'Description', 'components' ),
						'id'         => '_hero_content_description',
						'type'       => 'wysiwyg',
						'show_names' => false,
						'std'        => '<h1 class="h0">[Page Title]</h1>',

						'desc' => '<span class="hero-editor-visibility-status">
								<span class="dashicons  dashicons-visibility"></span>
								<span class="dashicons  dashicons-hidden"></span>
								<span class="hero-visibility-text">' . esc_html__( 'Visible Hero Area', 'components' ) . '</span>
								<span class="hero-hidden-text">' . esc_html__( 'Hidden Hero Area', 'components' ) . '</span>
								</span>
								<span class="hero-visibility-description">' . esc_html__( 'To hide the Hero Area section, remove the content above and any item from the Hero Area » Background.', 'components' ) . '</span>
								<span class="hero-hidden-description">' . esc_html__( 'Add some content above or an image to the Hero Area » Background to make the Hero Area visible.', 'components' ) . '</span>',

						'options'    => array(
							'media_buttons' => true,
							'textarea_rows' => 16,
							'editor_height' => 260,
							'teeny'         => false,
							'tinymce'       => true,
							'quicktags'     => true,
						),
					),

					array(
						'name'    => esc_html__( 'Hero Area Height', 'components' ),
						'desc'    => '<p>' . esc_html__( 'Set the height of the Hero Area relative to the browser window.', 'components' ) . '</p>',
						'id'      => '_hero_height',
						'type'    => 'select',
						'options' => array(
							array(
								'name'  => '&#9673;&#9673;&#9673; ' . esc_html__( 'Full Height', 'components' ),
								'value' => 'c-hero--full',
							),
							array(
								'name'  => '&#9673;&#9673;&#9711; ' . esc_html__( 'Two Thirds', 'components' ),
								'value' => 'c-hero--two-thirds',
							),
							array(
								'name'  => '&nbsp; &#9673;&#9711; ' . esc_html__( '&nbsp;Half', 'components' ),
								'value' => 'c-hero--half',
							)
						),
						'std'     => 'c-hero--full',
					),
					array(
						'name'    => esc_html__( 'Hero Content Alignment', 'components' ),
						'desc'    => '<p>Considering the background image focal point, you can align the content to make them both more visible.</p>
							<ul>
								<li>Mix it with a background color overlay to make it pop</li>
								<li>Individual text alignments will override this option</li>
								<li>You can align the content to make them both more visible.</li>
							</ul>',
						'id'      => '_hero_description_alignment',
						'type'    => 'positions_map',
						'options' => array(
							array(
								'name'  => '&#x2196;',
								'value' => 'top left',
							),
							array(
								'name'  => '&#8593;',
								'value' => 'top',
							),

							array(
								'name'  => '&#x2197;',
								'value' => 'top right',
							),

							array(
								'name'  => '&#8592; ',
								'value' => 'left',
							),

							array(
								'name'  => '&#x95;',
								'value' => '',
							),

							array(
								'name'  => '&#8594;',
								'value' => 'right',
							),

							array(
								'name'  => '&#x2199;',
								'value' => 'bottom left',
							),

							array(
								'name'  => '&#8595;',
								'value' => 'bottom',
							),

							array(
								'name'  => '&#x2198;',
								'value' => 'bottom right',
							),
						),
						'std'     => '',
					),
					// PAGE (Regular) Slideshow Options
					array(
						'name'    => '&#x1F307; &nbsp; ' . esc_html__( 'Slideshow Options', 'components' ),
						'id'      => '_hero_slideshow_options__title',
						'value' => __( 'Add more than one image to the <strong>Hero Area » Background</strong> to enable this section. ', 'components' ),
						'type'    => 'title',
					),
					array(
						'name'    => esc_html__( 'Auto Play', 'components' ),
						'desc'	  => esc_html__( 'The slideshow will automatically move to the next slide, after a period of time.', 'components' ),
						'id'      => '_hero_slideshow_options__autoplay',
						'type'    => 'select',
						'options' => array(
							array(
								'name'  => esc_html__( 'Enabled', 'components' ),
								'value' => true
							),
							array(
								'name'  => esc_html__( 'Disabled', 'components' ),
								'value' => false
							)
						),
						'std'     => false
					),
					array(
						'name'       => esc_html__( 'Auto Play Delay (s)', 'components' ),
						'desc'		=> esc_html__( 'Set the number of seconds to wait before moving to the next slide.', 'components' ),
						'id'         => '_hero_slideshow_options__delay',
						'type'       => 'text_small',
						'std'        => '5',
						'display_on' => array(
							'display' => true,
							'on'      => array(
								'field' => '_hero_slideshow_options__autoplay',
								'value' => true
							)
						),
					),
				),
			),

			//for the Contact/Location Page template
			'hero_area_map__page' => array(
				'id'         => 'hero_area_map__page',
				'title'      => esc_html__( 'Map Coordinates & Display Options', 'components' ),
				'pages'      => array( 'page' ), // Post type
				'context'    => 'normal',
				'priority'   => 'high',
				'hidden'     => true,
				'show_on'    => array(
					'key'   => 'page-template',
					'value' => array( 'page-templates/contact.php', 'page-templates/location-map.php' ),
					// 'hide' => true, // make this true if you want to hide it
				),
				'show_names' => true, // Show field names on the left
				'fields'     => array(
					array(
						'name'    => esc_html__( 'Map Height', 'components' ),
						'desc'    => '<p>' . esc_html__( 'Select the height of the Google Map area in relation to the browser window.', 'components' ) . '</p>',
						'id'      => '_hero_map_height',
						'type'    => 'select',
						'options' => array(
							array(
								'name'  => '&nbsp; &#9673;&#9711; ' . esc_html__( '&nbsp;Half', 'components' ),
								'value' => 'half-height',
							),
							array(
								'name'  => '&#9673;&#9673;&#9711; ' . esc_html__( 'Two Thirds', 'components' ),
								'value' => 'two-thirds-height',
							),
							array(
								'name'  => '&#9673;&#9673;&#9673; ' . esc_html__( 'Full Height', 'components' ),
								'value' => 'full-height',
							)
						),
						'std'     => 'full-height',
					),
					array(
						'name' => esc_html__( 'Google Maps URL', 'components' ),
						'desc' => __( 'Paste here the Share URL you have copied from <a href="http://www.google.com/maps" target="_blank">Google Maps</a>.', 'components' ),
						'id'   => '_hero_map_url',
						'type' => 'textarea_small',
						'std'  => '',
					),
					array(
						'name' => esc_html__( 'Custom Colors', 'components' ),
						'desc' => esc_html__( 'Allow us to change the map colors to better match your website.', 'components' ),
						'id'   => '_hero_map_custom_style',
						'type' => 'checkbox',
						'std'  => 'on',
					),
					array(
						'name'    => esc_html__( 'Pin Content', 'components' ),
						'desc'    => esc_html__( 'Insert here the content of the location marker - leave empty for no custom marker.', 'components' ),
						'id'      => '_hero_map_marker_content',
						'type'    => 'wysiwyg',
						'std'     => '',
						'options' => array(
							'media_buttons' => true,
							'textarea_rows' => 3,
							'teeny'         => false,
							'tinymce'       => true,
							'quicktags'     => true,
						),
					),
				),
			),
		);

		//allow others to make changes
		$hero_metaboxes = apply_filters( 'pixelgrade_hero_metaboxes_config', $hero_metaboxes );

		// Now add our metaboxes to the config
		if ( empty( $config['metaboxes'] ) ) {
			$config['metaboxes'] = array();
		}
		// We merge them so we allow for overwrite by our newer configurations when the same key has been used
		// http://php.net/manual/ro/function.array-merge.php
		$config['metaboxes'] = array_merge( $config['metaboxes'], $hero_metaboxes );

		// Return our modified PixTypes configuration
		return $config;
	}

	/**
	 * Force a metabox to be shown on
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
	 * Load on when the admin is initialized
	 */
	public function admin_init() {
		/* register the styles and scripts specific to heroes */
		wp_register_style( 'pixelgrade_hero-admin-style', trailingslashit( get_template_directory_uri() ) . "components/hero/css/admin.css", array(), $this->_assets_version );
		wp_register_script( 'pixelgrade_hero-admin-scripts', trailingslashit( get_template_directory_uri() ) . "components/hero/js/admin.js", array(), $this->_assets_version );

	}

	/**
	 * Enqueue scripts and styles for the admin area
	 *
	 * @param string $hook
	 */
	public function admin_enqueue_scripts( $hook ) {
		/* enqueue the styles and scripts specific to heroes */
		if ( 'edit.php' != $hook ) {
			wp_enqueue_style( 'pixelgrade_hero-admin-style');
			wp_enqueue_script( 'pixelgrade_hero-admin-scripts' );
		}
	}

	/**
	 * Return false to prevent the entry_header section markup to be displayed
	 *
	 * @param bool $display
	 * @param string|array $location Optional. The place (template) where this is needed.
	 *
	 * @return bool
	 */
	public function prevent_entry_header( $display, $location = '' ) {
		//if we actually have a valid hero, don't show the entry header
		if ( pixelgrade_hero_is_hero_needed( $location ) ) {
			return false;
		}

		return $display;
	}

	/**
	 * Add a data attribute to the menu items depending on the background color
	 *
	 * @param array $atts {
	 *     The HTML attributes applied to the menu item's `<a>` element, empty strings are ignored.
	 *
	 *     @type string $title  Title attribute.
	 *     @type string $target Target attribute.
	 *     @type string $rel    The rel attribute.
	 *     @type string $href   The href attribute.
	 * }
	 * @param WP_Post  $item  The current menu item.
	 * @param stdClass $args  An object of wp_nav_menu() arguments.
	 * @param int      $depth Depth of menu item. Used for padding.
	 *
	 * @return array
	 */
	public function menu_item_color($atts, $item, $args, $depth) {
		$atts['data-color'] = trim( pixelgrade_hero_get_background_color( $item->object_id ) );

		return $atts;
	}

	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function body_classes( $classes ) {
		//bail if we are in the admin area
		if ( is_admin() ) {
			return $classes;
		}

		if ( pixelgrade_hero_is_hero_needed() ) {
			$classes[] = 'has-hero';
		}

		return $classes;
	}

	/**
	 * Adds custom fields to attachments.
	 */
	public function _register_attachments_custom_fields() {
		//add video support for attachments
		add_filter( 'attachment_fields_to_edit', array( $this, '_add_video_url_field_to_attachments' ), 99999, 2 );
		add_filter( 'attachment_fields_to_save', array( $this, '_add_image_attachment_fields_to_save' ), 9999, 2 );
	}

	/**
	 * Add the video url field to attachments.
	 *
	 * @param array   $form_fields An array of attachment form fields.
	 * @param WP_Post $post        The WP_Post attachment object.
	 *
	 * @return array
	 */
	public function _add_video_url_field_to_attachments( $form_fields, $post ) {
		$link_media_to_value = get_post_meta( $post->ID, "_link_media_to", true );

		if ( ! isset( $form_fields["link_media_to"] ) ) {

			$select_options = array(
				'none'             => esc_html__( 'None', 'components' ),
				'media_file'       => esc_html__( 'Media File', 'components' ),
				'custom_image_url' => esc_html__( 'Custom Image URL', 'components' ),
				'custom_video_url' => esc_html__( 'Custom Video URL', 'components' ),
				'external'         => esc_html__( 'External URL', 'components' )
			);

			$select_html = '<select name="attachments[' . $post->ID . '][link_media_to]" id="attachments[' . $post->ID . '][link_media_to]">';

			foreach ( $select_options as $key => $option ) {

				$selected = '';

				if ( $link_media_to_value == $key ) {
					$selected = 'selected="selected"';
				}

				$select_html .= '<option value="' . $key . '" ' . $selected . '>' . $option . '</option>';
			}

			$select_html .= '</select>';

			$form_fields["link_media_to"] = array(
				'label' => esc_html__( 'Linked To', 'components' ),
				'input' => 'html',
				'html'  => $select_html
			);
		}

		if ( ! isset( $form_fields["video_url"] ) && ! empty( $link_media_to_value ) && $link_media_to_value == 'custom_video_url' ) {
			$form_fields["video_url"] = array(
				"label" => esc_html__( "Custom Video URL", 'components' ),
				"input" => "text", // this is default if "input" is omitted
				"value" => esc_url( get_post_meta( $post->ID, "_video_url", true ) ),
				"helps" => __( "<p class='desc'>Attach a video to this image <span class='small'>(YouTube or Vimeo)</span>.</p>", 'components' ),
			);
		}

		if ( ! isset( $form_fields["custom_image_url"] ) && ! empty( $link_media_to_value ) && $link_media_to_value == 'custom_image_url' ) {
			$form_fields["custom_image_url"] = array(
				"label" => esc_html__( "Custom Image URL", 'components' ),
				"input" => "text", // this is default if "input" is omitted
				"value" => esc_url( get_post_meta( $post->ID, "_custom_image_url", true ) ),
				"helps" => __( "<p class='desc'>Link this image to a custom url.</p>", 'components' ),
			);
		}

		if ( ! isset( $form_fields["video_autoplay"] ) && ! empty( $link_media_to_value ) && $link_media_to_value == 'custom_video_url' ) {

			$meta = get_post_meta( $post->ID, "_video_autoplay", true );
			// Set the checkbox checked or not
			if ( $meta == 'on' ) {
				$checked = ' checked="checked"';
			} else {
				$checked = '';
			}

			$form_fields["video_autoplay"] = array(
				"label" => esc_html__( "Video Autoplay", 'components' ),
				"input" => "html",
				"html"  => '<input' . $checked . ' type="checkbox" name="attachments[' . $post->ID . '][video_autoplay]" id="attachments[' . $post->ID . '][video_autoplay]" /><label for="attachments[' . $post->ID . '][video_autoplay]">' . __( 'Enable Video Autoplay?', 'components' ) . '</label>'
			);
		}

		if ( ! isset( $form_fields["external_url"] ) && ! empty( $link_media_to_value ) && $link_media_to_value == 'external' ) {
			$form_fields["external_url"] = array(
				"label" => esc_html__( "External URL", 'components' ),
				"input" => "text",
				"value" => esc_url( get_post_meta( $post->ID, "_external_url", true ) ),
				"helps" => __( "<p class='desc'>Set this image to link to an external website.</p>", 'components' ),
			);
		}

		return $form_fields;
	}

	/**
	 * Save custom media metadata fields
	 * Be sure to validate your data before saving it
	 * http://codex.wordpress.org/Data_Validation
	 *
	 * @param WP_Post $post       The $post data for the attachment
	 * @param array $attachment The $attachment part of the form $_POST ($_POST[attachments][postID])
	 *
	 * @return WP_Post $post
	 */
	public function _add_image_attachment_fields_to_save( $post, $attachment ) {

		if ( isset( $attachment['link_media_to'] ) ) {
			update_post_meta( $post['ID'], '_link_media_to', $attachment['link_media_to'] );
		}

		if ( isset( $attachment['custom_image_url'] ) ) {
			update_post_meta( $post['ID'], '_custom_image_url', esc_url( $attachment['custom_image_url'] ) );
		}

		if ( isset( $attachment['video_url'] ) ) {
			update_post_meta( $post['ID'], '_video_url', esc_url( $attachment['video_url'] ) );
		}

		if ( isset( $attachment['video_autoplay'] ) ) {
			update_post_meta( $post['ID'], '_video_autoplay', 'on' );
		} else {
			update_post_meta( $post['ID'], '_video_autoplay', 'off' );
		}


		if ( isset( $attachment['external_url'] ) ) {
			update_post_meta( $post['ID'], '_external_url', esc_url( $attachment['external_url'] ) );
		}

		return $post;
	}

	/**
	 * Main Pixelgrade_Hero Instance
	 *
	 * Ensures only one instance of Pixelgrade_Hero is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Hero()
	 * @return Pixelgrade_Hero
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
		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?', 'components' ) ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?', 'components' ) ),  esc_html( $this->_version ) );
	} // End __wakeup ()
}