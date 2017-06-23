<?php
/**
 * The main template for header
 *
 * This template can be overridden by copying it to a child theme in /components/header/templates/header.php
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see        https://pixelgrade.com
 * @author     Pixelgrade
 * @package    Components/Header
 * @version    1.1.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<header id="masthead" <?php pixelgrade_header_class(); ?> role="banner">
	<div class="u-header_sides_spacing">
		<div class="o-wrapper  u-container-width  c-navbar__wrapper">

			<?php
			/**
			 * pixelgrade_header_before_navbar hook.
			 */
			do_action( 'pixelgrade_header_before_navbar', 'header' );
			?>

			<div class="c-navbar  c-navbar--dropdown">
				<input class="c-navbar__checkbox" id="menu-toggle" type="checkbox">
				<label class="c-navbar__label" for="menu-toggle">
					<span class="c-navbar__label-icon"><?php pxg_load_component_file( 'header', 'templates/burger', '', false ); ?></span>
					<span class="c-navbar__label-text screen-reader-text"><?php esc_html_e( 'Primary Menu', 'components' ); ?></span>
				</label><!-- .c-navbar__label -->

				<?php pxg_load_component_file( 'header', 'templates/content-navbar', '', false ); ?>

			</div><!-- .c-navbar -->

			<?php
			/**
			 * pixelgrade_header_after_navbar hook.
			 */
			do_action( 'pixelgrade_header_after_navbar', 'header' );
			?>

		</div><!-- .o-wrapper  .u-container-width -->
	</div><!-- .u-header_sides_spacing -->
</header><!-- #masthead .site-header -->
