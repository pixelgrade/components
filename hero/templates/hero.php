<?php
/**
 * The main template for heroes
 *
 * This template can be overridden by copying it to a child theme in /components/hero/templates/hero.php
 * or in the same theme by putting it in template-parts/hero/templates/hero.php.
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Hero
 * @version     1.0.6
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// We first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location();

// We might be on a page set as a page for posts and the $post will be the first post in the loop
// So we check first
if ( is_home() ) {
	// find the id of the page for posts
	$post_id = get_option( 'page_for_posts' );
}

// Get the global post if we have none so far
if ( empty( $post_id ) ) {
	$post_id = get_the_ID();
} ?>

<?php if ( pixelgrade_hero_is_hero_needed( $location ) ) : ?>

	<div <?php pixelgrade_hero_class( '', $location ); pixelgrade_hero_background_color_style( $post_id ); ?>>

		<div class="c-hero__slider"	<?php pixelgrade_hero_slider_attributes( '', $post_id ); ?>>

			<?php
			// get all the images/videos/featured projects ids that we will use as slides (we also cover for when there are none)
			$slides = pixelgrade_hero_get_slides_ids( $post_id );

			// First go through all the attachments (images and/or videos) and add them as slides
			// the first slide we encounter is obviously the first one
			$first_slide = true;

			// Loop through each slide ( the first one is kinda special )
			foreach ( $slides as $key => $attachment_id ) : ?>

			<div class="c-hero__slide" <?php pixelgrade_hero_background_color_style( $post_id ); ?>>

				<div class="c-hero__background  c-hero__layer" data-rellax data-rellax-scale="1.2">

					<?php
					$hero_image_opacity = get_post_meta( $post_id, '_hero_image_opacity', true );
					pixelgrade_hero_the_slide_background( $attachment_id, $hero_image_opacity ); // Output the background image of the slide
					?>

				</div><!-- .c-hero__background -->

				<?php
				// we only show the hero description on the first slide
				if ( true === $first_slide ) :

					/**
					 * pixelgrade_hero_before_content_wrapper hook.
					 */
					do_action( 'pixelgrade_hero_before_content_wrapper', $location, $post_id );

					// First the hero content/description
					if ( ! class_exists( 'PixTypesPlugin' ) ) {
                        $description = '<h1 class="h0">[Page Title]</h1>';
						$description_alignment = '';
					} else {
						$description           = get_post_meta( $post_id, '_hero_content_description', true );
						$description_alignment = get_post_meta( $post_id, '_hero_description_alignment', true );
					}

					if ( ! empty( $description ) ) { ?>

					<div <?php pixelgrade_hero_wrapper_class( $description_alignment ); ?>>

						<?php
						/**
						 * pixelgrade_hero_before_content hook.
						 */
						do_action( 'pixelgrade_hero_before_content', $location, $post_id );
						?>

						<div class="c-hero__content">
							<?php pixelgrade_hero_the_description( $description ); ?>
						</div><!-- .c-hero__content -->

						<?php
						/**
						 * pixelgrade_hero_after_content hook.
						 */
						do_action( 'pixelgrade_hero_after_content', $location, $post_id );
						?>

					</div><!-- .c-hero__wrapper -->

					<?php }

					// remember that we are done with the first slide
					$first_slide = false;

					/**
					 * pixelgrade_hero_after_content hook.
					 */
					do_action( 'pixelgrade_hero_after_content_wrapper', $location, $post_id );
				endif; ?>

			</div><!-- .c-hero__slide -->

			<?php endforeach; ?>

		</div><!-- .c-hero__slider -->

	</div><!-- .c-hero -->

<?php endif; // if ( pixelgrade_hero_is_hero_needed() ) ?>
