<?php
/* $Id: Context.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * Context.class.php
 * 
 * <p>Class definition for the HulkMVC web application context.</p>
 * 
 * <p>The main design principle behind the Context object is its ability to support 
 * several web applications in a shared hosting environment.  Either through 
 * subdomains or a directory structure, several applications and their associated 
 * configuation can be supported by the HulkMVC framework.</p>
 * 
 * <p>This file is <i>require_once</i> by the {@link HulkMVC_FrontController}
 * as this file provides the <i>ClassLoader</i> and the base <i>PEAR</i>
 * modules.  The <i>ClassLoader</i> provides an interface to autoload any core
 * HulkMVC classes as well as any specific application classes derived from the 
 * HulkMVC core classes, provided they conform to the HulkMVC naming convention, as 
 * well as any class definitions which conform to the PEAR naming convention.  
 * Additionally, this class will load the inital class from a 3rd party template 
 * systems supported: <i>Smarty</i>, <i>Savant2</i> and <i>Savant3</i></p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Context
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/lgpl-license.php  LGPL License 2.1
 * @version SVN: $Revision: 714 $
 */

/** Insert ClassLoader class and IClassLoader interface and __autoload function. */
require_once 'ClassLoader.php';

/** Insert the PEAR::Cache_Lite package */
require_once 'Cache/Lite.php';

/** Include this manually as context may not parse correctly and need to know location 
      of configuraation exceptions */
require_once 'Exception/Config.php';
 
/**
 * Configures and sets the HulkMVC application context.
 * 
 * <p>This class implements <i>IClassLoader</i> interface to provide a quick
 * means of loading all the core HulkMVC classes and any web application classes 
 * derived from the core HulkMVC classes which follow the required HulkMVC naming
 * conventions.</p>
 * 
 * <p>Reads an application specific XML file, located in a subdirectory (named with 
 * the web application's name) of the config/ of the application directory structure.
 * The config XML contains all the properties to configure and run the web 
 * application.  The configuration file is parsed and the settings are serialized 
 * and cached for faster retrieval on the next page load.</p>
 * 
 * <p>The configuration settings allows the Context to construct the necessary 
 * objects for the operation of the web application.  The Context will construct the
 * defined objects: A database object which connects using a user ID with only SELECT
 * level privledges, A database object which connects using a user ID with UPDATE, 
 * INSERT and DELETE level privledges, A PEAR::Log logging object, a Templating 
 * system and an object for handling sessions in a secure manner.  All instances of
 * these objects are created on their first.  This prevents this class from recreating
 * all the objects on every page load even though the instance may never be used
 * for that page.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Context
 * @access public
 * @final
 */  
final class HulkMVC_Context implements IClassLoader {

	/**
	 * @var string The string that will be prefixed to the session filename along
	 * with the session ID.
	 * @access private
	 */
	private $sessionFilePrefix = 'sess_';

	/**
	 * @var mixed The array of parsed configuration details.
	 * @access private
	 */
	private $config = array();

	/**
	 * @var object An instance of the Cache_Lite PEAR module.
	 * @access private
	 */
	private $cache;

	/**
	 * @var string The application name passed into Context by user.
	 * @access private
	 */
	private $userAppName;
	
	/**
	 * Constructs an HulkMVC_Context object.
	 * 
	 * <p>The application name must conform to the naming convention in which 
	 * the application name must be a subdirectory of an include_path set in 
	 * httpd.conf, php.ini or .htaccess file.</p>
	 * 
	 * <p>Additionally this class makes uses of several PHP5 features and will
	 * terminate with an error if the current web environment is not running at
	 * a PHP version greater than or equal to 5.0.</p>
	 * 
	 * @param string $appName The name of the application which conforms to
	 * the naming convention.
	 * @param bool $forceCacheCheck A flag indicating whether the config XML file
	 * should be explicitly checked to see if changes were made after the last time
	 * the cached was saved.  If <i>true</i> the modified times of the config 
	 * XML file and cache file are compared.  The default is <i>false</i> and 
	 * should be left as such in a production environment and only the existence of 
	 * the cache is checked.
	 * @throws HulkMVC_ConfigException Thrown if there are any problems in loading 
	 * the config xml or cache files as well as any inconsistencies in the PHP 
	 * versions or configuration information in the XML config file.
	 * @access public
	 */
	public function __construct($appName) {

	   $this->userAppName = $appName;
	   
		// This class makes use of PHP5 stuff
		$phpVer = phpversion();
		if (version_compare($phpVer, '5.0', "<") === true) {
			die('The version of the installed language is not sufficient for the ' .
					'application.');
		}

		// Add classloading
		ClassLoader::addLoader('HulkMVC', &$this);

		// Construct config dir name.
		$contextFile = $this->findContextLocation();

		$options = array(
			'cacheDir' => dirname($contextFile) . '/',
			'lifeTime' => 7200,
		    'automaticSerialization' => true,
			'fileNameProtection' => false
		);
		$this->cache = new Cache_Lite($options);
        
		// Check if config is cached if not load XML and cache it
		if (!$this->config = $this->cache->get('context')) {
			$this->parseXMLConfigFile($contextFile);
			$this->cache->save($this->config);
		}

		// Setup the additional include paths and debugging reporting
		$this->setContext();
	}

