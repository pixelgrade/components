<?php
/**
 * The template for the branding of the header area (logo, site title, etc).
 *
 * @see        https://pixelgrade.com
 * @author        Pixelgrade
 * @package    Components/Header
 * @version     1.0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div class="c-branding">

	<?php if ( function_exists( 'the_custom_logo' ) ) {
		the_custom_logo();
	} ?>

    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
		<?php if ( is_front_page() && is_home() ) : ?>
            <h1 class="site-title"><?php bloginfo( 'name' ); ?></h1>
		<?php else : ?>
            <p class="site-title h1"><?php bloginfo( 'name' ); ?></p>
		<?php endif; ?>
    </a>
    <p class="site-description site-description-text"><?php bloginfo( 'description' ) /* WPCS: xss ok. */ ?></p>
</div><!-- .c-branding -->