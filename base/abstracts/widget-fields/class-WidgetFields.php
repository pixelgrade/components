<?php
/**
 * The Widget Fields class
 *
 * This class provides the logic for handling the programmatic (config) generation of widget fields, including conditional displaying behaviour.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Pixelgrade_WidgetFields' ) ) :

	/**
	 * Class used to add fields logic to WP_Widget.
	 *
	 * @see WP_Widget
	 */
	abstract class Pixelgrade_WidgetFields extends WP_Widget {

		// This is the default config
		public $config = array();

		protected static $default_field_type = 'text';
		protected static $default_field_section = 'default';
		protected static $default_field_section_state = 'closed';

		/**
		 * Sets up a new widget instance.
		 *
		 * @access public
		 *
		 * @param string $id
		 * @param string $name
		 * @param array $widget_ops
         * @param array $config
		 */
		public function __construct( $id, $name = '', $widget_ops = array(), $config = array() ) {
		    if ( ! empty( $config ) ) {
		        $this->config = $config;
            }

            // Make sure we have some sane configs
            if ( empty( $this->config['fields_sections'] ) ) {
                $this->config['fields_sections'] = array(
                    'default' => array(
                        'title' => '',
                        'priority' => 1,
                    ),
                );
            }
            if ( empty( $this->config['fields'] ) ) {
                $this->config['fields'] = array();
            } else {
		    	// Make sure each field has a section
		    	foreach ( $this->config['fields'] as $k => $v ) {
		    		if ( empty( $v['section'] ) ) {
		    			$this->config[ $k ]['section'] = self::$default_field_section;
				    }
			    }
            }
            if ( empty( $this->config['posts'] ) ) {
                $this->config['posts'] = array();
            }

            // Initialize the widget
            parent::__construct( $id,
                apply_filters( 'pixelgrade_widget_name', $name ),
                $widget_ops );

			// Enqueue the frontend styles and scripts, if that is the case
			if ( is_active_widget( false, false, $this->id_base ) || is_customize_preview() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueueScripts' ) );
			}
		}

		/**
		 * Enqueue admin scripts and styles.
		 *
		 * @access public
		 */
		public function enqueueAdminFieldsScripts() {
			if ( $this->isFieldTypeUsed( 'image'  ) ) {
				wp_enqueue_media();
				wp_enqueue_script( 'media-widgets' );
			}

			if ( $this->isFieldTypeUsed( 'select2'  ) ) {
				wp_enqueue_script( 'select2', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( Pixelgrade_Base::COMPONENT_SLUG ) . 'abstracts/widget-fields/vendor/select2/js/select2.min.js' ), array( 'jquery' ), '4.0.5' );
				wp_enqueue_script( 'select2-sortable', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( Pixelgrade_Base::COMPONENT_SLUG ) . 'abstracts/widget-fields/vendor/select2v4-sortable/select2-sortable.js' ), array( 'jquery', 'select2' ), '4.0.5' );
				wp_enqueue_style( 'select2', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( Pixelgrade_Base::COMPONENT_SLUG ) . 'abstracts/widget-fields/vendor/select2/css/select2.min.css' ), array(), 20171111 );
			}

			// Enqueue the needed admin scripts
            wp_enqueue_script( 'pixelgrade-widget-fields-js', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( Pixelgrade_Base::COMPONENT_SLUG ) . 'abstracts/widget-fields/widget-fields.js' ), array( 'jquery', 'media-upload', 'media-views' ), 20171111 );

			wp_localize_script( 'pixelgrade-widget-fields-js', 'pixelgradeWidgetFields', array(
				'image' => array(
					'frame_title'  => esc_html__( 'Select an Image', '__components_txtd' ),
					'button_title' => esc_html__( 'Insert Into Widget', '__components_txtd' ),
				),
			) );

            // Enqueue the needed admin styles
			wp_enqueue_style( 'pixelgrade-widget-fields', pixelgrade_get_theme_file_uri( trailingslashit( PIXELGRADE_COMPONENTS_PATH ) . trailingslashit( Pixelgrade_Base::COMPONENT_SLUG ) . 'abstracts/widget-fields/widget-fields.css' ), array(  ), 20171111 );
		}

        /**
         * Enqueue admin scripts and styles.
         *
         * @access public
         */
        public function enqueueAdminScripts() {
            // Nothing right now. Override by extending the class
        }

		/**
		 * Enqueue frontend scripts and styles.
		 *
		 * @access public
		 */
		public function enqueueScripts() {
			// Nothing right now. Override by extending the class
		}

		/**
		 * Outputs the settings form for the widget.
		 *
		 * @access public
		 *
		 * @param array $instance Current settings.
		 *
		 * @return void
		 */
		public function form( $instance ) {
		    // The conditional fields logic
			$this->enqueueAdminFieldsScripts();
			// Any WP admin logic a widget may have
			$this->enqueueAdminScripts();

			// Sanitize all field values
			$instance = $this->sanitizeFields( $instance );

			// For each section (ordered by priority ASC), display the fields (ordered by priority ASC)
            $sections = Pixelgrade_Array::array_orderby( $this->getFieldsSections(), 'priority', SORT_ASC );
            if ( ! empty( $sections ) ) {
            	$do_accordion = false;
	            // We are only making an accordion when there are at least two sections
	            if ( count( $sections ) > 1 ) {
		            $do_accordion = true;
	            }

	            // If the first sections is the default one (as it should normally be the case),
	            // we will not add it to the accordion.
	            reset( $sections );
	            $first_section_id = key( $sections );
	            if ( $first_section_id === self::$default_field_section ) {
		            // Get only the fields belonging to the current section
		            $section_fields = $this->getSectionFields( $first_section_id );

		            // We don't display anything if there are no fields
		            if ( ! empty( $section_fields ) ) {
			            $this->displayFields( $section_fields, $instance );
		            }

		            unset( $sections[ $first_section_id ] );
	            }

	            if ( $do_accordion ) {
		            // The accordion wrappers
		            echo '<div class="accordion-container">' . PHP_EOL;
		            echo '<ul>' . PHP_EOL;
	            }

	            foreach ( $sections as $section_id => $section ) {
		            // Get only the fields belonging to the current section
		            $section_fields = $this->getSectionFields( $section_id );

		            // We don't display anything if there are no fields
		            if ( ! empty( $section_fields ) ) {
		            	if ( $do_accordion ) {
				            $state_field_name = "widget-section-state[{$section_id}]";
				            $state_value = $this->getSectionDefaultState( $section_id );
				            // In case this is a AJAX request (like when saving the widget) - keep the current state so we don't confuse the user
				            if ( wp_doing_ajax() && isset( $_REQUEST['widget-section-state'][ $section_id ] ) && in_array( $_REQUEST['widget-section-state'][ $section_id ], array( 'open', 'closed', ) ) ) {
					            $state_value = $_REQUEST['widget-section-state'][ $section_id ];
				            }

				            // We will use the state value as a class also!!!
				            echo '<li class="control-section accordion-section ' . esc_attr( $state_value ) . '">' . PHP_EOL;

				            // We add a hidden input field so we can keep the open/closed state of the section on save/update
				            echo '<input class="_section-state" type="hidden" name="' . esc_attr( $state_field_name ) . '" value="' . esc_attr( $state_value ) . '"/>' . PHP_EOL;

				            // Handle the section title and wrappers
				            /* translators: Used for screen readers on widget sections. */
				            echo '<h3 class="accordion-section-title hndle">' . ( ! empty( $section['title'] ) ? $section['title'] : '' ) . '<span class="screen-reader-text">' . esc_html__( 'Press return or enter to open this section.', '__components_txtd' ) . '</span></h3>' . PHP_EOL;

				            // The section fields wrapper
				            echo '<div class="accordion-section-content">';
			            }

						$this->displayFields( $section_fields, $instance );

			            if ( $do_accordion ) {
				            echo '</div><!-- .accordion-section-content -->' . PHP_EOL;
				            echo '</li>' . PHP_EOL;
			            }
		            }
	            }

	            // There is no point in making an accordion when there is only one section
	            if ( $do_accordion ) {
		            // End the accordion wrappers
		            echo '</ul>' . PHP_EOL;
		            echo '</div><!-- .accordion-container -->' . PHP_EOL;
	            }
            } else {
            	// We have not sections so just display all the fields
	            $this->displayFields( $this->getFields(), $instance );
            }
		}

		/**
		 * Display a set of fields, ordered by priority ASC.
		 *
		 * @param array $fields The fields to display.
		 * @param array $instance The current widget instance details.
		 */
		public function displayFields( $fields, $instance ) {
			// Order current fields by priority
			$fields = Pixelgrade_Array::array_orderby( $fields, 'priority', SORT_ASC );

			// Display the fields' HTML
			foreach ( $fields as $field_name => $field_config ) {
				if ( empty( $field_config ) || $this->isFieldDisabled( $field_name ) ) {
					continue;
				}

				if ( empty( $field_config['type'] ) ) {
					$field_config['type'] = 'text';
				}

				$method = "displayField_{$field_config['type']}";
				if ( method_exists( $this, $method ) ) {
					echo call_user_func_array( array( $this, $method ), array(
						$field_name,
						$field_config,
						$instance
					) );
				}
			}
		}

		/**
		 * Get the defined fields sections.
		 *
		 * @return array
		 */
		public function getFieldsSections() {
			return $this->config['fields_sections'];
		}

		/**
		 * Get the defined fields.
		 *
		 * @return array
		 */
		public function getFields() {
			return $this->config['fields'];
		}

		/**
		 * Get the fields belonging to a certain section.
		 *
		 * @param string $section
		 *
		 * @return array
		 */
		public function getSectionFields( $section ) {
			$fields = array();
			foreach ( $this->getFields() as $field_id => $field_config ) {
				if ( ! $this->isFieldDisabled( $field_id ) && $field_config['section'] === $section ) {
					$fields[ $field_id ] = $field_config;
				}
			}

			return $fields;
        }

		/**
		 * Generate the text field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
		public function displayField_text( $field_name, $field_config, $instance ) {
		    // First the value
			$value = $this->getDefault( $field_name );
            if ( isset( $instance[ $field_name ] ) ) {
                $value = $instance[ $field_name ];
            }

            // Now for attributes
            $label = '';
            if ( ! empty( $field_config['label'] ) ) {
                $label = $field_config['label'];
            }

            $desc = '';
            if ( ! empty( $field_config['desc'] ) ) {
                $desc = $field_config['desc'];
            }

            // Lets generate the markup
            $output = '';
            $output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

            if ( ! empty( $label ) ) {
                $output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
            }

            $output .= '<input class="widefat" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" name="' . esc_attr( $this->get_field_name( $field_name ) ) . '" type="text" value="' . esc_attr( $value ) . '" />' . PHP_EOL;

            if ( ! empty( $desc ) ) {
                $output .= '<br />' . PHP_EOL;
                $output .= '<small>' . $desc . '</small>' . PHP_EOL;
            }

            $output .= '</p>' . PHP_EOL;

            return apply_filters( 'pixelgrade_widget_form_text_field_markup', $output, $field_name, $field_config, $instance );
        }

		/**
		 * Generate the textarea field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
		public function displayField_textarea( $field_name, $field_config, $instance ) {
			// First the value
			$value = $this->getDefault( $field_name );
			if ( isset( $instance[ $field_name ] ) ) {
				$value = $instance[ $field_name ];
			}

			// Now for attributes
			$label = '';
			if ( ! empty( $field_config['label'] ) ) {
				$label = $field_config['label'];
			}

			$desc = '';
			if ( ! empty( $field_config['desc'] ) ) {
				$desc = $field_config['desc'];
			}

			$rows = 5;
			if ( ! empty( $field_config['rows'] ) ) {
				$rows = absint( $field_config['rows'] );
			}

			// Lets generate the markup
			$output = '';
			$output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

			if ( ! empty( $label ) ) {
				$output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
			}

			$output .= '<textarea class="widefat" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" name="' . esc_attr( $this->get_field_name( $field_name ) ) . '" rows="' . esc_attr( $rows ) . '" >' . esc_html( $value ) . '</textarea>' . PHP_EOL;

			if ( ! empty( $desc ) ) {
				$output .= '<br />' . PHP_EOL;
				$output .= '<small>' . $desc . '</small>' . PHP_EOL;
			}

			$output .= '</p>' . PHP_EOL;

			return apply_filters( 'pixelgrade_widget_form_textarea_field_markup', $output, $field_name, $field_config, $instance );
		}


		/**
		 * Generate the number field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
        public function displayField_number( $field_name, $field_config, $instance ) {
            // First the value
	        $value = $this->getDefault( $field_name );
            if ( isset( $instance[ $field_name ] ) ) {
                $value = $instance[ $field_name ];
            }

            // Now for attributes
            $label = '';
            if ( ! empty( $field_config['label'] ) ) {
                $label = $field_config['label'];
            }

            $desc = '';
            if ( ! empty( $field_config['desc'] ) ) {
                $desc = $field_config['desc'];
            }

	        $min = '';
	        if ( ! empty( $field_config['min'] ) ) {
		        $min = $field_config['min'];
	        }

	        $max = '';
	        if ( ! empty( $field_config['max'] ) ) {
		        $max = $field_config['max'];
	        }

	        $step = 1;
	        if ( ! empty( $field_config['step'] ) ) {
		        $step = $field_config['step'];
	        }

            // Lets generate the markup
            $output = '';
            $output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

            if ( ! empty( $label ) ) {
                $output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
            }

            $output .= '<input class="widefat" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" name="' . esc_attr( $this->get_field_name( $field_name ) ) . '" type="number" step="' . esc_attr( $step ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" value="' . esc_attr( $value ) . '" />' . PHP_EOL;

            if ( ! empty( $desc ) ) {
                $output .= '<br />' . PHP_EOL;
                $output .= '<small>' . $desc . '</small>' . PHP_EOL;
            }

            $output .= '</p>' . PHP_EOL;

            return apply_filters( 'pixelgrade_widget_form_number_field_markup', $output, $field_name, $field_config, $instance );
        }

		/**
		 * Generate the range field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
		public function displayField_range( $field_name, $field_config, $instance ) {
			// First the value
			$value = $this->getDefault( $field_name );
			if ( isset( $instance[ $field_name ] ) ) {
				$value = $instance[ $field_name ];
			}

			// Now for attributes
			$label = '';
			if ( ! empty( $field_config['label'] ) ) {
				$label = $field_config['label'];
			}

			$desc = '';
			if ( ! empty( $field_config['desc'] ) ) {
				$desc = $field_config['desc'];
			}

			$min = '';
			if ( ! empty( $field_config['min'] ) ) {
				$min = $field_config['min'];
			}

			$max = '';
			if ( ! empty( $field_config['max'] ) ) {
				$max = $field_config['max'];
			}

			$step = '';
			if ( ! empty( $field_config['step'] ) ) {
				$step = $field_config['step'];
			}

			// Lets generate the markup
			$output = '';
			$output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

			if ( ! empty( $label ) ) {
				$output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
			}

			$output .= '<input class="widget-range" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" name="' . esc_attr( $this->get_field_name( $field_name ) ) . '" type="range" step="' . esc_attr( $step ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" value="' . esc_attr( $value ) . '" />' . PHP_EOL;
			$output .= '<input class="range-value" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" name="' . esc_attr( $this->get_field_name( $field_name ) ) . '" type="number" step="' . esc_attr( $step ) . '" min="' . esc_attr( $min ) . '" max="' . esc_attr( $max ) . '" value="' . esc_attr( $value ) . '" />' . PHP_EOL;

			if ( ! empty( $desc ) ) {
				$output .= '<br />' . PHP_EOL;
				$output .= '<small>' . $desc . '</small>' . PHP_EOL;
			}

			$output .= '</p>' . PHP_EOL;

			return apply_filters( 'pixelgrade_widget_form_range_field_markup', $output, $field_name, $field_config, $instance );
		}

		/**
		 * Generate the checkbox field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
        public function displayField_checkbox( $field_name, $field_config, $instance ) {
            // First the value
	        $value = $this->getDefault( $field_name );
            if ( isset( $instance[ $field_name ] ) ) {
                $value = $instance[ $field_name ];
            }

            // Now for attributes
            $label = '';
            if ( ! empty( $field_config['label'] ) ) {
                $label = $field_config['label'];
            }

            $desc = '';
            if ( ! empty( $field_config['desc'] ) ) {
                $desc = $field_config['desc'];
            }

            // Lets generate the markup
            $output = '';
            $output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

            $output .= '<input class="checkbox" type="checkbox" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" name="' . esc_attr( $this->get_field_name( $field_name ) ) . '" value="1" ' . checked( $value, 1, false ) . '" />' . PHP_EOL;

            if ( ! empty( $label ) ) {
                $output .= '<label for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
            }

            if ( ! empty( $desc ) ) {
                $output .= '<br />' . PHP_EOL;
                $output .= '<small>' . $desc . '</small>' . PHP_EOL;
            }

            $output .= '</p>' . PHP_EOL;

            return apply_filters( 'pixelgrade_widget_form_checkbox_field_markup', $output, $field_name, $field_config, $instance );
        }

		/**
		 * Generate the select field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
        public function displayField_select( $field_name, $field_config, $instance ) {
            // First the value
	        $value = $this->getDefault( $field_name );
            if ( isset( $instance[ $field_name ] ) ) {
                $value = $instance[ $field_name ];
            }

            $output = '';

            // If we have been given a callback we will rely on it to generate the markup
            if ( ! empty( $field_config['callback'] ) && is_callable( $field_config['callback'] ) ) {
                $output = call_user_func_array( $field_config['callback'], array( $value, $field_name, $field_config ) );
            } else {

                // Now for attributes
                $label = '';
                if ( ! empty( $field_config['label'] ) ) {
                    $label = $field_config['label'];
                }

                $desc = '';
                if ( ! empty( $field_config['desc'] ) ) {
                    $desc = $field_config['desc'];
                }

                // Lets generate the markup
                $output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

                if ( ! empty( $label ) ) {
                    $output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
                }

                if ( ! empty( $field_config['options'] ) ) {
                	$options = $field_config['options'];
                	// If we have been given a callback that returns the options, then we should give it a call
	                if ( is_callable( $options ) ) {
		                $options = call_user_func_array( $options, array() );
	                }

	                // Standardize the empty
	                if ( empty( $options ) ) {
		                $options = array();
	                }

	                // Handle the setting for multiple values
	                $multiple = '';
	                if ( ! empty( $field_config['multiple'] ) && true === $field_config['multiple'] ) {
	                	$multiple = '[]';
	                }

	                $output .= '<select name="' . esc_attr( $this->get_field_name( $field_name . $multiple ) ) . '" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" class="widefat">' . PHP_EOL;
                    foreach ( $options as $option_value => $option_name ) {
                        $output .= '<option value="' . esc_attr( $option_value ) . '" ' . $this->selected( $value, $option_value, false ) . '>' . $option_name . '</option>' . PHP_EOL;
                    }
                    $output .= '</select>' . PHP_EOL;
                }

                if ( ! empty( $desc ) ) {
                    $output .= '<br />' . PHP_EOL;
                    $output .= '<small>' . $desc . '</small>' . PHP_EOL;
                }

                $output .= '</p>' . PHP_EOL;
            }

            return apply_filters( 'pixelgrade_widget_form_select_field_markup', $output, $field_name, $field_config, $instance );
        }

		/**
		 * Generate the select2 field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
		public function displayField_select2( $field_name, $field_config, $instance ) {
			// First the value
			$value = $this->getDefault( $field_name );
			if ( isset( $instance[ $field_name ] ) ) {
				$value = $instance[ $field_name ];
			}

			$output = '';

			// If we have been given a callback we will rely on it to generate the markup
			if ( ! empty( $field_config['callback'] ) && is_callable( $field_config['callback'] ) ) {
				$output = call_user_func_array( $field_config['callback'], array( $value, $field_name, $field_config ) );
			} else {

				// Now for attributes
				$label = '';
				if ( ! empty( $field_config['label'] ) ) {
					$label = $field_config['label'];
				}

				$desc = '';
				if ( ! empty( $field_config['desc'] ) ) {
					$desc = $field_config['desc'];
				}

				// Lets generate the markup
				$output .= '<p class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

				if ( ! empty( $label ) ) {
					$output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
				}

				if ( ! empty( $field_config['options'] ) ) {
					$options = $field_config['options'];
					// If we have been given a callback that returns the options, then we should give it a call
					if ( is_callable( $options ) ) {
						$options = call_user_func_array( $options, array( $field_name, $field_config, $instance ) );
					}

					// Standardize the empty
					if ( empty( $options ) ) {
						$options = array();
					}

					// Handle the setting for multiple values
					$multiple = '';
					if ( ! empty( $field_config['multiple'] ) && true === $field_config['multiple'] ) {
						$multiple = '[]';
					}

					$output .= '<select name="' . esc_attr( $this->get_field_name( $field_name . $multiple ) ) . '" id="' . esc_attr( $this->get_field_id( $field_name ) ) . '" class="widefat js-select2" ' . ( $multiple === '' ? '' : 'multiple="multiple"' ) . ' style="width:100%;">' . PHP_EOL;
					foreach ( $options as $option_value => $option_name ) {
						$output .= '<option value="' . esc_attr( $option_value ) . '" ' . $this->selected( $value, $option_value, false ) . '>' . $option_name . '</option>' . PHP_EOL;
					}
					$output .= '</select>' . PHP_EOL;
				}

				if ( ! empty( $desc ) ) {
					$output .= '<br />' . PHP_EOL;
					$output .= '<small>' . $desc . '</small>' . PHP_EOL;
				}

				$output .= '</p>' . PHP_EOL;
			}

			return apply_filters( 'pixelgrade_widget_form_select_field_markup', $output, $field_name, $field_config, $instance );
		}

		/**
		 * Generate the radio group field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
        public function displayField_radio_group( $field_name, $field_config, $instance ) {
            // First the value
	        $value = $this->getDefault( $field_name );
            if ( isset( $instance[ $field_name ] ) ) {
                $value = $instance[ $field_name ];
            }

            $output = '';

            // If we have been given a callback we will rely on it to generate the markup
            if ( ! empty( $field_config['callback'] ) && is_callable( $field_config['callback'] ) ) {
                $output = call_user_func_array( $field_config['callback'], array( $value, $field_name, $field_config ) );
            } else {

                // Now for attributes
                $label = '';
                if ( ! empty( $field_config['label'] ) ) {
                    $label = $field_config['label'];
                }

                $desc = '';
                if ( ! empty( $field_config['desc'] ) ) {
                    $desc = $field_config['desc'];
                }

                // Lets generate the markup
                $output .= '<div class="pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

                if ( ! empty( $label ) ) {
                    $output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
                }

                if ( ! empty( $field_config['options'] ) ) {
                    $output .= '<ul>' . PHP_EOL;
                    foreach ( $field_config['options'] as $option_value => $option_name ) {
                        $output .= '<li><label><input id="' . esc_attr( $this->get_field_id( $field_name ) ) . '-' . esc_attr( $option_value ) . '" name="' . $this->get_field_name( $field_name ) . '" type="radio" value="' . esc_attr( $option_value ) . '" ' . checked( $option_value, $value, false ) . ' /> ' . $option_name . '</label></li>' . PHP_EOL;
                    }
                    $output .= '</ul>' . PHP_EOL;
                }

                if ( ! empty( $desc ) ) {
                    $output .= '<br />' . PHP_EOL;
                    $output .= '<small>' . $desc . '</small>' . PHP_EOL;
                }

                $output .= '</div>' . PHP_EOL;
            }

            return apply_filters( 'pixelgrade_widget_form_select_field_markup', $output, $field_name, $field_config, $instance );
        }

		/**
		 * Generate the image field markup.
		 *
		 * @param string $field_name The name if the field.
		 * @param array $field_config The field config.
		 * @param array $instance The current widget instance details.
		 *
		 * @return string The field HTML markup.
		 */
		public function displayField_image( $field_name, $field_config, $instance ) {
			// First the value
			$value = $this->getDefault( $field_name );
			if ( isset( $instance[ $field_name ] ) ) {
				$value = $instance[ $field_name ];
			}

			// Now for attributes
			$label = '';
			if ( ! empty( $field_config['label'] ) ) {
				$label = $field_config['label'];
			}

			$desc = '';
			if ( ! empty( $field_config['desc'] ) ) {
				$desc = $field_config['desc'];
			}

			$button_label = esc_html__( 'Select Image', '__components_txtd' );
			if ( ! empty( $field_config['button_label'] ) ) {
				$button_label = $field_config['button_label'];
			}

			$clear_label = esc_html__( 'Clear', '__components_txtd' );
			if ( ! empty( $field_config['clear_label'] ) ) {
				$clear_label = $field_config['clear_label'];
			}

			$id_prefix = $this->get_field_id( $field_name );

			// Lets generate the markup
			$output = '';
			$output .= '<div class="pixelgrade_image_field pixelgrade-widget-' . esc_attr( $field_name ) . $this->displayOnClass( $field_name, $field_config ) . '" style="' . ( empty( $field_config['hidden'] ) ? '' : 'display: none;' ) . '" ' . $this->displayOnAttributes( $field_name, $field_config ) . '>' . PHP_EOL;

			if ( ! empty( $label ) ) {
				$output .= '<label class="customize-control-title" for="' . esc_attr( $this->get_field_id( $field_name ) ) . '">' . $label . '</label>' . PHP_EOL;
			}

			// Output the image preview
			$output .= '<div class="pixelgrade_image_preview" id="' . $this->get_field_id( $field_name . '-preview' ) . '">' . PHP_EOL;
			// The clear button
			$output .= '<span class="clear-image" onclick="widgetImageFields.clear( \'' .  $this->id . '\', \'' . $id_prefix . '\' ); return false;" >' . $clear_label . '</span>' . PHP_EOL;

			$imageurl = '';
			if ( ! empty( $value ) ) {
				$image_details = wp_get_attachment_image_src( $value, 'full' );
				if ( ! empty( $image_details ) ) {
					$imageurl = $instance['imageurl'] = reset( $image_details );
				}

				$image_srcset = wp_get_attachment_image_srcset( $value, 'full' );
				if ( $image_srcset ) {
					$instance['srcset'] = $image_srcset;

					$image_sizes = wp_get_attachment_image_sizes( $value, 'full' );
					if ( $image_sizes ) {
						$instance['sizes'] = $image_sizes;
					}
				}
			}

			if ( $value > 0 ) {
				$output .= wp_get_attachment_image( $value, 'full' ) . PHP_EOL;
			}

			$output .= '</div>' . PHP_EOL;
			// End of image preview

			$output .= '<input type="submit" class="button" name="' . $this->get_field_name( $field_name . '-button' ) . '" id="' . esc_attr( $this->get_field_id( $field_name . '-button' ) ) . '" value="' . $button_label . '" onclick="widgetImageFields.uploader( \'' .  $this->id . '\', \'' . $id_prefix . '\' ); return false;" />' . PHP_EOL;

			// This hidden field holds our field value (the attachment ID)
			$output .= '<input type="hidden" id="'. $this->get_field_id( $field_name ) . '" name="'. $this->get_field_name( $field_name ) . '" value="' . $value . '" />' . PHP_EOL;

			$output .= '<input type="hidden" id="' . $this->get_field_id( $field_name . '-imageurl' ) . '" name="' . $this->get_field_name( $field_name . '-imageurl' ) . '" value="' . $imageurl . '" />' . PHP_EOL;

			if ( ! empty( $desc ) ) {
				$output .= '<br />' . PHP_EOL;
				$output .= '<small>' . $desc . '</small>' . PHP_EOL;
			}

			$output .= '</div>' . PHP_EOL;

			return apply_filters( 'pixelgrade_widget_form_image_field_markup', $output, $field_name, $field_config, $instance );
		}

		/**
		 * Get the field class when a field uses the display_on logic.
		 *
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return string
		 */
		public function displayOnClass( $field_name, $field_config ) {
            $class = '';
            if ( isset( $field_config['display_on'] ) ) {
                $class = ' widget-field-display_on';
            }

            return $class;
        }

		/**
		 * Get the field attributes when a field uses the display_on logic.
		 *
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return string
		 */
        public function displayOnAttributes( $field_name, $field_config ) {
            $requires = '';
            if ( isset( $field_config['display_on'] ) ) {
                $display_on = $field_config['display_on'];

                if ( isset( $display_on['display'] ) && ! empty( $display_on['display'] ) ) {
                	// 'display' is set to true or the like
                    $requires .= ' data-action="show"';
                } else {
	                // 'display' is not set or is false
                    $requires .= ' data-action="hide"';
                }

                if ( isset( $display_on['on'] ) && is_array( $display_on['on'] ) ) {

                    $on = $display_on['on'];

                    $requires .= ' data-when_key="' . $this->get_field_name( $on['field'] ) . '"';

                    if ( is_array( $on['value'] ) ) {
                        $requires .= ' data-has_value=\'' . json_encode( $on['value'] ) . '\'';
                    } else {
                        $requires .= ' data-has_value="' . $on['value'] . '"';
                    }
                }
            }

            return $requires;
        }

        /**
         * Handles updating the settings for the current widget instance.
         *
         * @access public
         *
         * @param array $new_instance New settings for this instance as input by the user via
         *                            WP_Widget::form().
         * @param array $old_instance Old settings for this instance.
         * @return array Updated settings to save.
         */
        public function update( $new_instance, $old_instance ) {
            $instance = $old_instance;

            $sanitized_instance = $this->sanitizeFields( $new_instance );

            // We only save the fields that have been defined
            foreach ( $this->getFields() as $field_name => $field_config ) {
                // We only want to save entries for the fields currently supports by the current widget type
	            // No disabled fields!
                if ( isset( $sanitized_instance[ $field_name ] ) ) {
	                // First put in the default value
	                $instance[ $field_name ] = $this->getDefault( $field_name );
	                // Now determine if we use the new value
                	if ( null !== $sanitized_instance[ $field_name ] ) {
		                $instance[ $field_name ] = $sanitized_instance[ $field_name ];
	                }
                }
            }

            /**
             * Filters Widget settings before they're saved.
             *
             * @param array $instance The santized widget instance. Only contains data processed by the current widget.
             * @param array $new_instance The new widget instance before sanitization.
             */
            $instance = apply_filters( 'pixelgrade_before_widget_saving', $instance, $new_instance );

            return $instance;
        }

		/**
		 * Apply filter callbacks for field values, if they are configured (per field).
		 *
		 * @param array $instance The current widget details.
		 *
		 * @return array
		 */
		public function applyFilters( $instance ) {
			// Make sure this is an array
			$instance = (array) $instance;

			foreach( $this->getFields() as $field_name => $field_config ) {
				if ( isset( $field_config['filter_callback'] ) && is_callable( $field_config['filter_callback'] ) ) {
					$instance[ $field_name ] = call_user_func( $field_config['filter_callback'], $instance[ $field_name ] );
				}
			}

			return $instance;
		}

		/**
		 * Sanitize the field values in the current instance.
		 *
		 * @param array $instance The current widget details.
		 *
		 * @return array
		 */
		public function sanitizeFields( $instance ) {
            // Make sure this is an array
            $instance = (array) $instance;

            // We need to remember if the instance was empty to being with
			// We will interpret this as being an initial instance that should use the default values (mostly important for checkboxes).
            $unsaved_instance = false;
            if ( count( $instance ) === 0 ) {
	            $unsaved_instance = true;
            }

            foreach( $this->getFields() as $field_name => $field_config ) {
	            if ( $this->isFieldDisabled( $field_name ) ) {
		            // We want to keep a clean instance, hence we don't want values for fields that are disabled
		            unset( $instance[ $field_name ] );
		            continue;
	            }

	            // Make sure the type is in place
	            if ( empty( $field_config['type'] ) ) {
		            $field_config['type'] = self::$default_field_type;
	            }

	            // Make sure the section is in place
	            if ( empty( $field_config['section'] ) ) {
		            $field_config['section'] = self::$default_field_section;
	            }

	            // If the field value is not set
	            if ( ! isset( $instance[ $field_name ] ) ) {
	            	// If it is a checkbox (that doesn't send the input when not checked)
		            // we need to do special handling to distinguish between an initial state (that should use the default value)
		            // and an update that should mark the checkbox as unchecked.
                    if ( $field_config['type'] === 'checkbox' && false === $unsaved_instance ) {
	                    // Give it an empty value, that will be sanitized
	                    $instance[ $field_name ] = '0';
                    } else {
                    	// Give it the default value
	                    $instance[ $field_name ] = $this->getDefault( $field_name );
                    }
	            }

	            if ( isset( $field_config['sanitize_callback'] ) && is_callable( $field_config['sanitize_callback'] ) ) {
		            $instance[ $field_name ] = call_user_func_array( $field_config['sanitize_callback'], array(
			            $instance[ $field_name ],
			            $field_name,
			            $field_config,
		            ) );
	            } elseif ( method_exists( $this, "sanitize_{$field_config['type']}" ) ) {
		            // Default to the field type sanitization, if available
		            $instance[ $field_name ] = call_user_func_array( array(
			            $this,
			            "sanitize_{$field_config['type']}"
		            ), array( $instance[ $field_name ], $field_name, $field_config, ) );
	            }
            }

            return $instance;
        }

		/**
		 * Sanitize a checkbox field value.
		 *
		 * @param mixed $value
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return bool
		 */
		public function sanitize_checkbox( $value, $field_name, $field_config ) {
            return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
        }

		/**
		 * Sanitize a positive int.
		 *
		 * @param mixed $value
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return int
		 */
		public function sanitize_positive_int( $value, $field_name, $field_config ) {
            return absint( $value );
        }

		/**
		 * Sanitize a text field.
		 *
		 * @param mixed $value
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return string
		 */
		public function sanitize_text( $value, $field_name, $field_config ) {
            return sanitize_text_field( $value );
        }

		/**
		 * Sanitize a textarea field.
		 *
		 * @param mixed $value
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return string
		 */
		public function sanitize_textarea( $value, $field_name, $field_config ) {
			// Handle invalid UTF8 characters
			$filtered = wp_check_invalid_utf8( $value );

			if ( strpos($filtered, '<') !== false ) {
				// Allow others to filter the allowed tags
				$allowed_tags = apply_filters( 'pixelgrade_widget_allowed_textarea_html_tags',
					array(
						'a' => array(
							'href' => array(),
							'title' => array(),
						),
						'strong' => array(),
						'b' => array(),
						'em' => array(),
						'u' => array(),
						'span' => array(
							'class' => array(),
						),
					), $field_name, $field_config );

				$filtered = wp_kses( $filtered, $allowed_tags );
			}

			// Remove new lines by default (define 'keep_newlines' to true in the field config to skip this)
			if ( ! isset( $field_config['keep_newlines'] ) || true !== $field_config['keep_newlines'] ) {
				$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
			}

			// Trim the whitespaces off the beginning and the end
			$filtered = trim( $filtered );

			return $filtered;
		}

		/**
		 * Sanitize a select field.
		 *
		 * @param mixed $value
		 * @param string $field_name
		 * @param array $field_config
		 *
		 * @return mixed
		 */
		public function sanitize_select( $value, $field_name, $field_config ) {
            // If this select has no options, any value is NOT good
            if ( empty( $field_config['options'] ) ) {
                return false;
            }

            if ( ! in_array( $value, array_keys( $field_config['options'] ) ) ) {
                // Fallback on the default value
                if ( isset( $field_config['default'] ) ) {
                    return $field_config['default'];
                } else {
                    return false;
                }
            }

            // All is good
            return $value;
        }

		// @todo Should consider this
		public function sanitize_select2( $value, $field_name, $field_config ) {
//			// If this select has no options, any value is NOT good
//			if ( empty( $field_config['options'] ) ) {
//				return false;
//			}
//
//			if ( ! in_array( $value, array_keys( $field_config['options'] ) ) ) {
//				// Fallback on the default value
//				if ( isset( $field_config['default'] ) ) {
//					return $field_config['default'];
//				} else {
//					return false;
//				}
//			}

			// All is good
			return $value;
		}

		/**
		 * Outputs the html selected attribute.
		 *
		 * This is a modified version of the core selected() to take into account multiple values, not just one,
		 * like in the case for multiple selects or select2 with multiple.
		 *
		 * @param mixed $selected One or more of the values to compare
		 * @param mixed $current  (true) The other value to compare if not just true
		 * @param bool  $echo     Whether to echo or just return the string
		 * @return string html attribute or empty string
		 */
		public function selected( $selected, $current = true, $echo = true ) {
			if ( ! is_array( $selected ) ) {
				return __checked_selected_helper( $selected, $current, $echo, 'selected' );
			} else {
				if ( in_array( $current, $selected ) ) {
					// It is definitely selected - force it to be so
					return __checked_selected_helper( 'yes', 'yes', $echo, 'selected' );
				}
			}

			return '';
		}

        /**
         * We check the $config and determine if this field should be show or not.
         *
         * @param string $field_name
         *
         * @return bool
         */
        public function isFieldDisabled( $field_name ) {
            if ( empty( $field_name ) ) {
                return false;
            }

            if ( ! empty( $this->config['fields'][$field_name]['disabled'] ) ) {
                return true;
            }

            return false;
        }

        /**
         * We check the $config and determine if this field should be hidden or not.
         *
         * @param string $field_name
         *
         * @return bool
         */
        public function isFieldHidden( $field_name ) {
            if ( empty( $field_name ) ) {
                return false;
            }

            if ( ! empty( $this->config['fields'][$field_name]['hidden'] ) ) {
                return true;
            }

            return false;
        }

		/**
		 * We check the $config and determine if this field type is used by any active field.
		 *
		 * @param string $field_type
		 * @param array $fields
		 *
		 * @return bool
		 */
		public function isFieldTypeUsed( $field_type, $fields = array() ) {
			if ( empty( $field_type ) ) {
				return false;
			}

			// If we are not given any fields, we default to all the fields
			if ( empty( $fields ) ) {
				$fields = $this->getFields();
			}

			// No fields, no used field type, doh!
			if ( empty( $fields ) ) {
				return false;
			}

			foreach ( $fields as $field_name => $field_config ) {
				if ( empty( $field_config ) || $this->isFieldDisabled( $field_name ) ) {
					continue;
				}

				if ( empty( $field_config['type'] ) ) {
					$field_config['type'] = 'text';
				}

				if ( $field_config['type'] == $field_type ) {
					return true;
				}
			}

			return false;
		}

        /**
         * Return an associative array of default values
         *
         * These values are used in new widgets.
         *
         * @access public
         *
         * @return array Array of default values for the Widget's options
         */
        public function getDefaults() {
            $defaults = array();

            if ( empty( $this->config['fields'] ) ) {
                return $defaults;
            }

            foreach ( $this->config['fields'] as $field_id => $field_config ) {
                if ( ! empty( $field_config['default'] ) ) {
                    $defaults[ $field_id ] = $field_config['default'];
                }
            }

            return $defaults;
        }

        /**
         * Get the default value for a certain field
         *
         * @access public
         *
         * @param string $field_name
         *
         * @return mixed|null The default value found or null when field is not found.
         */
        public function getDefault( $field_name ) {
            if ( isset( $this->config['fields'][ $field_name ]['default'] ) ) {
                return $this->config['fields'][ $field_name ]['default'];
            }

            return null;
        }

		/**
		 * Get the default state for a certain fields section.
		 *
		 * @access public
		 *
		 * @param string $section_id
		 *
		 * @return mixed|null The state found or the default section state.
		 */
		public function getSectionDefaultState( $section_id ) {
			if ( isset( $this->config['fields_sections'][ $section_id ]['default_state'] ) ) {
				return $this->config['fields_sections'][ $section_id ]['default_state'];
			}

			return self::$default_field_section_state;
		}
	}

endif;
