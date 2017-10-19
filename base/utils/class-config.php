<?php
/**
 * This is a utility class that groups all our config related helper functions
 *
 * These are to be used for all sort of config array processing and modifications, regardless if we are talking about component config,
 * metaboxes or Customizer/Customify config.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Pixelgrade_Config' ) ) :

class Pixelgrade_Config {

	/**
	 * Search a component's config and determine if it registers a certain page template identified by it's slug.
	 *
	 * @param string $page_template The page template (slug) we are looking for.
	 * @param array $config The full component config.
	 *
	 * @return bool
	 */
	public static function has_page_template( $page_template, $config ) {
		// Some sanity check
		if ( empty( $config ) || empty( $config['page-templates'] ) ) {
			return false;
		}

		$found_key = Pixelgrade_Array::find_subarray_by_key_value( $config['page-templates'], 'page_template', $page_template );
		if ( false !== $found_key ) {
			return true;
		}

		return true;
	}

	/**
	 * Process a value config and get the value
	 *
	 * We can handle post_meta, option, callback or a value straight away.
	 * No type coercing is done. If you send '10', you will receive '10'.
	 * If we fail to grab a value that is not null, false, '', or array(), we will fallback on the next entry.
	 * So the order matters. We will stop at the first valid value, top to bottom.
	 *
	 * @param array $config The value config.
	 * @param int $post_id
	 *
	 * @return mixed|false The determined value or false on failure.
	 */
	public static function get_config_value( $config, $post_id = 0 ) {
		// If the config is empty or not an array, return it - that might be the value
		if ( empty( $config ) || ! is_array( $config ) ) {
			return $config;
		}

		// Take each entry and try to get either the post_meta or option - fallback to post_meta
		foreach ( $config as $entry ) {
			// If we encounter a scalar type, stop and return it
			if ( ! is_array( $entry ) ) {
				return $entry;
			}

			// We've got an array on our hands
			if ( empty( $entry['name'] ) ) {
				continue;
			}
			$name = $entry['name'];

			$type = 'post_meta';
			if ( ! empty( $entry['type'] ) ) {
				$type = $entry['type'];
			}

			$value = null;
			switch ( $type ) {
				case 'callback':
					if ( is_callable( $name ) ) {
						$value = call_user_func( $name, $post_id );
					}
					break;
				case 'option':
					$value = get_option( $name, null );
					break;
				case 'post_meta':
				default:
					$value = get_post_meta( $post_id, $name, true );
					break;
			}

			if ( null !== $value && false !== $value && '' !== $value && array() !== $value ) {
				return $value;
			}
		}

		return false;
	}

	/**
	 * Evaluate a series of dependencies.
	 *
	 * We currently handle dependencies like these:
	 *  'components' => array(
	 *      // put here the main class of the component and we will test for existence and if the component is_active
	 *      'Pixelgrade_Hero',
	 *  ),
	 *  'class_exists' => array( 'Some_Class', 'Another_Class' ),
	 *  'function_exists' => array( 'some_function', 'another_function' ),
	 *
	 * @param array $dependencies The dependencies config array.
	 * @param array $data Optional. Extra data to use
	 *
	 * @return bool Returns true in case all dependencies are met, false otherwise. If there are no dependencies or the format is invalid, it returns true.
	 */
	public static function evaluate_dependencies( $dependencies, $data = array() ) {
		// Let's get some obvious things off the table
		// On invalid data, we allow things to proceed
		if ( empty( $dependencies ) || ! is_array( $dependencies ) ) {
			return true;
		}

		foreach ( $dependencies as $type => $checks ) {
			switch ( $type ) {
				case 'components' :
					if ( is_string( $checks ) ) {
						// we have a direct component main class name
						if ( ! class_exists( $checks ) || ! call_user_func( $checks . '::is_active' ) ) {
							return false;
						}
					} elseif ( is_array( $checks ) ) {
						foreach ( $checks as $component ) {
							if ( ! class_exists( $component ) || ! call_user_func( $component .'::is_active' ) ) {
								return false;
							}
						}
					}
					break;
				case 'class_exists' :
					if ( is_string( $checks ) ) {
						// we have a direct class name
						if ( ! class_exists( $checks ) ) {
							return false;
						}
					} elseif ( is_array( $checks ) ) {
						foreach ( $checks as $class ) {
							if ( ! class_exists( $class ) ) {
								return false;
							}
						}
					}
					break;
				case 'function_exists' :
					if ( is_string( $checks ) ) {
						// we have a direct function name
						if ( ! function_exists( $checks ) ) {
							return false;
						}
					} elseif ( is_array( $checks ) ) {
						foreach ( $checks as $function ) {
							if ( ! function_exists( $function ) ) {
								return false;
							}
						}
					}
					break;
				default :
					break;
			}
		}

		return true;
	}

	/**
	 * Evaluate a series of checks.
	 *
	 * We currently handle checks like these:
	 *  // Elaborate check description
	 *  array(
	 *		'function' => 'is_post_type_archive',
	 *		// The arguments we should pass to the check function.
	 *		// Think post types, taxonomies, or nothing if that is the case.
	 *		// It can be an array of values or a single value.
	 *		'args' => array(
	 *			'jetpack-portfolio',
	 *		),
	 *	),
	 *  // Simple check - just the function name
	 *  'is_404',
	 *
	 * @param array|string $checks The checks config.
	 * @param array $data Optional. Extra data to use
	 *
	 * @return bool Returns true in case all dependencies are met, false otherwise. If there are no dependencies or the format is invalid, it returns true.
	 */
	public static function evaluate_checks( $checks, $data = array() ) {
		// Let's get some obvious things off the table
		// On invalid data, we allow things to proceed
		if ( empty( $checks ) ) {
			return true;
		}

		// First, a little standardization
		if ( is_string( $checks ) ) {
			// We have gotten a single shorthand check
			$checks = array( $checks );
		}
		if ( is_array( $checks ) && isset( $checks['function'] ) ) {
			// We have gotten a single complex check
			$checks = array( $checks );
		}

		// Next, we test for a single check given as array
		if ( is_array( $checks ) ) {
			foreach ( $checks as $check ) {
				$response = self::evaluate_check( $check );
				if ( ! $response ) {
					// One check function returned false, bail
					return false;
				}
			}
		}

		// On invalid data, we allow things to proceed
		return true;
	}

	/**
	 * Evaluate a single check
	 *
	 * @param array|string $check
	 *
	 * @return bool
	 */
	public static function evaluate_check( $check ) {
		// Let's get some obvious things off the table
		// On invalid data, we allow things to proceed
		if ( empty( $check ) ) {
			return true;
		}

		// First, we handle the shorthand version: just a function name
		if ( is_string( $check ) && is_callable( $check ) ) {
			$response = call_user_func( $check );
			if ( ! $response ) {
				// One check function returned false, bail
				return false;
			}
		} elseif ( is_array( $check ) && ! empty( $check['function'] ) && is_callable( $check['function'] ) ) {
			if ( empty( $check['args'] ) ) {
				$check['args'] = array();
			}
			$response = call_user_func( $check['function'], $check['args'] );
			// Standardize the response
			if ( ! $response ) {
				return false;
			}
		}

		// On invalid data, we allow things to proceed
		return true;
	}

	/**
	 * Go through Customizer section(s) config and test if the defaults that should have been defined externally are so.
	 *
	 * @param array $modified_config The modified/filtered config.
	 * @param array $original_config The original component config.
	 * @param string $filter_to_use Optional. The filter that one should use for fixing things.
	 *
	 * @return bool
	 */
	public static function validate_customizer_section_config_defaults( $modified_config, $original_config, $filter_to_use = '' ) {
		if ( ! is_array( $modified_config ) || ! is_array( $original_config ) ) {
			return false;
		}

		$errors = false;
		// We will assume this is an array of array of sections
		foreach ( $original_config as $section_key => $section ) {
			if ( ! empty( $section['options'] ) && is_array( $section['options'] ) ) {
				foreach ( $section['options'] as $option_key => $option ) {
					if ( is_array( $option ) && array_key_exists( 'default', $option ) && null === $option['default'] ) {
						// This means we should receive a value in the modified config
						if ( ! isset( $modified_config[ $section_key ]['options'][ $option_key ]['default'] ) ) {
							_doing_it_wrong( __FUNCTION__,
								sprintf( 'You need to define a default value for the following Customizer option: %s > %s > %s.', $section_key, 'options', $option_key ) .
								( ! empty( $filter_to_use ) ? ' ' . sprintf( 'Use this filter: %s', $filter_to_use ) : ''), '1.0.0' );

							$errors = true;
						}
					}
				}
			}
		}

		return $errors;
	}

	/**
	 * Insert a value or key/value pair before a specific key in an array.  If key doesn't exist, value is prepended
	 * at the beginning of the array.
	 *
	 * @param array $original_config The original config we should apply the changes.
	 * @param array $partial_changes The partial changes we wish to make to the original config.
	 *
	 * @return array
	 */
	public static function merge( $original_config, $partial_changes ) {
		// For now we will just use array_replace_recursive that will replace only the leaves off the tree.
		// This solution makes it very greedy in terms of the fact that it keeps the original config unchanged
		// as much as possible.
		// The problem with this approach is that when a branch is new, it will be added at the END of the array.
		// You can't control the place where you wish to be added.
		return array_replace_recursive( $original_config, $partial_changes );
	}
}

endif;
