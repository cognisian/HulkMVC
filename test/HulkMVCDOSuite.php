<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'HulkMVCDOSuite::main');
}
 
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'TestDO.php';
 
class HulkMVCDOSuite extends PHPUnit_Framework_TestSuite {
    
    /**
     * The setup for the Context suite.
     *
     * @return PHPUnit_Framework_TestSuite The Context test suite.
     */
    public static function suite() {
        
        $suite = new PHPUnit_Framework_TestSuite('HulkMVC Data Object Suite');
 
        $suite->addTestSuite('TestDO');
 
        return $suite;
    }
 
    /**
     * Setup the Context test suite.
     */
    protected function setUp() {
        $_SERVER['DOCUMENT_ROOT'] = getcwd();
    }
 
    /**
     * Tears down the Context suite.
     */
    protected function tearDown() {        
    }
    
}

if (PHPUnit_MAIN_METHOD == 'HulkMVCDOSuite::main') {
    Framework_AllTests::main();
}

?>