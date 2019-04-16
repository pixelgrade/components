<?php
/**
 * Custom template tags for this component.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'is_woo_archive' ) ) {
	function is_woo_archive() {
		return ( function_exists( 'is_shop' ) && is_shop() )
			|| ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() )
			|| is_post_type_archive( 'product' );
	}
}

/**
 * Display the classes for the blog wrapper.
 *
 * @param string|array $class Optional. One or more classes to add to the class list.
 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
 */
function pixelgrade_woocommerce_grid_class( $class = '', $location = '' ) {
	// Separates classes with a single space, collates classes
	echo 'class="' . join( ' ', pixelgrade_get_woocommerce_grid_class( $class, $location ) ) . '"'; // @codingStandardsIgnoreLine
}

if ( ! function_exists( 'pixelgrade_get_woocommerce_grid_class' ) ) {
	/**
	 * Retrieve the classes for the blog wrapper as an array.
	 *
	 * @param string|array $class Optional. One or more classes to add to the class list.
	 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
	 *
	 * @return array Array of classes.
	 */
	function pixelgrade_get_woocommerce_grid_class( $class = '', $location = '' ) {

		$classes = array();

		/*
		 * General classes
		 */
		$classes[] = 'c-gallery';
		$classes[] = 'c-gallery--woocommerce';

		/*
		 * Options dependent classes
		 */
		$classes = array_merge( $classes, pixelgrade_get_woocommerce_grid_layout_class( $location ) );
		$classes = array_merge( $classes, pixelgrade_get_woocommerce_grid_column_class( $location ) );
		$classes = array_merge( $classes, pixelgrade_get_woocommerce_grid_alignment_class( $location ) );

		if ( ! empty( $class ) ) {
			$class   = Pixelgrade_Value::maybeSplitByWhitespace( $class );
			$classes = array_merge( $classes, $class );
		} else {
			// Ensure that we always coerce class to being an array.
			$class = array();
		}

		$classes = array_map( 'esc_attr', $classes );

		/**
		 * Filters the list of CSS classes for the woocommerce wrapper.
		 *
		 * @param array $classes An array of header classes.
		 * @param array $class An array of additional classes added to the woocommerce wrapper.
		 * @param string|array $location The place (template) where the classes are displayed.
		 */
		$classes = apply_filters( 'pixelgrade_woocommerce_grid_class', $classes, $class, $location );

		return array_unique( $classes );
	} // function
}

if ( ! function_exists( 'pixelgrade_get_woocommerce_grid_layout_class' ) ) {
	/**
	 * Retrieve the woocommerce wrapper grid layout classes.
	 *
	 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
	 *
	 * @return array Array of classes.
	 */
	function pixelgrade_get_woocommerce_grid_layout_class( $location = '' ) {
		$grid_layout         = pixelgrade_option( 'woocommerce_grid_layout', 'regular' );
		$grid_layout_classes = array( 'c-gallery--' . $grid_layout );

		// For certain kind of layouts, we need to add extra classes
		if ( in_array( $grid_layout, array( 'packed', 'regular', 'mosaic' ) ) ) {
			$grid_layout_classes[] = 'c-gallery--cropped';
		}
		if ( 'mosaic' === $grid_layout ) {
			$grid_layout_classes[] = 'c-gallery--regular';
		}

		return $grid_layout_classes;
	}
}

if ( ! function_exists( 'pixelgrade_get_woocommerce_grid_column_class' ) ) {
	/**
	 * Retrieve the woocommerce wrapper grid column classes.
	 *
	 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
	 *
	 * @return array Array of classes.
	 */
	function pixelgrade_get_woocommerce_grid_column_class( $location = '' ) {
		// Items per row
		$columns_at_desk  = intval( pixelgrade_option( 'woocommerce_items_per_row', 3 ) );
		$columns_at_lap   = $columns_at_desk >= 5 ? $columns_at_desk - 1 : $columns_at_desk;
		$columns_at_small = $columns_at_lap >= 4 ? $columns_at_lap - 1 : $columns_at_lap;

		$column_classes   = array();
		$column_classes[] = 'o-grid';
		$column_classes[] = 'o-grid--' . $columns_at_desk . 'col-@desk';
		$column_classes[] = 'o-grid--' . $columns_at_lap . 'col-@lap';
		$column_classes[] = 'o-grid--' . $columns_at_small . 'col-@small';

		return $column_classes;
	}
}

