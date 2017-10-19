<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.1.1
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
	// Get the attributes
	$attributes = pixelgrade_get_element_attributes( $attributes, $location );

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
				$full_attributes[] = $name;
			} else {
				$full_attributes[] = $name . '="' . esc_attr( $value ) . '"';
			}
		}
	}

	// Display the attributes
	if ( ! empty( $full_attributes ) ) {
		echo join( ' ', $full_attributes );
	}
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
} #function

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

	// Generate a string attributes array, like array( 'rel="test"', 'href="boom"' )
	$full_attributes = array();
	foreach ( $body_attributes as $name => $value ) {
		// We really don't want numeric keys as attributes names
		if ( ! empty( $name ) && ! is_numeric( $name ) ) {
			//if we get an array as value we will add them comma separated
			if ( ! empty( $value ) && is_array( $value ) ) {
				$value = join( ', ', $value );
			}

			//if we receive an empty array entry (but with a key) we will treat it like an attribute without value (i.e. itemprop)
			if ( empty( $value ) ) {
				$full_attributes[] = $name;
			} else {
				$full_attributes[] = $name . '="' . esc_attr( $value ) . '"';
			}
		}
	}

	// Display the attributes
	if ( ! empty( $full_attributes ) ) {
		echo join( ' ', $full_attributes );
	}
} #function

/**
 * Display the classes for a element.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
 * @param string $prefix Optional. Prefix to prepend to all of the provided classes
 * @param string $suffix Optional. Suffix to append to all of the provided classes
 */
function pixelgrade_css_class( $class = '', $location = '', $prefix = '', $suffix = '' ) {
	// Separates classes with a single space, collates classes for element
	echo 'class="' . join( ' ', pixelgrade_get_css_class( $class, $location ) ) . '"';
}

/**
 * Retrieve the classes for a element as an array.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
 * @param string $prefix Optional. Prefix to prepend to all of the provided classes
 * @param string $suffix Optional. Suffix to append to all of the provided classes
 *
 * @return array Array of classes.
 */
function pixelgrade_get_css_class( $class = '', $location = '', $prefix = '', $suffix = '' ) {
	$classes = array();

	if ( ! empty( $class ) ) {
		if ( ! is_array( $class ) ) {
			$class = preg_split( '#\s+#', $class );
		}

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
	 */
	$classes = apply_filters( 'pixelgrade_css_class', $classes, $class, $location, $prefix, $suffix );

	return array_unique( $classes );
} #function

/**
 * Display the classes for the blog wrapper.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
 */
function pixelgrade_blog_class( $class = '', $location = '' ) {
	// Separates classes with a single space, collates classes
	echo 'class="' . join( ' ', pixelgrade_get_blog_class( $class, $location ) ) . '"';
}

if ( ! function_exists( 'pixelgrade_get_blog_class' ) ) {
	/**
	 * Retrieve the classes for the blog wrapper as an array.
	 *
	 * @since fargo 1.0.0
	 *
	 * @param string|array $class Optional. One or more classes to add to the class list.
	 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
	 *
	 * @return array Array of classes.
	 */
	function pixelgrade_get_blog_class( $class = '', $location = '' ) {

		$classes = array();

		$classes[] = 'c-gallery c-gallery--blog';
		// layout
		$grid_layout       = pixelgrade_option( 'blog_grid_layout', 'regular' );
		$grid_layout_class = 'c-gallery--' . $grid_layout;

		if ( in_array( $grid_layout, array( 'packed', 'regular', 'mosaic' ) ) ) {
			$grid_layout_class .= ' c-gallery--cropped';
		}

		if ( 'mosaic' === $grid_layout ) {
			$grid_layout_class .= ' c-gallery--regular';
		}

		$classes[] = $grid_layout_class;

		// items per row
		$columns_at_desk  = intval( pixelgrade_option( 'blog_items_per_row', 3 ) );
		$columns_at_lap   = $columns_at_desk >= 5 ? $columns_at_desk - 1 : $columns_at_desk;
		$columns_at_small = $columns_at_lap >= 4 ? $columns_at_lap - 1 : $columns_at_lap;
		$columns_class    = 'o-grid--' . $columns_at_desk . 'col-@desk o-grid--' . $columns_at_lap . 'col-@lap o-grid--' . $columns_at_small . 'col-@small';

		// title position
		$title_position       = pixelgrade_option( 'blog_items_title_position', 'regular' );
		$title_position_class = 'c-gallery--title-' . $title_position;

		if ( $title_position == 'overlay' ) {
			$title_alignment_class = 'c-gallery--title-' . pixelgrade_option( 'blog_items_title_alignment_overlay', 'bottom-left' );
		} else {
			$title_alignment_class = 'c-gallery--title-' . pixelgrade_option( 'blog_items_title_alignment_nearby', 'left' );
		}

		$classes[] = $title_position_class;
		$classes[] = $title_alignment_class;
		$classes[] = $columns_class;

		if ( ! empty( $class ) ) {
			if ( ! is_array( $class ) ) {
				$class = preg_split( '#\s+#', $class );
			}
			$classes = array_merge( $classes, $class );
		} else {
			// Ensure that we always coerce class to being an array.
			$class = array();
		}

		$classes = array_map( 'esc_attr', $classes );

		/**
		 * Filters the list of CSS classes for the blog wrapper.
		 *
		 * @param array $classes An array of header classes.
		 * @param array $class An array of additional classes added to the blog wrapper.
		 * @param string|array $location The place (template) where the classes are displayed.
		 */
		$classes = apply_filters( 'pixelgrade_blog_class', $classes, $class, $location );

		return array_unique( $classes );
	} #function
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

		// $post is the thumbnail attachment
		$post = get_post( $post_id );

		$jetpack_show_single_featured_image = get_option( 'jetpack_content_featured_images_post', true );

		// Bail if no post or the image is hidden from Jetpack's content options
		if ( empty( $post ) || empty( $jetpack_show_single_featured_image ) ) {
			return 'none';
		}

		return pixelgrade_get_image_aspect_ratio_type( get_post_thumbnail_id( $post ), 'none' );
	} #function
}

