<?php
/* $Id: ClassLoader.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * ClassLoader.php
 * 
 * <p>Contains the global ClassLoader and the required interface to implement
 * and register a class loader with the ClassLoader.</p>
 * 
 * @category Infrastructure
 * @package ClassLoader
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @link http://www.php.net/manual/en/language.oop5.autoload.php ServiceLocator 
 * concept.
 */

/** Include exception in case class not found */
require_once 'ClassNotFoundException.php';

/**
 * Interface for specific library class loaders.
 * 
 * <p>To register a specific class loader with ClassLoader, the library must 
 * implement this interface before it can be added to the list of loaders maintained 
 * by ClassLoader.</p>
 * 
 * @access public
 * @category Infrastructure
 * @package ClassLoader
 */
interface IClassLoader {

	/**
	 * Informs ClassLoader that this loader is responsible for loading the given 
	 * class name.
	 * 
	 * @param string $class The class name to load.
	 * @return bool True if this loader can load the class
	 */
	public function canLoadClass($class);

	/**
	 * Gets the path to the source file containing the rquested class.
	 * 
	 * @param string $class The class name to load.
	 * @return string The path and source file name ,relative to the PHP
	 * include_path to load the class.
	 */
	public function getPath($class);

}

/**
 * Loads class files
 * 
 * <p>ClassLoader loads classes using an intelligent naming scheme and include paths.
 * The include paths are defined in the .htaccess file for a web application.  These
 * include directories are the root directories of where classes can be found and 
 * are predetermined.</p>
 * 
 * @access public
 * @category Infrastructure
 * @package ClassLoader
 */
final class ClassLoader {

	/**
	 * @staticvar array List of ClassLoaders.
	 * @access private
	 */
	private static $classloaders = array();

	/**
	 * Adds a new classloader.
	 * 
	 * @param string $key The name to identify the ClassLoader.
	 * @param object $loader IClassLoader object to add.
	 * @access public
	 * @static
	 */
	public static function addLoader($key, IClassLoader $classloader) {
		self::$classloaders[$key] = $classloader;
	}

	/**
	 * Removes a ClassLoader.
	 * 
	 * @param string $key The identifier of the ClassLoader to remove.
	 * @return bool True if ClassLoader was removed, false otherwise.
	 * @access public
	 * @static
	 */
	public static function removeLoader($key) {

		if (self::isLoader($key)) {
			unset(self::$classloaders[$key]);
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Checks if a ClassLoader has been added.
	 * 
	 * @param string $key The identifier of the ClassLoader to check.
	 * @return bool true if the ClassLoader has been added, false otherwise.
	 * @access public
	 * @static
	 */
	public static function isLoader($key) {
		return array_key_exists($key, self::$classloaders);
	}

	/**
	 * Loads the required class by checking all registered class loaders whose
	 * responsibility it is to the the class.
	 * 
	 * @param string $class The class name to loader.
	 * @return bool True if class was successfully loaded, false otherwise.
	 * @access public
	 * @static
	 */
	public static function load($class) {

		// Check which classloader has the responsibility
		foreach (self::$classloaders as $key => $classloader) {

			// If loader is responsible
			if ($classloader->canLoadClass($class)) {

				// Load the class file
				$path = $classloader->getPath($class);

				include_once $path;

				// Check whether class load was successful without invoking __autoload
				// and add it to the class cache
				if (class_exists($class, false)) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Directly loads a requested class, bypassing the installed classloaders.
	 * 
	 * <p>The classname can be specified in 2 formats:  
	 * 	<ul>
	 * 		<li>fully qualified package naming scheme in which the class file is 
	 * 		specified in a dot seperated notion in which the package and subpackage
	 * 		names correpsond to the directory structure:
	 * 		<br />
	 * 		package.subpackage.name => package/subpackage/name.php
	 * 		</li>
	 * 		<li>A PEAR based class naming scheme in which the class name is 
	 * 		specified in an underscore seperated notion in which the underscore 
	 * 		seperated names correpsond to the directory structure:
	 *  		<br />
	 * 		this_class_name => this/class/name.php.
	 * 		</li>
	 * 	</ul>
	 * </p>
	 * 
	 * <p>In the first case the classname is located in a file which is named in 
	 * exactly the same way as the classname, followed by '.php'.  The
	 * package.subpackage is the directory structure, relative to a PHP 
	 * include_path, wih the '.' replaced with a directory seperator.</p>
	 * 
	 * <p>In the second case the classname is located in a file which named in
	 * the same way as the last element of the underscore seperated classname,
	 * followed by '.php'  the preceding elements represents the directory structure
	 * as per the PEAR naming convention.</p>
	 * 
	 * @param string $class The class name in one of the 2 naming schemes.
	 * @return bool True if class was successfully loaded, false otherwise.
	 * @access public
	 * @static
	 */
	public static function loadClass($class) {

		if (NULL === $class || !isset($class)) {
			trigger_error('The class name cannot be NULL', E_USER_ERROR);
			return false;
		}

   		// Seperate the fully qualified class name
   		$className = '';
   		$classFileName = '';
   		$components = array();
   		if (strpos($class, '.') !== false) {
            
   			// Set the directory structure, pulling off last component as the
			// class name to load
			$components = explode('.', $class);
            
			$className = array_pop($components);
			$classFileName = $className;
		}
		else if (strpos($class, '_') !== false) {

			$components = explode('_', $class);

			$className = $class;
			$classFileName = array_pop($components);
		}
		else {
			// Return single named class used by PEAR
			$className = $class;
			$classFileName = $class;
		}

   		// Put it back together
   		$path = implode('/', $components);
   		$classFile = $classFileName . '.php';
   		if (count($components) > 0) {
            $path .= '/' . $classFile;
   		}
        else {
            $path = $classFile;
        }
        
   		// Load class file
		@include_once $path;
        
		// Check whether class load was successful without invoking __autoload
		if (!class_exists($className, false)) {
			return false;
		}

		return true;
   }

}

/**
 * Loads an instance of ClassLoader then loads the requested class.
 * 
 * @param string $class The class name to load.
 */
function __autoload($class) {

	// Load class directly , if it fails then try the direct approach
	if (!ClassLoader::load($class)) {
		if (!ClassLoader::loadClass($class)) {
		    $errorText = "Unable to autoload the requested class: {$class}.";
            eval("class {$class} { " .
                        "public function __construct() { " .
                            "throw new ClassNotFoundException('$errorText');" .
		                "}" .
                    '};'
            );            
		}
	}
}

?>