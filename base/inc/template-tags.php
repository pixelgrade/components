<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Base
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Display attributes for a element.
 *
 * @param string|array $attributes Optional. One or more attributes to add to the list.
 * @param string|array $location Optional. The place (template) where the attributes are displayed. This is a hint for filters.
 */
function pixelgrade_element_attributes( $attributes = array(), $location = '' ) {
	echo pixelgrade_generate_attributes_output( pixelgrade_get_element_attributes( $attributes, $location ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Retrieve the attributes for a element as an array.
 *
 * @param string|array $attributes Optional. One or more attributes to add to the list.
 * @param string|array $location Optional. The place (template) where the attributes are displayed. This is a hint for filters.
 *
 * @return array Array of classes.
 */
function pixelgrade_get_element_attributes( $attributes = array(), $location = '' ) {
	$final_attributes = array();

	if ( ! empty( $attributes ) ) {
		$final_attributes = array_merge( $final_attributes, $attributes );
	} else {
		// Ensure that we always coerce attributes to being an array.
		$attributes = array();
	}

	/**
	 * Filters the list of attributes.
	 *
	 * @param array $final_attributes An array of attributes.
	 * @param array $attributes  An array of additional attributes to be added.
	 * @param string|array $location The place (template) where the attributes are needed.
	 */
	return apply_filters( 'pixelgrade_get_element_attributes', $final_attributes, $attributes, $location );
}

/**
 * Concatenate attributes names and values, with escaping, into one string ready for output.
 *
 * @param array $attributes
 *
 * @return string The concatenated attributes string or empty string.
 */
function pixelgrade_generate_attributes_output( $attributes = array() ) {
	// Generate a string attributes array, like array( 'rel="test"', 'href="boom"' )
	$full_attributes = array();
	foreach ( $attributes as $name => $value ) {
		// We really don't want numeric keys as attributes names
		if ( ! empty( $name ) && ! is_numeric( $name ) ) {
			// If we get an array as value we will add them comma separated
			if ( ! empty( $value ) && is_array( $value ) ) {
				$value = join( ', ', $value );
			}

			// If we receive an empty array entry (but with a key) we will treat it like an attribute without value (i.e. itemprop)
			if ( empty( $value ) ) {
				$full_attributes[] = esc_attr( $name );
			} else {
				$full_attributes[] = esc_attr( $name ) . '="' . esc_attr( $value ) . '"';
			}
		}
	}

	if ( ! empty( $full_attributes ) ) {
		return join( ' ', $full_attributes );
	}

	return '';
}

/**
 * Display the attributes for the body element.
 *
 * @param string|array $attributes One or more attributes to add to the attributes list.
 */
function pixelgrade_body_attributes( $attributes = array() ) {
	// Get the attributes
	$body_attributes = pixelgrade_get_element_attributes( $attributes, array( 'body' ) );

	/**
	 * Filters the list of body attributes for the current post or page.
	 *
	 * @param array $attributes An array of body attributes.
	 * @param array $attribute  An array of additional attributes added to the body.
	 */
	$body_attributes = apply_filters( 'pixelgrade_body_attributes', $body_attributes, $attributes );

	echo pixelgrade_generate_attributes_output( $body_attributes ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

/**
 * Display the classes for a element.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
 * @param string       $prefix Optional. Prefix to prepend to all of the provided classes
 * @param string       $suffix Optional. Suffix to append to all of the provided classes
 */
function pixelgrade_css_class( $class = '', $location = '', $prefix = '', $suffix = '' ) {
	// Separates classes with a single space, collates classes for element
	echo 'class="' . esc_attr( join( ' ', pixelgrade_get_css_class( $class, $location ) ) ) . '"';
}

/**
 * Retrieve the classes for a element as an array.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
 * @param string       $prefix Optional. Prefix to prepend to all of the provided classes
 * @param string       $suffix Optional. Suffix to append to all of the provided classes
 *
 * @return array Array of classes.
 */
function pixelgrade_get_css_class( $class = '', $location = '', $prefix = '', $suffix = '' ) {
	$classes = array();

	if ( ! empty( $class ) ) {
		$class = Pixelgrade_Value::maybeSplitByWhitespace( $class );

		// If we have a prefix then we need to add it to every class
		if ( ! empty( $prefix ) && is_string( $prefix ) ) {
			foreach ( $class as $key => $value ) {
				$class[ $key ] = $prefix . $value;
			}
		}

		// If we have a suffix then we need to add it to every class
		if ( ! empty( $suffix ) && is_string( $suffix ) ) {
			foreach ( $class as $key => $value ) {
				$class[ $key ] = $value . $suffix;
			}
		}

		$classes = array_merge( $classes, $class );
	} else {
		// Ensure that we always coerce class to being an array.
		$class = array();
	}

	$classes = array_map( 'esc_attr', $classes );

	/**
	 * Filters the list of CSS classes
	 *
	 * @param array $classes An array of classes.
	 * @param array $class   An array of additional classes to be added.
	 * @param string|array $location   The place (template) where the classes are needed.
	 * @param string $prefix The prefix applied to all the classes.
	 * @param string $suffix The suffix applied to all the classes.
	 */
	$classes = apply_filters( 'pixelgrade_css_class', $classes, $class, $location, $prefix, $suffix );

	return array_unique( $classes );
}

if ( ! function_exists( 'pixelgrade_show_thumbnail' ) ) {
	/**
	 * Determine if a post thumbnail should be shown.
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or post object. Defaults to the current post.
	 *
	 * @return bool
	 */
	function pixelgrade_show_thumbnail( $post_id = null ) {
		$post = get_post( $post_id );

		$jetpack_show_single_featured_image = get_option( 'jetpack_content_featured_images_post', true );

		$show = true;

		// No not show if no post, no post thumbnail, or the image is hidden from Jetpack's content options
		if ( empty( $post ) || empty( $jetpack_show_single_featured_image ) || ! has_post_thumbnail( $post ) ) {
			$show = false;
		}

		return apply_filters( 'pixelgrade_show_thumbnail', $show, $post_id );
	}
}

if ( ! function_exists( 'pixelgrade_has_portrait_thumbnail' ) ) {
	/**
	 * Determine if a post thumbnail should be shown and it has a portrait aspect ratio.
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or post object. Defaults to the current post.
	 *
	 * @return bool
	 */
	function pixelgrade_has_portrait_thumbnail( $post_id = null ) {
		$post = get_post( $post_id );

		// Bail if we shouldn't show the post thumbnail.
		if ( ! pixelgrade_show_thumbnail( $post ) ) {
			return false;
		}

		$image_type = pixelgrade_get_image_aspect_ratio_type( get_post_thumbnail_id( $post ) );

		if ( 'portrait' === $image_type ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'pixelgrade_has_landscape_thumbnail' ) ) {
	/**
	 * Determine if a post thumbnail should be shown and it has a landscape aspect ratio.
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or post object. Defaults to the current post.
	 *
	 * @return bool
	 */
	function pixelgrade_has_landscape_thumbnail( $post_id = null ) {
		$post = get_post( $post_id );

		// Bail if we shouldn't show the post thumbnail.
		if ( ! pixelgrade_show_thumbnail( $post ) ) {
			return false;
		}

		$image_type = pixelgrade_get_image_aspect_ratio_type( get_post_thumbnail_id( $post ) );

		if ( 'landscape' === $image_type ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'pixelgrade_has_no_thumbnail' ) ) {
	/**
	 * Determine if a post thumbnail is missing or should not be shown.
	 *
	 * Notice: Please note the reverse logic this template tag uses!!!
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or post object. Defaults to the current post.
	 *
	 * @return bool
	 */
	function pixelgrade_has_no_thumbnail( $post_id = null ) {
		$post = get_post( $post_id );

		// Bail if we shouldn't show the post thumbnail.
		if ( pixelgrade_show_thumbnail( $post ) ) {
			return false;
		}

		if ( has_post_thumbnail( $post ) ) {
			return false;
		}

		return true;
	}

}

if ( ! function_exists( 'pixelgrade_get_post_thumbnail_aspect_ratio_class' ) ) {
	/**
	 * Get the class corresponding to the aspect ratio of the post featured image
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or post object.
	 *
	 * @return string Aspect ratio specific class.
	 */
	function pixelgrade_get_post_thumbnail_aspect_ratio_class( $post_id = null ) {
		// Bail if we shouldn't show the post thumbnail.
		if ( ! pixelgrade_show_thumbnail( $post_id ) ) {
			return 'none';
		}

		return pixelgrade_get_image_aspect_ratio_type( get_post_thumbnail_id( $post_id ), 'none' );
	}
}

if ( ! function_exists( 'pixelgrade_get_image_aspect_ratio_type' ) ) {
	/**
	 * Retrieve the aspect ratio type of an image.
	 *
	 * @param int|WP_Post          $image The image attachment ID or the attachment object.
	 * @param bool|string Optional . The default to return in case of failure.
	 *
	 * @return string|bool Returns the aspect ratio type string, or false|$default, if no image is available.
	 */
	function pixelgrade_get_image_aspect_ratio_type( $image, $default = false ) {
		// We expect to receive an attachment ID or attachment post object
		if ( is_numeric( $image ) ) {
			// In case we've got a number, we will coerce it to an int
			$image = (int) $image;
		}

		// Try and get the attachment post object
		$image = get_post( $image );
		if ( ! $image ) {
			return $default;
		}

		// We only work with real images
		if ( ! wp_attachment_is_image( $image ) ) {
			return $default;
		}

		// $image_data[1] is width
		// $image_data[2] is height
		// we use the full image size to avoid the Photon messing around with the data - at least for now
		$image_data = wp_get_attachment_image_src( $image->ID, 'full' );

		if ( empty( $image_data ) ) {
			return $default;
		}

		// We default to a landscape aspect ratio
		$type = 'landscape';
		if ( ! empty( $image_data[1] ) && ! empty( $image_data[2] ) ) {
			$image_aspect_ratio = $image_data[1] / $image_data[2];

			// now let's begin to see what kind of featured image we have
			// first portrait images
			if ( $image_aspect_ratio <= 1 ) {
				$type = 'portrait';
			}
		}

		return apply_filters( 'pixelgrade_image_aspect_ratio_type', $type, $image );
	}
}

if ( ! function_exists( 'pixelgrade_display_featured_images' ) ) {
	/**
	 * Check if according to the Content Options we need to display the featured image.
	 *
	 * @return bool
	 */
	function pixelgrade_display_featured_images() {
		if ( function_exists( 'jetpack_featured_images_get_settings' ) ) {
			$opts = jetpack_featured_images_get_settings();

			// Returns false if the archive option or singular option is unticked.
			if ( ( true === $opts['archive'] && ( is_home() || is_archive() || is_search() ) && ! $opts['archive-option'] )
				|| ( true === $opts['post'] && is_single() && ! $opts['post-option'] )
				|| ( true === $opts['page'] && is_singular() && is_page() && ! $opts['page-option'] )
			) {
				return false;
			}
		}

		return true;
	}
}

function pixelgrade_the_taxonomy_dropdown( $taxonomy, $current_term = null ) {
	echo pixelgrade_get_the_taxonomy_dropdown( $taxonomy, $current_term ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( ! function_exists( 'pixelgrade_get_the_taxonomy_dropdown' ) ) {

	/**
	 * @param $taxonomy
	 * @param null     $current_term
	 *
	 * @return bool|string
	 */
	function pixelgrade_get_the_taxonomy_dropdown( $taxonomy, $current_term = null ) {
		$output = '';

		// The HTML id and name attributes
		$id = $taxonomy . '-dropdown';

		$taxonomy_obj = get_taxonomy( $taxonomy );
		// bail if we couldn't get the taxonomy object or other important data
		if ( empty( $taxonomy_obj ) || empty( $taxonomy_obj->object_type ) ) {
			return false;
		}

		// Get all the terms of the taxonomy
		$terms = get_terms( $taxonomy );

		$selected = '';
		// If not given a $current_term, try to find out one
		if ( ! $current_term && ( is_tax() || is_tag() || is_category() ) ) {
			$current_term = get_queried_object();
			if ( $current_term ) {
				$selected = $current_term->slug;
			}
		}

		// Get the first post type
		$post_type = reset( $taxonomy_obj->object_type );
		// Get the post type's archive URL
		$archive_link = get_post_type_archive_link( $post_type );

		$output .= '<select class="taxonomy-select js-taxonomy-dropdown" name="' . esc_attr( $id ) . '" id="' . esc_attr( $id ) . '">';

		$selected_attr = '';
		if ( empty( $selected ) ) {
			$selected_attr = 'selected';
		}
		$output .= '<option value="' . esc_attr( $archive_link ) . '" ' . esc_attr( $selected_attr ) . '>' . esc_html__( 'Everything', '__components_txtd' ) . '</option>';

		foreach ( $terms as $term ) {
			$selected_attr = '';
			if ( ! empty( $selected ) && $selected === $term->slug ) {
				$selected_attr = 'selected';
			}
			$output .= '<option value="' . esc_attr( get_term_link( intval( $term->term_id ), $taxonomy ) ) . '" ' . esc_attr( $selected_attr ) . '>' . esc_html( $term->name ) . '</option>';
		}
		$output .= '</select>';

		// Allow others to have a go at it
		return apply_filters( 'pixelgrade_get_the_taxonomy_dropdown', $output, $taxonomy, $selected );
	}
}

if ( ! function_exists( 'pixelgrade_get_rendered_content' ) ) :
	/**
	 * Return the rendered post content.
	 *
	 * This is the same as the_content() except for the fact that it doesn't display the content, but returns it.
	 * Do make sure not to use this function twice for a post inside the loop, because it would defeat the purpose.
	 *
	 * @param string $more_link_text Optional. Content for when there is more text.
	 * @param bool   $strip_teaser   Optional. Strip teaser content before the more text. Default is false.
	 * @return string
	 */
	function pixelgrade_get_rendered_content( $more_link_text = null, $strip_teaser = false ) {
		$content = get_the_content( $more_link_text, $strip_teaser );

		/**
		 * Filters the post content.
		 *
		 * @param string $content Content of the current post.
		 */
		$content = apply_filters( 'the_content', $content );
		$content = str_replace( ']]>', ']]&gt;', $content );

		return $content;
	}
endif;

if ( ! function_exists( 'pixelgrade_get_header' ) ) {
	/**
	 * Load the header template.
	 *
	 * It does the same thing as @see get_header()
	 * but with the added benefit of being filterable via @see get_query_template()
	 *
	 * @param string $name The name of the specialised header.
	 */
	function pixelgrade_get_header( $name = '' ) {
		// We do the same action as the core get_header() to keep things consistent
		do_action( 'get_header', $name );

		// We start with the same templates as the core get_header()
		$template_names = array();
		$name           = (string) $name;
		if ( '' !== $name ) {
			$template_names[] = "header-{$name}.php";
		}
		$template_names[] = 'header.php';

		// But we do a little bit of magic by making use of the get_query_template() function and it's dynamic filters
		// This way we allow others (our components) to add to the template candidates stack and use their own templates in a predictable manner
		// Too bad the core function doesn't provide at least a filter for the used templates.
		$template = get_query_template( 'header', $template_names );

		if ( ! empty( $template ) ) {
			load_template( $template, true );
		}
	}
}

if ( ! function_exists( 'pixelgrade_get_footer' ) ) {
	/**
	 * Load the footer template.
	 *
	 * It does the same thing as @see get_footer()
	 * but with the added benefit of being filterable via @see get_query_template()
	 *
	 * @param string $name The name of the specialised footer.
	 */
	function pixelgrade_get_footer( $name = '' ) {
		// We do the same action as the core get_footer() to keep things consistent
		do_action( 'get_footer', $name );

		// We start with the same templates as the core get_footer()
		$template_names = array();
		$name           = (string) $name;
		if ( '' !== $name ) {
			$template_names[] = "footer-{$name}.php";
		}
		$template_names[] = 'footer.php';

		// But we do a little bit of magic by making use of the get_query_template() function and it's dynamic filters
		// This way we allow others (our components) to add to the template candidates stack and use their own templates in a predictable manner
		// Too bad the core function doesn't provide at least a filter for the used templates.
		$template = get_query_template( 'footer', $template_names );

		if ( ! empty( $template ) ) {
			load_template( $template, true );
		}
	}
}

if ( ! function_exists( 'pixelgrade_get_sidebar' ) ) {
	/**
	 * Load the sidebar template.
	 *
	 * It does the same thing as @see get_sidebar()
	 * but with the added benefit of being filterable via @see get_query_template()
	 *
	 * @param string $name The name of the specialised sidebar.
	 */
	function pixelgrade_get_sidebar( $name = '' ) {
		// We do the same action as the core get_sidebar() to keep things consistent
		do_action( 'get_sidebar', $name );

		// We start with the same templates as the core get_sidebar()
		$template_names = array();
		$name           = (string) $name;
		if ( '' !== $name ) {
			$template_names[] = "sidebar-{$name}.php";
		}
		$template_names[] = 'sidebar.php';

		// But we do a little bit of magic by making use of the get_query_template() function and it's dynamic filters
		// This way we allow others (our components) to add to the template candidates stack and use their own templates in a predictable manner
		// Too bad the core function doesn't provide at least a filter for the used templates.
		$template = get_query_template( 'sidebar', $template_names );

		if ( ! empty( $template ) ) {
			load_template( $template, true );
		}
	}
}

if ( ! function_exists( 'pixelgrade_do_fake_loop' ) ) {
	/**
	 * Use this to display the actual loop of a page that uses the Pixelgrade_Custom_Loops_For_Pages logic
	 *
	 * This is a fake loop as the class Pixelgrade_Custom_Loops_For_Pages uses post injection,
	 * thus being able to keep full post integrity,
	 * so $wp_the_query->post, $wp_query->post, $posts and $post stays constant throughout the template.
	 *
	 * @see Pixelgrade_CustomLoopsForPages
	 */
	function pixelgrade_do_fake_loop() {
		// The Loop - actually a fake loop
		while ( have_posts() ) :
			the_post();
			/*
			 * Do nothing here as we will do it via hooks
			 * @see Pixelgrade_Custom_Loops_For_Pages
			 */
		endwhile;

		wp_reset_query();
	}
}

if ( ! function_exists( 'pixelgrade_is_active_sidebar' ) ) {
	/**
	 * Determines whether a sidebar is in use.
	 *
	 * This is a modified version of the core template tag is_active_sidebar() due to the fact that it conflicted with
	 * the Customizer logic for displaying available widget areas. See WP_Customize_Widgets::tally_sidebars_via_is_active_sidebar_calls()
	 *
	 * Also see this discussion: https://core.trac.wordpress.org/ticket/39087#comment:12
	 *
	 * @param string|int $index Sidebar name, id or number to check.
	 *
	 * @return bool true if the sidebar is in use, false otherwise.
	 */
	function pixelgrade_is_active_sidebar( $index ) {
		global $wp_registered_sidebars;

		$index             = ( is_int( $index ) ) ? "sidebar-$index" : sanitize_title( $index );
		$sidebars_widgets  = wp_get_sidebars_widgets();
		$is_active_sidebar = ! empty( $wp_registered_sidebars[ $index ] ) && ! empty( $sidebars_widgets[ $index ] );

		// We have simply omitted to apply the "is_active_sidebar" filter.
		return $is_active_sidebar;
	}
}

if ( ! function_exists( 'pixelgrade_get_boundary_post' ) ) {
	/**
	 * Retrieves the boundary post in the same post type as the current post
	 *
	 * Boundary being either the first or last post by publish date within the constraints specified
	 * by $in_same_term or $excluded_terms.
	 *
	 * NOTICE: This is a enhanced version of the CORE get_boundary_post() function!
	 * - we have fixed it to remain in the same post type as does get_adjacent_post()!
	 *
	 * @param bool $in_same_term Optional. Whether returned post should be in a same taxonomy term.
	 *                                     Default false.
	 * @param array|string $excluded_terms Optional. Array or comma-separated list of excluded term IDs.
	 *                                     Default empty.
	 * @param bool $start Optional. Whether to retrieve first or last post. Default true
	 * @param string $taxonomy Optional. Taxonomy, if $in_same_term is true. Default 'category'.
	 *
	 * @return null|string|WP_Post Post object if successful. Null if global $post is not set. Empty string if no
	 *                             corresponding post exists.
	 */
	function pixelgrade_get_boundary_post( $in_same_term = false, $excluded_terms = '', $start = true, $taxonomy = 'category' ) {
		global $wpdb;

		$post = get_post();
		if ( ! $post || ! is_single() || is_attachment() || ! taxonomy_exists( $taxonomy ) ) {
			return null;
		}

		$join = '';
		$where = '';
		$location = $start ? 'first' : 'last';

		if ( $in_same_term || ! empty( $excluded_terms ) ) {
			if ( ! empty( $excluded_terms ) && ! is_array( $excluded_terms ) ) {
				// back-compat, $excluded_terms used to be $excluded_terms with IDs separated by " and "
				if ( false !== strpos( $excluded_terms, ' and ' ) ) {
					_deprecated_argument( __FUNCTION__, '3.3.0', sprintf( __( 'Use commas instead of %s to separate excluded terms.', 'components_txtd' ), "'and'" ) );
					$excluded_terms = explode( ' and ', $excluded_terms );
				} else {
					$excluded_terms = explode( ',', $excluded_terms );
				}

				$excluded_terms = array_map( 'intval', $excluded_terms );
			}

			if ( $in_same_term ) {
				$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
				$where .= $wpdb->prepare( "AND tt.taxonomy = %s", $taxonomy );

				if ( ! is_object_in_taxonomy( $post->post_type, $taxonomy ) )
					return '';
				$term_array = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );

				// Remove any exclusions from the term array to include.
				$term_array = array_diff( $term_array, (array) $excluded_terms );
				$term_array = array_map( 'intval', $term_array );

				if ( ! $term_array || is_wp_error( $term_array ) )
					return '';

				$where .= " AND tt.term_id IN (" . implode( ',', $term_array ) . ")";
			}

			/**
			 * Filters the IDs of terms excluded from adjacent post queries.
			 *
			 * The dynamic portion of the hook name, `$adjacent`, refers to the type
			 * of adjacency, 'next' or 'previous'.
			 *
			 * @since 4.4.0
			 *
			 * @param string $excluded_terms Array of excluded term IDs.
			 */
			$excluded_terms = apply_filters( "get_{$location}_post_excluded_terms", $excluded_terms );

			if ( ! empty( $excluded_terms ) ) {
				$where .= " AND p.ID NOT IN ( SELECT tr.object_id FROM $wpdb->term_relationships tr LEFT JOIN $wpdb->term_taxonomy tt ON (tr.term_taxonomy_id = tt.term_taxonomy_id) WHERE tt.term_id IN (" . implode( ',', array_map( 'intval', $excluded_terms ) ) . ') )';
			}
		}

		// 'post_status' clause depends on the current user.
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			$post_type_object = get_post_type_object( $post->post_type );
			if ( empty( $post_type_object ) ) {
				$post_type_cap    = $post->post_type;
				$read_private_cap = 'read_private_' . $post_type_cap . 's';
			} else {
				$read_private_cap = $post_type_object->cap->read_private_posts;
			}

			/*
			 * Results should include private posts belonging to the current user, or private posts where the
			 * current user has the 'read_private_posts' cap.
			 */
			$private_states = get_post_stati( array( 'private' => true ) );
			$where .= " AND ( p.post_status = 'publish'";
			foreach ( (array) $private_states as $state ) {
				if ( current_user_can( $read_private_cap ) ) {
					$where .= $wpdb->prepare( " OR p.post_status = %s", $state );
				} else {
					$where .= $wpdb->prepare( " OR (p.post_author = %d AND p.post_status = %s)", $user_id, $state );
				}
			}
			$where .= " )";
		} else {
			$where .= " AND p.post_status = 'publish'";
		}

		$order = $start ? 'ASC' : 'DESC';

		/**
		 * Filters the JOIN clause in the SQL for an adjacent post query.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 2.5.0
		 * @since 4.4.0 Added the `$taxonomy` and `$post` parameters.
		 *
		 * @param string  $join           The JOIN clause in the SQL.
		 * @param bool    $in_same_term   Whether post should be in a same taxonomy term.
		 * @param array   $excluded_terms Array of excluded term IDs.
		 * @param string  $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
		 * @param WP_Post $post           WP_Post object.
		 */
		$join = apply_filters( "get_{$location}_post_join", $join, $in_same_term, $excluded_terms, $taxonomy, $post );

		/**
		 * Filters the WHERE clause in the SQL for an adjacent post query.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 2.5.0
		 * @since 4.4.0 Added the `$taxonomy` and `$post` parameters.
		 *
		 * @param string $where          The `WHERE` clause in the SQL.
		 * @param bool   $in_same_term   Whether post should be in a same taxonomy term.
		 * @param array  $excluded_terms Array of excluded term IDs.
		 * @param string $taxonomy       Taxonomy. Used to identify the term used when `$in_same_term` is true.
		 * @param WP_Post $post           WP_Post object.
		 */
		$where = apply_filters( "get_{$location}_post_where", $wpdb->prepare( "WHERE p.post_type = %s $where", $post->post_type ), $in_same_term, $excluded_terms, $taxonomy, $post );

		/**
		 * Filters the ORDER BY clause in the SQL for an adjacent post query.
		 *
		 * The dynamic portion of the hook name, `$adjacent`, refers to the type
		 * of adjacency, 'next' or 'previous'.
		 *
		 * @since 2.5.0
		 * @since 4.4.0 Added the `$post` parameter.
		 *
		 * @param string $order_by The `ORDER BY` clause in the SQL.
		 * @param WP_Post $post    WP_Post object.
		 */
		$sort  = apply_filters( "get_{$location}_post_sort", "ORDER BY p.post_date $order LIMIT 1", $post );

		$query = "SELECT p.ID FROM $wpdb->posts AS p $join $where $sort";
		$query_key = 'boundary_post_' . md5( $query );
		$result = wp_cache_get( $query_key, 'counts' );
		if ( false !== $result ) {
			if ( $result )
				$result = get_post( $result );
			return $result;
		}

		$result = $wpdb->get_var( $query );
		if ( null === $result )
			$result = '';

		wp_cache_set( $query_key, $result, 'counts' );

		if ( $result )
			$result = get_post( $result );

		return $result;
	}
}