if ( ! function_exists( 'pixelgrade_get_image_aspect_ratio_type' ) ) {
	/**
	 * Retrieve the aspect ratio type of an image.
	 *
	 * @param int|WP_Post $image The image attachment ID or the attachment object.
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
		if ( ! $image = get_post( $image ) ) {
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
	} #function
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
	} #function
}

function pixelgrade_the_taxonomy_dropdown( $taxonomy, $current_term = null ) {
	echo pixelgrade_get_the_taxonomy_dropdown( $taxonomy, $current_term );
}

if ( ! function_exists( 'pixelgrade_get_the_taxonomy_dropdown' ) ) {

	/**
	 * @param $taxonomy
	 * @param null $current_term
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
		$output .= '<option value="' . esc_attr( $archive_link ) . '" ' . esc_attr( $selected_attr ) . '>' . esc_html__( 'Everything', 'components_txtd' ) . '</option>';

		foreach ( $terms as $term ) {
			$selected_attr = '';
			if ( ! empty( $selected ) && $selected == $term->slug ) {
				$selected_attr = 'selected';
			}
			$output .= '<option value="' . esc_attr( get_term_link( intval( $term->term_id ), $taxonomy ) ) . '" ' . esc_attr( $selected_attr ) . '>' . esc_html( $term->name ) . '</option>';
		}
		$output .= '</select>';

		// Allow others to have a go at it
		return apply_filters( 'pixelgrade_get_the_taxonomy_dropdown', $output, $taxonomy, $selected );
	} #function
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
	function pixelgrade_get_rendered_content( $more_link_text = null, $strip_teaser = false) {
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

if ( ! function_exists( 'pixelgrade_get_post_meta' ) ) {
	/**
	 * Get all the needed meta for a post.
	 *
	 * @return array
	 */
	function pixelgrade_get_post_meta() {
		// Gather up all the meta we might need to display
		// But first initialize please
		$meta = array(
			'category' => false,
			'tags'     => false,
			'author'   => false,
			'date'     => false,
			'comments' => false,
		);

		// And get the options
		$items_primary_meta   = pixelgrade_option( 'blog_items_primary_meta', 'category' );
		$items_secondary_meta = pixelgrade_option( 'blog_items_secondary_meta', 'date' );

		if ( 'category' == $items_primary_meta || 'category' == $items_secondary_meta ) {
			$category = '';

			if ( is_page() ) {
				// If we are on a page then we only want the main category
				$main_category = pixelgrade_get_main_category_link();
				if ( ! empty( $main_category ) ) {
					$category .= '<span class="screen-reader-text">' . esc_html__( 'Main Category', 'components_txtd' ) . '</span><ul>' . PHP_EOL;
					$category .= '<li>' . $main_category . '</li>' . PHP_EOL;
					$category .= '</ul>' . PHP_EOL;
				}
			} else {
				// On archives we want to show all the categories, not just the main one
				$categories = get_the_terms( get_the_ID(), 'category' );
				if ( ! is_wp_error( $categories ) && ! empty( $categories ) ) {
					$category .= '<span class="screen-reader-text">' . esc_html__( 'Categories', 'components_txtd' ) . '</span><ul class="cats">' . PHP_EOL;
					foreach ( $categories as $this_category ) {
						$category .= '<li><a href="' . esc_url( get_category_link( $this_category ) ) . '" rel="category">' . $this_category->name . '</a></li>' . PHP_EOL;
					};
					$category .= '</ul>' . PHP_EOL;
				}
			}
			$meta['category'] = $category;
		}

		if ( 'tags' == $items_primary_meta || 'tags' == $items_secondary_meta ) {
			$post_tags = get_the_terms( get_the_ID(), 'post_tag' );
			$tags      = '';
			if ( ! is_wp_error( $post_tags ) && ! empty( $post_tags ) ) {
				$tags .= '<span class="screen-reader-text">' . esc_html__( 'Tags', 'components_txtd' ) . '</span><ul class="tags">' . PHP_EOL;
				foreach ( $post_tags as $post_tag ) {
					$tags .= '<li><a href="' . esc_url( get_term_link( $post_tag ) ) . '" rel="tag">' . $post_tag->name . '</a></li>' . PHP_EOL;
				};
				$tags .= '</ul>' . PHP_EOL;
			}
			$meta['tags'] = $tags;
		}

		$meta['author'] = '<span class="byline">' . get_the_author() . '</span>';
		$meta['date']   = '<span class="posted-on">' . get_the_date() . '</span>';

		$comments_number = get_comments_number(); // get_comments_number returns only a numeric value
		if ( comments_open() ) {
			if ( $comments_number == 0 ) {
				$comments = esc_html__( 'No Comments', 'components_txtd' );
			} else {
				$comments = sprintf( _n( '%d Comment', '%d Comments', $comments_number, 'components_txtd' ), $comments_number );
			}
			$meta['comments'] = '<a href="' . esc_url( get_comments_link() ) . '">' . esc_html( $comments ) . '</a>';
		} else {
			$meta['comments'] = '';
		}

		return apply_filters( 'pixelgrade_get_post_meta', $meta );
	} #function
}


