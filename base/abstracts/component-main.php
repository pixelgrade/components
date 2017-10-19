<?php
/**
 * This is the abstract class for the main class of components. It's a singleton factory also.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @class Pixelgrade_Component_Main
 */
abstract class Pixelgrade_Component_Main {

	/**
	 * The ::COMPONENT_SLUG constant must be defined by the child class.
	 */

	/**
	 * The component's current version.
	 *
	 * @var string
	 */
	public $_version;

	/**
	 * The component's assets current version.
	 *
	 * @var string
	 */
	public $_assets_version = '1.0.0';

	/**
	 * The component's configuration.
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * The single instances of all the classes that extend this
	 */
	private static $_instance_array = null;

	/**
	 * The constructor.
	 *
	 * @throws Exception
	 * @param string $version Optional. The current component version.
	 * @param array $args Optional. Various arguments for the component initialization (like different priorities for the init hooks).
	 */
	public function __construct( $version = '1.0.0', $args = array() ) {
		$this->_version = $version;

		if ( ! defined(get_class( $this ) . '::COMPONENT_SLUG') ) {
			throw new Exception('Constant COMPONENT_SLUG is not defined on subclass ' . get_class( $this ) );
		}

		// Allow others to make changes to the arguments.
		// This can either be hooked before the autoloader does the instantiation (via the "pixelgrade_before_{$slug}_instantiation" action) or earlier, from a plugin.
		// A theme doesn't get another chance after the autoloader has done it's magic.
		// Make the hooks dynamic and standard
		// @todo When we get to using PHP 5.3+, refactor this to make use of static::COMPONENT_SLUG
		$hook_slug = self::prepare_string_for_hooks( constant( get_class( $this ) .'::COMPONENT_SLUG' ) );
		$args = apply_filters( "pixelgrade_{$hook_slug}_init_args", $args, constant( get_class( $this ) .'::COMPONENT_SLUG' ) );

		// Get going with the initialization of the component
		$this->init( $args );
	}

	/**
	 * Initialize the component.
	 *
	 * Initialize the whole component logic, including loading additional files, instantiating helpers, hooking etc.
	 *
	 * @param array $args Optional. Various arguments for the component initialization (like different priorities for the init hooks).
	 *
	 * @return void
	 */
	public function init( $args = array() ) {
		/**
		 * Setup the component config
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 *
		 * All component setups should happen at 'after_setup_theme' priority 20 - exceptions should be judged wisely!
		 * This is so we can allow for regular (priority 10) logic to properly hook up (with add_filter mainly) and
		 * be able to intervene in the setup of each component.
		 * IMPORTANT NOTICE: Do not go higher than priority 49 since the cross_config() is hooked at 50!
		 */
		$setup_config_priority = ( isset( $args['init']['priorities']['setup_config'] ) ? absint( $args['init']['priorities']['setup_config'] ) : 20 );
		add_action( 'after_setup_theme', array( $this, 'setup_config' ), $setup_config_priority );

		/**
		 * Process the component's config with regards to influencing other components and hookup
		 *
		 * Note that this function is hooked into the after_setup_theme hook, which
		 * runs before the init hook. The init hook is too late for some features, such
		 * as indicating support for post thumbnails.
		 *
		 * All component cross configuration setup should happen at 'after_setup_theme' priority 50 - exceptions should be judged wisely!
		 * This is so we can allow for the setup of each component to finish so they can influence each other
		 * in a predictable manner (e.g. modify the configuration of another component after it has been filtered).
		 */
		$setup_cross_config_priority = ( isset( $args['init']['priorities']['setup_cross_config'] ) ? absint( $args['init']['priorities']['setup_cross_config'] ) : 50 );
		add_action( 'after_setup_theme', array( $this, 'setup_cross_config' ), $setup_cross_config_priority );

		/**
		 * Fire up the cross configuration
		 *
		 * All component cross configuration should happen at 'after_setup_theme' priority 60 - exceptions should be judged wisely!
		 * Don't worry, you can have another go at the config after the cross configuration.
		 */
		$fire_up_cross_config_priority = ( isset( $args['init']['priorities']['fire_up_cross_config'] ) ? absint( $args['init']['priorities']['fire_up_cross_config'] ) : 60 );
		add_action( 'after_setup_theme', array( $this, 'fire_up_cross_config' ), $fire_up_cross_config_priority );

		/**
		 * One final occasion to filter the component's config
		 *
		 * If you want to skip all the internal config logic (e.g. all the headache), this is the hook to use to change a component's config.
		 * All component final configuration filtering should happen at 'after_setup_theme' priority 70 - exceptions should be judged wisely!
		 */
		$final_config_filter_priority = ( isset( $args['init']['priorities']['final_config_filter'] ) ? absint( $args['init']['priorities']['final_config_filter'] ) : 70 );
		add_action( 'after_setup_theme', array( $this, 'final_config_filter' ), $final_config_filter_priority );

		/*
		 * WE ARE DONE WITH THE COMPONENT'S CONFIGURATION AT THIS POINT
		 */

		/**
		 * Since some things like register_sidebars(), register_nav_menus() need to happen before the 'init' action (priority 10) - the point at which we fire_up()
		 * we do an extra init step, hooked to 'after_setup_theme' priority 80, by default.
		 * We only do this if the we have the pre_init_setup() method defined.
		 */
		if ( method_exists( $this, 'pre_init_setup' ) ) {
			$pre_init_setup_priority = ( isset( $args['init']['priorities']['pre_init_setup'] ) ? absint( $args['init']['priorities']['pre_init_setup'] ) : 80 );
			add_action( 'after_setup_theme', array( $this, 'pre_init_setup' ), $pre_init_setup_priority );
		}

		// Fire up our component logic, including registering our actions and filters
		$fire_up_priority = ( isset( $args['init']['priorities']['fire_up'] ) ? absint( $args['init']['priorities']['fire_up'] ) : 10 );
		add_action( 'init', array( $this, 'fire_up' ), $fire_up_priority );
	}

