<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Setup;

use Magento\Eav\Model\Entity\Setup\Context;
use Magento\Eav\Model\Entity\Setup\PropertyMapperInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @codeCoverageIgnore
 */
class EavSetup
{
    /**
     * Cache
     *
     * @var CacheInterface
     */
    private $cache;

    /**
     * Attribute group collection factory
     *
     * @var CollectionFactory
     */
    private $attrGroupCollectionFactory;

    /**
     * Attribute mapper
     *
     * @var PropertyMapperInterface
     */
    private $attributeMapper;

    /**
     * Setup model
     *
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * General Attribute Group Name
     *
     * @var string
     */
    private $_generalGroupName = 'General';

    /**
     * Default attribute group name to id pairs
     *
     * @var array
     */
    private $defaultGroupIdAssociations = ['general' => 1];

    /**
     * Default attribute group name
     *
     * @var string
     */
    private $_defaultGroupName = 'Default';

    /**
     * Default attribute set name
     *
     * @var string
     */
    private $_defaultAttributeSetName = 'Default';

    /**
     * Init
     *
     * @param ModuleDataSetupInterface $setup
     * @param Context $context
     * @param CacheInterface $cache
     * @param CollectionFactory $attrGroupCollectionFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        Context $context,
        CacheInterface $cache,
        CollectionFactory $attrGroupCollectionFactory
    ) {
        $this->cache = $cache;
        $this->attrGroupCollectionFactory = $attrGroupCollectionFactory;
        $this->attributeMapper = $context->getAttributeMapper();
        $this->setup = $setup;
    }

    /**
     * Gets setup model
     *
     * @return ModuleDataSetupInterface
     */
    public function getSetup()
    {
        return $this->setup;
    }

    /**
     * Gets attribute group collection factory
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    public function getAttributeGroupCollectionFactory()
    {
        return $this->attrGroupCollectionFactory->create();
    }

    /**
     * Clean cache
     *
     * @return $this
     */
    public function cleanCache()
    {
        $this->cache->clean([\Magento\Eav\Model\Cache\Type::CACHE_TAG]);
        return $this;
    }

    /**
     * Install Default Group Ids
     *
     * @return $this
     */
    public function installDefaultGroupIds()
    {
        $setIds = $this->getAllAttributeSetIds();
        foreach ($this->defaultGroupIdAssociations as $defaultGroupCode => $defaultGroupId) {
            foreach ($setIds as $set) {
                $groupId = $this->setup->getTableRow(
                    'eav_attribute_group',
                    'attribute_group_code',
                    $defaultGroupCode,
                    'attribute_group_id',
                    'attribute_set_id',
                    $set
                );
                if (!$groupId) {
                    $groupId = $this->setup->getTableRow(
                        'eav_attribute_group',
                        'attribute_set_id',
                        $set,
                        'attribute_group_id'
                    );
                }
                $this->setup->updateTableRow(
                    'eav_attribute_group',
                    'attribute_group_id',
                    $groupId,
                    'default_id',
                    $defaultGroupId
                );
            }
        }

        return $this;
    }

    /******************* ENTITY TYPES *****************/

    /**
     * Add an entity type
     *
     * If already exists updates the entity type with params data
     *
     * @param string $code
     * @param array $params
     * @return $this
     */
    public function addEntityType($code, array $params)
    {
        $data = [
            'entity_type_code' => $code,
            'entity_model' => $params['entity_model'],
            'attribute_model' => $this->_getValue($params, 'attribute_model'),
            'entity_table' => $this->_getValue($params, 'table', 'eav_entity'),
            'value_table_prefix' => $this->_getValue($params, 'table_prefix'),
            'entity_id_field' => $this->_getValue($params, 'id_field'),
            'increment_model' => $this->_getValue($params, 'increment_model'),
            'increment_per_store' => $this->_getValue($params, 'increment_per_store', 0),
            'increment_pad_length' => $this->_getValue($params, 'increment_pad_length', 8),
            'increment_pad_char' => $this->_getValue($params, 'increment_pad_char', 0),
            'additional_attribute_table' => $this->_getValue($params, 'additional_attribute_table'),
            'entity_attribute_collection' => $this->_getValue($params, 'entity_attribute_collection'),
        ];
        if (isset($params['entity_type_id'])) {
            $data['entity_type_id'] = $params['entity_type_id'];
        }

        if ($this->getEntityType($code, 'entity_type_id')) {
            $this->updateEntityType($code, $data);
        } else {
            $this->setup->getConnection()->insert($this->setup->getTable('eav_entity_type'), $data);
        }

        if (isset($params['entity_type_id'])) {
            $this->addAttributeSet($code, $this->_defaultAttributeSetName, null, $params['entity_type_id']);
        } else {
            $this->addAttributeSet($code, $this->_defaultAttributeSetName);
        }
        $this->addAttributeGroup($code, $this->_defaultGroupName, $this->_generalGroupName);

        return $this;
    }

    /**
     * Update entity row
     *
     * @param string $code
     * @param string $field
     * @param string $value
     * @return $this
     */
    public function updateEntityType($code, $field, $value = null)
    {
        $this->setup->updateTableRow(
            'eav_entity_type',
            'entity_type_id',
            $this->getEntityTypeId($code),
            $field,
            $value
        );
        return $this;
    }

