<?php
/**
 * Various Pixelgrade template tags.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package     Components
 * @version     1.2.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Loads a component file allowing child themes to overwrite entire components.
 *
 * @access public
 * @param string $component_slug
 * @param string $slug
 * @param string $name (default: '')
 * @param bool $require_once (default: true)
 */
function pxg_load_component_file( $component_slug, $slug, $name = '', $require_once = true ) {
	$template = '';
	$components_path = 'components/';

	// Look in yourtheme/template-parts/component_slug/slug-name.php, yourtheme/component_slug/slug-name.php and yourtheme/components/component_slug/slug-name.php
	if ( ! empty( $name ) ) {
		$template = locate_template( array(
			'template-parts/' . trailingslashit( $component_slug ) . "{$slug}-{$name}.php",
			trailingslashit( $component_slug ) . "{$slug}-{$name}.php",
			$components_path . trailingslashit( $component_slug ) . "{$slug}-{$name}.php",
		) );
	}

	// If template file doesn't exist, look in yourtheme/template-parts/component_slug/slug.php, yourtheme/component_slug/slug.php and yourtheme/components/component_slug/slug.php
	if ( empty( $template ) ) {
		$template = locate_template( array(
			'template-parts/' . trailingslashit( $component_slug ) . "{$slug}.php",
			trailingslashit( $component_slug ) . "{$slug}.php",
			$components_path . trailingslashit( $component_slug ) . "{$slug}.php",
		) );
	}

	// Allow others to filter this
	$template = apply_filters( 'pxg_load_component_file', $template, $component_slug, $slug, $name );

	if ( ! empty( $template ) ) {
		load_template( $template, $require_once );
	}
}

/**
 * Get the current request action (it is used in the WP admin)
 *
 * @return bool|string
 */
function pxg_current_action() {
	if ( isset( $_REQUEST['filter_action'] ) && ! empty( $_REQUEST['filter_action'] ) )
		return false;

	if ( isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] )
		return wp_unslash( sanitize_text_field( $_REQUEST['action'] ) );

	if ( isset( $_REQUEST['action2'] ) && -1 != $_REQUEST['action2'] )
		return wp_unslash( sanitize_text_field( $_REQUEST['action2'] ) );

	return false;
}

// This function should come from Customify, but we need to do our best to make things happen
if ( ! function_exists( 'pixelgrade_option') ) {
	/**
	 * Get option from the database
	 *
	 * @param string $option The option name.
	 * @param mixed $default Optional. The default value to return when the option was not found or saved.
	 * @param bool $force_default Optional. When true, we will use the $default value provided for when the option was not saved at least once.
	 *                          When false, we will let the option's default set value (in the Customify settings) kick in first, than our $default.
	 *                          It basically, reverses the order of fallback, first the option's default, then our own.
	 *                          This is ignored when $default is null.
	 *
	 * @return mixed
	 */
	function pixelgrade_option( $option, $default = null, $force_default = true ) {
		/** @var PixCustomifyPlugin $pixcustomify_plugin */
		global $pixcustomify_plugin;

		if ( $pixcustomify_plugin !== null ) {
			// if there is a customify value get it here

			// First we see if we are not supposed to force over the option's default value
			if ( $default !== null && $force_default == false ) {
				// We will not pass the default here so Customify will fallback on the option's default value, if set
				$customify_value = $pixcustomify_plugin->get_option( $option );

				// We only fallback on the $default if none was given from Customify
				if ( $customify_value == null ) {
					return $default;
				}
			} else {
				$customify_value = $pixcustomify_plugin->get_option( $option, $default );
			}

			return $customify_value;
		}

		return $default;
	}
}

/**
 * Display the attributes for the body element.
 *
 * @param string|array $attribute One or more attributes to add to the attributes list.
 */
