<?php
/**
 * Query module to get infomation about the currently logged-in user
 *
 * @ingroup API 
 */
class ApiQueryAllProjects extends ApiQueryGeneratorBase {
	protected $workflow;
	public function __construct( ApiQuery $query, $moduleName ){
		parent::__construct( $query, $moduleName, 'ap');
	}

	public function execute(){
		$this->run();
	}
	public function getCacheMode( $params ){
		return 'public';
	}
	public function executeGenerator( $resultPageSet ){
		if ( $resultPageSet->isResolvingRedirects() ) {
			$this->dieUsage(
				'Use "gapfilterredir=nonredirects" option instead of "redirects" ' .
					'when using allpages as a generator',
				'params'
			);
		}
		$this->run( $resultPageSet );
	}
	private function run ( $resultPageSet = null ) {
		$db = $this->getDB();
		$params = $this->extractRequestParams();

		$this->addTables( 'transproject' );
		if ( !is_null($params['continue']) ) {
			$cont = explode('|', $params['continue']);
			$this->dieContinueUsageIf( count( $cont ) != 1 );
			$op = $params['dir'] == 'descending' ? '<' : '>';
			$cont_from = $db->addQuotes( $cont[0] );
			$this->addWhere( "tp_name $op= $cont_from" );
		}

		//only show published works
		if ( isset($params['workflow'] ) ){
			$where = [];
			$this->workflow = array_flip($params['workflow']);
			if (isset($this->workflow['new'])){
				$where[] = "tp_workflow = 0";
			}
			if (isset($this->workflow['needsproofreading'])){
				$where[] = "tp_workflow = 1";
			}
			if (isset($this->workflow['ready'])){
				$where[] = "tp_workflow = 2";
			}
			if (isset($this->workflow['published'])){
				$where[] = "tp_workflow = 3";
			}
			if (isset($this->workflow['deleted'])){
				$where[] = "tp_workflow = 4";
			}
			$clause = implode(" OR ", $where);
			$this->addWhere( $clause );
			$this->addOption( 'USE INDEX', 'tp_name' );
		} else {
			$this->addWhereFld("tp_workflow", 3);
			$this->addOption( 'USE INDEX', 'page_title' );
		}
		

		$dir = ( $params['dir'] == 'descending' ? 'older' : 'newer' );
		$from = ( $params['from'] === null
			? null
			: $this->titlePartToKey( $params['from'], $params['namespace'] ) );
		$to = ( $params['to'] === null 
			? null
			: $this->titlePartToKey( $params['to'], $params['namespace'] ) );
		$this->addWhereRange( 'page_title', $dir, $from, $to );

		if ( is_null($resultPageSet) ){
			$selectFields = [
				'tp_id',
				'tp_name',
				'page_namespace',
				'page_title',
				'page_id',
			];
		} else {
			$selectFields = $resultPageSet->getPageTableFields();
		}
		$this->addFields( $selectFields );
		
		$limit = $params['limit'];
		$this->addOption( 'LIMIT', $limit + 1 );
		$res = $this->select( __METHOD__ );
		$count = 0;
		$result = $this->getResult();
		foreach ( $res as $row ) {
			if ( ++$count > $limit ) {
				$this->setContinueEnumParameter( 'continue', $row->page_title );
				break;
			}

			if ( is_null( $resultPageSet ) ) {
				$title = Title::makeTitle( $row->page_namespace, $row->page_title );
				if (is_null($title)){
					$vals = ['tp_name' => $row->tp_name];
				} else {
					$vals = [
						'tp_id' => $row->tp_id,
						'tp_name' => $row->tp_name,
						'pageid' => intval( $row->page_id ),
						'ns' => intval( $title->getNamespace() ),
						'title' => $title->getPrefixedText()
					];					
				}
				$fit = $result->addValue(['query', $this->getModuleName()], null, $vals );
				if (!$fit) {
					$this->setContinueEnumParameter( 'continue', $row->tp_name );
					break;
				}
			} else {
				$resultPageSet->processDbRow($row);
			}
		}

		if ( is_null( $resultPageSet ) ) {
			$result->addIndexedTagName( ['query', $this->getModuleName() ], 'p');
		}


	}
	public function getAllowedParams() {
		return [
			'from' => null,
			'continue' => [
				ApiBase::PARAM_HELP_MSG => 'api-help-param-continue',
			],
			'to' => null,
			'workflow' => [
				ApiBase::PARAM_ISMULTI => true,
				ApiBase::PARAM_TYPE => [
					'new',
					'needsproofreading',
					'ready',
					'published',
					'deleted',
					'error'
				]
			],
			'limit' => [
				ApiBase::PARAM_DFLT => 10,
				ApiBase::PARAM_TYPE => 'limit',
				ApiBase::PARAM_MIN => 1,
				ApiBase::PARAM_MAX => ApiBase::LIMIT_BIG1,
				ApiBase::PARAM_MAX2 => ApiBase::LIMIT_BIG2
			],
			'dir' => [
				ApiBase::PARAM_DFLT => 'ascending',
				ApiBase::PARAM_TYPE => [
					'ascending',
					'descending'
				]
			]

		];
	}
}