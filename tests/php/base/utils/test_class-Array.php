<?php
/**
 * Class CP_Tests_Class_Array
 *
 * @package Components
 */

/**
 * Test base component class Array.
 *
 * @group base
 */
class CP_Tests_Class_Array extends WP_UnitTestCase {

	/**
	 * @covers Pixelgrade_Array::insertBeforeKey
	 */
	function test_insertBeforeKey() {
		$this->assertEquals(
			[ 1, 2, 3, 4 ],
			Pixelgrade_Array::insertBeforeKey( [ 2, 3, 4 ], 0, 1 )
		);
		$this->assertEquals(
			[ 1, 2, 3, 4 ],
			Pixelgrade_Array::insertBeforeKey( [ 1, 3, 4 ], 1, 2 )
		);
		$this->assertEquals(
			[ 1, 2, 3, 4 ],
			Pixelgrade_Array::insertBeforeKey( [ 2, 3, 4 ], 100, 1 )
		);

		$this->assertEquals(
			[ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ],
			Pixelgrade_Array::insertBeforeKey( [ [ 'second' ], [ 'third' ], [ 'fourth' ] ], 0, [ [ 'first' ] ] )
		);
		$this->assertEquals(
			[ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ],
			Pixelgrade_Array::insertBeforeKey( [ [ 'second' ], [ 'third' ], [ 'fourth' ] ], 100, [ [ 'first' ] ] )
		);
		$this->assertEquals(
			[ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ],
			Pixelgrade_Array::insertBeforeKey( [ [ 'first' ], [ 'third' ], [ 'fourth' ] ], 1, [ [ 'second' ] ] )
		);

		$this->assertEquals(
			[ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ],
			Pixelgrade_Array::insertBeforeKey( [ 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ], 'boguskey', [ 'first' => [ 'first' ] ] )
		);
		$this->assertEquals(
			[ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ],
			Pixelgrade_Array::insertBeforeKey( [ 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ], 'second', [ 'first' => [ 'first' ] ] )
		);
		$this->assertEquals(
			[ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ],
			Pixelgrade_Array::insertBeforeKey( [ 'first' => [ 'first'], 'third' => ['third'], 'fourth' => ['fourth'] ], 'third', [ 'second' => [ 'second' ] ] )
		);
	}

	/**
	 * @covers Pixelgrade_Array::insertAfterKey
	 */
	function test_insertAfterKey() {
		$this->assertEquals(
			[ 1, 2, 3, 4 ],
			Pixelgrade_Array::insertAfterKey( [ 1, 2, 3 ], 2, 4 )
		);
		$this->assertEquals(
			[ 1, 2, 3, 4 ],
			Pixelgrade_Array::insertAfterKey( [ 1, 3, 4 ], 0, 2 )
		);
		$this->assertEquals(
			[ 1, 2, 3, 4 ],
			Pixelgrade_Array::insertAfterKey( [ 1, 2, 3 ], 100, 4 )
		);

		$this->assertEquals(
			[ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ],
			Pixelgrade_Array::insertAfterKey( [ [ 'first' ], [ 'second' ], [ 'third' ] ], 2, [ [ 'fourth' ] ] )
		);
		$this->assertEquals(
			[ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ],
			Pixelgrade_Array::insertAfterKey( [ [ 'first' ], [ 'second' ], [ 'third' ] ], 100, [ [ 'fourth' ] ] )
		);
		$this->assertEquals(
			[ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ],
			Pixelgrade_Array::insertAfterKey( [ [ 'first' ], [ 'third' ], [ 'fourth' ] ], 0, [ [ 'second' ] ] )
		);

		$this->assertEquals(
			[ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ],
			Pixelgrade_Array::insertAfterKey( [ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'] ], 'boguskey', [ 'fourth' => ['fourth'] ] )
		);
		$this->assertEquals(
			[ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ],
			Pixelgrade_Array::insertAfterKey( [ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'] ], 'third', [ 'fourth' => ['fourth'] ] )
		);
		$this->assertEquals(
			[ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ],
			Pixelgrade_Array::insertAfterKey( [ 'first' => [ 'first'], 'third' => ['third'], 'fourth' => ['fourth'] ], 'first', [ 'second' => [ 'second' ] ] )
		);
	}

	/**
	 * @covers Pixelgrade_Array::findSubarrayByKeyValue
	 */
	function test_findSubarrayByKeyValue() {
		$this->assertEquals( 0, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ], 0, 'first' ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ], 0, 'fifth' ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ], 1, 'first' ) );
		$this->assertEquals( 1, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' ], [ 'second' ], [ 'third' ], [ 'fourth' ] ], 0, 'second' ) );

		$this->assertEquals( 'first', Pixelgrade_Array::findSubarrayByKeyValue( [ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ], 0, 'first' ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ], 1, 'first' ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ 'first' => [ 'first'], 'second' => ['second'], 'third' => ['third'], 'fourth' => ['fourth'] ], 0, 'fifth' ) );

