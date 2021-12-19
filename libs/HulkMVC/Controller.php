<?php
/* $Id: Controller.php 712 2007-12-09 21:16:41Z chalmers $ */
/**
 * Controller.php
 * 
 * <p>Interface class for HulkMVC controllers.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Controller
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 712 $
 */

/**
 * The interface which all web application controllers must implement.
 *  
 * <p>This interface must be implemented by the specific web application that
 * wishes to use the HulkMVC library.  The concrete class is then used in the
 * web application's index.php page or by specific page controllers to process 
 * requests.</p>
 * 
 * <p>The concrete instance of this class can then add any number of 
 * {@link HulkMVC_Filter} that are executed on every user request.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Controller
 * @access public
 */
interface HulkMVC_Controller {

    /**
     * Dispatchs all incoming requests to the proper page controller.
     *
     * @param object $request The request object for the incoming request.  As
     * this is the front controller the request object is created and passed to the
     * page controller.
     * @param object $request The response object for the outgoing response.  As
     * this is the front controller the response object is created and passed to the
     * page controller.
     *  
	 * @param object $request The request object for the incoming request.
	 * @param object $request The response object for the outgoing response.
	 * @access public
	 */
	public function processRequest($request, $response);

}

?>
