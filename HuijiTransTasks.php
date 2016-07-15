<?php
/**
 * Custom tasks for Translate extension
 */
 class PublishMessagesTask extends ExportMessageTasks {
 	protected $id = 'publish';

 	public function output() {
		if ( !$this->group instanceof FileBasedMessageGroup ) {
			return 'Not supported';
		}
		//Edit Publication page

		//put output file on the cloud disk	

		//clean up	
 	}

 }