    /**
     * Retrieve Entity Type Data
     *
     * @param int|string $id
     * @param string $field
     * @return mixed
     */
    public function getEntityType($id, $field = null)
    {
        return $this->setup->getTableRow(
            'eav_entity_type',
            is_numeric($id) ? 'entity_type_id' : 'entity_type_code',
            $id,
            $field
        );
    }

    /**
     * Retrieve Entity Type Id By Id or Code
     *
     * @param int|string $entityTypeId
     * @return int
     * @throws LocalizedException
     */
    public function getEntityTypeId($entityTypeId)
    {
        if (!is_numeric($entityTypeId)) {
            $entityTypeId = $this->getEntityType($entityTypeId, 'entity_type_id');
        }
        if (!is_numeric($entityTypeId)) {
            throw new LocalizedException(__('Wrong entity ID'));
        }

        return $entityTypeId;
    }

    /**
     * Remove entity type by Id or Code
     *
     * @param int|string $id
     * @return $this
     */
    public function removeEntityType($id)
    {
        if (is_numeric($id)) {
            $this->setup->deleteTableRow('eav_entity_type', 'entity_type_id', $id);
        } else {
            $this->setup->deleteTableRow('eav_entity_type', 'entity_type_code', (string)$id);
        }

        return $this;
    }

    /******************* ATTRIBUTE SETS *****************/

    /**
     * Retrieve Attribute Set Sort order
     *
     * @param int|string $entityTypeId
     * @param int $sortOrder
     * @return int
     */
    public function getAttributeSetSortOrder($entityTypeId, $sortOrder = null)
    {
        if (!is_numeric($sortOrder)) {
            $bind = ['entity_type_id' => $this->getEntityTypeId($entityTypeId)];
            $select = $this->setup->getConnection()->select()->from(
                $this->setup->getTable('eav_attribute_set'),
                'MAX(sort_order)'
            )->where(
                'entity_type_id = :entity_type_id'
            );

            $sortOrder = $this->setup->getConnection()->fetchOne($select, $bind) + 1;
        }

        return $sortOrder;
    }

    /**
     * Add Attribute Set
     *
     * @param int|string $entityTypeId
     * @param string $name
     * @param int $sortOrder
     * @param int $setId
     * @return $this
     */
    public function addAttributeSet($entityTypeId, $name, $sortOrder = null, $setId = null)
    {
        $data = [
            'entity_type_id' => $this->getEntityTypeId($entityTypeId),
            'attribute_set_name' => $name,
            'sort_order' => $this->getAttributeSetSortOrder($entityTypeId, $sortOrder),
        ];

        if ($setId !== null) {
            $data['attribute_set_id'] = $setId;
        }

        $setId = $this->getAttributeSet($entityTypeId, $name, 'attribute_set_id');
        if ($setId) {
            $this->updateAttributeSet($entityTypeId, $setId, $data);
        } else {
            $this->setup->getConnection()->insert($this->setup->getTable('eav_attribute_set'), $data);

            $this->addAttributeGroup($entityTypeId, $name, $this->_generalGroupName);
        }

        return $this;
    }

    /**
     * Update attribute set data
     *
     * @param int|string $entityTypeId
     * @param int $id
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function updateAttributeSet($entityTypeId, $id, $field, $value = null)
    {
        $this->setup->updateTableRow(
            'eav_attribute_set',
            'attribute_set_id',
            $this->getAttributeSetId($entityTypeId, $id),
            $field,
            $value,
            'entity_type_id',
            $this->getEntityTypeId($entityTypeId)
        );
        return $this;
    }

    /**
     * Retrieve Attribute set data by id or name
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @param string $field
     * @return mixed
     */
    public function getAttributeSet($entityTypeId, $id, $field = null)
    {
        return $this->setup->getTableRow(
            'eav_attribute_set',
            is_numeric($id) ? 'attribute_set_id' : 'attribute_set_name',
            $id,
            $field,
            'entity_type_id',
            $this->getEntityTypeId($entityTypeId)
        );
    }

    /**
     * Retrieve Attribute Set Id By Id or Name
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @return int
     * @throws LocalizedException
     */
    public function getAttributeSetId($entityTypeId, $setId)
    {
        if (!is_numeric($setId)) {
            $setId = $this->getAttributeSet($entityTypeId, $setId, 'attribute_set_id');
        }
        if (!is_numeric($setId)) {
            throw new LocalizedException(__('Wrong attribute set ID'));
        }

        return $setId;
    }

    /**
     * Remove Attribute Set
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @return $this
     */
    public function removeAttributeSet($entityTypeId, $id)
    {
        $this->setup->deleteTableRow(
            'eav_attribute_set',
            'attribute_set_id',
            $this->getAttributeSetId($entityTypeId, $id)
        );
        return $this;
    }

