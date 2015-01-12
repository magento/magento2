<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Core\Model\Resource\File\Storage;

/**
 * Class AbstractStorage
 */
abstract class AbstractStorage extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * File storage connection name
     *
     * @var string
     */
    protected $_connectionName = null;

    /**
     * Sets name of connection the resource will use
     *
     * @param string $name
     * @return $this
     */
    public function setConnectionName($name)
    {
        $this->_connectionName = $name;
        return $this;
    }

    /**
     * Retrieve connection for read data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getReadAdapter()
    {
        return $this->_getConnection($this->_connectionName);
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getWriteAdapter()
    {
        return $this->_getConnection($this->_connectionName);
    }

    /**
     * Get connection by name or type
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
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
