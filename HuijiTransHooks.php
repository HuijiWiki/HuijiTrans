<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
class HuijiTransHooks {
	public static function onUnitTestsList( &$files ){
		$files = array_merge( $file, glob(__DIR__.'/tests/phpuinit/*Test.php'));
		return true;
	}
}