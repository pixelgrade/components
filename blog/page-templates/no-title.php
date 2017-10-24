<?php
/**
 * Template Name: Default Template (No title)
 *
 * The template for displaying pages without a title.
 *
 * This template can be overridden by copying it to a child theme or in the same theme
 * in `/page-templates/blog/no-title.php` or `/page-templates/no-title.php`
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Blog
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// let the template parts know about our location
$location = pixelgrade_set_location( 'page no-title' );
if ( is_front_page() ) {
	// Add some more contextual info
	$location = pixelgrade_set_location( 'front-page');
}

pixelgrade_get_header(); ?>

<?php
/**
 * pixelgrade_before_primary_wrapper hook.
 */
do_action( 'pixelgrade_before_primary_wrapper', $location );
?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<?php
			/**
			 * pixelgrade_before_loop hook.
			 *
			 * @hooked nothing - 10 (outputs nothing)
			 */
			do_action( 'pixelgrade_before_loop', $location );
			?>

			<?php
			/** @var WP_Post $post */
			global $post;

			if ( have_posts() ) {
				// Get the current page content
				// Using the_post() is NOT good at all!!!
				// It will bring us the custom loop and end up in an infinite loop.
				// We may accidentally trigger the end of the world!
				setup_postdata( $post );

				pixelgrade_get_component_template_part( Pixelgrade_Blog::COMPONENT_SLUG,'content', 'page', true );

			} // End of the page content loop. ?>

			<?php
			/**
			 * pixelgrade_after_loop hook.
			 *
			 * @hooked Pixelgrade_Multipage->the_subpages() - 10 (outputs the subpages)
			 */
			do_action( 'pixelgrade_after_loop', $location );
			?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
/**
 * pixelgrade_after_primary_wrapper hook.
 */
do_action( 'pixelgrade_after_primary_wrapper', $location );
?>

<?php
pixelgrade_get_footer();
