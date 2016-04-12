<?php
/**
 * @file
 * @author Xi Gu
 * @license GPL-2.0+
 */
 
/**
 * Insertable is a string that usually does not need translation and is
 * difficult to type manually.
 * @since 2013.09
 */
class SrtInsertablesSuggester {
    public function getInsertables( $text ) {
        $insertables = array();

        $matches = array();
        preg_match_all( '/\$(1[a-z]+|[0-9]+)/', $text, $matches, PREG_SET_ORDER );
        $new = array_map( function( $match ) {
            return new Insertable( $match[0], $match[0] );
        }, $matches );
        $insertables = array_merge( $insertables, $new );

        $matches = array();
        $re = "/([\\d\\.]+)\\/([\\d\\.]+)/"; 
        preg_match_all(
           $re,
           $text,
           $matches,
           PREG_SET_ORDER
        );
        $new = array_map( function( $match ) {
           return new Insertable( (string)new ChineseFraction($match[1], $match[2]), (string)new ChineseFraction($match[1], $match[2]) );
        }, $matches );
        $insertables = array_merge( $insertables, $new );

        $matches = array();
        $re = "/([\\d\\.]+)%/"; 
        preg_match_all(
           $re,
           $text,
           $matches,
           PREG_SET_ORDER
        );
        $new = array_map( function( $match ) {
           return new Insertable( (string)(new ChinesePercent($match[1])), (string)(new ChinesePercent($match[1])) );
        }, $matches );
        $insertables = array_merge( $insertables, $new );

        $matches = array();
        $re = "/([\\d\\.]+)/";
        preg_match_all(
           $re,
           $text,
           $matches,
           PREG_SET_ORDER
        );

        $new = array_map( function( $match ) {
           return new Insertable( (string)(new ChineseNumber($match[1])), (string)(new ChineseNumber($match[1])) );
        }, $matches );
        $insertables = array_merge( $insertables, $new );

        return $insertables;
    }
}