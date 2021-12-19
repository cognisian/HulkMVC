<?php
/* $Id: DO.php 714 2007-12-17 03:46:28Z chalmers $ */
/**
 * DO.php
 * 
 * <p>Encapsulates the web application Data Object exceptions.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 714 $
 */

/** Bring in the exception Base class manually as context may not load */
require_once 'HulkMVC/Exception.php';

/**
 * HulkMVC_Exception_DO
 *  
 * <p>Class which is thrown if there are any exceptions in accessing the database
 * via the PDO or native {@link HulkMVC_DO} Data Object.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @access public
 */
class HulkMVC_Exception_DO extends HulkMVC_Exception {

}

?>
