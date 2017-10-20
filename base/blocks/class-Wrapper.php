<?php
/**
 * Wrapper class
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
 * Pixelgrade_Wrapper class.
 */
class Pixelgrade_Wrapper {

	/**
	 * The default tag to use.
	 *
	 * @access public
	 * @var string
	 */
	public static $default_tag = 'div';

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
	 * Wrapper opening tag name (i.e. div or article).
	 *
	 * @access public
	 * @var string
	 */
	public $tag = '';

	/**
	 * Wrapper closing tag if the provided $tag is a fully qualified HTML element opening tag (i.e. <div>).
	 *
	 * @access public
	 * @var string
	 */
	public $end_tag = null;

	/**
	 * ID to add to the wrapper.
	 *
	 * @access public
	 * @var string
	 */
	public $id = '';

	/**
	 * Classes to add to the wrapper.
	 *
	 * @access public
	 * @var array|string
	 */
	public $classes = array();

	/**
	 * Attributes to add to the wrapper.
	 *
	 * @access public
	 * @var array
	 */
	public $attributes = array();

	/**
	 * Whether to display the wrapper if the content is empty. Child wrappers are not considered content.
	 *
	 * @access public
	 * @var bool
	 */
	public $display_on_empty_content = false;

	/**
	 * Order priority to display the wrapper in case there are multiple siblings.
	 *
	 * @access public
	 * @var int
	 */
	public $priority = 10;

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
	 * @param array                $args    {
	 *     Optional. Arguments to override class property defaults.
	 *
	 *     @type string               $tag             Wrapper opening tag name (i.e. div or article).
	 *     @type string               $end_tag         Wrapper closing tag if the provided $tag is a fully qualified HTML element opening tag (i.e. <div>).
	 *     @type string               $id              ID to add to the wrapper.
	 *     @type array|string         $classes         Classes to add to the wrapper.
	 *     @type array|string         $attributes      Attributes to add to the wrapper.
	 *     @type bool     $display_on_empty_content    Whether to display the wrapper if the content is empty.
	 *     @type int                  $priority        Order priority to display the wrapper. Default 10.
	 *     @type array                $checks          The checks config to determine at render time if this wrapper should be displayed.
	 * }
	 */
	public function __construct( $args = array() ) {
		$keys = array_keys( get_object_vars( $this ) );
		foreach ( $keys as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$this->$key = $args[ $key ];
			}
		}

