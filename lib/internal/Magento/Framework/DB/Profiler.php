<?php
/**
 * Magento profiler for requests to database
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB;

/**
 * Class \Magento\Framework\DB\Profiler
 *
 * @since 2.0.0
 */
class Profiler extends \Zend_Db_Profiler
{
    /**
     * Host IP whereto a request is sent
     *
     * @var string
     * @since 2.0.0
     */
    protected $_host = '';

    /**
     * Database connection type
     *
     * @var string
     * @since 2.0.0
     */
    protected $_type = '';

    /**
     * Last query Id
     *
     * @var string|null
     * @since 2.0.0
     */
    private $_lastQueryId = null;

    /**
     * Setter for host IP
     *
     * @param string $host
     * @return \Magento\Framework\DB\Profiler
     * @since 2.0.0
     */
    public function setHost($host)
    {
        $this->_host = $host;
        return $this;
    }

    /**
     * Setter for database connection type
     *
     * @param string $type
     * @return \Magento\Framework\DB\Profiler
     * @since 2.0.0
     */
    public function setType($type)
    {
        $this->_type = $type;
        return $this;
    }

    /**
     * Starts a query. Creates a new query profile object (\Zend_Db_Profiler_Query)
     *
     * @param string $queryText SQL statement
     * @param integer|null $queryType OPTIONAL Type of query, one of the \Zend_Db_Profiler::* constants
     * @return integer|null
     * @since 2.0.0
     */
    public function queryStart($queryText, $queryType = null)
    {
        $this->_lastQueryId = parent::queryStart($queryText, $queryType);
        return $this->_lastQueryId;
    }

    /**
     * Ends a query. Pass it the handle that was returned by queryStart().
     *
     * @param int $queryId
     * @return string|void
     * @since 2.0.0
     */
    public function queryEnd($queryId)
    {
        $this->_lastQueryId = null;
        return parent::queryEnd($queryId);
    }

    /**
     * Ends the last query if exists. Used for finalize broken queries.
     *
     * @return string|void
     * @since 2.0.0
     */
    public function queryEndLast()
    {
        if ($this->_lastQueryId !== null) {
            return $this->queryEnd($this->_lastQueryId);
        }

        return self::IGNORED;
    }
}
