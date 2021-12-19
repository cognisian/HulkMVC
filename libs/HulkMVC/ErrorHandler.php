<?php
/* $Id: ErrorHandler.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * ErrorHandler.php
 * 
 * <p>Contains the dfinition for the different error handlers.</p>
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

/** Bring in the HulkMVC factory to load logger */
require_once 'Factory.php';

/** Bring in the exception Base class */
require_once 'Exception.php';

/**
 * HulkMVC_ErrorHandler
 *  
 * <p>Contains the dfinition for the different error handlers.  All PHP errors 
 * raised via <i>trigger_error()</i> function or exceptions and any PEAR
 * errors raised are handled by this class.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @access public
 */
class HulkMVC_ErrorHandler {

	/**
	 * @var object The PEAR log object
	 * @access private
	 */
	private $logger;

	/**
	 * Constructs a HulkMVC_ErrorHandler.
	 * 
	 * <p>If the web application is in debug mode then the handlers will not be
	 * set allowing PHP or Xdebug extensions handle the errors.  If the handlers
	 * are set then they are logged using the PEAR log object as set in the web
	 * application's configuration context.</p>
	 * 
	 * @param HulkMVC_Context $context The web application's configuration
	 * context.
	 */
	public function __construct(HulkMVC_Context $context) {

		// Set the error handlers
		if (!$context->debug) {

			$this->logger =& HulkMVC_Factory::createLogger($context);

			set_error_handler(array(&$this, 'handlePHPError'), error_reporting());
			set_exception_handler(array(&$this, 'handlePHPException'));
			PEAR::setErrorHandling(PEAR_ERROR_CALLBACK,
												  array(&$this, 'handlePEARError'));
		}
	}

	/**
	 * Handles PHP native errors.
	 * 
	 * @access public.
	 * @param int $err_code The PHP error code issued via raise_error().
	 * @param string $err_msg The error message to log.
	 * @param string $err_file The name of the PHP file in which the error was
	 * raised.
	 * @param int $err_code The line number in $err_file in which the error was 
	 * raised.
	 * @access public
	 */
	public function handlePHPError($errorCode, $errorMsg, $errorFile, $errorLine) {

		// If reporting is turned on
		$rptlevel = error_reporting();
		if ($rptlevel > 0) {

			// Set the PEAR log level depending in PHP err level
			switch ($errorCode) {

				case E_ERROR :
				case E_USER_ERROR :
					$priority = PEAR_LOG_ERR;
					break;

				case E_WARNING :
				case E_USER_WARNING :
					$priority = PEAR_LOG_WARNING;
					break;

				case E_NOTICE :
				case E_USER_NOTICE :
					$priority = PEAR_LOG_NOTICE;
					break;

				default:
					$priority = PEAR_LOG_INFO;
					break;
			}

			// Log error
			$this->logger->log(' (' . $errorFile . ' at line ' . $errorLine . ') ' .
												$errorMsg, $priority);

			if (E_ERROR === $errorCode || E_USER_ERROR === $errorCode) {
				die('An error occured check the log file for further details.');
			}
		}
	}

	/**
	 * Handles exceptions thrown from PHP code.
	 * 
	 * @param object $exception The exception that was thrown.
	 * @access public
	 */
	public function handlePHPException(Exception $exception) {

		$message = '';

		if ($exception instanceof HulkMVC_Exception) {
			$message = $exception->__toString();
		}
		else {
			$message = "Exception: [Error Code = {$exception->getCode()}]: " .
									"{$exception->getMessage()}\n";
			$message .= $this->getTraceAsString();
		}

		// Log message
		$this->logger->log($message);

		die('Exception was thrown.');
	}

	/**
	 * Handles PEAR errors.
	 * 
	 * @param object $error The PEAR_Error object.
	 * @access public
	 */
	public function handlePEARError($error) {

		$message = '';

		$backtrace = $error->getBacktrace();

		// If file and line numbers are given
		if (!empty($backtrace['file'])) {

			$message .= ' (' . $backtrace['file'];

			if (!empty($backtrace['line'])) {
				$message .= ' at line ' . $backtrace['line'];
			}
			$message .= ') ';
		}

		$message .= $error->getMessage();

		// Log message
		$this->logger->log($message, $error->getCode());
	}

}

?>
