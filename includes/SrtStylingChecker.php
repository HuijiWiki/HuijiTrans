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
	protected function standardCheck( )
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
				$cn = $this->ch_num($matches[1][0]);
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
	private function ch_num($num,$mode=true) { 
		$char = array("零","一","二","三","四","五","六","七","八","九"); 
		$dw = array("","十","百","千","","万","亿","兆"); 
		$dec = "点"; 
		$retval = ""; 

		if($mode) 
			preg_match_all("/^0*(\d*)\.?(\d*)/",$num, $ar); 
		else 
			preg_match_all("/(\d*)\.?(\d*)/",$num, $ar); 

		if($ar[2][0] != "") 
			$retval = $dec . ch_num($ar[2][0],false); //如果有小数，先递归处理小数 
		if($ar[1][0] != "") { 
			$str = strrev($ar[1][0]); 
			for($i=0;$i<strlen($str);$i++) { 
				$out[$i] = $char[$str[$i]]; 
				if($mode) { 
					$out[$i] .= $str[$i] != "0"? $dw[$i％4] : ""; 
					if($str[$i]+$str[$i-1] == 0) 
						$out[$i] = ""; 
					if($i％4 == 0) 
						$out[$i] .= $dw[4+floor($i/4)]; 
				} 
			} 
			$retval = join("",array_reverse($out)) . $retval; 
		} 
		return $retval; 
	} 
}