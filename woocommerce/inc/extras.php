<?php
/**
 * Custom functions that act independently of the component templates.
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

function pixelgrade_woocommerce_change_blog_component_config() {

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/main', array(
		'extend' => 'blog/main',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/layout', array(
		'extend'   => 'blog/layout',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/container', array(
		'extend'   => 'blog/container',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/loop-none', array(
		'extend'   => 'blog/loop-none',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/loop-pagination', array(
		'extend'   => 'blog/loop-pagination',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/grid-item', array(
		'type'      => 'template_part',
		'templates' => array(
			array(
				'component_slug' => 'woocommerce',
				'slug'           => 'content-product'
			),
		),
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/loop-posts', array(
		'blocks' => array(

			'before-loop' => array(
				'type' => 'callback',
				'callback' => 'do_action',
				'args' => array( 'woocommerce_before_shop_loop' ),
			),
			'loop-start' => array(
				'type' => 'callback',
				'callback' => 'woocommerce_product_loop_start',
			),

			'posts' => array(
				'type' => 'loop',
				'blocks' => array(
					'woocommerce/grid-item'
				),
			),

			'loop-end' => array(
				'type' => 'callback',
				'callback' => 'woocommerce_product_loop_end',
			),
			'after-loop' => array(
				'type' => 'callback',
				'callback' => 'do_action',
				'args' => array( 'woocommerce_after_shop_loop' ),
			),
			'woocommerce/loop-pagination',
		),
		'checks' => array(
			array(
				'callback' => 'have_posts',
				'args'     => array(),
			),
		),
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/entry-header-archive', array(
		'type'      => 'template_part',
		'templates' => array(
			array(
				'component_slug' => 'woocommerce',
				'slug' => 'entry-header',
				'name' => 'archive',
			),
		),
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/archive-product', array(
		'extend' => 'woocommerce/container',
		'blocks' => array(
			'layout' => array(
				'extend' => 'woocommerce/layout',
				'blocks' => array(
					'main' => array(
						'extend' => 'blog/main',
						'blocks' => array(
							'woocommerce/entry-header-archive',
							'content' => array(
								'blocks' => array(
									'woocommerce/loop-posts',
									'woocommerce/loop-none'
								),
								'wrappers' => array(
									array(
										'classes' => 'u-content-width'
									),
									array(
										'classes' => 'woocommerce'
									),
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
    ) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/page', array(
        'extend' => 'blog/page',
        'checks' => array(
            array(
                'callback' => 'is_cart',
                'compare' => 'NOT'
            ),
            array(
                'callback' => 'is_checkout',
                'compare' => 'NOT'
            ),
            array(
                'callback' => 'is_woo_archive',
                'compare' => 'NOT'
            ),
            array(
                'callback' => 'is_singular',
                'args' => array( 'product' ),
                'compare' => 'NOT'
            ),
        ),
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/cart', array(
		'extend' => 'woocommerce/container',
		'blocks' => array(
			'layout' => array(
				'extend' => 'woocommerce/layout',
				'blocks' => array(
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
        'checks' => array(
            array(
                'callback' => 'is_cart',
            ),
        ),
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/checkout', array(
		'extend' => 'woocommerce/container',
		'blocks' => array(
			'layout' => array(
				'extend' => 'woocommerce/layout',
				'blocks' => array(
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
        'checks' => array(
            array(
                'callback' => 'is_checkout',
            ),
        ),
	) );

	// rendered in components/woocommerce/single-product
	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/single-product', array(
		'extend' => 'woocommerce/container',
		'type' => 'loop',
		'blocks' => array(
			'layout' => array(
				'extend' => 'woocommerce/layout',
				'blocks' => array(
					'main' => array(
						'extend' => 'woocommerce/main',
						'blocks' => array(
							'content' => array(
								'type'     => 'callback',
								'callback' => 'wc_get_template_part',
								'args' => array( 'content', 'single-product' ),
							),
						),
					),
				),
			),
		),
	) );

	Pixelgrade_BlocksManager()->getRegisteredBlock('woocommerce/single-product' );

	Pixelgrade_BlocksManager()->registerBlock( 'blog/page', array(
		'blocks' => array(
			'woocommerce/cart',
			'woocommerce/checkout',
			'woocommerce/page',
			'woocommerce/archive-product',
		),
	) );
}

add_action( 'pixelgrade_blog_after_register_blocks', 'pixelgrade_woocommerce_change_blog_component_config', 30 );
