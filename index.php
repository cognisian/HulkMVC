<?php

/**
 * index.php
 *
 * @author Sean Chalmers <seandchalmers@yahoo.ca>
 * @copyright 2006 Sean Chalmers <seandchalmers@yahoo.ca>
 * @license http://www.opensource.org/licenses/gpl-license.php GPL v2
 * @package Framework
 */
require_once 'HulkMVC/FrontController.php';

$controller = new HulkMVC_FrontController('example');

$controller->process();
?>
