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
 * @category    Magento
 * @package     Magento_Eav
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * EAV attribute resource model
 *
 * @category    Magento
 * @package     Magento_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Eav\Model\Resource\Entity;

class Attribute extends \Magento\Core\Model\Resource\Db\AbstractDb
{
    /**
     * Eav Entity attributes cache
     *
     * @var array
     */
    protected static $_entityAttributes = array();

    /**
     * Application instance
     *
     * @var \Magento\Core\Model\App
     */
    protected $_application;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_app;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Type
     */
    protected $_eavEntityType;

    /**
     * Class constructor
     *
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Core\Model\App $app
     * @param \Magento\Eav\Model\Resource\Entity\Type $eavEntityType
     * @param array $arguments
     */
    public function __construct(
        \Magento\Core\Model\Resource $resource,
        \Magento\Core\Model\App $app,
        \Magento\Eav\Model\Resource\Entity\Type $eavEntityType,
        array $arguments = array()
    ) {
        if (isset($arguments['application']) && $arguments['application'] instanceof \Magento\Core\Model\App) {
            $this->_application = $arguments['application'];
            unset($arguments['application']);
        }
        $this->_app = $app;
        $this->_eavEntityType = $eavEntityType;
        parent::__construct($resource);
    }

    /**
     * Define main table
     *
     */
    protected function _construct()
    {
        $this->_init('eav_attribute', 'attribute_id');
    }

    /**
     * Retrieve application instance
     *
     * @return \Magento\Core\Model\App
     */
    protected function _getApplication()
    {
        return $this->_application ?: $this->_app;
    }

    /**
     * Initialize unique fields
     *
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = array(array(
            'field' => array('attribute_code', 'entity_type_id'),
            'title' => __('Attribute with the same code')
        ));
        return $this;
    }

    /**
     * Load all entity type attributes
     *
     * @param int $entityTypeId
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
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
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @param int $entityTypeId
     * @param string $code
     * @return bool
     */
    public function loadByCode(\Magento\Core\Model\AbstractModel $object, $entityTypeId, $code)
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
     * @param \Magento\Core\Model\AbstractModel $object
     * @return int
     */
    private function _getMaxSortOrder(\Magento\Core\Model\AbstractModel $object)
    {
        if (intval($object->getAttributeGroupId()) > 0) {
            $adapter = $this->_getReadAdapter();
            $bind = array(
                ':attribute_set_id'   => $object->getAttributeSetId(),
                ':attribute_group_id' => $object->getAttributeGroupId()
            );
            $select = $adapter->select()
                ->from($this->getTable('eav_entity_attribute'), new \Zend_Db_Expr("MAX(sort_order)"))
                ->where('attribute_set_id = :attribute_set_id')
                ->where('attribute_group_id = :attribute_group_id');

            return $adapter->fetchOne($select, $bind);
        }

        return 0;
    }

    /**
     * Delete entity
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    public function deleteEntity(\Magento\Core\Model\AbstractModel $object)
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
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _beforeSave(\Magento\Core\Model\AbstractModel $object)
    {
        $frontendLabel = $object->getFrontendLabel();
        if (is_array($frontendLabel)) {
            if (!isset($frontendLabel[0]) || is_null($frontendLabel[0]) || $frontendLabel[0] == '') {
                throw new \Magento\Core\Exception(__('Frontend label is not defined'));
            }
            $object->setFrontendLabel($frontendLabel[0])
                   ->setStoreLabels($frontendLabel);
        }

        /**
         * @todo need use default source model of entity type !!!
         */
        if (!$object->getId()) {
            if ($object->getFrontendInput() == 'select') {
                $object->setSourceModel('Magento\Eav\Model\Entity\Attribute\Source\Table');
            }
        }

