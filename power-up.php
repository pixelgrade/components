<?php
/**
 * This file provides the functions needed to load each used component and loads it.
 * It also loads the general component files.
 *
 * @package Julia
 * @since Julia 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Load our global Typeline helper functions
 */
require_once 'typeline.php';

/**
 * Load our global Pixelgrade template tags
 */
require_once 'pixelgrade_template-tags.php';

/*==========================
	LOAD THE COMPONENTS
==========================*/

/*++++++++++++++++++++++++*/
/**
 * Load the Pixelgrade Header component.
 * https://pixelgrade.com/
 */

/**
 * Returns the main instance of Pixelgrade_Header to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Pixelgrade_Header
 */
function Pixelgrade_Header() {
	//only load if we have to
	if ( ! class_exists( 'Pixelgrade_Header') ) {
		pxg_load_component_file( 'header', 'class-header' );
	}
	return Pixelgrade_Header::instance();
}

// Load The Header
$header_instance = Pixelgrade_Header();
/*------------------------*/

/*++++++++++++++++++++++++*/
/**
 * Load the Pixelgrade Hero component.
 * https://pixelgrade.com/
 */

/**
 * Returns the main instance of Pixelgrade_Hero to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Pixelgrade_Hero
 */
function Pixelgrade_Hero() {
	//only load if we have to
	if ( ! class_exists( 'Pixelgrade_Hero') ) {
		pxg_load_component_file( 'hero', 'class-hero' );
	}
	return Pixelgrade_Hero::instance();
}

// Load The Heroes
$hero_instance = Pixelgrade_Hero();
/*------------------------*/

/*++++++++++++++++++++++++*/
/**
 * Load the Pixelgrade Footer component.
 * https://pixelgrade.com/
 */

/**
 * Returns the main instance of Pixelgrade_Footer to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Pixelgrade_Footer
 */
function Pixelgrade_Footer() {
	//only load if we have to
	if ( ! class_exists( 'Pixelgrade_Footer') ) {
		pxg_load_component_file( 'footer', 'class-footer' );
	}
	return Pixelgrade_Footer::instance();
}

// Load The Footer
$footer_instance = Pixelgrade_Footer();
/*------------------------*/

/*++++++++++++++++++++++++*/
/**
 * Load the Pixelgrade Featured Image component.
 * https://pixelgrade.com/
 */

/**
 * Returns the main instance of Pixelgrade_Feature_Image to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Pixelgrade_Feature_Image
 */
function Pixelgrade_Feature_Image() {
	//only load if we have to
	if ( ! class_exists( 'Pixelgrade_Feature_Image') ) {
		pxg_load_component_file( 'featured-image', 'class-featured-image' );
	}
	return Pixelgrade_Feature_Image::instance();
}

// Load The Feature Image
$featured_image_instance = Pixelgrade_Feature_Image();
/*------------------------*/


/*++++++++++++++++++++++++*/
/**
 * Load the Pixelgrade Gallery component.
 * https://pixelgrade.com/
 */

/**
 * Returns the main instance of Pixelgrade_Gallery_Settings to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Pixelgrade_Gallery_Settings
 */
function Pixelgrade_Gallery_Settings() {
	//only load if we have to
	if ( ! class_exists( 'Pixelgrade_Gallery_Settings') ) {
		pxg_load_component_file( 'gallery', 'class-gallery-settings' );
	}
	return Pixelgrade_Gallery_Settings::instance();
}

// Load The Gallery
$gallery_settings_instance = Pixelgrade_Gallery_Settings();
/*------------------------*/

/*++++++++++++++++++++++++*/
/**
 * Load the Pixelgrade Multipage component.
 * https://pixelgrade.com/
 */

/**
 * Returns the main instance of Pixelgrade_Multipage to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return Pixelgrade_Multipage
 */
function Pixelgrade_Multipage() {
	//only load if we have to
	if ( ! class_exists( 'Pixelgrade_Multipage') ) {
		pxg_load_component_file( 'multipage', 'class-multipage' );
	}
	return Pixelgrade_Multipage::instance();
}

// Load The Multipage
$multipage_instance = Pixelgrade_Multipage();
/*------------------------*/

/*=============================
FINISHED LOADING THE COMPONENTS
=============================*/