	/**
	 * Locates the conext configuration file.
	 * 
	 * <p>The context configuration file can be located in several places depending
	 *  on the setup of the hosting environment.  The file locations are searched in
	 *  the following order:
	 * 
	 * <ol>
	 *     <li>[PHP_INCLUDE_PATH]/conf/[APP_NAME]/context.xml</li>
	 *     <li>[DOCUMENT_ROOT]/conf/[APP_NAME]/context.xml</li>
	 * </ol>
	 * </p>
	 * 
	 * @return string The file location
	 * @thows HulkMVC_Exception_Config Thrown if the context configuration file 
	 * cannot be located.
	 */
	private function findContextLocation() {
	
        // Check the include path
        $temp = 'conf/' . $this->userAppName . '/context.xml';
        if ($path = $this->checkFileExistsInPath($temp)) {
	       return $path;
	    }
	    else {
	       // Check if it is under the document root
	       $temp = $_SERVER['DOCUMENT_ROOT'] . '/' . $temp;
            if ($this->checkFileExistsInPath($temp)) {
	           return $temp;   
            }
            else {
                throw new HulkMVC_Exception_Config('The context configuration file ' .
                    'could not be located in either the include path nor the Document root.',
                    HulkMVC_Exception_Config::MISSING_CONFIG_FILE);
            }
	    }
	}
	
	/**
	 * Sets the web application's PHP environment.
	 * 
	 * @throws HulkMVC_ConfigException Thrown if the web app is in production mode
	 * (ie the debug flag is false) and PHP Safe Mode setting is also off.
	 * @access private
	 */
	private function setContext() {

		ini_set('include_path', $this->config['include']);

		// Set the environment for the application
		if ($this->config['debug']) {

			// Set the DEBUG environment
			error_reporting(E_ALL |  E_STRICT);

			ini_set('display_errors', 1);
			ini_set('display_startup_errors', 1);
		}
		else {

			// Set the PRODUCTION environment
			error_reporting(E_ALL & ~(E_NOTICE | E_WARNING));

			// Set PHP environment
			ini_set('display_errors', 0);
			ini_set('display_startup_errors', 0);

			if (!(bool)ini_get("safe_mode")) {
				throw new HulkMVC_Exception_config('A Web application in production ' .
   					'mode requires that PHP Safe Mode be tuned on.',
					HulkMVC_Exception_Config::SAFE_MODE_OFF);
			}
		}
	}

	/**
	 * Parse the XML configuation file for this application.
	 * 
	 * @param string $configFile The full path to the configuration XML file.
	 * @throws HulkMVC_ConfigException Thrown if there are any problems in loading 
	 * or parsing the configuration xml file.
	 * @access private
	 */
	private function parseXMLConfigFile($contextFile) {

        // Load schema file
        $schemaFile = 'HulkMVC/config/context.xsd';
	   
        // Locate, read and validate the contents of the configuration XML file
        if ($this->checkFileExistsInPath($schemaFile)) {

            // Load context XML
            $string = file_get_contents($contextFile);
            $dom = new DOMDocument();
            $dom->loadXML($string);
            
            // Load and validate the context schema
            $schema = file_get_contents($schemaFile, FILE_USE_INCLUDE_PATH); 
            if (!empty($schema) && @$dom->schemaValidateSource($schema)) {

                // Create object hierarchy
                $xml = new SimpleXMLElement($dom->saveXML());
                if ($xml !== false) {
                    $this->createConfigurationArray($xml);
                }
                else {
                    throw new HulkMVC_Exception_Config('Unable to parse XML ' .
                            "configuration file {$contextFile}.",
                            HulkMVC_Exception_Config::INVALID_CONFIG_FILE);
                }
            }
            else {
                throw new HulkMVC_Exception_Config('Unable to validate XML ' .
                        "configuration file {$contextFile}.",
                        HulkMVC_Exception_Config::INVALID_CONFIG_FILE);
            }
        }
        else {
            throw new HulkMVC_Exception_Config('Unable to load configuration ' .
                "schema {$schemaFile}.",
                HulkMVC_Exception_Config::MISSING_CONFIG_FILE);
        }
	}

