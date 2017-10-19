<?php
/**
 * This is a utility class that groups all our array related helper functions.
 *
 * @see 	    https://pixelgrade.com
 * @author 		Pixelgrade
 * @package 	Components/Base
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Pixelgrade_Array' ) ) :

class Pixelgrade_Array {
	/**
	 * Insert a value or key/value pair before a specific key in an array.  If key doesn't exist, value is prepended
	 * at the beginning of the array.
	 *
	 * @param array $array
	 * @param string $key
	 * @param array $insert
	 *
	 * @return array
	 */
	public static function insert_before_key( $array, $key, $insert ) {
		$keys = array_keys( $array );
		$index = array_search( $key, $keys );
		$pos = ( ( false === $index ) ? 0 : $index );
		return array_merge( array_slice( $array, 0, $pos ), $insert, array_slice( $array, $pos ) );
	}

	/**
	 * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
	 * to the end of the array.
	 *
	 * @param array $array
	 * @param string $key
	 * @param array $insert
	 *
	 * @return array
	 */
	public static function insert_after_key( $array, $key, $insert ) {
		$keys = array_keys( $array );
		$index = array_search( $key, $keys );
		$pos = ( ( false === $index ) ? count( $array ) : $index + 1 );
		return array_merge( array_slice( $array, 0, $pos ), $insert, array_slice( $array, $pos ) );
	}

	/**
	 * Find a subarray that has the desired key=>value. We will only go one level deep.
	 *
	 * For example, given the following array:
	 * array( array( 'two' => 'value1' ), array( 'two' => 'value2' ) )
	 * you can search for the key of the subarray containing the 'two' key with the 'value2', that is 1
	 *
	 * @param array $array The array in which to search
	 * @param string $key The key to search for
	 * @param mixed $value The value to search for
	 *
	 * @return mixed|false
	 */
	public static function find_subarray_by_key_value( $array, $key, $value ) {
		// Bail if it's not array
		if ( ! is_array( $array ) ) {
			return false;
		}

		foreach ( $array as $k => $v ) {
			if ( isset( $v[ $key ] ) && $value == $v[ $key ] ) {
				return $k;
			}
		}

		return false;
	}

	/**
	 * Get the difference between two associative arrays, recursively.
	 *
	 * @link http://be2.php.net/manual/en/function.array-diff-assoc.php#114297
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return bool|array
	 */
	public static function array_diff_assoc_recursive( $array1, $array2 ) {
		foreach ( $array1 as $key => $value ) {
			if ( is_array( $value ) ) {
				if ( ! isset( $array2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} elseif ( ! is_array( $array2[ $key ] ) ) {
					$difference[ $key ] = $value;
				} else {
					$new_diff = self::array_diff_assoc_recursive( $value, $array2[ $key ] );
					if ( false !== $new_diff ) {
						$difference[ $key ] = $new_diff;
					}
				}
			} elseif ( ! array_key_exists( $key, $array2 ) || $array2[ $key ] != $value ) {
				$difference[ $key ] = $value;
			}
		}

		return ! isset( $difference ) ? false : $difference;
	}
}

endif;
