<?php
/**
 * Tests for AndroidXmlFFS
 *
 * @file
 * @author Niklas Laxström
 * @license GPL-2.0+
 */
 
class TransProjectTest extends MediaWikiTestCase {
    
    public testCreateNew(){
        self::setupTestDB('huiji-sites', 'lordofthetest');
        $obj = TransProject::createNew('test');
        $this->assertEquals('test', $obj->getName());
        $obj = TransProject::createNew('test');
        $this->assertEquals( null, $obj->getName());
        self::setupTestDB('huiji-sites', 'lordofthetest');
        self::teardownTestDB();
    }
}