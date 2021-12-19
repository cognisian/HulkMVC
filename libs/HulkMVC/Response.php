<?php
/* $Id: Response.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Response.php
 * 
 * <p>Implements an Facade to provide a high level unified interface to different 
 * templating systems.</p>
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
 */

/**
 * HulkMVC_Response
 *  
 * <p>Provides an high level interface to the different possible tempalting engines
 * as well as providing a response stream.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Response
 * @access public
 */
final class HulkMVC_Response {

	/**
	 * @var object The web application context.
	 * @access private
	 */
	private $context;

	/**
	 * Constructs a HulkMVC_Response
	 * 
	 * @access public
	 */
	public function __construct(HulkMVC_Context &$context) {
		$this->context =& $context;
	}

	/**
	 * Loads the given template name.
	 * 
	 * @param string $template The filename of the template to load.  Only the 
	 * file name is given as the path to the template is set is the 
	 * {@link HulkMVC_Context} object.
	 * @access public
	 */
	public function loadTemplate($template) {
	}

	/**
	 * Associates a defined field in the template with a value.
	 * 
	 * @param string $field The name of the field defined in the template.
	 * @param sting $value The value to give to the field when the template is 
	 * rendered.
	 * @access public
	 */
	public function addValue($field, $value) {

	}

	/**
	 * Adds an HTTP header directive to the output stream.
	 * 
	 * @param string $header The HTTP header name.
	 * @param string $value The value to associate with the HTTP header directive.
	 * @access public
	 */
	public function addHeader($header, $value) {

	}

	/**
	 * Adds the HTTP headers to enable or disable the client's browser from caching
	 * the response in the client's browser's cache.  This is a short hand to
	 * {@link addHeader()} to add several headers to the output stream to
	 * enable or disable the client cache.
	 * 
	 * @param boo $cache A flag indicating whether caching shold be on or off.  If
	 * the flag is <i>true</i> then caching will be enabled on the client's browser.
	 * The default is <i>true</i> and client caching HTTP headers will be sent when
	 * this function is called.
	 * @access public
	 */
	public function cacheResponseAtClient($cache = true) {

		if ($cache) {
			$expireDate = gmdate("D, d M Y H:i:s", time());
			$this->addHeader('Expires',  $expireDate);
		}
		else {
			// Always modified
			header('Expires: 0');

			// Expire the cache into the past
			$expireDate = gmdate('D, d M Y H:i:s', time() - 3600);
			header("Last-Modified: {$expireDate} GMT");

			// HTTP/1.1
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: post-check=0, pre-check=0');

			// HTTP/1.0 no cache
			header('Pragma: no-cache');
		}
	}

	/**
	 * Forwards the client's browser to a new URL.
	 * 
	 * @param string $location The new URL to redirect the client browser.  This is
	 * accomplished using the header directive.
	 * @access public
	 */
	public function forwardRequest($location) {

	}

	/**
	 * Redirects the client's browser to a new URL.
	 * 
	 * @param string $location The new URL to redirect the client browser.  This is
	 * accomplished using the header directive.
	 * @access public
	 */
	public function redirectRequest($location) {
		header("Location: $location");
	}

}

?>
