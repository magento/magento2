<?php
/**
 * Base Resource Setup Model
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
namespace Magento\Framework\Module;

class Setup
{
    /**
     * Setup Connection
     *
     * @var \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    protected $_connection = null;

    /**
     * Tables cache array
     *
     * @var array
     */
    protected $_tables = array();

    /**
     * Modules configuration
     *
     * @var \Magento\Framework\App\Resource
     */
    protected $_resourceModel;

    /**
     * Connection instance name
     *
     * @var string
     */
    protected $_connectionName;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->_resourceModel = $resource;
        $this->_connectionName = $connectionName;
    }

    /**
     * Get connection object
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    public function getConnection()
    {
        if (null === $this->_connection) {
            $this->_connection = $this->_resourceModel->getConnection($this->_connectionName);
        }
        return $this->_connection;
    }

    /**
     * Add table placeholder/table name relation
     *
     * @param string $tableName
     * @param string $realTableName
     * @return $this
     */
    public function setTable($tableName, $realTableName)
    {
        $this->_tables[$tableName] = $realTableName;
        return $this;
    }

    /**
     * Get table name (validated by db adapter) by table placeholder
     *
     * @param string|array $tableName
     * @return string
     */
    public function getTable($tableName)
    {
        $cacheKey = $this->_getTableCacheName($tableName);
        if (!isset($this->_tables[$cacheKey])) {
            $this->_tables[$cacheKey] = $this->_resourceModel->getTableName($tableName);
        }
        return $this->_tables[$cacheKey];
    }

    /**
     * Retrieve table name for cache
     *
     * @param string|array $tableName
     * @return string
     */
    protected function _getTableCacheName($tableName)
    {
        if (is_array($tableName)) {
            return join('_', $tableName);
        }
        return $tableName;
    }

    /**
     * Check is table exists
     *
     * @param string $table
     * @return bool
     */
    public function tableExists($table)
    {
        $table = $this->getTable($table);
        return $this->getConnection()->isTableExists($table);
    }

    /**
     * Run plain SQL query(ies)
     *
     * @param string $sql
     * @return $this
     */
    public function run($sql)
    {
        $this->getConnection()->multiQuery($sql);
        return $this;
    }

    /**
     * Prepare database before install/upgrade
     *
     * @return $this
     */
    public function startSetup()
    {
        $this->getConnection()->startSetup();
        return $this;
    }

    /**
     * Prepare database after install/upgrade
     *
     * @return $this
     */
    public function endSetup()
    {
        $this->getConnection()->endSetup();
        return $this;
    }
}
