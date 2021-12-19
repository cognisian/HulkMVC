<?php
/* $Id: Session.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Session.php
 * 
 * <p>Implements the {@link HulkMVC_Filter} interface.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Filter
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * HulkMVC_Filter_Session
 *  
 * <p>Implements the {@link HulkMVC_Filter} interface to allow the controller
 * executing this filter to start and validate sessions.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Filter
 * @access public
 */
class HulkMVC_Filter_Session  implements HulkMVC_Filter{

	/**
	 * Constructs a HulkMVC_Filter_Session
	 */
	public function __construct() {

	}

	/**
	 * @see execute(HulkMVC_Request $request)
	 */
	public function execute(HulkMVC_Request $request) {

	}

}

?>
