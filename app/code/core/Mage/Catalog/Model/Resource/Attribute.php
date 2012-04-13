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
 * @category    Mage
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog attribute resource model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Resource_Attribute extends Mage_Eav_Model_Resource_Entity_Attribute
{
    /**
     * Perform actions before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Catalog_Model_Resource_Attribute
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $applyTo = $object->getApplyTo();
        if (is_array($applyTo)) {
            $object->setApplyTo(implode(',', $applyTo));
        }
        return parent::_beforeSave($object);
    }

    /**
     * Perform actions after object save
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Catalog_Model_Resource_Attribute
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_clearUselessAttributeValues($object);
        return parent::_afterSave($object);
    }

    /**
     * Clear useless attribute values
     *
     * @param  Mage_Core_Model_Abstract $object
     * @return Mage_Catalog_Model_Resource_Attribute
     */
    protected function _clearUselessAttributeValues(Mage_Core_Model_Abstract $object)
    {
        $origData = $object->getOrigData();

        if ($object->isScopeGlobal()
            && isset($origData['is_global'])
            && Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL != $origData['is_global']
        ) {
            $attributeStoreIds = array_keys(Mage::app()->getStores());
            if (!empty($attributeStoreIds)) {
                $delCondition = array(
                    'entity_type_id=?' => $object->getEntityTypeId(),
                    'attribute_id = ?' => $object->getId(),
                    'store_id IN(?)'   => $attributeStoreIds
                );
                $this->_getWriteAdapter()->delete($object->getBackendTable(), $delCondition);
            }
        }

        return $this;
    }

    /**
     * Delete entity
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Catalog_Model_Resource_Attribute
     */
    public function deleteEntity(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('eav_entity_attribute'))
            ->where('entity_attribute_id = ?', (int)$object->getEntityAttributeId());
        $result = $this->_getReadAdapter()->fetchRow($select);

        if ($result) {
            $attribute = Mage::getSingleton('Mage_Eav_Model_Config')
                ->getAttribute(Mage_Catalog_Model_Product::ENTITY, $result['attribute_id']);

            if ($this->isUsedBySuperProducts($attribute, $result['attribute_set_id'])) {
                Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__("Attribute '%s' used in configurable products", $attribute->getAttributeCode()));
            }
            $backendTable = $attribute->getBackend()->getTable();
            if ($backendTable) {
                $select = $this->_getWriteAdapter()->select()
                    ->from($attribute->getEntity()->getEntityTable(), 'entity_id')
                    ->where('attribute_set_id = ?', $result['attribute_set_id']);

                $clearCondition = array(
                    'entity_type_id =?' => $attribute->getEntityTypeId(),
                    'attribute_id =?'   => $attribute->getId(),
                    'entity_id IN (?)'  => $select
                );
                $this->_getWriteAdapter()->delete($backendTable, $clearCondition);
            }
        }

        $condition = array('entity_attribute_id = ?' => $object->getEntityAttributeId());
        $this->_getWriteAdapter()->delete($this->getTable('eav_entity_attribute'), $condition);

        return $this;
    }

    /**
     * Defines is Attribute used by super products
     *
     * @param Mage_Core_Model_Abstract $object
     * @param int $attributeSet
     * @return int
     */
    public function isUsedBySuperProducts(Mage_Core_Model_Abstract $object, $attributeSet = null)
    {
        $adapter      = $this->_getReadAdapter();
        $attrTable    = $this->getTable('catalog_product_super_attribute');
        $productTable = $this->getTable('catalog_product_entity');

        $bind = array('attribute_id' => $object->getAttributeId());
        $select = clone $adapter->select();
        $select->reset()
            ->from(array('main_table' => $attrTable), array('psa_count' => 'COUNT(product_super_attribute_id)'))
            ->join(array('entity' => $productTable), 'main_table.product_id = entity.entity_id')
            ->where('main_table.attribute_id = :attribute_id')
            ->group('main_table.attribute_id')
            ->limit(1);

        if ($attributeSet !== null) {
            $bind['attribute_set_id'] = $attributeSet;
            $select->where('entity.attribute_set_id = :attribute_set_id');
        }

        $helper = Mage::getResourceHelper('Mage_Core');
        $query  = $helper->getQueryUsingAnalyticFunction($select);
        return $adapter->fetchOne($query, $bind);
    }
}
