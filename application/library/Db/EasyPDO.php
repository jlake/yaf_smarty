<?php
class Db_EasyPDO extends PDO
{
    private $_fetchMode = PDO::FETCH_ASSOC;

    /**
     * Class constructor
     *
     * @param  string  $dsn     Connection DSN
     * @param  string  $user    Connection user name
     * @param  string  $passwd  Connection password
     * @param  string  $options PDO driver options
     * @return PDO
     */
    public function  __construct($dsn, $user='', $passwd='', $options=NULL)
    {
        if(empty($options)) {
            $options = array(
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => true,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
        }

        parent::__construct($dsn, $user, $passwd, $options);
    }

    /**
     * Prepare and returns a PDOStatement
     *
     * @param  string  $sql  SQL statement
     * @param  array   $bind Parameters. A single value or an array of values
     * @return PDOStatement
     */
    private function _prepare($sql, $bind = array())
    {
        $stmt = $this->prepare($sql);

        if (!$stmt) {
            $errorInfo = $this->errorInfo();
            throw new PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }
        if(!is_array($bind)) {
            $bind = empty($bind) ? array() : array($bind);
        }
        if (!$stmt->execute($bind) || $stmt->errorCode() != '00000') {
            $errorInfo = $stmt->errorInfo();
            throw new PDOException("Database error [{$errorInfo[0]}]: {$errorInfo[2]}, driver error code is $errorInfo[1]");
        }

        return $stmt;
    }

    /**
     * Execute sql and returns number of effected rows
     *
     * Should be used for query which doesn't return resultset
     *
     * @param  string  $sql   SQL statement
     * @param  array   $bind  Parameters. A single value or an array of values
     * @return integer Number of effected rows
     */
    public function run($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->rowCount();
    }

    /**
     * set fetch mode for PDO
     *
     * @param  string  $fetchMode    PDO fetch mode
     * @return PDO
     */
    public function setFetchMode($fetchMode)
    {
        $this->_fetchMode = $fetchMode;
        return $this;
    }

    /**
     * get where expression (if array, convert to sting)
     *
     * @param  string  $where  where string or array
     * @param  array   $andOr  AND or OR
     * @return string  where string
     */
    public function where($where, $andOr = 'AND')
    {
        if(is_array($where)) {
            $tmp = array();
            foreach($where as $k => $v) {
                $tmp[] = $k . '=' . $this->quote($v);
            }
            return '(' . implode(" $andOr ", $tmp) . ')';
        }
        return $where;
    }

    /**
     * select records from a table
     *
     * @param  string $table  table name
     * @param  string $where  where string
     * @param  array  $bind  Parameters. A single value or an array of values
     * @param  string $fields  fields list
     * @return array
     */
    public function select($table, $where = "", $bind = array(), $fields = "*")
    {
        $sql = "SELECT " . $fields . " FROM " . $table;
        if(!empty($where)) {
            $where = $this->where($where);
            $sql .= " WHERE " . $where;
        }
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchAll($this->_fetchMode);
    }

    /**
     * insert a record to a table
     *
     * @param  string $table  table name
     * @param  array  $data  An array of values
     * @return array
     */
    public function insert($table, $data)
    {
        $fields = array_keys($data);
        $sql = "INSERT INTO " . $table . " (" . implode($fields, ", ") . ") VALUES (:" . implode($fields, ", :") . ");";
        $bind = array();
        foreach($fields as $field) {
            $bind[":$field"] = $data[$field];
        }
        return $this->run($sql, $bind);
    }

    /**
     * update records for one table
     *
     * @param  string $table  table name
     * @param  array  $data  An array of values
     * @param  string $where  where string
     * @return array
     */
    public function update($table, $data, $where)
    {
        $sql = "UPDATE " . $table . " SET ";
        $comma = '';
        $bind = array();
        foreach($data as $k => $v) {
            $sql .= $comma . $k . " = :" . $k;
            $comma = ', ';
            $bind[$k] = $v;
        }
        $where = $this->where($where);
        $sql .= " WHERE " . $where;
        return $this->run($sql, $bind);
    }

    /**
     * delete records from table
     *
     * @param  string $table  table name
     * @param  string $where  where string
     * @param  array  $bind  Parameters. A single value or an array of values
     * @return array
     */
    public function delete($table, $where, $bind = array())
    {
        $where = $this->where($where);
        $sql = "DELETE FROM " . $table . " WHERE " . $where . ";";
        return $this->run($sql, $bind);
    }

    /**
     * save data to table (update is exists, else insert)
     *
     * @param string $table  table name
     * @param array $data  data array
     * @param mixed $where  SQL WHERE string or key/value array
     * @return mixed
     */
    public function save($table, $data, $where = "")
    {
        $count = 0;
        if(!empty($where)) {
            $where = $this->where($where);
            $count = $this->fetchOne("SELECT COUNT(1) FROM $table WHERE $where");
        }
        if($count == 0) {
            return $this->insert($table, $data);
        } else {
            return $this->update($table, $data, $where);
        }
    }

    /**
     * Execute sql and returns a single value
     *
     * @param  string  $sql   SQL statement
     * @param  array   $bind  A single value or an array of values
     * @return mixed  Result value
     */
    public function fetchOne($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchColumn(0);
    }

    /**
     * Execute sql and returns the first row
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   A result row
     */
    public function fetchRow($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Execute sql and returns row(s) as 2D array
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchAll($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Execute sql and returns row(s) as 2D array, array key is first column's values 
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchAssoc($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        if(!empty($records)) {
            $k0 = key($records[0]);
            foreach($records as $rec) {
                $result[$rec[$k0]] = $rec;
            }
        }
        return $result;
    }

    /**
     * Execute sql and returns a key/value pairs array
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchPairs($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /**
     * Execute sql and returns an values array of first column
     *
     * @param  string  $sql    SQL statement
     * @param  array   $bind A single value or an array of values
     * @return array   Result rows
     */
    public function fetchCol($sql, $bind = array())
    {
        $stmt = $this->_prepare($sql, $bind);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = array();
        if(!empty($records)) {
            $k0 = key($records[0]);
            foreach($records as $rec) {
                $result[] = $rec[$k0];
            }
        }
        return $result;
    }
}
