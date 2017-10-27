<?php
/**
 * Block class
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pixelgrade_Block class.
 */
abstract class Pixelgrade_Block {

	/**
	 * Incremented with each new class instantiation, then stored in $instance_number.
	 *
	 * Used when sorting two instances whose priorities are equal.
	 *
	 * @static
	 * @access protected
	 * @var int
	 */
	protected static $instance_count = 0;

	/**
	 * Order in which this instance was created in relation to other instances.
	 * @access public
	 * @var int
	 */
	public $instance_number;

	/**
	 * Blocks manager.
	 *
	 * @access public
	 * @var Pixelgrade_BlocksManager
	 */
	public $manager;

	/**
	 * Block ID.
	 *
	 * @access public
	 * @var string
	 */
	public $id;

	/**
	 * Order priority to load the block in case there are multiple siblings.
	 *
	 * @access public
	 * @var int
	 */
	public $priority = 10;

	/**
	 * Block's Type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = '';

	/**
	 * Block's wrappers.
	 *
	 * It can either be a string in which case $end_wrappers needs to be provided, or an array of wrapper(s) instances.
	 *
	 * @access public
	 * @var string|array
	 */
	public $wrappers = array();

	/**
	 * Block's end wrappers.
	 *
	 * Only used if $wrappers is given as a string.
	 *
	 * @access public
	 * @var string
	 */
	public $end_wrappers = null;

	/**
	 * Checks to be evaluated at render time.
	 *
	 * @access public
	 * @var array
	 */
	public $checks = array();

	/**
	 * Constructor.
	 *
	 * Supplied `$args` override class property defaults.
	 *
	 * If `$args['settings']` is not defined, use the $id as the setting ID.
	 *
	 *
	 * @param Pixelgrade_BlocksManager $manager Pixelgrade_BlocksManager instance.
	 * @param string               $id      Block ID.
	 * @param array                $args    {
	 *     Optional. Arguments to override class property defaults.
	 *
	 *     @type string               $id              Block ID.
	 *     @type int                  $priority        Order priority to load the block. Default 10.
	 *     @type string|array         $wrappers        The block's wrappers. It can be a string or an array of Pixelgrade_BlockWrapper instances.
	 *     @type string               $end_wrappers    The block's end wrappers if $wrappers was string.
	 *     @type array                $checks          The checks config to determine at render time if this block should be rendered.
	 *     @type string               $type            Block type. Core blocks include 'layout', 'template', 'callback'.
	 * }
	 * @param Pixelgrade_Block $parent Optional. The block instance that contains the definition of this block (that first instantiated this block)
	 */
	public function __construct( $manager, $id, $args = array(), $parent = null ) {
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		$this->manager = $manager;
		$this->id = $id;
		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;

		// We need to check the wrappers and replace them with Pixelgrade_Wrapper instances (if they are not already)
		$this->maybeConvertWrappers();
	}

