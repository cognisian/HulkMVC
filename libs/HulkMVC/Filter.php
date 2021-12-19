<?php
/* $Id: Filter.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * Filter.php
 * 
 * <p>An interface for an intercepting filter used by {@link HulkMVC_FilterManager} 
 * and any {@link HulkMVC_Controller}s.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Filter
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * HulkMVC_Filter
 *  
 * <p>The interface for all intercepting filters.  The concrete implementation
 * of this class should be focused on a single task to filter the incoming request
 * prior to the request being dispatched to the appropriate controller.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Filter
 * @access public
 */
interface HulkMVC_Filter {

	/**
	 * Executes the filter
	 * 
	 * @param object $chain An reference to the {@link HulkMVC_FilterChain} that the
	 * filter should call to process the next filter if there are any more and if this
	 * filter successfully completed.
	 * @param object $request An reference to a {@link HulkMVC_Request} which 
	 * encapsulates the incoming request.
	 * @return bool Returns <i>true</i> if the filter successfully completed.  A 
	 * value of <i>false</i> will stop processing of any remaining filters.  These
	 * results should be passed back up the chain to the originator.
	 */
	public function execute(HulkMVC_FilterChain &$chain, HulkMVC_Request &$request);

}

?>
