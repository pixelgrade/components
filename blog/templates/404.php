<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/404.php` or in `/templates/blog/404.php`.
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
$location = pixelgrade_set_location( '404' );

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

			<div class="u-container-sides-spacing  u-content-top-spacing  u-content-bottom-spacing">
				<div class="o-wrapper u-container-width">

					<section class="error-404 not-found">

						<?php
						/**
						 * pixelgrade_before_entry_title hook.
						 *
						 * @hooked pixelgrade_the_hero() - 10 (outputs the hero markup)
						 */
						do_action( 'pixelgrade_before_entry_title', $location );
						?>

						<?php
						$visibility_class = '';

						if ( ! apply_filters( 'pixelgrade_display_entry_header', true, $location ) ) {
							$visibility_class = 'screen-reader-text';
						}
						?>

						<header class="entry-header  c-page-header  u-content-bottom-spacing  <?php echo $visibility_class; ?>">
							<h1 class="entry-title"><?php esc_html_e( 'Oops! This page can&rsquo;t be found anywhere.', 'components_txtd' ); ?></h1>
						</header><!-- .page-header -->

						<div class="entry-content u-content-width">
							<p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try one of the links below or a search?', 'components_txtd' ); ?></p>
							<?php get_search_form(); ?>
						</div><!-- .entry-content -->

						<?php
						/**
						 * pixelgrade_after_entry_title hook.
						 *
						 * @hooked nothing() - 10 (outputs nothing)
						 */
						do_action( 'pixelgrade_after_entry_title', $location ); ?>

					</section><!-- .error-404 -->

				</div> <!-- .o-wrapper .u-blog-grid-width -->
			</div><!-- .u-blog-sides-spacing -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php
/**
 * pixelgrade_after_primary_wrapper hook.
 *
 * @hooked nothing - 10 (outputs nothing)
 */
do_action( 'pixelgrade_after_primary_wrapper', $location );
?>

<?php
pixelgrade_get_footer();
