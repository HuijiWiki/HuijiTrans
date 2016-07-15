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
	protected $mPageNamespace;
	protected $mPageId;
	protected $mPageTitle;
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
					'tp_publication_time',
					'tp_head',
					'tp_head_type',
					'page_namespace',
					'page_title',
					'page_id',
				),
				array( 'tp_id' => $id ),
				__METHOD__
			);
			if ($s){
				$tp->mName = $s->tp_name;
				$tp->mWorkflow = $s->tp_workflow;
				$tp->mPublicationTime = $s->tp_publication_time;
				$tp->mHead = $s->tp_head;
				$tp->mHeadType = $s->tp_head_type;
				$tp->mPageTitle = $s->page_title;
				$tp->mPageId = $s->page_id;
				$tp->mPageNamespace = $s->page_namespace;
				$tpCache->set($id, $tp);
				return $tp;				
			} else {
				$tpCache->set($id, $tp);
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
	public function getId(){
		return $this->mId;
	}
	public function getPage(){
		$t = Title::makeTitleSafe($this->mPageNamespace, $this->mPageTitle);
		return $t;
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
		return HuijiFunctions::getTimeAgo(strtotime($this->mPublicationTime));
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

	public function getTranslators($lang = "zh-cn"){
		$groups = $this->getMessageGroups();
		$translators = array();
		foreach($groups as $group){
			if (is_null($group)){
				continue;
			}
			$collection = $group->initCollection($lang);
			$authors = $collection->getAuthors();
			foreach ($authors as $author){
				$huijiUser = HuijiUser::newFromName($author);
				$translators[] = array(
					"name" => $author,
					"avatar" => array(
							"l" => $huijiUser->getAvatar('l')->getAvatarUrlPath(),
							"ml" => $huijiUser->getAvatar('ml')->getAvatarUrlPath(),
							"m" => $huijiUser->getAvatar('m')->getAvatarUrlPath(),
							"s" => $huijiUser->getAvatar('s')->getAvatarUrlPath(),
					),
				);
			}
			//$translators = array_merge($translators, $group->getAuthors());
			
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
	public function toJson(){
		$arr = [];
		$it = $this->getIterator();
		foreach ($it as $item) {
			$obj = new \stdClass();
			$obj->id = $item->identifier;
			$obj->type = $item->type;
			$arr[] = $obj;
		}
		$output = json_encode($arr);
		return $output;

	}

	public function getWorkflowState(){
		switch($this->mWorkflow){
			case 0:
				return wfMessage('translate-workflow-state-new')->text();
			case 1:
				return wfMessage('translate-workflow-state-needs-proofreading')->text();
			case 2:
				return wfMessage('translate-workflow-state-ready')->text();
			case 3:
				return wfMessage('translate-workflow-state-published')->text();
			case 4:
				return wfMessage('translate-workflow-state-deleted')->text();
			default:
				return wfMessage('translate-workflow-state-error')->text();
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
				break;
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
		$r = $dbw->update(
			'transproject',
			array('tp_workflow' => 4),
			array('tp_id' => $this->mId),
			__METHOD__
		);
		if ($r == ''){
			return false;
		}
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
		global $wgMemc, $wgHuijiPrefix;
		if (self::isValidName($name)){
			$dbw = wfGetDB(DB_MASTER);
			$dbw->insert(
				'transproject',
				array('tp_name' => $name),
				__METHOD__
			);
			$id = $dbw->insertId();
			$key = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'translating_work', $wgHuijiPrefix);
			$wgMemc->incr($key);
			return self::newFromId($id);			
		} else {
			return null;
		}

	}
	public static function rename($id, $newName){
		if (self::isValidName($to)){
			$dbw->update(
				'transproject',
				array('tp_name' => $newName),
				array('tp_id' => $id),
				__METHOD__
			);
			$r = $dbw->affectedRows();
			if ($r > 0){
				return true;
			} else {
				return false;
			}	
		} else {
			return false;
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
	public function recall(){
		global $wgHuijiPrefix, $wgOssPrefix, $wgMemc;
		if ($mWorkflow != 3){
			return false;
		}
		$dbw = wfGetDB(DB_MASTER);
		$this->mPublicationTime = '';
		$this->mPageTitle = null;
		$this->mPageId = null;
		$this->mPageNamespace = null;
		$this->mWorkflow = 2;
		$tpCache = self::getCache();
		$tpCache->set($this->mId, $this);
		$dbw->update(
			'transproject',
			array('tp_workflow' => 2, 
				'tp_publication_time' => $this->mPublicationTime,
				'page_title' => '',
				'page_id' => '',
				'page_namespace' => '',
				),
			array('tp_id' => $this->mId),
			__METHOD__
		);
		$key = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'published_work', $wgHuijiPrefix);
		$wgMemc->decr($key);
		$key = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'translating_work', $wgHuijiPrefix);
		$wgMemc->incr($key);		

	}

	public function publish( $publicName = null ){
		global $wgHuijiPrefix, $wgOssPrefix, $wgMemc;
		if ($this->mWorkflow >= 4){
			return false;
		}
		$dbw = wfGetDB(DB_MASTER);
		$this->mWorkflow = 3;
		$this->mPublicationTime = date( 'Y-m-d H:i:s' );
		$title = Title::makeTitleSafe(
			NS_PUBLICATION,
			$publicName // @TODO: convert this name to valid html.
		);
		$article = new WikiPage($title);
		if ($article->exists()){
			return false;
		}

		// Create Downloadable file.
		$it = $this->getIterator();
		$output = "";
		foreach ($it as $item) {
			$output .= $item->getContent();
		}
		if ( empty($output) ){
			return false;
		}
		$fs = wfGetFS(FS_OSS);
		$path = "$wgHuijiPrefix/external/publication/{$publicName}";
		$fs->put( $path, $output );
		
	
		$article->doEditContent(
			new WikitextContent('Project published! Link: http://fs.huijiwiki.com/'.$path),
			'bot edit',
			EDIT_NEW && EDIT_FORCE_BOT,
			false,
			User::newFromName('米拉西斯')
		);
		
		$aid = $article->getId();
		$r = $dbw->update(
			'transproject',
			array('tp_workflow' => 3, 
				'tp_publication_time' => $this->mPublicationTime,
				'page_title' => $title->getDBkey(),
				'page_id' => $aid,
				'page_namespace' => NS_PUBLICATION,
				),
			array('tp_id' => $this->mId),
			__METHOD__
		);
		if ($r == ''){
			return false;
		}
		$this->mPageTitle = $title->getDBkey();
		$this->mPageId = $aid;
		$this->mPageNamespace = NS_PUBLICATION;
		$tpCache = self::getCache();
		$tpCache->set($this->mId, $this);

	
		$key = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'published_work', $wgHuijiPrefix);
		$wgMemc->incr($key);
		$key = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'translating_work', $wgHuijiPrefix);
		$wgMemc->decr($key);
		return true;
	}
}
class LinkedList{
	public $id;
	public $identifier;
	public $next;
	public $prev;
	public $type;
	public function getContent($lang = "zh-cn"){
		if ($this->type == 0 ){
			MediaWiki\SuppressWarnings();
			$group = MessageGroups::getGroup($this->identifier);
			$collection = $group->initCollection( $lang );
			$ffs = $group->getFFS();
			$data = $ffs->writeIntoVariable( $collection );
			return $data;
		}
		if ($this->type == 1){
			$title = Title::newFromText($this->identifier);
			if ($title != null){
				$article = new WikiPage($title);
				$content = $article->getContent();
				$data = $content->getNativeData();
				return $data;
			}
			return "";
			

		}
	}
	public function delete(){
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
	public function __construct($head){
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