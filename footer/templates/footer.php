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
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Footer
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$copyright_text = pixelgrade_option( 'copyright_text' );

if ( is_active_sidebar( 'sidebar-footer' ) || ! empty( $copyright_text ) ) { ?>

    <footer <?php pixelgrade_footer_class(); ?>>
        <div class="o-wrapper u-container-width content-area">

	        <?php
                /**
                 * pixelgrade_footer_before_widgets hook.
                 */
                do_action( 'pixelgrade_footer_before_widgets', 'footer' );
	        ?>

            <?php if ( is_active_sidebar( 'sidebar-footer' ) ): ?>
                <div class="c-gallery c-gallery--footer o-grid o-grid--4col-@lap">
					<?php dynamic_sidebar( 'sidebar-footer' ); ?>
                </div><!-- .c-gallery--footer -->
			<?php endif; ?>

	        <?php
                /**
                 * pixelgrade_footer_before_widgets hook.
                 */
                do_action( 'pixelgrade_footer_before_widgets', 'footer' );
	        ?>

            <div class="c-footer__content">
				<?php
				if ( ! empty( $copyright_text ) ) {
					// We need to parse some tags
					// like %year%
					$copyright_text = str_replace( '%year%', date( 'Y' ), $copyright_text );
					echo do_shortcode( $copyright_text );
				}
				if ( ! pixelgrade_option( 'footer_hide_back_to_top_link' ) ) { ?>
                    <a class="back-to-top" href="#"><?php _e( 'Back to Top', 'components' ); ?></a>
				<?php } ?>
            </div><!-- .c-footer__content -->

	        <?php
                /**
                 * pixelgrade_footer_before_widgets hook.
                 */
                do_action( 'pixelgrade_footer_after_content', 'footer' );
	        ?>
        </div><!-- .o-wrapper.u-container-width.content-area -->
    </footer>

<?php } ?>