/**
 * Displays the navigation to next/previous post, when applicable.
 *
 * @param array $args Optional. See get_the_post_navigation() for available arguments.
 *                    Default empty array.
 */
function pixelgrade_the_post_navigation( $args = array() ) {
	echo pixelgrade_get_the_post_navigation( $args );
}

if ( ! function_exists( 'pixelgrade_get_the_post_navigation' ) ) {
	/**
	 * Retrieves the navigation to next/previous post, when applicable.
	 *
	 * @param array $args {
	 *     Optional. Default post navigation arguments. Default empty array.
	 *
	 * @type string $prev_text Anchor text to display in the previous post link. Default '%title'.
	 * @type string $next_text Anchor text to display in the next post link. Default '%title'.
	 * @type bool $in_same_term Whether link should be in a same taxonomy term. Default false.
	 * @type array|string $excluded_terms Array or comma-separated list of excluded term IDs. Default empty.
	 * @type string $taxonomy Taxonomy, if `$in_same_term` is true. Default 'category'.
	 * @type string $screen_reader_text Screen reader text for nav element. Default 'Post navigation'.
	 * }
	 * @return string Markup for post links.
	 */
	function pixelgrade_get_the_post_navigation( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'prev_text'          => '%title',
			'next_text'          => '%title',
			'in_same_term'       => false,
			'excluded_terms'     => '',
			'taxonomy'           => 'category',
			'screen_reader_text' => esc_html__( 'Post navigation', 'components_txtd' ),
		) );

		$navigation = '';

		$previous = get_previous_post_link(
			'<div class="nav-previous"><span class="nav-links__label  nav-links__label--previous">' . esc_html__( 'Previous article', 'components_txtd' ) . '</span><span class="h3 nav-title  nav-title--previous">%link</span></div>',
			$args['prev_text'],
			$args['in_same_term'],
			$args['excluded_terms'],
			$args['taxonomy']
		);

		$next = get_next_post_link(
			'<div class="nav-next"><span class="nav-links__label  nav-links__label--next">' . esc_html__( 'Next article', 'components_txtd' ) . '</span><span class="h3 nav-title  nav-title--next">%link</span></div>',
			$args['next_text'],
			$args['in_same_term'],
			$args['excluded_terms'],
			$args['taxonomy']
		);

		// Only add markup if there's somewhere to navigate to.
		if ( $previous || $next ) {
			$navigation = _navigation_markup( $previous . $next, 'post-navigation', $args['screen_reader_text'] );
		}

		return $navigation;
	}
}

