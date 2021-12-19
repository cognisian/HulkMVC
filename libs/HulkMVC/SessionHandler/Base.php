<?php
/* $Id: Base.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Base.class.php
 * 
 * <p>Class definition of the HulkMVC  base SessionHandler.</p>
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
 * Abstracts the HulkMVC session handling.
 * 
 * <p>Concrete session handlers must extend this class.</p>
 * 
 * <p>Sets the interface and default processing for all concrete session handling 
 * instances.  This allows the {@linkHulkMVC_Session} to create a session
 * regardless of where that session is located (ie database or file).</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Session
 * @access private
 */
abstract class HulkMVC_SessionHandler_Base {

	/**
	 * @var object A <i>PEAR::Log</i> object
	 * @access protected
	 */
	protected $logger;

	/**
	 * @var int A valid session ID
	 * @access protected
	 */
	protected $sessionID;

	/**
	 * @var string A string representing the security handling, should be either
	 * 'strict' or 'permissive'
	 * @access protected
	 */
	protected $level;

	/**
	 * @var int A UNIX timestamp set to when the session should expire.
	 * @access protected
	 */
	protected $timeout;

	/**
	 * @var int A domain path within the web server in which the session cookie is
	 * valid.
	 * @access protected
	 */
	protected $domain;

	/**
	 * Constructs an abstract session handler.
	 * 
	 * @param object $context The {@link HulkMVC_Context} object.
	 * @access protected
	 */
	protected function __construct(HulkMVC_Context $context) {

		// Set the session security
		ini_set('url_rewriter.tags', '');
		ini_set('session.auto_start', 0);
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_cookies', 1);
		ini_set('session.use_only_cookies', 1);
		ini_set('session.cookie_httponly', 1);

		$this->logger =& HulkMVC_Factory::createLogger($context);

		$settings = $context->session;

		$this->level = $settings['security'];
		$this->timeout = $settings['timeout'];
		$this->domain = '/' . $context->appName . '/';
	}

	/**
	 * Prints the object as a string.
	 * 
	 * @return string The string representation of the object.
	 */
	public function __toString() {

		$str = 'Session ' . $this->sessionID;
		$str .= ' with security level ' . $this->level;
		$str .= ' and cookie timeout of ' . $this->timeout;
		$str .= ' for the domain ' . $this->path;

		return $str;
	}

	/**
	 * Generates and sets a new session ID.
	 * 
	 * @return int A new session ID.
	 * @access protected
	 */
	protected function generateSessionID() {

		srand((double)microtime() * 1000000);

		$this->sessionID = md5(rand());

		session_id($this->sessionID);
	}

	/**
	 * Removes all associated data from the $_SESSION and $_COOKIE superglobals.
	 * 
	 * @access protected
	 */
	protected function removeGlobalData() {

		foreach ($_SESSION as $key => $value) {
			unset($_SESSION['key']);
		}

		foreach ($_COOKIE as $key => $value) {
			unset($_COOKIE['key']);
		}
	}

	/**
	 * Verifys that the given session ID is a valid session ID.
	 * 
	 * @param int $sessionID The session ID to verify.
	 * @access protected
	 */
	abstract public function isValidSessionID($sessionID);

	/**
	 * Opens a session.
	 * 
	 * @param string The path of the file to save session.
	 * @param string The name of the session, the default is PHPSESSID.
	 * @return bool True if the session was created, false otherwise.
	 * @access public
	 */
	abstract public function open($path, $sessionName);

	/**
	 * Closes a session.
	 * 
	 * @return bool True if the session was closed, false otherwise.
	 * @access public
	 */
	abstract public function close();

	/**
	 * Reads the session data.
	 * 
	 * @param string The session identifier.
	 * @return string The session data associated with $id, otherwise an empty 
	 * string.
	 * @access public
	 */
	abstract public function read($id);

	/**
	 * Writes session data.
	 * 
	 * @param string $id The session identifier.
	 * @param string $data The session data to associate with $id.
	 * @return bool True if the session data was successfully saved, otherwise 
	 * false.
	 * @access public
	 */
	abstract public function write($id, $data);

	/**
	 * Performs garbage collection of expired sessions.
	 * 
	 * @param string $id The session identifier.
	 * @return bool True if the session was successfully removed, otherwise
	 * false.
	 * @access public
	 */
	abstract public function destroy($id);

}

?>
