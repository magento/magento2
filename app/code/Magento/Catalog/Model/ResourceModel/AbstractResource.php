<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;

/**
 * Catalog entity abstract model
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractResource extends \Magento\Eav\Model\Entity\AbstractEntity
{
    /**
     * Store manager to get the store information
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Model factory to create a model object
     *
     * @var \Magento\Catalog\Model\Factory
     */
    protected $_modelFactory;

    /**
     * @param \Magento\Eav\Model\Entity\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Factory $modelFactory
     * @param array $data
     * @param UniqueValidationInterface|null $uniqueValidator
     */
    public function __construct(
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Factory $modelFactory,
        $data = [],
        UniqueValidationInterface $uniqueValidator = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_modelFactory = $modelFactory;
        parent::__construct($context, $data, $uniqueValidator);
    }

    /**
     * Re-declare attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class;
    }

    /**
     * Returns default Store ID
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return \Magento\Store\Model\Store::DEFAULT_STORE_ID;
    }

    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param \Magento\Framework\DataObject $object
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute
     * @return boolean
     */
    protected function _isApplicableAttribute($object, $attribute)
    {
        $applyTo = $attribute->getApplyTo() ?: [];
        return (count($applyTo) == 0 || in_array($object->getTypeId(), $applyTo))
            && $attribute->isInSet($object->getAttributeSetId() ?? $this->getEntityType()->getDefaultAttributeSetId());
    }

    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param AbstractAttribute|AbstractBackend|AbstractFrontend|AbstractSource $instance
     * @param string $method
     * @param array $args array of arguments
     * @return boolean
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if ($instance instanceof AbstractBackend
            && ($method == 'beforeSave' || $method == 'afterSave')
        ) {
            $attributeCode = $instance->getAttribute()->getAttributeCode();
            if (isset($args[0])
                && $args[0] instanceof \Magento\Framework\DataObject
                && $args[0]->getData($attributeCode) === false
            ) {
                return false;
            }
        }

        return parent::_isCallableAttributeInstance($instance, $method, $args);
    }

    /**
     * Retrieve select object for loading entity attributes values
     *
     * Join attribute store value
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $table
     * @return \Magento\Framework\DB\Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        /**
         * This condition is applicable for all cases when we was work in not single
         * store mode, customize some value per specific store view and than back
         * to single store mode. We should load correct values
         */
        if ($this->_storeManager->hasSingleStore()) {
            $storeId = (int) $this->_storeManager->getStore(true)->getId();
        } else {
            $storeId = (int) $object->getStoreId();
        }

        $setId = $object->getAttributeSetId();
        $storeIds = [$this->getDefaultStoreId()];
        if ($storeId != $this->getDefaultStoreId()) {
            $storeIds[] = $storeId;
        }

        $select = $this->getConnection()
            ->select()
            ->from(['attr_table' => $table], [])
            ->where("attr_table.{$this->getLinkField()} = ?", $object->getData($this->getLinkField()))
            ->where('attr_table.store_id IN (?)', $storeIds, \Zend_Db::INT_TYPE);

        if ($setId) {
            $select->join(
                ['set_table' => $this->getTable('eav_entity_attribute')],
                $this->getConnection()->quoteInto(
                    'attr_table.attribute_id = set_table.attribute_id' . ' AND set_table.attribute_set_id = ?',
                    $setId
                ),
                []
            );
        }
        return $select;
    }

    /**
     * Prepare select object for loading entity attributes values
     *
     * @param array $selects
     * @return \Magento\Framework\DB\Select
     */
    protected function _prepareLoadSelect(array $selects)
    {
        $select = parent::_prepareLoadSelect($selects);
        $select->order('store_id');
        return $select;
    }

    /**
     * Insert or Update attribute data
     *
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _saveAttributeValue($object, $attribute, $value)
    {
        $connection = $this->getConnection();
        $hasSingleStore = $this->_storeManager->hasSingleStore();
        $storeId = $hasSingleStore
            ? $this->getDefaultStoreId()
            : (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        $table = $attribute->getBackend()->getTable();

        /**
         * If we work in single store mode all values should be saved just
         * for default store id
         * In this case we clear all not default values
         */
        $entityIdField = $this->getLinkField();
        $conditions = [
            'attribute_id = ?' => $attribute->getAttributeId(),
            "{$entityIdField} = ?" => $object->getData($entityIdField),
            'store_id <> ?' => $storeId
        ];
        if ($hasSingleStore
            && !$object->isObjectNew()
            && $this->isAttributePresentForNonDefaultStore($attribute, $conditions)
        ) {
            $connection->delete(
                $table,
                $conditions
            );
        }

        $data = new \Magento\Framework\DataObject(
            [
                'attribute_id' => $attribute->getAttributeId(),
                'store_id' => $storeId,
                $entityIdField => $object->getData($entityIdField),
                'value' => $this->_prepareValueForSave($value, $attribute),
            ]
        );
        $bind = $this->_prepareDataForTable($data, $table);

        if ($attribute->isScopeStore()) {
            /**
             * Update attribute value for store
             */
            $this->_attributeValuesToSave[$table][] = $bind;
        } elseif ($attribute->isScopeWebsite() && $storeId != $this->getDefaultStoreId()) {
            /**
             * Update attribute value for website
             */
            $storeIds = $this->_storeManager->getStore($storeId)->getWebsite()->getStoreIds(true);
            foreach ($storeIds as $storeId) {
                $bind['store_id'] = (int) $storeId;
                $this->_attributeValuesToSave[$table][] = $bind;
            }
        } else {
            /**
             * Update global attribute value
             */
            $bind['store_id'] = $this->getDefaultStoreId();
            $this->_attributeValuesToSave[$table][] = $bind;
        }

        return $this;
    }

    /**
     * Check if attribute present for non default Store View.
     *
     * Prevent "delete" query locking in a case when nothing to delete
     *
     * @param AbstractAttribute $attribute
     * @param array $conditions
     *
     * @return boolean
     */
    private function isAttributePresentForNonDefaultStore($attribute, $conditions)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($attribute->getBackend()->getTable());
        foreach ($conditions as $condition => $conditionValue) {
            $select->where($condition, $conditionValue);
        }
        $select->limit(1);

        return !empty($connection->fetchRow($select));
    }

    /**
     * Insert entity attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        /**
         * save required attributes in global scope every time if store id different from default
         */
        $storeId = (int) $this->_storeManager->getStore($object->getStoreId())->getId();
        if ($this->getDefaultStoreId() != $storeId) {
            if ($attribute->getIsRequired() || $attribute->getIsRequiredInAdminStore()) {
                $table = $attribute->getBackend()->getTable();

                $select = $this->getConnection()->select()
                    ->from($table)
                    ->where('attribute_id = ?', $attribute->getAttributeId())
                    ->where('store_id = ?', $this->getDefaultStoreId())
                    ->where($this->getLinkField() . ' = ?', $object->getData($this->getLinkField()));
                $row = $this->getConnection()->fetchOne($select);

                if (!$row) {
                    $data = new \Magento\Framework\DataObject(
                        [
                            'attribute_id' => $attribute->getAttributeId(),
                            'store_id' => $this->getDefaultStoreId(),
                            $this->getLinkField() => $object->getData($this->getLinkField()),
                            'value' => $this->_prepareValueForSave($value, $attribute),
                        ]
                    );
                    $bind = $this->_prepareDataForTable($data, $table);
                    $this->getConnection()->insertOnDuplicate($table, $bind, ['value']);
                }
            }
        }

        return $this->_saveAttributeValue($object, $attribute, $value);
    }

    /**
     * Update entity attribute value
     *
     * @param \Magento\Framework\DataObject $object
     * @param AbstractAttribute $attribute
     * @param mixed $valueId
     * @param mixed $value
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
        $entity = $attribute->getEntity();
        $row = $this->getAttributeRow($entity, $object, $attribute);
        $hasSingleStore = $this->_storeManager->hasSingleStore();
        $storeId = $hasSingleStore
            ? $this->getDefaultStoreId()
            : (int) $this->_storeManager->getStore($object->getStoreId())->getId();

        $updateExistingEntry = $valueId > 0 && array_key_exists('store_id', $row) && $storeId === $row['store_id'];
        $needToUpdateMultipleStores = !$hasSingleStore && $attribute->isScopeWebsite();

        if ($updateExistingEntry && !$needToUpdateMultipleStores) {
            $table = $attribute->getBackend()->getTable();
            $connection = $this->getConnection();
            $connection->update(
                $table,
                ['value' => $this->_prepareValueForSave($value, $attribute)],
                sprintf('%s=%d', $connection->quoteIdentifier('value_id'), $valueId)
            );

            return $this;
        } else {
            return $this->_saveAttributeValue($object, $attribute, $value);
        }
    }

    /**
     * Update attribute value for specific store
     *
     * @param \Magento\Catalog\Model\AbstractModel $object
     * @param object $attribute
     * @param mixed $value
     * @param int $storeId
     * @return $this
     */
    protected function _updateAttributeForStore($object, $attribute, $value, $storeId)
    {
        $connection = $this->getConnection();
        $table = $attribute->getBackend()->getTable();
        $entityIdField = $this->getLinkField();
        $select = $connection->select()
            ->from($table, 'value_id')
            ->where("$entityIdField = :entity_field_id")
            ->where('store_id = :store_id')
            ->where('attribute_id = :attribute_id');
        $bind = [
            'entity_field_id' => $object->getId(),
            'store_id' => $storeId,
            'attribute_id' => $attribute->getId(),
        ];
        $valueId = $connection->fetchOne($select, $bind);
        /**
         * When value for store exist
         */
        if ($valueId) {
            $bind = ['value' => $this->_prepareValueForSave($value, $attribute)];
            $where = ['value_id = ?' => (int) $valueId];

            $connection->update($table, $bind, $where);
        } else {
            $bind = [
                $entityIdField => (int) $object->getId(),
                'attribute_id' => (int) $attribute->getId(),
                'value' => $this->_prepareValueForSave($value, $attribute),
                'store_id' => (int) $storeId,
            ];

            $connection->insert($table, $bind);
        }

        return $this;
    }

    /**
     * Delete entity attribute values
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $table
     * @param array $info
     * @return $this
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $connection = $this->getConnection();
        $entityIdField = $this->getLinkField();
        $globalValues = [];
        $websiteAttributes = [];
        $storeAttributes = [];

        /**
         * Separate attributes by scope
         */
        foreach ($info as $itemData) {
            $attribute = $this->getAttribute($itemData['attribute_id']);
            if ($attribute->isScopeStore()) {
                $storeAttributes[] = (int) $itemData['attribute_id'];
            } elseif ($attribute->isScopeWebsite()) {
                $websiteAttributes[] = (int) $itemData['attribute_id'];
            } elseif ($itemData['value_id'] !== null) {
                $globalValues[] = (int) $itemData['value_id'];
            }
        }

        /**
         * Delete global scope attributes
         */
        if (!empty($globalValues)) {
            $connection->delete($table, ['value_id IN (?)' => $globalValues]);
        }

        $condition = [
            $entityIdField . ' = ?' => $object->getId(),
        ];

        /**
         * Delete website scope attributes
         */
        if (!empty($websiteAttributes)) {
            $storeIds = $object->getWebsiteStoreIds();
            if (!empty($storeIds)) {
                $delCondition = $condition;
                $delCondition['attribute_id IN(?)'] = $websiteAttributes;
                $delCondition['store_id IN(?)'] = $storeIds;

                $connection->delete($table, $delCondition);
            }
        }

        /**
         * Delete store scope attributes
         */
        if (!empty($storeAttributes)) {
            $delCondition = $condition;
            $delCondition['attribute_id IN(?)'] = $storeAttributes;
            $delCondition['store_id = ?'] = (int) $object->getStoreId();

            $connection->delete($table, $delCondition);
        }

        return $this;
    }

    /**
     * Retrieve Object instance with original data
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Framework\DataObject
     */
    protected function _getOrigObject($object)
    {
        //TODO:
        $className = get_class($object);
        $origObject = $this->_modelFactory->create($className);
        $origObject->setData([]);
        $origObject->setStoreId($object->getStoreId());
        $this->load($origObject, $object->getData($this->getEntityIdField()));

        return $origObject;
    }

    /**
     * Return if attribute exists in original data array.
     * Checks also attribute's store scope:
     * We should insert on duplicate key update values if we unchecked 'STORE VIEW' checkbox in store view.
     *
     * @param AbstractAttribute $attribute
     * @param mixed $value New value of the attribute.
     * @param array $origData
     * @return bool
     */
    protected function _canUpdateAttribute(AbstractAttribute $attribute, $value, array &$origData)
    {
        $result = parent::_canUpdateAttribute($attribute, $value, $origData);
        if ($result
            && ($attribute->isScopeStore() || $attribute->isScopeWebsite())
            && !$this->_isAttributeValueEmpty($attribute, $value)
            && $value == $origData[$attribute->getAttributeCode()]
            && isset($origData['store_id'])
            && $origData['store_id'] != $this->getDefaultStoreId()
        ) {
            return false;
        }

        return $result;
    }

    /**
     * Retrieve attribute's raw value from DB.
     *
     * @param int $entityId
     * @param int|string|array $attribute attribute's ids or codes
     * @param int|\Magento\Store\Model\Store $store
     * @return bool|string|array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getAttributeRawValue($entityId, $attribute, $store)
    {
        if (!$entityId || empty($attribute)) {
            return false;
        }
        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $attributesData = [];
        $staticAttributes = [];
        $typedAttributes = [];
        $staticTable = null;
        $connection = $this->getConnection();

        foreach ($attribute as $item) {
            /* @var $attribute \Magento\Catalog\Model\Entity\Attribute */
            $item = $this->getAttribute($item);
            if (!$item) {
                continue;
            }
            $attributeCode = $item->getAttributeCode();
            $attrTable = $item->getBackend()->getTable();
            $isStatic = $item->getBackend()->isStatic();

            if ($isStatic) {
                $staticAttributes[] = $attributeCode;
                $staticTable = $attrTable;
            } else {
                /**
                 * That structure needed to avoid farther sql joins for getting attribute's code by id
                 */
                $typedAttributes[$attrTable][$item->getId()] = $attributeCode;
            }
        }

        /**
         * Collecting static attributes
         */
        if ($staticAttributes) {
            $select = $connection->select()->from(
                $staticTable,
                $staticAttributes
            )->join(
                ['e' => $this->getTable($this->getEntityTable())],
                'e.' . $this->getLinkField() . ' = ' . $staticTable . '.' . $this->getLinkField()
            )->where(
                'e.entity_id = :entity_id'
            );
            $attributesData = $connection->fetchRow($select, ['entity_id' => $entityId]);
        }

        /**
         * Collecting typed attributes, performing separate SQL query for each attribute type table
         */
        if ($store instanceof \Magento\Store\Model\Store) {
            $store = $store->getId();
        }

        $store = (int) $store;
        if ($typedAttributes) {
            foreach ($typedAttributes as $table => $_attributes) {
                $defaultJoinCondition = [
                    $connection->quoteInto(
                        'default_value.attribute_id IN (?)',
                        array_keys($_attributes),
                        \Zend_Db::INT_TYPE
                    ),
                    "default_value.{$this->getLinkField()} = e.{$this->getLinkField()}",
                    'default_value.store_id = 0',
                ];

                $select = $connection->select()
                    ->from(['e' => $this->getTable($this->getEntityTable())], [])
                    ->joinLeft(
                        ['default_value' => $table],
                        implode(' AND ', $defaultJoinCondition),
                        []
                    )->where("e.entity_id = :entity_id");

                $bind = ['entity_id' => $entityId];

                if ($store != $this->getDefaultStoreId()) {
                    $valueExpr = $connection->getCheckSql(
                        'store_value.value IS NULL',
                        'default_value.value',
                        'store_value.value'
                    );
                    $attributeIdExpr = $connection->getCheckSql(
                        'store_value.attribute_id IS NULL',
                        'default_value.attribute_id',
                        'store_value.attribute_id'
                    );
                    $joinCondition = [
                        $connection->quoteInto(
                            'store_value.attribute_id IN (?)',
                            array_keys($_attributes),
                            \Zend_Db::INT_TYPE
                        ),
                        "store_value.{$this->getLinkField()} = e.{$this->getLinkField()}",
                        'store_value.store_id = :store_id',
                    ];

                    $select->joinLeft(
                        ['store_value' => $table],
                        implode(' AND ', $joinCondition),
                        ['attribute_id' => $attributeIdExpr, 'attr_value' => $valueExpr]
                    );

                    $bind['store_id'] = $store;
                } else {
                    $select->columns(
                        ['attribute_id' => 'attribute_id', 'attr_value' => 'value'],
                        'default_value'
                    );
                }

                $result = $connection->fetchPairs($select, $bind);
                foreach ($result as $attrId => $value) {
                    if ($attrId !== '') {
                        $attrCode = $typedAttributes[$table][$attrId];
                        $attributesData[$attrCode] = $value;
                    }
                }
            }
        }

        if (is_array($attributesData) && count($attributesData) == 1) {
            $attributesData = array_shift($attributesData);
        }

        return $attributesData === false ? false : $attributesData;
    }
}
