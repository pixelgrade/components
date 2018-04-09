<?php
/**
 * The Featured Posts Base Widget abstract class.
 * Extend this class and make it your own.
 * You can use it as it is but it will work as the Featured Posts - Grid widget.
 *
 * @see         https://pixelgrade.com
 * @author      Pixelgrade
 * @package     Components/Base
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Pixelgrade_FeaturedPosts_BaseWidget' ) ) :

	/**
	 * Class used to implement a Featured Posts widget.
	 *
	 * @see Pixelgrade_Widget_Fields
	 * @see WP_Widget
	 */
	abstract class Pixelgrade_FeaturedPosts_BaseWidget extends Pixelgrade_WidgetFields {

		/**
		 * These are the widget args.
		 *
		 * @access public
		 *
		 * @var array
		 */
		public $args = array(
			'before_title'  => '<h4 class="widgettitle">',
			'after_title'   => '</h4>',
			'before_widget' => '<div class="widget-wrap">',
			'after_widget'  => '</div></div>',
		);

		/**
		 * This is an array that will hold all the posts that have been shown thus far
		 * and need to be excluded from subsequent Featured Posts widgets.
		 * Only widgets that have the "Prevent duplicate posts" option active add posts to this array.
		 *
		 * @access public
		 *
		 * @var array
		 */
		public static $exclude_posts = array();

		/**
		 * Sets up a new Featured Posts widget instance.
		 *
		 * @access public
		 *
		 * @param string $id
		 * @param string $name
		 * @param array  $widget_ops
		 * @param array  $config
		 */
		public function __construct( $id = 'pixelgrade-featured-posts', $name = '', $widget_ops = array(), $config = array() ) {
			// Set up the default config.
			$default_config = array(
				'fields_sections'        => array(
					'default' => array(
						'title'    => '',
						'priority' => 1, // This section should really be the first as it is not part of the accordion.
					),
					'content' => array(
						'title'         => esc_html__( 'Content', '__theme_txtd' ),
						'default_state' => 'open',
						'priority'      => 10,
					),
					'layout'  => array(
						'title'    => esc_html__( 'Layout', '__theme_txtd' ),
						'priority' => 20,
					),
					'display' => array(
						'title'    => esc_html__( 'Display', '__theme_txtd' ),
						'priority' => 30,
					),
					'others'  => array(
						'title'    => esc_html__( 'Others', '__theme_txtd' ),
						'priority' => 40,
					),
				),
				'fields'                 => array(

					// Title Section.
					'title'                   => array(
						'type'     => 'text',
						'label'    => esc_html__( 'Section Title:', '__theme_txtd' ),
						'default'  => esc_html__( 'My Featured Posts', '__theme_txtd' ),
						'section'  => 'default',
						'priority' => 10,
					),

					// Content Section.
					'source'                  => array(
						'type'     => 'radio_group',
						'label'    => esc_html__( 'Posts Source:', '__theme_txtd' ),
						'options'  => array(
							'recent'   => esc_html__( 'Recent Posts', '__theme_txtd' ),
							'category' => esc_html__( 'Category', '__theme_txtd' ),
							'tag'      => esc_html__( 'Tag', '__theme_txtd' ),
							'post_ids' => esc_html__( 'Selected Posts', '__theme_txtd' ),
						),
						'default'  => 'recent',
						'section'  => 'content',
						'priority' => 10,
					),
					'source_category'         => array(
						'type'              => 'select',
						'label'             => esc_html__( 'Category:', '__theme_txtd' ),
						'callback'          => array( $this, 'categoriesDropdown' ),
						'sanitize_callback' => array( $this, 'sanitizeCategory' ), // We need to do custom sanitization for custom generated selects.
						'default'           => 0,
						'display_on'        => array(
							'display' => true,
							'on'      => array(
								'field' => 'source',
								'value' => 'category',
							),
						),
						'section'           => 'content',
						'priority'          => 20,
					),
					'source_tag'              => array(
						'type'              => 'select',
						'label'             => esc_html__( 'Tag:', '__theme_txtd' ),
						'callback'          => array( $this, 'tagsDropdown' ),
						'sanitize_callback' => array( $this, 'sanitizeTag' ), // We need to do custom sanitization for custom generated selects.
						'default'           => 0,
						'display_on'        => array(
							'display' => true,
							'on'      => array(
								'field' => 'source',
								'value' => 'tag',
							),
						),
						'section'           => 'content',
						'priority'          => 30,
					),
					'post_ids'                => array(
						'type'       => 'text',
						'label'      => esc_html__( 'Post IDs:', '__theme_txtd' ),
						'desc'       => esc_html__( 'Use Posts IDs, separated by commas, to show only a set of specific posts.', '__theme_txtd' ),
						'default'    => '',
						'display_on' => array(
							'display' => true,
							'on'      => array(
								'field' => 'source',
								'value' => 'post_ids',
							),
						),
						'section'    => 'content',
						'priority'   => 40,
					),
					'orderby'                 => array(
						'type'       => 'select',
						'label'      => esc_html__( 'Order by:', '__theme_txtd' ),
						'options'    => array(
							'date'    => esc_html__( 'Date', '__theme_txtd' ),
							'popular' => esc_html__( 'Most Popular', '__theme_txtd' ),
						),
						'default'    => 'date',
						'display_on' => array(
							'display' => false,
							'on'      => array(
								'field' => 'source',
								'value' => 'post_ids',
							),
						),
						'section'    => 'content',
						'priority'   => 50,
					),
					'number'                  => array(
						'type'              => 'number',
						'label'             => esc_html__( 'Number of posts:', '__theme_txtd' ),
						'sanitize_callback' => array( $this, 'sanitize_positive_int' ),
						'default'           => 6,
						'display_on'        => array(
							'display' => false,
							'on'      => array(
								'field' => 'source',
								'value' => 'post_ids',
							),
						),
						'section'           => 'content',
						'priority'          => 60,
					),
					'prevent_duplicate_posts' => array(
						'type'     => 'checkbox',
						'label'    => esc_html__( 'Prevent Duplicate Posts', '__theme_txtd' ),
						'desc'     => esc_html__( 'The posts displayed by this widget won\'t show up in the next widgets.', '__theme_txtd' ),
						'default'  => true,
						'section'  => 'content',
						'priority' => 70,
					),

					// Layout Section.
					'columns'                 => array(
						'type'     => 'select',
						'label'    => esc_html__( 'Number of columns:', '__theme_txtd' ),
						'options'  => array(
							'1' => esc_html__( '1 Column', '__theme_txtd' ),
							'2' => esc_html__( '2 Columns', '__theme_txtd' ),
							'3' => esc_html__( '3 Columns', '__theme_txtd' ),
							'4' => esc_html__( '4 Columns', '__theme_txtd' ),
						),
						'default'  => '3',
						'section'  => 'layout',
						'priority' => 10,
					),
					'image_ratio'             => array(
						'type'     => 'select',
						'label'    => esc_html__( 'Image Aspect Ratio:', '__theme_txtd' ),
						'options'  => array(
							'portrait'  => esc_html__( 'Portrait', '__theme_txtd' ),
							'square'    => esc_html__( 'Square', '__theme_txtd' ),
							'landscape' => esc_html__( 'Landscape', '__theme_txtd' ),
						),
						'default'  => 'portrait',
						'section'  => 'layout',
						'priority' => 20,
					),

					// Display Section.
					'show_excerpt'            => array(
						'type'     => 'checkbox',
						'label'    => esc_html__( 'Show Excerpt', '__theme_txtd' ),
						'default'  => true,
						'section'  => 'display',
						'priority' => 10,
					),
					'show_readmore'           => array(
						'type'     => 'checkbox',
						'label'    => esc_html__( 'Show "Read More" Link', '__theme_txtd' ),
						'default'  => true,
						'section'  => 'display',
						'priority' => 20,
					),
					'primary_meta'            => array(
						'type'     => 'select',
						'label'    => esc_html__( 'Primary Meta:', '__theme_txtd' ),
						'options'  => array(
							'none'     => esc_html__( 'None', '__theme_txtd' ),
							'date'     => esc_html__( 'Date', '__theme_txtd' ),
							'category' => esc_html__( 'Category', '__theme_txtd' ),
							'tags'     => esc_html__( 'Tags', '__theme_txtd' ),
							'author'   => esc_html__( 'Author', '__theme_txtd' ),
							'comments' => esc_html__( 'Comments', '__theme_txtd' ),
						),
						'default'  => 'category',
						'section'  => 'display',
						'priority' => 30,
					),
					'secondary_meta'          => array(
						'type'     => 'select',
						'label'    => esc_html__( 'Secondary Meta:', '__theme_txtd' ),
						'options'  => array(
							'none'     => esc_html__( 'None', '__theme_txtd' ),
							'date'     => esc_html__( 'Date', '__theme_txtd' ),
							'category' => esc_html__( 'Category', '__theme_txtd' ),
							'tags'     => esc_html__( 'Tags', '__theme_txtd' ),
							'author'   => esc_html__( 'Author', '__theme_txtd' ),
							'comments' => esc_html__( 'Comments', '__theme_txtd' ),
						),
						'default'  => 'none',
						'section'  => 'display',
						'priority' => 40,
					),
					'show_view_more'          => array(
						'type'       => 'checkbox',
						'label'      => esc_html__( 'Show View More Button', '__theme_txtd' ),
						'default'    => false,
						'display_on' => array(
							'display' => false,
							'on'      => array(
								'field' => 'source',
								'value' => 'post_ids',
							),
						),
						'section'    => 'display',
						'priority'   => 50,
					),
					'view_more_label'         => array(
						'type'       => 'text',
						'label'      => esc_html__( 'Label:', '__theme_txtd' ),
						'default'    => esc_html__( 'View More', '__theme_txtd' ),
						'display_on' => array(
							'display' => true,
							'on'      => array(
								'field' => 'show_view_more',
								'value' => true,
							),
						),
						'section'    => 'display',
						'priority'   => 60,
					),

					// Others Section.
				),
				'posts'                  => array(
					'classes'   => array( 'featured-posts-grid' ),
					// You can have multiple templates here (array of arrays) and we will use the first one that passes processing and is found.
					// @see Pixelgrade_Config::evaluateTemplateParts()
					'templates' => array(
						'component_slug'    => Pixelgrade_Blog::COMPONENT_SLUG,
						'slug'              => 'content',
						'name'              => 'widget',
						'lookup_parts_root' => true,
					),
				),
				'sidebars_not_supported' => array(
					// Sidebar IDs that this widget is not meant for.
					// We will show a notification instead of the widget content.
				),
			);

			// If we are given a config, merge it with the default config.
			if ( ! empty( $config ) ) {
				$default_config = Pixelgrade_Array::array_merge_recursive_distinct( $default_config, $config );
			}

			// Set up the widget options - merge them with our defaults.
			$widget_ops = wp_parse_args(
				$widget_ops, array(
					'classname'                   => 'widget_featured_posts',
					'description'                 => esc_html__( 'Your featured posts.', '__theme_txtd' ),
					'customize_selective_refresh' => false,
				)
			);

			// The default widget name - as it will be shown in the WordPress admin.
			if ( empty( $name ) ) {
				$name = esc_html__( 'Featured Posts', '__theme_txtd' );
			}

			// Initialize the widget.
			parent::__construct(
				$id,
				apply_filters( 'pixelgrade_featured_posts_widget_name', $name ),
				$widget_ops,
				$default_config
			);

			// Set up an alternate widget options name.
			$this->alt_option_name = 'widget_featured_entries';

			// Enqueue the frontend styles and scripts, if that is the case.
			if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
			}

			// Add custom export logic.
			add_filter( "pixcare_sce_widget_data_export_{$id}", array( $this, 'custom_export_logic' ), 10, 3 );
		}

		/**
		 * Enqueue front end scripts and styles.
		 *
		 * @access public
		 */
		public function enqueueScripts() {
			// Nothing right now. Override by extending the class.
		}

		/**
		 * Enqueue admin scripts and styles.
		 *
		 * @access public
		 */
		public function enqueueAdminScripts() {
			// Nothing right now. Override by extending the class.
		}

		/**
		 * Outputs the content for the current Featured Posts widget instance.
		 *
		 * @access public
		 *
		 * @param array $args Display arguments including 'before_title', 'after_title',
		 *                        'before_widget', and 'after_widget'.
		 * @param array $instance Settings for the current Featured Posts widget instance.
		 */
		public function widget( $args, $instance ) {
			// First, process the sidebars that are not supported by the current widget instance, if any.
			if ( false === $this->showInSidebar( $args, $instance ) ) {
				$this->sidebarNotSupportedMessage( $args, $instance );
				return;
			}

			// There is no point in doing anything of we don't have a template part to display with.
			// So first try and find a template part to use.
			$found_template = false;
			if ( ! empty( $this->config['posts']['templates'] ) ) {
				$found_template = Pixelgrade_Config::evaluateTemplateParts( $this->config['posts']['templates'] );
			}
			if ( ! empty( $found_template ) ) {
				// Make sure that we have the defaults in place, where there entry is missing.
				$instance = wp_parse_args( $instance, $this->getDefaults() );

				// Make sure that we have properly sanitized values (although they should be sanitized on save/update).
				$instance = $this->sanitizeFields( $instance );

				// Make every instance entry a variable in the current symbol table (scope in plain English).
				foreach ( $instance as $k => $v ) {
					if ( ! $this->isFieldDisabled( $k ) ) {
						// Add the variable.
						$$k = $v;
					}
				}

				/**
				 * Filters the widget title.
				 *
				 * @var string $title
				 *
				 * @param string $title The widget title. Default 'Pages'.
				 * @param array $instance An array of the widget's settings.
				 * @param mixed $id_base The widget ID.
				 */
				$title = apply_filters( 'widget_title', $title, $instance, $this->id_base );

				// Get the query for the posts we should shown in this widget.
				$posts = $this->getPostsQuery( $instance );
				// Do the loop.
				if ( $posts->have_posts() ) {

					$classes = array();
					if ( ! empty( $this->config['posts']['classes'] ) ) {
						$classes = array_merge( $classes, (array) $this->config['posts']['classes'] );
					}

					// Add our dynamic classes.
					if ( isset( $columns ) ) {
						$classes[] = 'o-grid';
						$classes[] = 'o-grid--' . $columns . 'col-@small';
					}
					if ( isset( $image_ratio ) ) {
						$classes[] = 'aspect-ratio-' . $image_ratio;
					}

					// By controlling the display of the excerpt and read more button from CSS we can show them on smaller screens.
					// (for "weird widgets like 5 Cards, 6 Cards).
					if ( ! empty( $show_excerpt ) ) {
						$classes[] = 'featured-posts--show-excerpt';
					}

					if ( ! empty( $show_readmore ) ) {
						$classes[] = 'featured-posts--show-readmore';
					}

					// Allow others (maybe other widgets that extend this) to change the classes.
					$classes = apply_filters( 'pixelgrade_featured_posts_widget_classes' . $this->id, $classes, $instance, $posts );
					$classes = apply_filters( 'pixelgrade_featured_posts_widget_classes', $classes );

					// Allow others (maybe other widgets that extend this) to change the attributes.
					$attributes = apply_filters( 'pixelgrade_featured_posts_widget_attributes' . $this->id, array(), $instance, $posts );

					/**
					 * Fires before the widget markup, including the <section>.
					 *
					 * This is a dynamic action specific to each widget instance.
					 *
					 * @param array $args     Display arguments including 'before_title', 'after_title',
					 *                        'before_widget', and 'after_widget'.
					 * @param array $instance An array of the widget's settings.
					 */
					do_action( 'pixelgrade_widget_before_' . $this->id, $args, $instance );

					echo $args['before_widget'];

					if ( ! empty( $title ) ) {
						echo $args['before_title'] . $title . $args['after_title'];
					}

					/**
					 * Fires at the beginning of the Featured Posts widget, after the title.
					 */
					do_action( 'pixelgrade_featured_posts_widget_start', $instance, $args );

					/**
					 * Fires at the beginning of the Featured Posts widget, after the title.
					 * This is a dynamic action specific to each widget instance.
					 */
					do_action( 'pixelgrade_featured_posts_widget_start' . $this->id, $instance, $args ); ?>

					<div <?php pixelgrade_css_class( $classes ); ?> <?php pixelgrade_element_attributes( $attributes ); ?>>

						<?php

						/**
						 * Fires before the featured posts widget loop.
						 * This is a dynamic action specific to each widget instance.
						 */
						do_action( 'pixelgrade_featured_posts_before_loop' . $this->id, $instance, $args );

						while ( $posts->have_posts() ) :
							$posts->the_post();
							// We want to count from 1 since the current_post starts at 0.
							$post_index = $posts->current_post + 1;

							// Also allow others to introduce variables in the widget's template scope, for each post in the loop.
							// extract() overwrites the variable value if it already exists.
							$extra_vars = apply_filters( 'pixelgrade_featured_posts_widget_loop_extra_vars' . $this->id, array(), get_the_ID(), $posts, $instance );
							if ( ! empty( $extra_vars ) ) {
								extract( $extra_vars );
							}

							/**
							 * Fires before the widget post.
							 * This is a dynamic action specific to each widget instance.
							 */
							do_action( 'pixelgrade_featured_posts_widget_before_post' . $this->id, $post_index, $posts );

							// We use include so the template parts gets access to all the variables defined above.
							include $found_template;

							/**
							 * Fires after the widget post.
							 * This is a dynamic action specific to each widget instance.
							 */
							do_action( 'pixelgrade_featured_posts_widget_after_post' . $this->id, $post_index, $posts );
						endwhile;

						/**
						 * Fires after the featured posts widget loop.
						 * This is a dynamic action specific to each widget instance.
						 */
						do_action( 'pixelgrade_featured_posts_after_loop' . $this->id, $instance, $args );
						?>

					</div>

					<?php
					if ( ! empty( $show_view_more ) && ! empty( $view_more_label ) ) {
						// We need a View More button linking to the appropriate archive, depending on the posts source.
						$view_more_link = false;

						switch ( $instance['source'] ) {
							case 'recent':
								// link to the posts home page.
								$view_more_link = get_post_type_archive_link( 'post' );
								break;
							case 'category':
								if ( empty( $instance['source_category'] ) || -1 == $instance['source_category'] ) {
									// link to the posts home page.
									$view_more_link = get_post_type_archive_link( 'post' );
								} else {
									$view_more_link = get_term_link( $instance['source_category'], 'category' );
								}
								break;
							case 'tag':
								if ( empty( $instance['source_tag'] ) || -1 == $instance['source_tag'] ) {
									// link to the posts home page.
									$view_more_link = get_post_type_archive_link( 'post' );
								} else {
									$view_more_link = get_term_link( $instance['source_tag'], 'post_tag' );
								}
								break;
						}

						if ( ! empty( $view_more_link ) && ! is_wp_error( $view_more_link ) ) {
							echo '<div class="featured-posts__footer">' . PHP_EOL .
								 '<a class="featured-posts__more" href="' . esc_url( $view_more_link ) . '">' . $view_more_label . '</a>' . PHP_EOL .
								 '</div>';
						}
					}
					?>

					<?php

					/**
					 * Fires at the end of the Featured Posts widget.
					 */
					do_action( 'pixelgrade_featured_posts_widget_end', $instance, $args );

					/**
					 * Fires at the end of the Featured Posts widget.
					 * This is a dynamic action specific to each widget instance.
					 */
					do_action( 'pixelgrade_featured_posts_widget_end' . $this->id, $instance, $args );

					echo $args['after_widget'];

					/**
					 * Fires after the widget markup, including the closing </section>.
					 *
					 * This is a dynamic action specific to each widget instance.
					 *
					 * @param array $args     Display arguments including 'before_title', 'after_title',
					 *                        'before_widget', and 'after_widget'.
					 * @param array $instance An array of the widget's settings.
					 */
					do_action( 'pixelgrade_widget_after_' . $this->id, $args, $instance );

					// Reset the global $the_post as this query will have stomped on it.
					wp_reset_postdata();

					// If this widget wants to prevent post duplication we need to add the queried posts to the static property.
					if ( ! empty( $prevent_duplicate_posts ) ) {
						$queried_post_ids    = wp_list_pluck( $posts->posts, 'ID' );
						self::$exclude_posts = array_merge( self::$exclude_posts, $queried_post_ids );
					}
				}
			} else {
				// Let the developers know that something is amiss.
				_doing_it_wrong( __METHOD__, sprintf( 'Couldn\'t find a template part to use for displaying widget posts in the %s widget!', $this->name ), null );
			}
		}

		/**
		 * Get the WP_Query instance for the current widget.
		 *
		 * @param array $instance The current widget instance details.
		 *
		 * @return WP_Query
		 */
		public function getPostsQuery( $instance ) {
			$query_args = array(
				'posts_per_page'      => 10, // a decent default
				'no_found_rows'       => true, // extra performance
				'post_status'         => 'publish', // only published posts in featured posts widgets
				'ignore_sticky_posts' => true, // we don't deal with sticky posts in featured posts widgets
				'order'               => 'desc',
			);

			// If the number field is disabled, force the default value
			// because we really need a number, despite the fact that the user can't select one (if disabled field).
			if ( $this->isFieldDisabled( 'number' ) ) {
				$number = absint( $this->getDefault( 'number' ) );
			} else {
				$number = absint( $instance['number'] );
			}
			$query_args['posts_per_page'] = $number;

			if ( ! $this->isFieldDisabled( 'source' ) ) {
				if ( ! $this->isFieldDisabled( 'source_category' )
					 && 'category' === $instance['source']
					 && ! empty( $instance['source_category'] )
					 && - 1 != $instance['source_category'] ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'category',
							'field'    => 'slug',
							'terms'    => array( $instance['source_category'] ),
						),
					);
				} elseif ( ! $this->isFieldDisabled( 'source_tag' )
						   && 'tag' === $instance['source']
						   && ! empty( $instance['source_tag'] )
						   && - 1 != $instance['source_tag'] ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'post_tag',
							'field'    => 'slug',
							'terms'    => array( $instance['source_tag'] ),
						),
					);
				} elseif ( ! $this->isFieldDisabled( 'post_ids' )
						   && 'post_ids' == $instance['source']
						   && ! empty( $instance['post_ids'] ) ) {

					// If we are given a list of post_ids, then we will ignore the posts queried thus far and the ones that need to be excluded.
					// You can't have post__in and post__not_in in the same query!
					// Transform and sanitize the ids.
					$post_ids = Pixelgrade_Value::maybeExplodeList( $instance['post_ids'] );
					if ( ! empty( $post_ids ) ) {
						foreach ( $post_ids as $key => $value ) {
							if ( ! is_numeric( $value ) ) {
								unset( $post_ids[ $key ] );
							} else {
								$post_ids[ $key ] = intval( $value );
							}
						}

						$query_args['post__in']       = $post_ids;
						$query_args['posts_per_page'] = count( $post_ids );
						$query_args['orderby']        = 'post__in';
					}
				}
			}

			// If we don't have specific post IDs, we can exclude posts.
			if ( empty( $query_args['post__in'] ) && ! empty( self::$exclude_posts ) ) {
				// We need to exclude the posts that gathered thus far in the $exclude_posts static variable.
				$query_args['post__not_in'] = self::$exclude_posts;
			}

			if ( empty( $query_args['orderby'] ) && ! $this->isFieldDisabled( 'orderby' ) ) {
				$query_args['orderby'] = $instance['orderby'];
			}

			/**
			 * Filters the arguments for the Featured Posts widget query.
			 *
			 * @see WP_Query::get_posts()
			 *
			 * @param array $args An array of arguments used to retrieve the widget posts.
			 */
			return new WP_Query( apply_filters( 'pixelgrade_widget_featured_posts_query_args', $query_args, $instance ) );

		}

		/**
		 * Generate the HTML for the dropdown (select) field.
		 *
		 * @param string $selected The current selected value.
		 * @param string $field_name The field ID.
		 * @param array  $field_config The field config.
		 *
		 * @return string The select HTML.
		 */
		public function categoriesDropdown( $selected, $field_name, $field_config ) {
			$output = '';

			// Now for attributes.
			$label = '';
			if ( ! empty( $field_config['label'] ) ) {
				$label = $field_config['label'];
			}

			$desc = '';
			if ( ! empty( $field_config['desc'] ) ) {
				$desc = $field_config['desc'];
			}

			// Lets generate the markup.
			$output .= '<p class="pixelgrade-featured-posts-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

			if ( ! empty( $label ) ) {
				$output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
			}

			$args         = array(
				'show_option_all'   => esc_html__( 'All', '__theme_txtd' ),
				'orderby'           => 'id',
				'order'             => 'ASC',
				'show_count'        => 1,
				'hide_empty'        => 1,
				'child_of'          => 0,
				'exclude'           => '',
				'echo'              => 0,
				'selected'          => $selected,
				'hierarchical'      => 1,
				'name'              => $this->get_field_name( $field_name ),
				'id'                => $this->get_field_id( $field_name ),
				'class'             => 'widefat',
				'depth'             => 0,
				'tab_index'         => 0,
				'taxonomy'          => 'category',
				'option_none_value' => - 1,
				'value_field'       => 'slug',
				'required'          => false,
				'hide_if_empty'     => true,
			);
			$cat_dropdown = wp_dropdown_categories( $args );

			if ( empty( $cat_dropdown ) ) {
				$output .= '<br /><small>' . esc_html__( 'Please define some categories first.', '__theme_txtd' ) . '</small>' . PHP_EOL;
			} else {
				$output .= $cat_dropdown;
			}

			if ( ! empty( $desc ) ) {
				$output .= '<br />' . PHP_EOL;
				$output .= '<small>' . $desc . '</small>' . PHP_EOL;
			}

			$output .= '</p>' . PHP_EOL;

			return $output;
		}

		/**
		 * We need to do custom sanitization for custom generated selects.
		 *
		 * @param string $value
		 * @param string $field_name
		 * @param array  $field_config
		 *
		 * @return bool
		 */
		public function sanitizeCategory( $value, $field_name, $field_config ) {
			// Get all the categories shown in the dropdown.
			$categories = get_terms(
				'category', array(
					'hide_empty'   => 1,
					'child_of'     => 0,
					'exclude'      => '',
					'hierarchical' => 1,
					'fields'       => 'id=>slug',
				)
			);

			if ( ! in_array( $value, $categories ) ) {
				// Fallback on the default value.
				if ( isset( $field_config['default'] ) ) {
					return $field_config['default'];
				} else {
					return false;
				}
			}

			// All is good.
			return $value;
		}

		/**
		 * Generate the HTML for the dropdown (select) field.
		 *
		 * @param string $selected The current selected value.
		 * @param string $field_name The field ID.
		 * @param array  $field_config The field config.
		 *
		 * @return string The select HTML.
		 */
		public function tagsDropdown( $selected, $field_name, $field_config ) {
			$output = '';

			// Now for attributes.
			$label = '';
			if ( ! empty( $field_config['label'] ) ) {
				$label = $field_config['label'];
			}

			$desc = '';
			if ( ! empty( $field_config['desc'] ) ) {
				$desc = $field_config['desc'];
			}

			// Lets generate the markup.
			$output .= '<p class="pixelgrade-featured-posts-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

			if ( ! empty( $label ) ) {
				$output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
			}

			$args         = array(
				'show_option_all'   => esc_html__( 'All', '__theme_txtd' ),
				'orderby'           => 'id',
				'order'             => 'ASC',
				'show_count'        => 1,
				'hide_empty'        => 0,
				'echo'              => 0,
				'selected'          => $selected,
				'hierarchical'      => 0,
				'name'              => $this->get_field_name( $field_name ),
				'id'                => $this->get_field_id( $field_name ),
				'class'             => 'widefat',
				'tab_index'         => 0,
				'taxonomy'          => 'post_tag',
				'option_none_value' => - 1,
				'value_field'       => 'slug',
				'required'          => false,
				'hide_if_empty'     => true,
			);
			$tag_dropdown = wp_dropdown_categories( $args );

			if ( empty( $tag_dropdown ) ) {
				$output .= '<br /><small>' . esc_html__( 'Please define some tags first.', '__theme_txtd' ) . '</small>' . PHP_EOL;
			} else {
				$output .= $tag_dropdown;
			}

			if ( ! empty( $desc ) ) {
				$output .= '<br />' . PHP_EOL;
				$output .= '<small>' . $desc . '</small>' . PHP_EOL;
			}

			$output .= '</p>' . PHP_EOL;

			return $output;
		}

		/**
		 * We need to do custom sanitization for custom generated selects.
		 *
		 * @param string $value
		 * @param string $field_name
		 * @param array  $field_config
		 *
		 * @return bool
		 */
		public function sanitizeTag( $value, $field_name, $field_config ) {
			// Get all the tags shown in the dropdown.
			$tags = get_terms(
				'post_tag', array(
					'hide_empty'   => 0,
					'exclude'      => '',
					'hierarchical' => 0,
					'fields'       => 'id=>slug',
				)
			);

			if ( ! in_array( $value, $tags ) ) {
				// Fallback on the default value.
				if ( isset( $field_config['default'] ) ) {
					return $field_config['default'];
				} else {
					return false;
				}
			}

			// All is good.
			return $value;
		}

		/**
		 * Handle various export logic specific to this widget's fields.
		 *
		 * @param array $widget_data The widget instance values.
		 * @param string $widget_type The widget type.
		 * @param array $matching_data The matching import/export data like old-new post IDs, old-new attachment IDs, etc.
		 *
		 * @return array The modified widget data.
		 */
		public function custom_export_logic( $widget_data, $widget_type, $matching_data ) {
			// Replace the post IDs with the new ones.
			if ( ! empty( $widget_data['post_ids'] ) && ! empty( $matching_data['post_types']['post'] ) ) {
				$post_ids = Pixelgrade_Value::maybeExplodeList( $widget_data['post_ids'] );
				if ( ! empty( $post_ids ) ) {
					foreach ( $post_ids as $key => $value ) {
						if ( ! is_numeric( $value ) ) {
							unset( $post_ids[ $key ] );
						} else {
							$post_ids[ $key ] = intval( $value );
						}
					}
				}

				foreach ( $post_ids as $key => $old_post_id ) {
					if ( ! empty( $matching_data['post_types']['post'][ $old_post_id ] ) ) {
						$post_ids[ $key ] = $matching_data['post_types']['post'][ $old_post_id ];
					}
				}

				// We need to convert the post IDs back to comma separated list.
				$widget_data['post_ids'] = implode( ',', $post_ids );
			}

			return $widget_data;
		}
	}

endif;
