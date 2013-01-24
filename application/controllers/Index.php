<?php
class IndexController extends Yaf_Controller_Abstract
{
   public function indexAction()
   {
        $this->_view->msg1 = 'Hello World';
        $test = new TestModel();
        $this->_view->msg2 = $test->getMessage();
   }

   public function dbtestAction()
   {
        $db = Yaf_Registry::get('db');
        $result = $db->select('dummy', '*', 'id = 1');
        $this->_view->msg = print_r($result, 1);
   }
}
