<?php
/* $Id: Config.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Config.php
 * 
 * <p>Encapsulates the web application context configuration exceptions.</p>
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

/** Bring in the exception Base class manually as context may not load */
require_once 'HulkMVC/Exception.php';

/**
 * HulkMVC_Exception_Config
 *  
 * <p>Class which is thrown if there are any exceptions in configuring the web
 * application's context via the {@link HulkMVC_Context} object.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @access public
 */
class HulkMVC_Exception_Config extends HulkMVC_Exception {

	const INVALID_PHP_VER_FOR_APP = 1;
	const INVALID_CONFIG_FILE = 2;
	const MISSING_CONFIG_FILE = 3;
	const SAFE_MODE_OFF = 4;
	const INVALID_DATABASE_HANDLER = 5;
	const INVALID_SESSION_HANDLER = 6;
	const INVALID_SESSION_SEC_LEVEL = 7;
	const INVALID_SESSION_TIMEOUT = 8;
	const INVALID_TEMPLATE_LOCATON = 9;
	const INVALID_TEMPLATE_CACHE_LOCATON = 10;

}

?>
