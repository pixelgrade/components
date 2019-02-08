<?php
/**
 * This is the main file of our Woocommerce component
 *
 * Most importantly, this file provides the instantiation function that gets called when autoloading the components.
 * This function must be named in the following format:
 * - it is called Pixelgrade_{Component_Directory_Name} with the first letter or each word in uppercase separated by underscores
 * - the word separator is the minus sign, meaning "-" in directory name will be converted to "_"
 * The version of this file holds the version of the component, meaning that whenever you make any changes
 * to the component you should increase the header version of this file.
 * Please follow the Semantic Versioning 2.0.0 guidelines: http://semver.org/
 *
 * (A little inspiration close at hand https://www.youtube.com/watch?v=7PCkvCPvDXk )
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Woocommerce
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'Pixelgrade_Woocommerce' ) ) :
	/**
	 * Returns the main instance of Pixelgrade_Woocommerce to prevent the need to use globals.
	 *
	 * @since  1.0.0
	 * @return Pixelgrade_Woocommerce|object
	 */
	function Pixelgrade_Woocommerce() {
		// only load if we have to
		if ( ! class_exists( 'Pixelgrade_Woocommerce' ) ) {
			pixelgrade_load_component_file( 'woocommerce', 'class-Woocommerce' );
		}
		return Pixelgrade_Woocommerce::instance( '1.0.0' );
	}
endif;

/**
 * Load other files that this component needs loaded before the actual class instantiation.
 */

// Load our component's template tags
pixelgrade_load_component_file( 'woocommerce', 'inc/template-tags' );

// Load our component's extra functionality
pixelgrade_load_component_file( 'woocommerce', 'inc/extras' );
