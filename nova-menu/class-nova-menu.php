<?php
/**
 * This is the main class of our Nova Menu component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Nova-Menu
 * @version     1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Pixelgrade_Nova_Menu {
	const MENU_ITEM_POST_TYPE = 'nova_menu_item';
	const MENU_ITEM_LABEL_TAX = 'nova_menu_item_label';
	const MENU_SECTION_TAX = 'nova_menu';

	public $component = 'nova-menu';
	public $_version  = '1.0.2';
	public $_assets_version = '1.0.1';

	protected $default_menu_item_loop_markup = array(
		'menu_tag'               => 'section',
		'menu_class'             => 'menu-list__section',
		'menu_header_tag'        => 'header',
		'menu_header_class'      => 'menu-group__header underlined',
		'menu_title_tag'         => 'h4',
		'menu_title_class'       => 'menu-group__title',
		'menu_description_tag'   => 'div',
		'menu_description_class' => 'menu-group__description',
	);

	private static $_instance = null;

	public function __construct() {
		// Register our actions and filters
		$this->register_hooks();

		$this->menu_item_loop_markup = array();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		add_action( 'after_setup_theme', array( $this, 'activate_jetpack_nova_menu_items' ) );
		add_action( 'after_setup_theme', array( $this, 'add_shortcodes' ), 20 );

		//Initialize the Jetpack Nova_Restaurant class with our settings for markup
		add_action( 'wp_head', array( $this, 'nova_restaurant_init' ) );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_nova_menu_registered_hooks' );
	}

	public function nova_restaurant_init() {
		// bail if we are on the WP admin or if for some reasone the Nova Menu is not supported
		if ( is_admin() || ! $this->site_supports_nova() ) {
			return;
		}

		if ( class_exists( 'Nova_Restaurant' ) ) {
			Nova_Restaurant::init( $this->menu_item_loop_markup );
		}
	}

	public function activate_jetpack_nova_menu_items() {
		// Add theme support for Food Menu
		add_theme_support( 'nova_menu_item' );
	}

	/**
	 * Setup the shortcodes we are going to use to output parts or the entire menu
	 */
	public function add_shortcodes() {
		// bail if we are on the WP admin or if for some reason the Nova Menu is not supported
		if ( is_admin() || ! $this->site_supports_nova() ) {
			return;
		}

		$this->menu_item_loop_markup = apply_filters( 'pixelgrade_nova_menu_shortcode_menu_item_loop_markup', $this->default_menu_item_loop_markup );

		// Register [jetpack_nova_menu] always and
		add_shortcode( 'jetpack_nova_menu', array( $this, 'nova_menu_shortcode' ) );

		// register [nova_menu] if [nova_menu] isn't already set
		if ( ! shortcode_exists( 'nova_menu' ) ) {
			add_shortcode( 'nova_menu', array( $this, 'nova_menu_shortcode' ) );
		}

		// register [restaurant_menu] if [restaurant_menu] isn't already set
		if ( ! shortcode_exists( 'restaurant_menu' ) ) {
			add_shortcode( 'restaurant_menu', array( $this, 'nova_menu_shortcode' ) );
		}
	}

	/**
	 * Is the Custom Post Type available?
	 */
	function site_supports_nova() {
		// If we're on WordPress.com, and it has the menu site vertical.
		if ( function_exists( 'site_vertical' ) && 'nova_menu' == site_vertical() )
			return true;

		// Else, if the current theme requests it.
		if ( current_theme_supports( self::MENU_ITEM_POST_TYPE ) )
			return true;

		// Otherwise, say no unless something wants to filter us to say yes.
		/**
		 * Allow something else to hook in and enable this CPT.
		 *
		 * @module custom-content-types
		 *
		 * @since 2.6.0
		 *
		 * @param bool false Whether or not to enable this CPT.
		 * @param string $var The slug for this CPT.
		 */
		return (bool) apply_filters( 'jetpack_enable_cpt', false, self::MENU_ITEM_POST_TYPE );
	}

	/**
	 * Our [nova_menu] shortcode.
	 * Prints Menu data styled to look good on *any* theme.
	 *
	 * @return string
	 */
	public function nova_menu_shortcode( $atts ) {
		// Default attributes
		$atts = shortcode_atts( array(
			'display_sections'   => false,
			'display_labels'    => true,
			'display_content' => true, // this can be either false, true for the_excerpt() or `full` for the_content()
			'link_items' => false,
			'featured_label' => 'featured', // we are going to use this label as a marker for highlighting certain menu items
			'style' => 'regular', // this can be `dotted` if that is what one fancies
			'include_section'    => false,
			'include_label'     => false,
			'showposts'       => -1,
			'order'           => 'asc',
			'orderby'         => 'date',
		), $atts, 'nova_menu' );

		// A little sanitization
		if ( $atts['display_sections'] && true != filter_var( $atts['display_sections'], FILTER_VALIDATE_BOOLEAN ) ) {
			$atts['display_sections'] = false;
		}

		if ( $atts['display_labels'] && true != filter_var( $atts['display_labels'], FILTER_VALIDATE_BOOLEAN ) ) {
			$atts['display_labels'] = false;
		}

		if ( $atts['display_content'] && true != filter_var( $atts['display_content'], FILTER_VALIDATE_BOOLEAN ) && 'full' != $atts['display_content'] ) {
			$atts['display_content'] = false;
		}

		if ( $atts['link_items'] && true != filter_var( $atts['link_items'], FILTER_VALIDATE_BOOLEAN ) ) {
			$atts['link_items'] = false;
		}

		if ( ! empty( $atts['featured_label'] ) ) {
			$atts['featured_label'] = explode( ',', str_replace( ' ', '', $atts['featured_label'] ) );
		} else {
			$atts['featured_label'] = false;
		}

		if ( $atts['style'] ) {
			$atts['style'] = trim( $atts['style'] );
		} else {
			$atts['style'] = 'regular';
		}

		if ( ! empty( $atts['include_section'] ) ) {
			$atts['include_section'] = explode( ',', str_replace( ' ', '', $atts['include_section'] ) );
		} else {
			$atts['include_section'] = false;
		}

		if ( ! empty( $atts['include_label'] ) ) {
			$atts['include_label'] = explode( ',', str_replace( ' ', '', $atts['include_label'] ) );
		} else {
			$atts['include_label'] = false;
		}

		$atts['showposts'] = intval( $atts['showposts'] );


		if ( $atts['order'] ) {
			$atts['order'] = urldecode( $atts['order'] );
			$atts['order'] = strtoupper( $atts['order'] );
			if ( 'DESC' != $atts['order'] ) {
				$atts['order'] = 'ASC';
			}
		}

		if ( $atts['orderby'] ) {
			$atts['orderby'] = urldecode( $atts['orderby'] );
			$atts['orderby'] = strtolower( $atts['orderby'] );
			$allowed_keys = array( 'date', 'title', 'rand' );

			$parsed = array();
			foreach ( explode( ',', $atts['orderby'] ) as $menu_item_index_number => $orderby ) {
				if ( ! in_array( $orderby, $allowed_keys ) ) {
					continue;
				}
				$parsed[] = $orderby;
			}

			if ( empty( $parsed ) ) {
				unset( $atts['orderby'] );
			} else {
				$atts['orderby'] = implode( ' ', $parsed );
			}
		}

		// If we don't need to display menu section titles then prevent them
		if ( false == $atts['display_sections'] ) {
			add_filter( 'jetpack_nova_menu_item_loop_open_element' , array( $this, 'menu_item_open_hidden' ), 10, 4 );
			add_filter( 'jetpack_nova_menu_item_loop_close_element' , array( $this, 'menu_item_close_hidden' ), 10, 4 );
		} else {
			// make sure that we wrap each section items in `<div class="menu-list__items">`
			add_filter( 'jetpack_nova_menu_item_loop_close_element' , array( $this, 'menu_item_close_wrap_section_items' ), 10, 4 );
		}

		// We need to make sure that we have the proper actions for each shortcode
		// Jetpack's Nova_Restaurant() class runs only once, so we have the action only for the first shortcode
		$instance = Nova_Restaurant::init( $this->menu_item_loop_markup );
		add_action( 'loop_start', array( $instance, 'start_menu_item_loop' ) );

		$output = $this->nova_menu_shortcode_html( $atts );

		// Remove the filters in case we've added them
		if ( false == $atts['display_sections'] ) {
			remove_filter( 'jetpack_nova_menu_item_loop_open_element' , array( $this, 'menu_item_open_hidden' ), 10 );
			remove_filter( 'jetpack_nova_menu_item_loop_close_element' , array( $this, 'menu_item_close_hidden' ), 10 );
		} else {
			remove_filter( 'jetpack_nova_menu_item_loop_close_element' , array( $this, 'menu_item_close_wrap_section_items' ), 10 );
		}

		return $output;
	}

	/**
	 * Filter a menu item's element opening tag to hide the section details.
	 *
	 * @param string       $tag    Menu item's element closing tag.
	 * @param string       $field  Menu Item Markup settings field.
	 * @param array        $markup Array of markup elements for the menu item.
	 * @param false|object $term   Taxonomy term for current menu item.
	 *
	 * @return string
	 */
	public function menu_item_open_hidden( $tag, $field, $markup, $term ) {
		// We don't want the <section> or <header> wrap
		// We just keep the minimum markup to be able to hide the section title
		$menu_tag = $field . '_tag';
		if ( in_array( $menu_tag, array( 'menu_tag', 'menu_header_tag' ) ) ) {
			return '';
		}

		return '<li class="screen-reader-text">' . PHP_EOL;
	}

	/**
	 * Filter a menu item's element closing tag to hide the section details.
	 *
	 * @param string       $tag    Menu item's element closing tag.
	 * @param string       $field  Menu Item Markup settings field.
	 * @param array        $markup Array of markup elements for the menu item.
	 * @param false|object $term   Taxonomy term for current menu item.
	 *
	 * @return string
	 */
	public function menu_item_close_hidden( $tag, $field, $markup, $term ) {
		// We don't want the <section> or <header> wrap
		// We just keep the minimum markup to be able to hide the section title
		$menu_tag = $field . '_tag';
		if ( in_array( $menu_tag, array( 'menu_tag', 'menu_header_tag' ) ) ) {
			return '';
		}

		return '</li>';
	}

	/**
	 * Filter a menu item's element closing tag to wrap the section's items.
	 *
	 * @param string       $tag    Menu item's element closing tag.
	 * @param string       $field  Menu Item Markup settings field.
	 * @param array        $markup Array of markup elements for the menu item.
	 * @param false|object $term   Taxonomy term for current menu item.
	 *
	 * @return string
	 */
	public function menu_item_close_wrap_section_items( $tag, $field, $markup, $term ) {
		// If this is the section's </header> open the wrap tag after it
		$menu_tag = $field . '_tag';
		if ( 'menu_header_tag' == $menu_tag ) {
			$tag .= '<ul class="menu-list__items">';
		} elseif ( 'menu_tag' == $menu_tag ) {
			// If this is the </section> close the wrap tag
			$tag = '</ul><!-- .menu-list__items -->' . $tag;
		}

		return $tag;
	}

	/**
	 * Query to retrieve entries from the Portfolio post_type.
	 *
	 * @return object
	 */
	public function nova_menu_query( $atts ) {
		// Default query arguments
		$default = array(
			'order'          => $atts['order'],
			'orderby'        => $atts['orderby'],
			'posts_per_page' => $atts['showposts'],
		);

		$args = wp_parse_args( $atts, $default );
		$args['post_type'] = self::MENU_ITEM_POST_TYPE; // Force this post type

		if ( false !== $atts['include_section'] || false !== $atts['include_label'] ) {
			$args['tax_query'] = array();
		}

		// If 'include_section' has been set use it on the main query
		if ( false !== $atts['include_section'] ) {
			array_push( $args['tax_query'], array(
				'taxonomy' => self::MENU_SECTION_TAX,
				'field'    => 'slug',
				'terms'    => $atts['include_section'],
			) );
		}

		// If 'include_label' has been set use it on the main query
		if ( false !== $atts['include_label'] ) {
			array_push( $args['tax_query'], array(
				'taxonomy' => self::MENU_ITEM_LABEL_TAX,
				'field'    => 'slug',
				'terms'    => $atts['include_label'],
			) );
		}

		if ( false !== $atts['include_section'] && false !== $atts['include_label'] ) {
			$args['tax_query']['relation'] = 'AND';
		}

		// Run the query and return
		$query = new WP_Query( $args );
		return $query;
	}

	/**
	 * The Nova Menu shortcode loop.
	 *
	 * @param array $atts
	 * @return string
	 */
	public function nova_menu_shortcode_html( $atts ) {

		$query = $this->nova_menu_query( $atts );
		$menu_item_index_number = 0;

		ob_start();

		// If we have posts, create the html
		// with nova menu markup
		if ( $query->have_posts() ) { ?>

			<div class="jetpack-nova-menu-shortcode menu-list menu-list__<?php echo esc_attr( $atts['style'] ); ?>">
				<?php // Since we are not using sections, we need to wrap everything
				if ( false == $atts['display_sections'] ) : ?>
				<ul class="menu-list__items">
				<?php endif; ?>

				<?php  // open .jetpack-nova-menu-shortcode

				// Construct the loop...
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id = get_the_ID();
					$title = trim( get_the_title() );

					// Determine if we should highlight this item
					$highlight = false;
					$highlight_title = '';
					$featured_labels = array();
					// First get its labels
					$labels = $this->get_menu_item_labels( $post_id );
					if ( ! empty( $labels ) && ! empty( $atts['featured_label'] ) ) {
						// Lets determine if some of the featured labels are among between the item's labels
						$featured_labels = array_intersect( $atts['featured_label'], $labels );
						// We have found some common labels -> we need to highlight
						if ( ! empty( $featured_labels ) ) {
							$highlight = true;

							// Now we need to see if we can extract the highlight title from the menu item's title
							// Meaning a portion that is wrapped by () or []

							// First try the []
							preg_match('/\[[^\]]*\]/', $title, $matches);
							if ( ! empty( $matches ) ) {
								$highlight_title = $matches[0];
							} else {
								// Now let try searching for text surrounded by ()
								preg_match('/\([^\)]*\)/', $title, $matches);
								if ( ! empty( $matches ) ) {
									$highlight_title = $matches[0];
								}
							}

							// Now remove the matched portion from the title
							if ( ! empty( $highlight_title ) ) {
								$title = trim( str_replace( $highlight_title, '', $title ) );
							}

							// Now cleanup the highlight title
							$highlight_title = trim( $highlight_title, '[]()' );
						}
					} ?>

                    <?php
                    $has_highlight_title = false;

                    if ( true === $highlight ) {
						if ( ! empty( $highlight_title ) ) {
							$has_highlight_title = true;
						}
					} ?>

					<li class="menu-list__item <?php
                        if ( true === $highlight ) {
	                        echo ' menu-list__item--highlight ';
                        }

                        if ( true === $has_highlight_title ) {
	                        echo ' menu-list__item--has-highlight-title ';
                        }

                        echo esc_attr( $this->get_menu_item_class( $post_id, $menu_item_index_number, $atts ) ); ?>">

					<?php
                    if ( $has_highlight_title ) {
                        echo '<span class="menu-list__item-highlight-title">' . $highlight_title . '</span>' . PHP_EOL;
                    } ?>

						<div class="menu-list__item-title">

							<?php
							// Featured image
							echo $this->get_menu_item_thumbnail( $post_id, $atts ); ?>

							<?php
							$before = '<h4 class="item_title">';
							$after = '</h4>';
							if ( false !== $atts['link_items'] ) {
								$before .= '<a href="' . esc_url( get_permalink() ) .'" title="' . esc_attr( the_title_attribute( ) ) . '">';
								$after = '</a>' . $after;
							}
							echo $before . $title . $after; ?>

							<?php
							if ( 'dotted' == $atts['style'] ) {
								echo '<span class="dots"></span>';
							} ?>

						</div>

                        <div class="menu-list__item-prices"><?php echo $this->get_menu_item_price( $post_id, $menu_item_index_number, $atts ); ?></div>

						<div class="menu-list__item-desc">
							<?php
							// The content
							if ( false !== $atts['display_content'] ) {
								echo '<div class="menu-list__item-content">';
								if ( 'full' === $atts['display_content'] ) {
									the_content();
								} else {
									the_excerpt();
								}
								echo '</div>';
							} ?>
						</div><span class="dots"></span>

						<div class="menu-list__item-meta">
							<?php
							if ( false !== $atts['display_labels'] ) {
								echo $this->the_menu_item_labels( $post_id, $featured_labels );
							} ?>
							<?php edit_post_link( esc_html__( 'Edit', 'components' ), '<span class="edit-link">', '</span>' ); ?>
						</div><!-- .entry-meta -->

					</li><!-- close .menu_item-entry -->
					<?php $menu_item_index_number++;
				} // end of while loop

				wp_reset_postdata(); ?>

				<?php // Since we are not using sections, we need to wrap everything
				if ( false == $atts['display_sections'] ) : ?>
				</ul><!-- .menu-list__items -->
				<?php endif; ?>

			</div><!-- close .jetpack-menu_item -->
			<?php
		} else { ?>
			<p class="jetpack-nova-menu-shortcode no-items"><?php esc_html_e( 'You seem to be short on menu entries. You can start creating them on your dashboard.', 'components' ); ?></p>
			<?php
		}
		$html = ob_get_clean();

		// If there is a [nova_menu] within a [nova_menu], remove the shortcode
		if ( has_shortcode( $html, 'nova_menu' ) ){
			remove_shortcode( 'nova_menu' );
		}

		// If there is a [restaurant_menu] within a [nova_menu], remove the shortcode
		if ( has_shortcode( $html, 'restaurant_menu' ) ){
			remove_shortcode( 'restaurant_menu' );
		}

		// Return the HTML block
		return $html;
	}

	/**
	 * Individual menu-item class
	 *
	 * @return string
	 */
	public function get_menu_item_class( $post_id, $menu_item_index_number, $atts ) {
		$class = array();

		// add a section- class for each menu-item section
		$menu_item_sections = wp_get_object_terms( $post_id, self::MENU_SECTION_TAX, array( 'fields' => 'slugs' ) );
		if ( ! empty( $menu_item_sections ) ) {
			foreach ( $menu_item_sections as $menu_item_section ) {
				$class[] = 'section-' . esc_attr( $menu_item_section );
			}
		}

		// Also add a class with labels so we can treat some of them differently
		$menu_item_labels = get_the_terms( $post_id, self::MENU_ITEM_LABEL_TAX );
		// Loop through all the labels
		if ( ! empty( $menu_item_labels ) ) {
			foreach ( $menu_item_labels as $menu_item_label ) {
				$class[] = 'label-' . esc_attr( $menu_item_label->slug );
			}
		}

		/**
		 * Filter the class applied to menu item div in the menu
		 *
		 * @param string $class class name of the div.
		 * @param int $menu_item_index_number iterator count the number of items up starting from 0.
		 *
		 */
		return apply_filters( 'pixelgrade_nova_menu-menu-item-post-class', implode( ' ', $class ) , $menu_item_index_number );
	}

	/**
	 * Displays the menu-item price.
	 *
	 * @param int $post_id
	 * @param int $menu_item_index_number
	 * @param array $atts
	 *
	 * @return string
	 */
	public function get_menu_item_price( $post_id, $menu_item_index_number, $atts ) {
		$price_string = get_post_meta( $post_id, 'nova_price', true );

		// We need to split the price by a series of markers if that is the case
		// First standardize the delimiter to comma
		// We do not consider space a delimiter
		$price_string = str_replace( array( '/', '|', ';' ), ', ', $price_string );
		// Second make sure that we strip all multiple spaces and reduce them to one
		$price_string = preg_replace( '/\s+/', ' ',$price_string );
		// Make sure that people who perceive the (), [] or -- as delimiters for discounted prices (strikeout) are taken care of
		$price_string = str_replace( ') ', '), ', $price_string );
		$price_string = str_replace( '] ', '], ', $price_string );
		$price_string = str_replace( '- ', '-, ', $price_string );

		// Now lets explode
		$prices = explode( ', ', $price_string );

		$html = '';

		foreach ( $prices as $price ) {
			$price = trim( $price );

			$class = array( 'menu-list__item-price' );
			// We have some special cases that make a certain price get a certain class, like discounted prices
			// First if we have wrapping characters like () or []
			if ( strpos( $price, '(' ) === 0 && strpos( $price, ')' ) === ( strlen( $price ) - 1 ) ) {
				$class[] = 'discounted';
				// trim the price
				$price = trim( $price, '()' );
			}
			if ( strpos( $price, '[' ) === 0 && strpos( $price, ']' ) === ( strlen( $price ) - 1 ) ) {
				$class[] = 'discounted';
				// trim the price
				$price = trim( $price, '[]' );
			}
			if ( strpos( $price, '-' ) === 0 && strpos( $price, '-' ) === ( strlen( $price ) - 1 ) ) {
				$class[] = 'discounted';
				// trim the price
				$price = trim( $price, '-' );
			}
			// If a price starts or ends with * we highlight it
			if ( strpos( $price, '*' ) === 0 || strpos( $price, '*' ) === ( strlen( $price ) - 1 ) ) {
				$class[] = 'highlighted';
				// trim the price
				$price = trim( $price, '*' );
			}

			$html .= '<span class="' . implode( ' ', $class ) . '">' . $price . '</span>';
		}

		return $html;
	}

	/**
	 * Displays the menu-item labels that a menu-item belongs to.
	 *
	 * @param int $post_id
	 * @param array $exclude_labels Optional. Labels to exclude from the list
	 *
	 * @return string
	 */
	public function the_menu_item_labels( $post_id, $exclude_labels = array() ) {
		$menu_item_labels = get_the_terms( $post_id, self::MENU_ITEM_LABEL_TAX );

		// If no labels, return empty string
		if ( empty( $menu_item_labels ) || is_wp_error( $menu_item_labels ) ) {
			return '';
		}

		$html = '<div class="menu-item-labels"><span class="label-text screen-reader-text">' . esc_html__( 'Labels', 'components' ) . ':</span>';
		$labels = array();
		// Loop through all the labels
		foreach ( $menu_item_labels as $menu_item_label ) {
			if ( ! in_array( $menu_item_label->slug, $exclude_labels ) ) {
				$term_name = $menu_item_label->name;
				$term_slug = $menu_item_label->slug;
				$labels[]  = '<span class="' . $term_slug . '">' . $term_name . '</span>';
			}
		}
		$html .= ' ' . implode( ', ', $labels );
		$html .= '</div><!-- .menu-item-labels -->';

		return $html;
	}

	public function get_menu_item_labels( $post_id ) {
		$terms = get_the_terms( $post_id, self::MENU_ITEM_LABEL_TAX );

		// If no labels, return false
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		$menu_item_labels = array();

		// Loop through all the labels
		foreach ( $terms as $term ) {
			$menu_item_labels[] = $term->slug;
		}

		return $menu_item_labels;
	}

	/**
	 * Display the featured image if it's available
	 *
	 * @return string
	 */
	public function get_menu_item_thumbnail( $post_id, $atts ) {
		$output = '';
		if ( has_post_thumbnail( $post_id ) ) {
			if ( false != $atts['link_items'] ) {
				$output .= '<a class="menu-item-featured-image" href="' . esc_url( get_permalink( $post_id ) ) . '">';
			}
			/**
			 * Change the Menu Item thumbnail size.
			 *
			 * @param string|array $var Either a registered size keyword or size array.
			 */
			$output .= get_the_post_thumbnail( $post_id, apply_filters( 'jetpack_nova_menu_thumbnail_size', 'small' ) );

			if ( false != $atts['link_items'] ) {
				$output .= '</a>';
			}
		}

		return $output;
	}

	/**
	 * Main Pixelgrade_Nova_Menu Instance
	 *
	 * Ensures only one instance of Pixelgrade_Nova_Menu is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Nova_Menu()
	 * @return Pixelgrade_Nova_Menu
	 */
	public static function instance(  $menu_item_loop_markup = array() ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		if ( $menu_item_loop_markup ) {
			self::$_instance->menu_item_loop_markup = wp_parse_args( $menu_item_loop_markup,
				apply_filters( 'pixelgrade_nova_menu_shortcode_menu_item_loop_markup', self::$_instance->default_menu_item_loop_markup ) );
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
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components' ),  esc_html( $this->_version ) );
	} // End __wakeup ()
}