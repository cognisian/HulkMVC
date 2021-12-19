<?php

require_once 'PHPUnit/Framework.php';

require_once '../libs/HulkMVC/Context.php';
require_once '../libs/HulkMVC/Factory.php';

class TestDO
 extends PHPUnit_Framework_TestCase {

    private $fixture;
    private $appName = 'example';
    private $confDir = 'conf/';
    private $dirname;

    private $previousPath;

    private $fullXML = 'fullDetails.xml';
    private $validXML = 'minimal.xml';
    private $invalidXML = 'invalid.xml';
    private $dbSessionXML = 'dbSession.xml';
    private $fileSessionXML = 'fileSession.xml';
    private $productionXML = 'production.xml';
    private $invalidSessionSecurityXML = 'invalidSessionSecurity.xml';
    private $invalidSessionTimeoutXML = 'invalidSessionTimeout.xml';

    /**
     * Sets up the Context environment
     */
    public function setUp() {

        unset($this->fixture);

        // Create a directory to hold the context.xml files
        $this->dirname = './' . $this->confDir . $this->appName;
        mkdir($this->dirname, 0777, true);

        // Get current include path and save copy before setting new
        $this->previousPath = ini_get('include_path');
        ini_set('include_path', getcwd() . ':' . $this->previousPath);
    }

    /**
     * Tears down the Context environment
     */
    public function tearDown() {
        $this->removeContextDir($this->confDir);
        ini_set('include_path', $this->previousPath);
    }

    /**
     * Tests the creation of database access object which utilizes the
     * PDO SQL functions with query permissions.
     */
    public function testPDOExec() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $db =& HulkMVC_Factory::createDB($this->fixture, HulkMVC_Factory::QUERY_DB);

    }

    /**
     * Creates a context file from the given filename.
     *
     * <p>There is a set of test context files which will exercise various parts of the
     * context configuration setup.  This function will copy the filename to the
     * test path into a file named context.xml.</p>
     *
     * <p>This will also set the include path to point to the fixed location of
     * the context file location.  A fixture variable is set to the previous
     * include path so it can be restored in the tearDown.</p>
     *
     * @param string $contents The context test file to use for the test.
     */
    private function setContextFile($filename) {
        copy('./files/' . $filename, $this->dirname . '/context.xml');
    }

    /**
     * Removes all files and directories from the Context configuration
     * direectories.
     *
     * @param string $folderDir The folder to delete
     */
    private function removeContextDir($folderPath) {

        // If this is a directory
        if (is_dir($folderPath)) {

            // Loop through each entry in directory
            foreach (scandir($folderPath) as $value) {

                // Delete everything but the special directories
                if ($value != "." && $value != "..") {
                    $value = $folderPath . "/" . $value;
                    if (is_dir($value)) {
                        $this->removeContextDir($value);
                    }
                    elseif (is_file($value)) {
                        @unlink($value);
                    }
                }
            }

            rmdir($folderPath);
        }
    }

}
?>