		self::$instance_count += 1;
		$this->instance_number = self::$instance_count;
	}

	/**
	 * Evaluate the checks of the wrapper.
	 *
	 * @return bool Returns true if the all the checks have passed, false otherwise
	 */
	final public function evaluateChecks() {
		return Pixelgrade_Config::evaluateChecks( $this->checks );
	}

	/**
	 * Evaluate checks and wrap the content.
	 *
	 * @param string $content The content to be wrapped
	 *
	 * @return string Wrapped content.
	 */
	final public function maybeWrapContent( $content = '' ) {
		if ( ! $this->evaluateChecks() ) {
			return $content;
		}

		return $this->wrapContent( $content );
	}

	/**
	 * Given some content wrap it and return the modified content.
	 *
	 * @param string $content The content to be wrapped
	 *
	 * @return string Wrapped content.
	 */
	final public function wrapContent( $content = '' ) {
		return $this->getOpeningMarkup() . PHP_EOL . $content . PHP_EOL . $this->getClosingMarkup();
	}

	protected function getOpeningMarkup() {
		// If the given tag starts with a '<' character then we will treat as inline opening markup - no processing
		$tag = $this->getTag();
		if ( self::isInlineTag( $tag ) ) {
			return $tag;
		}

		// We will filter the markup parts to avoid gluing empty entries
		return '<' . implode( ' ', array_filter( array( $tag, self::getIdMarkup( $this->id ), self::getClassMarkup( $this->classes, $this ), self::getAttributesMarkup( $this->attributes, $this ) ) ) ) . '>';
	}

	protected function getClosingMarkup() {
		// If the opening tag starts with a '<' character then we will use $end_tag - no
		$tag = $this->getTag();
		if ( self::isInlineTag( $tag ) ) {
			return $this->getEndTag();
		}

		return "</{$tag}>";
	}

	public static function isInlineTag( $tag ) {
		// If the given tag starts with a '<' character then we will treat as inline opening markup - no processing
		if ( 0 === strpos( trim( $tag ), '<' ) ) {
			return true;
		}

		return false;
	}

	protected function getTag() {
		$tag = $this->tag;

		if ( ! empty( $tag ) && is_callable( $tag ) ) {
			$tag = call_user_func( $tag );
		} else {
			$tag = tag_escape( $tag );
		}

		// Use the default tag
		if ( empty( $tag ) ) {
			$tag = self::$default_tag;
		}

		return $tag;
	}

	protected function getEndTag() {
		$tag = $this->end_tag;

		if ( ! empty( $tag ) && is_callable( $tag ) ) {
			$tag = call_user_func( $tag );
		}

		// Use the default tag
		if ( empty( $tag ) ) {
			$tag = '</' . self::$default_tag . '>';
		}

		return $tag;
	}

	/**
	 * Given an HTML id definition, return the full id attribute (ie. 'id="..."').
	 *
	 * @param string $id
	 * @param Pixelgrade_Wrapper|null $wrapper Optional. The wrapper instance the id belongs to.
	 *
	 * @return string
	 */
	public static function getIdMarkup( $id = '', $wrapper = null ) {
		$prefix = '';
		$suffix = '';

		// We need to save the prefix and suffix before processing the callback because we will overwrite the config
		if ( is_array( $id ) ) {
			if ( ! empty( $id['prefix'] ) ) {
				$prefix = $id['prefix'];
				unset( $id['prefix'] );
			}
			if ( ! empty( $id['suffix'] ) ) {
				$suffix = $id['suffix'];
				unset( $id['suffix'] );
			}
		}

		// Maybe process the defined callback
		$id = self::maybeProcessCallback( $id );

		/**
		 * Filters the HTML id
		 *
		 * @param string $id The HTML id.
		 * @param string $prefix The prefix applied to the id.
		 * @param string $suffix The sufix applied to the id.
		 * @param Pixelgrade_Wrapper|null $wrapper The wrapper instance the id belongs to.
		 */
		$id = apply_filters( 'pixelgrade_wrapper_html_id', $id, $prefix, $suffix, $wrapper );

		if ( ! empty( $id ) ) {
			return 'id="' . esc_attr( Pixelgrade_Value::maybePrefixSuffix( $id, $prefix, $suffix ) ) . '"';
		}

		return '';
	}

	/**
	 * Given an array of class definitions, return the full class attribute (ie. 'class="..."' ).
	 *
	 * @param array $classes
	 * @param Pixelgrade_Wrapper|null $wrapper Optional. The wrapper instance the classes belong to.
	 *
	 * @return string
	 */
	public static function getClassMarkup( $classes = array(), $wrapper = null ) {
		$classes = self::getProcessedClasses( $classes, $wrapper );

		// Glue the attributes
		if ( ! empty( $classes ) ) {
			return 'class="' . join( ' ', $classes ) . '"';
		}

		return '';
	}

	/**
	 * Process an array of classes definition and return the final classes as an array of strings.
	 *
	 * @param array $classes
	 * @param Pixelgrade_Wrapper|null $wrapper Optional. The wrapper instance the classes belong to.
	 *
	 * @return array
	 */
	protected static function getProcessedClasses( $classes = array(), $wrapper = null ) {
		$prefix = '';
		$suffix = '';

		// We need to save the prefix and suffix before processing the callback because we will overwrite the config
		if ( is_array( $classes ) ) {
			if ( ! empty( $classes['prefix'] ) ) {
				$prefix = $classes['prefix'];
				unset( $classes['prefix'] );
			}
			if ( ! empty( $classes['suffix'] ) ) {
				$suffix = $classes['suffix'];
				unset( $classes['suffix'] );
			}
		}

		// Maybe process the defined callback
		$classes = self::maybeProcessCallback( $classes );

		if ( is_string( $classes ) ) {
			$classes = Pixelgrade_Value::maybeSplitByWhitespace( $classes );
		} elseif ( is_array( $classes ) ) {
			$classes = array_map( 'Pixelgrade_Wrapper::maybeProcessCallback', $classes );
		}

		if ( empty( $classes ) ) {
			$classes = array();
		}

		// Add the prefix and suffix, maybe
		$classes = Pixelgrade_Value::maybePrefixSuffix( $classes, $prefix, $suffix );

		// Escape all the classes
		$classes = array_map( 'esc_attr', $classes );

		/**
		 * Filters the list of CSS classes
		 *
		 * @param array $classes An array of classes.
		 * @param string $prefix The prefix applied to all the classes.
		 * @param string $suffix The sufix applied to all the classes.
		 * @param Pixelgrade_Wrapper|null $wrapper The wrapper instance the classes belong to.
		 */
		$classes = apply_filters( 'pixelgrade_wrapper_css_class', $classes, $prefix, $suffix, $wrapper );

		return array_unique( array_filter( $classes ) );
	}

	/**
	 * Process an array of attributes definition and return the final attributes as an array of strings.
	 *
	 * @param array $attributes
	 * @param Pixelgrade_Wrapper|null $wrapper Optional. The wrapper instance the attributes belong to.
	 *
	 * @return string
	 */
	public static function getAttributesMarkup( $attributes = array(), $wrapper = null ) {
		$attributes = self::maybeProcessCallback( $attributes );

		// Bail early
		if ( empty( $attributes ) ) {
			return '';
		}

		// First, generate a string attributes array, like array( 'rel="test"', 'href="boom"' )
		$full_attributes = array();

		if ( is_array( $attributes ) || is_object( $attributes ) ) {
			foreach ( $attributes as $name => $value ) {
				// We really don't want numeric keys as attributes names
				if ( ! empty( $name ) && ! is_numeric( $name ) ) {
					// If we get an array as value for this attributes
					// we will first test it for is_callable - it may be a callback.
					// If not, we will add them comma separated
					if ( ! empty( $value ) ) {
						$value = self::maybeProcessCallback( $value );

						if ( is_array( $value ) ) {
							$value = join( ', ', $value );
						} else {
							$value = (string) $value;
						}
					}

					// If we receive an empty array entry (but with a key) we will treat it like an attribute without value (i.e. itemprop)
					if ( empty( $value ) ) {
						$full_attributes[] = $name;
					} else {
						$full_attributes[] = $name . '="' . esc_attr( $value ) . '"';
					}
				}
			}
		}

		/**
		 * Filters the list of HTML attributes for a wrapper
		 *
		 * @param array $attributes An array of attributes in the form of 'name=value'.
		 * @param Pixelgrade_Wrapper|null $wrapper Optional. The wrapper instance the attributes belong to.
		 */
		$full_attributes = apply_filters( 'pixelgrade_wrapper_html_attributes', $full_attributes, $wrapper );

		// Glue the attributes - it will work for empty attributes
		return join( ' ', $full_attributes );
	}

	/**
	 * Return the callback response, if that is the case.
	 *
	 * Given some array, determine if it has the necessary callback information and return the call response.
	 * Otherwise just return what we have received.
	 *
	 * @param string|array $value
	 *
	 * @return mixed
	 */
	protected static function maybeProcessCallback( $value ) {
		if ( is_array( $value ) && ! empty( $value['callback'] ) && is_callable( $value['callback'] ) ) {
			$args = array();
			if ( ! empty( $value['args'] ) ) {
				$args = $value['args'];
			}
			return Pixelgrade_Helper::ob_function( $value['callback'], $args );
		}

		return $value;
	}
}
