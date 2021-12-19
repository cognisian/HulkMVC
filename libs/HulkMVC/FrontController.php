<?php
/* $Id: $ */
/**
 * FrontController.php
 *
 * <p>The web application's Front Controller which receives all user requests
 * and dispatches them to the correct page controller.</p>
 *
 * PHP versions 5.
 *
 * @category Framework
 * @package HulkMVC
 * @subpackage Controller
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright Copyright &copy; 2006, Sean Chalmers
 * @license http://www.opensource.org/licenses/gpl-license.php GPLv2
 * @version SVN: $Revision: $
 */

/** Include the HulkMVC_Controller interface */
require_once 'Controller.php';

/** Include the HulkMVC_Context object which brings in the class loader */
require_once 'Context.php';

/**
 * Front Controller for the TheHulk MVC web application.
 *
 * <p>This class implements the {@link HulkMVC_Controller} interface and acts</p>
 * as the entry point for the web application.  This controller is caleld via
 * mod_rewrite rules so that readable URLs are used throughout the application.
 * When a user request is received, the request is filtered and then routed to the
 * proper page controller defined in the web application's {@link HulkMVC_Context}.
 * </p>
 *
 * <p>The single instance of the FrontController can have any number of
 * {@link InterceptingFilters} that are executed on every user request prior to the
 * request being routed to the proper page controller.  If any of the filters fail
 * then the requested is not routed to the requested page controller and procesing
 * is stopped.</p>
 *
 * <p>The single instance of the FrontController is also the final point of error
 * handling.  The class will instantiate the {@link HulkMVC_ErrorHandler} object
 * which will be the target to receive PHP errors via the
 * <i>set_error_handler()</i>, PEAR errors via the <i>PEAR::setErrorHandling()</i>
 * and PHP exception via <i>set_exception_handler()</i>.</p>
 *
 * @category Framework
 * @package HulkMVC
 * @subpackage Controller
 * @access public
 */
class HulkMVC_FrontController implements HulkMVC_Controller{

    /**
     * @var object The web application context for which this is a controller.
     * @access private
     */
    private $context;

    /**
     * @var object The filter manager class which controls the addition, removal and
     * execution of the chain of filters.
     * @access private
     */
    private $filterManager;

    /**
     * Constructs the bootstrapping object.
     *
     * @param string $siteName The name of the site this controller is registered for.
     * @param bool $debug A flag indicating whether this site should be in debug
     * mode.  It is passed to the {@link Context} object and determines how the
     * context cache is read.  Default value is false
     * 
     * @access public
     */
    public function __construct($siteName, $debug = false) {

        // Create the web application context
        $this->context = new HulkMVC_Context($siteName, $debug);

        // Create filter manager and add any filters for this controller
        $this->filterManager = new HulkMVC_FilterManager();

        $filters = $this->context->front_controller['filters'];
        foreach ($filters as $filterName) {
            $filter = new $filterName;
            $this->filterManager->addFilter($filter);
        }

    }

    /**
     * Applies any defined {@link HulkMVC_Filter}s and dispatches the request to
     * the appropriate page controller.
     * 
     * @access public
     */
    public function process() {

        // Parse the request and create the response object
        $request = new HulkMVC_Request($this->context);
        $response = new HulkMVC_Response($this->context);

        // TODO Apply filters

        // Dispatch to page controller
        $this->processRequest($request, $response);
    }

    /**
     * @see Controller->processRequest()
     */
    public function processRequest($request, $response) {

        // Get and execute the corresponding controller
        $controller = $request->getController();

        $controller->processRequest($request, $response);
    }

}

?>
