<?php
/**
 * Contains logic for special page Special:TransList.
 *
 * @file
 * @author Xi Gu
 * @license GPL-2.0+
 */
/**
 * Implements the core of Translate extension - a special page which shows
 * a list of messages in a format defined by Tasks.
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class TransList extends SpecialPage {
	function __construct() {
		parent::__construct( 'TransList' );
	}
	protected function getGroupName() {
		return 'trans';
	}
	public function execute( $parameters ) {
	}
	public function getCircles(){
		$circles = array(
			'published' => 'number',
			'members' => 'number',
			'new' => 'number',
			'follow' => 'button',
			'manage' => 'button',
		);
		return $circles;
	}
	public function getLeftRail(){
		$leftRail = array(
			'published' => 'numberbutton',
			'category' => 'numberbutton',
			'members' => 'numberbutton',

		);
	}
	public function getList(){

	}

}
class TransNumber {
	public function published(){
		global $wgContLang;
		$states = $this->getWorkflowStates();
		$statesCount = array_count_values($states);
		return $statesCount['published'];

	}
	public function members(){
		return SiteStats::numberingroup( 'members' );
	}
	public function new(){
		global $wgContLang;
		$states = $this->getWorkflowStates();
		$statesCount = array_count_values($states);
		return $statesCount['new'];		
	}
	public function category(){
		$title = Title::makeTitleSafe( NS_CATEGORY, '作品合辑' );
		if ($title){
			return 0;
		}
		$category = Category::newFromTitle( $title );
		return (int)$category->getPageCount();
	}
	private function getWorkflowStates( $field = 'tgr_group', $filter = 'tgr_lang' ) {
		global $wgMemc;
		$key = wfMemcKey('translate_groupreviews', 'getWorkflowStates');
		$data = $wgMemc->get($key);
		if ($data != ''){
			return $data;
		} else {
			$db = wfGetDB( DB_SLAVE );
			$res = $db->select(
				'translate_groupreviews',
				array( 'tgr_state', $field ),
				array( $filter => $this->target ),
				__METHOD__
			);
			$states = array();
			foreach ( $res as $row ) {
				$states[$row->$field] = $row->tgr_state;
			}
			$wgMemc->set($key, $states);			
		}

		return $states;
	}
}
class TransButton {
	public function follow(){
		return Linker::linkKnown('', wfMessage('trans-follow')->plain(), array('class' => 'follow') );

	}
	public function manage(){
		return Linker::linkKnown( SpecialPage::getTitleFor('TransManager'), wfMessage('trans-manage')->plain(), array('class' => 'manage') );

	}

	public function category(){
		return Linker::linkKnown( Title::newFromName('Category:作品合辑'), wfMessage('trans-category')->plain(), array('class' => 'category'));

	}
	public function members(){
		return Linker::linkKnown( '', wfMessage('trans-members')->plain(), array('class' => 'members'));

	}
	public function published(){
		return Linker::linkKnown( '', wfMessage('trans-published')->plain(), warray('class' => 'published'));
	}

}