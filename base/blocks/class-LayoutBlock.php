<?php
/**
 * Layout Block class
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
 * Pixelgrade_LayoutBlock class.
 */
class Pixelgrade_LayoutBlock extends Pixelgrade_Block {

	/**
	 * Block's Type ID.
	 *
	 * @access public
	 * @var string
	 */
	public $type = 'layout';

	/**
	 * Child blocks.
	 *
	 * @access public
	 * @var array
	 */
	public $blocks = array();

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
	 *                                                 Default 'layout'.
	 *     @type array                $blocks          Child blocks definition.
	 * }
	 */
	public function __construct( $manager, $id, $args = array() ) {
		parent::__construct( $manager, $id, $args );

		// We need to check the child blocks and replace them with Pixelgrade_Block instances (if they are not already)
		$this->maybeRegisterBlocks();
	}

	protected function maybeRegisterBlocks() {
		// $blocks should be an array
		if ( ! empty( $this->blocks ) && ! is_array( $this->blocks ) ) {
			$this->blocks = array( $this->blocks );
		}

		// Bail if there are no blocks
		if ( empty( $this->blocks ) ) {
			return;
		}

		// To be sure we are not bother with the intricacies of foreach
		// (whether or not it makes a copy of the array it iterates over, and what does it copy)
		// we will recreate the array.
		$new_blocks = array();

		foreach ( $this->blocks as $block_id => $block ) {
			// We can receive blocks in 3 different ways:
			// - a Pixelgrade_Blocks instance
			// - a registered block ID
			// - an inline block definition
			if ( $block instanceof Pixelgrade_Block ) {
				// We are good
				$new_blocks[ $block_id ] = $block;
				continue;
			} elseif ( is_string( $block ) ) {
				// We need to search for the registered block ID and save it's instance
				if ( $this->manager->isRegisteredBlock( $block ) ) {
					$new_blocks[ $block ] = $this->manager->getRegisteredBlock( $block );
				} else {
					continue;
				}
			} elseif ( is_array( $block ) ) {
				// We have an inline block definition
				// Get the block instance, if all is well
				$block_instance = $this->addBlock( $block_id, $block, true );

				if ( false !== $block_instance ) {
					$new_blocks[ $block_instance->id ] = $block_instance;
				}
			}
		}

		$this->blocks = $new_blocks;
	}

	/**
	 * Add a child block.
	 *
	 * @access public
	 *
	 * @param Pixelgrade_Block|string $id Block object, ID of an already registered block, or ID of an inline block if $args is not empty.
	 * @param array $args The arguments to pass to the block instance to override the default class properties.
	 * @param bool $skip_add_child Optional. Whether to skip adding the block instance to the child blocks.
	 *
	 * @return Pixelgrade_Block|false The instance of the block that was added. False on failure.
	 */
	public function addBlock( $id, $args = array(), $skip_add_child = false ) {
		$block = false;
		if ( $id instanceof Pixelgrade_Block ) {
			// We have got a Pixelgrade_Block instance directly - just save it and that is that
			$block = $id;
		} elseif ( is_string( $id ) ) {
			// We've got a string
			// If we have also got $args, this means we are dealing with an inline block
			if ( ! empty( $args ) ) {
				// Inline blocks have their $id prefixed with the parent id, if it has one
				// Thus we maintain uniqueness among directly defined blocks and inline defined blocks
				$id = $this->id . PIXELGRADE_BLOCK_ID_SEPARATOR . $id;

				// If the type is not set, we will default to 'layout' (if registered)
				if ( ! isset( $args['type'] ) && $this->manager->isRegisteredBlockType( 'layout' ) ) {
					$args['type'] = 'layout';
				}

				// Register the new block (and instantiate it)
				$block = $this->manager->registerBlock( $id, $args );
			} else {
				// This means we have received the ID of a previously registered block
				// We need to search it among the registered blocks and save it
				$block = $this->manager->getRegisteredBlock( $id );
			}
		} else {
			_doing_it_wrong( __METHOD__, 'You tried to add or define a block using a strange (e.g. not supported) way!', '1.0.0' );
		}

		// Add the block instance to the child blocks list
		if ( false !== $block && false === $skip_add_child ) {
			$this->blocks[ $block->id ] = $block;
		}

		return $block;
	}

	/**
	 * Retrieve a child block.
	 *
	 * @param string $id ID of the block.
	 * @return Pixelgrade_Block|false The block object, if set. False otherwise.
	 */
	public function getBlock( $id ) {
		if ( isset( $this->blocks[ $id ] ) ) {
			return $this->blocks[ $id ];
		}

		return false;
	}

	/**
	 * Remove a child block.
	 *
	 * @param string $id ID of the block.
	 */
	public function removeBlock( $id ) {
		unset( $this->blocks[ $id ] );
	}

	/**
	 * Render the each child block's content.
	 *
	 * Allows the content to be overridden without having to rewrite the wrapper in `$this::render()`.
	 *
	 * Block content can alternately be rendered in JS. See Pixelgrade_Block::printTemplate().
	 *
	 * @param array $blocks_trail The current trail of parent blocks (aka the anti-looping machine).
	 */
	protected function renderContent( $blocks_trail = array() ) {
		/** @var Pixelgrade_Block $block */
		foreach ( $this->blocks as $id => $block ) {
			// Render each child block (pass the new blocks trail).

			// First we need to make sure that we don't render an instance already in the blocks trail
			// thus avoiding infinite loops.
			if ( false === Pixelgrade_BlocksManager::isBlockInTrail( $block, $blocks_trail ) ) {
				$block->maybeRenderContent( $blocks_trail + array( $id => $block ) );
			}
		}
	}
}
