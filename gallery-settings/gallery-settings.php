<?php
/**
 * This is the main file of our Gallery component
 *
 * Most importantly, this file provides the instantiation function that gets called when autoloading the components.
 * This function must be named in the following format:
 * - it is called Pixelgrade_{Component_Directory_Name} with the first letter or each word in uppercase separated by underscores
 * - the word separator is the minus sign, meaning "-" in directory name will be converted to "_"
 * The version of this file holds the version of the component, meaning that whenever you make any changes
 * to the component you should increase the header version of this file.
 * Please follow the Semantic Versioning 2.0.0 guidelines: http://semver.org/
 *
 * (A little inspiration close at hand https://www.youtube.com/watch?v=h4eueDYPTIg )
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Gallery
 * @version     1.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'Pixelgrade_GallerySettings' ) ) :
	/**
	 * Returns the main instance of Pixelgrade_GallerySettings to prevent the need to use globals.
	 *
	 * @since  1.2.0
	 * @return Pixelgrade_GallerySettings|object
	 */
	function Pixelgrade_GallerySettings() {
		// only load if we have to
		if ( ! class_exists( 'Pixelgrade_GallerySettings' ) ) {
			pixelgrade_load_component_file( 'gallery-settings', 'class-GallerySettings' );
		}
		return Pixelgrade_GallerySettings::instance( '1.2.1' );
	}
endif;

/**
 * Load other files that this component needs loaded before the actual class instantiation
 */