	/**
	 * Setup the initial version of the component's config.
	 *
	 * @return void
	 */
	abstract public function setup_config();

	/**
	 * Process the component config and hookup to influence other components
	 */
	public function setup_cross_config() {
		if ( ! empty( $this->config['components'] ) ) {
			// Go through every item and hookup so we can change the other component config, when the hook gets fired
			foreach ( $this->config['components'] as $component_to_config_slug => $details ) {
				// First we check if the target component is active
				// Get the component main class name
				$component_class = Pixelgrade_Components_Autoloader::get_component_main_class( $component_to_config_slug );
				if ( empty( $component_class ) || ! class_exists( $component_class ) || ! call_user_func( array( $component_class, 'is_active') ) ) {
					continue;
				}

				// Next, we get to the actual config change
				// Bail if we didn't get such details
				if ( empty( $details['config'] ) || ! is_array( $details['config'] ) ) {
					continue;
				}

				// Hookup
				$hook_slug = self::prepare_string_for_hooks( $component_to_config_slug );
				add_filter( "pixelgrade_{$hook_slug}_cross_config", array( $this, 'cross_config' ), 10, 2 );
			}
		}
	}

	/**
	 * Filter another component's config and change it according to the current component config.
	 *
	 * The config changes will be merged, not replaced, using array_replace_recursive().
	 *
	 * @param array $component_config The component config we wish to change.
	 * @param string $component_slug The slug of the component we wish to change.
	 *
	 * @return array The modified component config
	 */
	public function cross_config( $component_config, $component_slug ) {
		if ( ! empty( $this->config['components'][ $component_slug ]['config'] ) ) {
			// Change the 'config' by merging it
			// Thus overwriting the old with what we have changed
			$component_config = array_replace_recursive( $component_config, $this->config['components'][ $component_slug ]['config'] );
		}

		return $component_config;
	}

	/**
	 * Allow other components that have previously hooked up to change the component's config.
	 */
	public function fire_up_cross_config() {
		// Make the hooks dynamic and standard
		// @todo When we get to using PHP 5.3+, refactor this to make use of static::COMPONENT_SLUG
		$hook_slug = self::prepare_string_for_hooks( constant( get_class( $this ) .'::COMPONENT_SLUG' ) );
		$modified_config = apply_filters( "pixelgrade_{$hook_slug}_cross_config", $this->config, constant( get_class( $this ) .'::COMPONENT_SLUG' ) );

		// On cross config, another component (or others for what matters), can not modify the 'components' section of the config.
		// Not at this stage anyhow. That is to be done before the setup_cross_config, best via the "pixelgrade_{$hook_slug}_initial_config"
		if ( ! empty( $this->config['components'] ) &&
		     ! empty( $modified_config['components'] ) &&
		     false !== Pixelgrade_Array::array_diff_assoc_recursive( $this->config['components'], $modified_config['components'] ) ) {
			_doing_it_wrong( __METHOD__, sprintf( 'You should not modify the \'components\' part of the component config through the "pixelgrade_%1$s_cross_config" dynamic filter (due to possible logic loops). Use the "pixelgrade_%1$s_initial_config" filter instead.', $hook_slug ), '1.0.0' );
			return;
		}

		// Check/validate the modified config
		if ( method_exists( $this, 'validate_config' ) && ! $this->validate_config( $modified_config ) ) {
			_doing_it_wrong( __METHOD__, sprintf( 'The component config  modified through the "pixelgrade_%1$s_cross_config" dynamic filter is invalid! Please check the modifications you are trying to do!', $hook_slug ), '1.0.0' );
			return;
		}

		// Change the component's config with the modified one
		$this->config = $modified_config;
	}

