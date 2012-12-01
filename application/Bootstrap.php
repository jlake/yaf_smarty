<?php
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    public function _initConfig()
    {
		$config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set("config", $config);
	}

    public function _initViewAdapter(Yaf_Dispatcher $dispatcher)
    {
        $smarty = new View_SmartyAdapter(null, Yaf_Registry::get("config")->get("smarty"));
        Yaf_Dispatcher::getInstance()->setView($smarty);
        Yaf_Registry::set("viewAdapter", $smarty);
    }
}
