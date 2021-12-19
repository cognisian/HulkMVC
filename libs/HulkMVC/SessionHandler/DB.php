<?php
/* $Id: DB.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * DB.class.php
 * 
 * <p>Class implementation the PHP session functions using a database.</p>
 *
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Session
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * Concrete class extending the {@link HulkMVC_SessionHandler_Base} class.
 * 
 * <p>Extends the {@link HulkMVC_SessionHandler_Base} class to allow an application
 * to store its session information in a database defined via {@link HulkMVC_Context}
 * object configuration.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Session
 * @access private
 */
class HulkMVC_SessionHandler_DB extends HulkMVC_SessionHandler_Base {

	/**
	 * @var object An instance of <i>PEAR::MDB2</i> with update privledges.
	 * @access private
	 */
	private $dbConn;

	/**
	 * Constructs a SessionHandler_DB.
	 * 
	 * @param object $context {@link HulkMVC_Context} object.
	 * @access public
	 */
	public function __construct(HulkMVC_Context $context) {

		parent::__construct($context);

		$settings = $context->session;
		$this->location = $settings['name'];

		$this->dbConn = HulkMVC_Factory::createDB($context,
													HulkMVC_Factory::SESSION_DB);

		$this->logger->log('Constructed SessionHandler_DB', PEAR_LOG_DEBUG);
	}

	/**
	 * Verifys that the given session ID is a valid session ID.
	 * 
	 * @param int $sessionID The session ID to verify.
	 * @access public
	 */
	public function isValidSessionID($sessionID) {

		// Check to make sure that the session ID is alphanumeric
		if(!preg_match('#^[[:alnum:]]+$#', $sessionID)) {
			return false;
		}

		// Check to see if session ID is in database
		if ('strict' === $this->level) {

			$id = $this->dbConn->escape($sessionID);
			$addr = $this->dbConn->escape($_SERVER['REMOTE_ADDR']);

			$sql = "SELECT id FROM $this->location WHERE id='$sessionID'";
			$sql .= " AND address=INET_ATON('$addr')";

			$result =& $this->dbConn->query($sql);
			if (PEAR::isError($result)) {
				trigger_error($result->getMessage(), E_USER_WARNING);
				return false;
			}

			// Should only be one row from client IP address
			if ($result->numRows() !== 1) {
				trigger_error('Unable to locate the correct session for the client '
										. 'IP address', E_USER_WARNING);
				return false;
			}
		}

		return true;
	}

	/**
	 * Opens a connection to session DB.
	 * 
	 * @return bool True if the database connection was connected, false 
	 * otherwise.
	 * @access public
	 */
	public function open($savePath, $sessionName) {

		// Remove any stale sessions
		$this->clean();

		// Check for session
		session_get_cookie_params();
		$currSessionID = session_id();

		// If current session is empty then starting a new one
		// else validate the session and open existing session
		if (empty($currSessionID)) {

			$this->generateSessionID();

			// Remove leading www. from host name
			$host = preg_replace('/^[Ww][Ww][Ww]\./', '',
							preg_replace('/:[0-9]*$/', '', $_SERVER['HTTP_HOST']));

			// Save session cookie
			$result = setcookie(session_name(),
											$this->sessionID,
											time() + $this->timeout,
											$this->domain, $host,
											false);

			$this->logger->log('Opening new session: ' . $this->sessionID
											. ' named ' . session_name(),	PEAR_LOG_DEBUG);
		}
		else {

			if ($this->isValidSessionID($currSessionID)) {

				// Set this session's ID
				$this->sessionID = $currSessionID;

				$this->logger->log('Opening previous session: ' . $this->sessionID
												. ' named ' . session_name(),	PEAR_LOG_DEBUG);
			}
		}

		return true;
	}

	/**
	 * Closes a connection to session DB.
	 * 
	 * @return bool True if the database connection was closed, false 
	 * otherwise.
	 * @access public
	 */
	public function close() {

		unset($this->sessionID);

		$this->logger->log('Closing session: ' . session_name(), PEAR_LOG_DEBUG);

		return true;
	}