	/**
	 * One final go at filtering the component config, this time after the cross configuration has taken place
	 *
	 * If you want to skip all the internal config logic, this is the hook to use to change a component's config.
	 */
	public function final_config_filter() {
		// Make the hooks dynamic and standard
		// @todo When we get to using PHP 5.3+, refactor this to make use of static::COMPONENT_SLUG
		$hook_slug = self::prepare_string_for_hooks( constant( get_class( $this ) .'::COMPONENT_SLUG' ) );
		$modified_config = apply_filters( "pixelgrade_{$hook_slug}_config", $this->config, constant( get_class( $this ) .'::COMPONENT_SLUG' ) );

		// Check/validate the modified config
		if ( method_exists( $this, 'validate_config' ) && ! $this->validate_config( $modified_config ) ) {
			_doing_it_wrong( __METHOD__, sprintf( 'The component config  modified through the "pixelgrade_%1$s_after_cross_config" dynamic filter is invalid! Please check the modifications you are trying to do!', $hook_slug ), '1.0.0' );
			return;
		}

		// Change the component's config with the modified one
		$this->config = $modified_config;
	}

	/**
	 * Load, instantiate and hook up.
	 *
	 * @return void
	 */
	abstract public function fire_up();

	/**
	 * Register the component's needed actions and filters, to do it's job.
	 *
	 * @return void
	 */
	abstract public function register_hooks();

	/**
	 * Get the component's configuration.
	 *
	 * @return array
	 */
	public function get_config() {
		return $this->config;
	}

	/**
	 * Given a string, it sanitizes and standardize it to be used for hook name parts (dynamic hooks).
	 *
	 * @param $string
	 *
	 * @return mixed
	 */
	public static function prepare_string_for_hooks( $string ) {
		// We replace all the minus chars with underscores
		$string = str_replace( '-', '_', $string );

		return $string;
	}

	/**
	 * Checks the configured page templates and registers them for use in the WP Admin.
	 *
	 * @param array $config The component's page-templates config.
	 * @param string $component_slug The component's slug.
	 *
	 * @return false|Pixelgrade_Page_Templater
	 */
	public static function setup_page_templates( $config, $component_slug ) {
		// Some sanity check
		if ( empty( $config ) || ! is_array( $config ) || empty( $component_slug ) ) {
			return false;
		}

		// We will gather the page templates that need to be registered
		$to_register = array();

		foreach ( $config as $key => $page_template ) {
			// We can handle two types of page template definitions
			// First the simple, more direct one
			if ( is_string( $key ) && is_string( $page_template ) ) {
				$to_register[ $key ] = $page_template;
			} elseif ( is_array( $page_template ) ) {
				// This is the more extended way of defining things

				// First some sanity check
				if ( empty( $page_template['page_template'] ) || empty( $page_template['name'] ) ) {
					continue;
				}

				// Now we need to process the dependencies
				if ( empty( $page_template['dependencies'] ) ) {
					$page_template['dependencies'] = array();
				}
				// We only register the page template if all dependencies are met
				if ( true === Pixelgrade_Config::evaluate_dependencies( $page_template['dependencies'], $page_template ) ) {
					$to_register[ $page_template['page_template'] ] = $page_template['name'];
				}
			}
		}

		// Fire up our component's page templates logic
		if ( ! empty( $to_register ) ) {
			// The class that handles the custom page templates for components
			pixelgrade_load_component_file( Pixelgrade_Base::COMPONENT_SLUG, 'inc/class-page-templater' );

			return new Pixelgrade_Page_Templater( $component_slug, $to_register );
		}

		return false;
	}

