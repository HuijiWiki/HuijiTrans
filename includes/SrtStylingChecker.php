<?php
/**
 * Implements MessageChecker for srt files.
 *
 * @file
 * @author Reasno
 * @copyright Copyright © 2016, Xi Gu
 * @license GPL-2.0+
 */

class SrtStylingChecker extends MessageChecker {
	protected function srtCheck( $message, $code, &$warnings ){
		foreach( $messages as $message ){
			$key = $message->key();
			$translation = $message->translation();
			//Do Something.
		}
	} 
}