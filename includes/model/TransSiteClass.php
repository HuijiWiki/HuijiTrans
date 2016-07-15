<?php
/**
* Base Model for trans site.
* @ingroup file
*/
class TransSite extends WikiSite
{
	const CACHE_MAX = 1000;
	private static $siteCache;
	
	private static function getSiteCache() {
        if ( self::$siteCache == null ) {
            self::$siteCache = new HashBagOStuff( [ 'maxKeys' => self::CACHE_MAX ] );
		}
		return self::$siteCache;
	}
	/**
	 * get site object by site prefix
	 * @param  string $prefix site prefix such as 'lotr'、'asoiaf'..
	 * @return object         site object
	 */
	public static function newFromPrefix( $prefix ){
		$siteCache = self::getSiteCache();
		$site = $siteCache->get($prefix);
		if ( $site != '' ){
			return $site;
		} else {
			$site = new TransSite();
			$site->setPrefix($prefix);
			$siteCache->set($prefix, $site);
			return $site;
		}

	}
	/**
	 * site's group
	 * @return string always wiki
	 */
	public function getGroup(){
		return 'trans';
	}

	/**
	 * get site stats is $formatted is true, those number will be fortamtted
	 * @param  boolean $formatted
	 * @return array    the site stats
	 */
	public function getStats( $formatted = true ){
		global $wgMemc;
		$data = parent::getStats($formatted);
		$key = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'published_work', $this->mPrefix);
		$key2 = wfForeignMemcKey('huiji', '', 'TransSite', 'getStats', 'translating_work', $this->mPrefix);
		$pub = $wgMemc->get($key);
		if ($pub != ''){
			$data['published_work'] = $pub;
		} else {
			$dbr = wfGetDB(DB_SLAVE);
			$r = $dbr->select(
				'transproject',
				'tp_id',
				array( 'tp_workflow' => 3 ),
				__METHOD__
			);
			if ($r != false){
				$i = 0;
				foreach( $r as $item){
					$i++;
				}
				$data['published_work'] = $i;
				$wgMemc->set($key, $i);
			} else {
				$data['published_work'] = 0;
				$wgMemc->set($key, 0);				
			}
		}
		$translating = $wgMemc->get($key2);
		if ($translating != ''){
			$data['translating_work'] = $translating;
		} else {
			$dbr = wfGetDB(DB_SLAVE);
			$r = $dbr->select(
				'transproject',
				'tp_id',
				'tp_workflow < 3',
				__METHOD__
			);
			if ($r != false){
				$i = 0;
				foreach( $r as $item){
					$i++;
				}
				$data['translating_work'] = $i;
				$wgMemc->set($key2, $i);
			} else {
				$data['translating_work'] = 0;
				$wgMemc->set($key2, 0);				
			}
		}
		$data['members'] = SiteStats::numberingroup( 'member' );
		if ($formatted){
			$data['members'] = HuijiFunctions::format_nice_number($data['members']);
			$data['translating_work'] =  HuijiFunctions::format_nice_number($data['translating_work']);
			$data['published_work'] = HuijiFunctions::format_nice_number($data['published_work']);
		}
		return $data;

	}


}