function pixelgrade_body_attributes( $attribute = '' ) {
	//get the attributes
	$attributes = pixelgrade_get_body_attributes( $attribute );

	//generate a string attributes array, like array( 'rel="test"', 'href="boom"' )
	$full_attributes = array();
	foreach ($attributes as $name => $value ) {
		//we really don't want numeric keys as attributes names
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

	if ( ! empty( $full_attributes ) ) {
		echo join( ' ', $full_attributes );
	}
}

function pixelgrade_get_body_attributes( $attribute = array() ) {
	$attributes = array();

	if ( ! empty( $attribute ) ) {
		$attributes = array_merge( $attributes, $attribute );
	} else {
		// Ensure that we always coerce class to being an array.
		$attribute = array();
	}

	/**
	 * Filters the list of body attributes for the current post or page.
	 *
	 * @since 2.8.0
	 *
	 * @param array $attributes An array of body attributes.
	 * @param array $attribute  An array of additional attributes added to the body.
	 */
	return apply_filters( 'pixelgrade_body_attributes', $attributes, $attribute );
}

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

		//if we have a prefix then we need to add it to every class
		if ( ! empty( $prefix ) && is_string( $prefix ) ) {
			foreach ( $class as $key => $value ) {
				$class[ $key ] = $prefix . $value;
			}
		}

		//if we have a suffix then we need to add it to every class
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
	 * Filters the list of CSS header classes for the current post or page
	 *
	 * @param array $classes An array of header classes.
	 * @param array $class   An array of additional classes added to the header.
	 * @param string|array $location   The place (template) where the classes are displayed.
	 */
	$classes = apply_filters( 'pixelgrade_css_class', $classes, $class, $location, $prefix, $suffix );

	return array_unique( $classes );
}

function pixelgrade_get_location( $default = '', $force = true ) {
	$location = get_query_var( 'pixelgrade_location' );

	if ( empty( $location ) ) {
		$location = $default;

		//we will force the query var to have the default value, in case it was empty
		if ( true === $force ) {
			//DO NOT put the second parameter of pixelgrade_set_location() to 'true' because you will cause an infinite loop!!!
			$location = pixelgrade_set_location( $default );
		}
	}

	return pixelgrade_standardize_location( $location );
}

function pixelgrade_set_location( $location = '', $merge = false ) {
	//In case one wants to add to the current location, not replace it
	if ( true === $merge ) {
		//The current location is already standardized
		$current_location = pixelgrade_get_location();
		$location = pixelgrade_standardize_location( $location );

		$location = array_merge( $current_location, $location );
	}

	//Make sure we have a standardized (Array) location
	$location = pixelgrade_standardize_location( $location );

	set_query_var( 'pixelgrade_location', $location );

	//allow others to chain this
	return $location;
}

/**
 * Searches for hints in a location string or array. If either of them is empty, returns false.
 *
 * @param string|array $search
 * @param string |array $location
 * @param bool $and Optional. Whether to make a AND search (the default) or a OR search. Anything other that 'true' means OR search.
 *
 * @return bool
 */
function pixelgrade_in_location( $search, $location, $and = true ) {
	// First make sure that we have a standard location format (i.e. array with each location)
	$location = pixelgrade_standardize_location( $location );

	// Also make sure that $search is standard
	$search = pixelgrade_standardize_location( $search );

	// Bail if either of them is empty
	if ( empty( $location ) || empty( $search ) ) {
		return false;
	}

	// Now let's handle the search
	// First we need to see if $search is an array with multiple values,
	// in which case we will do a AND search for each values if $and is true
	// else we will do a OR search
	$found = false;
	foreach ( $search as $item ) {
		if ( in_array( $item, $location ) ) {
			if ( true !== $and ) {
				// we are doing a OR search, so if we find any of the search locations, we are fine with that
				$found = true;
				break;
			} else {
				$found = true;
				continue;
			}
		} else {
			if ( true === $and ) {
				// we are doing a AND search, so if we haven't found one of the search locations, we can safely bail
				$found = false;
				break;
			} else {
				continue;
			}
		}
	}

	return $found;
}

/**
 * Takes a location hint and returns it in a standard format: an array with each hint separate
 *
 * @param string|array $location The location hints
 *
 * @return array|string
 */
function pixelgrade_standardize_location( $location ) {

	if ( is_string( $location ) ) {
		//the location might be a space separated series of hints
		//make sure we don't have white spaces at the beginning or the end
		$location = trim( $location );
		//some may use commas to separate
		$location = str_replace( ',', ' ', $location );
		//make sure we collapse multiple whitespaces into one space
		$location = preg_replace( '!\s+!', ' ', $location );
		//explode by space
		$location = explode( ' ', $location );
	}

	if ( empty( $location ) ) {
		$location = array();
	}

	return $location;
}