/**
 * Display the HTML of the author info box
 */
function pixelgrade_the_author_info_box() {
	echo pixelgrade_get_the_author_info_box();
}

if ( ! function_exists( 'pixelgrade_get_the_author_info_box' ) ) {

	/**
	 * Get the HTML of the author info box
	 *
	 * @return string
	 */
	function pixelgrade_get_the_author_info_box() {
		// Get the current post for easy use
		$post = get_post();

		// Bail if no post
		if ( empty( $post ) ) {
			return '';
		}

		// If we aren't on a single post or it's a single post without author, don't continue.
		if ( ! is_single() || ! isset( $post->post_author ) ) {
			return '';
		}

		$options            = get_theme_support( 'jetpack-content-options' );
		$author_bio         = ( ! empty( $options[0]['author-bio'] ) ) ? $options[0]['author-bio'] : null;
		$author_bio_default = ( isset( $options[0]['author-bio-default'] ) && false === $options[0]['author-bio-default'] ) ? '' : 1;

		// If the theme doesn't support 'jetpack-content-options[ 'author-bio' ]', don't continue.
		if ( true !== $author_bio ) {
			return '';
		}

		// If 'jetpack_content_author_bio' is false, don't continue.
		if ( ! get_option( 'jetpack_content_author_bio', $author_bio_default ) ) {
			return '';
		}

		// Get author's biographical information or description
		$user_description = get_the_author_meta( 'user_description', $post->post_author );
		// If an author doesn't have a description, don't display the author info box
		if ( empty( $user_description ) ) {
			return '';
		}

		$author_details = '';

		// Get author's display name
		$display_name = get_the_author_meta( 'display_name', $post->post_author );

		// If display name is not available then use nickname as display name
		if ( empty( $display_name ) ) {
			$display_name = get_the_author_meta( 'nickname', $post->post_author );
		}

		if ( ! empty( $user_description ) ) {
			$author_details .= '<div class="c-author has-description" itemscope itemtype="http://schema.org/Person">';
		} else {
			$author_details .= '<div class="c-author" itemscope itemtype="http://schema.org/Person">';
		}

		// The author avatar
		$author_avatar = get_avatar( get_the_author_meta( 'user_email' ), 100 );
		if ( ! empty( $author_avatar ) ) {
			$author_details .= '<div class="c-author__avatar">' . $author_avatar . '</div>';
		}

		$author_details .= '<div class="c-author__details">';

		if ( ! empty( $display_name ) ) {
			$author_details .= '<span class="c-author__name h3">' . $display_name . '</span>';
		}

		// The author bio
		if ( ! empty( $user_description ) ) {
			$author_details .= '<p class="c-author__description" itemprop="description">' . nl2br( $user_description ) . '</p>';
		}

		$author_details .= '<footer class="c-author__footer  h6">';

		$author_details .= pixelgrade_get_author_bio_links( $post->ID );

		$author_details .= '</footer>';
		$author_details .= '</div><!-- .c-author__details -->';
		$author_details .= '</div><!-- .c-author -->';

		return $author_details;
	} #function
}

