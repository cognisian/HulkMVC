<?php
/* $Id: FilterChain.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * FilterChain.php
 * 
 * <p>Implementation of a chain of filters to be processed.</p>
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
 * HulkMVC_FilterChain
 *  
 * <p>A queue of filters which are to be processed in order.   As each filter is 
 * retrieved via the call to {@link next()} it is removed from the list of filters.
 * </p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Filter
 * @access public
 */
class HulkMVC_FilterChain {

	/**
	 * @var mixed The list of filters in the chain.
	 * @access private
	 */
	private $chain = array();

	/**
	 * @var int The number of filters in the chain.  Used to determine if there are
	 * more filters to process.
	 * @access private
	 */
	private $filterCount = 0;

	/**
	 * Constructs a chain of filters from an array of {@link HulkMVC_Filter} objects.
	 * 
	 * @param mixed $filters The array of {@link HulkMVC_Filter} objects.
	 */
	public function __contruct($filters) {
		$this->chain = $filters;
		$this->filterCount = count($filters);
	}

	/**
	 * Removes the next {@link HulkMVC_Filter} to be processed from the list of 
	 * filters.
	 * 
	 * @return object A reference to the next {@link HulkMVC_Filter} to be 
	 * processed.
	 */
	public function &next() {
		$this->filterCount--;
		return array_shift($this->chain);
	}

	/**
	 * Determines if there are more filters to be processed in the chain.
	 * 
	 * @return bool A flag indicating if there are more filters to be processed.
	 */
	public function hasMoreFilters() {
		return $this->filterCount > 0 ? true : false;
	}

}

?>
