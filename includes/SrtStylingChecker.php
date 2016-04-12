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
	protected function numberCheck( $message, $code, &$warnings ){
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
			if (count($matches) > 0){
				$cn = new ChineseNumber($matches[1][0]);
				$params = array();
				$params[] = $cn;
				$params[] = $matches[1][0];
				$warnings[$key][] = array(
	                 array( 'number', 'chinese', $key, $code ),
	                 'translate-checks-chinese-number', // Needs to be defined in i18n file
	                 array( 'PARAMS', $params ),
	                 array( 'COUNT', count($params) ),
	            );					
			}	
		}
	}
	protected function markCheck( $message, $code, &$warnings ){
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
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );				
			}
			if(mb_strpos($translation, '。'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'period', $key, $code ),
	                 'translate-checks-chinese-period', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}
			if(mb_strpos($translation, '：'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'colon', $key, $code ),
	                 'translate-checks-chinese-colon', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}	
			if(mb_strpos($translation, '!'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'exclaimation', $key, $code ),
	                 'translate-checks-exclaimation-mark', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}				
			if(mb_strpos($translation, '?'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'question', $key, $code ),
	                 'translate-checks-question-mark', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}	
			if(mb_strpos($translation, '...'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'ellipsis', $key, $code ),
	                 'translate-checks-ellipsis', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}	
			if(mb_strpos($translation, '.'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'period', $key, $code ),
	                 'translate-checks-period', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}
			if(mb_strpos($translation, ','， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'comma', $key, $code ),
	                 'translate-checks-comma', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}
			if(mb_strpos($translation, ':'， $key, $code)!==FALSE){
				$warnings[$key][] = array(
	                 array( 'mark', 'colon', $key, $code ),
	                 'translate-checks-colon', // Needs to be defined in i18n file
	                 array( 'PARAMS', '' ),
	                 array( 'COUNT', '' ),
	            );					
			}
		}
	} 
}