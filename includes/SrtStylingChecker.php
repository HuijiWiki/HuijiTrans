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
	protected function dialogueCheck( $messages, $code, &$warnings ){
		foreach( $messages as $message ){
			$key = $message->key();
			$translation = $message->translation();
			$defination = $message->defination();
			if ($code != 'zh'){
				return;
			}
			$dc = substr_count($defination, '- ');	
			$tc = substr_count($translation, '-');
			if ( $dc>=2 && $tc != $dc){
				$warnings[$key][] = array(
	                 array( 'dialogue', 'balance', $key, $code ),
	                 'translate-checks-dialogue', // Needs to be defined in i18n file
	            );					
			}
			elseif (preg_match("/-\\s.+\\s-\\s.+/", $dc) && !preg_match("/-\\s.+\\s-\\s.+/", $tc)){
				$warnings[$key][] = array(
	                 array( 'dialogue', 'balance', $key, $code ),
	                 'translate-checks-dialogue-format', // Needs to be defined in i18n file
	            );					
			}
		}		
	}
	protected function numberCheck( $messages, $code, &$warnings ){
		foreach( $messages as $message ){
			$key = $message->key();
			$translation = $message->translation();
			if ($code != 'zh'){
				return;
			}
			$nb = str_replace(',', '' , $translation );
			$re = "/(?:^|[^\\d])(\\d{1,2}0{2,})/u";
			$matches = array(); 	
			preg_match_all($re, $nb, $matches);	
			if (count($matches) > 0 && count($matches[1])>0){
				$cn = new ChineseNumber($matches[1][0]);
				$params = $params2 = array();
				$params[] = $cn;
				$params2[] = $matches[1][0];
				$warnings[$key][] = array(
	                 array( 'number', 'chinese', $key, $code ),
	                 'translate-checks-chinese-number', // Needs to be defined in i18n file
	                 array( 'PARAMS', $params ),
	                 array( 'PARAMS', $params2 ),
	            );					
			}	
		}
	}
	protected function markCheck( $messages, $code, &$warnings ){
		foreach( $messages as $message ){
			$key = $message->key();
			$translation = $message->translation();
			//Do Something.
			if ($code != 'zh'){
				return;
			}
			if(mb_strpos($translation, '，')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'comma', $key, $code ),
	                 'translate-checks-chinese-comma', // Needs to be defined in i18n file
	            );				
			}
			if(mb_strpos($translation, '--')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'hyphen', $key, $code ),
	                 'translate-checks-hyphen', // Needs to be defined in i18n file
	            );				
			}
			if(mb_strpos($translation, '。')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'period', $key, $code ),
	                 'translate-checks-chinese-period', // Needs to be defined in i18n file

	            );					
			}
			if(mb_strpos($translation, '：')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'colon', $key, $code ),
	                 'translate-checks-chinese-colon', // Needs to be defined in i18n file

	            );					
			}	
			if(mb_strpos($translation, '!')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'exclaimation', $key, $code ),
	                 'translate-checks-exclaimation-mark', // Needs to be defined in i18n file

	            );					
			}				
			if(mb_strpos($translation, '?')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'question', $key, $code ),
	                 'translate-checks-question-mark', // Needs to be defined in i18n file

	            );					
			}	
			if(mb_strpos($translation, '...')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'ellipsis', $key, $code ),
	                 'translate-checks-ellipsis', // Needs to be defined in i18n file

	            );					
			}	
			if(mb_strpos($translation, '.')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'period', $key, $code ),
	                 'translate-checks-period', // Needs to be defined in i18n file
	            );					
			}
			if(mb_strpos($translation, ',')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'comma', $key, $code ),
	                 'translate-checks-comma', // Needs to be defined in i18n file
	            );					
			}
			if(mb_strpos($translation, ':')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'colon', $key, $code ),
	                 'translate-checks-colon', // Needs to be defined in i18n file
	            );					
			}
			if(mb_strpos($translation, '·')!==FALSE || mb_strpos($translation, '•')!==FALSE
				|| mb_strpos($translation, '⋅')!==FALSE || mb_strpos($translation, '・')!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'colon', $key, $code ),
	                 'translate-checks-middot', // Needs to be defined in i18n file
	            );					
			}

		}
	} 
}