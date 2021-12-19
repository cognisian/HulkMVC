<?php
/* $Id: Adaptor.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * TemplateAdaptor.php
 * 
 * <p>Interface for the Class Adaptor design pattern.  This allows the HulkMVC 
 * framework to use several different templating systems.  This is a unified interface 
 * which is provided to the {@link HulkMVC_Response} client.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Template
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * HulkMVC_TemplateAdaptor
 *  
 * <p>Interface for the Class Adaptor pattern to wrap the underlying templating
 * systems allowable by the HulkMVC framework.  This is an interface as PHP does not
 * allow multiple inheritence.</p>
 * 
 * <p><i>Smarty</i> is one of the templating systems supported and is a very large
 * and complex system, some of its functionality is not exposed through the interface
 * to support the other templating systems:
 * <ul>
 * 	<li>native <i>HulkMVC</i> tmeplates</li>
 * 	<li><i>Savant2</i></li>
 * 	<li><i>Savant3</i></li>
 * </ul>
 * Additionally, some of the features of Savant such as plugins is not supported.
 * </p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Template
 * @access public
 */
interface HulkMVC_TemplateAdaptor {

	/**
	 * Assigns a value to a template field.
	 * 
	 * @param string $field The name of the field defined in a template file.
	 * @param mixed $value The value to assign to the template field.  This can be
	 * a string, an array of values or an object.  If this is an object then it pertains to
	 * the native HulkMVC template system which allows nesting of templates.  If the
	 * context defines Smarty or Savant then this fucntion will throw an exception
	 * @throws {@link HulkMVC_Exception_Template}.  If an object is assigned to a 
	 * field and that the value is not an instance of 
	 * {@link HulkMVC_AdaptorHulkMVC}.
	 */
	public function assign($field, $value);

	/**
	 * Displays an template file with its assigned values.
	 * 
	 * @param string $template The name of the template file to display.  The file 
	 * name should only be the template file name as the template directory should 
	 * already be set and will be prepended to the given template file name.
	 */
	public function display($template);

	/**
	 * Creates the output as a string which is the result of applying the the 
	 * temlate field values.
	 * 
	 * @param string $template The name of the template file to display.  The file 
	 * name should only be the template file name as the template directory should 
	 * already be set and will be prepended to the given template file name.
	 * @return string The output string to be displayed
	 */
	public function fetch($template);

	/**
	 * Determines if the template is cached.
	 * 
	 * @return bool Flag indicating whether the template data is cached.
	 */
	public function isCached();

	/**
	 * Sets a flag indicating that the template should output additional debugging 
	 * information in a new window.
	 * 
	 * @param boo $flag Sets the debug mode of this template.
	 */
	public function setDebug($flag);

	/**
	 * Sets the location of the directory which contains the template files.
	 * 
	 * @param string $templateDir The location of the directory containg the 
	 * template files.
	 */
	public function setTemplateDir($templateDir);

	/**
	 * Sets the caching system properties for a template.
	 * 
	 * @param string $cacheDir The location of the directory containg the 
	 * template file cache.
	 * @param int $cacheTime The amount of time in seconds in which the cache file
	 * for this tempalte is valid.
	 */
	public function setCache($cacheDir, $cacheTime);

}

?>
