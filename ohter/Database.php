<?php
/**
 * 数据库封装类
 * 基于PDO
 * @author SamDing
 */
namespace koboshi\tool;

class Database
{
    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var int
     */
    private $port;

    /**
     * @var string
     */
    private $dbName;

    /**
     * @var string
     */
    private $charset;

    /**
     * @var string
     */
    private $lastSql;

    /**
     * @var int
     */
    private $affectedRows = 0;

    /**
     * @var \PDO
     */
    private $pdoHandle;

    /**
     * Database constructor.
     * @param string $host
     * @param string $user
     * @param string $password
     * @param int $port
     * @param string $dbName
     * @param string $charset
     */
    public function __construct($host, $user, $password, $port = 3306, $dbName = '', $charset = 'utf8')
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->port = $port;
        $this->dbName = $dbName;
        $this->charset = $charset;
    }

    public function __destruct()
    {
        $this->pdoHandle = null;
    }

    private function connect($force = false)
    {
        if (is_null($this->pdoHandle) || $force) {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset={$this->charset}";
            $handle = new \PDO($dsn, $this->user, $this->password, array(
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
                \PDO::MYSQL_ATTR_COMPRESS => false
            ));
            $handle->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdoHandle = $handle;
        }
    }

    /**
     * @param string $dbName
     */
    public function selectDatabase($dbName)
    {
        $this->exec("USE {$dbName};");
        $this->dbName = $dbName;
    }

    public function lastSql()
    {
        return $this->lastSql;
    }

    public function queryOne($sql, $params = array())
    {
        $statement = $this->_query($sql, $params);
        $output = $statement->fetch(\PDO::FETCH_ASSOC);
        return empty($output) ? array() : $output;
    }

    public function query($sql, $params = array())
    {
        $statement = $this->_query($sql, $params);
        $output = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return empty($output) ? array() : $output;
    }

    private function _query($sql, $params)
    {
        $this->connect();
        $statement = $this->pdoHandle->prepare($sql);
        foreach ($params as $k => $v) {
            $this->bindParam($k, $v, $statement);
        }
        $flag = $statement->execute();
        if ($flag === false) {
            $this->handleError($statement);
        }
        $this->affectedRows = $statement->rowCount();
        $this->lastSql = $statement->queryString;
        return $statement;
    }

    private function _insert(array $data, $tbl, $db, $type)
    {
        $type = strtoupper($type);
        $output = array();
        foreach ($data as $k => $v) {
            $output[] = "`{$k}` = :{$k}";
        }
        if (empty($db)) {
            $tblStr = "`{$tbl}`";
        } else {
            $tblStr = "`{$db}`.`{$tbl}";
        }
        $setStr = implode(', ', $output);
        $sql = "{$type} INTO {$tblStr} SET {$setStr};";
        return $sql;
    }

    public function delete($whereStr, $tbl, $db = null)
    {
        $this->connect();
        if (empty($db)) {
            $tblStr = "`{$tbl}`";
        } else {
            $tblStr = "`{$db}`.`{$tbl}";
        }
        $sql = "DELETE FROM {$tblStr} WHERE {$whereStr}";
        $statement = $this->pdoHandle->prepare($sql);
        $flag = $statement->execute();
        if ($flag === false) {
            $this->handleError($statement);
        }
        $this->affectedRows = $statement->rowCount();
        $this->lastSql = $statement->queryString;
        return $this->affectedRows();
    }

    public function update(array $data, $whereStr, $tbl, $db = null)
    {
        $this->connect();
        $output = array();
        foreach ($data as $k => $v) {
            $output[] = "`{$k}` = :{$k}";
        }
        if (empty($db)) {
            $tblStr = "`{$tbl}`";
        } else {
            $tblStr = "`{$db}`.`{$tbl}";
        }
        $setStr = implode(', ', $output);
        $sql = "UPDATE {$tblStr} SET {$setStr} WHERE {$whereStr};";
        $statement = $this->pdoHandle->prepare($sql);
        foreach ($data as $k => $v) {
            $this->bindParam(':' . $k, $v, $statement);
        }
        $flag = $statement->execute();
        if ($flag === false) {
            $this->handleError($statement);
        }
        $this->affectedRows = $statement->rowCount();
        $this->lastSql = $statement->queryString;
        return $this->affectedRows();
    }

    public function insert(array $data, $tbl, $db = null)
    {
        $this->connect();
        $sql = $this->_insert($data, $tbl, $db, 'INSERT');
        $statement = $this->pdoHandle->prepare($sql);
        foreach ($data as $k => $v) {
            $this->bindParam(':' . $k, $v, $statement);
        }
        $flag = $statement->execute();
        if ($flag === false) {
            $this->handleError($statement);
        }
        $this->affectedRows = $statement->rowCount();
        $this->lastSql = $statement->queryString;
        return $this->lastInsertId();
    }

    public function replace(array $data, $tbl, $db = null)
    {
        $this->connect();
        $sql = $this->_insert($data, $tbl, $db, 'REPLACE');
        $statement = $this->pdoHandle->prepare($sql);
        foreach ($data as $k => $v) {
            $this->bindParam(':' . $k, $v, $statement);
        }
        $flag = $statement->execute();
        if ($flag === false) {
            $this->handleError($statement);
        }
        $this->affectedRows = $statement->rowCount();
        $this->lastSql = $statement->queryString;
        return $this->lastInsertId();
    }

    public function ignore(array $data, $tbl, $db = null)
    {
        $this->connect();
        $sql = $this->_insert($data, $tbl, $db, 'INSERT IGNORE');
        $statement = $this->pdoHandle->prepare($sql);
        foreach ($data as $k => $v) {
            $this->bindParam(':' . $k, $v, $statement);
        }
        $flag = $statement->execute();
        if ($flag === false) {
            $this->handleError($statement);
        }
        $this->affectedRows = $statement->rowCount();
        $this->lastSql = $statement->queryString;
        return $this->lastInsertId();
    }

    public function exec($sql)
    {
        $this->connect();
        $flag = $this->pdoHandle->exec($sql);
        if ($flag === false) {
            $this->handleError($this->pdoHandle);
        }
        $this->affectedRows = $flag;
        $this->lastSql = $sql;
        return $this->affectedRows();
    }

    public function affectedRows()
    {
        return $this->affectedRows;
    }

    public function lastInsertId()
    {
        $this->connect();
        return $this->pdoHandle->lastInsertId();
    }

    public function begin()
    {
        $this->connect();
        if ($this->pdoHandle->inTransaction()) {
            throw new \PDOException('in transaction already!');
        } else {
            $this->pdoHandle->beginTransaction();
        }
    }

    public function commit()
    {
        $this->connect();
        $this->pdoHandle->commit();
    }

    public function rollback()
    {
        $this->connect();
        $this->pdoHandle->rollBack();
    }

    public function escape($str)
    {
        $this->connect();
        return $this->pdoHandle->quote($str);
    }

    /**
     * @param \PDO|\PDOStatement $obj
     */
    private function handleError($obj)
    {
        $tmp = $obj->errorInfo();
        $errCode = intval($tmp[1]);
        $errMsg = strval($tmp[2]);
        throw new \PDOException($errMsg, $errCode);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param \PDOStatement $statement
     */
    private function bindParam($key, $value, $statement)
    {
        if (is_numeric($value)) {
            $statement->bindParam($key, $value, \PDO::PARAM_INT);
        } elseif (is_null($value)) {
            $statement->bindParam($key, $value, \PDO::PARAM_NULL);
        } else {
            $statement->bindParam($key, $value, \PDO::PARAM_STR);
        }
    }
}