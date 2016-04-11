<?php
/**
 * Tests for AndroidXmlFFS
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
 
class SrtFFSTest extends MediaWikiTestCase {
 
    protected $groupConfiguration = array(
       'BASIC' => array(
           'class' => 'FileBasedMessageGroup',
           'id' => 'test-id',
           'label' => 'Test Label',
           'namespace' => 'NS_SRT',
           'description' => 'Test description',
       ),
       'FILES' => array(
           'class' => 'SrtFFS',
           'sourcePattern' => '',
       ),
    );

    public function testParsing() {
       $file =
<<<SRT
1
00:02:52,184 --> 00:02:53,617
慢慢来

2
00:02:56,184 --> 00:02:59,617
"foobar!"

3
00:11:56,184 --> 00:12:59,123
- "foo!" - 'bar'

4
00:14:56,184 --> 00:14:59,000
\music\

5
00:15:56,184 --> 00:17:59,123
!!FUZZY!!已过期

6
01:15:56,184 --> 02:17:59,123
@已过期
SRT;
 
       /**
        * @var FileBasedMessageGroup $group
        */
       $group = MessageGroupBase::factory( $this->groupConfiguration );
       $ffs = new SrtFFS( $group );
       $parsed = $ffs->readFromVariable( $file );
       $expected = array(
           '序号：0 自：00:02:52,184 至：00:02:53,617' => '慢慢来',
           '序号：1 自：00:02:56,184 至：00:02:59,617' => '"foobar!"',
           '序号：2 自：00:11:56,184 至：00:12:59,123' => '- "foo!" - \'bar\'',
           '序号：3 自：00:14:56,184 至：00:14:59,000' => '\\music\\',
           '序号：4 自：00:15:56,184 至：00:17:59,123' => '!!FUZZY!!已过期',
           '序号：5 自：01:15:56,184 至：02:17:59,123' => '@已过期'
       );
       $expected = array( 'MESSAGES' => $expected, 'AUTHORS' => array() );
       $this->assertEquals( $expected, $parsed );
    }

    public function testWrite() {
       /**
        * @var FileBasedMessageGroup $group
        */
       $group = MessageGroupBase::factory( $this->groupConfiguration );
       $ffs = new SrtFFS( $group );

       $messages = array(
           '序号：0 自：00:02:52,184 至：00:02:53,617' => '慢慢来',
           '序号：1 自：00:02:56,184 至：00:02:59,617' => '"foobar!"',
           '序号：2 自：00:11:56,184 至：00:12:59,123' => '- "foo!" - \'bar\'',
           '序号：3 自：00:14:56,184 至：00:14:59,000' => '\\music\\',
           '序号：4 自：00:15:56,184 至：00:17:59,123' => '!!FUZZY!!已过期',
           '序号：5 自：01:15:56,184 至：02:17:59,123' => '@已过期'
       );
       $collection = new MockMessageCollection( $messages );
       $xml = $ffs->writeIntoVariable( $collection );
       var_dump($xml);
       $parsed = $ffs->readFromVariable( $xml );
       $expected = array( 'MESSAGES' => $messages, 'AUTHORS' => array() );
       $this->assertEquals( $expected, $parsed );
    }
}
 
class MockMessageCollection extends MessageCollection {
   public function __construct( $messages ) {
       $keys = array_keys( $messages );
       $this->keys = array_combine( $keys, $keys );
       foreach ( $messages as $key => $value ) {
           $m = new FatMessage( $key, $value );
           $m->setTranslation( $value );
           $this->messages[$key] = $m;
       }
 
       $this->messages['序号：4 自：00:15:56,184 至：00:17:59,123']->addTag( 'fuzzy' );
   }
 
   public function filter( $type, $condition = true, $value = null ) {
   }
}