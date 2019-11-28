<?php
/**
 * The template part used for displaying the entry header for archives.
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/entry-header-archive.php` or in `/template-parts/blog/entry-header-archive.php`.
 *
 * @see pixelgrade_locate_component_template_part()
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Portfolio
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$location = pixelgrade_get_location();

$visibility_class = '';
if ( ! apply_filters( 'pixelgrade_display_entry_header', true, $location ) ) {
	$visibility_class = 'screen-reader-text';
}

?>

<div class="u-portfolio-sides-spacing  <?php echo esc_attr( $visibility_class ); ?>">
	<div class="o-wrapper  u-portfolio-grid-width">

		<?php
		/**
		 * pixelgrade_before_entry_title hook.
		 */
		do_action( 'pixelgrade_before_entry_title', $location );
		?>
		<!-- pixelgrade_before_entry_title -->

		<header class="entry-header  c-page-header">
			<h1 class="entry-title">
				<?php
				// We only want to show the page_for_projects title or default title on the type and main archive pages
				if ( ! is_tax( Jetpack_Portfolio::CUSTOM_TAXONOMY_TYPE ) && ! is_post_type_archive( Jetpack_Portfolio::CUSTOM_POST_TYPE ) ) {
					the_archive_title();
				} elseif ( pixelgrade_get_page_for_projects() ) {
					echo get_the_title( pixelgrade_get_page_for_projects() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				} else {
					echo apply_filters( 'pixelgrade_default_portfolio_archives_title', esc_html__( 'Projects', '__components_txtd' ), $location ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>
			</h1>

			<?php
			if ( is_post_type_archive( Jetpack_Portfolio::CUSTOM_POST_TYPE ) || is_tax( Jetpack_Portfolio::CUSTOM_TAXONOMY_TYPE ) ) {
				pixelgrade_the_taxonomy_dropdown( Jetpack_Portfolio::CUSTOM_TAXONOMY_TYPE );
				the_archive_description( '<div class="entry-description">', '</div>' );
			}
			?>
		</header><!-- .entry-header.c-page-header -->

		<?php
		/**
		 * pixelgrade_after_entry_title hook.
		 */
		do_action( 'pixelgrade_after_entry_title', $location );
		?>

	</div><!-- .o-wrapper .u-portfolio-grid-width -->
</div><!-- .u-portfolio-sides-spacing -->
