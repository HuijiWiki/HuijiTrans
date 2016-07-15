<?php
/**
 * Query module to get infomation about the currently logged-in user
 *
 * @ingroup API 
 */
class ApiQueryProjects extends ApiQueryBase {
	private $prop;
	/**
	 * Constructor is optional. Only needed if we give
	 * this module properties a prefix (in this case we're using
	 * "ex" as the prefix for the module's properties.
	 * Query modules have the convention to use a property prefix.
	 * Base modules generally don't use a prefix, and as such don't
	 * need the constructor in most cases.
	 */
	public function __construct( $query, $moduleName ) {
		parent::__construct( $query, $moduleName, 'tr' );
	}
	/**
	 * In this example we're returning one ore more properties
	 * of wgExampleFooStuff. In a more realistic example, this
	 * method would probably
	 */
	public function execute() {
		global $wgHuijiPrefix;
		$params = $this->extractRequestParams();

		if ( !is_null( $params['prop'] ) ) {
			$this->prop = array_flip( $params['prop'] );
		} else {
			$this->prop = [];
		}
		$data = [];

		$ids = (array)$params['ids'];
		$result = $this->getResult();

		foreach( $ids as $id ){
			
			$tp = TransProject::newFromId($id);

			if (isset( $this->prop['translator'] )){
				$data[$id]['translator'] =  $tp->getTranslators();
			}
			if (isset( $this->prop['rating'])){
				
				$site = TransSite::newFromPrefix($wgHuijiPrefix);
				if ($tp->getPage() != null  && $site->getProperty( 'enable-voteny' ) == 1){
					$vote = new VoteStars($tp->getPage()->getArticleId());
					$data[$id]['rating'] = $vote->display();
				} else {
					$data[$id]['rating'] =  null;
				}
			}
			if ( isset($this->prop['title'] ) ){
				if ($tp->getPage() != null){
					$data[$id]['title'] = $tp->getPage()->getFullText();
				} else {
					$data[$id]['title'] = 'undefined';
				}
				
			}
			if ( isset($this->prop['workflow'] ) ){
				$data[$id]['workflow'] = $tp->getWorkflowState();
			}
			if ( isset($this->prop['publicationtime'] ) ){
				$data[$id]['publicationtime'] = $tp->getPublicationTime();
			}		
			if ( isset($this->prop['lastmodified'] ) ){
				$data[$id]['lastmodified'] = '还没做好';
			}	
		}

		$result->addValue( null, $this->getModuleName(), $data );
	}

	public function getAllowedParams() {
		return array(
			'prop' => [
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => [
					'translator',
					'rating',
					'title',
					'workflow',
					'publicationtime',
					'lastmodified'
				],
				ApiBase::PARAM_HELP_MSG_PER_VALUE => [],
			],
			'ids' => [
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_REQUIRED => true,
			],

		);
	}

	protected function getExamplesMessages() {
		return array(
			'action=query&list=example'
				=> 'apihelp-query+example-example-1',
			'action=query&list=example&key=do'
				=> 'apihelp-query+example-example-2',
		);
	}	

}