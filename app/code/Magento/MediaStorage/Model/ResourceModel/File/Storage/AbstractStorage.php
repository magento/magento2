<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\ResourceModel\File\Storage;

/**
 * Class AbstractStorage
 */
abstract class AbstractStorage extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * @todo: make method protected
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
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