if ( ! function_exists( 'pixelgrade_get_author_bio_links' ) ) {
	/**
	 * Return the markup for the author bio links.
	 * These are the links/websites added by one to it's Gravatar profile
	 *
	 * @param int|WP_Post $post_id Optional. Post ID or post object.
	 * @return string The HTML markup of the author bio links list.
	 */
	function pixelgrade_get_author_bio_links( $post_id = null ) {
		$post = get_post( $post_id );
		$markup = '';
		if ( empty( $post ) ) {
			return $markup;
		}

		// Get author's website URL
		$user_website = get_the_author_meta( 'url', $post->post_author );

		// Get link to the author archive page
		$user_posts = get_author_posts_url( get_the_author_meta( 'ID', $post->post_author ) );

		$str = wp_remote_fopen( 'https://www.gravatar.com/' . md5( strtolower( trim( get_the_author_meta( 'user_email' ) ) ) ) . '.php' );
		$profile = unserialize( $str );

		$markup .= '<span class="c-author__links">' . PHP_EOL;

		$markup .= '<a class="c-author__social-link  c-author__website-link" href="' . esc_url( $user_posts ) . '" rel="author" title="' . esc_attr( sprintf( __( 'View all posts by %s', 'components_txtd' ), get_the_author() ) ) . '">' . esc_html__( 'All posts', 'components_txtd' ) . '</a>';

		if ( is_array( $profile ) && ! empty( $profile['entry'][0]['urls'] ) || ! empty( $user_website ) ) {
			foreach ( $profile['entry'][0]['urls'] as $link ) {
				if ( ! empty( $link['value'] ) && ! empty( $link['title'] ) ) {
					$markup .= '<a class="c-author__social-link" href="' . esc_url( $link['value'] ) . '" target="_blank">' . $link['title'] . '</a>' . PHP_EOL;
				}
			}
		} elseif ( ! empty( $user_website ) ) {
			$markup .= '<a class="c-author__social-link" href="' . esc_url( $user_website ) . '" target="_blank">' . esc_html__( 'Website', 'components_txtd' ) . '</a>' . PHP_EOL;
		}
		$markup .= '</span>' . PHP_EOL;

		return $markup;
	} #function
}

/**
 * Returns true if a blog has more than 1 category.
 *
 * @return bool
 */
function pixelgrade_categorized_blog() {
	if ( false === ( $all_the_cool_cats = get_transient( 'pixelgrade_categories' ) ) ) {
		// Create an array of all the categories that are attached to posts.
		$all_the_cool_cats = get_categories( array(
			'fields'     => 'ids',
			'hide_empty' => 1,
			// We only need to know if there is more than one category.
			'number'     => 2,
		) );

		// Count the number of categories that are attached to the posts.
		$all_the_cool_cats = count( $all_the_cool_cats );

		set_transient( 'pixelgrade_categories', $all_the_cool_cats );
	}

	$is_categorized = false;

	if ( $all_the_cool_cats > 1 ) {
		// This blog has more than 1 category so we should return true.
		$is_categorized = true;
	}

	return apply_filters( 'pixelgrade_categorized_blog', $is_categorized );
} #function

/**
 * Flush out the transients used in pixelgrade_categorized_blog.
 */
function pixelgrade_category_transient_flusher() {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	// Like, beat it. Dig?
	delete_transient( 'pixelgrade_categories' );
}
add_action( 'edit_category', 'pixelgrade_category_transient_flusher' );
add_action( 'save_post',     'pixelgrade_category_transient_flusher' );

/**
 * Get the main post category WP_Term object based on our custom logic.
 *
 * @param int $post_ID Optional. Defaults to current post.
 *
 * @return WP_Term|bool
 */
function pixelgrade_get_main_category( $post_ID = null ) {

	// Use the current post ID is none given
	if ( empty( $post_ID ) ) {
		$post_ID = get_the_ID();
	}

	// Obviously pages don't have categories
	if ( 'page' == get_post_type( $post_ID ) ) {
		return false;
	}

	$categories = get_the_category();

	if ( empty( $categories ) ) {
		return false;
	}

	// We need to sort the categories like this: first categories with no parent, and secondly ordered DESC by post count
	// Thus parent categories take precedence and categories with more posts take precedence
	usort( $categories, '_pixelgrade_special_category_order' );

	// The first category should be the one we are after
	// We allow others to filter this (Yoast primary category maybe?)
	return apply_filters( 'pixelgrade_get_main_category', $categories[0], $post_ID );
}

/**
 * Prints an anchor of the main category of a post
 *
 * @param string $before
 * @param string $after
 * @param string $category_class Optional. A CSS class that the category will receive.
 */
