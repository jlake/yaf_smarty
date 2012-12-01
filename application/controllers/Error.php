<?php
class ErrorController extends Yaf_Controller_Abstract {

    public function errorAction() {
        $exception = $this->getRequest()->getParam('exception');

        $this->_view->message = ($showErrors) ? $exception->getMessage() : '';
        if(APP_ENV != 'product') {
            $this->_view->trace = ($showErrors) ? $exception->getTraceAsString() : '';
        }       

        /*Yaf has a few different types of errors*/
        switch(true):
            case ($exception instanceof Yaf_Exception_LoadFailed):
                return $this->_pageNotFound();
            default:
                return $this->_unknownError();
        endswitch;
    }

    private function _pageNotFound(){
        $this->getResponse()->setHeader('HTTP/1.0 404 Not Found');
        $this->_view->error = 'Page was not found';
    }

    private function _unknownError(){
        $this->getResponse()->setHeader('HTTP/1.0 500 Internal Server Error');
        $this->_view->error = 'Application Error';
    }

}
