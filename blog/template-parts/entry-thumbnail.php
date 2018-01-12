<?php if ( has_post_thumbnail() ) { ?>

	<div class="entry-thumbnail">
		<div><?php the_post_thumbnail( 'single-' . pixelgrade_get_post_thumbnail_aspect_ratio_class() ); ?></div>
	</div>

<?php } ?>
