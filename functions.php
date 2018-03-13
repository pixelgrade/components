<?php
/**
 * Components mock functions and definitions. Used only for testing the components.
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Components
 * @since 1.0.0
 */

/*
 * =========================
 * Autoload the Pixelgrade Components FTW!
 * This must be the FIRST thing a theme does!
 * =========================
 */
require_once trailingslashit( get_template_directory() ) . 'components-autoload.php';
Pixelgrade_Components_Autoload();

/**
 * Declare support for all components to allow for proper testing.
 */
function components_force_setup_all_components() {
	/*
	 * Declare support for the Pixelgrade Components the theme uses.
	 * Please note that some components will load regardless (like Base, Blog, Header, Footer).
	 * It is safe although to declare support for all that you use (for future proofing).
	 */
	add_theme_support( 'pixelgrade-base-component' );
	add_theme_support( 'pixelgrade-blog-component' );
	add_theme_support( 'pixelgrade-header-component' );
	add_theme_support( 'pixelgrade-hero-component' );
	add_theme_support( 'pixelgrade-footer-component' );
	add_theme_support( 'pixelgrade-featured-image-component' );
	add_theme_support( 'pixelgrade-gallery-settings-component' );
	add_theme_support( 'pixelgrade-multipage-component' );
	add_theme_support( 'pixelgrade-portfolio-component' );
}
add_action( 'after_setup_theme', 'components_force_setup_all_components', 10 );
