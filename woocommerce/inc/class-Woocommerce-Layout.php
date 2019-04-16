<?php
/**
 * This is the class that handles the Layout behaviour of our Woocommerce component.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Pixelgrade_Woocommerce_Layout extends Pixelgrade_Singleton {

	/**
	 * The main component object (the parent).
	 *
	 * @var     Pixelgrade_Woocommerce
	 * @access  public
	 * @since     1.0.0
	 */
	public $parent = null;

	/**
	 * Pixelgrade_Woocommerce_Layout constructor.
	 *
	 * @param Pixelgrade_Woocommerce $parent
	 */
	public function __construct( $parent ) {
		$this->parent = $parent;

		// Register our actions and filters
		$this->registerHooks();
	}

	/**
	 * Register our actions and filters
	 */
	public function registerHooks() {

		add_filter( 'wc_get_template_part', array( $this, 'addTemplatePartPaths' ), 30, 3 );
		add_filter( 'wc_get_template', array( $this, 'addTemplatePaths' ), 30, 5 );

		add_filter( 'woocommerce_template_loader_files', array( $this, 'addTemplateLoaderFiles' ), 30, 2 );
		add_filter( 'woocommerce_product_loop_start', array( $this, 'alterLoopStart' ), 30, 1 );
		add_filter( 'woocommerce_product_loop_end', array( $this, 'alterLoopEnd' ), 30, 1 );
		add_filter( 'woocommerce_comment_pagination_args', array( $this, 'alterPaginationArgs' ), 30, 1 );
		add_filter( 'woocommerce_pagination_args', array( $this, 'alterPaginationArgs' ), 30, 1 );
		add_filter( 'woocommerce_sale_flash', array( $this, 'changeSaleFlashMarkup' ), 30, 3 );

		// hide tabs content titles
		add_filter( 'woocommerce_product_description_heading', '__return_false', 30 );
		add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 30 );

		// This theme doesn't have a traditional sidebar. We use BLOCKS to build stuff.
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
		remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );

		add_action( 'woocommerce_checkout_billing', array( $this, 'outputCheckoutSiteIdentity' ), 1 );
		add_action( 'woocommerce_checkout_billing', array( $this, 'outputCheckoutBreadcrumbs' ), 2 );
		add_action( 'woocommerce_checkout_billing', 'woocommerce_checkout_coupon_form', 10 );

		add_filter( 'body_class', array( $this, 'removeSidebarClass' ), 30 );
		add_filter( 'components_entry_header_classes', array( $this, 'alterEntryHeaderClassList' ), 30, 1 );

		add_action( 'woocommerce_before_single_product_summary', array(
			$this,
			'addStartWrapperBeforeSingleProductSummary'
		), 1 );
		add_action( 'woocommerce_after_single_product_summary', array(
			$this,
			'addEndWrapperAfterSingleProductSummary'
		), 1 );
		add_action( 'pixelgrade_before_header', array( $this, 'outputMiniCart' ), 1 );

		// add various opening and closing tags to wrap upsells and related products

		// before and after upsells (priority 10)
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'addStartWrapperBeforeTabs' ), 9 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'addEndWrapperAfterTabs' ), 11 );

		// before and after upsells (priority 15)
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'addStartWrapperBeforeUpsells' ), 14 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'addEndWrapperAfterUpsells' ), 16 );

		// before and after related (priority 20)
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'addStartWrapperBeforeRelated' ), 19 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'addEndWrapperAfterRelated' ), 21 );

		//
		add_filter( 'pixelgrade_footer_auto_output_footer', array( $this, 'removeFooterFromCheckout' ), 10 );
		add_filter( 'pixelgrade_header_auto_output_header', array( $this, 'removeHeaderFromCheckout' ), 10 );

		add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'outputAjaxAddToCartButton' ) );

		remove_action( 'woocommerce_before_subcategory', 'woocommerce_template_loop_category_link_open', 10 );
		add_action( 'woocommerce_after_subcategory', array( $this, 'woocommerceTemplateLoopCategoryLinkOpen' ), 5 );

		add_filter( 'wp_nav_menu_items', array( $this, 'appendCartIconToMenu' ), 10, 2 );
		add_filter( 'woocommerce_review_gravatar_size', array( $this, 'changeReviewAvatarSize' ), 10 );

		add_action( 'woocommerce_single_product_summary', array( $this, 'singleProductCategory' ), 4 );

		add_action( 'woocommerce_single_product_summary', array( $this, 'singleProductHeaderStart' ), 3 );
		add_action( 'woocommerce_single_product_summary', array( $this, 'singleProductHeaderEnd' ), 11 );

		add_action( 'pixelgrade_before_card_frame_end', array( $this, 'appendSaleFlashToCard' ) );
		add_action( 'pixelgrade_before_card_frame_end', array( $this, 'appendAddToCartToCardAside' ) );
	}

	public function outputAjaxAddToCartButton() {
		if ( 'product' !== get_post_type() ) {
			return;
		}

		$product = wc_get_product();

		if ( $product->is_type( 'simple' ) ) {
			woocommerce_template_loop_add_to_cart( array(
				'class' => 'c-btn  add_to_cart_button  ajax_add_to_cart'
			) );
		}
	}

	public function addTemplatePartPaths( $template, $slug, $name ) {
		$located = pixelgrade_locate_template_part( $slug, 'woocommerce', $name );
		if ( $located ) {
			return $located;
		}

		return $template;
	}

	public function addTemplatePaths( $located, $template_name, $args, $template_path, $default_path ) {
		$located_components = pixelgrade_locate_template_part( $template_name, 'woocommerce' );
		if ( $located_components ) {
			return $located_components;
		}

		return $located;
	}

	public function addTemplateLoaderFiles( $templates, $default_file ) {
		if ( is_singular( 'product' ) ) {
			$templates[] = 'components/woocommerce/templates/single-product.php';
		} elseif ( is_woo_archive() ) {
			$templates[] = 'components/woocommerce/templates/archive-product.php';
		}

		return $templates;
	}

	public function alterLoopStart( $loop_start ) {
		return '<div class="' . join( ' ', pixelgrade_get_woocommerce_grid_class() ) . '">'; // WPCS: XSS OK.
	}

	public function alterLoopEnd( $loop_end ) {
		return '</div>';
	}

	public function alterPaginationArgs( $args ) {
		$args['prev_text'] = esc_html_x( '&laquo; Previous', 'previous set of posts', '__components_txtd' );
		$args['next_text'] = esc_html_x( 'Next &raquo;', 'next set of posts', '__components_txtd' );

		return $args;
	}

	public function changeSaleFlashMarkup( $sale_flash, $post, $product ) {
		return '<span class="c-btn  c-btn--sale-flash">' . esc_html__( 'Sale!', '__components_txtd' ) . '</span>';
	}

	public function alterEntryHeaderClassList( $classes ) {
		if ( is_woo_archive() ) {
			$classes[] = 'entry-title--woocommerce';
		}

		if ( is_cart() ) {
			$classes   = array_diff( $classes, array( 'h0' ) );
			$classes[] = 'h1';
		}

		return $classes;
	}

	public function removeSidebarClass( $classes ) {
		if ( is_product() ) {
			$classes = array_diff( $classes, array( 'has-sidebar' ) );
		}

		return $classes;
	}

	public function addStartWrapperBeforeSingleProductSummary() {
		echo '<div class="c-product-main">';
	}

	public function addEndWrapperAfterSingleProductSummary() {
		echo '</div>';
	}

	public function addStartWrapperBeforeTabs() {
		echo '<div class="c-woo-section  c-woo-tabs">';
	}

	public function addEndWrapperAfterTabs() {
		echo '</div>';
	}

	public function addStartWrapperBeforeUpsells() {
		echo '<div class="c-woo-section  c-woo-upsells">';
	}

	public function addEndWrapperAfterUpsells() {
		echo '</div>';
	}

	public function addStartWrapperBeforeRelated() {
		echo '<div class="c-woo-section  c-woo-related">';
	}

	public function addEndWrapperAfterRelated() {
		echo '</div>';
	}

	public function outputMiniCart() {
		if ( ! is_cart() ) {
			ob_start(); ?>
            <div class="c-mini-cart">
                <div class="c-mini-cart__overlay"></div>
                <div class="c-mini-cart__flyout">
                    <div class="c-mini-cart__header">
                        <h5 class="c-mini-cart__title"><?php echo esc_html__( 'Your cart', '__components_txtd' ); ?></h5>
                        <div class="c-mini-cart__close"></div>
                    </div>
					<?php the_widget( 'WC_Widget_Cart', 'title=' ); ?>
                </div>
            </div>
			<?php echo ob_get_clean(); // WPCS: XSS OK.
		}
	}

	public function removeHeaderFromCheckout( $allow ) {
		if ( is_checkout() ) {
			$allow = false;
		}

		return $allow;
	}

	public function removeFooterFromCheckout( $allow ) {
		if ( is_checkout() ) {
			$allow = false;
		}

		return $allow;
	}

	public function woocommerceTemplateLoopCategoryLinkOpen( $category ) {
		echo '<a class="c-card__link" href="' . esc_url( get_term_link( $category, 'product_cat' ) ) . '">';
	}

	public function appendCartIconToMenu( $items, $args ) {
		$cart_item_count = WC()->cart->get_cart_contents_count();
		$cart_count_span = '';

		if ( $cart_item_count ) {
			$cart_count_span = '<div class="cart-count"><span>' . $cart_item_count . '</span></div>';
		}

		$cart_link               = apply_filters( 'pixelgrade_cart_menu_item_markup', '<li class="menu-item  menu-item--cart"><a class="js-open-cart" href="' . esc_url( get_permalink( wc_get_page_id( 'cart' ) ) ) . '">' . esc_html__( 'My Cart', '__components_txtd' ) . $cart_count_span . '</a></li>' );
		$cart_menu_item_location = apply_filters( 'pixelgrade_cart_menu_item_location', 'primary-left' );

		// Add the cart link to the end of the menu.
		if ( $args->theme_location === $cart_menu_item_location ) {
			$items = $items . $cart_link;
		}

		return $items;
	}

	public function changeReviewAvatarSize( $size ) {
		$size = 80;

		return $size;
	}

	public function singleProductCategory() {
		global $product;

		echo '<div class="woocommerce-product-category c-meta__primary">';
		echo wc_get_product_category_list( $product->get_id(), ' / ' ); // WPCS: XSS OK.
		echo '</div>';
	}

	public function singleProductHeaderStart() {
		echo '<div class="woocommerce-product-header">';
	}

	public function singleProductHeaderEnd() {
		echo '</div>';
	}

	public function appendAddToCartToCardAside() {

		if ( 'product' !== get_post_type() ) {
			return;
		}

		$product = wc_get_product();
		$class = 'c-btn  add_to_cart_button';

		if ( $product->is_type( 'simple' ) ) {
			$class .= '  ajax_add_to_cart';
		} ?>

        <div class="c-card__add-to-cart">
			<?php woocommerce_template_loop_add_to_cart( array( 'class' => $class ) ); ?>
        </div>
	<?php }

	public function appendSaleFlashToCard() {

		if ( 'product' !== get_post_type() ) {
			return;
		}

		woocommerce_show_product_loop_sale_flash();
    }

    public function outputCheckoutSiteIdentity() {
	    echo '<h1 class="woocommerce-checkout-title"><span>'. get_bloginfo( 'name' ) .'</span></h1>';
    }

    public function outputCheckoutBreadcrumbs() {
	    ob_start(); ?>
        <ul class="woocommerce-checkout-breadcrumbs">
            <li><a href="<?php echo wc_get_cart_url(); ?>"><?php _e( 'Cart', '__components_txtd' ); ?></a></li>
            <li><?php _e( 'Checkout', '__components_txtd' ); ?></li>
        </ul>
	    <?php echo ob_get_clean();
    }
}
