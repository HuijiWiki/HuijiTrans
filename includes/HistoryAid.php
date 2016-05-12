<?php
class HistoryAid extends TranslationAid{
	public function getData(){
		$page = $this->handle->getKey();
		$ns = $this->handle->getTitle()->getNamespace();
		//getHistory
		$title = $this->handle->getTitle();
		$latestRevId = $title->getLatestRevID();
		$lastRev = Revision::newFromId($latestRevId);
		$i = 0;
		$value = array();
		while($lastRev != null){
			$value[$i]['text'] = $lastRev->getContent->getNativeData();
			$value[$i]['user'] = $lastRev->getUser()->getName();
			$i++;
			$lastRev = $lastRev->getNext();
		}

		//BuildHtml
		$templateParser = new TemplateParser(  __DIR__ . '/../views');
		
		$history = $templateParser->processTemplate(
    		'history',
    		array(
        		'value' => $value,
    		)
		);
		return array(
			'language' => $wgContLang->getCode(),
			'value' => $i,
			'html' => $history
		);
	}
	private function getLine($row){
		$title = 

		$output = '';
		$rev = new Revision($row);
		$output .= Linker::revUserTools( $rev, true );
		$output .= Linker::revComment( $rev, true );
		return $output;

	}
}