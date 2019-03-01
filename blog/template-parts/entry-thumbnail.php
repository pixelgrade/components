<?php
/**
 * The template part used for displaying the entry thumbnail.
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/entry-thumbnail.php` or in `/template-parts/blog/entry-thumbnail.php`.
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
 * @package    Components/Blog
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// We first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location();
?>

<?php if ( has_post_thumbnail() ) { ?>

	<?php
	/**
	 * pixelgrade_before_entry_thumbnail hook.
	 */
	do_action( 'pixelgrade_before_entry_thumbnail', $location );
	?>
	<!-- pixelgrade_before_entry_thumbnail -->

	<div class="entry-thumbnail">
		<div>

			<?php
			/**
			 * pixelgrade_before_entry_thumbnail_content hook.
			 */
			do_action( 'pixelgrade_before_entry_thumbnail_content', $location );
			?>
			<!-- pixelgrade_before_entry_thumbnail_content -->

			<?php the_post_thumbnail( 'pixelgrade_single_' . pixelgrade_get_post_thumbnail_aspect_ratio_class() ); ?>

			<?php
			/**
			 * pixelgrade_after_entry_thumbnail_content hook.
			 */
			do_action( 'pixelgrade_after_entry_thumbnail_content', $location );
			?>
			<!-- pixelgrade_after_entry_thumbnail_content -->
		</div>
	</div><!-- .entry-thumbnail -->

	<?php
	/**
	 * pixelgrade_after_entry_thumbnail hook.
	 */
	do_action( 'pixelgrade_after_entry_thumbnail', $location );
	?>
	<!-- pixelgrade_after_entry_thumbnail -->

<?php }
