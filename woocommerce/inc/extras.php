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

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/main', array(
		'extend' => 'blog/main',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/layout', array(
		'extend'   => 'blog/layout',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/container', array(
		'extend'   => 'blog/container',
	) );

	Pixelgrade_BlocksManager()->registerBlock( 'woocommerce/loop', array(
		'extend' => 'blog/loop',
		'blocks' => array(
			'loop-posts' => array(
				'blocks'   => array(
					'grid-item' => array(
						'type'     => 'callback',
						'callback' => 'pixelgrade_get_template_part',
						'args' => array( 'content', 'product' ),
					),
				),
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
							'blog/entry-header-archive',
							'blog/loop', // These two are mutually exclusive
							'blog/loop-none',
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
			'woocommerce/page',
			'woocommerce/archive-product',
		),
	) );
}

add_action( 'pixelgrade_blog_after_register_blocks', 'pixelgrade_woocommerce_change_blog_component_config', 30 );