function pixelgrade_the_main_category_link( $before = '', $after = '', $category_class = '' ) {
	echo pixelgrade_get_main_category_link( $before, $after, $category_class );

} #function


if ( ! function_exists( 'pixelgrade_get_main_category_link' ) ) {
	/**
	 * Returns an anchor of the main category of a post
	 *
	 * @param string $before
	 * @param string $after
	 * @param string $category_class Optional. A CSS class that the category will receive.
	 *
	 * @return string
	 */
	function pixelgrade_get_main_category_link( $before = '', $after = '', $category_class = '' ) {
		$category = pixelgrade_get_main_category();

		// Bail if we have nothing to work with
		if ( empty( $category ) || is_wp_error( $category ) ) {
			return '';
		}

		$class_markup = '';

		if ( ! empty( $category_class ) ) {
			$class_markup = 'class="' . $category_class . '" ';
		}
		return $before . '<a ' . $class_markup . ' href="' . esc_url( get_category_link( $category->term_id ) ) . '" title="' . esc_attr( $category->name ) . '">' . $category->name . '</a>' . $after;

	} #function
}

if ( ! function_exists( 'pixelgrade_entry_header' ) ) {
	/**
	 * Prints HTML with meta information in the entry header.
	 *
	 * @param int|WP_Post $post_id Optional. Default to current post.
	 */
	function pixelgrade_entry_header( $post_id = null ) {
		// Fallback to the current post if no post ID was given.
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Bail if we still don't have a post ID.
		if ( empty( $post_id ) ) {
			return;
		}

		the_date( '', '<div class="entry-date">', '</div>', true );

	} #function
}

if ( ! function_exists( 'pixelgrade_entry_footer' ) ) {
	/**
	 * Prints HTML with meta information in the entry footer.
	 *
	 * @param int|WP_Post $post_id Optional. Default to current post.
	 */
	function pixelgrade_entry_footer( $post_id = null ) {
		// Fallback to the current post if no post ID was given.
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}

		// Bail if we still don't have a post ID.
		if ( empty( $post_id ) ) {
			return;
		}

		if ( ! is_single( $post_id ) && ! post_password_required( $post_id ) && ( comments_open( $post_id ) || get_comments_number( $post_id ) ) ) {
			echo '<span class="comments-link">';
			/* translators: %s: post title */
			comments_popup_link( sprintf( wp_kses( __( 'Leave a Comment<span class="screen-reader-text"> on %s</span>', 'components_txtd' ), array( 'span' => array( 'class' => array() ) ) ), get_the_title( $post_id ) ) );
			echo '</span>';
		}

		edit_post_link(
			sprintf(
			/* translators: %s: Name of current post */
				esc_html__( 'Edit %s', 'components_txtd' ),
				the_title( '<span class="screen-reader-text">"', '"</span>', false )
			),
			'<div class="edit-link">',
			'</div>',
			$post_id
		);
	} #function
}

