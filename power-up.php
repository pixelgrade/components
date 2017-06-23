<?php
/**
 * This file provides the functions needed to load each used component and loads it.
 * It also loads the general component files.
 *
 * @package Components
 * @since Components 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Load our global Typeline helper functions
 */
require_once( get_template_directory() . '/components/typeline.php' );

/**
 * Load our global Pixelgrade template tags
 */
require_once( get_template_directory() . '/components/pixelgrade_template-tags.php' );

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

/*=============================
FINISHED LOADING THE COMPONENTS
=============================*/
