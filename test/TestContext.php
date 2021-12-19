<?php

require_once 'PHPUnit/Framework.php';

require_once '../libs/HulkMVC/Context.php';

class TestContext extends PHPUnit_Framework_TestCase {

    private $fixture;
    private $appName = 'example';
    private $confDir = 'conf/';
    private $dirname;

    private $previousPath;

    private $fullXML = 'fullDetails.xml';
    private $validXML = 'minimal.xml';
    private $invalidXML = 'invalid.xml';
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
     * Tests the creation of a Context object.
     *
     * <p>The context.xml file does not exist.</p>
     */
    public function testContextCreation() {

        try {
            $this->fixture = new HulkMVC_Context($this->appName);
        }
        catch (HulkMVC_Exception_Config $ex) {

            echo "Test Exception toString dump: \n";
            echo $ex;
            echo "\n\n";

            echo "Test Exception HTML dump: \n";
            echo $ex->fullDisplay();
            echo "\n";
            return;
        }

        $this->fail('HulkMVC_Exception_Config expected exception has not been raised.');
    }

    /**
     * Tests the creation of a Context in production mode.
     */
    public function testProductionContextCreation() {

        // Set the context file for this test
        $this->setContextFile($this->productionXML);

        $this->fixture = new HulkMVC_Context($this->appName);
    }

    /**
     * Tests the parsing of an invalid XML context content.
     *
     * <p>The XML context file contains an invalid context configuration and
     * throws an exception.</p>
     */
    public function testInvalidContextConfig() {

        // Set the context file for this test
        $this->setContextFile($this->invalidXML);

        try {
            $this->fixture = new HulkMVC_Context($this->appName);
        }
        catch (HulkMVC_Exception_Config $ex) {
            return;
        }

        $this->fail('HulkMVC_Exception_Config expected exception has not been raised.');
    }

    /**
     * Tests the parsing of the context.xml on the include path.
     *
     * <p>This tests the parsing of the context configuration file located in
     * the include path.</p>
     */
    public function testContextFileIncludePath() {

        // Set the context file for this test
        $this->setContextFile($this->validXML);

        $this->fixture = new HulkMVC_Context($this->appName);
    }

    /**
     * Tests the invalid location of the context schema file.
     *
     * <p>The context loads the schema relative to the path by not
     * setting the incorrect path the schema could not be found.</p>
     */
    public function testInvalidSchemaLocation() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        // Set the include path to be invalid by removing any HULKMVC paths
        $path = ini_get('include_path');
        $elements = explode(':', $path);
        $index = 0;
        foreach ($elements as $element) {
            $path = explode('/', $element);
            foreach ($path as $name) {
                if ($name == 'HulkMVC') {
                    unset($elements[$index]);
                }
            }
            $index ++;
        }
        ini_set('include_path', implode($elements, ':'));

        try {
            $this->fixture = new HulkMVC_Context($this->appName);
        }
        catch (HulkMVC_Exception_Config $ex) {
            return;
        }

