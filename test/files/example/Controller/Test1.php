<?php
class Test1 {
    public $test = 'Test1';
    public function testFunc() {
        echo $this;
    }
    public function __toString() {
        return "This is a test";
    }
}
?>