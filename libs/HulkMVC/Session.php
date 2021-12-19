<?php
/**
 * Session.class.php
 * 
 * <p>Class definition of the HulkMVC Session.</p>
 * 
 * <p>Class loading for the different conrete session handlers is taken take of the
 * <i>__autoload</i> and <i>ClassLoader</i> initiated via 
 * {@link HulkMVC_FrontController}.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Session
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/lgpl-license.php  LGPL License 2.1
 * @version SVN: $Revision: 707 $
 */

/**
 * PHP session handling.
 * 
 * <p>Uses the abstract session handler allowing the application to use 
 * databases or files transparently.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Session
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/lgpl-license.php  LGPL License 2.1
 * @version SVN: $Revision: 707 $
 */
class HulkMVC_Session {

	/**
	 * @staticvar object Singleton instance of a SessionHandler.
	 * @access private
	 */
	private static $instance;

	/**
	 * @var object Instance of the concrete implementation of 
	 * {@link HulkMVC_SessionHandler_Base}.
	 * @access private
	 */
	private $sessionHandler;

	/**
	 * @var mixed A reference to the global session data.
	 * @access private
	 */
	private $sessionData;

	/**
	 * Constructs a concrete instance of {@link HulkMVC_SessionHandler_Base}.
	 * 
	 * @param object The {@link HulkMVC_Context} object.
	 * @access public
	 * @static
	 */
	public static function create(HulkMVC_Context $context) {

		// Check to make sure the singleton hasn't been previously created
		// else create it
		if (!isset(self::$instance)) {

			// Create the session handler and associate global session
			// array with this object
			self::$instance = new HulkMVC_Session();
		}

	   return self::$instance;
	}

	/**
	 * Constructs a HulkMVC_Session.
	 * 
	 * <p>The HulkMVC_Session constructor is nade private to disable client apps
	 * from instantianting their own copies.  This is a Singleton pattern and an
	 * instance of HulkMVC_Session must be obtained from the instance method.</p>
	 * 
	 * @param object $context  An instance of the web application's 
	 * {@link HulkMVC_Context}.
	 * @access private
	 */
	private function __construct(HulkMVC_Context $context) {

		// Set a reference to superglobal so client can access data via a class
		// property
		$this->sessionData =& $_SESSION;

		// Get the configuration settings
		$settings = $context->session;

		// Instantiate the concrete session handler
		if ('DB' === $settings['handler']) {
			$this->sessionHandler = new HulkMVC_SessionHandler_DB($context);
		}
		else if ('file' === $settings['handler']) {
			$this->sessionHandler = new HulkMVC_SessionHandler_File($context);
		}

		// Set the session handler
		session_set_save_handler(array(&self::$instance->sessionHandler, 'open'),
													array(&self::$instance->sessionHandler, 'close'),
													array(&self::$instance->sessionHandler, 'read'),
													array(&self::$instance->sessionHandler, 'write'),
													array(&self::$instance->sessionHandler, 'destroy'),
													array(&self::$instance->sessionHandler, 'clean'));
	}

	/**
	 * Starts a session.
	 * 
	 * @access public
	 */
	public function start() {
		session_start();
	}

	/**
	 * Remove any session data or cookies.
	 * 
	 * @access public
	 */
	public function destroy() {
		session_unset();
		session_destroy();
	}

	/**
	 * Gets the specific data stored in the PHP session.
	 *
	 * @param string $key The key of the data to retrieve from the session.
	 * @return mixed The session saved data.
	 * @access public
	 */
	public function __get($key) {
		return $_SESSION[$key];
	}

	/**
	 * Sets the data to be stored in the PHP session.
	 *
	 * @param string $key The key of the data to store in the session.
	 * @param mixed $vvalue The value to store in the session.
	 * @access public
	 */
	public function __set($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Prevent users from cloning the HulkMVC_Session.
	 * 
	 * @access public
	 */ 
	public function __clone() {
		trigger_error('Unable to clone session.', E_USER_ERROR);
	}

	/**
	 * When this object is destroyed it calls the session handler to save the
	 * session data.
	 * 
	 * @access public
	 */
	public function __destruct() {
		session_write_close();
	}

}

?>
