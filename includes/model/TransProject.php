<?php
/**
 * Contains a class as the base model of all trans project
 *
 * @file
 * @author Xi Gu
 * @license GPL-2.0+
 */
 class TransProject {
	protected static $cache;
	protected $mId;
	protected $mName;
	protected $mWorkflow;
	protected $mPage;
	protected $mPublicationTime;
	protected $mHead;
	protected $mHeadType;
	protected $mIterator;
	const CACHE_MAX = 1000;
	private static function getCache(){
		if ( self::$cache == null) {
			self::$cache = new HashBagOStuff( ['maxKeys' => self::CACHE_MAX] ); 
		}
		return self::$cache;
	}
	private function __construct(){

	}
	/**
	 * Initialize a new transproject from Id
	 */
	public static function newFromId( $id ){
		$tpCache = self::getCache();
		$tp = $tpCache->get($id);
		if ($tp != ''){
			return $tp;
		}
		else {
			$tp = new TransProject();
			$tp->mId = $id;
			$dbr = wfGetDB(DB_SLAVE);
			$s = $dbr->selectRow(
				'transproject',
				array( 'tp_id',
					'tp_name',
					'tp_workflow',
					'tp_page',
					'tp_publication_time',
					'tp_head',
					'tp_head_type',
				),
				array( 'tp_id' => $id ),
				__METHOD__
			);
			if ($s){
				$tp->mName = $s->tp_name;
				$tp->mWorkflow = $s->tp_workflow;
				$tp->mPage = $s->tp_page;
				$tp->mPublicationTime = $s->tp_publication_time;
				$tp->mHead = $s->tp_head;
				$tp->mHeadType = $s->tp_head_type;
				$tpCache->set($id, $tp);
				return $tp;				
			} else {
				return null;
			}

		}

	}
	/**
	 * get a transproject name from Id
	 */
	public static function nameFromId($id){
		global $wgMemc;
		$key = wfMemcKey('transproject', 'nameFromId', $id);
		$data = $wgMemc->get($key);
		if ($data != ''){
			return $data;
		} else {
			$dbr = wfGetDB (DB_SLAVE);
			$s = $dbr->selectRow(
				'transproject',
				array('tp_name'),
				array('tp_id' => $id),
				__METHOD__
			);
			if ($s){
				$wgMemc->set($key, $s->tp_name);
				return $s->tp_name;
			} else {
				return null;
			}
		}
	}
	/**
	 * get a transproject Id from Name
	 */
	public static function idFromName($name){
		global $wgMemc;
		$key = wfMemcKey('transproject', 'idFromName', $id);
		$data = $wgMemc->get($key);
		if ($data != ''){
			return $data;
		} else {
			$dbr = wfGetDB (DB_SLAVE);
			$s = $dbr->selectRow(
				'transproject',
				array('tp_id'),
				array('tp_name' => $name),
				__METHOD__
			);
			if ($s){
				$wgMemc->set($key, $s->tp_name);
				return $s->tp_id;
			} else {
				return null;
			}
		}
	}
	/**
	 * get a transproject instance from name
	 */
	public static function newFromName($name){
		$id = self::idFromName($name);
		return self::newFromId($id);
	}

	public function getName(){
		return $this->mName;
	}
	public function getHead(){
		return $this->mHead;
	}
	public function getHeadType(){
		return $this->mHeadType;
	}
	//Various time funciton
	//abstract protected function getCreationTime();
	//abstract protected function getLastEditionTime();
	public function getPublicationTime(){
		return HuijiFunctions::getTimeAgo($this->mPublicationTime);
	}
	protected function getList($head, $headType, $prevll = null){
		if ($head == ''){
			return null;
		}
		$type = $headType;
		$identifier = $head;
		list($next, $nextType) = $this->getNext($identifier, $type);
		$ll = new LinkedList();
		$ll->id = $this->mId;
		$ll->identifier = $identifier;
		$ll->type = $type;
		$ll->prev = $prevll;
		$ll->next = $this->getList($next, $nextType, $ll);
		return $ll;
	}
	protected function getListFromJson($it, $prevll = null){
		$obj = $it->current();
		if ($obj == ''){
			return null;
		}
		$type = $obj->type;
		$identifier = $obj->id;
		$ll = new LinkedList();
		$ll->id = $this->mId;
		$ll->identifier = $identifier;
		$ll->type = $type;
		$ll->prev = $prevll;
		$it->next();
		$ll->next = $this->getListFromJson($it, $ll);
		return $ll;
	}
	public function getIteratorFromJson($jsonArr){
		$ao = new ArrayObject($jsonArr);
		$arrIt = $ao->getIterator();
		$llHead = $this->getListFromJson($arrIt);
		$it = new TransProjectIterator($llHead);
		return $it;

	}
	protected function getNext($identifier, $type){
		if ($type == 0){
			//Look up identifier,
			$dbr = wfGetDB(DB_SLAVE);
			$s = $dbr->selectRow(
				'transproject_messagegroups',
				array('next', 'next_type'),
				array('tp_id' => $this->mId,
					'mg_name' => $identifier,
				),
				__METHOD__
			);
			if ($s != ''){

				return array($s->next, $s->next_type);
			} else {
				return array(null, null);
			}

		} elseif ($type == 1) {
			//Look up identifier,
			$dbr = wfGetDB(DB_SLAVE);
			$s = $dbr->selectRow(
				'transproject_sharedresources',
				array('next', 'next_type'),
				array('tp_id' => $this->mId,
					'rs_name' => $identifier,
				),
				__METHOD__
			);
			if ($s != ''){
				return array($s->next, $s->next_type);
			} else {
				return array(null, null);
			}			
		}
	}
	public function getIterator(){
		$llHead = $this->getList($this->mHead, $this->mHeadType);
		$it = new TransProjectIterator($llHead);
		return $it;
	}
	public function saveIterator($it){
		//TODO: Lock before proceed
		$isHead = true;
		foreach($it as $ll){
			if ($isHead){
				$this->setHead($ll->identifier, $ll->type);
				$isHead = false;
			}
			$ll->save();
		}
		$it->rewind();
	}
	public function getMessageGroups(){
		$it = $this->getIterator();
		$res = array();
		foreach($it as $item){
			if ($item->type == 0){
				$res[] = MessageGroups::getGroup($item->identifier);
			}
		}
		return $res;
	}

	public function getTranslators(){
		$groups = $this->getMessageGroups();
		$translators = array();
		foreach($groups as $group){
			$translators = array_merge($translators, $group->getAuthors());
		}
		return $translators;
	}

	public function getSharedResources(){
		$it = $this->getIterator();
		$res = array();
		foreach($it as $item){
			if ($item->type == 1){
				$res[] = Title::newFromText($item->identifier);
			}
		}
		return $res;

	}

	public function getWorkflowState(){
		switch($this->mWorkflow){
			case 0:
				return 'new';
			case 1:
				return 'needs_proofread';
			case 2:
				return 'ready';
			case 3:
				return 'published';
			case 4:
				return 'deleted';
			default:
				return 'error';
		}
	}

	public function remove($identifier, $type){
		$it = $this->getIterator();
		foreach($it as $ll){
			if ($ll->identifier == $identifier && $ll->type == $type){
				if ($ll->prev != null){
					$ll->prev->next = $ll->next;
					$ll->prev->save();
				} 
				if ($ll->next != null) {
					$ll->next->prev = $ll->prev;
					$ll->next->save();					
				}
				$ll->delete();
			}
		}
	}
	public function append($identifier, $type){
		$it = $this->getIterator();
		foreach($it as $ll){
			if ($ll->next == null){
				$ll->next = new LinkedList();
				$ll->next->id = $this->mId;
				$ll->next->identifier->$identifier;
				$ll->next->type = $type;
				$ll->next->prev = $ll;
				$ll->next->next = null;
			}
		}
	}

	public function delete(){
		$dbw = wfGetDB(DB_MASTER);
		$dbw->update(
			'transproject',
			array('tp_workflow' => 4),
			array('tp_id' => $this->mId),
			__METHOD__
		);
		$this->mWorkflow = 4;
		$cache = self::getCache();
		$cache->set($this->mId, $this);
		return true;
	}
	/**
	 * Check if this name is valid.
	 */
	private static function isValidName($name){
		$dbr = wfGetDB(DB_SLAVE);
		$s = $dbr->select(
			'transproject',
			array('tp_id'),
			array('tp_name' => $name),
			__METHOD__
		);
		if (count($s) > 0){
			return true;
		} else {
			return false;
		}
	}

	public static function createNew($name){
		if (self::isValidName($name)){
			$dbw = wfGetDB(DB_MASTER);
			$dbw->insert(
				'transproject',
				array('tp_name' => $name),
				__METHOD__
			);
			$id = $dbw->insertId();
			return self::newFromId($id);			
		} else {
			return null;
		}

	}
	public function setHead($head, $headType){
		$dbw = wfGetDB(DB_MASTER);
		$dbw->update(
			'transproject',
			array('tp_head' => $head, 'tp_head_type' => $headType),
			array('tp_id' => $this->mId),
			__METHOD__
		);
		$this->mHead = $head;
		$this->headType = $headType;
		$tpCache = self::getCache();
		$tpCache->set($this->mId, $this);
	}
}
class LinkedList{
	public $id;
	public $identifier;
	public $next;
	public $prev;
	public $type;
	public function delete( ){
		if ($this->type == 0 ){
			$dbw = wfGetDB(DB_MASTER);
			$dbw->delete(
				'transproject_messagegroups',
				array(
					'tp_id' => $this->id,
					'mg_name' => $this->identifier,
				),		
				__METHOD__	
			);
		} elseif ($this->type == 1){
			$dbw = wfGetDB(DB_MASTER);
			$dbw->delete(
				'transproject_sharedresources',
				array(
					'tp_id' => $this->id,
					'rs_name' => $this->identifier,
				),	
				__METHOD__		
			);			
		}
	}
	public function save(){
		$dbw = wfGetDB(DB_MASTER);
		if ($this->type == 0 ){
			
			$dbw->upsert(
				'transproject_messagegroups',
				array(
					'tp_id' => $this->id,
					'mg_name' => $this->identifier,
					'next' => (is_object($this->next)?$this->next->identifier:null),
					'next_type' => (is_object($this->next)?$this->next->type:null),
				),
				array(
					'tp_id' => $this->id,
					'mg_name' => $this->identifier,
				),
				array(
					'tp_id' => $this->id,
					'mg_name' => $this->identifier,
					'next' => (is_object($this->next)?$this->next->identifier:null),
					'next_type' => (is_object($this->next)?$this->next->type:null),
				),
				__METHOD__
			);			
		} elseif ($this->type == 1){
			$dbw->upsert(
				'transproject_sharedresources',
				array(
					'tp_id' => $this->id,
					'rs_name' => $this->identifier,
					'next' => (is_object($this->next)?$this->next->identifier:null),
					'next_type' => (is_object($this->next)?$this->next->type:null),
				),
				array(
					'tp_id' => $this->id,
					'rs_name' => $this->identifier,
				),
				array(
					'tp_id' => $this->id,
					'rs_name' => $this->identifier,
					'next' => (is_object($this->next)?$this->next->identifier:null),
					'next_type' => (is_object($this->next)?$this->next->type:null),
				),
				__METHOD__
			);				
		}
	}
}
class TransProjectIterator implements Iterator {
	private $position = 0;
	private $element;
	private $mHead;
	public function __construct(LinkedList $head){
		$this->position = 0;
		$this->element = $head;
		$this->mHead = $head;
	}
	function rewind(){
		$this->position = 0;
		$this->element = $this->mHead;
	}
	function current(){
		return $this->element;
	}
	function key(){
		return $this->position;
	}
	function next(){
		++$this->position;
		$this->element = $this->element->next;
	}
	function valid(){
		return isset($this->element);
	}
}
?>