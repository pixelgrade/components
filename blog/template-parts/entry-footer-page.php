<?php if ( get_edit_post_link() ) : ?>
    <footer class="entry-footer  u-content-width">
        <?php
        edit_post_link(
            sprintf(
            /* translators: %s: Name of current post */
                esc_html__( 'Edit %s', '__theme_txtd' ),
                the_title( '<span class="screen-reader-text">"', '"</span>', false )
            ),
            '<div class="edit-link">',
            '</div>'
        );
        ?>
    </footer><!-- .entry-footer -->
<?php endif; ?>

<?php
// If comments are open or we have at least one comment, load up the comment template.
if ( comments_open() || get_comments_number() ) :
    pixelgrade_comments_template();
endif; ?>
