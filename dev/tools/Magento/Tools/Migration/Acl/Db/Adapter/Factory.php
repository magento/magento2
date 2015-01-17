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
     * @return \Zend_Db_Adapter_Abstract
     */
    public function getAdapter(array $config, $type = null)
    {
        $dbAdapterClassName = 'Magento\Framework\DB\Adapter\Pdo\Mysql';

        if (false == empty($type)) {
            $dbAdapterClassName = $type;
        }

        if (false == class_exists($dbAdapterClassName, true)) {
            throw new \InvalidArgumentException('Specified adapter not exists: ' . $dbAdapterClassName);
        }

        $adapter = $this->_objectManager->create($dbAdapterClassName, ['config' => $config]);
        if (false == $adapter instanceof \Zend_Db_Adapter_Abstract) {
            unset($adapter);
            throw new \InvalidArgumentException('Specified adapter is not instance of \Zend_Db_Adapter_Abstract');
        }
        return $adapter;
    }
}
