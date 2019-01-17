<?php
/**
 * Custom functions that act independently of the component templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Woocommerce
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function pixelgrade_woocommerce_change_blog_component_config() {

	$page_default = array(
		'extend' => 'blog/page',
		'type' => 'loop',
		'checks' => array(
			'relation' => 'NOT',
			array(
				'callback' => 'is_woo_archive',
			),
		),
	);

	$page_default = Pixelgrade_BlocksManager()->maybeExtendBlock( $page_default );

	$page_shop = array(
		'type' => 'loop',
		'blocks' => array(
			'content' => array(
				'extend' => 'blog/container',
				'blocks' => array(
					'layout' => array(
						'extend'   => 'blog/layout',
						'classes' => 'o-layout  o-layout--blog',
						'blocks'   => array(
							'main' => array(
								'extend' => 'blog/main',
								'blocks' => array(
									'blog/entry-header-page',
									'blog/entry-content',
								),
							),
						),
					),
				),
			),
		),
		'checks' => array(
			array(
				'callback' => 'is_woo_archive',
			),
		),
	);

	Pixelgrade_BlocksManager()->registerBlock( 'blog/page', array(
		'blocks' => array(
			'default' => $page_default,
			'shop' => $page_shop,
		),
	) );

	$single_default = array(
		'extend' => 'blog/page',
		'type' => 'loop',
		'checks' => array(
			'relation' => 'NOT',
			array(
				'callback' => 'is_product',
			),
		),
	);

	$single_default = Pixelgrade_BlocksManager()->maybeExtendBlock( $single_default );

	$single_product = array(
		'type' => 'loop',
		'blocks' => array(
			'content' => array(
				'extend' => 'blog/container',
				'blocks' => array(
					'layout' => array(
						'extend'   => 'blog/layout',
						'classes' => 'o-layout  o-layout--blog',
						'blocks'   => array(
							'main' => array(
								'extend' => 'blog/main',
								'blocks' => array(
									'blog/entry-content',
								),
							),
						),
					),
				),
			),
		),
		'checks' => array(
			array(
				'callback' => 'is_product',
			),
		),
	);

	Pixelgrade_BlocksManager()->registerBlock( 'blog/single', array(
		'blocks' => array(
			'default' => $single_default,
			'product' => $single_product,
		),
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'blog/single-product', array(
		'type' => 'loop',
		'blocks' => array(
			'content' => array(
				'extend' => 'blog/container',
				'blocks' => array(
					'layout' => array(
						'extend'   => 'blog/layout',
						'classes' => 'o-layout  o-layout--blog',
						'blocks'   => array(
							'main' => array(
								'extend' => 'blog/main',
								'blocks' => array(
									'product-content' => array(
										'type'     => 'callback',
										'callback' => 'wc_get_template_part',
                                        'args' => array( 'content', 'single-product' ),
                                    ),
								),
							),
						),
					),
				),
			),
		),
    ) );
}

add_action( 'pixelgrade_blog_after_register_blocks', 'pixelgrade_woocommerce_change_blog_component_config', 30 );

function pixelgrade_woocommerce_add_template_part_paths( $template, $slug, $name ) {
	$located = pixelgrade_locate_template_part( $slug, 'woocommerce', $name );
	if ( $located ) {
		return $located;
	}
	return $template;

}
add_filter( 'wc_get_template_part', 'pixelgrade_woocommerce_add_template_part_paths', 30, 3 );

function pixelgrade_woocommerce_add_template_paths( $located, $template_name, $args, $template_path, $default_path ) {
	$located_components = pixelgrade_locate_template_part( $template_name, 'woocommerce' );
	if ( $located_components ) {
		return $located_components;
	}
	return $located;

}
add_filter( 'wc_get_template', 'pixelgrade_woocommerce_add_template_paths', 30, 5 );

function pixelgrade_woocommerce_template_loader_files( $templates, $default_file ) {
	if ( is_singular( 'product' ) ) {
        $templates[] = 'components/woocommerce/templates/single-product.php';
	}
	return $templates;
}
add_filter( 'woocommerce_template_loader_files', 'pixelgrade_woocommerce_template_loader_files', 30, 2 );

function pixelgrade_woocommerce_alter_loop_start( $loop_start ) {
	return '<div class="' . join( ' ', pixelgrade_get_blog_grid_class() ) . '">';
}
add_filter( 'woocommerce_product_loop_start', 'pixelgrade_woocommerce_alter_loop_start', 30, 1 );

function pixelgrade_woocommerce_alter_loop_end( $loop_end ) {
	return '</div>';
}
add_filter( 'woocommerce_product_loop_end', 'pixelgrade_woocommerce_alter_loop_end', 30, 1 );

function woocommerce_alter_entry_header_classes( $classes ) {
	if ( function_exists('is_woo_archive') && is_woo_archive() ) {
		$classes[] = 'entry-title--woocommerce';
	}
	return $classes;
}
add_filter( 'components_entry_header_classes', 'woocommerce_alter_entry_header_classes', 30, 1 );

function pixelgrade_woocommerce_comment_pagination_args( $args ) {
	$args['prev_text'] = esc_html_x( '&laquo; Previous', 'previous set of posts', '__theme_txtd' );
	$args['next_text'] = esc_html_x( 'Next &raquo;', 'next set of posts', '__theme_txtd' );
	return $args;
}
add_filter( 'woocommerce_comment_pagination_args', 'pixelgrade_woocommerce_comment_pagination_args', 30, 1 );
add_filter( 'woocommerce_pagination_args', 'pixelgrade_woocommerce_comment_pagination_args', 30, 1 );

function pixelgrade_remove_sidebar_class( $classes ) {
	if ( is_singular( 'product' ) ) {
		$classes = array_diff( $classes, array( 'has-sidebar' ) );
	}
	return $classes;
}
add_filter( 'body_class', 'pixelgrade_remove_sidebar_class', 30 );

function pixelgrade_product_header_area() {

}
add_action( 'woocommerce_single_product_summary', 'pixelgrade_product_header_area', 1 );

function pixelgrade_woocommerce_sale_flash( $sale_flash, $post, $product ) {
	return '<span class="c-btn  c-btn--sale-flash">' . esc_html__( 'Sale!', '__theme_txtd' ) . '</span>';
}
add_filter( 'woocommerce_sale_flash', 'pixelgrade_woocommerce_sale_flash', 3, 30 );

// hide tabs content titles
add_filter( 'woocommerce_product_description_heading', '__return_false', 30 );
add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 30 );

function pixelgrade_start_wrapper_before_single_product_summary() {
	echo '<div class="c-product-main">';
}
add_action( 'woocommerce_before_single_product_summary', 'pixelgrade_start_wrapper_before_single_product_summary', 1 );

function pixelgrade_end_wrapper_after_single_product_summary() {
	echo '</div>';
}
add_action( 'woocommerce_after_single_product_summary', 'pixelgrade_end_wrapper_after_single_product_summary', 1 );

function pixelgrade_add_cart_before_header() {
	ob_start(); ?>
	<div class="c-mini-cart">
		<div class="c-mini-cart__overlay"></div>
		<div class="c-mini-cart__flyout">
            <div class="c-mini-cart__header">
                <h5 class="c-mini-cart__title"><?php echo esc_html__( 'Your cart', '__theme_txtd' ); ?></h5>
                <div class="c-mini-cart__close"></div>
            </div>
			<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
		</div>
	</div>
	<?php echo ob_get_clean();
}
add_action( 'pixelgrade_before_header', 'pixelgrade_add_cart_before_header', 1 );

// This theme doesn't have a traditional sidebar. We use BLOCKS to build stuff.
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