	/**
	 * Checks the configured custom templates and handles their logic to fit in the WordPress template hierarchy.
	 *
	 * @param array $config The component's templates config.
	 * @param string $component_slug The component's slug.
	 * @param int $priority The priority with which to hook into the templates hook. Higher means higher priority for the template candidates
	 *                      because they will be added more at the top of the stack.
	 *
	 * @return false|Pixelgrade_Templater
	 */
	public static function setup_custom_templates( $config, $component_slug, $priority = 10 ) {
		// Some sanity check
		if ( empty( $config ) || ! is_array( $config ) || empty( $component_slug ) ) {
			return false;
		}

		// Pick only the templates that are properly defined
		$templates = array();
		foreach ( $config as $key => $template ) {
			if ( is_array( $template ) ) {
				// First some sanity check
				if ( empty( $template['type'] ) || empty( $template['template'] ) ) {
					_doing_it_wrong( __FUNCTION__, sprintf( 'The custom template configuration is wrong! Please check the %s component config, at the %s template.', $component_slug, $key ), '1.0.0' );
					continue;
				}

				// Now we need to process the dependencies
				if ( empty( $template['dependencies'] ) ) {
					$template['dependencies'] = array();
				}
				// We only register the template if all dependencies are met
				if ( true === Pixelgrade_Config::evaluate_dependencies( $template['dependencies'], $template ) ) {
					// We need to keep the relative order in the array
					// So we will always add at the end of the array
					$templates = array_merge( $templates, array( $key => $template ) );
				}
			}
		}

		// Fire up our component's templates hierarchy logic
		if ( ! empty( $templates ) ) {
			// The class that handles the custom WordPress templates for components (not template parts)
			pixelgrade_load_component_file( Pixelgrade_Base::COMPONENT_SLUG, 'inc/class-templater' );

			return new Pixelgrade_Templater( $component_slug, $templates, $priority );
		}

		return false;
	}

