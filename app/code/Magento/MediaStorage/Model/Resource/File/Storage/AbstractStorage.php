<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\Resource\File\Storage;

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
        return $this->_getConnection();
    }

    /**
     * Retrieve connection for write data
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getWriteAdapter()
    {
        return $this->_getConnection();
    }

    /**
     * Get connection by name or type
     *
     * @param string $resourceName
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected function _getConnection()
    {
        return $this->_resources->getConnection($this->_resourcePrefix);
    }
}