    /**
     * Set Default Attribute Set to Entity Type
     *
     * @param int|string $entityType
     * @param string $attributeSet
     * @return $this
     */
    public function setDefaultSetToEntityType($entityType, $attributeSet = 'Default')
    {
        $entityTypeId = $this->getEntityTypeId($entityType);
        $setId = $this->getAttributeSetId($entityTypeId, $attributeSet);
        $this->updateEntityType($entityTypeId, 'default_attribute_set_id', $setId);
        return $this;
    }

    /**
     * Get identifiers of all attribute sets
     *
     * @param int|string|null $entityTypeId
     * @return array
     */
    public function getAllAttributeSetIds($entityTypeId = null)
    {
        $select = $this->setup->getConnection()->select()
            ->from($this->setup->getTable('eav_attribute_set'), 'attribute_set_id');

        $bind = [];
        if ($entityTypeId !== null) {
            $bind['entity_type_id'] = $this->getEntityTypeId($entityTypeId);
            $select->where('entity_type_id = :entity_type_id');
        }

        return $this->setup->getConnection()->fetchCol($select, $bind);
    }

    /**
     * Retrieve Default Attribute Set for Entity Type
     *
     * @param string|int $entityType
     * @return int
     */
    public function getDefaultAttributeSetId($entityType)
    {
        $bind = ['entity_type' => $entityType];
        if (is_numeric($entityType)) {
            $where = 'entity_type_id = :entity_type';
        } else {
            $where = 'entity_type_code = :entity_type';
        }
        $select = $this->setup->getConnection()->select()->from(
            $this->setup->getTable('eav_entity_type'),
            'default_attribute_set_id'
        )->where(
            $where
        );

        return $this->setup->getConnection()->fetchOne($select, $bind);
    }

    /******************* ATTRIBUTE GROUPS *****************/

    /**
     * Retrieve Attribute Group Sort order
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int $sortOrder
     * @return int
     */
    public function getAttributeGroupSortOrder($entityTypeId, $setId, $sortOrder = null)
    {
        if (!is_numeric($sortOrder)) {
            $bind = ['attribute_set_id' => $this->getAttributeSetId($entityTypeId, $setId)];
            $select = $this->setup->getConnection()->select()->from(
                $this->setup->getTable('eav_attribute_group'),
                'MAX(sort_order)'
            )->where(
                'attribute_set_id = :attribute_set_id'
            );

            $sortOrder = $this->setup->getConnection()->fetchOne($select, $bind) + 1;
        }

        return $sortOrder;
    }

    /**
     * Add Attribute Group
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param string $name
     * @param int $sortOrder
     * @return $this
     */
    public function addAttributeGroup($entityTypeId, $setId, $name, $sortOrder = null)
    {
        $setId = $this->getAttributeSetId($entityTypeId, $setId);
        $data = ['attribute_set_id' => $setId, 'attribute_group_name' => $name];
        $attributeGroupCode = $this->convertToAttributeGroupCode($name);

        if (isset($this->defaultGroupIdAssociations[$attributeGroupCode])) {
            $data['default_id'] = $this->defaultGroupIdAssociations[$attributeGroupCode];
        }

        if ($sortOrder !== null) {
            $data['sort_order'] = $sortOrder;
        }

        $groupId = $this->getAttributeGroup($entityTypeId, $setId, $attributeGroupCode, 'attribute_group_id');
        if ($groupId) {
            $this->updateAttributeGroup($entityTypeId, $setId, $groupId, $data);
        } else {
            if ($sortOrder === null) {
                $data['sort_order'] = $this->getAttributeGroupSortOrder($entityTypeId, $setId, $sortOrder);
            }
            if (empty($data['attribute_group_code'])) {
                if (empty($attributeGroupCode)) {
                    // in the following code md5 is not used for security purposes
                    $attributeGroupCode = md5($name);
                }
                $data['attribute_group_code'] = $attributeGroupCode;
            }
            $this->setup->getConnection()->insert($this->setup->getTable('eav_attribute_group'), $data);
        }

        return $this;
    }

