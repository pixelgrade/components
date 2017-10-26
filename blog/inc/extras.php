<?php
/**
 * Custom functions that act independently of the component templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Blog
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Adds HTML with meta information for the categories at the end of the provided content.
 * It can be used directly on `the_content` filter
 *
 * @param string $content
 *
 * @return string
 */
function pixelgrade_add_cats_list( $content ) {

	$cats_content = '';

	// Hide category text for pages.
	$add = ( 'post' == get_post_type() && is_singular( 'post' ) && is_main_query() );
	if ( apply_filters( 'pixelgrade_add_categories_to_content', $add ) ) {
		// This is list can be filtered via 'the_category_list' and the main category be removed on single posts
		$categories_list = get_the_category_list( ' ' );

		if ( ! empty( $categories_list ) ) {
			$cats_content .= '<div class="cats"><span class="cats__title h6">' . esc_html__( 'Categories', 'components_txtd' ) . sprintf( '</span>' . esc_html__( '%1$s', 'components_txtd' ), $categories_list ) . '</div>'; // WPCS: XSS OK.
		}
	}

	return $content . $cats_content;
}
// add this filter with a priority smaller than tags - it has 18
add_filter( 'the_content', 'pixelgrade_add_cats_list', 17 );

/**
 * Adds HTML with meta information for the tags at the end of the provided content.
 * It can be used directly on `the_content` filter
 *
 * @param string $content
 *
 * @return string
 */
function pixelgrade_add_tags_list( $content ) {

	$tags_content = '';

	// Hide tag text for pages.
	$add = ( 'post' == get_post_type() && is_singular( 'post' ) && is_main_query() );
	if ( apply_filters( 'pixelgrade_add_tags_to_content', $add ) ) {
		$tags_list = get_the_tag_list();

		if ( ! empty( $tags_list ) ) {
			$tags_content .= '<div class="tags"><div class="tags__title h6">' . esc_html__( 'Tags', 'components_txtd' ) . sprintf( '</div>' . esc_html__( '%1$s', 'components_txtd' ) . '</div>', $tags_list ); // WPCS: XSS OK.
		}
	}

	return $content . $tags_content;
}
// add this filter with a priority smaller than sharedaddy - it has 19
add_filter( 'the_content', 'pixelgrade_add_tags_list', 18 );

/**
 * Removes the main category from the category list.
 *
 * @param array $categories
 * @param int $post_id
 *
 * @return array
 */
function pixelgrade_remove_main_category_from_list( $categories, $post_id ) {
	if ( is_singular( 'post' ) ) {
		$main_category = pixelgrade_get_main_category( $post_id );

		foreach ( $categories as $key => $category ) {
			if ( $main_category->term_id == $category->term_id ) {
				unset( $categories[ $key ] );
			}
		}
	}

	return $categories;
}
//add_filter( 'the_category_list', 'pixelgrade_remove_main_category_from_list', 10, 2 );

/**
 * Compares two category objects by post count
 *
 * This is used for ordering categories.
 *
 * @param WP_Term $a
 * @param WP_Term $b
 *
 * @return int
 */
function _pixelgrade_special_category_order( $a, $b ) {
	if ( $a->parent == $b->parent ) {
		if ( $a->count == $b->count ) {
			return 0;
		}

		return ( $a->count > $b->count ) ? -1 : 1;
	}

	return ( $a->parent < $b->parent ) ? -1 : 1;
}

if ( ! function_exists( 'pixelgrade_search_form' ) ) :
	/**
	 * Custom search form
	 *
	 * @param string $form
	 *
	 * @return string
	 */
	function pixelgrade_search_form( $form ) {
		$form = '<form role="search" method="get" class="search-form" action="' . esc_attr( home_url( '/' ) ) . '" >
		<label class="screen-reader-text">' . esc_html__( 'Search for:', 'components_txtd' ) . '</label>
		<input type="text" placeholder="' . esc_attr__( 'Search here', 'components_txtd' ) . '" value="' . esc_attr( get_search_query() ) . '" name="s" class="search-field" />
		<button type="submit" class="search-submit"><span>'. esc_html__( 'Search', 'components_txtd' ) .'</span></button>
		</form>';

		return $form;
	}
endif;
add_filter( 'get_search_form', 'pixelgrade_search_form', 100 );

/**
 * Add the blog categories dropdown after the hero content.
 *
 * @param string $content
 */
function pixelgrade_blog_hero_the_category_dropdown( $content ) {
	if ( is_home() ) { ?>

		<div class="category-dropdown has-inputs-inverted">
			<?php pixelgrade_the_taxonomy_dropdown( 'category' ); ?>
		</div><!-- .category-dropdown -->

	<?php }
}

add_action( 'pixelgrade_hero_after_the_description', 'pixelgrade_blog_hero_the_category_dropdown', 20, 1 );

if ( ! function_exists( 'pixelgrade_is_page_for_projects' ) ) {

	/**
	 * Determine if we are displaying the page_for_projects page
	 *
	 * @param int|null $page_ID
	 *
	 * @return bool
	 */
	function pixelgrade_is_page_for_projects( $page_ID = null ) {
		global $wp_query;

		if ( ! isset( $wp_query ) ) {
			_doing_it_wrong( __FUNCTION__, esc_html__( 'Conditional query tags do not work before the query is run. Before then, they always return false.', 'components_txtd' ), '3.1.0' );

			return false;
		}

		if ( empty( $page_ID ) ) {
			// Get the current page ID
			$page_ID = $wp_query->get( 'page_id' );
			if ( empty( $page_ID ) ) {
				$page_ID = $wp_query->queried_object_id;
			}
		}

		// Bail if we don't have a page ID
		if ( empty( $page_ID ) ) {
			return false;
		}

		$page_for_projects = get_option( 'page_for_projects' );
		if ( empty( $page_for_projects ) ) {
			return false;
		}

		if ( $page_ID == $page_for_projects ) {
			return true;
		}

		return false;
	}
}
