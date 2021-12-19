<?php
/* $Id: Factory.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * Factory.php
 * 
 * <p>Provides the factory methods to create the objects configured in the 
 * {@link HulkMVC_Context} object.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Context
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 714 $
 */

/**
 * HulkMVC_Factory
 *  
 * <p>A factory object which provides factory methods to create the objects that
 * are configured via the {@link HulkMVC_Context} configuration files.</p>
 * 
 * <p>The factory methods provided by this class are:
 * 	<ul>
 * 		<li>A <i>PEAR::MDB2</i> database object created with read-write 
 * 		privledges</li>
 * 		<li>A <i>PEAR::MDB2</i> database object created with read-only 
 * 		privledges</li>
 * 		<li>A <i>PEAR::MDB2</i> database object created with read-write 
 * 		privledges on the session storage location</li>
 * 		<li>A <i>PEAR::Log</i> logging object</li>
 * 		<li>A template object as defined in the {@link HulkMVC_Context} object</li>
 * 	</ul>
 * </p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Context
 * @access public
 */
class HulkMVC_Factory {

	/**
	 * @staticvar string The constant passed to {@link createDB()} to indicate the 
	 * creation of a database that has read/write privledges.
	 * @access public 
	 */
	const UPDATE_DB = 'updateDB';

	/**
	 * @staticvar string The constant passed to {@link createDB()} to indicate the 
	 * creation of a database that has read-only privledges.
	 * @access public 
	 */
	const QUERY_DB = 'queryDB';

	/**
	 * @staticvar string The constant passed to {@link createDB()} to indicate the 
	 * creation of a database that has read/write privledges to the session table.
	 * @access public 
	 */
	const SESSION_DB = 'sessionDB';

	/**
	 * @staticvar object The <i>PEAR::Log</i> composite logging object.
	 * @access private
	 */
	private static $logger;

	/**
	 * @staticvar object <i>PDO</i> database object using a user with 
	 * read/write privledges.
	 * @access private
	 */
	private static $updateDB;

	/**
	 * @staticvar object <i>PDO</i> database object using a user with only 
	 * read-only privledges.
	 * @access private
	 */
	private static $queryDB;

	/**
	 * @staticvar object <i>PDO</i> database object accessing the session storage.
	 * @access private
	 */
	private static $sesisonDB;

	/**
	 * @staticvar object A template object.  This cached object is only set if a 
	 * template filename is not given (ie a <i>Smarty</i> template object does not
	 * have a template file as a constructor param so the same object can be reused)
	 * @access private
	 */
	private static $template;

	/**
	 * Creates an instance of the <i>PEAR::Log</i> logging class.
	 * 
	 * <p>There is one and only one instance of the logger so this methods returns
	 * a reference to that logger.  This method will cache the logger object so that on
	 * subsequent calls the logger is returned without having to recreate it.</p>
	 * 
	 * @param HulkMVC_Context $context The wb application context object.
	 * @return object A reference to the logging class.
	 * @access public
	 * @static
	 */
	public static function &createLogger(HulkMVC_Context $context) {

		if (!isset(self::$logger)) {

			// Get the configuration settings
			$settings = $context->logger;

			// Create composite logger
			@self::$logger = &Log::factory('composite');
			
			// Create iNdividual loggers
			if (isset($settings->file)) {
			    
			    // Loop through properties to create configuration array
			    $conf = array();
			    $logConf = $settings['file'];
			    foreach ($logConf as $confName => $confValue) {
			    	if ($confName != 'filename') {
			    	    $conf[$confName] = $confValue;
			    	}
			    }
			    
                @$file =& Log::factory('file', $settings['file']['filename'], $settings['ident'],
                        $conf);
                @self::$logger->addChild($file);
			}
			
		    if (isset($settings->sqlite)) {
                
                // Loop through properties to create configuration array
                $conf = array();
                $logConf = $settings['sqlite'];
                foreach ($logConf as $confName => $confValue) {
                    $conf[$confName] = $confValue;
                }
                
                @$sql =& Log::factory('sqlite', 'log_table', $settings['ident'],
                        $conf);
                @self::$logger->addChild($sql);
            }
            
            if (isset($settings->win)) {
                
                // Loop through properties to create configuration array
                $conf = array();
                $logConf = $settings['win'];
                foreach ($logConf as $confName => $confValue) {
                    $conf[$confName] = $confValue;
                }
                
                @$win =& Log::factory('win', 'LogWindow', $settings['ident'],
                        $conf);
                @self::$logger->addChild($win);
            }

    		// Set logging mask
            if ($context->debug) {
                $mask = PEAR_LOG_ALL;
            }
            else {
                $mask = PEAR_LOG_ALL & ~(Log::UPTO(PEAR_LOG_INFO));
            }
			self::$logger->setMask($mask);
			
		}
        
		return self::$logger;

	}

