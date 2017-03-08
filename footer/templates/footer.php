<?php
/**
 * The main template for footer
 *
 * This template can be overridden by copying it to a child theme in /components/footer/templates/footer.php
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author        Pixelgrade
 * @package    Components/Footer
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<footer <?php pixelgrade_footer_class(); ?>>
	<div class="o-wrapper u-container-width content-area">

		<?php
		/**
		 * pixelgrade_footer_before_content hook.
		 */
		do_action( 'pixelgrade_footer_before_content', 'footer' );
		?>

        <?php pxg_load_component_file( 'footer', 'templates/content-footer', '', false ); ?>

		<?php
		/**
		 * pixelgrade_footer_after_content hook.
		 */
		do_action( 'pixelgrade_footer_after_content', 'footer' );
		?>
	</div><!-- .o-wrapper.u-container-width.content-area -->
</footer>
