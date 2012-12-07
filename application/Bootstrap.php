<?php
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    private $_config;

    public function _initConfig()
    {
        $this->_config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->_config);
    }

    public function _initViewAdapter(Yaf_Dispatcher $dispatcher)
    {
        $smarty = new View_SmartyAdapter(null, $this->_config->smarty);
        Yaf_Dispatcher::getInstance()->setView($smarty);
        Yaf_Registry::set('viewAdapter', $smarty);
    }

    public function _initDb()
    {
        $dbConf = $this->_config->db;
        $db = NULL;
        if($dbConf->adapter == 'PDO_MYSQL') {
            $dsn = 'mysql:dbname='.$dbConf->dbname.';host='.$dbConf->host;
            if(isset($dbConf->port)) {
                $dsn .= ';port='.$dbConf->port;
            }
            $db = new Db_EasyPDO($dsn, $dbConf->username, $dbConf->password);
        } elseif($dbConf->adapter == 'PDO_PGSQL') {
            $dsn = 'pgsql:dbname='.$dbConf->dbname.' host='.$dbConf->host;
            if(isset($dbConf->port)) {
                $dsn .= ' port='.$dbConf->port;
            }
            $db = new Db_EasyPDO($dsn, $dbConf->username, $dbConf->password);
        } elseif($dbConf->adapter == 'PDO_SQLITE') {
            $dsn = 'sqlite:dbname='.$dbConf->dbname;
            $db = new Db_EasyPDO($dsn);
        }
        Yaf_Registry::set('db', $db);
    }
}
