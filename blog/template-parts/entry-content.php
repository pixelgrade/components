<div class="entry-content  u-content-width">
	<?php
	the_content( sprintf(
	/* translators: %s: Name of current post. */
		wp_kses( __( 'Continue reading %s <span class="meta-nav">&rarr;</span>', '__theme_txtd' ), array( 'span' => array( 'class' => array() ) ) ),
		the_title( '<span class="screen-reader-text">"', '"</span>', false )
	) );

	wp_link_pages( array(
		'before' => '<div class="page-links">' . esc_html__( 'Pages:', '__theme_txtd' ),
		'after'  => '</div>',
	) );

	?>
</div><!-- .entry-content -->
