<?php
class HuijiTransAPI extends ApiBase {
	public function execute() {
		$result = $this->getResult();
		$user = $this->getUser();
		if ( !$this->getRequest()->wasPosted() ){
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '请使用Post方式发送HTTP请求',
	          'result' => 'fail',
	        );
	   
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

	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;      
	    } elseif (!$user->isAllowed( 'upload' ) || !$user->isAllowed( 'translate-manage' )){
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '您没有删除翻译文件的权限。',
	          'result' => 'fail',
	        );
	      
	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;        
	    } elseif ($user->isBlocked()){
	         $responseBody = array(
	          'state'  => 200,
	          'message' => '您已被封禁。',
	          'result' => 'fail',
	        );
	      
	        $result->addValue($this->getModuleName(),'res', $responseBody);   
	        return true;              
	    }
	    $task = $this->getMain()->getVal( 'task' );
	    if ($task == 'create'){
	    	$project = $this->getMain()->getVal('project');
	    	$obj = TransProject::createNew($project);
	    	$responseBody = array(
	    		'state' => 200,
	    		'message' => '',
	    		'result' => 'success'
	    	);
	    	$result->addValue($this->getModuleName(),'res', $responseBody); 
	    	$result->addValue($this->getModuleName(),'pid', $obj->getId() ); 
	    	return true;
	    }
	    if ($task == 'rename'){
	    	$pid = $this->getMain()->getInt('pid');
	    	$newName = $this->getMain()->getVal('name');
	    	if ($newName == ''){
		    	$responseBody = array(
		    		'state' => 200,
		    		'message' => '新名称不能为空',
		    		'result' => 'fail'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody);  
	    		return true;
	    	}
	    	if (TransProject::rename($pid, $newName)){
		    	$responseBody = array(
		    		'state' => 200,
		    		'message' => '',
		    		'result' => 'success'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
		    	return true;	    		
	    	} else {
		    	$responseBody = array(
		    		'state' => 200,
		    		'message' => '改名失败',
		    		'result' => 'fail'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
		    	return true;	    		
	    	}
	    	
	    }
	    
	    if ($task == 'update'){
	    	$pid = $this->getMain()->getInt('pid');
	    	$project = HuijiProject::newFromId($pid);
	    	if ($project == null){
	    		$responseBody = array(
		    		'state' => 200,
		    		'message' => '项目不存在',
		    		'result' => 'fail'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
	    		return true;
	    	} 
	    	$content = $this->getMain()->getVal('content');
	    	$it = $project->getIteratorFromJson($content);
	    	$project->saveIterator($it);
    		$responseBody = array(
	    		'state' => 200,
	    		'message' => '',
	    		'result' => 'success'
	    	);
	    	$result->addValue($this->getModuleName(),'res', $responseBody); 
    		return true;
	    }
	    if ($task == 'add'){
	    	$pid = $this->getMain()->getInt('pid');
	    	$name = $this->getMain()->getVal('name');
	    	$type = $this->getMain()->getVal('type');
	    	if (!$project || !$name || !$type){
	    		$responseBody = array(
		    		'state' => 200,
		    		'message' => '参数不足',
		    		'result' => 'fail'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 	    		
	    	} else {
	    		$project = HuijiProject::newFromId($pid);
	    		$project->append($name, $ype);
	    		$responseBody = array(
		    		'state' => 200,
		    		'message' => '',
		    		'result' => 'success'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
	    	}
	    }
	    if ($task == 'delete'){
	    	$pid = $this->getMain()->getInt('pid');
	    	$project = HuijiProject::newFromId($pid);
	    	if ($project == null){
	    		$responseBody = array(
		    		'state' => 200,
		    		'message' => '项目不存在',
		    		'result' => 'fail'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
	    		return true;
	    	} 
	    	$ret = $project->delete();
	    	if ($ret){
	    		$responseBody = array(
		    		'state' => 200,
		    		'message' => '',
		    		'result' => 'success'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
	    		return true;
	    	} else {
	    		$responseBody = array(
		    		'state' => 200,
		    		'message' => '未知错误',
		    		'result' => 'fail'
		    	);
		    	$result->addValue($this->getModuleName(),'res', $responseBody); 
	    		return true;	    		
	    	}
	    }

	}
	public function getAllowedParams() {
        return array(
            'task' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'string'
            ), 
            'pid' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer'
            ),      
            'content' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),  
            'name' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),  
            'type' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'integer'
            ),              
        );
    }
    public function needToken(){
    	return 'csrf';
    }
}