	/**
	 * Reads session data from the session DB.
	 * 
	 * @return string The session data is returned or an empty string if no 
	 * session data.
	 * @access public
	 */
	public function read($id) {

		// Open DB connection
		$conn = $this->dbConn->connect();
		if (PEAR::isError($conn)) {
			$this->logger->log('Unable to connect to session database for read.',
											PEAR_LOG_ERR);
			$this->logger->log($conn->getMessage(), PEAR_LOG_ERR);
			return '';
		}

		$id = $this->dbConn->escape($id);

		// Create stored proc variables
		$ipAddr = '0';
		if ('strict' === $this->level) {
			$ipAddr = $_SERVER['REMOTE_ADDR'];
		}
		$spData = array($id, $ipAddr, '@sessionData');

		// Call stored procedure (OUT param in result)
		$result = $conn->function->executeStoredProc('sp_session_data_read',
																						$spData);
		if (PEAR::isError($result)) {
			trigger_error($result->getMessage(), E_USER_WARNING);
			return '';
		}

		// Get session data
		$sessionData = $result->fetchRow();

		$this->logger->log('Reading session: ' . $this->sessionID
										. ' with data -> ' . $sessionData['data'],
										PEAR_LOG_DEBUG);

		// Close DB connection
		$result->free();
		$this->dbConn->disconnect(false);

		return $sessionData['data'];
	}

	/**
	 * Writes session data to the session DB.
	 * 
	 * @return bool True if the sesison data was successfully saved, otherwise
	 * false.
	 * @access public
	 */
	public function write($id, $data) {

		// Open DB connection
		$conn = $this->dbConn->connect();
		if (PEAR::isError($conn)) {
			$this->logger->log('Unable to connect to session database for write.',
											PEAR_LOG_ERR);
			$this->logger->log($conn->getMessage(), PEAR_LOG_ERR);
			return false;
		}

		$id = $this->dbConn->escape($id);
		$data = $this->dbConn->escape($data);

		// Create stored proc variables
		$ipAddr = '0';
		if ('strict' === $this->level) {
			$ipAddr = $_SERVER['REMOTE_ADDR'];
		}
		$spData = array($id, $ipAddr, $data);

		// Call stored procedure (OUT param in result)
		$result = $conn->function->executeStoredProc('sp_session_data_write',
																						$spData);
		if (PEAR::isError($result)) {
			trigger_error($result->getMessage(), E_USER_WARNING);
			return false;
		}

		$this->logger->log('Writing session: ' . $id . ' with data -> ' . $data,
										PEAR_LOG_DEBUG);

		// Close DB connection
		$this->dbConn->disconnect(false);

		return true;
	}

	/**
	 * Removes a session from the session DB.
	 * 
	 * @return bool True if the session information was successfully removed,
	 * otherwise false.
	 * @access public
	 */
	public function destroy($id) {

		// Open DB connection
		$conn = $this->dbConn->connect();
		if (PEAR::isError($conn)) {
			$this->logger->log('Unable to connect to session database for destroy.',
											PEAR_LOG_ERR);
			$this->logger->log($conn->getMessage(), PEAR_LOG_ERR);
			return false;
		}

		$id = $this->dbConn->escape($id);

		// Call stored procedure
		$spData = array($id);
		$result = $conn->function->executeStoredProc('sp_session_data_delete',
																						$spData);
		if (PEAR::isError($result)) {
			trigger_error($result->getMessage(), E_USER_WARNING);
			return false;
		}

		// Remove cookies, if necessary
		if ('strict' === $this->level) {

			$host = preg_replace('/^[Ww][Ww][Ww]\./', '',
							preg_replace('/:[0-9]*$/', '', $_SERVER['HTTP_HOST']));

			$result = setcookie(session_name(),
											"",
											1,
											$this->path,
											$host,
											false);
		}

		$this->logger->log('Destroying session: ' . $id, PEAR_LOG_DEBUG);

		// Close DB connection
		$this->dbConn->disconnect(false);

		$this->removeGlobalData();

		return true;
	}

	/**
	 * Cleans any expired sessions from disk.
	 * 
	 * @return bool True if the expired sessions were successfully removed,
	 * otherwise false.
	 * @access public
	 */
	public function clean($max) {

		// Open DB connection
		$conn = $this->dbConn->connect();
		if (PEAR::isError($conn)) {
			$this->logger->log('Unable to connect to session database for clean.',
											PEAR_LOG_ERR);
			$this->logger->log($conn->getMessage(), PEAR_LOG_ERR);
			return false;
		}

		$max = $this->dbConn->escape($max);

		// Call stored procedure
		$spData = array($max);
		$result = $conn->function->executeStoredProc('sp_session_data_cleanup',
																						$spData);
		if (PEAR::isError($result)) {
			trigger_error($result->getMessage(), E_USER_WARNING);
			return false;
		}

		$this->logger->log('Cleaning stale sessions', PEAR_LOG_DEBUG);

		// Close DB connection
		$this->dbConn->disconnect(false);

		return true;
	}

}

?>