	/**
	 * Creates an instance of the <i>PEAR::MDB2</i> database driver class.
	 * 
	 * <p>This creates an instance of the database access class depending on the 
	 * flag.  If the flag is true then then a database access class is created using
	 * a database user with read and write prviledges.  If the flag is false then the
	 * database user with read-only privledges is created.</p>
	 * 
	 * @param HulkMVC_Context $context The wb application context object.
	 * @param string $dbType One of the database factory class constants:
	 * 	- {@link UPDATE_DB}
	 * 	- {@link QUERY_DB}
	 * 	- {@link SESSION_DB}
	 * @return object A <i>PEAR::MDB2_Driver_Common</i> concrete class
	 * is returned depending on the context configuration.
	 * @access public
	 * @static
	 */
	public static function &createDB(HulkMVC_Context $context, $dbType) {

	    // Bring the Data Object proxy interface
	    require_once 'HulkMVC/DO.php';
	    
		// Is database object cached or are we in debug mode
		if ($context->debug || !isset(self::${$dbType})) {

			$username = '';
			$password = '';

			// Get the configuration settings and set appropriate username
			// and password
			if ($dbType === HulkMVC_Factory::UPDATE_DB) {
				$settings = $context->database;
				$username = $settings['updateUser'];
				$password = $settings['updatePassword'];
			}
			else if ($dbType === HulkMVC_Factory::QUERY_DB) {
				$settings = $context->database;
				$username = $settings['queryUser'];
				$password = $settings['queryPassword'];
			}
			else if ($dbType === HulkMVC_Factory::SESSION_DB) {
				$settings = $context->session;
				$username = $settings['user'];
				$password = $settings['password'];
			}
			else {
				trigger_error("Unknown database type {$dbType} is being requested for " .
									 "construction.", E_USER_ERROR);
			}

			// Create the database DSN details from database context config
			if ($dbType === HulkMVC_Factory::SESSION_DB) {
			    // Change the settings to database if using session DB
                $settings = $context->database;
			}

			// cache the DSN
            self::${$dbType} = $settings['driver'] . ':host=' . $settings['host'] . ';dbname=' . $settings['schema'];
		}
        
		$db = null;
        if ($settings['ext'] === 'pdo') {
            $db = new HulkMVC_DO_PDO(self::${$dbType}, $username, $password);  
        }
        else {
            // TODO Set set_ini mysql.trace_mode  for specific driver
            $driverClass = ucfirst($settings['driver']);
            $class = "HulkMVC_DO_{$driverClass}";
            $db = new $class(self::${$dbType}, $username, $password);
        }
        
        return $db; 
	}

	/**
	 * Creates the configuration and locations for the template engine.
	 * 
	 * @param HulkMVC_Context $context The wb application context object.
	 * @param string $template An optional name of a template file to be used in 
	 * instantiating the template object.
	 * @return HulkMVC_Adaptor A template object of a type depending on the context 
	 * configuration.
	 * @access public
	 * @static
	 */
	public static function &createTemplate(HulkMVC_Context $context, $file = '') {

		$settings = $context->template;

		switch ($settings['type']) {

			case 'HulkMVC' :

				$filename = $settings['template'] . $file;
				self::$template = new HulkMVC_AdaptorHulkMVC($filename);

				self::$template->setCacheDir($settings['cache']);

				break;

			case 'Savant2' :

				// If there is no cached copy
				if (!isset(self::$template)) {

					self::$template = new HulkMVC_AdaptorSavant();
				}

				break;

			case 'Savant3' :

				// If there is no cached copy
				if (!isset(self::$template)) {

					self::$template = new HulkMVC_AdaptorSavant();
				}

				break;

			case 'Smarty' :

				// If there is no cached copy
				if (!isset(self::$template)) {

					self::$template = new HulkMVC_AdaptorSmarty();

					self::$template->template_dir = $settings['template'];
					self::$template->compile_dir = $settings['template_c'];
					self::$template->config_dir = $settings['config'];
					self::$template->cache_dir = $settings['cache'];

					// Recompile each template in debug mode
					if ($context->debug) {
						self::$template->force_compile = true;
						self::$template->debugging = false;
						self::$template->debug_tpl = 'debug.tpl';
					}
					else {
						self::$template->caching = true;
						self::$template->compile_check = false;
						self::$template->debugging = false;
					}
				}

				break;
		}

		return self::$template;
	}

	/**
	 * Creates the web application page controller class name.
	 * 
	 * <p>The web application class name <b>MUST</b> conform to the naming
	 * convention in which the page controller is named: 
	 * 	appName_HulkMVC_Controller_pagControllerName
	 * </p>
	 * 
	 * @param HulkMVC_Context $context The wb application context object.
	 * @param string $name The page controller name.
	 * @return object An object which implements the HulkMVC_Controller interface.
	 * @access public
	 * @static
	 */
	public static function &createController(HulkMVC_Context $context, $name) {
		$className = $context->appName . '_Controller_' . $name;
		$class = new $className;
		return $class;
	}

	/**
	 * Clears any objects which have been cached due to a factory function being 
	 * called.  This pertains to the logger and database factory methods.
	 */
	public static function clearCached() {
        self::$logger = null;
        self::$updateDB = null;
        self::$queryDB = null;
        self::$sesisonDB = null;
	}
}

?>
