<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/index.php` or in `/templates/blog/index.php`.
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
$location = pixelgrade_set_location( 'index' );

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
			 * pixelgrade_after_entry_article_start hook.
			 */
			do_action( 'pixelgrade_after_entry_article_start', $location );
			?>
			<!-- pixelgrade_after_entry_article_start -->

			<div class="u-container-sides-spacing  u-content-top-spacing  u-content-bottom-spacing">
				<div class="o-wrapper  u-container-grid-width">

					<?php
					/**
					 * pixelgrade_after_entry_start hook.
					 */
					do_action( 'pixelgrade_after_entry_start', $location );
					?>
					<!-- pixelgrade_after_entry_start -->

					<div class="o-layout">

						<?php
						/**
						 * pixelgrade_before_entry_main hook.
						 */
						do_action( 'pixelgrade_before_entry_main', $location );
						?>
						<!-- pixelgrade_before_entry_main -->

						<div class="o-layout__main">

							<?php
							/*
							 * Load the loop
							 */
							pixelgrade_get_component_template_part( Pixelgrade_Blog::COMPONENT_SLUG, 'loop', '', true ); ?>

						</div><!-- .o-layout__main -->

						<?php
						/**
						 * pixelgrade_after_entry_main hook.
						 */
						do_action( 'pixelgrade_after_entry_main', $location );
						?>
						<!-- pixelgrade_after_entry_main -->

						<?php // pixelgrade_get_sidebar(); ?>

					</div><!-- .o-layout -->

					<?php
					/**
					 * pixelgrade_before_entry_end hook.
					 */
					do_action( 'pixelgrade_before_entry_end', $location );
					?>
					<!-- pixelgrade_before_entry_end -->

				</div><!-- .o-wrapper.u-container-grid-width -->
			</div><!-- .u-container-sides-spacing -->
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
/**
 * pixelgrade_after_primary_wrapper hook.
 */
do_action( 'pixelgrade_after_primary_wrapper', $location );
?>

<?php pixelgrade_get_footer();
