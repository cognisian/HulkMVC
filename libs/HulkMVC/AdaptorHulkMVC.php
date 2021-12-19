<?php
/* $Id: AdaptorHulkMVC.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * AdaptorHulkMVC.php
 * 
 * <p>The HulkMVC template system.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Response
 * @author Sean Chalmers <seand chalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 * @see http://www.massassi.com/php/articles/template_engines/
 */

/** Bring in the caching system */
require_once('Cache/Lite.php');

/** Bring in the Adaptor interface */
require_once 'HulkMVC/Adaptor.php';

/**
 * HulkMVC_AdaptorHulkMVC
 *  
 * <p>This is the HulkMVC templating system.  It primary goal is to seperate the
 * view from the processing logic according to the MVC architecture.  While Smarty
 * is an excellent templating system it is rather large and also provides an 
 * unnecessary seperation of PHP and HTML.  The link provided is a description of
 * the drawbacks of using a system such as Smarty.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Response
 * @access public
 */
final class HulkMVC_AdaptorHulkMVC implements HulkMVC_Adaptor {

	/**
	 * @var string The directory location of all templates.
	 * @access private
	 */
	private $templateDir;

	/**
	 * @var string The filename of the template.
	 * @access private
	 */
	private $templateFile;

	/**
	 * @var mixed The collection of field names in the template, used as keys to 
	 * the array, and their associated value.  If the value of a key is an object it
	 * should be another template object.
	 * @access private
	 */
	private $fields = array();

	/**
	 * @var object THe caching system
	 * @access private
	 */
	private $cache;

	/**
	 * @var string A hash of the template file and REQUEST_URI to give the
	 * cache file name a unique ID.
	 * @access private
	 */
	private $cacheID;

	/**
	 * @var bool A flag indicating that the template file encapsulated by this 
	 * template has been cached.
	 * @access private
	 */
	private $cached = false;

	/**
	 * @var bool A flag indicating whether the output buffer should have its 
	 * whitespace characters stripped out and the resulting buffer compressed.
	 * @access private
	 */
	private $debug = false;

	/**
	 * Constructs a HulkMVC_Template.
	 * 
	 * @param string $template The filename of the template.
	 * @access private
	 */
	public function __contstruct($template) {
		$this->templateFile = $template;
		$this->cacheID = md5($_SERVER[REQUEST_URI] . $template);
	}

	/**
	 * Sets the value of a template field.  Wrapper for a call to assign.
	 * 
	 * @param string $name The name of the template field.
	 * @param mixed $value The string or object containing the value to assign to 
	 * the template field.
	 */
	public function __set($name, $value) {
		$this->assign($name, $value);
	}

	/**
	 * @see HulkMVC_Adaptor::assign()
	 * @access public
	 */
	public function assign($field, $value) {

		if (is_object($value)) {

			// If this is a HulkMVC sub tmeplate then get its contents
			// else it is an plain ol' object so get its properties
			if ($value instanceof HulkMVC_AdaptorHulkMVC) {
				$this->fields[$field] = $value->fetch();
			}
			else {
				// TODO Throw HulkMVC_Exception_Template
			}
		}
		else {
			$this->fields[$field] = $value;
		}
	}

	/**
	 * @see HulkMVC_Adaptor::display()
	 * @access public
	 */
	public function display($template) {

		// Create a counter to control recursion.  When depth is 0 we
		// are at the top of the
		static $depth = -1;

		$depth++;

		$contents = '';

		// If the root template is cached then get it
		if ($depth === 0 && $this->isCached()) {

			$fp = @fopen($this->cacheFile, 'r');
			$contents = fread($fp, filesize($this->cacheFile));
			fclose($fp);
		}
		else {

			// Start output buffer
			if ($this->debug) {
				ob_start();
			}
			else {
				if ($depth === 0 && function_exists('ob_gzhandler')) {
					ob_start('ob_gzhandler');
				}
				ob_start(array(&$this, 'cleanup'));
			}

			// Loop through all the variables and import them into the local
			// namespace
			foreach ($this->fields as $field => $value) {

				// Check if valid cache and load cache else create
				// If the value is a HulkMVC_Template then recurse into that template
				if ($value instanceof HulkMVC_Template) {
					${$field} = $value->render();
				}
				else {
					// Import variable into local space
					${$field} = $value;
				}
			}

			// Import the template file
			include $this->templateFile;

			// If this is the root of the templates
			if ($depth === 0) {

				// Write the cache
				if ($fp = @fopen($this->cacheFile, 'w')) {
					fwrite($fp, $contents);
					fclose($fp);
				}
				else {
					trigger_error("Failed writing template cache file $this->cacheFile.",
											E_USER_WARNING);
				}

				// Send output buffer to client
				ob_end_flush();
			}
			else {

				// Get the subtemplate contents and return it up to the root template
				$contents = ob_get_contents();
				ob_end_clean();
			}
		}

		return $contents;
	}

