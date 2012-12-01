<?php
class IndexController extends Yaf_Controller_Abstract {
   	public function indexAction() {
		$this->_view->name = 'Yet Another Framework';
		$this->_view->msg = 'Hello World';
   	}
}
