<?php
/**
 * AJAX functions to upload avatar.
 */
class ApiSrtDelete extends ApiBase {
	public function execute() {
	    global $wgHuijiPrefix;
			$user = $this->getUser();
			// Blocked users cannot submit new comments, and neither can those users
	    // without the necessary privileges. Also prevent obvious cross-site request
	    // forgeries (CSRF)
	    if ( !$this->getRequest()->wasPosted() ){
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '请使用Post方式发送HTTP请求',
	          'result' => '',
	        );
	        $result = $this->getResult();
	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;             	
	    }
	    if (
	        wfReadOnly()
	    ) {
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '本维基处于只读状态。',
	          'result' => 'fail',
	        );
	        $result = $this->getResult();
	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;      
	    } elseif (!$user->isAllowed( 'upload' ) || !$user->isAllowed( 'translate-manage' )){
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '您没有删除翻译文件的权限。',
	          'result' => 'fail',
	        );
	        $result = $this->getResult();
	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;        
	    } elseif ($user->isBlocked()){
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '您已被封禁。',
	          'result' => 'fail',
	        );
	        $result = $this->getResult();
	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;              
	    }
	    $label = $this->getMain()->getVal( 'id' );
	    $id = preg_replace('/[^A-Za-z0-9_\-]/', '_', $label); //escape whitespace to avoid xss
	    $yml = "/var/www/virtual/".$wgHuijiPrefix."/external/yml";
	    $structure = "/var/www/virtual/".$wgHuijiPrefix."/external/srt/{$id}";
	    if (unlink($structure) && unlink($yml."./{$id}.yml") ){
	        $responseBody = array(
		        'state'  => 200,
		        'message' => 'success',
		        'result' => '',
	        );  
	    } else {
	    	$responseBody = array(
		        'state'  => 200,
		        'message' => '删除文件失败',
		        'result' => '',
	      	); 
	    }

	    $result = $this->getResult();
	    $result->addValue($this->getModuleName(),'res', $responseBody);
	    return true;       
	}
	public function needsToken() {
	    return 'csrf';
	}
	public function getAllowedParams() {
        return array(
            'id' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ),      
        );
    }
}