if ( ! function_exists( 'pixelgrade_shape_comment' ) ) {
	/**
	 * Template for comments and pingbacks.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 *
	 * @param WP_Comment $comment
	 * @param array $args
	 * @param int $depth
	 *
	 */
	function pixelgrade_shape_comment( $comment, $args, $depth ) {
		$GLOBALS['comment'] = $comment;
		switch ( $comment->comment_type ) :
			case 'pingback' :
			case 'trackback' : ?>
				<li class="post pingback">
				<p><?php esc_html_e( 'Pingback:', 'components_txtd' ); ?><?php comment_author_link(); ?><?php edit_comment_link( esc_html__( '(Edit)', 'components_txtd' ), ' ' ); ?></p>
				<?php
				break;
			default : ?>
			<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
				<article id="div-comment-<?php comment_ID(); ?>" class="comment__wrapper">
					<?php if ( 0 != $args['avatar_size'] ) : ?>
						<div class="comment__avatar"><?php echo get_avatar( $comment, $args['avatar_size'] ); ?></div>
					<?php endif; ?>
					<div class="comment__body">
						<header class="c-meta">
							<div class="comment__author vcard">
								<?php
								/* translators: %s: comment author link */
								printf( __( '%s <span class="says">says:</span>', 'components_txtd' ),
									sprintf( '<b class="fn">%s</b>', get_comment_author_link( $comment ) )
								);
								?>
							</div><!-- .comment-author -->

							<div class="comment__metadata">
								<a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
									<time datetime="<?php comment_time( 'c' ); ?>">
										<?php
										/* translators: 1: comment date, 2: comment time */
										printf( __( '%1$s at %2$s', 'components_txtd' ), get_comment_date( '', $comment ), get_comment_time() );
										?>
									</time>
								</a>
								<?php edit_comment_link( esc_html__( 'Edit', 'components_txtd' ), '<span class="edit-link">', '</span>' ); ?>
							</div><!-- .comment-metadata -->

							<?php if ( '0' == $comment->comment_approved ) : ?>
								<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'components_txtd' ); ?></p>
							<?php endif; ?>
						</header><!-- .comment-meta -->

						<div class="comment__content entry-content">
							<?php comment_text(); ?>
						</div><!-- .comment-content -->

						<?php
						comment_reply_link( array_merge( $args, array(
							'add_below' => 'div-comment',
							'depth'     => $depth,
							'max_depth' => $args['max_depth'],
							'before'    => '<div class="reply">',
							'after'     => '</div>'
						) ) );
						?>
					</div>
				</article><!-- .comment-body -->
				<?php break;
		endswitch;
	} #function
}

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
		$name = (string) $name;
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
		$name = (string) $name;
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
		$name = (string) $name;
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

if ( ! function_exists( 'pixelgrade_comments_template' ) ) {
	/**
	 * Output the comments template
	 *
	 * This is just a wrapper to comments_template() called with the template path determined according to our components logic.
	 */
	function pixelgrade_comments_template() {
		// We need to pass the template path retrieved by our locate function so the component template is accounted for
		// If present in the root of the theme or child theme, `/comments.php` will take precedence.
		comments_template( '/' . pixelgrade_make_relative_path( pixelgrade_locate_component_template( Pixelgrade_Base::COMPONENT_SLUG, 'comments' ) ) );
	}
}

if ( ! function_exists( 'pixelgrade_the_post_custom_css' ) ) {
	/**
	 * Display custom CSS styles set by the custom meta box, per post
	 *
	 * @param string $location A hint regarding where this action was called from
	 */
	function pixelgrade_the_post_custom_css( $location = '' ) {
		// We allow others to prevent us from displaying
		if ( true === apply_filters( 'pixelgrade_display_the_post_custom_css', true, get_the_ID(), $location ) ) {
			$output = '';
			// This metabox is defined in the Pixelgrade_Base_Metaboxes class
			$custom_css = get_post_meta( get_the_ID(), 'custom_css_style', true );
			if ( ! empty( $custom_css ) ) {
				$output .= '<div class="custom-css" data-css="' . esc_attr( $custom_css ) . '"></div>' . PHP_EOL;
			}

			// Allow others to modify this
			echo apply_filters( 'pixelgrade_the_post_custom_css', $output, get_the_ID(), $location );
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
	 * @see Pixelgrade_Custom_Loops_For_Pages
	 */
	function pixelgrade_do_fake_loop() {
		// The Loop - actually a fake loop
		while ( have_posts() ):
			the_post();
			/*
			 * Do nothing here as we will do it via hooks
			 * @see Pixelgrade_Custom_Loops_For_Pages
			 */
		endwhile;
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

if ( ! function_exists( 'pixelgrade_posts_container_id' ) ) {
	/**
	 * Display the id attribute for the posts-container
	 *
	 * @param array $location
	 */
	function pixelgrade_posts_container_id( $location = array() ) {
		$posts_container_id =  pixelgrade_get_posts_container_id( $location );
		if ( ! empty( $posts_container_id ) ) {
			echo 'id="' . esc_attr( $posts_container_id ) . '"';
		}
	}
}

if ( ! function_exists( 'pixelgrade_get_posts_container_id' ) ) {
	/**
	 * Get the markup id for the posts-container
	 *
	 * This way we keep things consistent across the theme and stuff like Infinite Scroll can rely on it.
	 *
	 * @param array $location
	 *
	 * @return string
	 */
	function pixelgrade_get_posts_container_id( $location = array() ) {
		return apply_filters( 'pixelgrade_posts_container_id', 'posts-container', $location );
	}
}
