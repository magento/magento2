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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract resource data model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Install\Model\Installer\Db;

abstract class AbstractDb
{
    /**
     * Resource connection adapter factory
     *
     * @var \Magento\Framework\Model\Resource\Type\Db\Pdo\MysqlFactory
     */
    protected $_adapterFactory;

    /**
     * List of necessary extensions for DBs
     *
     * @var array
     */
    protected $_dbExtensions;

    /**
     *  Adapter instance
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $_connection;

    /**
     *  Connection configuration
     *
     * @var array
     */
    protected $_connectionData;

    /**
     *  Connection configuration
     *
     * @var array
     */
    protected $_configData;

    /**
     * Configuration arguments
     *
     * @var \Magento\Framework\App\Arguments
     */
    protected $_arguments;

    /**
     * @param \Magento\Framework\Model\Resource\Type\Db\Pdo\MysqlFactory $adapterFactory
     * @param \Magento\Framework\App\Arguments $arguments
     * @param array $dbExtensions
     */
    public function __construct(
        \Magento\Framework\Model\Resource\Type\Db\Pdo\MysqlFactory $adapterFactory,
        \Magento\Framework\App\Arguments $arguments,
        array $dbExtensions = array()
    ) {
        $this->_adapterFactory = $adapterFactory;
        $this->_dbExtensions = $dbExtensions;
        $this->_arguments = $arguments;
    }

    /**
     * Return the name of DB model from config
     *
     * @return string
     */
    public function getModel()
    {
        return $this->_configData['db_model'];
    }

    /**
     * Return the DB type from config
     *
     * @return string
     */
    public function getType()
    {
        return $this->_configData['db_type'];
    }

    /**
     * Set configuration data
     *
     * @param array $config the connection configuration
     * @return void
     */
    public function setConfig($config)
    {
        $this->_configData = $config;
    }

    /**
     * Retrieve connection data from config
     *
     * @return array
     */
    public function getConnectionData()
    {
        if (!$this->_connectionData) {
            if ($this->_configData) {
                $connectionData = array(
                    'host' => $this->_configData['db_host'],
                    'username' => $this->_configData['db_user'],
                    'password' => $this->_configData['db_pass'],
                    'dbName' => $this->_configData['db_name'],
                    'pdoType' => $this->getPdoType()
                );
            } else {
                $default = $this->_arguments->getConnection('default');
                $connectionData = array(
                    'host' => $default['host'],
                    'username' => $default['username'],
                    'password' => $default['password'],
                    'dbName' => $default['dbName'],
                    'pdoType' => $this->getPdoType()
                );
            }
            $this->_connectionData = $connectionData;
        }
        return $this->_connectionData;
    }

    /**
     * Check InnoDB support
     *
     * @return bool
     */
    public function supportEngine()
    {
        return true;
    }

    /**
     * Create new connection with custom config
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        if (!isset($this->_connection)) {
            $connection = $this->_adapterFactory->create($this->getConnectionData())->getConnection();
            $this->_connection = $connection;
        }
        return $this->_connection;
    }

    /**
     * Return pdo type
     *
     * @return null
     */
    public function getPdoType()
    {
        return null;
    }

    /**
     * Retrieve required PHP extension list for database
     *
     * @return array
     */
    public function getRequiredExtensions()
    {
        return isset($this->_dbExtensions[$this->getModel()]) ? $this->_dbExtensions[$this->getModel()] : array();
    }

    /**
     * Clean up database
     *
     * @return void
     */
    abstract public function cleanUpDatabase();
}
