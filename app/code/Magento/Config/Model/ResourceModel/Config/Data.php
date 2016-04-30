<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\ResourceModel\Config;

/**
 * Core config data resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Data extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('core_config_data', 'config_id');
    }

    /**
     * Convert array to comma separated value
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            $this->_checkUnique($object);
        }

        if (is_array($object->getValue())) {
            $object->setValue(join(',', $object->getValue()));
        }
        return parent::_beforeSave($object);
    }

    /**
     * Validate unique configuration data before save
     * Set id to object if exists configuration instead of throw exception
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _checkUnique(\Magento\Framework\Model\AbstractModel $object)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'scope = :scope'
        )->where(
            'scope_id = :scope_id'
        )->where(
            'path = :path'
        );
        $bind = [
            'scope' => $object->getScope(),
            'scope_id' => $object->getScopeId(),
            'path' => $object->getPath(),
        ];

        $configId = $this->getConnection()->fetchOne($select, $bind);
        if ($configId) {
            $object->setId($configId);
        }

        return $this;
    }

    /**
     * Clear Scope data
     *
     * @param string $scopeCode
     * @param int|array $scopeIds
     * @return void
     */
    public function clearScopeData($scopeCode, $scopeIds)
    {
        if (!is_array($scopeIds)) {
            $scopeIds = [$scopeIds];
        }
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['scope = ?' => $scopeCode, 'scope_id IN (?)' => $scopeIds]
        );
    }
}
