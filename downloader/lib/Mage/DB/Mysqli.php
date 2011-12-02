<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_DB
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Mysqli database connector
 *
 * @category    Mage
 * @package     Mage_Db
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_DB_Mysqli
{
    /**
     * Default port
     * @var int
     */
    const DEFAULT_PORT = 3306;

    /**
     * Table name escaper
     * @var string
     */
    const TABLE_ESCAPER = '`';

    /**
     * Value escaper
     * @var unknown_type
     */
    const VALUE_ESCAPER = '"';

    /**
     * Connection
     * @var mysqli
     */
    protected $conn;
    /**
     * Fetch mode
     * @var unknown_type
     */
    private $fetch_mode = MYSQLI_ASSOC;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->conn = new mysqli();
    }

    /**
     * Connect
     * @param string $host
     * @param string $user
     * @param string $paswd
     * @param string $db
     * @param int $port
     * @return mixed
     */
    public function connect($host, $user, $paswd, $db, $port = self::DEFAULT_PORT)
    {
        $port = (int) $port;
        $res = @$this->conn->connect($host, $user, $paswd, $db, $port);
        if(0 !== mysqli_connect_errno($this->conn)) {
            throw new Mage_DB_Exception(mysqli_connect_error($this->conn));
        }
        return $res;
    }

    /**
     * Select database
     * @param $db db name
     * @return mixed
     */
    public function selectDb($db)
    {
        $res = mysqli_select_db($this->conn, $db);
        return $res;
    }

    /**
     * Escape string
     * @param string $str
     * @return string
     */
    public function escapeString($str)
    {
        return mysqli_real_escape_string($this->conn, $str);
    }

    /**
     * Escape table name
     * @param string $table
     * @return string
     */
    public function escapeTableName($table)
    {
        return self::TABLE_ESCAPER.$this->escapeString($table).self::TABLE_ESCAPER;
    }

    /**
     * Escape field name
     * @param stirng $fld
     * @return string
     */
    public function escapeFieldName($fld)
    {
        return self::TABLE_ESCAPER.$this->escapeString($fld).self::TABLE_ESCAPER;
    }

    /**
     * Escape field value
     * @param $data
     * @return string
     */
    public function escapeFieldValue($data)
    {
        return self::VALUE_ESCAPER.$this->escapeString($data).self::VALUE_ESCAPER;
    }

    /**
     * Fetch all rows
     * @param $sql
     * @return array
     */
    public function fetchAll($sql)
    {
        $res = $this->query($sql);
        for($out = array(); $row = $res->fetch_array($this->fetch_mode); $out[] = $row);
        return $out;
    }

    /**
     * Fetch one row
     * @param $sql
     * @return array
     */
    public function fetchOne($sql)
    {
        $res = $this->query($sql);
        return $res->fetch_array($this->fetch_mode);
    }

    /**
     * Fetch rows grouped by key
     * @param $sql
     * @param $key
     * @param $arrayMode force Array mode
     * @return array
     */
    public function fetchGroupedArrayByKey($sql, $key, $arrayMode = true)
    {
        $res = $this->query($sql);
        $out = array();
        while($row = $res->fetch_array(MYSQLI_ASSOC)) {
            if($arrayMode) {
                if(!isset($out[$row[$key]])) {
                    $out[$row[$key]] = array();
                }
                $out[$row[$key]][] = $row;
            } else {
                $out[$row[$key]] = $row;
            }
        }
        return $out;
    }

    /**
     * Fetch one field from all rows and place to list
     * @param string $sql
     * @param string $fld
     * @return array
     */
    public function fetchOneFieldAll($sql, $fld)
    {
        $res = $this->query($sql);
        for($out = array(); $row = $res->fetch_array($this->fetch_mode); $out[] = $row[$fld]);
        return $out;
    }

    /**
     * List one item
     * @param $table
     * @param $condition
     * @return array
     */
    public function listOne($table, $condition)
    {
        $table = $this->escapeTableName($table);
        $sql = "SELECT * FROM {$table} WHERE {$condition}";
        return $this->fetchOne($sql);
    }

    /**
     * List items in table by condition
     * @param string $table        table name
     * @param string $condition    optional, if empty 1=1 is used
     * @return array
     */
    public function listAll($table, $condition = '1=1')
    {
        $table = $this->escapeTableName($table);
        $sql = "SELECT * FROM {$table} WHERE {$condition}";
        return $this->fetchAll($sql);
    }

    /**
     * List by key single entry
     * @param string $table   table name
     * @param string $value   field value
     * @param string $key     field name
     * @return array
     */
    public function listByKeyOne($table, $value, $key = 'id')
    {
        $table = $this->escapeTableName($table);
        $key = $this->escapeFieldName($key);
        $value = $this->escapeFieldValue($value);
        $sql = "SELECT * FROM {$table} WHERE {$key} = {$value}";
        return $this->fetchOne($sql);
    }

    /**
     * List by key all rows in table
     * @param string $table   table name
     * @param string $value   value of key field
     * @param string $key     key field name
     * @param string $add     additional conditions
     * @return array
     */
    public function listByKeyAll($table, $value, $key = 'id', $add = '')
    {
        $table = $this->escapeTableName($table);
        $key = $this->escapeFieldName($key);
        $value = $this->escapeFieldValue($value);
        $sql = "SELECT * FROM {$table} WHERE {$key} = {$value} {$add}";
        return $this->fetchAll($sql);
    }

    /**
     * List by key grouped
     * @param string $table
     * @param string $key
     * @param bool $forcedArrayMode
     * @return array
     */
    public function listByKeyGrouped($table,  $key = 'id', $forcedArrayMode = false)
    {
        $table = $this->escapeTableName($table);
        $sql = "SELECT * FROM {$table}";
        return $this->fetchGroupedArrayByKey($sql, $key, $forcedArrayMode);
    }


    /**
     * Escape field names
     * @param array $arrNames
     * @return array
     */
    public function escapeFieldNames(array $arrNames)
    {
        $out = array();
        for ($i=0, $c = count($arrNames) ; $i<$c; $i++) {
            $out[] = $this->escapeFieldName($arrNames[$i]);
        }
        return $out;
    }

    /**
     * Escape field values
     * @param array $arrNames
     * @return array
     */
    public function escapeFieldValues(array $arrNames)
    {
        $out = array();
        for ($i=0, $c = count($arrNames) ; $i<$c; $i++) {
            if($arrNames[$i] !== 'LAST_INSERT_ID()') {
                $out[] = $this->escapeFieldValue($arrNames[$i]);
            } else {
                $out[] = $arrNames[$i];
            }
        }
        return $out;
    }


    /**
     * Throw connect exception
     * @throws Mage_DB_Exception
     * @return void
     */
    protected function throwConnectException()
    {
        throw new Mage_DB_Exception($this->conn->connect_error);
    }

    /**
     * Query - perform with throwing exception on error
     * @param sting $sql query
     * @throws Mage_DB_Exception
     * @return mixed
     */
    public function query($sql)
    {
        $res = $this->unsafeQuery($sql);
        if(!$res) {
            throw new Mage_DB_Exception($this->conn->error);
        }
        return $res;
    }

    /**
     * Unsafe query - perform without error checking
     * @param string $sql query
     * @return mixed
     */
    public function unsafeQuery($sql)
    {
        return $this->conn->query($sql);
    }

    /**
     * Insert assoc array to table
     * @param string $table
     * @param array $data
     * @param bool $replace
     * @return mixed
     */
    public function insertAssocOne($table, array $data, $replace = false) {
        $keys = $this->escapeFieldNames(array_keys($data));
        $keys = "(" . implode (",", $keys) . ")";
        $table = $this->escapeTableName($table);
        $sql = $replace ? "REPLACE INTO {$table} " : "INSERT INTO {$table} ";
        $values = $this->escapeFieldValues(array_values($data));
        $values = " VALUES (" . implode (",", $values) . ")";
        $sql .= $keys . $values;
        return $this->query($sql);
    }

    /**
     * Insert several records to table
     * @param string $table
     * @param array $data
     * @param bool $replace   use REPLACE INTO instead of INSERT INTO
     * @return array
     */
    public function insertAssocMultiple($table, array $data, $replace = false, $excludeFields = array())
    {
        $table = $this->escapeTableName($table);
        $sql = $replace ? "REPLACE INTO {$table} " : "INSERT INTO {$table} ";
        $keys = array_keys($data[0]);
        $excluded = array();
        for($i = 0, $c = count($excludeFields); $i < $c; $i++) {
            $k = $excludeFields[$i];
            if(isset($keys[$k])) {
                $excluded [] = $k;
                unset($keys[$k]);    
            }            
        }
        
        $keys = $this->escapeFieldNames($keys);
        $sql .= " ( ";
        for($i = 0, $c = count($keys); $i<$c; $i++) {
            $sql .= $keys[$i];
            if($i!=$c-1) {
                $sql .= ",";
            }
        }
        $sql .= " ) VALUES ";
        for($i = 0, $c = count($data); $i<$c; $i++) {
            $row = $data[$i];
            for ($j = 0, $jc = count($excluded); $j<$jc; $j++) {
                unset($data[$excluded[$j]]);
            }
            $values = $this->escapeFieldValues(array_values($row));
            $sql .= "( ";
            for ($j = 0, $jc = count($values); $j < $jc; $j++) {
                $sql .= $values[$j];
                if($j != $jc-1) {
                    $sql .= ",";
                }
            }
            $sql .= " )";
            if($i!=$c-1) {
                $sql .= ",";
            }
        }        
        return $this->query($sql);
    }


    /**
     * Set table data by condition
     * @param $table
     * @param $data
     * @param $condition
     * @return mixed
     */
    public function updateAssoc($table, array $data, $condition = '1=1')
    {
        $table = $this->escapeTableName($table);
        $set = array();
        foreach($data as $k=>$v) {
            $k = $this->escapeFieldName($k);
            $v = $this->escapeFieldValue($v);
            $set[] = $k . " = " . $v;
        }
        $set = implode(",", $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$condition}";
        return $this->query($sql);
    }


    /**
     * Update entry by pk
     * @param string $table
     * @param array $data
     * @param string $value
     * @param string $key
     * @return mixed
     */
    public function updateAssocByKey($table, array $data, $value, $key = 'id')
    {
        $table = $this->escapeTableName($table);
        $key = $this->escapeFieldName($key);
        $value = $this->escapeFieldValue($value);
        $set = array();
        foreach($data as $k=>$v) {
            $k = $this->escapeFieldName($k);
            $v = $this->escapeFieldValue($v);
            $set[] = $k . " = " . $v;
        }
        $set = implode(",", $set);
        $sql = "UPDATE {$table} SET {$set} WHERE {$key} = {$value}";
        return $this->query($sql);
    }


    /**
     * Convert ids to string
     * @param array|string $ids
     * @return string
     */
    public function idsToString($ids)
    {
        if(is_scalar($ids)) {
            return $this->escapeFieldValue(strval($ids));
        }
        $out = array();
        foreach ($values as $id) {
            $out .= $this->escapeFieldValue($id);
        }
        return implode(",", $out);
    }

    /**
     * Ids equality condition
     * @param mixed $ids   array or string
     * @return string
     */
    public function idsEqualCondition($ids)
    {
        $vals = $this->idsToString($ids);
        $condition = is_scalar($ids) ? " = {$vals} " : " IN ({$vals}) ";
        return $condition;
    }


    /**
     * Delete items by id
     * @param string $table
     * @param mixed $ids      array or string
     * @param string $key     key field
     * @return mixed
     */
    public function deleteById($table, $ids, $key = 'id')
    {
        $key = $this->escapeFieldName($key);
        $cond = $this->idsEqualCondition($ids);
        $table = $this->escapeTableName($table);
        $sql = "DELETE FROM {$table} WHERE {$key} {$cond}";
        return $this->query($sql);
    }

    /**
     * Count items in table by condition
     * @param string $table
     * @param string $condition ex: "a>0"
     * @return int
     */
    public function simpleCount($table, $condition) {
        $sql = "SELECT count(*) AS `cnt` WHERE {$condition}";
        $data = $this->fetchOne($sql);
        if(empty($data['cnt'])) {
            return 0;
        }
        return intval($data['cnt']);

    }
    
    public function lastInsertId()
    {
        $sql = "SELECT LAST_INSERT_ID() as `id`";
        $data = $this->fetchOne($sql);
        return $data['id'];        
    }

}
