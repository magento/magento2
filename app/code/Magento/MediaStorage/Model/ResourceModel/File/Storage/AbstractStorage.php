<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\ResourceModel\File\Storage;

/**
 * Class AbstractStorage
 * @since 2.0.0
 */
abstract class AbstractStorage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * File storage connection name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_connectionName = null;

    /**
     * Sets name of connection the resource will use
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setConnectionName($name)
    {
        $this->_connectionName = $name;
        return $this;
    }

    /**
     * Retrieve connection for read data
     *
     * @todo: make method protected
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    public function getConnection()
    {
        return $this->_getConnection($this->_connectionName);
    }

    /**
     * Get connection by name or type
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @since 2.0.0
     */
    protected function _getConnection($resourceName)
    {
        if (isset($this->_connections[$resourceName])) {
            return $this->_connections[$resourceName];
        }

        $this->_connections[$resourceName] = $this->_resources->getConnection($resourceName);

        return $this->_connections[$resourceName];
    }
}