	/**
	 * Checks if the named file exists in any of the elements of the PHP
	 * inlude_path.
	 * 
	 * <p>This function is used in lieu of file_exiists as the PHP function 
	 * requires and absolute path to check.</p>
	 *
	 * @param string $filename The name of the file to check the4 inlude_path.
	 * @return string The full path of the file.
	 */
	private function checkFileExistsInPath($filename) {
	    
        //Loop through each element of include_path and check if file is there
        $paths = explode(PATH_SEPARATOR, get_include_path());
        foreach ($paths as $path) {
            $fullpath = $path . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($fullpath)) {
                return $fullpath;
            }
        }
        
        return false;
	}
	
	/**
	 * Create this environment's configuration array containing the 
	 * application configuration info.
	 * 
	 * @param object $xml The root <i>SimplXMLElement</i> for the confiuration XML.
	 * @throws HulkMVC_ConfigException Thrown the PHP version does not meet the we
	 * application's requirements.
	 * @access private
	 */
	private function createConfigurationArray(SimpleXMLElement $app) {

		// Check that the application meets PHP version requirements
		$phpVer = phpversion();
		$this->config['phpVersion'] = (string)$app->php_version;
		if (version_compare($phpVer, $this->config['phpVersion'], "<") === true) {

			throw new HulkMVC_Exception_Config("The PHP version {$phpVer} is not " .
					'sufficent for the application.  Requres at least a PHP version of ' .
					"{$this->config['phpVersion']}.",
					HulkMVC_Exception_Config::INVALID_PHP_VER_FOR_APP);
		}

		// Set WEB APP details
		$this->config['appName'] = (string)$app['name'];
		$this->config['appVersion'] = (string)$app['version'];

		$this->config['debug'] = (bool)$app->debug;

		$this->config['webRoot'] = (string)$app->web_root;
		$this->config['appRoot'] = (string)$app->app_root;
		if (substr($this->config['appRoot'], -1) !== '/' ) {
		    $this->config['appRoot'] .= '/';
		}

		// Set INCLUDE path
		$delim = (PHP_OS == "WIN32" || PHP_OS == "WINNT") ? ';': ':';

		$this->config['include'] = ini_get('include_path');
		$this->config['include'] .= $delim . $this->config['appRoot'];
		foreach($app->includes->include as $include) {
			$this->config['include'] .= $delim . (string)$include;
		}

		// Set object PROPERTIES
		$this->setDatabaseConfigProperties($app);
		$this->setSessionConfigProperties($app);
		$this->setLoggerConfigProperties($app);
		$this->setTemplateConfigProperties($app);
		$this->setControllerConfigProperties($app);
	}

	/**
	 * Sets the configuration properties for the database objects.
	 * 
	 * @param object $xml The root <i>SimplXMLElement</i> for the confiuration XML.
	 * @throws HulkMVC_ConfigException Thrown if the DB handler type PHP module is
	 * not loaded.
	 * @access private
	 */
	private function setDatabaseConfigProperties($app) {

		$this->config['database']['ext'] = (string)$app->database['ext'];
		$this->config['database']['driver'] = (string)$app->database['driver'];
		$this->config['database']['host'] = (string)$app->database->host;
		$this->config['database']['schema'] = (string)$app->database->schema;

		// Check to make sure DB handler type is valid
		if ($this->config['database']['ext'] === 'pdo') {
            $ext = $this->config['database']['ext'] . '_' . $this->config['database']['driver'];
		}
		else {
		    $ext = $this->config['database']['driver'];
		}
 
		if (!extension_loaded($ext)) {
			throw new HulkMVC_Exception_Config('The PHP module ' .
				"{$ext} is not loaded and is the required database handler.",
				HulkMVC_Exception_Config::INVALID_DATABASE_HANDLER);
		}

		$queryDetails = $app->database->query_user;
		$this->config['database']['queryUser'] = (string)$queryDetails->username;
		$this->config['database']['queryPassword'] = (string)$queryDetails->password;
		$updateDetails = $app->database->update_user;
		$this->config['database']['updateUser'] = (string)$updateDetails->username;
		$this->config['database']['updatePassword'] = (string)$updateDetails->password;
	}

	/**
	 * Sets the configuration properties for the HulkMVC sessions.
	 * 
	 * @param object $xml The root <i>SimplXMLElement</i> for the confiuration XML.
	 * @throws HulkMVC_ConfigException Thrown if the session configuration is invalid.
	 * @access private
	 */
	private function setSessionConfigProperties($app) {

	    // Set the default session handler if there is no value
	    $this->config['session']['handler'] = (string)$app->session['handler'];
	    if (empty($this->config['session']['handler'])) {
	        $this->config['session']['handler'] = 'file';
	    }

        // Set the default security level if no value is set
	    $this->config['session']['security'] = (string)$app->session['security'];
        if (empty($this->config['session']['security'])) {
            $this->config['session']['security'] = 'permissive';
        }

        // Set the default session timeout if no value set
		$this->config['session']['timeout'] = (int)$app->session['timeout'];
		if (empty($this->config['session']['timeout'])) {
			$this->config['session']['timeout'] = (int)0;
		}

		// set up session details depending on the type of handler
		if ('db' === $this->config['session']['handler']) {

		    // Get the session database details
		    $this->config['session']['ext'] = (string)$app->database['ext'];
            $this->config['session']['driver'] = (string)$app->database['driver'];
			$this->config['session']['host'] = $this->config['database']['host'];
			$this->config['session']['schema'] = $this->config['database']['schema'];
			
			$sessDBDetails = $app->session->session_user;
			$this->config['session']['user'] = (string)$sessDBDetails->username;
			$this->config['session']['password'] = (string)$sessDBDetails->password;
		}
		else if ('file' === $this->config['session']['handler']) {
		    $dir = (string)$app->session->directory;
            if (empty($dir)) {
                $dir = '/tmp/';
            }
			$this->config['session']['directory'] = $dir;
			$this->config['session']['filename'] = $this->sessionFilePrefix . $this->config['appName'];
		}
		else {

			throw new HulkMVC_Exception_Config('The specified session handler ' .
				"{$this->config['session']['handler']} is not valid.",
				HulkMVC_Exception_Config::INVALID_SESSION_HANDLER);
		}

		// Check the session settings as they are dependent on database settings
		if ('strict' === $this->config['session']['security']
				&& 'db' !== $this->config['session']['handler']) {

			throw new HulkMVC_Exception_Config('Specifing a strict session ' .
				'security level the handler type attribute MUST be "db"',
				HulkMVC_Exception_Config::INVALID_SESSION_HANDLER);
		}

		// Make sure that a STRICT policy requires a non-zero timeout
		if ('strict' === $this->config['session']['security']
				&& 0 === $this->config['session']['timeout']) {

			throw new HulkMVC_Exception_Config('Specifing a strict session ' .
				'security level the timeout attribute MUST have a value greater ' .
				'than 0',
				HulkMVC_Exception_Config::INVALID_SESSION_TIMEOUT);
		}
	}

	/**
	 * Sets the configuration properties for the logger object.
	 * 
	 * @param object $xml The root <i>SimplXMLElement</i> for the confiuration XML.
	 * @access private
	 */
	private function setLoggerConfigProperties($app) {

	    $logging = $app->logging;
	    foreach ($logging->children()  as $logger) {
	        
	        switch ($logger->getName()) {
	            
	            case 'log_file' :
	                $this->config['logger']['file']['filename'] = $this->appRoot . $this->appName . '/' .
	                       (string)$logger->filename;
	                if (isset($logger->append)) {
	                   $this->config['logger']['file']['append'] = (boolean)$logger->append;
	                }
                    if (isset($logger->mode)) {
                       $this->config['logger']['file']['mode'] = (integer)$logger->mode;
                    }
                    if (isset($logger->lineFormat)) {
                       $this->config['logger']['file']['lineFormat'] = (string)$logger->lineFormat;
                    }
	                if (isset($logger->timeFormat)) {
                       $this->config['logger']['file']['timeFormat'] = (string)$logger->timeFormat;
                    }
	                break;
	            
	            case 'log_db' :
	                $this->config['logger']['sqlite']['filename'] = $this->appRoot . $this->appName . '/' .
                           (string)$logger->filename;
	                if (isset($logger->append)) {
                       $this->config['logger']['sqlite']['append'] = (boolean)$logger->append;
                    }
	                if (isset($logger->persistent)) {
                       $this->config['logger']['sqlite']['persistent'] = (boolean)$logger->persistent;
                    }
                    break;
                    
                case 'log_win' :
	                $this->config['logger']['win']['title'] = (string)$logger->title;
                    if (isset($logger->colors)) {
                       $this->config['logger']['rgb']['colors'] = explode(' ', (string)$logger->colors);
                    }
                    break;
	        }
	    }

		// Create logger prepend strings
		if ($this->debug) {
			$this->config['logger']['ident'] = '[DEBUG] ';
		}
		else {
			$this->config['logger']['ident'] = '';
		}
	}

	/**
	 * Sets the configuration properties for the MVC template system.
	 * 
	 * @param object $xml The root <i>SimplXMLElement</i> for the confiuration XML.
	 * @access private
	 */
	private function setTemplateConfigProperties($app) {

	    // Template system is optional
		if (isset($app->template)) {
		    
		    // Set the root of the template locations
		    $rootDir = $this->config['appRoot'];
		    
		    // Get the root template element
		    $template = $app->template;
		    
		    // Get the type of template system to use
            $this->config['template']['type'] = (string)$template['type'];
            if (empty($this->config['template']['type'])) {
                $this->config['template']['type'] = 'HulkMVC';
            }
            
    		$this->config['template']['templates'] = $rootDir . (string)$template->templates;
    		$this->config['template']['cache'] = $rootDir . (string)$template->cache;
    
    		// Set optional sets for different handlers
    		if (isset($template->config)) {
    			$this->config['template']['config'] = $rootDir . (string)$template->config;
    		}
    		if (isset($template->templates_c)) {
    			$this->config['template']['templates_c'] = $rootDir .
                        (string)$template->templates_c;
    		}
		}
	}

	/**
	 * Sets the configuration properties for the MVC controllers.
	 * 
	 * @param object $xml The root <i>SimplXMLElement</i> for the confiuration XML.
	 * @access private
	 */
	private function setControllerConfigProperties($app) {

		$controllers = $app->controllers;

		// Setup the individual page controllers
		foreach($controllers->controller as $controller) {

			$name = md5((string)$controller['path']);
            
			$this->config['controllers'][$name]['name'] = (string)$controller['name'];
			$this->config['controllers'][$name]['url'] = (string)$controller['path'];
			
			// Parse the associated mime types
            $mimeTypeList = array();
            foreach($controller->mime_types as $mimeType) {
                $mimeTypeList[] = (string)$mimeType;
            }
            $this->config['controllers'][$name]['mime-types'] = $mimeTypeList;
            
			// Parse the associated filters
			$filterList = array();
			foreach($controller->filters as $filter) {
				$filterList[] = (string)$filter;
			}
			$this->config['controllers'][$name]['filters'] = $filterList;
		}
	}

	/**
	 * Prevents setting configuration information from outside of the class.
	 * 
	 * @param string $name The named class variable.
	 * @param string $value The value for the named class variable.
	 * @access private
	 */
	private function __set($name, $value) {
	}

	/**
	 * Gets the value of the named class variable.
	 * 
	 * @param string $name The named class variable.
	 * @return mixed The value of the named class variable.
	 * @access public
	 */
	public function __get($name) {
		return $this->config[$name];
	}

	/**
	 * Implements the <i>canLoadClass()</bi> function of the <i>IClassLoader</i>
	 * interface.
	 * 
	 * @param string $class The name of the class to use to find its corresponding
	 * class file.
	 * @return boolean True if this class can located the source file of the class.
	 */
	public function canLoadClass($class) {

		$components = explode('_', $class);

		// If the class is a base class of the framework or a derived class
		// of the framework
		if ('HulkMVC' === $components[0]) {
			return true;
		}
		else if ($this->config['appName'] === $components[0]) {
			return true;
		}
		else if ('Smarty' === $components[0] || 'Savant2' === $components[0] ||
					'Savant3' === $components[0]) {

			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Implements the <i>getPath()</i> function of the <i>IClassLoader</i> interface.
	 * 
	 * @param string $class The name of the class to use to find its corresponding
	 * class file.
	 * @return string The path relative to the include directory in which the class
	 * file is located.
	 */
	public function getPath($class) {

		$components = explode('_', $class);

		$classFile = '';
		if ('HulkMVC' === $components[0]) {
			$classFile = str_replace('_', '/', $class) . '.php';
		}
		else if ($this->config['appName'] === $components[0]) {
			$classFile = $this->config['appRoot'] . 
									str_replace('_', '/', $class) . '.php';
		}
		else if ('Smarty' === $components[0]) {

			$classFile = $components[0] . '.class.php';
		}
		else if ('Savant2' === $components[0] || 'Savant3' === $components[0]) {

			$classFile = $components[0] . '.php';
		}
		else {
			trigger_error("Unknown class {$class} is being requested from the HulkMVC " .
									'classloader.', E_USER_ERROR);
		}

		return $classFile;
	}

}

?>
