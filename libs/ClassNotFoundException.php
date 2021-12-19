<?php
/* $Id: ClassNotFoundException.php 707 2007-12-02 19:07:42Z chalmers $ */
/**
 * ClassNotFound.php
 * 
 * <p>Encapsulates autoloading class exceptions.</p>
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
require_once 'ClassNotFoundException.php';

/**
 * HulkMVC_Exception_ClassNotFound
 *  
 * <p>Class which is thrown if the autoloader is unable to locate any HulkMVC
 * based classes.</p>
 * 
 * @category Framework
 * @package HulkMVC
 * @subpackage Exception
 * @access public
 */
class ClassNotFoundException extends Exception {
    
    /**
     * Override to display full exception details.  This will be used to either log
     * the exception to a log file or as plain text.  Therefore no tags will
     * be present ot markup the details.
     * 
     * @return string The plain string containing the exception details.
     */
    public function __toString() {
        $message .= $this->getTraceAsString();
        return $message;
    }
}
?>