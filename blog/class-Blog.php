<?php
/**
 * This is the main class of our Blog component.
 * (maybe this inspires you https://www.youtube.com/watch?v=7PCkvCPvDXk - actually, it really should! )
 *
 * Everything gets hooked up and bolted in here.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Blog
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Blog extends Pixelgrade_Component {

	const COMPONENT_SLUG = 'blog';

	/**
	 * Setup the blog component config
	 */
	public function setupConfig() {
		// Initialize the $config
		$this->config = array(
			'blocks' => array(
				'abstract' => array(
					'type' => 'template_part',
					'wrappers' => array(
						'primary' => array(
							'tag' => 'div',
							'id' => 'primary',
							'classes' => 'content-area',
							'priority' => array( 'callback' => 'get_the_id', ),
						),
						'main' => array(
							'id' => 'main',
							'classes' => 'site-main',
							'attributes' => array( 'role' => 'main', ),
							'priority' => 20,
						),
						array( 'classes' => 'o-layout', ),
					),
					'templates' => array(
						'none' => array(
							'component_slug' => self::COMPONENT_SLUG,
							'slug' => 'content',
							'name' => 'none',
						),
						'secondnone' => array(
							'component_slug' => self::COMPONENT_SLUG,
							'slug' => 'content',
							'name' => 'none',
						),
						array(
							'component_slug' => self::COMPONENT_SLUG,
							'slug' => 'content',
							'name' => 'none',
						),
					),
					'blocks' => array(
						'content_page_small' => array(
							'type' => 'template_part',
							'templates' => array(
								array(
									'component_slug' => self::COMPONENT_SLUG,
									'slug' => 'content',
									'name' => 'page_small',
								),
							),
							'wrappers' => array(
								array( 'classes' => 'o-layout__main' ),
							),
						),
						'content_page_small234' => array(
							'extend' => 'asdasd',
							'templates' => 'contentasdasdas',
						),
						'content_page_sidebar',
					),
					'checks' => array(
						array(
							'callback' => 'is_single',
							'args' => array(),
						),
						array(
							'callback' => 'is_home',
							'args' => array(),
						),
						'is_archive',
					),
				),
				'page' => array(
					'extend' => 'abstract',
					'type' => 'template_part',
					'wrappers' => array(
						'primary' => array(
							'tag' => 'div',
							'id' => 'primary',
							'classes' => 'content-area',
							'priority' => array( 'callback' => 'get_the_id', ),
						),
						'main' => array(
							'id' => 'main',
							'classes' => 'site-main',
							'attributes' => array( 'role' => 'main', ),
							'priority' => 20,
						),
						array( 'classes' => 'u-container-sides-spacing', ),
						array( 'classes' => array(
								'prefix' => 'boom-',
								'o-wrapper', 'u-container-width',
							),
						),
					),
					'templates' => array(
						'secondnone' => array(
							'component_slug' => self::COMPONENT_SLUG,
							'slug' => 'content',
							'name' => 'none',
						),
						array(
							'component_slug' => self::COMPONENT_SLUG,
							'slug' => 'loop',
							'name' => 'page',
						),
					),
					'checks' => 'is_archive',
				),
				'page22' => array(
					'extend' => 'abstract',
					'type' => 'layout',
					'wrappers' => array(
						'primary' => array(
							'tag' => 'div',
							'id' => 'primary',
							'classes' => 'content-area',
							'priority' => array( 'callback' => 'the_ID', ),
						),
						'main' => array(
							'id' => 'main',
							'classes' => 'site-main',
							'attributes' => array( 'role' => 'main', ),
							'priority' => 20,
						),
						array( 'classes' => 'u-container-sides-spacing', ),
						array( 'classes' => array(
							'prefix' => 'boom-',
							'o-wrapper', 'u-container-width',
						),
						),
					),
					'blocks' => array(
						'content_page_small' => array(
							'type' => 'template_part',
							'templates' => array(
								array(
									'component_slug' => self::COMPONENT_SLUG,
									'slug' => 'content',
									'name' => 'page_small',
								),
							),
							'wrappers' => array(
								array( 'classes' => 'o-layout__mainnnn' ),
							),
						),
						'content_page_small235' => array(
							'type' => 'layout',
							'blocks' => array(
								'content_page_small' => array(
									'type' => 'layout',
									'blocks' => array(
										'blog/page22/content_page_small235',
										'content_page_small235' => array(
											'extend' => 'booom',
											'templates' => 'contentasdasdas',
										),
										'blog/page22/content_page_small235',
									),
									'wrappers' => array(
										array( 'classes' => 'o-layout__mainnnn' ),
									),
								),
								'content_page_small',
								'content_page_small235' => array(
									'extend' => 'booom',
									'templates' => 'contentasdasdas',
								),
								'content_page_small',
							),
						),
						'content_page_sidebar',
					),
					'checks' => '',
				),
				'content_page' => array(
					'type' => 'layout',
					'wrappers' => array(
						'article' => array(
							'tag' => 'article',
							'id' => array( 'prefix' => 'post-', 'callback' => 'the_ID', ),
							'classes' => array( 'prefix' => 'post-', 'callback' => 'post_class' ),
						),
						array( 'classes' => 'u-container-sides-spacing', ),
						array( 'classes' => array(
								'prefix' => 'boom-',
								'o-wrapper', 'u-container-width',
							),
						),
						array( 'classes' => 'o-layout', ),
					),
					'blocks' => array(
						'asdas' => array(
							'type' => 'layout',
							'blocks' => array(
								'content_page_small' => array(
									'type' => 'template_part',
									'templates' => array(
										array(
											'component_slug' => self::COMPONENT_SLUG,
											'slug' => 'content',
											'name' => 'page_small',
										),
									),
									'wrappers' => array(
										array( 'classes' => 'o-layout__main' ),
									),
								),
								'content_page_small234' => array(
									'extend' => 'asdasd',
									'templates' => 'contentasdasdas',
									),
								'content_page_sidebar',
							),
						),
					),
				),
			),
			// For custom page templates, we can handle two formats:
			// - a simple one, where the key is the page_template partial path and the value is the template name as shown in the WP Admin dropdown; like so:
			// 'portfolio/page-templates/portfolio-page.php' => 'Portfolio Template'
			// - an extended one, where you can define dependencies (like other components); like so:
			// array (
			//	'page_template' => 'portfolio/page-templates/portfolio-page.php',
			//	'name' => 'Portfolio Template',
			//  'loop' => array(), // Optional - mark this as having a custom loop and define the behavior
			//	'dependencies' => array (
			//		'components' => array(
			//			// put here the main class of the component and we will test for existence and if the component isActive
			//			'Pixelgrade_Hero',
			//		),
			//		// We can also handle dependencies like 'class_exists' or 'function_exists':
			//		// 'class_exists' => array( 'Some_Class', 'Another_Class' ),
			//		// 'function_exists' => array( 'some_function', 'another_function' ),
			//	),
			// ),
			'page_templates' => array(
				// We put the component slug in front to make sure that we don't have collisions with other components or theme defined templates
				trailingslashit( self::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) .'full-width.php'          => 'Full Width',
				trailingslashit( self::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) .'full-width-no-title.php' => 'Full Width (No Title)',
				trailingslashit( self::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) .'no-title.php'            => 'Default Template (No title)',
			),
			'templates' => array(
				// The config key is just for easy identification by filters. It doesn't matter in the logic.
				//
				// However, the order in which the templates are defined matters: an earlier template has a higher priority
				// than a latter one when both match their conditions!
				'404' => array(
					// The type of this template.
					// Possible core values: 'index', '404', 'archive', 'author', 'category', 'tag', 'taxonomy', 'date',
					// 'embed', home', 'frontpage', 'page', 'paged', 'search', 'single', 'singular', and 'attachment'.
					// You can use (carefully) other values as long it is to your logic's advantage (e.g. 'header').
					// @see get_query_template() for more details.
					'type' => '404',
					// What checks should the current query pass for the templates to be added to the template hierarchy stack?
					// IMPORTANT: In case of multiple checks, it needs to pass all of them!
					// The functions will usually be conditional tags like `is_archive`, `is_tax`.
					// @see /wp-includes/template-loader.php for inspiration.
					// This is optional so you can have a template always added to a query type.
					// @see Pixelgrade_Config::evaluateChecks()
					'checks' => array(
						'callback' => 'is_404',
						// The arguments we should pass to the check callback.
						// Each top level array entry will be a parameter - see call_user_func_array()
						// So if you want to pass an array as a parameter you need to double enclose it like: array(array(1,2,3))
						'args' => array(),
					),
					// The template(s) that we should attempt to load.
					//
					// It can be a:
					// - a single string: this will be treated as the template slug;
					// - an array with the slug and maybe the name of the template;
					// - an array of arrays each with the slug and maybe the name of the template.
					// @see pixelgrade_add_configured_templates()
					//
					// The order is important as this is the order of priority, descending!
					'templates' => array(
						array(
							'slug' => '404',
							'name' => '',
						),
					),
					// We also support dependencies defined like the ones bellow.
					// Just make sure that the defined dependencies can be reliably checked at `after_setup_theme`, priority 12
					//
					// 'dependencies' => array (
					//      'components' => array(
					//	    	// put here the main class of the component and we will test for existence and if the component isActive
					//  		'Pixelgrade_Hero',
					//      ),
					//      // We can also handle dependencies like 'class_exists' or 'function_exists':
					//      'class_exists' => array( 'Some_Class', 'Another_Class', ),
					//      'function_exists' => array( 'some_function', 'another_function', ),
					//  ),
				),
				'home' => array(
					'type' => 'home',
					'checks' => array(
						'callback' => 'is_home',
						'args' => array(),
					),
					'templates' => 'home',
				),
				'single' => array(
					'type' => 'single',
					'checks' => array(
						'callback' => 'is_single',
						'args' => array(),
					),
					'templates' => 'single',
				),
				'page' => array(
					'type' => 'page',
					'checks' => array(
						'callback' => 'is_page',
						'args' => array(),
					),
					'templates' => 'page',
				),
				'archive' => array(
					'type' => 'archive',
					'checks' => array(
						'callback' => 'is_archive',
						'args' => array(),
					),
					'templates' => 'archive',
				),
				'search' => array(
					'type' => 'search',
					'checks' => array(
						'callback' => 'is_search',
						'args' => array(),
					),
					'templates' => 'search',
				),

				// Add our index at the end to be sure that it is used
				'index' => array(
					'type' => 'index',
					'templates' => array(
						'slug' => 'index',
						'name' => 'blog', // We need this so we can overcome the limitation of WordPress wanting a index.php in the theme root
					),
				),

				// Now for some of our own "types" that we use to handle pseudo-templates like `header.php`, `footer.php`
				// in a standard way
				'header' => array(
					'type' => 'header',
					'templates' => 'header',
				),
				'footer' => array(
					'type' => 'footer',
					'templates' => 'footer',
				),
				'sidebar' => array(
					'type' => 'sidebar',
					'templates' => 'sidebar',
				),
				// The comments.php template can't be configured this way. We pass the template path directly to comments_template()
				// @see pixelgrade_comments_template()
			),
		);

		// Allow others to make changes to the config
		// Make the hooks dynamic and standard
		$hook_slug = self::prepareStringForHooks( self::COMPONENT_SLUG );
		$modified_config = apply_filters( "pixelgrade_{$hook_slug}_initial_config", $this->config, self::COMPONENT_SLUG );

		// Check/validate the modified config
		if ( method_exists( $this, 'validate_config' ) && ! $this->validate_config( $modified_config ) ) {
			_doing_it_wrong( __METHOD__, sprintf( 'The component config  modified through the "pixelgrade_%1$s_initial_config" dynamic filter is invalid! Please check the modifications you are trying to do!', $hook_slug ), '1.0.0' );
			return;
		}

		// Change the component's config with the modified one
		$this->config = $modified_config;
	}

	/**
	 * Load, instantiate and hook up.
	 */
	public function fireUp() {
		/**
		 * Load and instantiate various classes
		 */

		// The class that handles the Customizer experience
		pixelgrade_load_component_file( self::COMPONENT_SLUG, 'inc/class-Blog-Customizer' );
		Pixelgrade_Blog_Customizer::instance( $this );

		// The class that handles the metaboxes
		pixelgrade_load_component_file( self::COMPONENT_SLUG, 'inc/class-Blog-Metaboxes' );
		Pixelgrade_Blog_Metaboxes::instance( $this );

		// Let parent's fire up as well - One big happy family!
		parent::fireUp();
	}

	/**
	 * Register our actions and filters
	 *
	 * @return void
	 */
	public function registerHooks() {
		/*
		 * ================================
		 * Handle our scripts and styles
		 */

		// Enqueue the frontend assets
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );

		// Setup how things will behave in the WP admin area
		add_action( 'admin_init', array( $this, 'adminInit' ) );

		// Enqueue assets for the admin
		add_action( 'admin_enqueue_scripts', array( $this, 'adminEnqueueScripts' ) );

		/*
		 * ================================
		 * Hook-up to various places where we need to output things
		 */

		// Add a pingback link element to the page head section for singularly identifiable articles
		add_action( 'wp_head', array( $this, 'pingbackHeader' ) );

		// Add a classes to the body element
		add_filter( 'body_class', array( $this, 'bodyClasses' ), 10, 1 );

		// Add a classes to individual posts
		add_filter( 'post_class', array( $this, 'postClasses' ), 10, 1 );

		/*
		 * ================================
		 * Hook-up to properly manage our templates like header.php, footer.php, etc
		 * We do it in a standard, fallbacky manner.
		 */

		// Add classes to the footer of the page
		add_filter( 'pixelgrade_footer_class', array( $this, 'footerClasses' ), 10, 1 );

		// Others might want to know about this and get a chance to do their own work (like messing with our's :) )
		do_action( 'pixelgrade_blog_registered_hooks' );
	}

	/**
	 * Enqueue styles and scripts on the frontend
	 */
	public function enqueueScripts() {
		// Register the frontend styles and scripts specific to blog
	}

	/**
	 * Load on when the admin is initialized
	 */
	public function adminInit() {
		/* register the styles and scripts specific to bloges */
		wp_register_style( 'pixelgrade_blog-admin-style', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( self::COMPONENT_SLUG ) . 'css/admin.css' ), array(), $this->assets_version );
		wp_register_script( 'pixelgrade_blog-admin-scripts', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( self::COMPONENT_SLUG ) . 'js/admin.js' ), array(), $this->assets_version );

	}

	/**
	 * Enqueue scripts and styles for the admin area
	 *
	 * @param string $hook
	 */
	public function adminEnqueueScripts( $hook ) {
		/* enqueue the styles and scripts specific to bloges */
		if ( 'edit.php' != $hook ) {
			wp_enqueue_style( 'pixelgrade_blog-admin-style');
			wp_enqueue_script( 'pixelgrade_blog-admin-scripts' );

			wp_localize_script( 'pixelgrade_blog-admin-scripts', 'pixelgrade_blog_admin', array(
				'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			) );
		}
	}

	/**
	 * Add classes to body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	public function bodyClasses( $classes ) {
		// Bail if we are in the admin area
		if ( is_admin() ) {
			return $classes;
		}

		$classes[] = 'u-content-background';

		// Add a class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}

		// Add a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		// Add a class to the body for the full width page templates
		// @todo We should account for the actual component config - e.g. if the page templates actually exist
		if ( is_page() &&
		     ( is_page_template( trailingslashit( self::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) .'full-width.php' ) ||
		       is_page_template( trailingslashit( self::COMPONENT_SLUG ) . trailingslashit( PIXELGRADE_COMPONENTS_PAGE_TEMPLATES_PATH ) .'full-width-no-title.php' ) )
		) {
			$classes[] = 'full-width';
		}

		if ( is_singular() && ! is_attachment() ) {
			$classes[] =  'singular';
		}

		if ( is_single() && is_active_sidebar( 'sidebar-1' ) ) {
			$classes[] = 'has-sidebar';
		}

		if ( is_single() ) {
			$image_orientation = pixelgrade_get_post_thumbnail_aspect_ratio_class();

			if ( ! empty( $image_orientation ) ) {
				$classes[] = 'entry-image--' . pixelgrade_get_post_thumbnail_aspect_ratio_class();
			}
		}

		if ( is_customize_preview() ) {
			$classes[] = 'is-customizer-preview';
		}

		if ( class_exists( 'PixCustomifyPlugin' ) ) {
			$classes[] = 'customify';
		} else {
			$classes[] = 'no-customify';
		}

		return $classes;
	}

	/**
	 * Add custom classes for individual posts
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function postClasses( $classes ) {
		//we first need to know the bigger picture - the location this template part was loaded from
		$location = pixelgrade_get_location();

		// This means we are displaying the blog loop
		if ( pixelgrade_in_location( 'index blog post portfolio jetpack', $location, false ) && ! is_single() ) {
			$classes[] = 'c-gallery__item';

			// $classes[] = 'u-width-' . $columns * 25 . '-@desk';
			$classes[] = 'c-gallery__item--' . pixelgrade_get_image_aspect_ratio_type( get_post_thumbnail_id(), 'landscape' );
		}

		// Add a class to the post for the full width page templates
		// And also make sure that we don't add it for every project in the portfolio shortcode
		if ( is_page() && pixelgrade_in_location( 'full-width', $location ) && ! pixelgrade_in_location( 'portfolio shortcode', $location ) ) {
			$classes[] = 'full-width';
		}

		return $classes;
	}

	/**
	 * Add custom classes to the footer of the page
	 *
	 * @param array $classes
	 *
	 * @return array
	 */
	function footerClasses( $classes ) {
		//we first need to know the bigger picture - the location this template part was loaded from
		$location = pixelgrade_get_location();

		// Add a class to the footer for the full width page templates
		if ( is_page() && pixelgrade_in_location( 'full-width', $location ) ) {
			$classes[] = 'full-width';
		}

		return $classes;
	}

	/**
	 * Add a pingback url auto-discovery header for singularly identifiable articles.
	 */
	function pingbackHeader() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="'. get_bloginfo( 'pingback_url', 'display' ). '">';
		}
	}
}
