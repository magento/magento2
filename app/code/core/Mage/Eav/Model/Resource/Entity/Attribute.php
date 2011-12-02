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
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * EAV attribute resource model
 *
 * @category    Mage
 * @package     Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Resource_Entity_Attribute extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Eav Entity attributes cache
     *
     * @var array
     */
    protected static $_entityAttributes     = array();

    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('eav_attribute', 'attribute_id');
    }

    /**
     * Initialize unique fields
     *
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => array('attribute_code', 'entity_type_id'),
            'title' => Mage::helper('Mage_Eav_Helper_Data')->__('Attribute with the same code')
        ));
        return $this;
    }

    /**
     * Load all entity type attributes
     *
     * @param int $entityTypeId
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _loadTypeAttributes($entityTypeId)
    {
        if (!isset(self::$_entityAttributes[$entityTypeId])) {
            $adapter = $this->_getReadAdapter();
            $bind    = array(':entity_type_id' => $entityTypeId);
            $select  = $adapter->select()
                ->from($this->getMainTable())
                ->where('entity_type_id = :entity_type_id');

            $data    = $adapter->fetchAll($select, $bind);
            foreach ($data as $row) {
                self::$_entityAttributes[$entityTypeId][$row['attribute_code']] = $row;
            }
        }

        return $this;
    }

    /**
     * Load attribute data by attribute code
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @param int $entityTypeId
     * @param string $code
     * @return boolean
     */
    public function loadByCode(Mage_Core_Model_Abstract $object, $entityTypeId, $code)
    {
        $bind   = array(':entity_type_id' => $entityTypeId);
        $select = $this->_getLoadSelect('attribute_code', $code, $object)
            ->where('entity_type_id = :entity_type_id');
        $data = $this->_getReadAdapter()->fetchRow($select, $bind);

        if ($data) {
            $object->setData($data);
            $this->_afterLoad($object);
            return true;
        }
        return false;
    }

    /**
     * Retrieve Max Sort order for attribute in group
     *
     * @param Mage_Core_Model_Abstract $object
     * @return int
     */
    private function _getMaxSortOrder(Mage_Core_Model_Abstract $object)
    {
        if (intval($object->getAttributeGroupId()) > 0) {
            $adapter = $this->_getReadAdapter();
            $bind = array(
                ':attribute_set_id'   => $object->getAttributeSetId(),
                ':attribute_group_id' => $object->getAttributeGroupId()
            );
            $select = $adapter->select()
                ->from($this->getTable('eav_entity_attribute'), new Zend_Db_Expr("MAX(sort_order)"))
                ->where('attribute_set_id = :attribute_set_id')
                ->where('attribute_group_id = :attribute_group_id');

            return $adapter->fetchOne($select, $bind);
        }

        return 0;
    }

    /**
     * Delete entity
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    public function deleteEntity(Mage_Core_Model_Abstract $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $this->_getWriteAdapter()->delete($this->getTable('eav_entity_attribute'), array(
            'entity_attribute_id = ?' => $object->getEntityAttributeId()
        ));

        return $this;
    }

    /**
     * Validate attribute data before save
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $frontendLabel = $object->getFrontendLabel();
        if (is_array($frontendLabel)) {
            if (!isset($frontendLabel[0]) || is_null($frontendLabel[0]) || $frontendLabel[0] == '') {
                Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Frontend label is not defined'));
            }
            $object->setFrontendLabel($frontendLabel[0])
                   ->setStoreLabels($frontendLabel);
        }

        /**
         * @todo need use default source model of entity type !!!
         */
        if (!$object->getId()) {
            if ($object->getFrontendInput() == 'select') {
                $object->setSourceModel('Mage_Eav_Model_Entity_Attribute_Source_Table');
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * Save additional attribute data after save attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $this->_saveStoreLabels($object)
             ->_saveAdditionalAttributeData($object)
             ->saveInSetIncluding($object)
             ->_saveOption($object);

        return parent::_afterSave($object);
    }

    /**
     * Save store labels
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _saveStoreLabels(Mage_Core_Model_Abstract $object)
    {
        $storeLabels = $object->getStoreLabels();
        if (is_array($storeLabels)) {
            $adapter = $this->_getWriteAdapter();
            if ($object->getId()) {
                $condition = array('attribute_id =?' => $object->getId());
                $adapter->delete($this->getTable('eav_attribute_label'), $condition);
            }
            foreach ($storeLabels as $storeId => $label) {
                if ($storeId == 0 || !strlen($label)) {
                    continue;
                }
                $bind = array (
                    'attribute_id' => $object->getId(),
                    'store_id'     => $storeId,
                    'value'        => $label
                );
                $adapter->insert($this->getTable('eav_attribute_label'), $bind);
            }
        }

        return $this;
    }

    /**
     * Save additional data of attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _saveAdditionalAttributeData(Mage_Core_Model_Abstract $object)
    {
        $additionalTable = $this->getAdditionalAttributeTable($object->getEntityTypeId());
        if ($additionalTable) {
            $adapter    = $this->_getWriteAdapter();
            $data       = $this->_prepareDataForTable($object, $this->getTable($additionalTable));
            $bind       = array(':attribute_id' => $object->getId());
            $select     = $adapter->select()
                ->from($this->getTable($additionalTable), array('attribute_id'))
                ->where('attribute_id = :attribute_id');
            $result     = $adapter->fetchOne($select, $bind);
            if ($result) {
                $where  = array('attribute_id = ?' => $object->getId());
                $adapter->update($this->getTable($additionalTable), $data, $where);
            } else {
                $adapter->insert($this->getTable($additionalTable), $data);
            }
        }

        return $this;
    }

    /**
     * Save in set including
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    public function saveInSetIncluding(Mage_Core_Model_Abstract $object)
    {
        $attributeId = (int) $object->getId();
        $setId       = (int) $object->getAttributeSetId();
        $groupId     = (int) $object->getAttributeGroupId();

        if ($setId && $groupId && $object->getEntityTypeId()) {
            $adapter = $this->_getWriteAdapter();
            $table   = $this->getTable('eav_entity_attribute');

            $sortOrder = (($object->getSortOrder()) ? $object->getSortOrder() : $this->_getMaxSortOrder($object) + 1);
            $data = array(
                'entity_type_id'     => $object->getEntityTypeId(),
                'attribute_set_id'   => $setId,
                'attribute_group_id' => $groupId,
                'attribute_id'       => $attributeId,
                'sort_order'         => $sortOrder
            );

            $where = array(
                'attribute_id =?'     => $attributeId,
                'attribute_set_id =?' => $setId
            );

            $adapter->delete($table, $where);
            $adapter->insert($table, $data);
        }

        return $this;
    }

    /**
     *  Save attribute options
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _saveOption(Mage_Core_Model_Abstract $object)
    {
        $option = $object->getOption();
        if (is_array($option)) {
            $adapter            = $this->_getWriteAdapter();
            $optionTable        = $this->getTable('eav_attribute_option');
            $optionValueTable   = $this->getTable('eav_attribute_option_value');

            $stores = Mage::app()->getStores(true);
            if (isset($option['value'])) {
                $attributeDefaultValue = array();
                if (!is_array($object->getDefault())) {
                    $object->setDefault(array());
                }

                foreach ($option['value'] as $optionId => $values) {
                    $intOptionId = (int) $optionId;
                    if (!empty($option['delete'][$optionId])) {
                        if ($intOptionId) {
                            $adapter->delete($optionTable, array('option_id = ?' => $intOptionId));
                        }
                        continue;
                    }

                    $sortOrder = !empty($option['order'][$optionId]) ? $option['order'][$optionId] : 0;
                    if (!$intOptionId) {
                        $data = array(
                           'attribute_id'  => $object->getId(),
                           'sort_order'    => $sortOrder
                        );
                        $adapter->insert($optionTable, $data);
                        $intOptionId = $adapter->lastInsertId($optionTable);
                    } else {
                        $data  = array('sort_order'    => $sortOrder);
                        $where = array('option_id =?' => $intOptionId);
                        $adapter->update($optionTable, $data, $where);
                    }

                    if (in_array($optionId, $object->getDefault())) {
                        if ($object->getFrontendInput() == 'multiselect') {
                            $attributeDefaultValue[] = $intOptionId;
                        } elseif ($object->getFrontendInput() == 'select') {
                            $attributeDefaultValue = array($intOptionId);
                        }
                    }

                    // Default value
                    if (!isset($values[0])) {
                        Mage::throwException(Mage::helper('Mage_Eav_Helper_Data')->__('Default option value is not defined'));
                    }

                    $adapter->delete($optionValueTable, array('option_id =?' => $intOptionId));
                    foreach ($stores as $store) {
                        if (isset($values[$store->getId()])
                            && (!empty($values[$store->getId()])
                            || $values[$store->getId()] == "0")
                        ) {
                            $data = array(
                                'option_id' => $intOptionId,
                                'store_id'  => $store->getId(),
                                'value'     => $values[$store->getId()],
                            );
                            $adapter->insert($optionValueTable, $data);
                        }
                    }
                }
                $bind  = array('default_value' => implode(',', $attributeDefaultValue));
                $where = array('attribute_id =?' => $object->getId());
                $adapter->update($this->getMainTable(), $bind, $where);
            }
        }

        return $this;
    }


    /**
     * Retrieve attribute id by entity type code and attribute code
     *
     * @param string $entityType
     * @param string $code
     * @return int
     */
    public function getIdByCode($entityType, $code)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(
            ':entity_type_code' => $entityType,
            ':attribute_code'   => $code
        );
        $select = $adapter->select()
            ->from(array('a' => $this->getTable('eav_attribute')), array('a.attribute_id'))
            ->join(
                array('t' => $this->getTable('eav_entity_type')),
                'a.entity_type_id = t.entity_type_id',
                array())
            ->where('t.entity_type_code = :entity_type_code')
            ->where('a.attribute_code = :attribute_code');

        return $adapter->fetchOne($select, $bind);
    }

    /**
     * Retrieve attribute codes by front-end type
     *
     * @param string $frontendType
     * @return array
     */
    public function getAttributeCodesByFrontendType($frontendType)
    {
        $adapter = $this->_getReadAdapter();
        $bind    = array(':frontend_input' => $frontendType);
        $select  = $adapter->select()
            ->from($this->getTable('eav_attribute'), 'attribute_code')
            ->where('frontend_input = :frontend_input');

        return $adapter->fetchCol($select, $bind);
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param int $storeId
     * @return Varien_Db_Select
     */
    public function getFlatUpdateSelect(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $joinConditionTemplate = "%s.entity_id=%s.entity_id"
            ." AND %s.entity_type_id = ".$attribute->getEntityTypeId()
            ." AND %s.attribute_id = ".$attribute->getId()
            ." AND %s.store_id = %d";
        $joinCondition = sprintf($joinConditionTemplate,
            'e', 't1', 't1', 't1', 't1',
            Mage_Core_Model_App::ADMIN_STORE_ID);
        if ($attribute->getFlatAddChildData()) {
            $joinCondition .= ' AND e.child_id = t1.entity_id';
        }

        $valueExpr = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');

        /** @var $select Varien_Db_Select */
        $select = $adapter->select()
            ->joinLeft(
                array('t1' => $attribute->getBackend()->getTable()),
                $joinCondition,
                array())
            ->joinLeft(
                array('t2' => $attribute->getBackend()->getTable()),
                sprintf($joinConditionTemplate, 't1', 't2', 't2', 't2', 't2', $storeId),
                array($attribute->getAttributeCode() => $valueExpr));
        if ($attribute->getFlatAddChildData()) {
            $select->where("e.is_child = ?", 0);
        }

        return $select;
    }

    /**
     * Returns the column descriptions for a table
     *
     * @param string $table
     * @return array
     */
    public function describeTable($table)
    {
        return $this->_getReadAdapter()->describeTable($table);
    }

    /**
     * Retrieve additional attribute table name for specified entity type
     *
     * @param integer $entityTypeId
     * @return string
     */
    public function getAdditionalAttributeTable($entityTypeId)
    {
        return Mage::getResourceSingleton('Mage_Eav_Model_Resource_Entity_Type')
            ->getAdditionalAttributeTable($entityTypeId);
    }

    /**
     * Load additional attribute data.
     * Load label of current active store
     *
     * @param Mage_Eav_Model_Entity_Attribute $object
     * @return Mage_Eav_Model_Resource_Entity_Attribute
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        /** @var $entityType Mage_Eav_Model_Entity_Type */
        $entityType = $object->getData('entity_type');
        if ($entityType) {
            $additionalTable = $entityType->getAdditionalAttributeTable();
        } else {
            $additionalTable = $this->getAdditionalAttributeTable($object->getEntityTypeId());
        }

        if ($additionalTable) {
            $adapter = $this->_getReadAdapter();
            $bind    = array(':attribute_id' => $object->getId());
            $select  = $adapter->select()
                ->from($this->getTable($additionalTable))
                ->where('attribute_id = :attribute_id');

            $result = $adapter->fetchRow($select, $bind);
            if ($result) {
                $object->addData($result);
            }
        }

        return $this;
    }

    /**
     * Retrieve store labels by given attribute id
     *
     * @param integer $attributeId
     * @return array
     */
    public function getStoreLabelsByAttributeId($attributeId)
    {
        $adapter   = $this->_getReadAdapter();
        $bind      = array(':attribute_id' => $attributeId);
        $select    = $adapter->select()
            ->from($this->getTable('eav_attribute_label'), array('store_id', 'value'))
            ->where('attribute_id = :attribute_id');

        return $adapter->fetchPairs($select, $bind);
    }

    /**
     * Load by given attributes ids and return only exist attribute ids
     *
     * @param array $attributeIds
     * @return array
     */
    public function getValidAttributeIds($attributeIds)
    {
        $adapter   = $this->_getReadAdapter();
        $select    = $adapter->select()
            ->from($this->getMainTable(), array('attribute_id'))
            ->where('attribute_id IN (?)', $attributeIds);

        return $adapter->fetchCol($select);
    }
}
