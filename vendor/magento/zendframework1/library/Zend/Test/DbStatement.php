<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Db_Statement_Interface
 */
#require_once "Zend/Db/Statement/Interface.php";

/**
 * Testing Database Statement that acts as a stack to SQL resultsets.
 *
 * @category   Zend
 * @package    Zend_Test
 * @subpackage PHPUnit
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Test_DbStatement implements Zend_Db_Statement_Interface
{
    /**
     * @var array
     */
    protected $_fetchStack = array();

    /**
     * @var int
     */
    protected $_columnCount = 0;

    /**
     * @var int
     */
    protected $_rowCount = 0;

    /**
     * @var Zend_Db_Profiler_Query
     */
    protected $_queryProfile = null;

    /**
     * Create a Select statement which returns the given array of rows.
     *
     * @param array $rows
     * @return Zend_Test_DbStatement
     */
    static public function createSelectStatement(array $rows=array())
    {
        $stmt = new Zend_Test_DbStatement();
        foreach($rows AS $row) {
            $stmt->append($row);
        }
        return $stmt;
    }

    /**
     * Create an Insert Statement
     *
     * @param  int $affectedRows
     * @return Zend_Test_DbStatement
     */
    static public function createInsertStatement($affectedRows=0)
    {
        return self::_createRowCountStatement($affectedRows);
    }

    /**
     * Create an Delete Statement
     *
     * @param  int $affectedRows
     * @return Zend_Test_DbStatement
     */
    static public function createDeleteStatement($affectedRows=0)
    {
        return self::_createRowCountStatement($affectedRows);
    }

    /**
     * Create an Update Statement
     *
     * @param  int $affectedRows
     * @return Zend_Test_DbStatement
     */
    static public function createUpdateStatement($affectedRows=0)
    {
        return self::_createRowCountStatement($affectedRows);
    }

    /**
     * Create a Row Count Statement
     *
     * @param  int $affectedRows
     * @return Zend_Test_DbStatement
     */
    static protected function _createRowCountStatement($affectedRows)
    {
        $stmt = new Zend_Test_DbStatement();
        $stmt->setRowCount($affectedRows);
        return $stmt;
    }

    /**
     * @param Zend_Db_Profiler_Query $qp
     */
    public function setQueryProfile(Zend_Db_Profiler_Query $qp)
    {
        $this->_queryProfile = $qp;
    }

    /**
     * @param int $rowCount
     */
    public function setRowCount($rowCount)
    {
        $this->_rowCount = $rowCount;
    }

    /**
     * Append a new row to the fetch stack.
     *
     * @param array $row
     */
    public function append($row)
    {
        $this->_columnCount = count($row);
        $this->_fetchStack[] = $row;
    }

    /**
     * Bind a column of the statement result set to a PHP variable.
     *
     * @param string $column Name the column in the result set, either by
     *                       position or by name.
     * @param mixed  $param  Reference to the PHP variable containing the value.
     * @param mixed  $type   OPTIONAL
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function bindColumn($column, &$param, $type = null)
    {
        return true;
    }

    /**
     * Binds a parameter to the specified variable name.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $variable  Reference to PHP variable containing the value.
     * @param mixed $type      OPTIONAL Datatype of SQL parameter.
     * @param mixed $length    OPTIONAL Length of SQL parameter.
     * @param mixed $options   OPTIONAL Other options.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function bindParam($parameter, &$variable, $type = null, $length = null, $options = null)
    {
        if($this->_queryProfile !== null) {
            $this->_queryProfile->bindParam($parameter, $variable);
        }
        return true;
    }

    /**
     * Binds a value to a parameter.
     *
     * @param mixed $parameter Name the parameter, either integer or string.
     * @param mixed $value     Scalar value to bind to the parameter.
     * @param mixed $type      OPTIONAL Datatype of the parameter.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function bindValue($parameter, $value, $type = null)
    {
        return true;
    }

    /**
     * Closes the cursor, allowing the statement to be executed again.
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function closeCursor()
    {
        return true;
    }

    /**
     * Returns the number of columns in the result set.
     * Returns null if the statement has no result set metadata.
     *
     * @return int The number of columns.
     * @throws Zend_Db_Statement_Exception
     */
    public function columnCount()
    {
        return $this->_columnCount;
    }

    /**
     * Retrieves the error code, if any, associated with the last operation on
     * the statement handle.
     *
     * @return string error code.
     * @throws Zend_Db_Statement_Exception
     */
    public function errorCode()
    {
        return false;
    }

    /**
     * Retrieves an array of error information, if any, associated with the
     * last operation on the statement handle.
     *
     * @return array
     * @throws Zend_Db_Statement_Exception
     */
    public function errorInfo()
    {
        return false;
    }

    /**
     * Executes a prepared statement.
     *
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function execute(array $params = array())
    {
        if($this->_queryProfile !== null) {
            $this->_queryProfile->bindParams($params);
            $this->_queryProfile->end();
        }
        return true;
    }

    /**
     * Fetches a row from the result set.
     *
     * @param int $style  OPTIONAL Fetch mode for this fetch operation.
     * @param int $cursor OPTIONAL Absolute, relative, or other.
     * @param int $offset OPTIONAL Number for absolute or relative cursors.
     * @return mixed Array, object, or scalar depending on fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetch($style = null, $cursor = null, $offset = null)
    {
        if(count($this->_fetchStack)) {
            $row = array_shift($this->_fetchStack);
            return $row;
        } else {
            return false;
        }
    }

    /**
     * Returns an array containing all of the result set rows.
     *
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * @return array Collection of rows, each in a format by the fetch mode.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchAll($style = null, $col = null)
    {
        $rows = $this->_fetchStack;
        $this->_fetchStack = array();

        return $rows;
    }

    /**
     * Returns a single column from the next row of a result set.
     *
     * @param int $col OPTIONAL Position of the column to fetch.
     * @return string
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchColumn($col = 0)
    {
        $row = $this->fetch();

        if($row == false) {
            return false;
        } else {
            if(count($row) < $col) {
                #require_once "Zend/Db/Statement/Exception.php";
                throw new Zend_Db_Statement_Exception(
                    "Column Position '".$col."' is out of bounds."
                );
            }

            $keys = array_keys($row);
            return $row[$keys[$col]];
        }
    }

    /**
     * Fetches the next row and returns it as an object.
     *
     * @param string $class  OPTIONAL Name of the class to create.
     * @param array  $config OPTIONAL Constructor arguments for the class.
     * @return mixed One object instance of the specified class.
     * @throws Zend_Db_Statement_Exception
     */
    public function fetchObject($class = 'stdClass', array $config = array())
    {
        if(!class_exists($class)) {
            throw new Zend_Db_Statement_Exception("Class '".$class."' does not exist!");
        }

        $object = new $class();
        $row = $this->fetch();
        foreach($row AS $k => $v) {
            $object->$k = $v;
        }

        return $object;
    }

    /**
     * Retrieve a statement attribute.
     *
     * @param string $key Attribute name.
     * @return mixed      Attribute value.
     * @throws Zend_Db_Statement_Exception
     */
    public function getAttribute($key)
    {
        return false;
    }

    /**
     * Retrieves the next rowset (result set) for a SQL statement that has
     * multiple result sets.  An example is a stored procedure that returns
     * the results of multiple queries.
     *
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function nextRowset()
    {
        return false;
    }

    /**
     * Returns the number of rows affected by the execution of the
     * last INSERT, DELETE, or UPDATE statement executed by this
     * statement object.
     *
     * @return int     The number of rows affected.
     * @throws Zend_Db_Statement_Exception
     */
    public function rowCount()
    {
        return $this->_rowCount;
    }

    /**
     * Set a statement attribute.
     *
     * @param string $key Attribute name.
     * @param mixed  $val Attribute value.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function setAttribute($key, $val)
    {
        return true;
    }

    /**
     * Set the default fetch mode for this statement.
     *
     * @param int   $mode The fetch mode.
     * @return bool
     * @throws Zend_Db_Statement_Exception
     */
    public function setFetchMode($mode)
    {
        return true;
    }
}
