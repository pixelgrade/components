<?php
/**
 * Template Part Block class
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
 * Pixelgrade_TemplatePartBlock class.
 */
class Pixelgrade_TemplatePartBlock extends Pixelgrade_Block {

	/**
	 * Block's Type.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'template_part';

	/**
	 * Templates (or template parts) to be used at render time. Only used for the template_part block type
	 *
	 * @access public
	 * @var array
	 */
	public $templates = array();

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
	 *     @type string|array         $templates       The templates configuration.
	 * }
	 */
	public function __construct( $manager, $id, $args = array() ) {
		// If we don't receive any templates, something is wrong
		if ( empty( $args['templates'] ) ) {
			_doing_it_wrong( __METHOD__, 'Can\'t register a TEMPLATE type block without any templates!', '1.0.0' );
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
		// @todo ^

		// Handle the various formats we could be receiving the template info in
		if ( is_string( $this->templates ) ) {
			// This is directly the slug of a template part - load it
			get_template_part( $this->templates );
		} elseif ( is_array( $this->templates ) ) {
			// We have an array but it may be a simple array, or an array of arrays - standardize it
			if ( isset( $this->templates['slug'] ) ) {
				// We have a simple array
				$this->templates = array( $this->templates );
			}

			// We respect our promise to process the templates according to their priority, descending
			// So we will stop at the first found template
			foreach ( $this->templates as $item ) {
				// We really need at least a slug to be able to do something
				if ( ! empty( $item['slug'] ) ) {
					// We have a simple template array - just a slug; make sure the name is present
					if ( empty( $item['name'] ) ) {
						$item['name'] = '';
					}

					if ( ! empty( $item['component_slug'] ) ) {
						// We will treat it as a component template part
						$found_template = pixelgrade_locate_component_template_part( $item['component_slug'], $item['slug'], $item['name'] );
					} else {
						$found_template = pixelgrade_locate_template_part( $item['slug'], '', $item['name'] );
					}

					// If we found a template, we load it and stop since upper templates get precedence over lower ones
					if ( ! empty( $found_template ) ) {
						load_template( $found_template, true );
						return;
					}
				}
			}
		}
	}
}
