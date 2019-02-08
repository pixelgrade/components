<?php
/**
 * The template part used for displaying post content on archives
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/content-product_cat.php` or in `/template-parts/woocommerce/content-product_cat.php`.
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

ob_start();
do_action( 'woocommerce_before_subcategory_title', $category );
$thumbnail = ob_get_clean();
$class = '';

if ( $thumbnail ) {
    $class = 'has-post-thumbnail';
}

?>

<article <?php wc_product_cat_class( $class, $category ); ?>>

    <div class="c-card">

        <?php do_action( 'woocommerce_before_subcategory', $category ); ?>

        <div class="c-card__aside c-card__thumbnail-background  has-post-thumbnail">
            <div class="c-card__frame">
                <?php echo $thumbnail; ?>
            </div>
        </div>

        <div class="c-card__content">
            <h2 class="c-card__title"><span><?php do_action( 'woocommerce_shop_loop_subcategory_title', $category ); ?></span></h2>
            <?php do_action( 'woocommerce_after_subcategory_title', $category ); ?>
        </div>

        <?php do_action( 'woocommerce_after_subcategory', $category ); ?>

    </div>

</article>
