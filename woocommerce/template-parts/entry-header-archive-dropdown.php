<?php
/**
 * The template part used for displaying the entry header with categories in dropdown
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/entry-header-archive.php` or in `/template-parts/woocommerce/entry-header-archive.php`.
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
 * @package    Components/Woocommerce
 * @version    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<header class="woocommerce-header c-page-header">

	<?php
	$archive_page_title = woocommerce_page_title( false );
	if ( ! empty( $archive_page_title ) && apply_filters( 'woocommerce_show_page_title', true ) ) { ?>
		<h1 class="woocommerce-products-header__title entry-title"><?php echo wp_kses( $archive_page_title, wp_kses_allowed_html() ); ?></h1>
	<?php }

	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );

	if ( is_woo_archive() ) {
		pixelgrade_the_taxonomy_dropdown( 'product_cat' );
	} ?>

</header>
