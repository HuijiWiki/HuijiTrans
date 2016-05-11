<?php
/**
 * AJAX functions to upload avatar.
 */
class ApiSrtSubmit extends ApiBase {
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
          'result' => $avatar->getResult(),
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
          'result' => $avatar->getResult(),
        );
        $result = $this->getResult();
        $result->addValue($this->getModuleName(),'res', $responseBody);   
        return true;      
    } elseif (!$user->isAllowed( 'upload' ) || !$user->isAllowed( 'translate-manage' )){
         $responseBody = array(
          'state'  => 200,
          'message' => '您没有上传翻译文件的权限。',
          'result' => $avatar->getResult(),
        );
        $result = $this->getResult();
        $result->addValue($this->getModuleName(),'res', $responseBody);   
        return true;        
    } elseif ($user->isBlocked()){
         $responseBody = array(
          'state'  => 200,
          'message' => '您已被封禁。',
          'result' => $avatar->getResult(),
        );
        $result = $this->getResult();
        $result->addValue($this->getModuleName(),'res', $responseBody);   
        return true;              
    }
    $label = $this->getMain()->getVal( 'id' );
    $description = $this->getMain()->getVal( 'description' );
    $file = $this->getMain()->getUpload( 'file' );
    $language = $this->getMain()->getVal( 'language' );
    if ($language == '' || !isset($language)){
      $language = 'en';
    }
    //@TODO There can't be duplicate ids
    $id = preg_replace('/[^A-Za-z0-9_\-]/', '_', $label);
    $filename = $file->getTempName();
    $yml = "/var/www/virtual/".$wgHuijiPrefix."/external/yml";
    $ymlTemplate = "/var/www/src/extensions/HuijiTrans/includes/formats/srt.yml";
    $structure = "/var/www/virtual/".$wgHuijiPrefix."/external/srt/{$id}";
    $oldmask = umask(0);
    if (!file_exists($yml)){
      mkdir($yml, 0777,true);
    }
    if (!file_exists($structure)){
      mkdir($structure, 0777,true);
    }
    umask($oldumask); 
    $file_contents = file_get_contents($ymlTemplate);
    $file_contents = str_replace("%id%",$id,$file_contents);
    $file_contents = str_replace("%label%",$label,$file_contents);
    $file_contents = str_replace("%description%",$description,$file_contents);
    file_put_contents($yml."/{$id}.yml", $file_contents);
    $text =  file_get_contents($filename)；
    $text = iconv(mb_detect_encoding($text, mb_detect_order(), true), "UTF-8", $text);
    file_put_contents($structure."/{$language}.srt", $text); 
    $command = "php /var/www/virtual/".$wgHuijiPrefix."/extensions/Translate/scripts/processMessageChanges.php  --conf=/var/www/virtual/".$wgHuijiPrefix."/LocalSettings.php";
    $output = $ret = '';
    exec($command, $output, $ret);
    if ($ret == 0){
      $responseBody = array(
        'state'  => 200,
        'message' => 'success',
        'result' => '/wiki/特殊:信息组管理',
      );      
    } else {
      //remove invalid file
      unlink($structure."/{$language}.srt");
      unlink($yml."/{$id}.yml");
      $responseBody = array(
        'state'  => 500,
        'message' => 'srt文件不合法。',
        'result' => $output,
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
            'description' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'
            ),
            'file' => array(
                ApiBase::PARAM_REQUIRED => true,
                ApiBase::PARAM_TYPE => 'upload'
            ),
            'language' => array(
                ApiBase::PARAM_REQUIRED => false,
                ApiBase::PARAM_TYPE => 'string'                
            ),          
        );
    }
}