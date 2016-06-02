<?php
/**
 * Contains a class as the base model of public trans project
 *
 * @file
 * @author Xi Gu
 * @license GPL-2.0+
 */
class PublicTransProject extends TransProject {
	public static function newFromId();
	protected function loadFromRow(){

	}

	public function getName(){
		if ($this->mName != ''){
			return $this->mName;
		} else {
			$this->loadFromRow()
		}
	}
	
	abstract protected function getRelatedPage();

	abstract protected function getVoting();
}
?>