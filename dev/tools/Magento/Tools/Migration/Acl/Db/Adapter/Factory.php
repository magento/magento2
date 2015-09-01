<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Db adapters factory
 */
namespace Magento\Tools\Migration\Acl\Db\Adapter;

class Factory
{
    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Get db adapter
     *
     * @param array $config
     * @param string $type
     * @throws \InvalidArgumentException
     * @return \Magento\Framework\DB\Adapter\Pdo\Mysql
     */
    public function getConnection(array $config, $type = null)
    {
        $connectionClassName = 'Magento\Framework\DB\Adapter\Pdo\Mysql';

        if (false == empty($type)) {
            $connectionClassName = $type;
        }

        if (false == class_exists($connectionClassName, true)) {
            throw new \InvalidArgumentException('Specified adapter not exists: ' . $connectionClassName);
        }

        $connection = $this->_objectManager->create($connectionClassName, ['config' => $config]);
        if (false == $connection instanceof \Magento\Framework\DB\Adapter\Pdo\Mysql) {
            unset($connection);
            throw new \InvalidArgumentException(
                'Specified adapter is not instance of \Magento\Framework\DB\Adapter\Pdo\Mysql'
            );
        }
        return $connection;
    }
}
