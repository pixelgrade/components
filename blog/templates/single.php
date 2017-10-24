<?php
/**
 * The template for displaying all single posts.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/single.php` or in `/templates/blog/single.php`.
 * @see pixelgrade_locate_component_template()
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Blog
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Let the template parts know about our location
$location = pixelgrade_set_location( 'single post' );

pixelgrade_get_header(); ?>

<?php
/**
 * pixelgrade_before_primary_wrapper hook.
 *
 * @hooked nothing() - 10 (outputs nothing)
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
			while ( have_posts() ) : the_post();

				/**
				 * Include the Post-Format-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
				 */
				pixelgrade_get_component_template_part( Pixelgrade_Blog::COMPONENT_SLUG, 'content-single', get_post_format(), true );

			endwhile; // End of the loop.
			?>

			<?php
			/**
			 * pixelgrade_after_loop hook.
			 *
			 * @hooked nothing - 10 (outputs nothing)
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