    /**
     * @param string $groupName
     * @return string
     */
    public function convertToAttributeGroupCode($groupName)
    {
        return trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($groupName)), '-');
    }

    /**
     * Update Attribute Group Data
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $id
     * @param string $field
     * @param mixed $value
     * @return $this
     */
    public function updateAttributeGroup($entityTypeId, $setId, $id, $field, $value = null)
    {
        $this->setup->updateTableRow(
            'eav_attribute_group',
            'attribute_group_id',
            $this->getAttributeGroupId($entityTypeId, $setId, $id),
            $field,
            $value,
            'attribute_set_id',
            $this->getAttributeSetId($entityTypeId, $setId)
        );

        return $this;
    }

    /**
     * Retrieve Attribute Group Data
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $id
     * @param string $field
     * @return mixed
     */
    public function getAttributeGroup($entityTypeId, $setId, $id, $field = null)
    {
        if (is_numeric($id)) {
            $searchField = 'attribute_group_id';
        } else {
            $id = $this->convertToAttributeGroupCode($id);
            if (isset($this->defaultGroupIdAssociations[$id])) {
                $searchField = 'default_id';
                $id = $this->defaultGroupIdAssociations[$id];
            } else {
                $searchField = 'attribute_group_code';
            }
        }

        return $this->setup->getTableRow(
            'eav_attribute_group',
            $searchField,
            $id,
            $field,
            'attribute_set_id',
            $this->getAttributeSetId($entityTypeId, $setId)
        );
    }

    /**
     * Retrieve Attribute Group Data by Code
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param string $code
     * @param string $field
     * @return mixed
     */
    public function getAttributeGroupByCode($entityTypeId, $setId, $code, $field = null)
    {
        return $this->setup->getTableRow(
            'eav_attribute_group',
            'attribute_group_code',
            $code,
            $field,
            'attribute_set_id',
            $this->getAttributeSetId($entityTypeId, $setId)
        );
    }

    /**
     * Retrieve Attribute Group Id by Id or Name
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $groupId
     * @return $this
     * @throws LocalizedException
     */
    public function getAttributeGroupId($entityTypeId, $setId, $groupId)
    {
        if (!is_numeric($groupId)) {
            $groupId = $this->getAttributeGroup($entityTypeId, $setId, $groupId, 'attribute_group_id');
        }

        if (!is_numeric($groupId)) {
            $groupId = $this->getDefaultAttributeGroupId($entityTypeId, $setId);
        }

        if (!is_numeric($groupId)) {
            throw new LocalizedException(__('Wrong attribute group ID'));
        }
        return $groupId;
    }

    /**
     * Remove Attribute Group By Id or Name
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $id
     * @return $this
     */
    public function removeAttributeGroup($entityTypeId, $setId, $id)
    {
        $this->setup->deleteTableRow(
            'eav_attribute_group',
            'attribute_group_id',
            $this->getAttributeGroupId($entityTypeId, $setId, $id)
        );
        return $this;
    }

    /**
     * Retrieve Default Attribute Group Id By Entity Type and Attribute Set
     *
     * @param string|int $entityType
     * @param int $attributeSetId
     * @return int
     */
    public function getDefaultAttributeGroupId($entityType, $attributeSetId = null)
    {
        $entityType = $this->getEntityTypeId($entityType);
        if (!is_numeric($attributeSetId)) {
            $attributeSetId = $this->getDefaultAttributeSetId($entityType);
        }
        $bind = ['attribute_set_id' => $attributeSetId];
        $select = $this->setup->getConnection()->select()->from(
            $this->setup->getTable('eav_attribute_group'),
            'attribute_group_id'
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->order(
            ['default_id ' . \Magento\Framework\DB\Select::SQL_DESC, 'sort_order']
        )->limit(
            1
        );

        return $this->setup->getConnection()->fetchOne($select, $bind);
    }

    /**
     * Get number of all attributes in group
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $groupId
     *
     * @return string
     */
    public function getAttributesNumberInGroup($entityTypeId, $setId, $groupId)
    {
        $select = $this->setup->getConnection()->select()->from(
            $this->setup->getTable('eav_entity_attribute'),
            ['count' => 'COUNT(*)']
        )->where(
            'attribute_group_id = ?',
            $this->getAttributeGroupId($entityTypeId, $setId, $groupId)
        )->where(
            'entity_type_id = ?',
            $entityTypeId
        )->where(
            'attribute_set_id = ?',
            $setId
        );

        return $this->setup->getConnection()->fetchOne($select);
    }

    /******************* ATTRIBUTES *****************/

    /**
     * Retrieve value from array by key or return default value
     *
     * @param array $array
     * @param string $key
     * @param string $default
     * @return string
     */
    private function _getValue($array, $key, $default = null)
    {
        if (isset($array[$key]) && is_bool($array[$key])) {
            $array[$key] = (int)$array[$key];
        }
        return isset($array[$key]) ? $array[$key] : $default;
    }

    /**
     * Validate attribute data before insert into table
     *
     * @param  array $data
     * @return true
     * @throws LocalizedException
     */
    private function _validateAttributeData($data)
    {
        $attributeCodeMaxLength = \Magento\Eav\Model\Entity\Attribute::ATTRIBUTE_CODE_MAX_LENGTH;

        if (isset(
            $data['attribute_code']
        ) && !\Zend_Validate::is(
            $data['attribute_code'],
            'StringLength',
            ['max' => $attributeCodeMaxLength]
        )
        ) {
            throw new LocalizedException(
                __('An attribute code must not be more than %1 characters.', $attributeCodeMaxLength)
            );
        }

        return true;
    }

    /**
     * Add attribute to an entity type
     *
     * If attribute is system will add to all existing attribute sets
     *
     * @param string|integer $entityTypeId
     * @param string $code
     * @param array $attr
     * @return $this
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        $entityTypeId = $this->getEntityTypeId($entityTypeId);

        $data = array_replace(
            ['entity_type_id' => $entityTypeId, 'attribute_code' => $code],
            $this->attributeMapper->map($attr, $entityTypeId)
        );

        $this->_validateAttributeData($data);

        $sortOrder = isset($attr['sort_order']) ? $attr['sort_order'] : null;
        $attributeId = $this->getAttribute($entityTypeId, $code, 'attribute_id');
        if ($attributeId) {
            $this->updateAttribute($entityTypeId, $attributeId, $data, null, $sortOrder);
        } else {
            $this->_insertAttribute($data);
        }

        if (!empty($attr['group']) || empty($attr['user_defined'])) {
            $select = $this->setup->getConnection()->select()->from(
                $this->setup->getTable('eav_attribute_set')
            )->where(
                'entity_type_id = :entity_type_id'
            );
            $sets = $this->setup->getConnection()->fetchAll($select, ['entity_type_id' => $entityTypeId]);
            foreach ($sets as $set) {
                if (!empty($attr['group'])) {
                    $this->addAttributeGroup($entityTypeId, $set['attribute_set_id'], $attr['group']);
                    $this->addAttributeToSet(
                        $entityTypeId,
                        $set['attribute_set_id'],
                        $attr['group'],
                        $code,
                        $sortOrder
                    );
                } else {
                    $this->addAttributeToSet(
                        $entityTypeId,
                        $set['attribute_set_id'],
                        $this->_generalGroupName,
                        $code,
                        $sortOrder
                    );
                }
            }
        }

        if (isset($attr['option']) && is_array($attr['option'])) {
            $option = $attr['option'];
            $option['attribute_id'] = $this->getAttributeId($entityTypeId, $code);
            $this->addAttributeOption($option);
        }

        return $this;
    }

    /**
     * Add Attribute Option
     *
     * @param array $option
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addAttributeOption($option)
    {
        $optionTable = $this->setup->getTable('eav_attribute_option');
        $optionValueTable = $this->setup->getTable('eav_attribute_option_value');

        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = (int)$optionId;
                if (!empty($option['delete'][$optionId])) {
                    if ($intOptionId) {
                        $condition = ['option_id =?' => $intOptionId];
                        $this->setup->getConnection()->delete($optionTable, $condition);
                    }
                    continue;
                }

                if (!$intOptionId) {
                    $data = [
                        'attribute_id' => $option['attribute_id'],
                        'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    ];
                    $this->setup->getConnection()->insert($optionTable, $data);
                    $intOptionId = $this->setup->getConnection()->lastInsertId($optionTable);
                } else {
                    $data = [
                        'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    ];
                    $this->setup->getConnection()->update($optionTable, $data, ['option_id=?' => $intOptionId]);
                }

                // Default value
                if (!isset($values[0])) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Default option value is not defined')
                    );
                }
                $condition = ['option_id =?' => $intOptionId];
                $this->setup->getConnection()->delete($optionValueTable, $condition);
                foreach ($values as $storeId => $value) {
                    $data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                    $this->setup->getConnection()->insert($optionValueTable, $data);
                }
            }
        } elseif (isset($option['values'])) {
            foreach ($option['values'] as $sortOrder => $label) {
                // add option
                $data = ['attribute_id' => $option['attribute_id'], 'sort_order' => $sortOrder];
                $this->setup->getConnection()->insert($optionTable, $data);
                $intOptionId = $this->setup->getConnection()->lastInsertId($optionTable);

                $data = ['option_id' => $intOptionId, 'store_id' => 0, 'value' => $label];
                $this->setup->getConnection()->insert($optionValueTable, $data);
            }
        }
    }

    /**
     * Update Attribute data and Attribute additional data
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @param string|array $field
     * @param mixed $value
     * @param int $sortOrder
     * @return $this
     */
    public function updateAttribute($entityTypeId, $id, $field, $value = null, $sortOrder = null)
    {
        $this->_updateAttribute($entityTypeId, $id, $field, $value, $sortOrder);
        $this->_updateAttributeAdditionalData($entityTypeId, $id, $field, $value);
        return $this;
    }

    /**
     * Update Attribute data
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @param string $field
     * @param mixed $value
     * @param int $sortOrder
     * @return $this
     */
    private function _updateAttribute($entityTypeId, $id, $field, $value = null, $sortOrder = null)
    {
        if ($sortOrder !== null) {
            $this->setup->updateTableRow(
                'eav_entity_attribute',
                'attribute_id',
                $this->getAttributeId($entityTypeId, $id),
                'sort_order',
                $sortOrder
            );
        }

        $attributeFields = $this->_getAttributeTableFields();
        if (is_array($field)) {
            $bind = [];
            foreach ($field as $k => $v) {
                if (isset($attributeFields[$k])) {
                    $bind[$k] = $this->setup->getConnection()->prepareColumnValue($attributeFields[$k], $v);
                }
            }
            if (!$bind) {
                return $this;
            }
            $field = $bind;
        } else {
            if (!isset($attributeFields[$field])) {
                return $this;
            }
        }

        $this->setup->updateTableRow(
            'eav_attribute',
            'attribute_id',
            $this->getAttributeId($entityTypeId, $id),
            $field,
            $value,
            'entity_type_id',
            $this->getEntityTypeId($entityTypeId)
        );

        return $this;
    }

    /**
     * Update Attribute Additional data
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @param string|array $field
     * @param mixed $value
     * @return $this
     */
    private function _updateAttributeAdditionalData($entityTypeId, $id, $field, $value = null)
    {
        $additionalTable = $this->getEntityType($entityTypeId, 'additional_attribute_table');
        if (!$additionalTable) {
            return $this;
        }
        $additionalTableExists = $this->setup->getConnection()->isTableExists($this->setup->getTable($additionalTable));
        if ($additionalTable && $additionalTableExists) {
            $attributeFields = $this->setup->getConnection()->describeTable($this->setup->getTable($additionalTable));
            if (is_array($field)) {
                $bind = [];
                foreach ($field as $k => $v) {
                    if (isset($attributeFields[$k])) {
                        $bind[$k] = $this->setup->getConnection()->prepareColumnValue($attributeFields[$k], $v);
                    }
                }
                if (!$bind) {
                    return $this;
                }
                $field = $bind;
            } else {
                if (!isset($attributeFields[$field])) {
                    return $this;
                }
            }
            $this->setup->updateTableRow(
                $this->setup->getTable($additionalTable),
                'attribute_id',
                $this->getAttributeId($entityTypeId, $id),
                $field,
                $value
            );

            $attribute = $this->getAttribute($entityTypeId, $id);
            $this->updateCachedRow($field, $value, $attribute);
        }

        return $this;
    }

    /**
     * Updates cache for the row
     *
     * @param string|array $field
     * @param mixed $value
     * @param array $attribute
     *
     * @return void
     */
    private function updateCachedRow($field, $value, $attribute)
    {
        $setupCache = $this->setup->getSetupCache();
        $mainTable = $this->setup->getTable('eav_attribute');
        if (is_array($field)) {
            $oldRow = $setupCache->has($mainTable, $attribute['entity_type_id'], $attribute['attribute_code']) ?
                $setupCache->get($mainTable, $attribute['entity_type_id'], $attribute['attribute_code']) :
                [];
            $newRowData = array_merge($oldRow, $field);
            $setupCache->setRow(
                $mainTable,
                $attribute['entity_type_id'],
                $attribute['attribute_code'],
                $newRowData
            );
        } else {
            $setupCache->setField(
                $mainTable,
                $attribute['entity_type_id'],
                $attribute['attribute_code'],
                $field,
                $value
            );
        }
    }

    /**
     * Retrieve Attribute Data By Id or Code
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @param string $field
     * @return mixed
     */
    public function getAttribute($entityTypeId, $id, $field = null)
    {
        $additionalTable = $this->getEntityType($entityTypeId, 'additional_attribute_table');
        $entityTypeId = $this->getEntityTypeId($entityTypeId);
        $idField = is_numeric($id) ? 'attribute_id' : 'attribute_code';
        if (!$additionalTable) {
            return $this->setup->getTableRow('eav_attribute', $idField, $id, $field, 'entity_type_id', $entityTypeId);
        }

        $mainTable = $this->setup->getTable('eav_attribute');
        $setupCache = $this->setup->getSetupCache();
        if (!$setupCache->has($mainTable, $entityTypeId, $id)) {
            $additionalTable = $this->setup->getTable($additionalTable);
            $bind = ['id' => $id, 'entity_type_id' => $entityTypeId];
            $select = $this->setup->getConnection()->select()->from(
                ['main' => $mainTable]
            )->join(
                ['additional' => $additionalTable],
                'main.attribute_id = additional.attribute_id'
            )->where(
                "main.{$idField} = :id"
            )->where(
                'main.entity_type_id = :entity_type_id'
            );

            $row = $this->setup->getConnection()->fetchRow($select, $bind);
            if (!$row) {
                $setupCache->setRow($mainTable, $entityTypeId, $id, []);
            } else {
                $setupCache->setRow($mainTable, $entityTypeId, $row['attribute_id'], $row);
                $setupCache->setRow($mainTable, $entityTypeId, $row['attribute_code'], $row);
            }
        }

        $row = $setupCache->get($mainTable, $entityTypeId, $id);
        if ($field !== null) {
            return isset($row[$field]) ? $row[$field] : false;
        }

        return $row;
    }

    /**
     * Retrieve Attribute Id Data By Id or Code
     *
     * @param int|string $entityTypeId
     * @param int|string $id
     * @return int
     */
    public function getAttributeId($entityTypeId, $id)
    {
        if (!is_numeric($id)) {
            $id = $this->getAttribute($entityTypeId, $id, 'attribute_id');
        }
        if (!is_numeric($id)) {
            return false;
        }
        return $id;
    }

    /**
     * Return table name for eav attribute
     *
     * @param int|string $entityTypeId Entity Type id or Entity Type code
     * @param int|string $id Attribute id or Attribute code
     * @return string
     */
    public function getAttributeTable($entityTypeId, $id)
    {
        $entityKeyName = is_numeric($entityTypeId) ? 'entity_type_id' : 'entity_type_code';
        $attributeKeyName = is_numeric($id) ? 'attribute_id' : 'attribute_code';

        $bind = ['id' => $id, 'entity_type_id' => $entityTypeId];
        $select = $this->setup->getConnection()->select()->from(
            ['entity_type' => $this->setup->getTable('eav_entity_type')],
            ['entity_table']
        )->join(
            ['attribute' => $this->setup->getTable('eav_attribute')],
            'attribute.entity_type_id = entity_type.entity_type_id',
            ['backend_type']
        )->where(
            "entity_type.{$entityKeyName} = :entity_type_id"
        )->where(
            "attribute.{$attributeKeyName} = :id"
        )->limit(
            1
        );

        $result = $this->setup->getConnection()->fetchRow($select, $bind);
        if ($result) {
            $table = $this->setup->getTable($result['entity_table']);
            if ($result['backend_type'] != 'static') {
                $table .= '_' . $result['backend_type'];
            }
            return $table;
        }

        return false;
    }

    /**
     * Remove Attribute
     *
     * @param int|string $entityTypeId
     * @param int|string $code
     * @return $this
     */
    public function removeAttribute($entityTypeId, $code)
    {
        $mainTable = $this->setup->getTable('eav_attribute');
        $attribute = $this->getAttribute($entityTypeId, $code);
        if ($attribute) {
            $this->setup->deleteTableRow('eav_attribute', 'attribute_id', $attribute['attribute_id']);
            $setupCache = $this->setup->getSetupCache();
            if ($setupCache->has($mainTable, $attribute['entity_type_id'], $attribute['attribute_code'])) {
                $setupCache->remove($mainTable, $attribute['entity_type_id'], $attribute['attribute_code']);
            }
        }
        return $this;
    }

    /**
     * Retrieve Attribute Sort Order
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $groupId
     * @param int $sortOrder
     * @return $this
     */
    public function getAttributeSortOrder($entityTypeId, $setId, $groupId, $sortOrder = null)
    {
        if (!is_numeric($sortOrder)) {
            $bind = ['attribute_group_id' => $this->getAttributeGroupId($entityTypeId, $setId, $groupId)];
            $select = $this->setup->getConnection()->select()->from(
                $this->setup->getTable('eav_entity_attribute'),
                'MAX(sort_order)'
            )->where(
                'attribute_group_id = :attribute_group_id'
            );

            $sortOrder = $this->setup->getConnection()->fetchOne($select, $bind) + 1;
        }

        return $sortOrder;
    }

    /**
     * Add Attribute to All Groups on Attribute Set
     *
     * @param int|string $entityTypeId
     * @param int|string $setId
     * @param int|string $groupId
     * @param int|string $attributeId
     * @param int $sortOrder
     * @return $this
     */
    public function addAttributeToSet($entityTypeId, $setId, $groupId, $attributeId, $sortOrder = null)
    {
        $entityTypeId = $this->getEntityTypeId($entityTypeId);
        $setId = $this->getAttributeSetId($entityTypeId, $setId);
        $groupId = $this->getAttributeGroupId($entityTypeId, $setId, $groupId);
        $attributeId = $this->getAttributeId($entityTypeId, $attributeId);
        $table = $this->setup->getTable('eav_entity_attribute');

        $bind = ['attribute_set_id' => $setId, 'attribute_id' => $attributeId];
        $select = $this->setup->getConnection()->select()->from(
            $table
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->where(
            'attribute_id = :attribute_id'
        );
        $result = $this->setup->getConnection()->fetchRow($select, $bind);

        if ($result) {
            if ($result['attribute_group_id'] != $groupId) {
                $where = ['entity_attribute_id =?' => $result['entity_attribute_id']];
                $data = ['attribute_group_id' => $groupId];
                $this->setup->getConnection()->update($table, $data, $where);
            }
        } else {
            $data = [
                'entity_type_id' => $entityTypeId,
                'attribute_set_id' => $setId,
                'attribute_group_id' => $groupId,
                'attribute_id' => $attributeId,
                'sort_order' => $this->getAttributeSortOrder($entityTypeId, $setId, $groupId, $sortOrder),
            ];

            $this->setup->getConnection()->insert($table, $data);
        }

        return $this;
    }

    /**
     * Add or update attribute to group
     *
     * @param int|string $entityType
     * @param int|string $setId
     * @param int|string $groupId
     * @param int|string $attributeId
     * @param int $sortOrder
     * @return $this
     */
    public function addAttributeToGroup($entityType, $setId, $groupId, $attributeId, $sortOrder = null)
    {
        $entityType = $this->getEntityTypeId($entityType);
        $setId = $this->getAttributeSetId($entityType, $setId);
        $groupId = $this->getAttributeGroupId($entityType, $setId, $groupId);
        $attributeId = $this->getAttributeId($entityType, $attributeId);

        $data = [
            'entity_type_id' => $entityType,
            'attribute_set_id' => $setId,
            'attribute_group_id' => $groupId,
            'attribute_id' => $attributeId,
        ];

        $bind = ['entity_type_id' => $entityType, 'attribute_set_id' => $setId, 'attribute_id' => $attributeId];
        $select = $this->setup->getConnection()->select()->from(
            $this->setup->getTable('eav_entity_attribute')
        )->where(
            'entity_type_id = :entity_type_id'
        )->where(
            'attribute_set_id = :attribute_set_id'
        )->where(
            'attribute_id = :attribute_id'
        );
        $row = $this->setup->getConnection()->fetchRow($select, $bind);
        if ($row) {
            // update
            if ($sortOrder !== null) {
                $data['sort_order'] = $sortOrder;
            }

            $this->setup->getConnection()->update(
                $this->setup->getTable('eav_entity_attribute'),
                $data,
                $this->setup->getConnection()->quoteInto('entity_attribute_id=?', $row['entity_attribute_id'])
            );
        } else {
            if ($sortOrder === null) {
                $select = $this->setup->getConnection()->select()->from(
                    $this->setup->getTable('eav_entity_attribute'),
                    'MAX(sort_order)'
                )->where(
                    'entity_type_id = :entity_type_id'
                )->where(
                    'attribute_set_id = :attribute_set_id'
                )->where(
                    'attribute_id = :attribute_id'
                );

                $sortOrder = $this->setup->getConnection()->fetchOne($select, $bind) + 10;
            }
            $sortOrder = is_numeric($sortOrder) ? $sortOrder : 1;
            $data['sort_order'] = $sortOrder;
            $this->setup->getConnection()->insert($this->setup->getTable('eav_entity_attribute'), $data);
        }

        return $this;
    }

    /******************* BULK INSTALL *****************/

    /**
     * Gets default entities and attributes
     *
     * @return array
     */
    public function getDefaultEntities()
    {
        return [];
    }

    /**
     * Install entities
     *
     * @param array $entities
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function installEntities($entities = null)
    {
        $this->cleanCache();

        if ($entities === null) {
            $entities = $this->getDefaultEntities();
        }

        foreach ($entities as $entityName => $entity) {
            $this->addEntityType($entityName, $entity);

            $frontendPrefix = isset($entity['frontend_prefix']) ? $entity['frontend_prefix'] : '';
            $backendPrefix = isset($entity['backend_prefix']) ? $entity['backend_prefix'] : '';
            $sourcePrefix = isset($entity['source_prefix']) ? $entity['source_prefix'] : '';

            if (is_array($entity['attributes']) && !empty($entity['attributes'])) {
                foreach ($entity['attributes'] as $attrCode => $attr) {
                    if (!empty($attr['backend'])) {
                        if ('_' === $attr['backend']) {
                            $attr['backend'] = $backendPrefix;
                        } elseif ('_' === $attr['backend'][0]) {
                            $attr['backend'] = $backendPrefix . $attr['backend'];
                        }
                    }
                    if (!empty($attr['frontend'])) {
                        if ('_' === $attr['frontend']) {
                            $attr['frontend'] = $frontendPrefix;
                        } elseif ('_' === $attr['frontend'][0]) {
                            $attr['frontend'] = $frontendPrefix . $attr['frontend'];
                        }
                    }
                    if (!empty($attr['source'])) {
                        if ('_' === $attr['source']) {
                            $attr['source'] = $sourcePrefix;
                        } elseif ('_' === $attr['source'][0]) {
                            $attr['source'] = $sourcePrefix . $attr['source'];
                        }
                    }

                    $this->addAttribute($entityName, $attrCode, $attr);
                }
            }
            $this->setDefaultSetToEntityType($entityName);
        }

        return $this;
    }

    /**
     * Retrieve attribute table fields
     *
     * @return array
     */
    private function _getAttributeTableFields()
    {
        return $this->setup->getConnection()->describeTable($this->setup->getTable('eav_attribute'));
    }

    /**
     * Insert attribute and filter data
     *
     * @param array $data
     * @return $this
     */
    private function _insertAttribute(array $data)
    {
        $bind = [];

        $fields = $this->_getAttributeTableFields();

        foreach ($data as $k => $v) {
            if (isset($fields[$k])) {
                $bind[$k] = $this->setup->getConnection()->prepareColumnValue($fields[$k], $v);
            }
        }
        if (!$bind) {
            return $this;
        }

        $this->setup->getConnection()->insert($this->setup->getTable('eav_attribute'), $bind);
        $attributeId = $this->setup->getConnection()->lastInsertId($this->setup->getTable('eav_attribute'));
        $this->_insertAttributeAdditionalData(
            $data['entity_type_id'],
            array_merge(['attribute_id' => $attributeId], $data)
        );

        return $this;
    }

    /**
     * Insert attribute additional data
     *
     * @param int|string $entityTypeId
     * @param array $data
     * @return $this
     */
    private function _insertAttributeAdditionalData($entityTypeId, array $data)
    {
        $additionalTable = $this->getEntityType($entityTypeId, 'additional_attribute_table');
        if (!$additionalTable) {
            return $this;
        }
        $additionalTableExists = $this->setup->getConnection()->isTableExists($this->setup->getTable($additionalTable));
        if ($additionalTable && $additionalTableExists) {
            $bind = [];
            $fields = $this->setup->getConnection()->describeTable($this->setup->getTable($additionalTable));
            foreach ($data as $k => $v) {
                if (isset($fields[$k])) {
                    $bind[$k] = $this->setup->getConnection()->prepareColumnValue($fields[$k], $v);
                }
            }
            if (!$bind) {
                return $this;
            }
            $this->setup->getConnection()->insert($this->setup->getTable($additionalTable), $bind);
        }

        return $this;
    }
}
