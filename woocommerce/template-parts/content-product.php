<?php
/**
 * The template part used for displaying post content on archives
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/content.php` or in `/template-parts/blog/content.php`.
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
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $post, $product;

// We first need to know the bigger picture - the location this template part was loaded from
$location = pixelgrade_get_location( 'product' );

$primary_meta_output   = '';
$secondary_meta_output   = '';

$terms = get_the_terms( $post->ID, 'product_cat' );

if ( $terms && ! is_wp_error( $terms ) ) :
    if ( ! empty( $terms ) ) {
        $primary_meta_output = $terms[0]->name;
    }
endif;

?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="c-card">

			<?php
			/**
			 * pixelgrade_after_entry_start hook.
			 */
			do_action( 'pixelgrade_after_entry_start', $location );
			?>

            <div class="c-card__aside c-card__thumbnail-background">
                <div class="c-card__frame">
                    <?php
                        if ( has_post_thumbnail() ) {
                            the_post_thumbnail( 'pixelgrade_card_image' );
                        }
                    ?>

                    <div class="c-card__add-to-cart">
                        <?php
                        $class = 'c-btn  add_to_cart_button';
                        if ( $product->is_type( 'simple' ) ) {
                            $class .= '  ajax_add_to_cart';
                        }
                        woocommerce_template_loop_add_to_cart( array(
                            'class' => $class
                        ) ); ?>
                    </div>
                </div>
            </div>

			<div class="c-card__content">

				<?php
				if ( $primary_meta_output || $secondary_meta_output ) {
					?>

					<div class="c-card__meta c-meta">
						<?php
						do_action( 'pixelgrade_before_card_meta', $location );

						if ( $primary_meta_output ) {
							echo '<div class="c-meta__primary">' . $primary_meta_output . '</div>';
							// Add a separator if we also have secondary meta
							if ( $secondary_meta_output ) {
								echo '<div class="c-meta__separator js-card-meta-separator"></div>';
							}
						}

						if ( $secondary_meta_output ) {
							echo '<div class="c-meta__secondary">' . $secondary_meta_output . '</div>';
						}

						do_action( 'pixelgrade_after_card_meta', $location ); ?>
					</div>

					<?php
				}

				if ( pixelgrade_option( 'blog_items_title_visibility', true ) && get_the_title() ) { ?>
					<h2 class="c-card__title"><span><?php the_title(); ?></span></h2>
                <?php } ?>

                <div class="c-card__excerpt">
                    <div class="h4"><?php woocommerce_template_loop_price(); ?></div>
                </div>

			</div>

			<a class="c-card__link" href="<?php the_permalink(); ?>"></a>

            <?php woocommerce_show_product_loop_sale_flash(); ?>

			<?php
			/**
			 * pixelgrade_before_entry_end hook.
			 */
			do_action( 'pixelgrade_before_entry_end', $location );
			?>

		</div>

	</article>

<?php
/**
 * pixelgrade_after_loop_entry hook.
 *
 * @hooked nothing() - 10 (outputs nothing)
 */
do_action( 'pixelgrade_after_loop_entry', $location );
