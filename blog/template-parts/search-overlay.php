<?php
/**
 * The template used for the Search Overlay
 *
 * @package Vasco
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<div class="c-search-overlay  u-content-background">
	<div class="c-search-overlay__content">
		<?php get_search_form(); ?>
		<p class="c-search-overlay__description">
			<?php esc_html_e( 'Begin typing your search above and press return to search.', '__theme_txtd' ); ?>
			<span class="hide-on-mobile"><?php esc_html_e( 'Press Esc to cancel.', '__theme_txtd' ); ?></span>
		</p>
	</div><!-- .c-search-overlay__content -->
	<button class="c-search-overlay__close-button  js-search-close  u-header-sides-spacing">
		<span class="screen-reader-text"><?php esc_html_e( 'Close overlay search', '__theme_txtd' ) ?></span>
		<?php get_template_part( 'template-parts/svg/icon-close' ); ?>
	</button>
</div><!-- .c-search-overlay -->