	/**
	 * Handle the initialization of custom loops for archive pages with custom templates
	 *
	 * @param WP_Query $query
	 */
	public function setup_page_templates_custom_loop_query( $query ) {
		// We only do this on the frontend and only for the main query
		// Bail otherwise
		if ( is_admin() || ! $query->is_main_query() || empty( $this->config['page-templates'] ) ) {
			return;
		}

		// Get the current page ID
		$page_ID = $query->get( 'page_id' );
		if ( empty( $page_ID ) ) {
			$page_ID = $query->queried_object_id;
		}

		// Bail if we don't have a page ID
		if ( empty( $page_ID ) ) {
			return;
		}
		// For each custom page template that has a custom loop for some custom post type(s), setup the queries
		foreach ( $this->config['page-templates'] as $page_template_config ) {
			// Without a page-template and post types we can't do much
			if ( empty( $page_template_config['page_template'] ) || empty( $page_template_config['loop']['post_type'] ) ) {
				continue;
			}

			// Allow others to short-circuit this
			if ( true === apply_filters( 'pixelgrade_skip_custom_loops_for_page', false, $page_ID, $page_template_config ) ) {
				continue;
			}

			$page_template = $page_template_config['page_template'];
			$post_type     = $page_template_config['loop']['post_type'];
			// We also handle single post type declarations as string - standardize it to an array
			if ( ! is_array( $page_template_config['loop']['post_type'] ) ) {
				$post_type = array( $page_template_config['loop']['post_type'] );
			}

			// Determine how many posts per page
			if ( ! empty( $page_template_config['loop']['posts_per_page'] ) ) {
				// We will process the posts_per_page config and get the value
				$posts_per_page = intval( Pixelgrade_Config::get_config_value( $page_template_config['loop']['posts_per_page'], $page_ID ) );
			} else {
				$posts_per_page = intval( get_option( 'posts_per_page' ) );
			}
			// Make sure we have a sane posts_per_page value
			if ( empty( $posts_per_page ) ) {
				$posts_per_page = 10;
			}


			// Determine the ordering
			$orderby = array( 'menu_order' => 'ASC', 'date' => 'DESC' );
			if ( ! empty( $page_template_config['loop']['orderby'] ) && is_array( $page_template_config['loop']['orderby'] ) ) {
				$orderby = $page_template_config['loop']['orderby'];
			}

			$query_args = array(
				'post_type'        => $post_type,
				'posts_per_page'   => $posts_per_page,
				'orderby'          => $orderby,
				'suppress_filters' => false,
			);

			// Here we test to see if we need to exclude the featured projects
			if ( ! empty( $page_template_config['loop']['post__not_in'] ) ) {
				$query_args['post__not_in'] = Pixelgrade_Config::get_config_value( $page_template_config['loop']['post__not_in'], $page_ID );
			}

			// Determine the template part to use for individual posts - defaults to 'content' as in 'content.php'
			$post_template_part = 'content';
			if ( ! empty( $page_template_config['loop']['post_template_part'] ) && is_string( $page_template_config['loop']['post_template_part'] ) ) {
				$post_template_part = $page_template_config['loop']['post_template_part'];
			}

			// Determine the template part to use for the loop - defaults to false, meaning it will use a inline loop with out a template part
			$loop_template_part = false;
			if ( ! empty( $page_template_config['loop']['loop_template_part'] ) && is_string( $page_template_config['loop']['loop_template_part'] ) ) {
				$loop_template_part = $page_template_config['loop']['loop_template_part'];
			}

			// Make sure that the helper class is loaded
			pixelgrade_load_component_file( Pixelgrade_Base::COMPONENT_SLUG, 'inc/class-custom-loops-for-pages' );

			$new_query = new Pixelgrade_Custom_Loops_For_Pages(
				constant( get_class( $this ) .'::COMPONENT_SLUG' ),
				$page_template, // The page template slug we will target
				$post_template_part, // Component template part which will be used to display posts, name should be without .php extension
				$loop_template_part, // Component template part which will be used to display the loop, name should be without .php extension
				$query_args  // Array of valid arguments that will be passed to WP_Query/pre_get_posts
			);
			$new_query->init();

			// Now setup the hooks for outputting the custom loop and the wrappers
			// First the fake loop
			$fake_loop_action = 'pixelgrade_do_fake_loop';
			$fake_loop_priority = 10;
			if ( ! empty( $page_template_config['loop']['fake_loop_action'] ) ) {
				if ( is_array( $page_template_config['loop']['fake_loop_action'] ) && ! empty( $page_template_config['loop']['fake_loop_action']['function'] ) ) {
					$fake_loop_action = $page_template_config['loop']['fake_loop_action']['function'];
					if ( ! empty( $page_template_config['loop']['fake_loop_action']['priority'] ) ) {
						$fake_loop_priority = $page_template_config['loop']['fake_loop_action']['priority'];
					}
				} else {
					$fake_loop_action = $page_template_config['loop']['fake_loop_action'];
				}
			}
			// Hookup the fake loop
			add_action( $fake_loop_action, 'pixelgrade_do_fake_loop', $fake_loop_priority );

			// Now for other defined hooks, if any
			// Take each one and hook it to the appropriate action
			if ( ! empty( $page_template_config['loop']['hooks'] ) ) {
				foreach ( $page_template_config['loop']['hooks'] as $action => $hook ) {
					if ( is_callable( $hook ) ) {
						if ( 0 !== strpos( $action, 'pixelgrade_custom_loops_for_pages_' ) ) {
							$action = 'pixelgrade_custom_loops_for_pages_' . $action;
						}
						add_action( $action, $hook );
					}
				}
			}
		}
	}

	/**
	 * Check if the class has been instantiated.
	 *
	 * @return bool
	 */
	public static function is_active() {
		$called_class_name = get_called_class();

		if ( ! is_null( self::$_instance_array[ $called_class_name ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @static
	 *
	 * @param  string $version The component's current version.
	 * @param array $args Optional. Various arguments for the component initialization (like different priorities for the init hooks).
	 *
	 * @return object
	 */
	final public static function instance( $version, $args = array() ) {
		// We use PHP 5.3's late binding feature, but we provide a fallback function for when we are using PHP 5.2
		// @see /base/_core-functions.php
		// @todo Clean this up when we can use PHP 5.3+
		$called_class_name = get_called_class();

		if ( ! isset( self::$_instance_array[ $called_class_name ] ) ) {
			self::$_instance_array[ $called_class_name ] = new $called_class_name( $version, $args );
		}
		return self::$_instance_array[ $called_class_name ];
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 */
	final private function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components_txtd' ), esc_html( $this->_version ) );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	final private function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'components_txtd' ),  esc_html( $this->_version ) );
	} // End __wakeup ()
}