	/**
	 * @see HulkMVC_Adaptor::fetch()
	 * @access public
	 */
	public function fetch($template = '') {

		// Create a counter to control recursion.  When depth is 0 we
		// are at the top of the
		static $depth = -1;

		$depth++;

		$contents = '';

		// If the root template is cached then get it
		if ($depth === 0 && $this->isCached()) {

			$fp = @fopen($this->cacheFile, 'r');
			$contents = fread($fp, filesize($this->cacheFile));
			fclose($fp);
		}
		else {

			// Start output buffer
			if ($this->debug) {
				ob_start();
			}
			else {
				if ($depth === 0 && function_exists('ob_gzhandler')) {
					ob_start('ob_gzhandler');
				}
				ob_start(array(&$this, 'cleanup'));
			}

			// Loop through all the variables and import them into the local
			// namespace
			foreach ($this->fields as $field => $value) {

				// Check if valid cache and load cache else create
				// If the value is a HulkMVC_Template then recurse into that template
				if ($value instanceof HulkMVC_Template) {
					${$field} = $value->render();
				}
				else {
					// Import variable into local space
					${$field} = $value;
				}
			}

			// Import the template file
			include $this->templateFile;

			// If this is the root of the templates
			if ($depth === 0) {

				// Write the cache
				if ($fp = @fopen($this->cacheFile, 'w')) {
					fwrite($fp, $contents);
					fclose($fp);
				}
				else {
					trigger_error("Failed writing template cache file $this->cacheFile.",
											E_USER_WARNING);
				}

				// Send output buffer to client
				ob_end_flush();
			}
			else {

				// Get the subtemplate contents and return it up to the root template
				$contents = ob_get_contents();
				ob_end_clean();
			}
		}

		return $contents;
	}

	/**
	 * Determines if the current emplate has been previously cached.
	 * 
	 * @return bool Flag indicating if the template is cached.
	 * @access public
	 */
	public function isCached() {

		// If previously cached
		if ($this->cached) {
			return true;
		}

//		// Get modified time of file, FALSE is returned if problem (ie no file)
//		$modifiedTime = filemtime($this->cache_id);
//		if (!$modifiedTime) {
//			return false;
//		}
//
//		// Check if cache expired, if it has then remove cahced file
//		if (($modifiedTime + $this->expire) < time()) {
//			@unlink($this->cacheID);
//			return false;
//		}
//		else {
//			// Cache result of this function so no need to recheck filemtime()
//			$this->cached = true;
//			return true;
//		}
	}

	/**
	 * @see HulkMVC_Adaptor::setDebug()
	 * @access public
	 */
	public function setDebug($debug) {
		$this->debug = $debug;
	}

	/**
	 * @see HulkMVC_Adaptor::setTemplateDir()
	 * @access public
	 */
	public function setTemplateDir($templateDir) {

		// Append last directory seperator if not present
		if (!strpos($templateDir, '/', strlen($templateDir) - 1)) {
			$cacheDir .= '/';
		}
	}

	/**
	 * @see HulkMVC_Adaptor::setCache()
	 * @access public
	 */
	public function setCache($cacheDir, $cacheTime) {

		// Append last directory seperator if not present
		if (!strpos($cacheDir, '/', strlen($cacheDir) - 1)) {
			$cacheDir .= '/';
		}

		$options = array(
			'cacheDir' => $cacheDir,
			'lifeTime' => $cacheTime,
			'fileNameProtection' => false
		);

		// Create a Cache_Lite object
		$this->cache = new Cache_Lite($options);
	}

	/**
	 * Removes any whitespace characters from the output buffer.
	 * 
	 * @param string $buffer The contentsof the output buffer.
	 */
	public function cleanup($buffer) {
		return strtr($buffer, array('\t' => '', '\n' => '', '\r' => ''));
	}

}

?>
