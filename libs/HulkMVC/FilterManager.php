<?php
/* $Id: FilterManager.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * FilterManager.class.php
 * 
 * <p>Manages the chain of intercepting filters.</p>
 * 
 * PHP versions 5.
 * 
 * @category Framework
 * @package HulkMVC
 * @package Filter
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: 707 $
 */

/**
 * HulkMC_FilterManager
 *  
 * <p>Maintains and processes a {@link HulkMVC_FilterChain} which consists of a list
 * of {@link HulkMVC_Filter}s.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @package Filter
 * @access protected
 */
class HulkMVC_FilterManager {

	/**
	 * @var mixed The list of filters.
	 * @access private
	 */
	private $filters = array();

	/**
	 * Adds a {@link HulkMVC_Filter} to the list of filters.
	 * 
	 * @param object $filter The {@link HulkMVC_Filter} to add to the list.
	 */
	public function addFilter(HulkMVC_Filter $filter) {
		$this->filters[] = $filter;
	}

	/**
	 * Iterates over the chain of filters and executes each filter in order.
	 * 
	 * @param object $request The incoming request.
	 * @return bool Returns <i>true</i> if the chain of filters was successful, 
	 * otherwise <i>false</i>.  If there is no filters to process then <i>true</i>
	 * is returned.
	 */
	public function processFilters(HulkMVC_Filter &$request) {

		$result = true;

		// Setup chain and get the first filter, if any, and process
		$chain = new HulkMVC_FilterChain($this->filters);
		if ($chain->hasMoreFilters()) {

			$filter =& $chain->next();

			// Process the chain of filters
			if (!$filter->execute($chain, $request)) {
				$result = false;
			}
		}

		return $result;
	}

}

?>
