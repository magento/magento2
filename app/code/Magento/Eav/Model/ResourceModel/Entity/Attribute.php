<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\ResourceModel\Entity;

use Magento\Eav\Model\Entity\Attribute as EntityAttribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\AttributeCache;
use Magento\Framework\DB\Select;
use Magento\Framework\Model\AbstractModel;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ObjectManager;

/**
 * EAV attribute resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Attribute extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Eav Entity attributes cache
     *
     * @var array
     */
    protected static $_entityAttributes = [];

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var Type
     */
    protected $_eavEntityType;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AttributeCache
     */
    private $attributeCache;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Type $eavEntityType
     * @param string $connectionName
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Type $eavEntityType,
        $connectionName = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_eavEntityType = $eavEntityType;
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init('eav_attribute', 'attribute_id');
    }

    /**
     * Initialize unique fields
     *
     * @return $this
     * @codeCoverageIgnore
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            ['field' => ['attribute_code', 'entity_type_id'], 'title' => __('Attribute with the same code')],
        ];
        return $this;
    }

    /**
     * Load attribute data by attribute code
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @param int $entityTypeId
     * @param string $code
     * @return bool
     */
    public function loadByCode(AbstractModel $object, $entityTypeId, $code)
    {
        $bind = [':entity_type_id' => $entityTypeId];
        $select = $this->_getLoadSelect('attribute_code', $code, $object)->where('entity_type_id = :entity_type_id');
        $data = $this->getConnection()->fetchRow($select, $bind);

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
     * @param AbstractModel $object
     * @return int
     */
    private function _getMaxSortOrder(AbstractModel $object)
    {
        if (intval($object->getAttributeGroupId()) > 0) {
            $connection = $this->getConnection();
            $bind = [
                ':attribute_set_id' => $object->getAttributeSetId(),
                ':attribute_group_id' => $object->getAttributeGroupId(),
            ];
            $select = $connection->select()->from(
                $this->getTable('eav_entity_attribute'),
                new \Zend_Db_Expr("MAX(sort_order)")
            )->where(
                'attribute_set_id = :attribute_set_id'
            )->where(
                'attribute_group_id = :attribute_group_id'
            );

            return $connection->fetchOne($select, $bind);
        }

        return 0;
    }

    /**
     * Delete entity
     *
     * @param \Magento\Framework\Model\AbstractMode $object
     * @return $this
     */
    public function deleteEntity(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getEntityAttributeId()) {
            return $this;
        }

        $this->getConnection()->delete(
            $this->getTable('eav_entity_attribute'),
            ['entity_attribute_id = ?' => $object->getEntityAttributeId()]
        );

        return $this;
    }

    /**
     * Validate attribute data before save
     *
     * @param EntityAttribute|AbstractModel $object
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $frontendLabel = $object->getFrontendLabel();
        if (is_array($frontendLabel)) {
            if (!isset($frontendLabel[0]) || $frontendLabel[0] === null || $frontendLabel[0] == '') {
                throw new \Magento\Framework\Exception\LocalizedException(__('The storefront label is not defined.'));
            }
            $object->setFrontendLabel($frontendLabel[0])->setStoreLabels($frontendLabel);
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
     * @param EntityAttribute|AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->_saveStoreLabels(
            $object
        )->_saveAdditionalAttributeData(
            $object
        )->saveInSetIncluding(
            $object
        )->_saveOption(
            $object
        );
        $this->getConfig()->clear();
        $this->getAttributeCache()->clear();
        return parent::_afterSave($object);
    }

    /**
     * Perform actions after object delete
     *
     * @param \Magento\Framework\Model\AbstractModel|\Magento\Framework\DataObject $object
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->getConfig()->clear();
        $this->getAttributeCache()->clear();
        return $this;
    }

    /**
     * @return AttributeCache
     * @deprecated
     */
    private function getAttributeCache()
    {
        if (!$this->attributeCache) {
            $this->attributeCache = ObjectManager::getInstance()->get(Config::class);
        }
        return $this->attributeCache;
    }

    /**
     * @return Config
     * @deprecated
     */
    private function getConfig()
    {
        if (!$this->config) {
            $this->config = ObjectManager::getInstance()->get(Config::class);
        }
        return $this->config;
    }

    /**
     * Save store labels
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveStoreLabels(AbstractModel $object)
    {
        $storeLabels = $object->getStoreLabels();
        if (is_array($storeLabels)) {
            $connection = $this->getConnection();
            if ($object->getId()) {
                $condition = ['attribute_id =?' => $object->getId()];
                $connection->delete($this->getTable('eav_attribute_label'), $condition);
            }
            foreach ($storeLabels as $storeId => $label) {
                if ($storeId == 0 || !strlen($label)) {
                    continue;
                }
                $bind = ['attribute_id' => $object->getId(), 'store_id' => $storeId, 'value' => $label];
                $connection->insert($this->getTable('eav_attribute_label'), $bind);
            }
        }

        return $this;
    }

    /**
     * Save additional data of attribute
     *
     * @param EntityAttribute|\Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _saveAdditionalAttributeData(AbstractModel $object)
    {
        $additionalTable = $this->getAdditionalAttributeTable($object->getEntityTypeId());
        if ($additionalTable) {
            $connection = $this->getConnection();
            $data = $this->_prepareDataForTable($object, $this->getTable($additionalTable));
            $bind = [':attribute_id' => $object->getId()];
            $select = $connection->select()->from(
                $this->getTable($additionalTable),
                ['attribute_id']
            )->where(
                'attribute_id = :attribute_id'
            );
            $result = $connection->fetchOne($select, $bind);
            if ($result) {
                $where = ['attribute_id = ?' => $object->getId()];
                $connection->update($this->getTable($additionalTable), $data, $where);
            } else {
                $connection->insert($this->getTable($additionalTable), $data);
            }
        }
        return $this;
    }

    /**
     * Save in set including
     *
     * @param AbstractModel $object
     * @param null $attributeEntityId
     * @param null $attributeSetId
     * @param null $attributeGroupId
     * @param null $attributeSortOrder
     * @return $this
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function saveInSetIncluding(
        AbstractModel $object,
        $attributeEntityId = null,
        $attributeSetId = null,
        $attributeGroupId = null,
        $attributeSortOrder = null
    ) {
        $attributeId = $attributeEntityId === null ? (int)$object->getId() : (int)$attributeEntityId;
        $setId = $attributeSetId === null ? (int)$object->getAttributeSetId() : (int)$attributeSetId;
        $groupId = $attributeGroupId === null ? (int)$object->getAttributeGroupId() : (int)$attributeGroupId;
        $attributeSortOrder = $attributeSortOrder === null ? (int)$object->getSortOrder() : (int)$attributeSortOrder;

        if ($setId && $groupId && $object->getEntityTypeId()) {
            $connection = $this->getConnection();
            $table = $this->getTable('eav_entity_attribute');

            $sortOrder = $attributeSortOrder ?: $this->_getMaxSortOrder($object) + 1;
            $data = [
                'entity_type_id' => $object->getEntityTypeId(),
                'attribute_set_id' => $setId,
                'attribute_group_id' => $groupId,
                'attribute_id' => $attributeId,
                'sort_order' => $sortOrder,
            ];

            $where = ['attribute_id =?' => $attributeId, 'attribute_set_id =?' => $setId];

            $connection->delete($table, $where);
            $connection->insert($table, $data);
        }
        return $this;
    }

    /**
     * Save attribute options
     *
     * @param EntityAttribute|AbstractModel $object
     * @return $this
     */
    protected function _saveOption(AbstractModel $object)
    {
        $option = $object->getOption();
        if (!is_array($option)) {
            return $this;
        }

        $defaultValue = $object->getDefault() ?: [];
        if (isset($option['value'])) {
            if (!is_array($object->getDefault())) {
                $object->setDefault([]);
            }
            $defaultValue = $this->_processAttributeOptions($object, $option);
        }

        $this->_saveDefaultValue($object, $defaultValue);
        return $this;
    }

    /**
     * Save changes of attribute options, return obtained default value
     *
     * @param EntityAttribute|AbstractModel $object
     * @param array $option
     * @return array
     */
    protected function _processAttributeOptions($object, $option)
    {
        $defaultValue = [];
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
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _checkDefaultOptionValue($values)
    {
        if (!isset($values[0])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Default option value is not defined'));
        }
    }

    /**
     * Update attribute default value
     *
     * @param EntityAttribute|AbstractModel $object
     * @param int|string $optionId
     * @param int $intOptionId
     * @param array $defaultValue
     * @return void
     */
    protected function _updateDefaultValue($object, $optionId, $intOptionId, &$defaultValue)
    {
        if (in_array($optionId, $object->getDefault())) {
            $frontendInput = $object->getFrontendInput();
            if ($frontendInput === 'multiselect') {
                $defaultValue[] = $intOptionId;
            } elseif ($frontendInput === 'select') {
                $defaultValue = [$intOptionId];
            }
        }
    }

    /**
     * Save attribute default value
     *
     * @param AbstractModel $object
     * @param array $defaultValue
     * @return void
     */
    protected function _saveDefaultValue($object, $defaultValue)
    {
        if ($defaultValue !== null) {
            $bind = ['default_value' => implode(',', $defaultValue)];
            $where = ['attribute_id = ?' => $object->getId()];
            $this->getConnection()->update($this->getMainTable(), $bind, $where);
        }
    }

    /**
     * Save option records
     *
     * @param AbstractModel $object
     * @param int $optionId
     * @param array $option
     * @return int|bool
     */
    protected function _updateAttributeOption($object, $optionId, $option)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('eav_attribute_option');
        $intOptionId = (int)$optionId;

        if (!empty($option['delete'][$optionId])) {
            if ($intOptionId) {
                $connection->delete($table, ['option_id = ?' => $intOptionId]);
            }
            return false;
        }

        $sortOrder = empty($option['order'][$optionId]) ? 0 : $option['order'][$optionId];
        if (!$intOptionId) {
            $data = ['attribute_id' => $object->getId(), 'sort_order' => $sortOrder];
            $connection->insert($table, $data);
            $intOptionId = $connection->lastInsertId($table);
        } else {
            $data = ['sort_order' => $sortOrder];
            $where = ['option_id = ?' => $intOptionId];
            $connection->update($table, $data, $where);
        }

        return $intOptionId;
    }

    /**
     * Save option values records per store
     *
     * @param int $optionId
     * @param array $values
     * @return void
     */
    protected function _updateAttributeOptionValues($optionId, $values)
    {
        $connection = $this->getConnection();
        $table = $this->getTable('eav_attribute_option_value');

        $connection->delete($table, ['option_id = ?' => $optionId]);

        $stores = $this->_storeManager->getStores(true);
        foreach ($stores as $store) {
            $storeId = $store->getId();
            if (!empty($values[$storeId]) || isset($values[$storeId]) && $values[$storeId] == '0') {
                $data = ['option_id' => $optionId, 'store_id' => $storeId, 'value' => $values[$storeId]];
                $connection->insert($table, $data);
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
        $connection = $this->getConnection();
        $bind = [':entity_type_code' => $entityType, ':attribute_code' => $code];
        $select = $connection->select()->from(
            ['a' => $this->getTable('eav_attribute')],
            ['a.attribute_id']
        )->join(
            ['t' => $this->getTable('eav_entity_type')],
            'a.entity_type_id = t.entity_type_id',
            []
        )->where(
            't.entity_type_code = :entity_type_code'
        )->where(
            'a.attribute_code = :attribute_code'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Get entity attribute
     *
     * @param int|string $entityAttributeId
     * @return array
     */
    public function getEntityAttribute($entityAttributeId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getTable('eav_entity_attribute')
        )->where(
            'entity_attribute_id = ?',
            (int)$entityAttributeId
        );
        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Retrieve attribute codes by front-end type
     *
     * @param string $frontendType
     * @return array
     */
    public function getAttributeCodesByFrontendType($frontendType)
    {
        $connection = $this->getConnection();
        $bind = [':frontend_input' => $frontendType];
        $select = $connection->select()->from(
            $this->getTable('eav_attribute'),
            'attribute_code'
        )->where(
            'frontend_input = :frontend_input'
        );

        return $connection->fetchCol($select, $bind);
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param AbstractAttribute $attribute
     * @param int $storeId
     * @return Select
     */
    public function getFlatUpdateSelect(AbstractAttribute $attribute, $storeId)
    {
        $connection = $this->getConnection();
        $joinConditionTemplate = "%s.entity_id=%s.entity_id" .
            " AND %s.entity_type_id = " .
            $attribute->getEntityTypeId() .
            " AND %s.attribute_id = " .
            $attribute->getId() .
            " AND %s.store_id = %d";
        $joinCondition = sprintf(
            $joinConditionTemplate,
            'e',
            't1',
            't1',
            't1',
            't1',
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );
        if ($attribute->getFlatAddChildData()) {
            $joinCondition .= ' AND e.child_id = t1.entity_id';
        }

        $valueExpr = $connection->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');

        /** @var $select Select */
        $select = $connection->select()->joinLeft(
            ['t1' => $attribute->getBackend()->getTable()],
            $joinCondition,
            []
        )->joinLeft(
            ['t2' => $attribute->getBackend()->getTable()],
            sprintf($joinConditionTemplate, 't1', 't2', 't2', 't2', 't2', $storeId),
            [$attribute->getAttributeCode() => $valueExpr]
        );
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
     * @codeCoverageIgnore
     */
    public function describeTable($table)
    {
        return $this->getConnection()->describeTable($table);
    }

    /**
     * Retrieve additional attribute table name for specified entity type
     *
     * @param int $entityTypeId
     * @return string
     * @codeCoverageIgnore
     */
    public function getAdditionalAttributeTable($entityTypeId)
    {
        return $this->_eavEntityType->getAdditionalAttributeTable($entityTypeId);
    }

    /**
     * Load additional attribute data.
     * Load label of current active store
     *
     * @param EntityAttribute|AbstractModel $object
     * @return $this
     */
    protected function _afterLoad(AbstractModel $object)
    {
        /** @var $entityType \Magento\Eav\Model\Entity\Type */
        $entityType = $object->getData('entity_type');
        if ($entityType) {
            $additionalTable = $entityType->getAdditionalAttributeTable();
        } else {
            $additionalTable = $this->getAdditionalAttributeTable($object->getEntityTypeId());
        }

        if ($additionalTable) {
            $connection = $this->getConnection();
            $bind = [':attribute_id' => $object->getId()];
            $select = $connection->select()->from(
                $this->getTable($additionalTable)
            )->where(
                'attribute_id = :attribute_id'
            );

            $result = $connection->fetchRow($select, $bind);
            if ($result) {
                $object->addData($result);
            }
        }

        return $this;
    }

    /**
     * Retrieve store labels by given attribute id
     *
     * @param int $attributeId
     * @return array
     */
    public function getStoreLabelsByAttributeId($attributeId)
    {
        $connection = $this->getConnection();
        $bind = [':attribute_id' => $attributeId];
        $select = $connection->select()->from(
            $this->getTable('eav_attribute_label'),
            ['store_id', 'value']
        )->where(
            'attribute_id = :attribute_id'
        );

        return $connection->fetchPairs($select, $bind);
    }

    /**
     * Load by given attributes ids and return only exist attribute ids
     *
     * @param array $attributeIds
     * @return array
     */
    public function getValidAttributeIds($attributeIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['attribute_id']
        )->where(
            'attribute_id IN (?)',
            $attributeIds
        );

        return $connection->fetchCol($select);
    }

    /**
     * Provide variables to serialize
     *
     * @return array
     */
    public function __sleep()
    {
        $properties = parent::__sleep();
        $properties = array_diff($properties, ['_storeManager']);
        return $properties;
    }

    /**
     * Restore global dependencies
     *
     * @return void
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $this->_storeManager = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Store\Model\StoreManagerInterface::class);
    }
}
