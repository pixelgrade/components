<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// We first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location( 'product' );

if ( $cross_sells ) : ?>

	<div class="cross-sells">

		<h2><?php _e( 'You may be interested in&hellip;', 'woocommerce' ); ?></h2>

		<div class="c-gallery c-gallery--woocommerce c-gallery--cropped c-gallery--regular o-grid--variable">

			<?php foreach ( $cross_sells as $cross_sell ) : ?>

				<?php
				$post_object = get_post( $cross_sell->get_id() );
				setup_postdata( $GLOBALS['post'] =& $post_object );
				pixelgrade_the_card( 'woocommerce', $location ); ?>

			<?php endforeach; ?>

		</div>

	</div>

<?php endif;
wp_reset_postdata();
