<?php
/**
 * The template used for displaying archive loops
 *
 * This template can be overridden by copying it to a child theme
 * or in the same theme by putting it in template-parts/loop.php.
 * @see pixelgrade_locate_component_template_part()
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author        Pixelgrade
 * @package    Components/Base
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//we first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location(); ?>

<?php
/**
 * pixelgrade_before_loop hook.
 *
 * @hooked nothing - 10 (outputs nothing)
 */
do_action( 'pixelgrade_before_loop', $location );
?>

<?php if ( have_posts() ) : /* Start the Loop */ ?>

	<div <?php pixelgrade_posts_container_id( $location ); ?> <?php pixelgrade_blog_class( '', $location ); ?>>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php
			/**
			 * Include the Post-Format-specific template for the content.
			 * If you want to override this in a child theme, then include a file
			 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
			 */
			pixelgrade_get_component_template_part( Pixelgrade_Base::COMPONENT_SLUG, 'content', get_post_format(), true ); ?>
		<?php endwhile; ?>
	</div>
	<?php the_posts_navigation(); ?>

<?php else: ?>

	<?php pixelgrade_get_component_template_part( Pixelgrade_Base::COMPONENT_SLUG, 'content', 'none', true ); ?>

<?php endif; ?>

<?php
/**
 * pixelgrade_before_entry_article_end hook.
 */
do_action( 'pixelgrade_before_entry_article_end', $location );
?>
<!-- pixelgrade_before_entry_article_end -->

<?php
/**
 * pixelgrade_after_loop hook.
 *
 * @hooked nothing - 10 (outputs nothing)
 */
do_action( 'pixelgrade_after_loop', $location );