if ( ! function_exists( 'pixelgrade_get_woocommerce_grid_alignment_class' ) ) {
	/**
	 * Retrieve the woocommerce wrapper grid alignment classes.
	 *
	 * @param string|array $location Optional. The place (template) where the classes are displayed. This is a hint for filters.
	 *
	 * @return array Array of classes.
	 */
	function pixelgrade_get_woocommerce_grid_alignment_class( $location = '' ) {
		// Title position
		$title_position = pixelgrade_option( 'woocommerce_items_title_position', 'regular' );
		$title_classes  = array( 'c-gallery--title-' . $title_position );

		if ( $title_position == 'overlay' ) {
			$title_classes[] = 'c-gallery--title-' . pixelgrade_option( 'woocommerce_items_title_alignment_overlay', 'bottom-left' );
		} else {
			$title_classes[] = 'c-gallery--title-' . pixelgrade_option( 'woocommerce_items_title_alignment_nearby', 'left' );
		}

		return $title_classes;
	}
}

function pixelgrade_woocommerce_grid_item_class( $class = '', $location = '' ) {
	echo 'class="' . join( ' ', pixelgrade_get_woocommerce_grid_item_class( $class, $location ) ) . '"'; // @codingStandardsIgnoreLine
}

if ( ! function_exists( 'pixelgrade_get_woocommerce_grid_item_class' ) ) {

	function pixelgrade_get_woocommerce_grid_item_class( $class = '', $location = '' ) {
		$classes   = array();
		$classes[] = 'c-gallery__item';

		if ( has_post_thumbnail() ) {
			$classes[] = 'c-gallery__item--' . pixelgrade_get_image_aspect_ratio_type( get_post_thumbnail_id(), 'landscape' );
		} else {
			$classes[] = 'c-gallery__item--no-image';
		}

		return array_unique( $classes );
	}
}

if ( ! function_exists( 'woocommerce_display_categories' ) ) {

	function woocommerce_display_categories() {

		global $wp_query;

		// get all product categories
		$terms = get_terms( array(
			'taxonomy' => 'product_cat',
		) );

		// if there is a category queried cache it
		$current_term =	get_queried_object();

		if ( ! empty( $terms ) ) {
			// create a link which should link to the shop
			$all_link = get_post_type_archive_link( 'product' );

			echo '<ul class="woocommerce-categories">';
			// display the shop link first if there is one
			if ( ! empty( $all_link ) ) {
				// also if the current_term doesn't have a term_id it means we are quering the shop and the "all categories" should be active
				echo '<li><a href="' . esc_url( $all_link ) . '" ' . ( ( ! isset( $current_term->term_id ) ) ? ' class="active"' : ' class="inactive"' ) . '>' . esc_html__( 'All Products', '__components_txtd' ) . '</a></li>';
			}

			// display a link for each product category
			foreach ($terms as $key => $term ) {
				$link  = get_term_link( $term, 'product_cat' );
				if ( ! is_wp_error( $link ) ) {

					if ( ! empty( $current_term->name ) && $current_term->name === $term->name ) {
						echo '<li><span class="active">' . esc_html( $term->name ) . '</span></li>';
					} else {
						echo '<li><a href="' . esc_url( $link ) . '">' . esc_html( $term->name ) . '</a></li>';
					}
				}
			}
			echo '</ul>';
		}
	}
}
