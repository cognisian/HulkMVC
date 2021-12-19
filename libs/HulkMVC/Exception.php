<?php
/* $Id: Exception.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Exception.php
 * 
 * <p>Base class from which all HulkMVC exceptions are derived.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * HulkMVC_Exception
 *  
 * <p>Base class from which all HulkMVC exceptions are derived.  The class is
 * abstract and overrides the __toString method for logging the exception.  This
 * class also provides a function to display the exception using HTML markup</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @access public
 * @abstract
 */
abstract class HulkMVC_Exception extends Exception {

	/**
	 * Override to display full exception details.  This will be used to either log
	 * the exception to a log file or as plain text.  Therefore no tags will
	 * be present ot markup the details.
	 * 
	 * @return string The plain string containing the exception details.
	 */
	public function __toString() {

		$message = __CLASS__ . ": [Error Code = {$this->code}]: {$this->message}\n";
		$message .= $this->getTraceAsString();

		return $message;
	}

	/**
	 * Used to display the full exception details with markup.  This will allow the
	 * exception to shown with HTML markup which can be shown in a debug
	 * terminal.
	 * 
	 * @return string The string containing the exception details with HTML markup.
	 */
	public function fullDisplay() {

		$exCode = htmlentities($this->code, ENT_QUOTES);
		$exMessage = htmlentities($this->message, ENT_QUOTES);
		$exTrace = htmlentities($this->getTraceAsString, ENT_QUOTES);

		$message = "<div class=\"exception\">";
		$message .= "<h1 class=\"exception_name\">" . __CLASS__ . "</h1>";
		$message = "<br />";
		$message .= "<h2 class=\"exception_code\">Error Code: {$exCode}</h2>";
		$message = "<br />";
		$message .= "<p class=\"exception_msg\">{$exMessage}</p>";
		$message = "<br />";
		$message .= "<h2 class=\"exception_code\">Stack Trace</h2>";
		$message = "<br />";
		$message .= "<p class=\"exception_trace\">{$exMessage}</p>";
		$message .= "</div>";
        
		return $message;
	}

}

?>
