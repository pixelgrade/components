<?php
/**
 * The template for displaying archive pages.
 *
 * Please note that various theme components may hijack this template and use a more specialized template like archive-jetpack-portfolio.php
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/archive.php` or in `/templates/base/archive.php`.
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
 * @package    Components/Base
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// Let the template parts know about our location
$location = pixelgrade_set_location( 'archive  index' );

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
			$visibility_class = '';
			if ( ! apply_filters( 'pixelgrade_display_entry_header', true, $location ) ) {
				$visibility_class = 'screen-reader-text';
			} ?>

			<div class="u-blog-sides-spacing  u-content-top-spacing  u-content-bottom-spacing <?php echo $visibility_class; ?>">
				<div class="o-wrapper u-blog-grid-width">

					<?php
					/**
					 * pixelgrade_before_title hook.
					 */
					do_action( 'pixelgrade_before_entry_title', $location );
					?>
					<!-- pixelgrade_before_entry_title -->

					<header class="entry-header  c-page-header">
						<h1 class="entry-title">
							<?php
							// We only want to show the page_for_post title or default title on the category and main archive pages
							if ( ! is_category() && ! is_post_type_archive( 'post' ) ) {
								the_archive_title();
							} elseif ( get_option( 'page_for_posts' ) ) {
								echo get_the_title( get_option( 'page_for_posts' ) );
							} else {
								echo apply_filters( 'pixelgrade_default_blog_archives_title', esc_html__( 'Journal', 'components_txtd' ), $location );
							} ?>
						</h1>
						<?php
						if ( is_category() || is_post_type_archive( 'post' ) ) {
							pixelgrade_the_taxonomy_dropdown( 'category' );
							the_archive_description( '<div class="entry-description">', '</div>' );
						} ?>
					</header><!-- .entry-header.c-page-header -->

					<?php
					/**
					 * pixelgrade_after_entry_title hook.
					 *
					 * @hooked nothing() - 10 (outputs nothing)
					 */
					do_action( 'pixelgrade_after_entry_title', $location ); ?>

				</div> <!-- .o-wrapper .u-blog-grid-width -->
			</div><!-- .u-blog-sides-spacing -->

			<div class="u-blog-sides-spacing  u-content-top-spacing  u-content-bottom-spacing">
				<div class="o-wrapper  u-blog-grid-width">

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
							pixelgrade_get_component_template_part( Pixelgrade_Base::COMPONENT_SLUG, 'loop', '', true ); ?>

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

				</div><!-- .o-wrapper.u-blog-grid-width -->
			</div><!-- .u-blog-sides-spacing -->
		</main><!-- #main -->
	</div><!-- #primary -->

<?php
/**
 * pixelgrade_after_primary_wrapper hook.
 */
do_action( 'pixelgrade_after_primary_wrapper', $location );
?>

<?php pixelgrade_get_footer();
