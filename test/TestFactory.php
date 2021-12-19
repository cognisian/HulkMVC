<?php

require_once 'PHPUnit/Framework.php';

require_once '../libs/HulkMVC/Context.php';
require_once '../libs/HulkMVC/Factory.php';

class TestFactory extends PHPUnit_Framework_TestCase {

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
        if (isset($this->fixture)) {
            if (file_exists($this->fixture->logger['file']['filename'])) {
                unlink($this->fixture->logger['file']['filename']);
            }
            if (file_exists($this->fixture->logger['sqlite']['filename'])) {
                unlink($this->fixture->logger['sqlite']['filename']);
            }
        }
    }

    /**
     * Tests the creation of logging resources via Factory.
     */
    public function testCreateLogger() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $logger = HulkMVC_Factory::createLogger($this->fixture);

        $message = 'This is a test message';
        $logger->log($message);

        if (!file_exists($this->fixture->logger['file']['filename'])) {
            $this->fail('The log file was not created.');
        }

        if (!file_exists($this->fixture->logger['sqlite']['filename'])) {
            $this->fail('The log database was not created.');
        }

        // Check if database contains message
        $db = sqlite_open($this->fixture->logger['sqlite']['filename']);
        $query = sqlite_query("SELECT * FROM log_table", $db);
        $rows = sqlite_fetch_all($query);
        if (count($rows) !== 1) {
            sqlite_close($db);
            $this->fail('The log message was not inserted.');
        }

        // Check if message is inserted
        foreach ($rows as $row) {
            if ($row['message'] !== $message) {
                sqlite_close($db);
                $this->fail('The log message was not inserted.');
            }
        }
        sqlite_close($db);

    }

    /**
     * Tests the creation of database access object which utilizes the
     * PDO SQL functions with query permissions.
     */
    public function testPDOQueryDataObject() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $db =& HulkMVC_Factory::createDB($this->fixture, HulkMVC_Factory::QUERY_DB);
        if (empty($db) || !($db instanceof HulkMVC_DO_PDO)) {
            $this->fail('The HulkMVC_DO was not created.');
        }
        $this->assertEquals('mysql',
                $db->getAttribute(HulkMVC_DO::ATTR_DRIVER_NAME),
                'The database driver is incorrect');
    }

    /**
     * Tests the creation of database access object which utilizes the
     * PDO SQL functions with update permissions.
     */
    public function testPDOUpdateDataObject() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $db =& HulkMVC_Factory::createDB($this->fixture, HulkMVC_Factory::UPDATE_DB);
        if (empty($db) || !($db instanceof HulkMVC_DO_PDO)) {
            $this->fail('The HulkMVC_DO was not created.');
        }
        $this->assertEquals('mysql',
                $db->getAttribute(HulkMVC_DO::ATTR_DRIVER_NAME),
                'The database driver is incorrect');
    }

    /**
     * Tests the creation of database access object which utilizes the
     * native SQL functions with query permissions.
     */
    public function testNativeQueryDataObject() {

        // Set the context file for this test
        $this->setContextFile($this->validXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $db =& HulkMVC_Factory::createDB($this->fixture, HulkMVC_Factory::QUERY_DB);
        if (empty($db) || !($db instanceof HulkMVC_DO_Mysql)) {
            $this->fail('The HulkMVC_DO was not created.');
        }
        $this->assertEquals('mysql',
                $db->getAttribute(HulkMVC_DO::ATTR_DRIVER_NAME),
                'The database driver is incorrect');
    }

    /**
     * Tests the creation of database access object which utilizes the
     * native SQL functions with update permissions.
     */
    public function testNativeUpdateDataObject() {

        // Set the context file for this test
        $this->setContextFile($this->validXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $db =& HulkMVC_Factory::createDB($this->fixture, HulkMVC_Factory::UPDATE_DB);
        if (empty($db) || !($db instanceof HulkMVC_DO_Mysql)) {
            $this->fail('The HulkMVC_DO was not created.');
        }
        $this->assertEquals('mysql',
                $db->getAttribute(HulkMVC_DO::ATTR_DRIVER_NAME),
                'The database driver is incorrect');
    }

    /**
     * Tests the creation of page controllers.
     */
    public function testController() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $controller = HulkMVC_Factory::createController($this->fixture, 'Test');
        $this->assertEquals('Test', $controller->test);
        $this->assertTrue(method_exists($controller, 'processRequest'));
    }

    /**
     * Tests the creation of the Template object.
     */
    public function testTemplate() {
        $this->markTestIncomplete("Have not fully implemented the Template system");

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);
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