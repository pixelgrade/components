<?php
/**
 * The template part for header
 *
 * This template part can be overridden by copying it to a child theme or in the same theme
 * by putting it in template-parts/header/header.php.
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
 * @version    1.1.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * pixelgrade_before_header hook.
 *
 * @hooked nothing() - 10 (outputs nothing)
 */
do_action( 'pixelgrade_before_header', 'main' );
?>

<header id="masthead" <?php pixelgrade_header_class(); ?> role="banner">
	<div class="u-header-sides-spacing">
		<div class="o-wrapper  u-container-width  c-navbar__wrapper">

			<?php
			/**
			 * pixelgrade_header_before_navbar hook.
			 */
			do_action( 'pixelgrade_header_before_navbar', 'header' );
			?>

			<div class="c-navbar  c-navbar--dropdown  u-header-height">

                <?php
                $header_zones = pixelgrade_header_get_zones();
                $header_active_menus = array();

                // Cycle through each zone and display the nav menus or other "bogus" things.
                foreach ( $header_zones as $zone_id => $zone ) {
                    // Get the menu_locations in the current zone.
                    $menu_locations = pixelgrade_header_get_zone_nav_menu_locations( $zone_id, $zone );

                    foreach ( $menu_locations as $menu_id => $menu_location ) {
                        if ( empty( $menu_location['bogus'] ) ) {
                            $menu = wp_nav_menu(
                                array (
                                    'theme_location' => $menu_id,
                                    'echo' => false,
                                    'fallback_cb' => '__return_false'
                                )
                            );

                            if ( false !== $menu ) {
                                $header_active_menus[] = $menu_id;
                            }
                        }
                    }
                }

                if ( apply_filters( 'pixelgrade_show_hamburger_icon', ! empty( $header_active_menus ) )  ) { ?>
				<input class="c-navbar__checkbox" id="menu-toggle" type="checkbox">
				<label class="c-navbar__label u-header-sides-spacing" for="menu-toggle">
					<span class="c-navbar__label-icon"><?php pixelgrade_get_component_template_part( Pixelgrade_Header::COMPONENT_SLUG, 'burger' ); ?></span>
					<span class="c-navbar__label-text screen-reader-text"><?php esc_html_e( 'Menu', '__components_txtd' ); ?></span>
				</label><!-- .c-navbar__label -->
                <?php } ?>

				<?php
				/**
				 * pixelgrade_header_before_navbar_content hook.
				 */
				do_action( 'pixelgrade_header_before_navbar_content', 'header' );
				?>

				<?php pixelgrade_get_component_template_part( Pixelgrade_Header::COMPONENT_SLUG, 'content-navbar' ); ?>

				<?php
				/**
				 * pixelgrade_header_after_navbar_content hook.
				 */
				do_action( 'pixelgrade_header_after_navbar_content', 'header' );
				?>

			</div><!-- .c-navbar -->

			<?php
			/**
			 * pixelgrade_header_after_navbar hook.
			 */
			do_action( 'pixelgrade_header_after_navbar', 'header' );
			?>

		</div><!-- .o-wrapper  .u-container-width -->
	</div><!-- .u-header-sides-spacing -->
</header><!-- #masthead .site-header -->

<?php
/**
 * pixelgrade_after_header hook.
 *
 * @hooked nothing() - 10 (outputs nothing)
 */
do_action( 'pixelgrade_after_header', 'main' );
