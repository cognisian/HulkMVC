<?php
/* $Id: File.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * File.clas.php
 * 
 * <p>Class implementation the PHP session functions using a file.</p>
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
 * to store its session information in a file defined via {@link HulkMVC_Context}
 * object configuration.</p>
 * 
 * <p>This class cannot be used if the web application has defined <b>strict</b>
 * level of security as defined in the context.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Session
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */
class HulkMVC_SessionHandler_File extends HulkMVC_SessionHandler_Base {

	/**
	 * @var string The name of the session file.
	 * @access private 
	 */
	private $sessionFile;

	/**
	 * @var string The file handle to the session file.
	 * @access private
	 */
	private $fileHandle;

	/**
	 * Constructs a SessionHandler_DB.
	 * 
	 * @param object $context {@link HulkMVC_Context} object.
	 * @access public
	 */
	public function __construct(HulkMVC_Context $context) {

		parent::__construct($context);

		$settings = $context->session;
		$this->sessionFile = $settings['filename'];
		session_save_path($settings['directory']);

		$this->fileHandle = null;

		$this->logger->log('Constructed SessionHandler_File', PEAR_LOG_DEBUG);
	}

	/**
	 * Verifys that the given session ID is a valid session ID.
	 * 
	 * @param int $sessionID The session ID to verify.
	 * @param bool Flag indicating whether to test whether a valid session
	 * exists and is associated with a client IP address.  Default is true.
	 */
	public function isValidSessionID($sessionID, $strict = true) {

		// Check to make sure that the session ID is alphanumeric
		if(!preg_match('#^[[:alnum:]]+$#', $sessionID)) {
			return false;
		}

		return true;
	}

	/**
	 * Opens the session file.
	 * 
	 * @return bool True if the session file was opened, false 
	 * otherwise.
	 */
	public function open($savePath, $sessionName) {

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

		// Create full filename with the session ID and extension
		$this->sessionFile .= $this->sessionID . '.ssn';

		return true;
	}

	/**
	 * Closes the session file.
	 * 
	 * @return bool True if the database connection was connected, false 
	 * otherwise.
	 */
	public function close() {

		$this->environ->logger->log('Closing session: ' . $this->sessionID,
														PEAR_LOG_DEBUG);

		flock($this->fileHandle, LOCK_UN);
  		fclose($this->fileHandle);

  		$this->fileHandle = null;

		return true;
	}

	/**
	 * Reads session information from the session file.
	 * 
	 * @return string The session data is returned or an empty string if no 
	 * session data.
	 */
	public function read($id) {

		$this->environ->logger->log('Reading session: ' . $this->sessionID
														. ' with data -> ' . '', PEAR_LOG_DEBUG);

		$sessionData = '';

		if ($this->fileHandle = fopen($this->sessionFile, "r+")) {
   			flock($this->fileHandle, LOCK_EX);
   			$sessionData = fread($this->fileHandle, filesize($this->sessionFile));
  		}

		return $sessionData;
	}

	/**
	 * Writes session data to the session file.
	 * 
	 * @return bool True if the sesison data was successfully saved, otherwise
	 * false.
	 */
	public function write($id, $data) {

		$this->environ->logger->log('Writing session: ' . $id . ' with data -> ' .
														$data, PEAR_LOG_INFO);

		$result = false;

		if (!empty($this->fileHandle)) {

   			fseek($this->fileHandle,0);
   			$result = fwrite($this->fileHandle, $data);
  		} 
  		else if ($this->fileHandle = fopen($this->sessionFile, "w")) {
   			flock($this->fileHandle, LOCK_EX);
   			$result = fwrite($this->fileHandle, $data);
  		}

		return $result;
	}

	/**
	 * Removes sessison data and session file.
	 * 
	 * @return bool True if the session information was successfully removed,
	 * otherwise false.
	 */
	public function destroy($id) {

		$this->environ->logger->log('Destroying session: ' . $id,
														PEAR_LOG_DEBUG);

		$result = false;

		$result = unlink($this->sessionFile);

		$this->removeGlobalData();

		return $result;
	}

	/**
	 * Cleans any expired sessions from disk.
	 * 
	 * @return bool True if the expired sessions were successfully removed,
	 * otherwise false.
	 */
	public function clean($max) {
		return true;
	}

}

?>