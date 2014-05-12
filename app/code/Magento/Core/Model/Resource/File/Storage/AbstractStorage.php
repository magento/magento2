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
