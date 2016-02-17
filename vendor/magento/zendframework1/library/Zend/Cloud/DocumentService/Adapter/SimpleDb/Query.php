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

/*
 * @see Zend_Cloud_DocumentService_Query
 */
#require_once 'Zend/Cloud/DocumentService/Query.php';

/**
 * Class implementing Query adapter for working with SimpleDb queries in a
 * structured way
 *
 * @category   Zend
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Cloud_DocumentService_Adapter_SimpleDb_Query
    extends Zend_Cloud_DocumentService_Query
{
    /**
     * @var Zend_Cloud_DocumentService_Adapter_SimpleDb
     */
    protected $_adapter;

    /**
     * Constructor
     *
     * @param  Zend_Cloud_DocumentService_Adapter_SimpleDb $adapter
     * @param  null|string $collectionName
     * @return void
     */
    public function __construct(Zend_Cloud_DocumentService_Adapter_SimpleDb $adapter, $collectionName = null)
    {
        $this->_adapter = $adapter;
        if (null !== $collectionName) {
            $this->from($collectionName);
        }
    }

    /**
     * Get adapter
     *
     * @return Zend_Cloud_DocumentService_Adapter_SimpleDb
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * Assemble the query into a format the adapter can utilize
     *
     * @var    string $collectionName Name of collection from which to select
     * @return string
     */
    public function assemble($collectionName = null)
    {
        $adapter = $this->getAdapter()->getClient();
        $select  = null;
        $from    = null;
        $where   = null;
        $order   = null;
        $limit   = null;
        foreach ($this->getClauses() as $clause) {
            list($name, $args) = $clause;

            switch ($name) {
                case self::QUERY_SELECT:
                    $select = $args[0];
                    break;
                case self::QUERY_FROM:
                    if (null === $from) {
                        // Only allow setting FROM clause once
                        $from = $adapter->quoteName($args);
                    }
                    break;
                case self::QUERY_WHERE:
                    $statement = $this->_parseWhere($args[0], $args[1]);
                    if (null === $where) {
                        $where = $statement;
                    } else {
                        $operator = empty($args[2]) ? 'AND' : $args[2];
                        $where .= ' ' . $operator . ' ' . $statement;
                    }
                    break;
                case self::QUERY_WHEREID:
                    $statement = $this->_parseWhere('ItemName() = ?', array($args));
                    if (null === $where) {
                        $where = $statement;
                    } else {
                        $operator = empty($args[2]) ? 'AND' : $args[2];
                        $where .= ' ' . $operator . ' ' . $statement;
                    }
                    break;
                case self::QUERY_ORDER:
                    $order = $adapter->quoteName($args[0]);
                    if (isset($args[1])) {
                        $order .= ' ' . $args[1];
                    }
                    break;
                case self::QUERY_LIMIT:
                    $limit = $args;
                    break;
                default:
                    // Ignore unknown clauses
                    break;
            }
        }

        if (empty($select)) {
            $select = "*";
        }
        if (empty($from)) {
            if (null === $collectionName) {
                #require_once 'Zend/Cloud/DocumentService/Exception.php';
                throw new Zend_Cloud_DocumentService_Exception("Query requires a FROM clause");
            }
            $from = $adapter->quoteName($collectionName);
        }
        $query = "select $select from $from";
        if (!empty($where)) {
            $query .= " where $where";
        }
        if (!empty($order)) {
            $query .= " order by $order";
        }
        if (!empty($limit)) {
            $query .= " limit $limit";
        }
        return $query;
    }

    /**
     * Parse a where statement into service-specific language
     *
     * @todo   Ensure this fulfills the entire SimpleDB query specification for WHERE
     * @param  string $where
     * @param  array $args
     * @return string
     */
    protected function _parseWhere($where, $args)
    {
        if (!is_array($args)) {
            $args = (array) $args;
        }
        $adapter = $this->getAdapter()->getClient();
        $i = 0;
        while (false !== ($pos = strpos($where, '?'))) {
           $where = substr_replace($where, $adapter->quote($args[$i]), $pos);
           ++$i;
        }
        if (('(' != $where[0]) || (')' != $where[strlen($where) - 1])) {
            $where = '(' . $where . ')';
        }
        return $where;
    }
 }
