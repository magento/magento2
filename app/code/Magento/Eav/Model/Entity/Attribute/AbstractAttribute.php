<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Framework\Api\AttributeDataBuilder;

/**
 * Entity/Attribute/Model - attribute abstract
 */
abstract class AbstractAttribute extends \Magento\Framework\Model\AbstractExtensibleModel implements
    AttributeInterface,
    \Magento\Eav\Api\Data\AttributeInterface
{
    const TYPE_STATIC = 'static';

    /**
     * Attribute name
     *
     * @var string
     */
    protected $_name;

    /**
     * Entity instance
     *
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected $_entity;

    /**
     * Backend instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     */
    protected $_backend;

    /**
     * Frontend instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
     */
    protected $_frontend;

    /**
     * Source instance
     *
     * @var \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     */
    protected $_source;

    /**
     * Attribute id cache
     *
     * @var array
     */
    protected $_attributeIdCache = [];

    /**
     * Attribute data table name
     *
     * @var string
     */
    protected $_dataTable = null;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_eavTypeFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Eav\Model\Resource\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionDataBuilder
     */
    protected $optionDataBuilder;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\MetadataServiceInterface $metadataService
     * @param AttributeDataBuilder $customAttributeBuilder
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Eav\Model\Resource\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionDataBuilder $optionDataBuilder
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\MetadataServiceInterface $metadataService,
        AttributeDataBuilder $customAttributeBuilder,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\Resource\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionDataBuilder $optionDataBuilder,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $metadataService,
            $customAttributeBuilder,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_coreData = $coreData;
        $this->_eavConfig = $eavConfig;
        $this->_eavTypeFactory = $eavTypeFactory;
        $this->_storeManager = $storeManager;
        $this->_resourceHelper = $resourceHelper;
        $this->_universalFactory = $universalFactory;
        $this->optionDataBuilder = $optionDataBuilder;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Eav\Model\Resource\Entity\Attribute');
    }

    /**
     * Load attribute data by code
     *
     * @param  string|int|\Magento\Eav\Model\Entity\Type $entityType
     * @param  string $code
     * @return $this
     * @throws \Magento\Eav\Exception
     */
    public function loadByCode($entityType, $code)
    {
        \Magento\Framework\Profiler::start('load_by_code');
        if (is_numeric($entityType)) {
            $entityTypeId = $entityType;
        } elseif (is_string($entityType)) {
            $entityType = $this->_eavTypeFactory->create()->loadByCode($entityType);
        }
        if ($entityType instanceof \Magento\Eav\Model\Entity\Type) {
            $entityTypeId = $entityType->getId();
        }
        if (empty($entityTypeId)) {
            throw new \Magento\Eav\Exception(__('Invalid entity supplied'));
        }
        $this->_getResource()->loadByCode($this, $entityTypeId, $code);
        $this->_afterLoad();
        \Magento\Framework\Profiler::stop('load_by_code');
        return $this;
    }

    /**
     * Get attribute name
     *
     * @return string
     */
    public function getName()
    {
        return $this->_getData('attribute_code');
    }

    /**
     * Specify attribute identifier
     *
     * @param   int $data
     * @return  $this
     */
    public function setAttributeId($data)
    {
        $this->_data['attribute_id'] = $data;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeId()
    {
        return $this->_getData('attribute_id');
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setAttributeCode($data)
    {
        return $this->setData('attribute_code', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCode()
    {
        return $this->_getData('attribute_code');
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setAttributeModel($data)
    {
        return $this->setData('attribute_model', $data);
    }

    /**
     * @return array
     */
    public function getAttributeModel()
    {
        return $this->_getData('attribute_model');
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setBackendType($data)
    {
        return $this->setData('backend_type', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendType()
    {
        return $this->_getData('backend_type');
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setBackendModel($data)
    {
        return $this->setData('backend_model', $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackendModel()
    {
        return $this->_getData('backend_model');
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setBackendTable($data)
    {
        return $this->setData('backend_table', $data);
    }

    /**
     * @return bool
     */
    public function getIsVisibleOnFront()
    {
        return $this->_getData('is_visible_on_front');
    }

    /**
     * @return string|int|bool|float
     */
    public function getDefaultValue()
    {
        return $this->_getData('default_value');
    }

    /**
     * @return int
     */
    public function getAttributeSetId()
    {
        return $this->_getData('attribute_set_id');
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setAttributeSetId($id)
    {
        $this->_data['attribute_set_id'] = $id;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityTypeId()
    {
        return $this->_getData('entity_type_id');
    }

    /**
     * @param int|string $id
     * @return $this
     */
    public function setEntityTypeId($id)
    {
        $this->_data['entity_type_id'] = $id;
        return $this;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setEntityType($type)
    {
        $this->setData('entity_type', $type);
        return $this;
    }

    /**
     * Get attribute alias as "entity_type/attribute_code"
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity exclude this entity
     * @return string
     */
    public function getAlias($entity = null)
    {
        $alias = '';
        if ($entity === null || $entity->getType() !== $this->getEntity()->getType()) {
            $alias .= $this->getEntity()->getType() . '/';
        }
        $alias .= $this->getAttributeCode();

        return $alias;
    }

    /**
     * Set attribute name
     *
     * @param   string $name
     * @return $this
     */
    public function setName($name)
    {
        return $this->setData('attribute_code', $name);
    }

    /**
     * Retrieve entity type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->_eavConfig->getEntityType($this->getEntityTypeId());
    }

    /**
     * Set attribute entity instance
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        return $this;
    }

    /**
     * Retrieve entity instance
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     */
    public function getEntity()
    {
        if (!$this->_entity) {
            $this->_entity = $this->getEntityType();
        }
        return $this->_entity;
    }

    /**
     * Retrieve entity type
     *
     * @return string
     */
    public function getEntityIdField()
    {
        return $this->getEntity()->getValueEntityIdField();
    }

    /**
     * Retrieve backend instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
     * @throws \Magento\Framework\Model\Exception
     */
    public function getBackend()
    {
        if (empty($this->_backend)) {
            if (!$this->getBackendModel()) {
                $this->setBackendModel($this->_getDefaultBackendModel());
            }
            $backend = $this->_universalFactory->create($this->getBackendModel());
            if (!$backend) {
                throw new \Magento\Eav\Exception(__('Invalid backend model specified: ' . $this->getBackendModel()));
            }
            $this->_backend = $backend->setAttribute($this);
        }

        return $this->_backend;
    }

    /**
     * Retrieve frontend instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend
     */
    public function getFrontend()
    {
        if (empty($this->_frontend)) {
            if (!$this->getFrontendModel()) {
                $this->setFrontendModel($this->_getDefaultFrontendModel());
            }
            $this->_frontend = $this->_universalFactory->create($this->getFrontendModel())->setAttribute($this);
        }

        return $this->_frontend;
    }

    /**
     * Retrieve source instance
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
     * @throws \Magento\Framework\Model\Exception
     */
    public function getSource()
    {
        if (empty($this->_source)) {
            if (!$this->getSourceModel()) {
                $this->setSourceModel($this->_getDefaultSourceModel());
            }
            $source = $this->_universalFactory->create($this->getSourceModel());
            if (!$source) {
                throw new \Magento\Eav\Exception(
                    __(
                        'Source model "%1" not found for attribute "%2"',
                        $this->getSourceModel(),
                        $this->getAttributeCode()
                    )
                );
            }
            $this->_source = $source->setAttribute($this);
        }
        return $this->_source;
    }

    /**
     * Whether possible attribute values are retrieved from finite source
     *
     * @return bool
     */
    public function usesSource()
    {
        $input = $this->getFrontendInput();
        return $input === 'select' || $input === 'multiselect' || $this->_getData('source_model') != '';
    }

    /**
     * @return string
     */
    protected function _getDefaultBackendModel()
    {
        return \Magento\Eav\Model\Entity::DEFAULT_BACKEND_MODEL;
    }

    /**
     * @return string
     */
    protected function _getDefaultFrontendModel()
    {
        return \Magento\Eav\Model\Entity::DEFAULT_FRONTEND_MODEL;
    }

    /**
     * @return string
     */
    protected function _getDefaultSourceModel()
    {
        return $this->getEntity()->getDefaultAttributeSourceModel();
    }

    /**
     * @param array|null|bool|int|float|string $value
     * @return bool
     */
    public function isValueEmpty($value)
    {
        $attrType = $this->getBackend()->getType();
        $isEmpty = (is_array($value) && count($value) == 0) ||
            $value === null ||
            $value === false && $attrType != 'int' ||
            $value === '' && ($attrType == 'int' ||
            $attrType == 'decimal' ||
            $attrType == 'datetime');

        return $isEmpty;
    }

    /**
     * Check if attribute in specified set
     *
     * @param int|int[] $setId
     * @return bool
     */
    public function isInSet($setId)
    {
        if (!$this->hasAttributeSetInfo()) {
            return true;
        }

        if (is_array($setId) && count(array_intersect($setId, array_keys($this->getAttributeSetInfo())))) {
            return true;
        }

        if (!is_array($setId) && array_key_exists($setId, $this->getAttributeSetInfo())) {
            return true;
        }

        return false;
    }

    /**
     * Check if attribute in specified group
     *
     * @param int $setId
     * @param int $groupId
     * @return bool
     */
    public function isInGroup($setId, $groupId)
    {
        $dataPath = sprintf('attribute_set_info/%s/group_id', $setId);
        if ($this->isInSet($setId) && $this->getData($dataPath) == $groupId) {
            return true;
        }

        return false;
    }

    /**
     * Return attribute id
     *
     * @param string $entityType
     * @param string $code
     * @return int
     */
    public function getIdByCode($entityType, $code)
    {
        $cacheKey = "{$entityType}|{$code}";
        if (!isset($this->_attributeIdCache[$cacheKey])) {
            $this->_attributeIdCache[$cacheKey] = $this->getResource()->getIdByCode($entityType, $code);
        }
        return $this->_attributeIdCache[$cacheKey];
    }

    /**
     * Check if attribute is static
     *
     * @return bool
     */
    public function isStatic()
    {
        return $this->getBackendType() == self::TYPE_STATIC || $this->getBackendType() == '';
    }

    /**
     * Get attribute backend table name
     *
     * @return string
     */
    public function getBackendTable()
    {
        if ($this->_dataTable === null) {
            if ($this->isStatic()) {
                $this->_dataTable = $this->getEntityType()->getValueTablePrefix();
            } else {
                $backendTable = trim($this->_getData('backend_table'));
                if (empty($backendTable)) {
                    $entityTable = [$this->getEntity()->getEntityTablePrefix(), $this->getBackendType()];
                    $backendTable = $this->getResource()->getTable($entityTable);
                }
                $this->_dataTable = $backendTable;
            }
        }
        return $this->_dataTable;
    }

    /**
     * Retrieve flat columns definition
     *
     * @return array
     */
    public function getFlatColumns()
    {
        // If source model exists - get definition from it
        if ($this->usesSource() && $this->getBackendType() != self::TYPE_STATIC) {
            return $this->getSource()->getFlatColumns();
        }
        return $this->_getFlatColumnsDdlDefinition();
    }

    /**
     * Retrieve flat columns DDL definition
     *
     * @return array
     */
    public function _getFlatColumnsDdlDefinition()
    {
        $columns = [];
        switch ($this->getBackendType()) {
            case 'static':
                $describe = $this->_getResource()->describeTable($this->getBackend()->getTable());
                if (!isset($describe[$this->getAttributeCode()])) {
                    break;
                }
                $prop = $describe[$this->getAttributeCode()];
                $type = $prop['DATA_TYPE'];
                $size = $prop['LENGTH'] ? $prop['LENGTH'] : null;

                $columns[$this->getAttributeCode()] = [
                    'type' => $this->_resourceHelper->getDdlTypeByColumnType($type),
                    'length' => $size,
                    'unsigned' => $prop['UNSIGNED'] ? true : false,
                    'nullable' => $prop['NULLABLE'],
                    'default' => $prop['DEFAULT'],
                    'extra' => null,
                ];
                break;
            case 'datetime':
                $columns[$this->getAttributeCode()] = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                    'unsigned' => false,
                    'nullable' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'decimal':
                $columns[$this->getAttributeCode()] = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'length' => '12,4',
                    'unsigned' => false,
                    'nullable' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'int':
                $columns[$this->getAttributeCode()] = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'unsigned' => false,
                    'nullable' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'text':
                $columns[$this->getAttributeCode()] = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'unsigned' => false,
                    'nullable' => true,
                    'default' => null,
                    'extra' => null,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                ];
                break;
            case 'varchar':
                $columns[$this->getAttributeCode()] = [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => '255',
                    'unsigned' => false,
                    'nullable' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            default:
                break;
        }

        return $columns;
    }

    /**
     * Retrieve flat columns definition in old format (before MMDB support)
     * Used in database compatible mode
     *
     * @return array
     */
    protected function _getFlatColumnsOldDefinition()
    {
        $columns = [];
        switch ($this->getBackendType()) {
            case 'static':
                $describe = $this->_getResource()->describeTable($this->getBackend()->getTable());
                if (!isset($describe[$this->getAttributeCode()])) {
                    break;
                }
                $prop = $describe[$this->getAttributeCode()];
                $columns[$this->getAttributeCode()] = [
                    'type' => $prop['DATA_TYPE'] . ($prop['LENGTH'] ? "({$prop['LENGTH']})" : ""),
                    'unsigned' => $prop['UNSIGNED'] ? true : false,
                    'is_null' => $prop['NULLABLE'],
                    'default' => $prop['DEFAULT'],
                    'extra' => null,
                ];
                break;
            case 'datetime':
                $columns[$this->getAttributeCode()] = [
                    'type' => 'datetime',
                    'unsigned' => false,
                    'is_null' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'decimal':
                $columns[$this->getAttributeCode()] = [
                    'type' => 'decimal(12,4)',
                    'unsigned' => false,
                    'is_null' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'int':
                $columns[$this->getAttributeCode()] = [
                    'type' => 'int',
                    'unsigned' => false,
                    'is_null' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'text':
                $columns[$this->getAttributeCode()] = [
                    'type' => 'text',
                    'unsigned' => false,
                    'is_null' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            case 'varchar':
                $columns[$this->getAttributeCode()] = [
                    'type' => 'varchar(255)',
                    'unsigned' => false,
                    'is_null' => true,
                    'default' => null,
                    'extra' => null,
                ];
                break;
            default:
                break;
        }
        return $columns;
    }

    /**
     * Retrieve index data for flat table
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $condition = $this->getUsedForSortBy();
        if ($this->getFlatAddFilterableAttributes()) {
            $condition = $condition || $this->getIsFilterable();
        }

        if ($condition) {
            if ($this->usesSource() && $this->getBackendType() != self::TYPE_STATIC) {
                return $this->getSource()->getFlatIndexes();
            }
            $indexes = [];

            switch ($this->getBackendType()) {
                case 'static':
                    $describe = $this->_getResource()->describeTable($this->getBackend()->getTable());
                    if (!isset($describe[$this->getAttributeCode()])) {
                        break;
                    }
                    $indexDataTypes = [
                        'varchar',
                        'varbinary',
                        'char',
                        'date',
                        'datetime',
                        'timestamp',
                        'time',
                        'year',
                        'enum',
                        'set',
                        'bit',
                        'bool',
                        'tinyint',
                        'smallint',
                        'mediumint',
                        'int',
                        'bigint',
                        'float',
                        'double',
                        'decimal',
                    ];
                    $prop = $describe[$this->getAttributeCode()];
                    if (in_array($prop['DATA_TYPE'], $indexDataTypes)) {
                        $indexName = 'IDX_' . strtoupper($this->getAttributeCode());
                        $indexes[$indexName] = ['type' => 'index', 'fields' => [$this->getAttributeCode()]];
                    }

                    break;
                case 'datetime':
                case 'decimal':
                case 'int':
                case 'varchar':
                    $indexName = 'IDX_' . strtoupper($this->getAttributeCode());
                    $indexes[$indexName] = ['type' => 'index', 'fields' => [$this->getAttributeCode()]];
                    break;
                default:
                    break;
            }

            return $indexes;
        }

        return [];
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param int $store
     * @return \Magento\Framework\DB\Select
     */
    public function getFlatUpdateSelect($store = null)
    {
        if ($store === null) {
            foreach ($this->_storeManager->getStores() as $store) {
                $this->getFlatUpdateSelect($store->getId());
            }
            return $this;
        }

        if ($this->getBackendType() == self::TYPE_STATIC) {
            return null;
        }

        if ($this->usesSource()) {
            return $this->getSource()->getFlatUpdateSelect($store);
        }
        return $this->_getResource()->getFlatUpdateSelect($this, $store);
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getIsUnique()
    {
        return $this->getData(self::IS_UNIQUE);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendClass()
    {
        return $this->getData(self::FRONTEND_CLASS);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendInput()
    {
        return $this->getData(self::FRONTEND_INPUT);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsRequired()
    {
        return $this->getData(self::IS_REQUIRED);
    }
    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getOptions()
    {
        $options = $this->getData(self::OPTIONS);
        if (!$options) {
            $options = $this->usesSource() ? $this->getSource()->getAllOptions() : [];
        }

        return $this->convertToObjects($options);
    }

    /**
     * Convert option values from arrays to data objects
     *
     * @param array $options
     * @return \Magento\Eav\Api\Data\AttributeOptionInterface[]
     */
    protected function convertToObjects(array $options)
    {
        $dataObjects = [];
        foreach ($options as $option) {
            $dataObjects[] = $this->optionDataBuilder->populateWithArray($option)->create();
        }
        return $dataObjects;
    }

    /**
     * @codeCoverageIgnoreStart
     * {@inheritdoc}
     */
    public function getIsUserDefined()
    {
        return $this->getData(self::IS_USER_DEFINED);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultFrontendLabel()
    {
        return $this->getData(self::FRONTEND_LABEL);
    }

    /**
     * {@inheritdoc}
     */
    public function getFrontendLabels()
    {
        return $this->getData(self::FRONTEND_LABELS);
    }

    /**
     * {@inheritdoc}
     */
    public function getNote()
    {
        return $this->getData(self::NOTE);
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceModel()
    {
        return $this->getData(self::SOURCE_MODEL);
    }
    //@codeCoverageIgnoreEnd

    /**
     * {@inheritdoc}
     */
    public function getValidationRules()
    {
        $rules = $this->getData(self::VALIDATE_RULES);
        if (is_array($rules)) {
            return $rules;
        } elseif (!empty($rules)) {
            return unserialize($rules);
        }
        return [];
    }
}
