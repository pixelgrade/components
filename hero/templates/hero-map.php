<?php
/**
 * The template for the hero area (the top area) of the contact/location page template.
 *
 * This template can be overridden by copying it to a child theme in /components/heroes/templates/hero-map.php
 *
 * HOWEVER, on occasion Pixelgrade will need to update template files and you
 * will need to copy the new files to your child theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Hero
 * @version     1.0.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//we first need to know the bigger picture - the location this template part was loaded from
//make sure we have some map in there
$location = pixelgrade_set_location( 'map', true );
?>

<?php if ( pixelgrade_hero_is_hero_needed( $location ) ) : ?>

	<div <?php pixelgrade_hero_class( '', $location ); pixelgrade_hero_background_color_style(); ?>>

		<?php
		//first lets get to know this page a little better
		//get the Google Maps URL
		$map_url = get_post_meta( get_the_ID(), '_hero_map_url', true );

		//get the custom styling and marker/pin content
		$map_custom_style   = get_post_meta( get_the_ID(), '_hero_map_custom_style', true );
		$map_marker_content = get_post_meta( get_the_ID(), '_hero_map_marker_content', true );
		?>

		<div class="c-hero__slider">
			<div class="hero-bg--map" id="gmap"
			     data-url="<?php esc_attr_e( $map_url ); ?>" <?php echo ( $map_custom_style == 'on' ) ? 'data-customstyle' : ''; ?>
			     data-markercontent="<?php echo esc_attr( $map_marker_content ); ?>"></div>
		</div><!-- .c-hero__slider -->

	</div><!-- .c-hero -->

<?php endif; // if ( pixelgrade_hero_is_hero_needed() ) ?>