		$this->assertEquals( 0, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' => 'first'], ['second' => 'second'], ['third' =>'third'], ['fourth' =>'fourth'] ], 'first', 'first' ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' => 'first'], ['second' => 'second'], ['third' =>'third'], ['fourth' =>'fourth'] ], 'first', 'second' ) );

		$this->assertEquals( 0, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' => ['first'] ], ['second' => ['second'] ], ['third' =>['third'] ], ['fourth' =>['fourth'] ] ], 'first', ['first'] ) );
		$this->assertEquals( 1, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' => ['first'] ], ['second' => ['second'] ], ['third' =>['third'] ], ['fourth' =>['fourth'] ] ], 'second', ['second'] ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' => ['first'] ], ['second' => ['second'] ], ['third' =>['third'] ], ['fourth' =>['fourth'] ] ], 'first', 'first' ) );
		$this->assertEquals( false, Pixelgrade_Array::findSubarrayByKeyValue( [ [ 'first' => ['first'] ], ['second' => ['second'] ], ['third' =>['third'] ], ['fourth' =>['fourth'] ] ], 'second', ['first'] ) );
	}

	/**
	 * @covers Pixelgrade_Array::objArraySearch
	 */
	function test_objArraySearch() {
		$object1 = (object) [
			'propertyOne' => 'foo1',
			'propertyTwo' => 1,
		];
		$object2 = (object) [
			'propertyOne' => 'foo2',
			'propertyTwo' => 2,
		];
		$object3 = (object) [
			'propertyOne' => 'foo3',
			'propertyTwo' => 3,
		];
		$object_array = [ $object1, $object2, $object3 ];

		$this->assertEquals( 0, Pixelgrade_Array::objArraySearch( $object_array, 'propertyOne', 'foo1' ) );
		$this->assertEquals( 1, Pixelgrade_Array::objArraySearch( $object_array, 'propertyOne', 'foo2' ) );
		$this->assertEquals( 1, Pixelgrade_Array::objArraySearch( $object_array, 'propertyTwo', 2 ) );
		$this->assertEquals( 1, Pixelgrade_Array::objArraySearch( $object_array, 'propertyTwo', '2' ) );
		$this->assertEquals( false, Pixelgrade_Array::objArraySearch( $object_array, 'bogus', '2' ) );
		$this->assertEquals( false, Pixelgrade_Array::objArraySearch( $object_array, 'propertyOne', 'bogus' ) );
	}

	/**
	 * @covers Pixelgrade_Array::arrayDiffAssocRecursive
	 */
	function test_arrayDiffAssocRecursive() {

	}

	/**
	 * @covers Pixelgrade_Array::strArraySearch
	 */
	function test_strArraySearch() {

	}

	/**
	 * @covers Pixelgrade_Array::strrArraySearch
	 */
	function test_strrArraySearch() {

	}

	/**
	 * @covers Pixelgrade_Array::detach
	 */
	function test_detach() {

	}

	/**
	 * @covers Pixelgrade_Array::detach_by_value
	 */
	function test_detach_by_value() {

	}

	/**
	 * @covers Pixelgrade_Array::reorder
	 */
	function test_reorder() {

	}

	/**
	 * @covers Pixelgrade_Array::array_merge_recursive_distinct
	 */
	function test_array_merge_recursive_distinct() {

	}

	/**
	 * @covers Pixelgrade_Array::array_orderby
	 */
	function test_array_orderby() {

	}
}
