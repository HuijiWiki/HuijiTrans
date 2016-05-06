<?php
/**
 * Support for srt translation format used by Huiji Trans.
 *
 * @file
 * @author Xi Gu
 * @license GPL-2.0+
 */
use Captioning\Format\SubripFile;
use Captioning\Format\SubripCue;
/**
 * Support for srt translation format used by Huiji Trans..
 * @since 2016
 * @ingroup FFS
 */
class SrtFFS extends SimpleFFS {
    public function supportsFuzzy() {
        return 'yes';
    }
   /**
    * @param string $data
    * @return array Parsed data.
    */
    public function readFromVariable( $data ){
        // create a temp file from $data
        $srt = new SubripFile();
        $srt->loadFromString($data);
        $cues = $srt->getCues();
        $messages = array();
        $mangler = $this->group->getMangler();
        foreach( $cues as $index => $cue ){
            //@todo fuzzy handler
            $key = self::buildUnmangledKey($index, (string)$cue->getStart(), (string)$cue->getStop());
            $value = $cue->getText();
            $messages[$key] = $value;
        }
        return array(
            'AUTHORS' => array(), // @todo
            'MESSAGES' => $mangler->mangle( $messages ),
        );
    }    
    public function writeReal( MessageCollection $collection ){
        $srt = new SubripFile();
        $mangler = $this->group->getMangler();


        // $collection->filter( 'hastranslation', false );
        // if ( count( $collection ) === 0 ) {
        //    return '';

        // }
        foreach ( $collection as $key => $m ) {
            $key = $mangler->unmangle( $key );
            list($oldKey, $index, $start, $stop ) = self::teardownUnmangledKey($key);
            $value = $m->translation();
            $value .= "\r\n";
            $value .= $m->definition();
            //$value = str_replace( TRANSLATE_FUZZY, '', $value );
            if (count($start)>0 && count($stop)>0){
                $cue = new SubripCue($start[0], $stop[0], $value);
                // $cue->setStartMS($start[0]);
                // $cue->setStopMS($stop[0]);
                $srt->addCue($cue);
            }
        }
        $srt->build();
        return $srt->getFileContent();

    }
    public static function buildUnmangledKey($number, $start, $end){
        $res = '';
        $res .= self::$keypart['number'].$number.' ';
        $res .= self::$keypart['start'].$start.' ';
        $res .= self::$keypart['end'].$end.'';
        return $res;

    }
    public static function teardownUnmangledKey($key){
        $matches = array();
        $re = "/：([\d]+)\\s.+?：([\\d\\.,:]+).+?：([\\d\\.,:]+)/u"; 
        preg_match_all($re, $key, $matches);
        return $matches;
    }

    protected static $keypart = array(
        'number' => '序号：',
        'start' => '自：',
        'end' => '至：',
    );


    public function getFileExtensions() {
        return array( '.srt' );
    }
 

}