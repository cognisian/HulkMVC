<?php
/* $Id: Authenticate.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Authenticate.php
 * 
 * <p>Implents the {@link HulkMVC_Filter} interface.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMNVC
 * @subpackage Filter
 * @author chalmers <user email here>
 * @copyright Copyright &copy; 2006, chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * HulkMVC_Filter_Authenticate.
 *  
 * p>Implements the {@link HulkMVC_Filter} interface to allow the controller
 * executing this filter to ensure that that a user is authenticated and authorized
 * to access a page under the control of the executing page controller.</p>
 * 
 * @access public
 * @category Framework
 * @package HulkMVC
 * @subpackage Filter
 */
class HulkMVC_Filter_Authenticate implements HulkMVC_Filter {

	/**
	 * Constructs a HulkMVC_Filter_Authenticate.
	 */
	public function __construct() {

	}

	/**
	 * Executes the filter.
	 */
	public function execute() {

	}

}

?>
