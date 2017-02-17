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
 * @version    1.0.1
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
				<input class="c-navbar__checkbox" id="menu-toggle" type="checkbox" aria-controls="primary-menu" aria-expanded="false">
				<label class="c-navbar__label" for="menu-toggle">
					<span class="c-navbar__label-icon"><?php pxg_load_component_file( 'header', 'templates/burger', '', false ); ?></span>
					<span class="c-navbar__label-text screen-reader-text"><?php esc_html_e( 'Primary Menu', 'components' ); ?></span>
				</label><!-- .c-navbar__label -->

				<div class="c-navbar__content">

					<?php
					$has_left_menu   = has_nav_menu( 'primary-left' );
					$has_right_menu  = has_nav_menu( 'primary-right' );
					$has_social_menu = has_nav_menu( 'jetpack-social-menu' );

					$menu_left_markup = pixelgrade_header_get_the_left_menu();
					$menu_right_markup = pixelgrade_header_get_the_right_menu();

					// Setup the classes for the various areas
					// First the left area
					$zone_left_classes  = array( 'c-navbar__zone', 'c-navbar__zone--left' );
					if ( $has_left_menu && $has_right_menu ) {
						$zone_left_classes[] = 'c-navbar__zone--push-right';
					}

					// Then the middle area
					$zone_middle_classes  = array( 'c-navbar__zone', 'c-navbar__zone--middle' );

					// Then the right area
					$zone_right_classes = array( 'c-navbar__zone', 'c-navbar__zone--right' );
					if ( ! $has_right_menu || ( ! $has_left_menu && $has_right_menu ) ) {
						$zone_right_classes[] = 'c-navbar__zone--push-right';
					}

					/*
					 * All the conditionals bellow follow the logic outlined in the component's guides
					 * @link http://pixelgrade.github.io/guides/components/header
					 * They try to automatically adapt to the existence or non-existence of navbar components: the menus and the logo.
					 *
					 * Also note that you can make use of the fact that we've used the pixelgrade_css_class() function to
					 * output the classes for each zone. You can use the `pixelgrade_css_class` filter and depending on
					 * the location received act accordingly.
					 */ ?>

					<div <?php pixelgrade_css_class( $zone_left_classes, 'header navbar zone left' ); ?>>
						<?php if ( $has_left_menu ) {
							echo $menu_left_markup;
						} elseif ( $has_right_menu ) { ?>
							<div <?php pixelgrade_css_class( 'header nav', 'header navbar zone left' ); ?>>
								<?php pxg_load_component_file( 'header', 'templates/branding', '', false ); ?>
							</div>
						<?php } ?>
					</div><!-- .c-navbar__zone .c-navbar__zone--left -->

					<div <?php pixelgrade_css_class( $zone_middle_classes, 'header navbar zone middle' ); ?>>
						<?php if ( $has_left_menu || ! ( $has_left_menu || $has_right_menu ) ) { ?>
							<div <?php pixelgrade_css_class( 'header nav', 'header navbar zone middle' ); ?>>
								<?php pxg_load_component_file( 'header', 'templates/branding', '', false ); ?>
							</div>
						<?php } else {
							echo $menu_right_markup;
						}
						?>
					</div><!-- .c-navbar__zone .c-navbar__zone--middle -->

					<div <?php pixelgrade_css_class( $zone_right_classes, 'header navbar zone right' ); ?>>
						<?php if ( $has_left_menu ) {
							echo $menu_right_markup;
						}

						if ( function_exists( 'jetpack_social_menu' ) ) {
							jetpack_social_menu();
						} ?>
					</div><!-- .c-navbar__zone .c-navbar__zone--right -->
				</div><!-- .c-navbar__content -->
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
