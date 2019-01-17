<?php
/**
 * This is the class that handles the Layout behaviour of our Woocommerce component.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Woocommerce
 * @version    1.0.1
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

		add_filter( 'wc_get_template_part', array( $this, 'add_template_part_paths' ), 30, 3 );
		add_filter( 'wc_get_template', array( $this, 'add_template_paths' ), 30, 5 );

		add_filter( 'woocommerce_template_loader_files', array( $this, 'add_template_loader_files' ), 30, 2 );
		add_filter( 'woocommerce_product_loop_start', array( $this, 'alter_loop_start' ), 30, 1 );
		add_filter( 'woocommerce_product_loop_end', array( $this, 'alter_loop_end' ), 30, 1 );
		add_filter( 'woocommerce_comment_pagination_args', array( $this, 'alter_pagination_args' ), 30, 1 );
		add_filter( 'woocommerce_pagination_args', array( $this, 'alter_pagination_args' ), 30, 1 );
		add_filter( 'woocommerce_sale_flash', array( $this, 'change_sale_flash_markup' ), 3, 30 );

		// hide tabs content titles
		add_filter( 'woocommerce_product_description_heading', '__return_false', 30 );
		add_filter( 'woocommerce_product_additional_information_heading', '__return_false', 30 );

		// This theme doesn't have a traditional sidebar. We use BLOCKS to build stuff.
		remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

		add_filter( 'body_class', array( $this, 'remove_sidebar_class' ), 30 );
		add_filter( 'components_entry_header_classes', array( $this, 'alter_entry_header_classes' ), 30, 1 );

		add_action( 'woocommerce_before_single_product_summary', array( $this, 'add_start_wrapper_before_single_product_summary' ), 1 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_end_wrapper_after_single_product_summary' ), 1 );
		add_action( 'pixelgrade_before_header', array( $this, 'output_mini_cart' ), 1 );

		// add various opening and closing tags to wrap upsells and related products

		// before and after upsells (priority 10)
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_start_wrapper_before_tabs' ), 9 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_end_wrapper_after_tabs' ), 11 );

		// before and after upsells (priority 15)
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_start_wrapper_before_upsells' ), 14 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_end_wrapper_after_upsells' ), 16 );

		// before and after related (priority 20)
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_start_wrapper_before_related' ), 19 );
		add_action( 'woocommerce_after_single_product_summary', array( $this, 'add_end_wrapper_after_related' ), 21 );
	}

	public function add_template_part_paths( $template, $slug, $name ) {
		$located = pixelgrade_locate_template_part( $slug, 'woocommerce', $name );
		if ( $located ) {
			return $located;
		}
		return $template;
	}

	public function add_template_paths( $located, $template_name, $args, $template_path, $default_path ) {
		$located_components = pixelgrade_locate_template_part( $template_name, 'woocommerce' );
		if ( $located_components ) {
			return $located_components;
		}
		return $located;
	}

	public function add_template_loader_files( $templates, $default_file ) {
		if ( is_singular( 'product' ) ) {
			$templates[] = 'components/woocommerce/templates/single-product.php';
		} elseif ( is_post_type_archive( 'product' ) ) {
			$templates[] = 'components/woocommerce/templates/archive-product.php';
		}
		return $templates;
	}

	public function alter_loop_start( $loop_start ) {
		return '<div class="' . join( ' ', pixelgrade_get_blog_grid_class() ) . '">';
	}

	public function alter_loop_end( $loop_end ) {
		return '</div>';
	}

	public function alter_pagination_args( $args ) {
		$args['prev_text'] = esc_html_x( '&laquo; Previous', 'previous set of posts', '__theme_txtd' );
		$args['next_text'] = esc_html_x( 'Next &raquo;', 'next set of posts', '__theme_txtd' );
		return $args;
	}

	public function change_sale_flash_markup( $sale_flash, $post, $product ) {
		return '<span class="c-btn  c-btn--sale-flash">' . esc_html__( 'Sale!', '__theme_txtd' ) . '</span>';
	}

	public function alter_entry_header_classes( $classes ) {
		if ( is_woo_archive() ) {
			$classes[] = 'entry-title--woocommerce';
		}
		return $classes;
	}

	public function remove_sidebar_class( $classes ) {
		if ( is_product() ) {
			$classes = array_diff( $classes, array( 'has-sidebar' ) );
		}
		return $classes;
	}

	public function add_start_wrapper_before_single_product_summary() {
		echo '<div class="c-product-main">';
	}

	public function add_end_wrapper_after_single_product_summary() {
		echo '</div>';
	}

	public function add_start_wrapper_before_tabs() {
		echo '<div class="c-woo-section  c-woo-tabs">';
	}

	public function add_end_wrapper_after_tabs() {
		echo '</div>';
	}

	public function add_start_wrapper_before_upsells() {
		echo '<div class="c-woo-section  c-woo-upsells">';
	}

	public function add_end_wrapper_after_upsells() {
		echo '</div>';
	}

	public function add_start_wrapper_before_related() {
		echo '<div class="c-woo-section  c-woo-related">';
	}

	public function add_end_wrapper_after_related() {
		echo '</div>';
	}

	public function output_mini_cart() {
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

}
