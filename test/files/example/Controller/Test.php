<?php
/**
 * A test Controller used to test the controller as well as the Classloader.
 */
require_once 'HulkMVC/Controller.php';
  
class example_Controller_Test implements HulkMVC_Controller {
    
    public $test = 'Test';
    
    public function processRequest($request, $response) {
        echo $this;
    }
    
    public function __toString() {
        return "This is a test";
    }
    
}
?>