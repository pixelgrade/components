<?php
/**
 * Callback Block class
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
 * Pixelgrade_CallbackBlock class.
 */
class Pixelgrade_CallbackBlock extends Pixelgrade_Block {

	/**
	 * Block's Type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'callback';

	/**
	 * The callback to call for rendering the content. It should either echo the content or return it, NOT BOTH!
	 *
	 * Accepts anything that is_callable() will like.
	 *
	 * @access public
	 * @var string|array
	 */
	public $callback = '';

	/**
	 * The arguments to pass to the function
	 *
	 * @access public
	 * @var array
	 */
	public $args = array();

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
	 *     @type int                  $instance_number Order in which this instance was created in relation
	 *                                                 to other instances.
	 *     @type string               $id              Block ID.
	 *     @type int                  $priority        Order priority to load the block. Default 10.
	 *     @type string|array         $wrappers        The block's wrappers. It can be a string or an array of Pixelgrade_BlockWrapper instances.
	 *     @type string               $end_wrappers    The block's end wrappers if $wrappers was string.
	 *     @type array                $checks          The checks config to determine at render time if this block should be rendered.
	 *     @type string               $type            Block type. Core blocks include 'layout', 'template', 'callback'.
	 *     @type string|array         $callback       The callable function definition.
	 *     @type array                $args            The args to pass to the callable function.
	 * }
	 */
	public function __construct( $manager, $id, $args = array() ) {
		// If we don't receive a function, something is wrong
		if ( empty( $args['callback'] ) ) {
			_doing_it_wrong( __METHOD__, 'Can\'t register a CALLBACK type block without a callback function!', '1.0.0' );
			return;
		}

		// If the function is not callable, something is wrong, again
		if ( ! is_callable( $args['callback'], true ) ) {
			_doing_it_wrong( __METHOD__, 'Can\'t register a CALLBACK type block without a valid callback function!', '1.0.0' );
			return;
		}

		parent::__construct( $manager, $id, $args );
	}

	/**
	 * Render the block's content by calling the callback function.
	 *
	 * @param array $blocks_trail The current trail of parent blocks (aka the anti-looping machine).
	 */
	protected function renderContent( $blocks_trail = array() ) {
		// Pass along the blocks trail, just in case someone is interested.
		// Need to make a copy of the args to avoid side effects.
		$args = $this->args;
		$args['blocks_trail'] = $blocks_trail;
		echo call_user_func_array( $this->callback, $args );
	}
}
