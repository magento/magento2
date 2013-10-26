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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Product Flat Indexer Resource Model
 */
namespace Magento\Catalog\Model\Resource\Product\Flat;

class Indexer extends \Magento\Index\Model\Resource\AbstractResource
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    private $_eavConfig;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    private $_attributeConfig;

    /**
     * Attribute codes for flat
     *
     * @var array
     */
    protected $_attributeCodes;

    /**
     * Attribute objects for flat cache
     *
     * @var array
     */
    protected $_attributes;

    /**
     * Required system attributes for preload
     *
     * @var array
     */
    protected $_systemAttributes     = array('status', 'required_options', 'tax_class_id', 'weight');

    /**
     * Eav Catalog_Product Entity Type Id
     *
     * @var int
     */
    protected $_entityTypeId;

    /**
     * Flat table columns cache
     *
     * @var array
     */
    protected $_columns;

    /**
     * Flat table indexes cache
     *
     * @var array
     */
    protected $_indexes;

    /**
     * Product Type Instances cache
     *
     * @var array
     */
    protected $_productTypes;

    /**
     * Exists flat tables cache
     *
     * @var array
     */
    protected $_existsFlatTables     = array();

    /**
     * Flat tables which were prepared
     *
     * @var array
     */
    protected $_preparedFlatTables   = array();

    /**
     * Catalog product flat
     *
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_catalogProductFlat = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var int
     */
    protected $_maxIndexCount;

    /**
     * @var array
     */
    protected $_flatAttributeGroups;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_productType;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Eav attribute factory
     *
     * @var \Magento\Catalog\Model\Resource\Eav\AttributeFactory
     */
    protected $_eavAttributeFactory;

    /**
     * Config factory
     *
     * @var \Magento\Catalog\Model\Resource\ConfigFactory
     */
    protected $_configFactory;

    /**
     * Catalog resource helper
     *
     * @var \Magento\Catalog\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Resource\ConfigFactory $configFactory
     * @param \Magento\Catalog\Model\Resource\Eav\AttributeFactory $eavAttributeFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Core\Model\Resource $resource
     * @param \Magento\Core\Model\Logger $logger
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Attribute\Config $attributeConfig
     * @param \Magento\Catalog\Helper\Product\Flat $catalogProductFlat
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Catalog\Model\Resource\Helper $resourceHelper
     * @param $maxIndexCount
     * @param array $flatAttributeGroups
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Resource\ConfigFactory $configFactory,
        \Magento\Catalog\Model\Resource\Eav\AttributeFactory $eavAttributeFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Core\Model\Resource $resource,
        \Magento\Core\Model\Logger $logger,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Attribute\Config $attributeConfig,
        \Magento\Catalog\Helper\Product\Flat $catalogProductFlat,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Catalog\Model\Resource\Helper $resourceHelper,
        $maxIndexCount,
        array $flatAttributeGroups = array()
    ) {
        $this->_configFactory = $configFactory;
        $this->_eavAttributeFactory = $eavAttributeFactory;
        $this->_storeManager = $storeManager;
        $this->_eventManager = $eventManager;
        $this->_coreData = $coreData;
        $this->_eavConfig = $eavConfig;
        $this->_attributeConfig = $attributeConfig;
        $this->_catalogProductFlat = $catalogProductFlat;
        $this->_maxIndexCount = intval($maxIndexCount);
        $this->_flatAttributeGroups = $flatAttributeGroups;
        $this->_logger = $logger;
        $this->_productType = $productType;
        $this->_resourceHelper = $resourceHelper;
        parent::__construct($resource);
    }

    /**
     * Initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Rebuild Catalog Product Flat Data
     *
     * @param \Magento\Core\Model\Store|int $store
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function rebuild($store = null)
    {
        if ($store === null) {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->rebuild($store->getId());
            }
            return $this;
        }

        $storeId = (int)$this->_storeManager->getStore($store)->getId();

        $this->prepareFlatTable($storeId);
        $this->cleanNonWebsiteProducts($storeId);
        $this->updateStaticAttributes($storeId);
        $this->updateEavAttributes($storeId);
        $this->updateEventAttributes($storeId);
        $this->updateRelationProducts($storeId);
        $this->cleanRelationProducts($storeId);

        $flag = $this->getFlatHelper()->getFlag();
        $flag->setIsBuilt(true)->save();
        return $this;
    }

    /**
     * Retrieve Catalog Product Flat helper
     *
     * @return \Magento\Catalog\Helper\Product\Flat
     */
    public function getFlatHelper()
    {
        return $this->_catalogProductFlat;
    }

    /**
     * Retrieve attribute codes using for flat
     *
     * @return array
     */
    public function getAttributeCodes()
    {
        if ($this->_attributeCodes === null) {
            $adapter = $this->_getReadAdapter();
            $this->_attributeCodes = array();

            foreach ($this->_flatAttributeGroups as $attributeGroupName) {
                $attributes = $this->_attributeConfig->getAttributeNames($attributeGroupName);
                $this->_systemAttributes = array_unique(array_merge($attributes, $this->_systemAttributes));
            }

            $bind = array(
                'backend_type'      => \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::TYPE_STATIC,
                'entity_type_id'    => $this->getEntityTypeId()
            );

            $select = $adapter->select()
                ->from(array('main_table' => $this->getTable('eav_attribute')))
                ->join(
                    array('additional_table' => $this->getTable('catalog_eav_attribute')),
                    'additional_table.attribute_id = main_table.attribute_id'
                )
                ->where('main_table.entity_type_id = :entity_type_id');
            $whereCondition = array(
                'main_table.backend_type = :backend_type',
                $adapter->quoteInto('additional_table.is_used_for_promo_rules = ?', 1),
                $adapter->quoteInto('additional_table.used_in_product_listing = ?', 1),
                $adapter->quoteInto('additional_table.used_for_sort_by = ?', 1),
                $adapter->quoteInto('main_table.attribute_code IN(?)', $this->_systemAttributes)
            );
            if ($this->getFlatHelper()->isAddFilterableAttributes()) {
                $whereCondition[] = $adapter->quoteInto('additional_table.is_filterable > ?', 0);
            }

            $select->where(implode(' OR ', $whereCondition));
            $attributesData = $adapter->fetchAll($select, $bind);
            $this->_eavConfig->importAttributesData($this->getEntityType(), $attributesData);

            foreach ($attributesData as $data) {
                $this->_attributeCodes[$data['attribute_id']] = $data['attribute_code'];
            }
            unset($attributesData);
        }

        return $this->_attributeCodes;
    }

    /**
     * Retrieve entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return \Magento\Catalog\Model\Product::ENTITY;
    }

    /**
     * Retrieve Catalog Entity Type Id
     *
     * @return int
     */
    public function getEntityTypeId()
    {
        if ($this->_entityTypeId === null) {
            $this->_entityTypeId = $this->_configFactory->create()
                ->getEntityTypeId();
        }
        return $this->_entityTypeId;
    }

    /**
     * Retrieve attribute objects for flat
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_attributes === null) {
            $this->_attributes = array();
            $attributeCodes    = $this->getAttributeCodes();
            $entity = $this->_eavConfig
                ->getEntityType($this->getEntityType())
                ->getEntity();

            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->_eavConfig
                    ->getAttribute($this->getEntityType(), $attributeCode)
                    ->setEntity($entity);
                try {
                    // check if exists source and backend model.
                    // To prevent exception when some module was disabled
                    $attribute->usesSource() && $attribute->getSource();
                    $attribute->getBackend();
                    $this->_attributes[$attributeCode] = $attribute;
                } catch (\Exception $e) {
                    $this->_logger->logException($e);
                }
            }
        }

        return $this->_attributes;
    }

    /**
     * Retrieve loaded attribute by code
     *
     * @param string $attributeCode
     * @throws \Magento\Core\Exception
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttribute($attributeCode)
    {
        $attributes = $this->getAttributes();
        if (!isset($attributes[$attributeCode])) {
            $attribute = $this->_eavAttributeFactory->create()
                ->loadByCode($this->getEntityTypeId(), $attributeCode);
            if (!$attribute->getId()) {
                throw new \Magento\Core\Exception(__('Invalid attribute %1', $attributeCode));
            }
            $entity = $this->_eavConfig
                ->getEntityType($this->getEntityType())
                ->getEntity();
            $attribute->setEntity($entity);

            return $attribute;
        }

        return $attributes[$attributeCode];
    }

    /**
     * Retrieve Catalog Product Flat Table name
     *
     * @param int $storeId
     * @return string
     */
    public function getFlatTableName($storeId)
    {
        return sprintf('%s_%s', $this->getTable('catalog_product_flat'), $storeId);
    }

    /**
     * Retrieve catalog product flat columns array in old format (used before MMDB support)
     *
     * @return array
     */
    protected function _getFlatColumnsOldDefinition()
    {
        $columns = array();
        $columns['entity_id'] = array(
            'type'      => 'int(10)',
            'unsigned'  => true,
            'is_null'   => false,
            'default'   => null,
            'extra'     => null
        );
        if ($this->getFlatHelper()->isAddChildData()) {
            $columns['child_id'] = array(
                'type'      => 'int(10)',
                'unsigned'  => true,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
            $columns['is_child'] = array(
                'type'      => 'tinyint(1)',
                'unsigned'  => true,
                'is_null'   => false,
                'default'   => 0,
                'extra'     => null
            );
        }
        $columns['attribute_set_id'] = array(
            'type'      => 'smallint(5)',
            'unsigned'  => true,
            'is_null'   => false,
            'default'   => 0,
            'extra'     => null
        );
        $columns['type_id'] = array(
            'type'      => 'varchar(32)',
            'unsigned'  => false,
            'is_null'   => false,
            'default'   => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            'extra'     => null
        );

        return $columns;
    }

    /**
     * Retrieve catalog product flat columns array in DDL format
     *
     * @return array
     */
    protected function _getFlatColumnsDdlDefinition()
    {
        $columns = array();
        $columns['entity_id'] = array(
            'type'      => \Magento\DB\Ddl\Table::TYPE_INTEGER,
            'length'    => null,
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => false,
            'primary'   => true,
            'comment'   => 'Entity Id'
        );
        if ($this->getFlatHelper()->isAddChildData()) {
            $columns['child_id'] = array(
                'type'      => \Magento\DB\Ddl\Table::TYPE_INTEGER,
                'length'    => null,
                'unsigned'  => true,
                'nullable'  => true,
                'default'   => null,
                'primary'   => true,
                'comment'   => 'Child Id'
            );
            $columns['is_child'] = array(
                'type'      => \Magento\DB\Ddl\Table::TYPE_SMALLINT,
                'length'    => 1,
                'unsigned'  => true,
                'nullable'  => false,
                'default'   => '0',
                'comment'   => 'Checks If Entity Is Child'
            );
        }
        $columns['attribute_set_id'] = array(
            'type'      => \Magento\DB\Ddl\Table::TYPE_SMALLINT,
            'length'    => 5,
            'unsigned'  => true,
            'nullable'  => false,
            'default'   => '0',
            'comment'   => 'Attribute Set Id'
        );
        $columns['type_id'] = array(
            'type'      => \Magento\DB\Ddl\Table::TYPE_TEXT,
            'length'    => 32,
            'unsigned'  => false,
            'nullable'  => false,
            'default'   => \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
            'comment'   => 'Type Id'
        );

        return $columns;
    }

    /**
     * Retrieve catalog product flat table columns array
     *
     * @return array
     */
    public function getFlatColumns()
    {
        if ($this->_columns === null) {
            if ($this->_coreData->useDbCompatibleMode()) {
                $this->_columns = $this->_getFlatColumnsOldDefinition();
            } else {
                $this->_columns = $this->_getFlatColumnsDdlDefinition();
            }

            foreach ($this->getAttributes() as $attribute) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute\AbstractAttribute */
                $columns = $attribute
                    ->setFlatAddFilterableAttributes($this->getFlatHelper()->isAddFilterableAttributes())
                    ->setFlatAddChildData($this->getFlatHelper()->isAddChildData())
                    ->getFlatColumns();
                if ($columns !== null) {
                    $this->_columns = array_merge($this->_columns, $columns);
                }
            }

            $columnsObject = new \Magento\Object();
            $columnsObject->setColumns($this->_columns);
            $this->_eventManager->dispatch('catalog_product_flat_prepare_columns',
                array('columns' => $columnsObject)
            );
            $this->_columns = $columnsObject->getColumns();
        }

        return $this->_columns;
    }

    /**
     * Retrieve catalog product flat table indexes array
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        if ($this->_indexes === null) {
            $this->_indexes = array();

            if ($this->getFlatHelper()->isAddChildData()) {
                $this->_indexes['PRIMARY'] = array(
                    'type'   => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY,
                    'fields' => array('entity_id', 'child_id')
                );
                $this->_indexes['IDX_CHILD'] = array(
                    'type'   => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX,
                    'fields' => array('child_id')
                );
                $this->_indexes['IDX_IS_CHILD'] = array(
                    'type'   => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX,
                    'fields' => array('entity_id', 'is_child')
                );
            } else {
                $this->_indexes['PRIMARY'] = array(
                    'type'   => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY,
                    'fields' => array('entity_id')
                );
            }
            $this->_indexes['IDX_TYPE_ID'] = array(
                'type'   => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX,
                'fields' => array('type_id')
            );
            $this->_indexes['IDX_ATTRIBUTE_SET'] = array(
                'type'   => \Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_INDEX,
                'fields' => array('attribute_set_id')
            );

            foreach ($this->getAttributes() as $attribute) {
                /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
                $indexes = $attribute
                    ->setFlatAddFilterableAttributes($this->getFlatHelper()->isAddFilterableAttributes())
                    ->setFlatAddChildData($this->getFlatHelper()->isAddChildData())
                    ->getFlatIndexes();
                if ($indexes !== null) {
                    $this->_indexes = array_merge($this->_indexes, $indexes);
                }
            }

            $indexesObject = new \Magento\Object();
            $indexesObject->setIndexes($this->_indexes);
            $this->_eventManager->dispatch('catalog_product_flat_prepare_indexes', array(
                'indexes'   => $indexesObject
            ));
            $this->_indexes = $indexesObject->getIndexes();
        }

        return $this->_indexes;
    }

    /**
     * Compare Flat style with Describe style columns
     * If column a different - return false
     *
     * @param array $column
     * @param array $describe
     * @return bool
     */
    protected function _compareColumnProperties($column, $describe)
    {
        return $this->_resourceHelper->compareIndexColumnProperties($column, $describe);
    }

    /**
     * Retrieve UNIQUE HASH for a Table foreign key
     *
     * @param string $priTableName  the target table name
     * @param string $priColumnName the target table column name
     * @param string $refTableName  the reference table name
     * @param string $refColumnName the reference table column name
     * @return string
     */
    public function getFkName($priTableName, $priColumnName, $refTableName, $refColumnName)
    {
        return $this->_resources->getFkName($priTableName, $priColumnName, $refTableName, $refColumnName);
    }

    /**
     * Prepare flat table for store
     *
     * @param int $storeId
     * @throws \Magento\Core\Exception
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function prepareFlatTable($storeId)
    {
        if (isset($this->_preparedFlatTables[$storeId])) {
            return $this;
        }
        $adapter   = $this->_getWriteAdapter();
        $tableName = $this->getFlatTableName($storeId);

        // Extract columns we need to have in flat table
        $columns = $this->getFlatColumns();
        if ($this->_coreData->useDbCompatibleMode()) {
             /* Convert old format of flat columns to new MMDB format that uses DDL types and definitions */
            foreach ($columns as $key => $column) {
                $columns[$key] = $this->_resourceHelper->convertOldColumnDefinition($column);
            }
        }

        // Extract indexes we need to have in flat table
        $indexesNeed  = $this->getFlatIndexes();

        if (count($indexesNeed) > $this->_maxIndexCount) {
            throw new \Magento\Core\Exception(__("Please make sure you don\'t have too many filterable and sortable attributes. You now have %1\$d. The Flat Catalog module allows only %2\$d.", count($indexesNeed), $maxIndex));
        }

        // Process indexes to create names for them in MMDB-style and reformat to common index definition
        $indexKeys = array();
        $indexProps = array_values($indexesNeed);
        $upperPrimaryKey = strtoupper(\Magento\DB\Adapter\AdapterInterface::INDEX_TYPE_PRIMARY);
        foreach ($indexProps as $i => $indexProp) {
            $indexName = $adapter->getIndexName($tableName, $indexProp['fields'], $indexProp['type']);
            $indexProp['type'] = strtoupper($indexProp['type']);
            if ($indexProp['type'] == $upperPrimaryKey) {
                $indexKey = $upperPrimaryKey;
            } else {
                $indexKey = $indexName;
            }

            $indexProps[$i] = array(
                'KEY_NAME' => $indexName,
                'COLUMNS_LIST' => $indexProp['fields'],
                'INDEX_TYPE' => strtolower($indexProp['type'])
            );
            $indexKeys[$i] = $indexKey;
        }
        $indexesNeed = array_combine($indexKeys, $indexProps); // Array with index names as keys, except for primary

        // Foreign keys
        $foreignEntityKey = $this->getFkName($tableName, 'entity_id', 'catalog_product_entity', 'entity_id');
        $foreignChildKey  = $this->getFkName($tableName, 'child_id', 'catalog_product_entity', 'entity_id');

        // Create table or modify existing one
        if (!$this->_isFlatTableExists($storeId)) {
            /** @var $table \Magento\DB\Ddl\Table */
            $table = $adapter->newTable($tableName);
            foreach ($columns as $fieldName => $fieldProp) {
                $table->addColumn(
                    $fieldName,
                    $fieldProp['type'],
                    isset($fieldProp['length']) ? $fieldProp['length'] : null,
                    array(
                        'nullable' => isset($fieldProp['nullable']) ? (bool)$fieldProp['nullable'] : false,
                        'unsigned' => isset($fieldProp['unsigned']) ? (bool)$fieldProp['unsigned'] : false,
                        'default'  => isset($fieldProp['default']) ? $fieldProp['default'] : false,
                        'primary'  => false,
                    ),
                    isset($fieldProp['comment']) ? $fieldProp['comment'] : $fieldName
                );
            }

            foreach ($indexesNeed as $indexProp) {
                $table->addIndex($indexProp['KEY_NAME'], $indexProp['COLUMNS_LIST'],
                    array('type' => $indexProp['INDEX_TYPE']));
            }

            $table->addForeignKey($foreignEntityKey,
                'entity_id', $this->getTable('catalog_product_entity'), 'entity_id',
                \Magento\DB\Ddl\Table::ACTION_CASCADE, \Magento\DB\Ddl\Table::ACTION_CASCADE);

            if ($this->getFlatHelper()->isAddChildData()) {
                $table->addForeignKey($foreignChildKey,
                    'child_id', $this->getTable('catalog_product_entity'), 'entity_id',
                    \Magento\DB\Ddl\Table::ACTION_CASCADE, \Magento\DB\Ddl\Table::ACTION_CASCADE);
            }
            $table->setComment("Catalog Product Flat (Store {$storeId})");

            $adapter->createTable($table);

            $this->_existsFlatTables[$storeId] = true;
        } else {
            $adapter->resetDdlCache($tableName);

            // Sort columns into added/altered/dropped lists
            $describe   = $adapter->describeTable($tableName);
            $addColumns     = array_diff_key($columns, $describe);
            $dropColumns    = array_diff_key($describe, $columns);
            $modifyColumns  = array();
            foreach ($columns as $field => $fieldProp) {
                if (isset($describe[$field]) && !$this->_compareColumnProperties($fieldProp, $describe[$field])) {
                    $modifyColumns[$field] = $fieldProp;
                }
            }

            // Sort indexes into added/dropped lists. Altered indexes are put into both lists.
            $addIndexes = array();
            $dropIndexes = array();
            $indexesNow  = $adapter->getIndexList($tableName); // Note: primary is always stored under 'PRIMARY' key
            $newIndexes = $indexesNeed;
            foreach ($indexesNow as $key => $indexNow) {
                if (isset($indexesNeed[$key])) {
                    $indexNeed = $indexesNeed[$key];
                    if (($indexNeed['INDEX_TYPE'] != $indexNow['INDEX_TYPE'])
                        || ($indexNeed['COLUMNS_LIST'] != $indexNow['COLUMNS_LIST'])) {
                        $dropIndexes[$key] = $indexNow;
                        $addIndexes[$key] = $indexNeed;
                    }
                    unset($newIndexes[$key]);
                } else {
                    $dropIndexes[$key] = $indexNow;
                }
            }
            $addIndexes = $addIndexes + $newIndexes;

            // Compose contstraints
            $addConstraints = array();
            $addConstraints[$foreignEntityKey] = array(
                'table_index'   => 'entity_id',
                'ref_table'     => $this->getTable('catalog_product_entity'),
                'ref_index'     => 'entity_id',
                'on_update'     => \Magento\DB\Ddl\Table::ACTION_CASCADE,
                'on_delete'     => \Magento\DB\Ddl\Table::ACTION_CASCADE
            );

            // Additional data from childs
            $isAddChildData = $this->getFlatHelper()->isAddChildData();
            if (!$isAddChildData && isset($describe['is_child'])) {
                $adapter->delete($tableName, array('is_child = ?' => 1));
                $adapter->dropForeignKey($tableName, $foreignChildKey);
            }
            if ($isAddChildData && !isset($describe['is_child'])) {
                $adapter->delete($tableName);
                $dropIndexes['PRIMARY'] = $indexesNow['PRIMARY'];
                $addIndexes['PRIMARY']  = $indexesNeed['PRIMARY'];

                $addConstraints[$foreignChildKey] = array(
                    'table_index'   => 'child_id',
                    'ref_table'     => $this->getTable('catalog_product_entity'),
                    'ref_index'     => 'entity_id',
                    'on_update'     => \Magento\DB\Ddl\Table::ACTION_CASCADE,
                    'on_delete'     => \Magento\DB\Ddl\Table::ACTION_CASCADE
                );
            }

            // Drop constraints
            foreach (array_keys($adapter->getForeignKeys($tableName)) as $constraintName) {
                $adapter->dropForeignKey($tableName, $constraintName);
            }

            // Drop indexes
            foreach ($dropIndexes as $indexProp) {
                $adapter->dropIndex($tableName, $indexProp['KEY_NAME']);
            }

            // Drop columns
            foreach (array_keys($dropColumns) as $columnName) {
                $adapter->dropColumn($tableName, $columnName);
            }

            // Modify columns
            foreach ($modifyColumns as $columnName => $columnProp) {
                $columnProp = array_change_key_case($columnProp, CASE_UPPER);
                if (!isset($columnProp['COMMENT'])) {
                    $columnProp['COMMENT'] = ucwords(str_replace('_', ' ', $columnName));
                }
                $adapter->changeColumn($tableName, $columnName, $columnName, $columnProp);
            }

            // Add columns
            foreach ($addColumns as $columnName => $columnProp) {
                $columnProp = array_change_key_case($columnProp, CASE_UPPER);
                if (!isset($columnProp['COMMENT'])) {
                    $columnProp['COMMENT'] = ucwords(str_replace('_', ' ', $columnName));
                }
                $adapter->addColumn($tableName, $columnName, $columnProp);
            }

            // Add indexes
            foreach ($addIndexes as $indexProp) {
                $adapter->addIndex($tableName, $indexProp['KEY_NAME'], $indexProp['COLUMNS_LIST'],
                    $indexProp['INDEX_TYPE']);
            }

            // Add constraints
            foreach ($addConstraints as $constraintName => $constraintProp) {
                $adapter->addForeignKey($constraintName, $tableName,
                    $constraintProp['table_index'],
                    $constraintProp['ref_table'],
                    $constraintProp['ref_index'],
                    $constraintProp['on_delete'],
                    $constraintProp['on_update']
                );
            }
        }

        $this->_preparedFlatTables[$storeId] = true;

        return $this;
    }

    /**
     * Add or Update static attributes
     *
     * @param int $storeId
     * @param int|array $productIds update only product(s)
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function updateStaticAttributes($storeId, $productIds = null)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }
        $adapter   = $this->_getWriteAdapter();
        $websiteId = (int)$this->_storeManager->getStore($storeId)->getWebsite()->getId();
        /* @var $status \Magento\Eav\Model\Entity\Attribute */
        $status    = $this->getAttribute('status');

        $fieldList  = array('entity_id', 'type_id', 'attribute_set_id');
        $colsList   = array('entity_id', 'type_id', 'attribute_set_id');
        if ($this->getFlatHelper()->isAddChildData()) {
            $fieldList = array_merge($fieldList, array('child_id', 'is_child'));
            $isChild   = new \Zend_Db_Expr('0');
            $colsList  = array_merge($colsList, array('entity_id', $isChild));
        }

        $columns    = $this->getFlatColumns();
        $bind       = array(
            'website_id'     => $websiteId,
            'store_id'       => $storeId,
            'entity_type_id' => (int)$status->getEntityTypeId(),
            'attribute_id'   => (int)$status->getId()
        );

        $fieldExpr = $adapter->getCheckSql('t2.value_id > 0', 't2.value', 't1.value');
        $select     = $this->_getWriteAdapter()->select()
            ->from(array('e' => $this->getTable('catalog_product_entity')), $colsList)
            ->join(
                array('wp' => $this->getTable('catalog_product_website')),
                'e.entity_id = wp.product_id AND wp.website_id = :website_id',
                array())
            ->joinLeft(
                array('t1' => $status->getBackend()->getTable()),
                'e.entity_id = t1.entity_id',
                array())
            ->joinLeft(
                array('t2' => $status->getBackend()->getTable()),
                't2.entity_id = t1.entity_id'
                    . ' AND t1.entity_type_id = t2.entity_type_id'
                    . ' AND t1.attribute_id = t2.attribute_id'
                    . ' AND t2.store_id = :store_id',
                array())
            ->where('t1.entity_type_id = :entity_type_id')
            ->where('t1.attribute_id = :attribute_id')
            ->where('t1.store_id = ?', \Magento\Core\Model\AppInterface::ADMIN_STORE_ID)
            ->where("{$fieldExpr} = ?", \Magento\Catalog\Model\Product\Status::STATUS_ENABLED);
        foreach ($this->getAttributes() as $attributeCode => $attribute) {
            /** @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if ($attribute->getBackend()->getType() == 'static') {
                if (!isset($columns[$attributeCode])) {
                    continue;
                }
                $fieldList[] = $attributeCode;
                $select->columns($attributeCode, 'e');
            }
        }

        if ($productIds !== null) {
            $select->where('e.entity_id IN(?)', $productIds);
        }

        $sql = $select->insertFromSelect($this->getFlatTableName($storeId), $fieldList);
        $adapter->query($sql, $bind);

        return $this;
    }

    /**
     * Remove non website products
     *
     * @param int $storeId
     * @param int|array $productIds
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function cleanNonWebsiteProducts($storeId, $productIds = null)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        $websiteId = (int)$this->_storeManager->getStore($storeId)->getWebsite()->getId();
        $adapter   = $this->_getWriteAdapter();

        $joinCondition = array(
            'e.entity_id = wp.product_id',
            'wp.website_id = :website_id'
        );
        if ($this->getFlatHelper()->isAddChildData()) {
            $joinCondition[] = 'e.child_id = wp.product_id';
        }
        $bind   = array('website_id'    => $websiteId);
        $select = $adapter->select()
            ->from(array('e' => $this->getFlatTableName($storeId)), null)
            ->joinLeft(
                array('wp' => $this->getTable('catalog_product_website')),
                implode(' AND ', $joinCondition),
                array());
        if ($productIds !== null) {
            $condition = array(
                $adapter->quoteInto('e.entity_id IN(?)', $productIds)
            );
            if ($this->getFlatHelper()->isAddChildData()) {
                $condition[] = $adapter->quoteInto('e.child_id IN(?)', $productIds);
            }
            $select->where(implode(' OR ', $condition));
        }

        $sql = $select->deleteFromSelect('e');
        $adapter->query($sql, $bind);

        return $this;
    }

    /**
     * Update attribute flat data
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param int $storeId
     * @param int|array $productIds update only product(s)
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function updateAttribute($attribute, $storeId, $productIds = null)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }
        $adapter       = $this->_getWriteAdapter();
        $flatTableName = $this->getFlatTableName($storeId);
        $describe      = $adapter->describeTable($flatTableName);

        if ($attribute->getBackend()->getType() == 'static') {
            if (!isset($describe[$attribute->getAttributeCode()])) {
                return $this;
            }

            $select = $adapter->select()
                ->join(
                    array('main_table' => $this->getTable('catalog_product_entity')),
                    'main_table.entity_id = e.entity_id',
                    array($attribute->getAttributeCode() => 'main_table.' . $attribute->getAttributeCode())
                );
            if ($this->getFlatHelper()->isAddChildData()) {
                $select->where('e.is_child = ?', 0);
            }
            if ($productIds !== null) {
                $select->where('main_table.entity_id IN(?)', $productIds);
            }

            $sql = $select->crossUpdateFromSelect(array('e' => $flatTableName));
            $adapter->query($sql);
        } else {
            $columns = $attribute->getFlatColumns();
            if (!$columns) {
                return $this;
            }
            foreach (array_keys($columns) as $columnName) {
                if (!isset($describe[$columnName])) {
                    return $this;
                }
            }

            $select = $attribute->getFlatUpdateSelect($storeId);
            if ($select instanceof \Magento\DB\Select) {
                if ($productIds !== null) {
                    $select->where('e.entity_id IN(?)', $productIds);
                }

                $sql = $select->crossUpdateFromSelect(array('e' => $flatTableName));
                $adapter->query($sql);
            }
        }

        return $this;
    }

    /**
     * Update non static EAV attributes flat data
     *
     * @param int $storeId
     * @param int|array $productIds update only product(s)
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function updateEavAttributes($storeId, $productIds = null)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        foreach ($this->getAttributes() as $attribute) {
            /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
            if ($attribute->getBackend()->getType() != 'static') {
                $this->updateAttribute($attribute, $storeId, $productIds);
            }
        }
        return $this;
    }

    /**
     * Update events observer attributes
     *
     * @param int $storeId
     */
    public function updateEventAttributes($storeId = null)
    {
        $this->_eventManager->dispatch('catalog_product_flat_rebuild', array(
            'store_id' => $storeId,
            'table'    => $this->getFlatTableName($storeId)
        ));
    }

    /**
     * Retrieve Product Type Instances
     * as key - type code, value - instance model
     *
     * @return array
     */
    public function getProductTypeInstances()
    {
        if ($this->_productTypes === null) {
            $this->_productTypes = array();
            $productEmulator     = new \Magento\Object();

            foreach (array_keys($this->_productType->getTypes()) as $typeId) {
                $productEmulator->setTypeId($typeId);
                $this->_productTypes[$typeId] = $this->_productType
                    ->factory($productEmulator);
            }
        }
        return $this->_productTypes;
    }

    /**
     * Update relation products
     *
     * @param int $storeId
     * @param int|array $productIds Update child product(s) only
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function updateRelationProducts($storeId, $productIds = null)
    {
        if (!$this->getFlatHelper()->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        $adapter = $this->_getWriteAdapter();

        foreach ($this->getProductTypeInstances() as $typeInstance) {
            if (!$typeInstance->isComposite()) {
                continue;
            }
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $columns    = $this->getFlatColumns();
                $fieldList  = array_keys($columns);
                unset($columns['entity_id']);
                unset($columns['child_id']);
                unset($columns['is_child']);

                $select = $adapter->select()
                    ->from(
                        array('t' => $this->getTable($relation->getTable())),
                        array($relation->getParentFieldName(), $relation->getChildFieldName(), new \Zend_Db_Expr('1')))
                    ->join(
                        array('e' => $this->getFlatTableName($storeId)),
                        "e.entity_id = t.{$relation->getChildFieldName()}",
                        array_keys($columns)
                    );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                }
                if ($productIds !== null) {
                    $cond = array(
                        $adapter->quoteInto("{$relation->getChildFieldName()} IN(?)", $productIds),
                        $adapter->quoteInto("{$relation->getParentFieldName()} IN(?)", $productIds)
                    );

                    $select->where(implode(' OR ', $cond));
                }
                $sql = $select->insertFromSelect($this->getFlatTableName($storeId), $fieldList);
                $adapter->query($sql);
            }
        }

        return $this;
    }

    /**
     * Update children data from parent
     *
     * @param int $storeId
     * @param int|array $productIds
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function updateChildrenDataFromParent($storeId, $productIds = null)
    {
        if (!$this->getFlatHelper()->isAddChildData() || !$this->_isFlatTableExists($storeId)) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select();
        foreach (array_keys($this->getFlatColumns()) as $columnName) {
            if ($columnName == 'entity_id' || $columnName == 'child_id' || $columnName == 'is_child') {
                continue;
            }
            $select->columns(array($columnName => new \Zend_Db_Expr('t1.' . $columnName)));
        }
        $select
            ->joinLeft(
                array('t1' => $this->getFlatTableName($storeId)),
                $adapter->quoteInto('t2.child_id = t1.entity_id AND t1.is_child = ?', 0),
                array())
            ->where('t2.is_child = ?', 1);

        if ($productIds !== null) {
            $select->where('t2.child_id IN(?)', $productIds);
        }

        $sql = $select->crossUpdateFromSelect(array('t2' => $this->getFlatTableName($storeId)));
        $adapter->query($sql);

        return $this;
    }

    /**
     * Clean unused relation products
     *
     * @param int $storeId
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function cleanRelationProducts($storeId)
    {
        if (!$this->getFlatHelper()->isAddChildData()) {
            return $this;
        }

        foreach ($this->getProductTypeInstances() as $typeInstance) {
            if (!$typeInstance->isComposite()) {
                continue;
            }
            $adapter  = $this->_getWriteAdapter();
            $relation = $typeInstance->getRelationInfo();
            if ($relation
                && $relation->getTable()
                && $relation->getParentFieldName()
                && $relation->getChildFieldName()
            ) {
                $select = $this->_getWriteAdapter()->select()
                    ->distinct(true)
                    ->from(
                        $this->getTable($relation->getTable()),
                        "{$relation->getParentFieldName()}"
                    );
                $joinLeftCond = array(
                    "e.entity_id = t.{$relation->getParentFieldName()}",
                    "e.child_id = t.{$relation->getChildFieldName()}"
                );
                if ($relation->getWhere() !== null) {
                    $select->where($relation->getWhere());
                    $joinLeftCond[] = $relation->getWhere();
                }

                $entitySelect = new \Zend_Db_Expr($select->__toString());

                $select = $adapter->select()
                    ->from(array('e' => $this->getFlatTableName($storeId)), null)
                    ->joinLeft(
                        array('t' => $this->getTable($relation->getTable())),
                        implode(' AND ', $joinLeftCond),
                        array())
                    ->where('e.is_child = ?', 1)
                    ->where('e.entity_id IN(?)', $entitySelect)
                    ->where("t.{$relation->getChildFieldName()} IS NULL");

                $sql = $select->deleteFromSelect('e');
                $adapter->query($sql);
            }
        }

        return $this;
    }

    /**
     * Remove product data from flat
     *
     * @param int|array $productIds
     * @param int $storeId
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function removeProduct($productIds, $storeId)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }
        $adapter = $this->_getWriteAdapter();
        $cond = array(
            $adapter->quoteInto('entity_id IN(?)', $productIds)
        );
        if ($this->getFlatHelper()->isAddChildData()) {
            $cond[] = $adapter->quoteInto('child_id IN(?)', $productIds);
        }
        $cond = implode(' OR ', $cond);
        $adapter->delete($this->getFlatTableName($storeId), $cond);

        return $this;
    }

    /**
     * Remove children from parent product
     *
     * @param int|array $productIds
     * @param int $storeId
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function removeProductChildren($productIds, $storeId)
    {
        if (!$this->getFlatHelper()->isAddChildData()) {
            return $this;
        }
        $whereExpr = array(
            'entity_id IN(?)' => $productIds,
            'is_child = ?'    => 1
        );
        $this->_getWriteAdapter()->delete($this->getFlatTableName($storeId), $whereExpr);

        return $this;
    }

    /**
     * Update flat data for product
     *
     * @param int|array $productIds
     * @param int $storeId
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function updateProduct($productIds, $storeId)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        $this->saveProduct($productIds, $storeId);

        $this->_eventManager->dispatch('catalog_product_flat_update_product', array(
            'store_id'      => $storeId,
            'table'         => $this->getFlatTableName($storeId),
            'product_ids'   => $productIds
        ));

        return $this;
    }

    /**
     * Save product(s) data for store
     *
     * @param int|array $productIds
     * @param int $storeId
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function saveProduct($productIds, $storeId)
    {
        if (!$this->_isFlatTableExists($storeId)) {
            return $this;
        }

        $this->updateStaticAttributes($storeId, $productIds);
        $this->updateEavAttributes($storeId, $productIds);

        return $this;
    }

    /**
     * Delete flat table process
     *
     * @param int $storeId
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function deleteFlatTable($storeId)
    {
        if ($this->_isFlatTableExists($storeId)) {
            $this->_getWriteAdapter()->dropTable($this->getFlatTableName($storeId));
        }

        return $this;
    }

    /**
     * Check is flat table for store exists
     *
     * @param int $storeId
     * @return bool
     */
    protected function _isFlatTableExists($storeId)
    {
        if (!isset($this->_existsFlatTables[$storeId])) {
            $tableName     = $this->getFlatTableName($storeId);
            $isTableExists = $this->_getWriteAdapter()->isTableExists($tableName);

            $this->_existsFlatTables[$storeId] = $isTableExists ? true : false;
        }

        return $this->_existsFlatTables[$storeId];
    }

    /**
     * Retrieve previous key from array by key
     *
     * @param array $array
     * @param mixed $key
     * @return mixed
     */
    protected function _arrayPrevKey(array $array, $key)
    {
        $prev = false;
        foreach (array_keys($array) as $k) {
            if ($k == $key) {
                return $prev;
            }
            $prev = $k;
        }
        return false;
    }

    /**
     * Retrieve next key from array by key
     *
     * @param array $array
     * @param mixed $key
     * @return mixed
     */
    protected function _arrayNextKey(array $array, $key)
    {
        $next = false;
        foreach (array_keys($array) as $k) {
            if ($next === true) {
                return $k;
            }
            if ($k == $key) {
                $next = true;
            }
        }
        return false;
    }

    /**
     * Transactional rebuild Catalog Product Flat Data
     *
     * @return \Magento\Catalog\Model\Resource\Product\Flat\Indexer
     */
    public function reindexAll()
    {
        foreach ($this->_storeManager->getStores() as $storeId => $store) {
            $this->prepareFlatTable($storeId);
            $this->beginTransaction();
            try {
                $this->rebuild($store);
                $this->commit();
           } catch (\Exception $e) {
                $this->rollBack();
                throw $e;
           }
        }

        return $this;
    }
}
