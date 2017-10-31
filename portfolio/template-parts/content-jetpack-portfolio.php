<?php
/**
 * The template used for displaying post content on archives
 *
 * This template can be overridden by copying it to a child theme
 * or in the same theme by putting it in template-parts/portfolio/content-jetpack-portfolio.php.
 * @see pixelgrade_locate_component_template_part()
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Portfolio
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//we first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location( 'portfolio jetpack jetpack-portfolio' );
?>

<?php
/**
 * pixelgrade_before_loop_entry hook.
 *
 * @hooked pixelgrade_the_post_custom_css() - 10 (outputs the post's custom css)
 */
do_action( 'pixelgrade_before_loop_entry', $location );
?>

<article id="postit-<?php the_ID(); ?>" <?php post_class() ?>>
	<div class="c-card">
		<?php if ( pixelgrade_display_featured_images() ) { ?>
			<div class="c-card__aside c-card__thumbnail-background">
				<div class="c-card__frame">
					<?php if ( has_post_thumbnail() ) {
						the_post_thumbnail();
					}

					// Also output the markup for the hover image if we have it
					// Make sure that we have the Featured Image component loaded
					if ( function_exists( 'pixelgrade_featured_image_get_hover_id' ) ) {
						$hover_image_id = pixelgrade_featured_image_get_hover_id();
						if ( ! empty( $hover_image_id ) ) { ?>

							<div class="c-card__frame-hover">
								<?php echo wp_get_attachment_image( $hover_image_id, 'full' ); ?>
							</div>

						<?php }
					}

					if ( pixelgrade_option( 'portfolio_items_title_position', 'regular' ) != 'overlay' ) {
						echo '<span class="c-card__letter">' . substr( get_the_title(), 0, 1 ) . '</span>';
					}
					?>
				</div><!-- .c-card__frame -->
			</div><!-- .c-card__aside -->
		<?php } ?>

		<div class="c-card__content">

			<?php
			/*
			 * Let's deal with the meta
			 */
			$portfolio_items_primary_meta   = pixelgrade_option( 'portfolio_items_primary_meta', 'types' );
			$portfolio_items_secondary_meta = pixelgrade_option( 'portfolio_items_secondary_meta', 'date' );

			$meta = pixelgrade_portfolio_get_project_meta();

			if ( ( $portfolio_items_primary_meta === 'none' || empty( $meta[ $portfolio_items_primary_meta ] ) ) &&
			     ( $portfolio_items_secondary_meta === 'none' || empty( $meta[ $portfolio_items_secondary_meta ] ) ) ) {
				// We have nothing to do regarding meta
			} else {
				$primary_meta   = $portfolio_items_primary_meta !== 'none' && ! empty( $meta[ $portfolio_items_primary_meta ] ) ? $meta[ $portfolio_items_primary_meta ] : '';
				$secondary_meta = $portfolio_items_secondary_meta !== 'none' && ! empty( $meta[ $portfolio_items_secondary_meta ] ) ? $meta[ $portfolio_items_secondary_meta ] : '';


				if ( $primary_meta || $secondary_meta ) { ?>

					<div class='c-card__meta'>

						<?php
						if ( $primary_meta ) {
							echo '<div class="c-meta__primary">' . $primary_meta . '</div>';

							if ( $secondary_meta ) {
								echo '<div class="c-card__separator"></div>';
							}
						}

						if ( $secondary_meta ) {
							echo '<div class="c-meta__secondary">' . $secondary_meta . '</div>';
						} ?>

					</div><!-- .c-card__meta -->

				<?php }
			}
			/*
			 * Finished with the meta
			 */

			if ( pixelgrade_option( 'portfolio_items_title_visibility', true ) ) { ?>
				<h2 class="c-card__title"><span><?php the_title(); ?></span></h2>
			<?php }

			if ( pixelgrade_option( 'portfolio_items_excerpt_visibility', true ) ) { ?>
				<div class="c-card__excerpt"><?php the_excerpt(); ?></div>
			<?php } ?>

		</div><!-- .c-card__content -->
		<a class="c-card__link" href="<?php the_permalink(); ?>"></a>
	</div>
</article><!-- #post-<?php the_ID(); ?> -->

<?php
/**
 * pixelgrade_after_loop_entry hook.
 *
 * @hooked nothing() - 10 (outputs nothing)
 */
do_action( 'pixelgrade_after_loop_entry', $location );
