<?php
class IndexController extends Yaf_Controller_Abstract
{
   public function indexAction()
   {
        $this->_view->name = 'Yet Another Framework';
        //$this->_view->msg = 'Hello World';
		$test = new TestModel();
		$this->_view->msg = $test->getMessage();
   }

   public function dbtestAction()
   {
        $db = Yaf_Registry::get('db');
        $result = $db->select('dummy', '*', 'id = 1');
        $this->_view->msg = print_r($result, 1);
   }
}
