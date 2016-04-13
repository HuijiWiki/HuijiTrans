<?php
/**
 * Contains class with basic non-feature specific hooks.
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
class HuijiTransHooks {
	public static function onUnitTestsList( &$files ){
		$files = array_merge( $file, glob(__DIR__.'/tests/phpuinit/*Test.php'));
		return true;
	}
	public static function onSkinTemplateToolboxEnd( &$skinTemplate ){
		global $wgUser;
		if ($wgUser->isAllowed('translate-manage')){
			$title = SpecialPage::getTitleFor('ManageMessageGroups');
			$line = Linker::LinkKnown($title, '<i class="fa fa-file-video-o"></i> 创建字幕翻译', array('class'=>'create-srt') );
			echo Html::rawElement( 'li', array(), $line );			
		}
		return true;
	}
}