	protected function maybeConvertWrappers() {
		// Bail if there are no wrappers
		if ( empty( $this->wrappers ) ) {
			// Make sure the we coerce it to being an array
			$this->wrappers = array();
			return;
		}

		// $wrappers should usually be an array
		// But we also offer support for two short hand versions
		// - a callback
		// - inline wrapper markup (in this case $end_wrappers will be used as closing markup)
		if ( is_string( $this->wrappers ) || ( is_array( $this->wrappers ) && isset( $this->wrappers['callback'] ) ) ) {
			// Standardize it
			$this->wrappers = array( $this->wrappers );
		}

		// To be sure we are not bother with the intricacies of foreach
		// (whether or not it makes a copy of the array it iterates over, and what does it copy)
		// we will recreate the array.
		$new_wrappers = array();

		foreach ( $this->wrappers as $wrapper_id => $wrapper ) {
			if ( $wrapper instanceof Pixelgrade_Wrapper ) {
				// We are good
				$new_wrappers[ $wrapper_id ] = $wrapper;
				continue;
			} elseif ( is_string( $wrapper ) ) {
				// We will treat it as shorthand for just a HTML tag, an inline wrapper markup, or even a callback
				// Since we don't have a priority, we will put a priority with one higher than the last wrapper added
				$priority = 10;
				if ( $previous_wrapper = end( $new_wrappers ) ) {
					$priority = $previous_wrapper->priority + 1;
				}

				if ( Pixelgrade_Wrapper::isInlineTag( $this->wrappers ) ) {
					// We are dealing with a fully qualified opening markup
					// We need to also have the $end_wrappers
					if ( ! empty( $this->end_wrappers ) ) {
						$new_wrappers[] = new Pixelgrade_Wrapper( array( 'tag' => $this->wrappers, 'end_tag' => $this->end_wrappers, 'priority' => $priority, ) );
					} else {
						_doing_it_wrong( __METHOD__, sprintf( 'Failed to add wrapper! Got inline opening markup (%s), but no ending markup. Please provide the `end_wrappers` config also!', htmlspecialchars( $this->wrappers ) ), '1.0.0' );
					}
				} else {
					// This a shorthand tag
					$new_wrappers[ $wrapper_id ] = new Pixelgrade_Wrapper( array( 'tag' => $wrapper, 'priority' => $priority, ) );
				}
			} elseif ( is_array( $wrapper ) ) {
				// This is either a wrapper configuration or a callback configuration
				if ( isset( $wrapper['callback'] ) ) {
					// Since we don't have a priority, we will put a priority with one higher than the last wrapper added
					$priority = 10;
					if ( $previous_wrapper = end( $new_wrappers ) ) {
						$priority = $previous_wrapper->priority + 1;
					}

					// If it's a callback we will treat it as the callback for the tag
					$new_wrappers[ $wrapper_id ] = new Pixelgrade_Wrapper( array( 'tag' => $wrapper, 'priority' => $priority, ) );
				} else {
					// If we don't have a priority, we will put a priority with one higher than the last wrapper added
					if ( ! isset( $wrapper['priority'] ) ) {
						$wrapper['priority'] = 10;
						if ( $previous_wrapper = end( $new_wrappers ) ) {
							$wrapper['priority'] = $previous_wrapper->priority + 1;
						}
					}
					$new_wrappers[ $wrapper_id ] = new Pixelgrade_Wrapper( $wrapper );
				}
			}
		}

		$this->wrappers = $new_wrappers;
	}

	/**
	 * Enqueue control related scripts/styles.
	 */
	public function enqueue() {}

	/**
	 * Evaluate the checks of the block.
	 *
	 * @return bool Returns true if the all the checks have passed, false otherwise
	 */
	final public function evaluateChecks() {
		return Pixelgrade_Config::evaluateChecks( $this->checks );
	}

