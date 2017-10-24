<?php
/**
 * Blocks Manager class
 *
 * Serves as a factory for Blocks, and
 * instantiates default Blocks.
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
 * @class Pixelgrade_BlocksManager
 */
final class Pixelgrade_BlocksManager extends Pixelgrade_Singleton {

	/**
	 * Registered instances of Pixelgrade_Block.
	 *
	 * @access protected
	 * @var array
	 */
	protected $registered_blocks = array();

	/**
	 * Block types that may be rendered from JS templates.
	 *
	 * @access protected
	 * @var array
	 */
	protected $registered_block_types = array();

	/**
	 * The constructor.
	 *
	 * @throws Exception
	 * @param array $args Optional. Various arguments for the component initialization (like different priorities for the init hooks).
	 */
	public function __construct( $args = array() ) {

		// Allow others to make changes to the arguments.
		$args = apply_filters( 'pixelgrade_blocks_manager_init_args', $args );

		// Get going with the initialization
		$this->init( $args );
	}

	/**
	 * Initialize the blocks manager.
	 *
	 * @param array $args Optional
	 *
	 * @return void
	 */
	public function init( $args = array() ) {
		do_action( 'pixelgrade_blocks_manager_before_init', $this, $args );

		$this->registerDefaultBlockTypes();

		$this->registerDefaultBlocks();

		do_action( 'pixelgrade_blocks_manager_after_init', $this, $args );
	}

	/**
	 * Get the registered blocks.
	 *
	 * @return array
	 */
	public function registeredBlocks() {
		return $this->registered_blocks;
	}

	/**
	 * Register a block.
	 *
	 * @access public
	 *
	 * @param Pixelgrade_Block|string $id Block object, or ID.
	 * @param array $args The arguments to pass to the block instance to override the default class properties.
	 * @return Pixelgrade_Block|false The instance of the block that was added. False on failure.
	 */
	public function registerBlock( $id, $args = array() ) {
		if ( $id instanceof Pixelgrade_Block ) {
			$block = $id;
		} else {
			// We need to instantiate a new block

			// We really need a valid, registered block type to be able to do our job
			if ( ! empty( $args['type'] ) && is_string( $args['type'] ) && $this->isRegisteredBlockType( $args['type'] ) ) {
				$block_type_class = $this->getRegisteredBlockTypeClass( $args['type'] );
				if ( class_exists( $block_type_class ) ) {
					// Before adding the block we need to evaluate any dependencies it has
					if ( true === Pixelgrade_Config::evaluateDependencies( $args ) ) {
						$block = new $block_type_class( $this, $id, $args );
					} else {
						return false;
					}

					// @todo Maybe fallback on another previously registered block
//					if ( ! empty( $block_config['fallback'] ) ) {
//
//					}
				} else {
					_doing_it_wrong( __METHOD__, sprintf( 'Couldn\'t register the block %s because the class %s doesn\'t exist.', $id, $block_type_class ), '1.0.0' );
					return false;
				}
			} else {
				_doing_it_wrong( __METHOD__, sprintf( 'Couldn\'t add the block %s because the type provided is invalid or not registered.', $id ), '1.0.0' );
				return false;
			}
		}

		// Add the block to the registry
		$this->registered_blocks[ $block->id ] = $block;

		return $block;
	}

