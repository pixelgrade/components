<?php if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// We first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location( 'product' );

if ( $related_products ) : ?>

	<section class="related products">

		<h2><?php esc_html_e( 'Related products', 'woocommerce' ); ?></h2>

		<div class="c-gallery c-gallery--woocommerce c-gallery--cropped c-gallery--regular o-grid--variable">

		<?php foreach ( $related_products as $related_product ) : ?>

			<?php
			$post_object = get_post( $related_product->get_id() );
			setup_postdata( $GLOBALS['post'] =& $post_object );
			pixelgrade_the_card( 'woocommerce', $location ); ?>

		<?php endforeach; ?>

		</div>


	</section>

<?php endif;
wp_reset_postdata();
