<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'HulkMVCContextSuite::main');
}
 
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'TestClassLoader.php';
require_once 'TestContext.php';
require_once 'TestFactory.php';
 
class HulkMVCContextSuite extends PHPUnit_Framework_TestSuite {
    
    /**
     * The setup for the Context suite.
     *
     * @return PHPUnit_Framework_TestSuite The Context test suite.
     */
    public static function suite() {
        
        $suite = new PHPUnit_Framework_TestSuite('HulkMVC Context Suite');
 
        $suite->addTestSuite('TestClassLoader');
        $suite->addTestSuite('TestContext');       
        $suite->addTestSuite('TestFactory');
 
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

if (PHPUnit_MAIN_METHOD == 'HulkMVCContextSuite::main') {
    Framework_AllTests::main();
}

?>