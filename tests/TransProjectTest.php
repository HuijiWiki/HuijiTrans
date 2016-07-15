<?php
/**
 * Tests for AndroidXmlFFS
 *
 * @file
 * @group Trans
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
 
class TransProjectTest extends MediaWikiTestCase {
	private $obj;
	private $json =
<<<JSON3
[{
	"id": "a1",
	"type": 0
}, {
	"id": "a2",
	"type": 0
}, {
	"id": "b1",
	"type": 1
}, {
	"id": "a3",
	"type": 0
}, {
	"id": "b2",
	"type": 1
}]
JSON3;

    private $removeA2 = 
<<<JSON2
[{
    "id": "a1",
    "type": 0
}, {
    "id": "b1",
    "type": 1
}, {
    "id": "a3",
    "type": 0
}, {
    "id": "b2",
    "type": 1
}]
JSON2;

	protected function setUp(){
		$this->obj = TransProject::createNew('test');
		$this->obj->setHead('message', 0);
		// self::setupTestDB(wfGetDB('DB_MASTER'), 'lordofthetest');
		parent::setUp();
	}
	protected function tearDown() {
		// self::teardownTestDB();
		unset($this->obj);
		unset($this->json);
	    parent::tearDown();
  	}
    
    public function testCreateNew(){
        $this->obj = TransProject::createNew('testcreate');
        $this->assertEquals('testcreate', $this->obj->getName());
    }
    public function testSetHead(){
    	$this->obj->setHead('msg', 0);
    	$this->assertEquals('msg', $this->obj->getHead());
    	$this->assertEquals( 0, $this->obj->getHeadType());
    }
    public function testGetIteratorFromJson(){
    	$jsonArr = json_decode($this->json);
    	$it = $this->obj->getIteratorFromJson($jsonArr);
    	$a1 = $it->current();
    	$this->assertEquals('a1', $a1->identifier);
    	$this->assertEquals(0, $a1->type);
    	$it->next();
    	$a2 = $it->current();
    	$this->assertEquals('a2', $a2->identifier);
    	$this->assertEquals(0, $a2->type);
    	$it->next();
    	$b1 = $it->current();
    	$this->assertEquals('b1', $b1->identifier);
    	$this->assertEquals(1, $b1->type);    	
    	$it->next();
    	$a3 = $it->current();
    	$this->assertEquals('a3', $a3->identifier);
    	$this->assertEquals(0, $a3->type); 
    	$it->next();
    	$b2 = $it->current();
    	$this->assertEquals('b2', $b2->identifier);
    	$this->assertEquals(1, $b2->type); 
    	$it->next();
    	$this->assertEquals(null, $it->current());
    	$this->obj->saveIterator($it);
    }
    // public function testGetList(){
    	// $jsonArr = json_decode($this->json);
    	// $it = $this->obj->getIteratorFromJson($jsonArr);
    	// $this->obj->saveIterator($it);
    	// $this->assertEquals(0, $this->obj->getHeadType());
    	// list($next, $nextType) = $this->obj->getNext('a1', 0);
    	// $this->assertEquals('a2', $next);
    	// $this->assertEquals(0, $nextType);
    	// $list = $this->obj->getList('a1', 0, null);
    	// $this->assertEquals(0, $list->type);
    	// $this->assertTrue(is_object($list->next));
    // }
    public function testSaveIteratorAndGetIterator(){
    	$jsonArr = json_decode($this->json);
    	$it = $this->obj->getIteratorFromJson($jsonArr);
    	$this->obj->saveIterator($it);
    	$roundtrip = $this->obj->getIterator();
    	foreach($it as $item){
    		$this->assertEquals($item, $roundtrip->current());
    		$roundtrip->next();
    	}
    }
    // public function testGetMessageGroups(){
    //     $jsonArr = json_decode($this->json);
    //     $it = $this->obj->getIteratorFromJson($jsonArr);
    //     $this->obj->saveIterator($it); 
    //     $groups = $this->obj->getMessageGroups();
    //     $this->assertEquals(['a1', 'a2', 'a3'], $groups);
    // }
    public function testDelete(){
        $this->obj->delete();
        $this->assertEquals('<translate-workflow-state-deleted>',$this->obj->getWorkflowState());
    }
    public function testRemove(){
        $jsonArr = json_decode($this->json);
        $it = $this->obj->getIteratorFromJson($jsonArr);
        $this->obj->saveIterator($it);
        $this->obj->remove('a2',0);
     
        $jsonArr = json_decode($this->removeA2);
        $obj2 = TransProject::createNew('remove_a2');
        $it = $obj2->getIteratorFromJson($jsonArr);
        $obj2->saveIterator($it);
        $roundtrip = $obj2->getIterator();
        foreach($it as $item){
            $this->assertEquals($item, $roundtrip->current());
            $roundtrip->next();
        }
        //Done
    }
    public function testPublish(){
        $jsonArr = json_decode($this->json);
        $it = $this->obj->getIteratorFromJson($jsonArr);
        $this->obj->saveIterator($it);
        $ret =  $this->obj->publish(HuijiFunctions::getTradeNo('TEST'));
        $this->assertTrue($ret);
        $this->assertEquals('<translate-workflow-state-published>',$this->obj->getWorkflowState());
    }
}



?>