        return parent::_beforeSave($object);
    }

    /**
     * Save additional attribute data after save attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _afterSave(\Magento\Core\Model\AbstractModel $object)
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
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _saveStoreLabels(\Magento\Core\Model\AbstractModel $object)
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
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _saveAdditionalAttributeData(\Magento\Core\Model\AbstractModel $object)
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
     * @param \Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    public function saveInSetIncluding(\Magento\Core\Model\AbstractModel $object)
    {
        $attributeId = (int)$object->getId();
        $setId       = (int)$object->getAttributeSetId();
        $groupId     = (int)$object->getAttributeGroupId();

        if ($setId && $groupId && $object->getEntityTypeId()) {
            $adapter = $this->_getWriteAdapter();
            $table   = $this->getTable('eav_entity_attribute');

            $sortOrder = $object->getSortOrder() ?: $this->_getMaxSortOrder($object) + 1;
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
     * Save attribute options
     *
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _saveOption(\Magento\Core\Model\AbstractModel $object)
    {
        $option = $object->getOption();
        if (!is_array($option)) {
            return $this;
        }

        $defaultValue = $object->getDefault() ?: array();
        if (isset($option['value'])) {
            if (!is_array($object->getDefault())) {
                $object->setDefault(array());
            }
            $defaultValue = $this->_processAttributeOptions($object, $option);
        }

        $this->_saveDefaultValue($object, $defaultValue);
        return $this;
    }

    /**
     * Save changes of attribute options, return obtained default value
     *
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @param array $option
     * @return array
     */
    protected function _processAttributeOptions($object, $option)
    {
        $defaultValue = array();
        foreach ($option['value'] as $optionId => $values) {
            $intOptionId = $this->_updateAttributeOption($object, $optionId, $option);
            if ($intOptionId === false) {
                continue;
            }
            $this->_updateDefaultValue($object, $optionId, $intOptionId, $defaultValue);
            $this->_checkDefaultOptionValue($values);
            $this->_updateAttributeOptionValues($intOptionId, $values);
        }
        return $defaultValue;
    }

    /**
     * Check default option value presence
     *
     * @param array $values
     * @throws \Magento\Core\Exception
     */
    protected function _checkDefaultOptionValue($values)
    {
        if (!isset($values[0])) {
            throw new \Magento\Core\Exception(__('Default option value is not defined'));
        }
    }

    /**
     * Update attribute default value
     *
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @param int|string $optionId
     * @param int $intOptionId
     * @param array $defaultValue
     */
    protected function _updateDefaultValue($object, $optionId, $intOptionId, &$defaultValue)
    {
        if (in_array($optionId, $object->getDefault())) {
            $frontendInput = $object->getFrontendInput();
            if ($frontendInput === 'multiselect') {
                $defaultValue[] = $intOptionId;
            } elseif ($frontendInput === 'select') {
                $defaultValue = array($intOptionId);
            }
        }
    }

    /**
     * Save attribute default value
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param array $defaultValue
     */
    protected function _saveDefaultValue($object, $defaultValue)
    {
        if ($defaultValue !== null) {
            $bind = array('default_value' => implode(',', $defaultValue));
            $where = array('attribute_id = ?' => $object->getId());
            $this->_getWriteAdapter()->update($this->getMainTable(), $bind, $where);
        }
    }

    /**
     * Save option records
     *
     * @param \Magento\Core\Model\AbstractModel $object
     * @param int $optionId
     * @param array $option
     * @return int|bool
     */
    protected function _updateAttributeOption($object, $optionId, $option)
    {
        $adapter = $this->_getWriteAdapter();
        $table = $this->getTable('eav_attribute_option');
        $intOptionId = (int)$optionId;

        if (!empty($option['delete'][$optionId])) {
            if ($intOptionId) {
                $adapter->delete($table, array('option_id = ?' => $intOptionId));
            }
            return false;
        }

        $sortOrder = empty($option['order'][$optionId]) ? 0 : $option['order'][$optionId];
        if (!$intOptionId) {
            $data = array(
                'attribute_id' => $object->getId(),
                'sort_order' => $sortOrder
            );
            $adapter->insert($table, $data);
            $intOptionId = $adapter->lastInsertId($table);
        } else {
            $data  = array('sort_order' => $sortOrder);
            $where = array('option_id = ?' => $intOptionId);
            $adapter->update($table, $data, $where);
        }

        return $intOptionId;
    }

    /**
     * Save option values records per store
     *
     * @param int $optionId
     * @param array $values
     */
    protected function _updateAttributeOptionValues($optionId, $values)
    {
        $adapter = $this->_getWriteAdapter();
        $table = $this->getTable('eav_attribute_option_value');

        $adapter->delete($table, array('option_id = ?' => $optionId));

        $stores = $this->_getApplication()->getStores(true);
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!empty($values[$storeId]) || isset($values[$storeId]) && $values[$storeId] == '0') {
                $data = array(
                    'option_id' => $optionId,
                    'store_id' => $storeId,
                    'value' => $values[$storeId],
                );
                $adapter->insert($table, $data);
            }
        }
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
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param int $storeId
     * @return \Magento\DB\Select
     */
    public function getFlatUpdateSelect(\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute, $storeId)
    {
        $adapter = $this->_getReadAdapter();
        $joinConditionTemplate = "%s.entity_id=%s.entity_id"
            ." AND %s.entity_type_id = ".$attribute->getEntityTypeId()
            ." AND %s.attribute_id = ".$attribute->getId()
            ." AND %s.store_id = %d";
        $joinCondition = sprintf($joinConditionTemplate,
            'e', 't1', 't1', 't1', 't1',
            \Magento\Core\Model\AppInterface::ADMIN_STORE_ID);
        if ($attribute->getFlatAddChildData()) {
            $joinCondition .= ' AND e.child_id = t1.entity_id';
        }

        $valueExpr = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');

        /** @var $select \Magento\DB\Select */
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
        return $this->_eavEntityType->getAdditionalAttributeTable($entityTypeId);
    }

    /**
     * Load additional attribute data.
     * Load label of current active store
     *
     * @param \Magento\Eav\Model\Entity\Attribute|\Magento\Core\Model\AbstractModel $object
     * @return \Magento\Eav\Model\Resource\Entity\Attribute
     */
    protected function _afterLoad(\Magento\Core\Model\AbstractModel $object)
    {
        /** @var $entityType \Magento\Eav\Model\Entity\Type */
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
