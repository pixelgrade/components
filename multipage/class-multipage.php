<?php
/**
 * This is the main class of our Multipage component.
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Multipage
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//Load our component's template tags
pxg_load_component_file( 'multipage', 'template-tags' );

class Pixelgrade_Multipage {

	public $component = 'multipage';
	public $_version  = '1.0.0';
	public $_assets_version = '1.0.0';

	private static $_instance = null;

	public function __construct() {
		// Register our actions and filters
		$this->register_hooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function register_hooks() {
		// Enqueue the frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Output the subpages markup
		add_action( 'pixelgrade_after_loop', array( $this, 'the_subpages' ), 10, 1 );

		// Add some classes to the <article> for pages
		add_filter( 'post_class', array( $this, 'post_classes' ) );

		// Customize the hero scroll down arrow logic
		// Prevent the arrow from appearing on the subpage heroes
		// Do note that this only works if the theme adds the scroll down arrow with this filter applied
		add_filter( 'pixelgrade_hero_show_scroll_down_arrow', array( $this, 'prevent_hero_scroll_down_arrow' ), 10, 3 );

		// We will only play with redirects and permalinks if the permalinks are active
		if ( get_option('permalink_structure') ) {
			// Redirect subpages to the main page with hashtag at the end (blog.com/main-page/child-page -> blog.com/main-page/#child-page
			add_action( 'template_redirect', array( $this, 'redirect_subpages' ) );

			//modify page permalinks
			// Change the sample permalink in the WP Admin to match the one used in the redirect
			add_filter( 'page_link', array( $this, 'modify_page_permalink' ), 10, 3 );

			// Change the sample permalink in the WP Admin to match the one used in the redirect
			add_filter( 'get_sample_permalink', array( $this, 'modify_sample_permalink' ), 10, 5 );
		}

		// Prevent comments on multipages
		add_filter( 'comments_open', array( $this, 'prevent_comments' ), 10, 2 );
		// Even if there are comments, do not display them
		add_filter( 'get_comments_number', array( $this, 'prevent_comments' ), 10, 2 );

		//Add our edit links to the admin bar, in the WP Admin dashboard
		add_action( 'admin_bar_menu', array( $this, 'subpages_admin_bar_edit_links_backend' ), 999 );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_multipage_registered_hooks' );
	}

	/**
	 * Enqueue styles and scripts on the frontend
	 */
	public function enqueue_scripts() {
		// Register the frontend styles and scripts specific to multipages
		wp_register_script( 'tween-max', '//cdnjs.cloudflare.com/ajax/libs/gsap/1.19.0/TweenMax.min.js', array( 'jquery' ) );
		wp_register_script( 'ease-pack', '//cdnjs.cloudflare.com/ajax/libs/gsap/1.19.0/easing/EasePack.min.js', array( 'jquery' ) );
		wp_register_script( 'pixelgrade_multipage-scripts', trailingslashit( get_template_directory_uri() ) . 'components/multipage/js/front.js', array( 'jquery', 'tween-max', 'ease-pack' ), $this->_assets_version, true );

		// See if we need to enqueue something for multipages
		if ( is_page() && pixelgrade_multipage_has_children() ) {
			wp_enqueue_script( 'pixelgrade_multipage-scripts' );
		}
	}

	/**
	 * Displays the subpages.
	 *
	 * @param string|array $location Optional. This is a hint regarding the place/template where this is being displayed
	 */
	public function the_subpages( $location = '' ) {
		if ( is_page() && pixelgrade_multipage_has_children() ) {
			//so far we are interested only in pages
			if ( pixelgrade_in_location( 'page', $location ) ) {
				// Fire up the subpages loop
				// We will use the regular theme template parts like content-page.php
				pxg_load_component_file( 'multipage', 'templates/loop', '', false );
			}
		}
	}

	/**
	 * Add custom classes for pages
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function post_classes( $classes ) {
		//we first need to know the bigger picture - the location this template part was loaded from
		$location = pixelgrade_get_location();

		if ( is_page() ) {
			$classes[] = 'article--page';
		}

		return $classes;
	}

	/**
	 * Do not allow hero scroll down arrows on subpages
	 *
	 * @param bool $show
	 * @param array|string $location
	 * @param int $post_id
	 *
	 * @return bool
	 */
	function prevent_hero_scroll_down_arrow( $show, $location, $post_id ) {
		if ( pixelgrade_multipage_is_child( $post_id ) ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Redirect subpages to the main page with hashtag at the end (blog.com/main-page/child-page -> blog.com/main-page/#child-page)
	 */
	public function redirect_subpages() {
		$object = get_queried_object();

		// Allow others to short-circuit us and prevent us from entering the multipage logic
		if ( ! apply_filters( 'pixelgrade_multipage_allow', true, $object ) ) {
			return;
		}

		// If this is not a child page we do nothing
		if ( ! pixelgrade_multipage_is_child( $object ) ) {
			return;
		}

		$child_link = $object->post_name;

		// Get the parent permalink
		$parent_link = get_permalink( pixelgrade_multipage_get_parent( $object ) );

		// Construct the child permalink starting with the parent's and adding the hashtag for the child
		// we also replace / with . since slashes are not allowed in ids
		$child_link = user_trailingslashit( $parent_link, 'page' ) . '#' . str_replace( '/', '.', $child_link );

		// Finally redirect
		wp_redirect( $child_link );
		exit;
	}

	/**
	 * Returns the modified page permalink
	 *
	 * @param string  $permalink Sample permalink.
	 * @param int     $post_id   Post ID.
	 * @param string  $sample    Is it a sample permalink.
	 *
	 * @return string
	 */
	public function modify_page_permalink( $permalink, $post_id, $sample ) {
		if ( pixelgrade_multipage_is_child( $post_id ) ) {
			$post = get_post( $post_id );

			// Remove the trailing slash
			$permalink = untrailingslashit( $permalink);

			//replace the subpages name with #name
			$permalink = str_replace( '/' . $post->post_name, '/#' . $post->post_name, $permalink );
		}

		return $permalink;
	}

	/**
	 * Returns the modified sample permalink
	 *
	 * @param array  $permalink Sample permalink.
	 * @param int     $post_id   Post ID.
	 * @param string  $title     Post title.
	 * @param string  $name      Post name (slug).
	 * @param WP_Post $post      Post object.
	 *
	 * @return array
	 */
	public function modify_sample_permalink( $permalink, $post_id, $title, $name, $post ) {
		if ( pixelgrade_multipage_is_child( $post_id ) ) {
			// Remove the trailing slash
			$permalink[0] = untrailingslashit( $permalink[0]);

			// Replace the last %pagename% with #%pagename%
			$permalink[0] = str_replace( '%pagename%', '#%pagename%', $permalink[0] );
		}

		return $permalink;
	}

	/**
	 * If a page has subpages, prevent comments from being displayed regardless of Discussion settings.
	 *
	 * @param bool        $open    Whether the current post is open for comments.
	 * @param int|WP_Post $post_id The post ID or WP_Post object.
	 *
	 * @return bool
	 */
	public function prevent_comments( $open, $post_id ) {
		// If the current page has subpages, prevent comments from being displayed
		if ( is_page( $post_id ) && pixelgrade_multipage_has_children( $post_id ) ) {
			return false;
		}

		return $open;
	}

	/**
	 * Subpages edit links in the admin bar in the backend (edit/new page)
	 *
	 * @TODO move this inside a plugin
	 *
	 * @param WP_Admin_Bar $wp_admin_bar
	 */
	function subpages_admin_bar_edit_links_backend( $wp_admin_bar ) {
		global $post, $pagenow;

		$is_edit_page = in_array( $pagenow, array( 'post.php',  ) );

		if ( ! $is_edit_page ) //check for new post page
			$is_edit_page = in_array( $pagenow, array( 'post-new.php' ) );
		elseif ( ! $is_edit_page )  //check for either new or edit
			$is_edit_page = in_array( $pagenow, array( 'post.php', 'post-new.php' ) );


		if ( $is_edit_page && isset( $post->post_parent ) && ! empty( $post->post_parent ) ) {

			$wp_admin_bar->add_node( array(
				'id'    => 'edit_parent',
				'title' => __( 'Edit Parent', 'components' ),
				'href'  => get_edit_post_link( $post->post_parent ),
				'meta'  => array( 'class' => 'edit_parent_button' )
			) );

			$siblings = get_children(
				array(
					'post_parent' => $post->post_parent,
					'orderby' => 'menu_order title', //this is the exact ordering used on the All Pages page - order included
					'order' => 'ASC',
					'post_type' => 'page',
				)
			);

			$siblings = array_values($siblings);
			$current_pos = 0;
			foreach ( $siblings as $key => $sibling ) {

				if ( $sibling->ID == $post->ID ) {
					$current_pos = $key;
				}
			}

			if ( isset($siblings[ $current_pos - 1 ] ) ){

				$prev_post = $siblings[ $current_pos - 1 ];

				$wp_admin_bar->add_node( array(
					'id'    => 'edit_prev_child',
					'title' => __( 'Edit Prev Child', 'components' ),
					'href'  => get_edit_post_link( $prev_post->ID ),
					'meta'  => array( 'class' => 'edit_prev_child_button' )
				) );
			}

			if ( isset($siblings[ $current_pos + 1 ] ) ) {

				$next_post =  $siblings[ $current_pos + 1 ];

				$wp_admin_bar->add_node( array(
					'id'    => 'edit_next_child',
					'title' => __( 'Edit Next Child', 'components' ),
					'href'  => get_edit_post_link( $next_post->ID ),
					'meta'  => array( 'class' => 'edit_next_child_button' )
				) );
			}

		}

		//this way we allow for pages that have both a parent and children
		if ( $is_edit_page ) {

			$kids = get_children(
				array(
					'post_parent' => $post->ID,
					'orderby' => 'menu_order title', //this is the exact ordering used on the All Pages page - order included
					'order' => 'ASC',
					'post_type' => 'page',
				)
			);

			if ( ! empty( $kids ) ) {

				$args = array(
					'id'    => 'edit_children',
					'title' => __( 'Edit Children', 'components' ),
					'href'  => '#',
					'meta'  => array( 'class' => 'edit_children_button' )
				);

				$wp_admin_bar->add_node( $args );

				foreach ( $kids as $kid ) {
					$kid_args = array(
						'parent' => 'edit_children',
						'id'    => 'edit_child_' . $kid->post_name,
						'title' => __( 'Edit', 'components' ) . ': ' . $kid->post_title,
						'href'  => get_edit_post_link( $kid->ID ),
						'meta'  => array( 'class' => 'edit_child_button' )
					);
					$wp_admin_bar->add_node( $kid_args );
				}
			}
		}
	}

	/**
	 * Main Pixelgrade_Multipage Instance
	 *
	 * Ensures only one instance of Pixelgrade_Multipage is loaded or can be loaded.
	 *
	 * @since  1.0.0
	 * @static
	 *
	 * @see    Pixelgrade_Multipage()
	 * @return Pixelgrade_Multipage
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
		_doing_it_wrong( __FUNCTION__,esc_html( __( 'Cheatin&#8217; huh?' ) ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheatin&#8217; huh?' ) ),  esc_html( $this->_version ) );
	} // End __wakeup ()
}