<?php
/**
 * The template part used for displaying the single product review meta.
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in the root `/template-parts/single-product/review-meta.php` or in `/template-parts/woocommerce/single-product/review-meta.php`.
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

global $comment;
$verified = wc_review_is_from_verified_owner( $comment->comment_ID );

if ( '0' === $comment->comment_approved ) { ?>

	<p class="meta">
		<em class="woocommerce-review__awaiting-approval">
			<?php esc_html_e( 'Your review is awaiting approval', '__components_txtd' ); ?>
		</em>
	</p>

<?php } else { ?>

	<p class="meta">
		<strong class="woocommerce-review__author"><?php comment_author(); ?> </strong>
		<?php
		if ( 'yes' === get_option( 'woocommerce_review_rating_verification_label' ) && $verified ) {
			echo '<em class="woocommerce-review__verified verified">(' . esc_attr__( 'verified owner', '__components_txtd' ) . ')</em> ';
		}

		?>
		<span class="woocommerce-review__dash"><?php esc_html_e( 'on', '__theme_txtd' ); ?></span> <time class="woocommerce-review__published-date" datetime="<?php echo esc_attr( get_comment_date( 'c' ) ); ?>"><?php echo esc_html( get_comment_date( wc_date_format() ) ); ?></time>
	</p>

	<?php
}
