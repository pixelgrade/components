<?php
/**
 * The template part used for displaying post content on archives
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/content.php` or in `/template-parts/blog/content.php`.
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
 * @package    Components/Blog
 * @version    1.0.0
 */
?>

<div class="c-card">

	<?php if ( pixelgrade_display_featured_images() ) { ?>

		<div class="c-card__aside c-card__thumbnail-background">
			<div class="c-card__frame">
				<?php if ( has_post_thumbnail() ) {
					the_post_thumbnail();
				}

				if ( pixelgrade_option( 'blog_items_title_position', 'regular' ) != 'overlay' ) {
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
		$blog_items_primary_meta   = pixelgrade_option( 'blog_items_primary_meta', 'category' );
		$blog_items_secondary_meta = pixelgrade_option( 'blog_items_secondary_meta', 'date' );

		$meta = pixelgrade_get_post_meta();

		// Let's determine if we really need to do anything
		if ( ( $blog_items_primary_meta === 'none' || empty( $meta[ $blog_items_primary_meta ] ) ) &&
		     ( $blog_items_secondary_meta === 'none' || empty( $meta[ $blog_items_secondary_meta ] ) ) ) {
			// We have nothing to do regarding meta
		} else {
			$primary_meta   = $blog_items_primary_meta !== 'none' && ! empty( $meta[ $blog_items_primary_meta ] ) ? $meta[ $blog_items_primary_meta ] : '';
			$secondary_meta = $blog_items_secondary_meta !== 'none' && ! empty( $meta[ $blog_items_secondary_meta ] ) ? $meta[ $blog_items_secondary_meta ] : '';

			if ( $primary_meta || $secondary_meta ) { ?>

				<div class='c-meta c-card__meta'>

					<?php
					if ( $primary_meta ) {
						echo '<div class="c-card__meta-primary">' . $primary_meta . '</div>';
						// Add a separator if we also have secondary meta
						if ( $secondary_meta ) {
							echo '<div class="c-card__meta-separator"></div>';
						}
					}

					if ( $secondary_meta ) {
						echo '<div class="c-card__meta-secondary">' . $secondary_meta . '</div>';
					} ?>

				</div><!-- .c-meta.c-card__meta -->

			<?php }
		}

		if ( pixelgrade_option( 'blog_items_title_visibility', true ) && get_the_title() ) { ?>
			<h2 class="c-card__title"><span><?php the_title(); ?></span></h2>
		<?php }

		if ( pixelgrade_option( 'blog_items_excerpt_visibility', true ) ) { ?>
			<div class="c-card__excerpt"><?php the_excerpt(); ?></div>
		<?php } ?>

		<div class="c-card__action"><?php esc_html_e( 'Read More', 'components_txtd' ); ?></div>

	</div><!-- .c-card__content -->

	<a class="c-card__link" href="<?php the_permalink(); ?>">
		<span class="screen-reader-text">
			<?php esc_html_e( 'Read More', 'components_txtd' ); ?>
		</span>
	</a>
	<div class="c-card__badge"></div>

</div><!-- .c-card -->
