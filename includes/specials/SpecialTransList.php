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
class SpecialTransList extends SpecialPage {
	function __construct() {
		parent::__construct( 'TransList' );
	}
	protected function getGroupName() {
		return 'trans';
	}
	public function execute( $parameters ) {
		$out = $this->getOutput();
		$out->enableOOUI();
		$this->setHeaders();
		$out->addModules('ext.voteNY.styles');
		$out->addModules('ext.voteNY.scripts');
		$out->addModules('ext.huijitrans.translist');
		// $circleButtons = array(
		// 				new OOUI\ButtonWidget( array(
		// 					'id' => 'trans-published-projects-button',
		// 					'flags' => 'primary circle',
		// 					'label' => '翻译作品',
		// 					'infusable' => true,
 	// 					) ), 
		// 				new OOUI\ButtonWidget( array(
		// 					'id' => 'trans-memebers-button',
		// 					'flags' => 'primary circle',
		// 					'label' => '组成员',
		// 					'content' => '3',
		// 					'infusable' => true,
		// 				) ), 
		// 				new OOUI\ButtonWidget( array(
		// 					'id' => 'trans-memebers-button',
		// 					'flags' => 'primary circle',
		// 					'label' => '正在翻译',
		// 					'content' => '124',
		// 					'infusable' => true,
		// 				) ), 	
		// 				new OOUI\ButtonWidget( array(
		// 					'id' => 'trans-follow-group-button',
		// 					'flags' => 'primary circle',
		// 					'label' => '关注',
		// 					'infusable' => true,

		// 				) ), 	
		// 				new OOUI\ButtonWidget( array(
		// 					'id' => 'trans-manager-button',
		// 					'flags' => 'primary circle',
		// 					'label' => '进入后台',
		// 					'href' => '/wiki/Special:TransManager',
		// 					'nofollow' => true,
		// 					'infusable' => true,
		// 				) ), 							
		// 			);
		// $fieldArray = [];
		// foreach ($circleButtons as $button){
		// 	$fieldArray[] = new OOUI\FieldLayout( $button );

		// }
		// $widget = new OOUI\FieldsetLayout( array(
		// 	'items' => $fieldArray
		// ) );

		// $out->addHtml($widget);
	}
	public function getCircles(){
		$circles = array(
			'published' => 'number',
			'members' => 'number',
			'newwork' => 'number',
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
	public function newwork(){
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
		return Linker::linkKnown( '', wfMessage('trans-published')->plain(), array('class' => 'published'));
	}

}