        $this->fail('HulkMVC_Exception_Config expected exception has not been raised.');
    }


    /**
     * Tests the correct parsing of the application settings
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.</p>
     */
    public function testApplicationConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals(5.2, $this->fixture->phpVersion,
                "The required PHP version {$this->fixture->phpVersion} " .
                "in context.xml is not " .
                '5.2');
        $this->assertTrue($this->fixture->debug,
                "The HulkMVC is in debug mode: {$this->fixture->debug} " .
                "in context.xml is not " .
                'TRUE');
        $this->assertEquals('http://localhost', $this->fixture->webRoot,
                "The HulkMVC web root is {$this->fixture->webRoot} " .
                "in context.xml is not " .
                'http://localhost');
        $this->assertEquals('/home/www/localhost/htdocs/apps/', $this->fixture->appRoot,
                "The HulkMVC application directory is {$this->fixture->appRoot} " .
                "in context.xml is not " .
                '/home/www/localhost/htdocs/apps/');
        $this->assertEquals(getcwd() . ':' . $this->previousPath . ':' .
                $this->fixture->appRoot . ':/tmp/', $this->fixture->include,
                "The HulkMVC include path is {$this->fixture->include} " .
                "in context.xml is not " .
                getcwd() . ':' . $this->previousPath . ':' .
                $this->fixture->appRoot . ':/tmp/');
    }

    /**
     * Tests the loading of class.
     */
    public function testLoadApplicationClass() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);
        $this->fixture = new HulkMVC_Context($this->appName);

        $test = new example_Controller_Test();
        $this->assertEquals('Test', $test->test);
        $this->assertTrue(method_exists($test, 'processRequest'));
    }

    /**
     * Tests the correct parsing of the database configuration for PDO driver.
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.</p>
     */
    public function testPDODatabaseConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('pdo', $this->fixture->database['ext'],
                "The database extension {$this->fixture->database['ext']} " .
                "in context.xml is not " .
                'pdo');
        $this->assertEquals('mysql', $this->fixture->database['driver'],
                "The database handler {$this->fixture->database['driver']} " .
                "in context.xml is not " .
                'mysql');
        $this->assertEquals('localhost', $this->fixture->database['host'],
                "The database host {$this->fixture->database['host']} " .
                "in context.xml is not " .
                'localhost');
        $this->assertEquals('GWCollageDB', $this->fixture->database['schema'],
                "The database schema {$this->fixture->database['schema']} " .
                "in context.xml is not " .
                'GWCollageDB');
        $this->assertEquals('reader', $this->fixture->database['queryUser'],
                "The database query user {$this->fixture->database['queryUser']} " .
                "in context.xml is not " .
                'mysql');
        $this->assertEquals('', $this->fixture->database['queryPassword'],
                "The database query user {$this->fixture->database['queryPassword']} " .
                "in context.xml is not " .
                '(empty)');
        $this->assertEquals('chalmers', $this->fixture->database['updateUser'],
                "The database update user {$this->fixture->database['updateUser']} " .
                "in context.xml is not " .
                'chalmers');
        $this->assertEquals('hangHim69', $this->fixture->database['updatePassword'],
                "The database update password {$this->fixture->database['updatePassword']} " .
                "in context.xml is not " .
                'hangHim69');
    }

    /**
     * Tests the correct parsing of the database configuration for native driver.
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.</p>
     */
    public function testNativeDatabaseConfig() {

        // Set the context file for this test
        $this->setContextFile($this->validXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('native', $this->fixture->database['ext'],
                "The database extension {$this->fixture->database['ext']} " .
                "in context.xml is not " .
                'pdo');
        $this->assertEquals('mysql', $this->fixture->database['driver'],
                "The database handler {$this->fixture->database['driver']} " .
                "in context.xml is not " .
                'mysql');
        $this->assertEquals('localhost', $this->fixture->database['host'],
                "The database host {$this->fixture->database['host']} " .
                "in context.xml is not " .
                'localhost');
        $this->assertEquals('GWCollageDB', $this->fixture->database['schema'],
                "The database schema {$this->fixture->database['schema']} " .
                "in context.xml is not " .
                'GWCollageDB');
        $this->assertEquals('reader', $this->fixture->database['queryUser'],
                "The database query user {$this->fixture->database['queryUser']} " .
                "in context.xml is not " .
                'mysql');
        $this->assertEquals('', $this->fixture->database['queryPassword'],
                "The database query user {$this->fixture->database['queryPassword']} " .
                "in context.xml is not " .
                '(empty)');
        $this->assertEquals('chalmers', $this->fixture->database['updateUser'],
                "The database update user {$this->fixture->database['updateUser']} " .
                "in context.xml is not " .
                'chalmers');
        $this->assertEquals('hangHim69', $this->fixture->database['updatePassword'],
                "The database update password {$this->fixture->database['updatePassword']} " .
                "in context.xml is not " .
                'hangHim69');
    }

    /**
     * Tests the correct parsing of the default session configuration.
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.  The
     * session tag </p>
     */
    public function testDefaultSessionConfig() {

        // Set the context file for this test
        $this->setContextFile($this->validXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('file', $this->fixture->session['handler'],
                "The session handler {$this->fixture->session['handler']} " .
                "in context.xml is not " .
                'file');
        $this->assertEquals('permissive', $this->fixture->session['security'],
                "The session security {$this->fixture->session['security']} " .
                "in context.xml is not " .
                'permissive');
        $this->assertEquals(0, $this->fixture->session['timeout'],
                "The session timeout {$this->fixture->session['timeout']} " .
                "in context.xml is not " .
                '0');
        $this->assertEquals('/tmp/', $this->fixture->session['directory'],
                "The session file directory {$this->fixture->session['directory']} " .
                "in context.xml is not " .
                '/tmp/');
        $this->assertEquals('sess_example', $this->fixture->session['filename'],
                "The session filename {$this->fixture->session['filename']} " .
                "in context.xml is not sess_example");
    }

    /**
     * Tests the correct parsing of the session configuration using a file as
     * the session store.
     */
    public function testFileSessionConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fileSessionXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('file', $this->fixture->session['handler'],
                "The session handler {$this->fixture->session['handler']} " .
                "in context.xml is not " .
                'file');
        $this->assertEquals('permissive', $this->fixture->session['security'],
                "The session security {$this->fixture->session['security']} " .
                "in context.xml is not " .
                'permissive');
        $this->assertEquals(60, $this->fixture->session['timeout'],
                "The session timeout {$this->fixture->session['timeout']} " .
                "in context.xml is not " .
                '60');
        $this->assertEquals('/tmp/', $this->fixture->session['directory'],
                "The session file directory {$this->fixture->session['directory']} " .
                "in context.xml is not " .
                '/tmp/');
        $this->assertEquals('sess_example', $this->fixture->session['filename'],
                "The session filename {$this->fixture->session['filename']} " .
                "in context.xml is not sess_example");
    }

    /**
     * Tests the correct parsing of the session configuration using a database
     * as the session store.
     */
    public function testDBSessionConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('db', $this->fixture->session['handler'],
                "The session handler {$this->fixture->session['handler']} " .
                "in context.xml is not " .
                'db');
        $this->assertEquals('strict', $this->fixture->session['security'],
                "The session security {$this->fixture->session['security']} " .
                "in context.xml is not " .
                'strict');
        $this->assertEquals(60, $this->fixture->session['timeout'],
                "The session timeout {$this->fixture->session['timeout']} " .
                "in context.xml is not " .
                '60');
        $this->assertEquals('session', $this->fixture->session['user'],
                "The session database user {$this->fixture->session['security']} " .
                "in context.xml is not " .
                'session');
        $this->assertEquals('session', $this->fixture->session['password'],
                "The session database user password ".
                "{$this->fixture->session['security']} in context.xml is not " .
                'session');
    }

    /**
     * Tests that an exception is thrown if the Session configuration is valid XML
     * but the configuration is incorrect.
     *
     * <p>If the user specifies a strict security policy then the handler must
     * use a database.</p>
     */
    public function testInvalidSessionSecurityConfig() {

        // Set the context file for this test
        $this->setContextFile($this->invalidSessionSecurityXML);

        try {
            $this->fixture = new HulkMVC_Context($this->appName);
        }
        catch (HulkMVC_Exception_Config $ex) {
            return;
        }

        $this->fail('HulkMVC_Exception_Config expected exception has not been raised.');
    }

    /**
     * Tests that an exception is thrown if the Session configuration is valid XML
     * but the configuration is incorrect.
     *
     * <p>If the user specifies a strict security policy then a non-zero
     * timeout.</p>
     */
    public function testInvalidSessionTimeoutConfig() {

        // Set the context file for this test
        $this->setContextFile($this->invalidSessionTimeoutXML);

        try {
            $this->fixture = new HulkMVC_Context($this->appName);
        }
        catch (HulkMVC_Exception_Config $ex) {
            return;
        }

        $this->fail('HulkMVC_Exception_Config expected exception has not been raised.');
    }

    /**
     * Tests the correct parsing of the logging configuration.
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.</p>
     */
    public function testLoggingConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('/home/www/localhost/htdocs/apps/example/log/errors.log',
                $this->fixture->logger['file']['filename'],
                "The file logging filename {$this->fixture->logger['file']['filename']} " .
                "in context.xml is not " .
                '/home/www/localhost/htdocs/apps/example/log/errors.log');
        $this->assertEquals('/home/www/localhost/htdocs/apps/example/log/errors.db',
                $this->fixture->logger['sqlite']['filename'],
                "The database logging filename {$this->fixture->logger['sqlite']['filename']} " .
                "in context.xml is not " .
                '/home/www/localhost/htdocs/apps/example/log/errors.db');
    }

    /**
     * Tests the correct parsing of the template configuration.
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.</p>
     */
    public function testTemplateConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $this->assertEquals('HulkMVC', $this->fixture->template['type'],
                "The template type {$this->fixture->template['type']} in " .
                "context.xml is not HulkMVC");

        $this->assertEquals($this->fixture->appRoot . 'templates/cache/',
                $this->fixture->template['cache'],
                "The template cache directory {$this->fixture->template['cache']} " .
                "in context.xml is not " .
                $this->fixture->appRoot . 'templates/cache/');
        $this->assertEquals($this->fixture->appRoot . 'templates/conf/',
                $this->fixture->template['config'],
                "The template config directory {$this->fixture->template['config']} " .
                "in context.xml is not " .
                $this->fixture->appRoot . 'templates/conf/');
        $this->assertEquals($this->fixture->appRoot . 'templates/',
                $this->fixture->template['templates'],
                "The template directory {$this->fixture->template['templates']} " .
                "in context.xml is not " .
                $this->fixture->appRoot . 'templates/compile/');
        $this->assertEquals($this->fixture->appRoot . 'templates/compile/',
                $this->fixture->template['templates_c'],
                "The template compile directory {$this->fixture->template['templates_c']} ".
                "in context.xml is not " .
                $this->fixture->appRoot . 'templates/compile/');
    }

    /**
     * Tests the correct parsing of the controller configuration.
     *
     * <p>The XML contains all the configuration we need to check that the
     * XML is correctly parsed and stored in the configuration array.</p>
     */
    public function testControllersConfig() {

        // Set the context file for this test
        $this->setContextFile($this->fullXML);

        $this->fixture = new HulkMVC_Context($this->appName);

        $md5 = md5('/TestController');

        $this->assertTrue(key_exists($md5, $this->fixture->controllers),
                "Unable to locate the MD5 controller path key {$md5} for URL /TestController");
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