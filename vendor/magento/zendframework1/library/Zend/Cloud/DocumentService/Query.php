<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/DocumentService/QueryAdapter.php';

/**
 * Generic query object
 *
 * Aggregates operations in an array of clauses, where the first element
 * describes the clause type, and the next element describes the criteria.
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Query
    implements Zend_Cloud_DocumentService_QueryAdapter
{
    /**
     * Known query types
     */
    const QUERY_SELECT  = 'select';
    const QUERY_FROM    = 'from';
    const QUERY_WHERE   = 'where';
    const QUERY_WHEREID = 'whereid'; // request element by ID
    const QUERY_LIMIT   = 'limit';
    const QUERY_ORDER   = 'order';

    /**
     * Clause list
     *
     * @var array
     */
    protected $_clauses = array();

    /**
     * Generic clause
     *
     * You can use any clause by doing $query->foo('bar')
     * but concrete adapters should be able to recognise it
     *
     * The call will be iterpreted as clause 'foo' with argument 'bar'
     *
     * @param  string $name Clause/method name
     * @param  mixed $args
     * @return Zend_Cloud_DocumentService_Query
     */
    public function __call($name, $args)
    {
        $this->_clauses[] = array(strtolower($name), $args);
        return $this;
    }

    /**
     * SELECT clause (fields to be selected)
     *
     * @param  null|string|array $select
     * @return Zend_Cloud_DocumentService_Query
     */
    public function select($select)
    {
        if (empty($select)) {
            return $this;
        }
        if (!is_string($select) && !is_array($select)) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception("SELECT argument must be a string or an array of strings");
        }
        $this->_clauses[] = array(self::QUERY_SELECT, $select);
        return $this;
    }

    /**
     * FROM clause
     *
     * @param string $name Field names
     * @return Zend_Cloud_DocumentService_Query
     */
    public function from($name)
    {
        if(!is_string($name)) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception("FROM argument must be a string");
        }
        $this->_clauses[] = array(self::QUERY_FROM, $name);
        return $this;
    }

    /**
     * WHERE query
     *
     * @param string $cond Condition
     * @param array $args Arguments to substitute instead of ?'s in condition
     * @param string $op relation to other clauses - and/or
     * @return Zend_Cloud_DocumentService_Query
     */
    public function where($cond, $value = null, $op = 'and')
    {
        if (!is_string($cond)) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception("WHERE argument must be a string");
        }
        $this->_clauses[] = array(self::QUERY_WHERE, array($cond, $value, $op));
        return $this;
    }

    /**
     * Select record or fields by ID
     *
     * @param  string|int $value Identifier to select by
     * @return Zend_Cloud_DocumentService_Query
     */
    public function whereId($value)
    {
        if (!is_scalar($value)) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception("WHEREID argument must be a scalar");
        }
        $this->_clauses[] = array(self::QUERY_WHEREID, $value);
        return $this;
    }

    /**
     * LIMIT clause (how many items to return)
     *
     * @param  int $limit
     * @return Zend_Cloud_DocumentService_Query
     */
    public function limit($limit)
    {
        if ($limit != (int) $limit) {
            #require_once 'Zend/Cloud/DocumentService/Exception.php';
            throw new Zend_Cloud_DocumentService_Exception("LIMIT argument must be an integer");
        }
        $this->_clauses[] = array(self::QUERY_LIMIT, $limit);
        return $this;
    }

    /**
     * ORDER clause; field or fields to sort by, and direction to sort
     *
     * @param  string|int|array $sort
     * @param  string $direction
     * @return Zend_Cloud_DocumentService_Query
     */
    public function order($sort, $direction = 'asc')
    {
        $this->_clauses[] = array(self::QUERY_ORDER, array($sort, $direction));
        return $this;
    }

    /**
     * "Assemble" the query
     *
     * Simply returns the clauses present.
     *
     * @return array
     */
    public function assemble()
    {
        return $this->getClauses();
    }

    /**
     * Return query clauses as an array
     *
     * @return array Clauses in the query
     */
    public function getClauses()
    {
         return $this->_clauses;
    }
}
