<?php
/**
 * Magento profiler for requests to database
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\DB;

class Profiler extends \Zend_Db_Profiler
{
    /**
     * Host IP whereto a request is sent
     *
     * @var string
     */
    protected $_host = '';

    /**
     * Database connection type
     *
     * @var string
     */
    protected $_type = '';

    /**
     * Last query Id
     *
     * @var string|null
     */
    private $_lastQueryId = null;

    /**
     * Setter for host IP
     *
     * @param string $host
     * @return \Magento\Framework\DB\Profiler
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
     */
    public function queryEndLast()
    {
        if ($this->_lastQueryId !== null) {
            return $this->queryEnd($this->_lastQueryId);
        }

        return self::IGNORED;
    }
}
