<?php
// Set up the include path for the HulkMVC Framework
ini_set('include_path', ini_get('include_path') . ':' . dirname(getcwd()) . '/libs');

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'HulkMVCTests::main');
}
 
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
 
require_once 'HulkMVCContextSuite.php';
require_once 'HulkMVCDOSuite.php';

class HulkMVCTests {

    /**
     * Enter description here...
     *
     */
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
    
    /**
     * Enter description here...
     *
     * @return unknown
     */
    public static function suite() {
        
        $suite = new PHPUnit_Framework_TestSuite('HulkMVC Framework');

        $suite->addTestSuite(HulkMVCContextSuite::suite());
        $suite->addTestSuite(HulkMVCDOSuite::suite());

        return $suite;
    }
    
}
 
if (PHPUnit_MAIN_METHOD == 'HulkMVCTests::main') {
    HulkMVCTests::main();
}
?>