	/**
	 * Get the block's final HTML, including wrappers.
	 *
	 * @param array $blocks_trail The current trail of parent blocks.
	 *
	 * @return string The entire markup produced by the block.
	 */
	final public function getRendered( $blocks_trail = array() ) {
		// Initialize blocks trail if empty
		if ( empty( $blocks_trail ) ) {
			$blocks_trail[] = $this;
		}

		// Start the output buffering
		ob_start();

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### Before maybeRender() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}

		/**
		 * Fires before the current block is maybe rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_before_block', $this, $blocks_trail );

		/**
		 * Fires before a specific block is maybe rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_before_block_{$this->id}", $this, $blocks_trail );

		/* ======================
		 * Maybe do the rendering
		 */
		$this->maybeRender( $blocks_trail );

		/**
		 * Fires after the current block has been maybe rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_after_block', $this, $blocks_trail );

		/**
		 * Fires after a specific block has been maybe rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_after_block_{$this->id}", $this, $blocks_trail );

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### After maybeRender() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}

		// Get the output buffer and end it
		return ob_get_clean();
	}

	/**
	 * Evaluate checks and render the block, including wrappers.
	 *
	 * @uses Pixelgrade_Block::render()
	 *
	 * @param array $blocks_trail The current trail of parent blocks.
	 */
	final public function maybeRender( $blocks_trail = array() ) {
		if ( ! $this->evaluateChecks() ) {
			return;
		}

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### Before render() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}

		/**
		 * Fires just before the current block is rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_before_render_block', $this, $blocks_trail );

		/**
		 * Fires just before a specific block is rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_before_render_block_{$this->id}", $this, $blocks_trail );

		/* ======================
		 * Do the rendering
		 */
		$this->render( $blocks_trail );

		/**
		 * Fires just after the current block is rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_after_render_block', $this, $blocks_trail );

		/**
		 * Fires just after a specific block is rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_after_render_block_{$this->id}", $this, $blocks_trail );

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### After render() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}
	}

	/**
	 * Renders the block's wrappers and calls $this->getRenderedContent() for the internals.
	 *
	 * @param array $blocks_trail The current trail of parent blocks.
	 */
	protected function render( $blocks_trail = array() ) {
		// Initialize blocks trail if empty
		if ( empty( $blocks_trail ) ) {
			$blocks_trail[] = $this;
		}

		// Since there might be wrappers that shouldn't be shown when there is no content
		// we first need to get the content, process the wrappers and then output everything.
		$content = $this->getRenderedContent( $blocks_trail );

		// We need to determine if the content is empty before we start wrapping it
		// because the wrapper $display_on_empty_content refers to the actual content regardless of any wrapper!
		$empty_content = false;
		if ( '' == trim( $content ) ) {
			$empty_content = true;
		}

		// Order the wrappers according to their priority,
		// highest priority first (DESC by priority) because we want to start wrapping from the most inner wrappers
		$wrappers = Pixelgrade_Block::orderWrappers( $this->wrappers );

		/**
		 * Filter the wrappers just before the current block is wrapped.
		 *
		 * @param array $wrappers The wrappers array of Pixelgrade_Wrapper instances.
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		$wrappers = apply_filters( 'pixelgrade_render_block_wrappers', $wrappers, $this, $blocks_trail );

		/**
		 * Filter the wrappers just before the current block is wrapped.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param array $wrappers The wrappers array of Pixelgrade_Wrapper instances.
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		$wrappers = apply_filters( "pixelgrade_render_block_{$this->id}_wrappers", $wrappers, $this, $blocks_trail );

		// Now render the wrappers
		/** @var Pixelgrade_Wrapper $wrapper */
		foreach ( $wrappers as $wrapper ) {
			// Wrappers that have $display_on_empty_content false, do not output anything if there is no content
			if ( false === $wrapper->display_on_empty_content && $empty_content ) {
				// We need to skip this wrapper
				continue;
			}

			$content = $wrapper->maybeWrapContent( $content );
		}

		echo $content;
	}

	/**
	 * Get the block's rendered content, without the wrappers.
	 *
	 * @param array $blocks_trail The current trail of parent blocks.
	 *
	 * @return string Contents of the block.
	 */
	final public function getRenderedContent( $blocks_trail = array() ) {
		// Start the output buffering
		ob_start();

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### Before maybeRenderContent() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}

		/**
		 * Fires before the current block content is maybe rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_before_block_content', $this, $blocks_trail );

		/**
		 * Fires before a specific block content is maybe rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_before_block_{$this->id}_content", $this, $blocks_trail );

		/* =============================
		 * Maybe do the content rendering
		 */
		$this->maybeRenderContent( $blocks_trail );

		/**
		 * Fires after the current block content has been maybe rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_after_block_content', $this, $blocks_trail );

		/**
		 * Fires after a specific block content has been maybe rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_after_block_{$this->id}_content", $this, $blocks_trail );

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### After maybeRenderContent() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}

		// Get the output buffer and end it
		return ob_get_clean();
	}

	/**
	 * Evaluate checks and render the block content.
	 *
	 * @uses Pixelgrade_Block::renderContent()
	 *
	 * @param array $blocks_trail The current trail of parent blocks.
	 */
	final public function maybeRenderContent( $blocks_trail = array() ) {
		if ( ! $this->evaluateChecks() ) {
			return;
		}

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### Before renderContent() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}

		/**
		 * Fires just before the current block content is rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_before_render_block_content', $this, $blocks_trail );

		/**
		 * Fires just before a specific block content is rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_before_render_block_{$this->id}_content", $this, $blocks_trail );

		/* ==============================
		 * Do the block content rendering
		 */
		$this->renderContent( $blocks_trail );

		/**
		 * Fires just after the current block content has been rendered.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( 'pixelgrade_after_render_block_content', $this, $blocks_trail );

		/**
		 * Fires just after a specific block content has been rendered.
		 *
		 * The dynamic portion of the hook name, `$this->id`, refers to
		 * the block ID.
		 *
		 * @param Pixelgrade_Block $this Pixelgrade_Block instance.
		 * @param array $blocks_trail The current trail of parent blocks.
		 */
		do_action( "pixelgrade_after_render_block_{$this->id}_content", $this, $blocks_trail );

		if ( pixelgrade_is_block_debug() ) {
			echo PHP_EOL . str_repeat( "\t", count( $blocks_trail ) ) . sprintf( '<!-- ### After renderContent() block \'%s\' ### -->', $this->id ) . PHP_EOL;
		}
	}

	/**
	 * Render the control's content.
	 *
	 * Allows the content to be overridden without having to rewrite the wrapper in `$this::render()`.
	 *
	 * @param array $blocks_trail The current trail of parent blocks.
	 */
	abstract protected function renderContent( $blocks_trail = array() );

	/**
	 * Render the control's JS template.
	 *
	 * This function is only run for control types that have been registered with
	 * WP_Customize_Manager::register_control_type().
	 *
	 * In the future, this will also print the template for the control's container
	 * element and be override-able.
	 */
	final public function printTemplate() {
		?>
		<script type="text/html" id="tmpl-block-<?php echo $this->type; ?>-content">
			<?php $this->contentTemplate(); ?>
		</script>
		<?php
	}

	/**
	 * An Underscore (JS) template for this control's content (but not its container).
	 *
	 * Class variables for this control class are available in the `data` JS object;
	 * export custom variables by overriding WP_Customize_Control::to_json().
	 *
	 * @see WP_Customize_Control::print_template()
	 */
	protected function contentTemplate() {}

	/**
	 * Order a list of wrappers by priority, ascending.
	 *
	 * @param array $list List of wrapper instances to order.
	 * @param string|array $orderby Optional. By what field to order.
	 *                              Defaults to ordering by 'priority' => DESC and 'instance_number' => DESC.
	 * @param string $order Optional. The order direction in case $orderby is a string. Defaults to 'DESC'
	 * @param bool $preserve_keys Optional. Whether to preserve array keys or not. Defaults to true.
	 *
	 * @return array
	 */
	public static function orderWrappers( $list, $orderby = array( 'priority' => 'DESC', 'instance_number' => 'DESC', ), $order = 'DESC', $preserve_keys = true ) {
		if ( ! is_array( $list ) ) {
			return array();
		}

		$util = new Pixelgrade_WrapperListUtil( $list );
		return $util->sort( $orderby, $order, $preserve_keys );
	}

	/**
	 * Given a set of block args and a extended block instance, merge the args.
	 *
	 * @param array $args
	 * @param Pixelgrade_Block $extended_block
	 *
	 * @return array The merged args
	 */
	public static function mergeExtendedBlock( $args, $extended_block ) {
		// Work on a copy
		$new_args = $args;

		// Extract the extended block properties
		$extended_block_props = get_object_vars( $extended_block );

		if ( ! empty( $extended_block_props ) && is_array( $extended_block_props ) ) {
			foreach ( $extended_block_props as $key => $prop ) {
				// If the $args don't specify a certain property present in the extended block, simply copy it over
				if ( ! isset( $args[ $key ] ) && property_exists( __CLASS__, $key ) ) {
					$new_args[ $key ] = $prop;
				} else {
					// The entry is present in both the supplied $args and the extended block
					switch( $key ) {
						case 'wrappers':
							$new_args['wrappers'] = array_merge( $prop, $args['wrappers'] );
							break;
						case 'checks':
							// When it comes to checks they can be in three different forms
							// @see Pixelgrade_Config::evaluateChecks()

							// First, we handle the shorthand version: just a function name
							if ( is_string( $args['checks'] ) ) {
								// We have gotten a single shorthand check - no merging
								$new_args['checks'] = $args['checks'];
								break;
							}

							if ( is_array( $args['checks'] ) && ( isset( $args['checks']['function'] ) || isset( $args['checks']['callback'] ) ) ) {
								// We have gotten a single complex check - no merging
								$new_args['checks'] = $args['checks'];
								break;
							}

							// If we have got an array, merge the two
							$new_args['checks'] = array_merge( Pixelgrade_Config::sanitizeChecks( $prop ), Pixelgrade_Config::sanitizeChecks( $args['checks'] ) );
							break;
						default:
							break;
					}
				}
			}
		}

		return $new_args;
	}
}