	/**
	 * Check if a block ID exists.
	 *
	 * @param string $id ID of the block.
	 * @return bool
	 */
	public function isRegisteredBlock( $id ) {
		if ( isset( $this->registered_blocks[ $id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve a registered block.
	 *
	 * @param string $id ID of the block.
	 * @return Pixelgrade_Block|false The block object, if set. False otherwise.
	 */
	public function getRegisteredBlock( $id ) {
		if ( isset( $this->registered_blocks[ $id ] ) ) {
			return $this->registered_blocks[ $id ];
		}

		return false;
	}

	/**
	 * Remove a registered block.
	 *
	 * Remove a registered block from the root level registry,
	 * preventing the block from being used in new blocks or have any effect in previously registered blocks that use it.
	 *
	 * @param string $id ID of the block.
	 */
	public function removeRegisteredBlock( $id ) {
		unset( $this->registered_blocks[ $id ] );
	}

	/**
	 * Register a registered block type.
	 *
	 * The class file needs to be loaded before the block type registration because it will do a class_exists check.
	 *
	 * @access public
	 *
	 * @param string $type_id The unique block type identifier
	 * @param string $type_class Name of a block class which is a subclass of
	 *                           Pixelgrade_Block.
	 */
	public function registerBlockType( $type_id, $type_class ) {
		// We will only add the type if the class exists
		if ( class_exists( $type_class ) ) {
			$this->registered_block_types[ $type_id ] = $type_class;
		}
	}

	/**
	 * Check if a block type ID exists.
	 *
	 * @param string $id ID of the block type.
	 * @return bool
	 */
	public function isRegisteredBlockType( $id ) {
		if ( isset( $this->registered_block_types[ $id ] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieve a registered block type class name.
	 *
	 * @param string $id ID of the block type.
	 * @return string|false The block type class name, if set. False otherwise.
	 */
	public function getRegisteredBlockTypeClass( $id ) {
		if ( isset( $this->registered_block_types[ $id ] ) ) {
			return $this->registered_block_types[ $id ];
		}

		return false;
	}

	/**
	 * Remove a registered block type.
	 *
	 * @param string $id ID of the block.
	 */
	public function removeRegisteredBlockType( $id ) {
		unset( $this->registered_block_types[ $id ] );
	}

	/**
	 * Render JS templates for all registered block types.
	 *
	 * @access public
	 */
	public function renderBlockTemplates() {
		foreach ( $this->registered_block_types as $block_type ) {
			$block = new $block_type( $this, 'temp', array(
				'settings' => array(),
			) );
			$block->print_template();
		}
		?>
		<script type="text/html" id="tmpl-block-notifications">
			<ul>
				<# _.each( data.notifications, function( notification ) { #>
					<li class="notice notice-{{ notification.type || 'info' }} {{ data.altNotice ? 'notice-alt' : '' }}" data-code="{{ notification.code }}" data-type="{{ notification.type }}">{{{ notification.message || notification.code }}}</li>
					<# } ); #>
			</ul>
		</script>
		<?php
	}

	/**
	 * Enqueue scripts for blocks.
	 */
	public function enqueueBlockScripts() {
		foreach ( $this->registered_blocks as $block ) {
			$block->enqueue();
		}
	}

	/**
	 * Register some default block types.
	 *
	 * The class file needs to be loaded before the block type registration because it will do a class_exists check.
	 */
	public function registerDefaultBlockTypes() {
		require_once PIXELGRADE_BLOCKS_PATH . 'class-LayoutBlock.php';
		$this->registerBlockType( 'layout', 'Pixelgrade_LayoutBlock' );
		require_once PIXELGRADE_BLOCKS_PATH . 'class-TemplatePartBlock.php';
		$this->registerBlockType( 'template_part', 'Pixelgrade_TemplatePartBlock' );
		require_once PIXELGRADE_BLOCKS_PATH . 'class-CallbackBlock.php';
		$this->registerBlockType( 'callback', 'Pixelgrade_CallbackBlock' );
	}

	/**
	 * Register some default blocks.
	 */
	public function registerDefaultBlocks() {

	}

	/**
	 * Order a list of blocks by priority, ascending.
	 *
	 * @param array $blocks List of block instances to order.
	 *
	 * @return array
	 */
	public static function orderBlocks( $blocks ) {
		return wp_list_sort( $blocks, array(
			'priority'        => 'ASC',
			'instance_number' => 'ASC',
		), 'ASC', true );
	}

	/**
	 * Search for a block instance in a block trail.
	 *
	 * @param object $block The Pixelgrade_Block instance to search for.
	 * @param array $block_trail The block trail/list of Pixelgrade_Block instances.
	 *
	 * @return bool|int
	 */
	public static function isBlockInTrail( $block, $block_trail ) {
		return array_search( $block, $block_trail );
	}
}