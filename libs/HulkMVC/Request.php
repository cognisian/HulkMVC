<?php
/* $Id: Request.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Request.php
 * 
 * <p>Class declaration of a helper class which parses user requests.</p>
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Response
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/lgpl-license.php  LGPL License 2.1
 * @version SVN: $Revision: 707 $
 */

/**
 * Request parameter parsing helper class.
 *  
 * <p>This class parses incoming GET and POST requests extracting the data
 * and making accessible to other components of the HulkMVC application.</p>
 * 
 * @package HulkMVC
 * @package HulkMVC
 * @subpackage Response
 * @access public
 */
final class HulkMVC_Request {

	/**
	 * @var object The web application context.
	 * @access private
	 */
	private $context;

	/**
	 * @var mixed The set of parsed values from the $_GET and $_POST superglobals
	 * @access private
	 */
	private $requestValues = array();

	/**
	 * Constructs the request helper object.
	 * 
	 * <p>Parses the GET and POST superglobal arrays for the incoming user
	 * request.  The data is santized and checked prior to populating this object
	 * with their values.</p>
	 * 
	 * <p>Uses variable variables in which the name of the variable is an array
	 * key of the GET array and its value is the assocaited value from the GET
	 * array</p>
	 * 
	 * @param object $context The web application's {@link HulkMVC_Context} object.
	 * @access public
	 */
	public function __construct(HulkMVC_Context &$context) {

		if (filter_has_var(INPUT_SERVER, 'REQUEST_METHOD')) {
          $this->requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD',
                  FILTER_SANITIZE_STRING);
        }

        // Get the HTTP payload
        $data = file_get_contents('php://input');

        // Decode and strip slashes if necessary
        if (!empty($data)) {
            $data = urldecode($data);
            if (get_magic_quotes_gpc()) {
                $this->payload = stripslashes($data);
            }
        }

        // Split GET/POST query params into object properties
        if ($this->requestMethod === 'GET') {
            $queryParams = filter_input_array(INPUT_GET);
        }
        else if ($this->requestMethod === 'POST'){
            $queryParams = filter_input_array(INPUT_POST);
        }

        if (!empty($queryParams) && is_array($queryParams)) {
            extract($queryParams, EXTR_SKIP);
        }

        $this->request =& $_SERVER;
	}

	/**
	 * Factory method to get the appropriate controller based on the request 
	 * parameters.
	 * 
	 * @return object A concrete implementation of {@link HulkMVC_Controller} 
	 * which is responsible for proessing the incoming request.
	 * @access public 
	 */
	public function getController() {

		// Get the user requested page
		$name = $this->requestValues['page'];
		$controller = HulkMVC_Factory::createController($this->context, $name);

		return $controller;
	}

	 /**
     * Gets the value of the named HTTP request header directive or HTTP payload.
     *
     * <p>The HTTP payload data assopciated with the request can be retrieved using
     * either 'body' or 'payload' as field names.</p>
     *
     * <p>The named header directive is NOT case-sensitive and it should correspond
     * to the name defined in the HTTP/1.1 specification.  The function will also
     * allow the use of the header directive names used by the _SERVER supergloabal
     * array.<p>
     *
     * <p>The function will convert the header anme to upper case first.  If the
     * header name is not prefixed with HTTP_ then it will be prepended to the
     * header name.  If the header name is Content-Type or Content-Length then the
     * HTTP_ prefix will be ommitted as is the case with the corresponding keys of
     * the _SERVER super global.  All dashes are then converted to underscores.  The
     * resulting name will then be used as the associative key to access the
     * _SERVER array.</p>
     *
     * @param string $field The named HTTP header directive value or the HTTP
     * request payload to retrieve.
     * @return string The value of the named HTTP header directive.  Null if the
     * named header directive does not exist.
     */
	public function __get($name) {
	 
		// Get HTTP request method
        if ($field === 'requestMethod' || $field === 'method') {
            return $this->requestMethod;
        }

        // Get HTTP payload data
        if ($field === 'body' || $field === 'payload') {
            return $this->payload;
        }

        // Get HTTTP header values
        $index = strtoupper($field);

        // Check if using _SERVER key name as request header
        if (strpos($index, 'HTTP_') === 0
                && array_key_exists($index, $this->request)) {

            return $this->request[$index];
        }

        $index = str_replace('-', '_', $index);

        // Given a HTTP request header directive, convert to _SERVER format
        if (strpos($index, 'CONTENT_' !== 0) && strpos($index, 'REQUEST_' !== 0)) {
            $index = 'HTTP_' . $index;
        }

        if (array_key_exists($index, $this->request)) {
            return $this->request[$index];
        }

        return null;
	}

}

?>