/**
 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
 * to the end of the array.
 *
 * @param array $array
 * @param string $key
 * @param array $insert
 *
 * @return array
 */
function pixelgrade_array_insert_after( $array, $key, $insert ) {
	$keys = array_keys( $array );
	$index = array_search( $key, $keys );
	$pos = false === $index ? count( $array ) : $index + 1;
	return array_merge( array_slice( $array, 0, $pos ), $insert, array_slice( $array, $pos ) );
}

/**
 * Retrieves the path of a file in the theme.
 *
 * Searches in the stylesheet directory before the template directory so themes
 * which inherit from a parent theme can just override one file.
 *
 * It will use the new function in WP 4.7, but will fallback to the old way of doing things otherwise.
 *
 * @param string $file Optional. File to search for in the stylesheet directory.
 * @return string The path of the file.
 */
function pixelgrade_get_theme_file_path( $file = '' ) {
	if ( function_exists( 'get_theme_file_path' ) ) {
		return get_theme_file_path( $file );
	} else {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$path = get_stylesheet_directory();
		} elseif ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
			$path = get_stylesheet_directory() . '/' . $file;
		} else {
			$path = get_template_directory() . '/' . $file;
		}

		/**
		 * Filters the path to a file in the theme.
		 *
		 * @since WP 4.7.0
		 *
		 * @param string $path The file path.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'theme_file_path', $path, $file );
	}
}

/**
 * Retrieves the URL of a file in the theme.
 *
 * Searches in the stylesheet directory before the template directory so themes
 * which inherit from a parent theme can just override one file.
 *
 * It will use the new function in WP 4.7, but will fallback to the old way of doing things otherwise.
 *
 * @param string $file Optional. File to search for in the stylesheet directory.
 * @return string The URL of the file.
 */
function pixelgrade_get_theme_file_uri( $file = '' ) {
	if ( function_exists( 'get_theme_file_uri' ) ) {
		return get_theme_file_uri( $file );
	} else {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$url = get_stylesheet_directory_uri();
		} elseif ( file_exists( get_stylesheet_directory() . '/' . $file ) ) {
			$url = get_stylesheet_directory_uri() . '/' . $file;
		} else {
			$url = get_template_directory_uri() . '/' . $file;
		}

		/**
		 * Filters the URL to a file in the theme.
		 *
		 * @since WP 4.7.0
		 *
		 * @param string $url  The file URL.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'theme_file_uri', $url, $file );
	}
}

/**
 * Retrieves the path of a file in the parent theme.
 *
 * It will use the new function in WP 4.7, but will fallback to the old way of doing things otherwise.
 *
 * @param string $file Optional. File to return the path for in the template directory.
 * @return string The path of the file.
 */
function pixelgrade_get_parent_theme_file_path( $file = '' ) {
	if ( function_exists( 'get_parent_theme_file_path' ) ) {
		return get_parent_theme_file_path( $file );
	} else {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$path = get_template_directory();
		} else {
			$path = get_template_directory() . '/' . $file;
		}

		/**
		 * Filters the path to a file in the parent theme.
		 *
		 * @since WP 4.7.0
		 *
		 * @param string $path The file path.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'parent_theme_file_path', $path, $file );
	}
}

/**
 * Retrieves the URL of a file in the parent theme.
 *
 * It will use the new function in WP 4.7, but will fallback to the old way of doing things otherwise.
 *
 * @param string $file Optional. File to return the URL for in the template directory.
 * @return string The URL of the file.
 */
function pixelgrade_get_parent_theme_file_uri( $file = '' ) {
	if ( function_exists( 'get_parent_theme_file_uri' ) ) {
		return get_parent_theme_file_uri( $file );
	} else {
		$file = ltrim( $file, '/' );

		if ( empty( $file ) ) {
			$url = get_template_directory_uri();
		} else {
			$url = get_template_directory_uri() . '/' . $file;
		}

		/**
		 * Filters the URL to a file in the parent theme.
		 *
		 * @since 4.7.0
		 *
		 * @param string $url The file URL.
		 * @param string $file The requested file to search for.
		 */
		return apply_filters( 'parent_theme_file_uri', $url, $file );
	}
}
