<?php
/**
 * Magento profiler for requests to database
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db;

class Profiler extends \Magento\Framework\DB\Profiler
{
    /**
     * Default connection type for timer name creation
     */
    const TIMER_PREFIX = 'DB_QUERY';

    /**
     * Default connection type for timer name creation
     */
    const DEFAULT_CONNECTION_TYPE = 'database';

    /**
     * @var array Allowed query types
     */
    protected $_queryTypes = ['select', 'insert', 'update', 'delete'];

    /**
     * Form and return timer name
     *
     * @param string $operation
     * @return string
     */
    protected function _getTimerName($operation)
    {
        // default name of connection type
        $timerName = \Magento\Framework\Model\ResourceModel\Db\Profiler::DEFAULT_CONNECTION_TYPE;

        // connection type to database
        if (!empty($this->_type)) {
            $timerName = $this->_type;
        }

        // sql operation
        $timerName .= '_' . $operation;

        // database host
        if (!empty($this->_host)) {
            $timerName .= '_' . $this->_host;
        }

        return \Magento\Framework\Model\ResourceModel\Db\Profiler::TIMER_PREFIX . ':' . $timerName;
    }

    /**
     * Parse query type and return
     *
     * @param string $queryText
     * @return string
     */
    protected function _parseQueryType($queryText)
    {
        $queryTypeParsed = strtolower(substr(ltrim($queryText), 0, 6));

        if (!in_array($queryTypeParsed, $this->_queryTypes)) {
            $queryTypeParsed = 'query';
        }

        return $queryTypeParsed;
    }

    /**
     * Starts a query. Creates a new query profile object (\Zend_Db_Profiler_Query)
     *
     * @param string $queryText SQL statement
     * @param integer $queryType OPTIONAL Type of query, one of the \Zend_Db_Profiler::* constants
     * @return integer|null
     */
    public function queryStart($queryText, $queryType = null)
    {
        $result = parent::queryStart($queryText, $queryType);

        if ($result !== null) {
            $queryTypeParsed = $this->_parseQueryType($queryText);
            $timerName = $this->_getTimerName($queryTypeParsed);

            $tags = [];

            // connection type to database
            $typePrefix = '';
            if ($this->_type) {
                $tags['group'] = $this->_type;
                $typePrefix = $this->_type . ':';
            }

            // sql operation
            $tags['operation'] = $typePrefix . $queryTypeParsed;

            // database host
            if ($this->_host) {
                $tags['host'] = $this->_host;
            }

            \Magento\Framework\Profiler::start($timerName, $tags);
        }

        return $result;
    }

    /**
     * Ends a query. Pass it the handle that was returned by queryStart().
     *
     * @param int $queryId
     * @return string|void
     */
    public function queryEnd($queryId)
    {
        $result = parent::queryEnd($queryId);

        if ($result != self::IGNORED) {
            /** @var \Zend_Db_Profiler_Query $queryProfile */
            $queryProfile = $this->_queryProfiles[$queryId];
            $queryTypeParsed = $this->_parseQueryType($queryProfile->getQuery());
            $timerName = $this->_getTimerName($queryTypeParsed);

            \Magento\Framework\Profiler::stop($timerName);
        }

        return $result;
    }
}
