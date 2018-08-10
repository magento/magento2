<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Eav\Model\Entity;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Magento\Eav\Model\Entity\Attribute\Frontend\AbstractFrontend;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\App\Config\Element;
use Magento\Framework\App\ResourceConnection\Config;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor;
use Magento\Framework\Model\ResourceModel\Db\TransactionManagerInterface;
use Magento\Eav\Model\ResourceModel\Attribute\DefaultEntityAttributes\ProviderInterface as DefaultAttributesProvider;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\App\ObjectManager;

/**
 * Entity/Attribute/Model - entity abstract
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractEntity extends AbstractResource implements EntityInterface, DefaultAttributesProvider
{
    /**
     * @var \Magento\Eav\Model\Entity\AttributeLoaderInterface
     */
    protected $attributeLoader;

    /**
     * Connection name
     *
     * @var string
     */
    protected $connectionName;

    /**
     * Entity type configuration
     *
     * @var Type
     */
    protected $_type;

    /**
     * Attributes array by attribute name
     *
     * @var array
     */
    protected $_attributesByCode = [];

    /**
     * Two-dimensional array by table name and attribute name
     *
     * @var array
     */
    protected $_attributesByTable = [];

    /**
     * Attributes that are static fields in entity table
     *
     * @var array
     */
    protected $_staticAttributes = [];

    /**
     * Entity table
     *
     * @var string
     */
    protected $_entityTable;

    /**
     * Describe data for tables
     *
     * @var array
     */
    protected $_describeTable = [];

    /**
     * Entity table identification field name
     *
     * @var string
     */
    protected $_entityIdField;

    /**
     * Entity primary key for link field name
     *
     * @var string
     */
    protected $linkIdField;

    /**
     * Entity values table identification field name
     *
     * @var string
     */
    protected $_valueEntityIdField;

    /**
     * Entity value table prefix
     *
     * @var string
     */
    protected $_valueTablePrefix;

    /**
     * Entity table string
     *
     * @var string
     */
    protected $_entityTablePrefix;

    /**
     * Partial load flag
     *
     * @var bool
     */
    protected $_isPartialLoad = false;

    /**
     * Partial save flag
     *
     * @var bool
     */
    protected $_isPartialSave = false;

    /**
     * Attribute set id which used for get sorted attributes
     *
     * @var int
     */
    protected $_sortingSetId = null;

    /**
     * Entity attribute values per backend table to delete
     *
     * @var array
     */
    protected $_attributeValuesToDelete = [];

    /**
     * Entity attribute values per backend table to save
     *
     * @var array
     */
    protected $_attributeValuesToSave = [];

    /**
     * Array of describe attribute backend tables
     * The table name as key
     *
     * @var array
     */
    protected static $_attributeBackendTables = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected $_attrSetEntity;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $_localeFormat;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @var TransactionManagerInterface
     */
    protected $transactionManager;

    /**
     * @var ObjectRelationProcessor
     */
    protected $objectRelationProcessor;

    /**
     * Attributes stored by scope (store id and attribute set id).
     *
     * @var array
     */
    private $attributesByScope;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(Context $context, $data = [])
    {
        $this->_eavConfig = $context->getEavConfig();
        $this->_resource = $context->getResource();
        $this->_attrSetEntity = $context->getAttributeSetEntity();
        $this->_localeFormat = $context->getLocaleFormat();
        $this->_resourceHelper = $context->getResourceHelper();
        $this->_universalFactory = $context->getUniversalFactory();
        $this->transactionManager = $context->getTransactionManager();
        $this->objectRelationProcessor = $context->getObjectRelationProcessor();
        parent::__construct();
        $properties = get_object_vars($this);
        foreach ($data as $key => $value) {
            if (array_key_exists('_' . $key, $properties)) {
                $this->{'_' . $key} = $value;
            }
        }
    }

    /**
     * Set connections for entity operations
     *
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|string $connection
     * @return $this
     * @codeCoverageIgnore
     */
    public function setConnection($connection)
    {
        $this->connectionName = $connection;
        return $this;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Get connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     * @codeCoverageIgnore
     */
    public function getConnection()
    {
        return $this->_resource->getConnection();
    }

    /**
     * For compatibility with AbstractModel
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getIdFieldName()
    {
        return $this->getEntityIdField();
    }

    /**
     * Retrieve table name
     *
     * @param string $alias
     * @return string
     * @codeCoverageIgnore
     */
    public function getTable($alias)
    {
        return $this->_resource->getTableName($alias);
    }

    /**
     * Set configuration for the entity
     *
     * Accepts config node or name of entity type
     *
     * @param string|Type $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $this->_eavConfig->getEntityType($type);
        return $this;
    }

    /**
     * Retrieve current entity config
     *
     * @return Type
     * @throws LocalizedException
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            throw new LocalizedException(__('Entity is not initialized'));
        }
        return $this->_type;
    }

    /**
     * Get entity type name
     *
     * @return string
     */
    public function getType()
    {
        return $this->getEntityType()->getEntityTypeCode();
    }

    /**
     * Get entity type id
     *
     * @return int
     */
    public function getTypeId()
    {
        return (int) $this->getEntityType()->getEntityTypeId();
    }

    /**
     * Unset attributes
     *
     * If NULL or not supplied removes configuration of all attributes
     * If string - removes only one, if array - all specified
     *
     * @param array|string|null $attributes
     * @return $this
     * @throws LocalizedException
     */
    public function unsetAttributes($attributes = null)
    {
        if ($attributes === null) {
            $this->_attributesByCode = [];
            $this->_attributesByTable = [];
            return $this;
        }

        if (is_string($attributes)) {
            $attributes = [$attributes];
        }

        if (!is_array($attributes)) {
            throw new LocalizedException(__('Unknown parameter'));
        }

        foreach ($attributes as $attrCode) {
            if (!isset($this->_attributesByCode[$attrCode])) {
                continue;
            }

            $attr = $this->getAttribute($attrCode);
            unset($this->_attributesByTable[$attr->getBackend()->getTable()][$attrCode]);
            unset($this->_attributesByCode[$attrCode]);
        }

        return $this;
    }

    /**
     * Get EAV config model
     *
     * @return \Magento\Eav\Model\Config
     */
    protected function _getConfig()
    {
        return $this->_eavConfig;
    }

    /**
     * Retrieve attribute instance by name, id or config node
     *
     * This will add the attribute configuration to entity's attributes cache
     *
     * If attribute is not found false is returned
     *
     * @param string|int|Element $attribute
     * @return AbstractAttribute|false
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getAttribute($attribute)
    {

        /** @var $config \Magento\Eav\Model\Config */
        $config = $this->_getConfig();
        if (is_numeric($attribute)) {
            $attributeId = $attribute;
            $attributeInstance = $config->getAttribute($this->getEntityType(), $attributeId);
            if ($attributeInstance) {
                $attributeCode = $attributeInstance->getAttributeCode();
            }
        } elseif (is_string($attribute)) {
            $attributeCode = $attribute;
            $attributeInstance = $config->getAttribute($this->getEntityType(), $attributeCode);
            if (!$attributeInstance->getAttributeCode() && in_array($attribute, $this->getDefaultAttributes())) {
                $attributeInstance->setAttributeCode(
                    $attribute
                )->setBackendType(
                    AbstractAttribute::TYPE_STATIC
                )->setIsGlobal(
                    1
                )->setEntity(
                    $this
                )->setEntityType(
                    $this->getEntityType()
                )->setEntityTypeId(
                    $this->getEntityType()->getId()
                );
            }
        } elseif ($attribute instanceof AbstractAttribute) {
            $attributeInstance = $attribute;
            $attributeCode = $attributeInstance->getAttributeCode();
        }

        if (empty($attributeInstance)
            || !$attributeInstance instanceof AbstractAttribute
            || !$attributeInstance->getId()
            && !in_array($attributeInstance->getAttributeCode(), $this->getDefaultAttributes())
        ) {
            return false;
        }

        $attribute = $attributeInstance;

        if (!$attribute->getAttributeCode()) {
            $attribute->setAttributeCode($attributeCode);
        }
        if (!$attribute->getAttributeModel()) {
            $attribute->setAttributeModel($this->_getDefaultAttributeModel());
        }

        $this->addAttribute($attribute);

        return $attribute;
    }

    /**
     * Adding attribute to entity by scope.
     *
     * @param AbstractAttribute $attribute
     * @param string $suffix
     * @return $this
     */
    public function addAttributeByScope(AbstractAttribute $attribute, $suffix)
    {
        $attributeCode = $attribute->getAttributeCode();
        $this->attributesByScope[$suffix][$attributeCode] = $attribute;
        return $this->addAttribute($attribute);
    }

    /**
     * Adding attribute to entity
     *
     * @param AbstractAttribute $attribute
     * @return $this
     */
    public function addAttribute(AbstractAttribute $attribute)
    {
        $attribute->setEntity($this);
        $attributeCode = $attribute->getAttributeCode();

        $this->_attributesByCode[$attributeCode] = $attribute;

        if ($attribute->isStatic()) {
            $this->_staticAttributes[$attributeCode] = $attribute;
        } else {
            $this->_attributesByTable[$attribute->getBackendTable()][$attributeCode] = $attribute;
        }

        return $this;
    }

    /**
     * Retrieve partial load flag
     *
     * @param bool $flag
     * @return bool
     */
    public function isPartialLoad($flag = null)
    {
        $result = $this->_isPartialLoad;
        if ($flag !== null) {
            $this->_isPartialLoad = (bool)$flag;
        }
        return $result;
    }

    /**
     * Retrieve partial save flag
     *
     * @param bool $flag
     * @return bool
     */
    public function isPartialSave($flag = null)
    {
        $result = $this->_isPartialSave;
        if ($flag !== null) {
            $this->_isPartialSave = (bool) $flag;
        }
        return $result;
    }

    /**
     * Retrieve configuration for all attributes
     *
     * @param null|\Magento\Framework\DataObject $object
     * @return $this
     */
    public function loadAllAttributes($object = null)
    {
        return $this->getAttributeLoader()->loadAllAttributes($this, $object);
    }

    /**
     * Retrieve sorted attributes
     *
     * @param int $setId
     * @return array
     */
    public function getSortedAttributes($setId = null)
    {
        $attributes = $this->getAttributesByCode();
        if ($setId === null) {
            $setId = $this->getEntityType()->getDefaultAttributeSetId();
        }

        // initialize set info
        $this->_attrSetEntity->addSetInfo($this->getEntityType(), $attributes, $setId);

        foreach ($attributes as $code => $attribute) {
            /* @var $attribute AbstractAttribute */
            if (!$attribute->isInSet($setId)) {
                unset($attributes[$code]);
            }
        }

        $this->_sortingSetId = $setId;
        uasort($attributes, [$this, 'attributesCompare']);
        return $attributes;
    }

    /**
     * Compare attributes
     *
     * @param Attribute $firstAttribute
     * @param Attribute $secondAttribute
     * @return int
     */
    public function attributesCompare($firstAttribute, $secondAttribute)
    {
        $firstSort = $firstAttribute->getSortWeight((int) $this->_sortingSetId);
        $secondSort = $secondAttribute->getSortWeight((int) $this->_sortingSetId);

        if ($firstSort > $secondSort) {
            return 1;
        } elseif ($firstSort < $secondSort) {
            return -1;
        }

        return 0;
    }

    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   AbstractAttribute $attribute
     * @return  bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isApplicableAttribute($object, $attribute)
    {
        return true;
    }

    /**
     * Get attributes by scope
     *
     * @return array
     */
    private function getAttributesByScope($suffix)
    {
        return !empty($this->attributesByScope[$suffix])
            ? $this->attributesByScope[$suffix]
            : $this->getAttributesByCode();
    }

    /**
     * Get attributes cache suffix.
     *
     * @param DataObject $object
     * @return string
     */
    private function getAttributesCacheSuffix(DataObject $object)
    {
        $attributeSetId = $object->getAttributeSetId() ?: 0;
        $storeId = $object->getStoreId() ?: 0;
        return $storeId . '-' . $attributeSetId;
    }

    /**
     * Walk through the attributes and run method with optional arguments
     *
     * Returns array with results for each attribute
     *
     * if $partMethod is in format "part/method" will run method on specified part
     * for example: $this->walkAttributes('backend/validate');
     *
     * @param string $partMethod
     * @param array $args
     * @param null|bool $collectExceptionMessages
     *
     * @throws \Exception|\Magento\Eav\Model\Entity\Attribute\Exception
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function walkAttributes($partMethod, array $args = [], $collectExceptionMessages = null)
    {
        $methodArr = explode('/', $partMethod);
        switch (sizeof($methodArr)) {
            case 1:
                $part = 'attribute';
                $method = $methodArr[0];
                break;

            case 2:
                $part = $methodArr[0];
                $method = $methodArr[1];
                break;

            default:
                break;
        }
        $results = [];
        $suffix = $this->getAttributesCacheSuffix($args[0]);
        foreach ($this->getAttributesByScope($suffix) as $attrCode => $attribute) {
            if (isset($args[0]) && is_object($args[0]) && !$this->_isApplicableAttribute($args[0], $attribute)) {
                continue;
            }

            switch ($part) {
                case 'attribute':
                    $instance = $attribute;
                    break;

                case 'backend':
                    $instance = $attribute->getBackend();
                    break;

                case 'frontend':
                    $instance = $attribute->getFrontend();
                    break;

                case 'source':
                    $instance = $attribute->getSource();
                    break;

                default:
                    break;
            }

            if (!$this->_isCallableAttributeInstance($instance, $method, $args)) {
                continue;
            }

            try {
                $results[$attrCode] = call_user_func_array([$instance, $method], $args);
            } catch (\Magento\Eav\Model\Entity\Attribute\Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    throw $e;
                }
            } catch (\Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    /** @var \Magento\Eav\Model\Entity\Attribute\Exception $e */
                    $e = $this->_universalFactory->create(
                        'Magento\Eav\Model\Entity\Attribute\Exception',
                        ['phrase' => __($e->getMessage())]
                    );
                    $e->setAttributeCode($attrCode)->setPart($part);
                    throw $e;
                }
            }
        }

        return $results;
    }

    /**
     * Check whether attribute instance (attribute, backend, frontend or source) has method and applicable
     *
     * @param AbstractAttribute|AbstractBackend|AbstractFrontend|AbstractSource $instance
     * @param string $method
     * @param array $args array of arguments
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if (!is_object($instance) || !method_exists($instance, $method) || !is_callable([$instance, $method])) {
            return false;
        }

        return true;
    }

    /**
     * Get attributes by name array
     *
     * @return array
     */
    public function getAttributesByCode()
    {
        return $this->_attributesByCode;
    }

    /**
     * Get attributes by table and name array
     *
     * @return array
     */
    public function getAttributesByTable()
    {
        return $this->_attributesByTable;
    }

    /**
     * Get entity table name
     *
     * @return string
     */
    public function getEntityTable()
    {
        if (!$this->_entityTable) {
            $table = $this->getEntityType()->getEntityTable();
            if (!$table) {
                $table = \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE;
            }
            $this->_entityTable = $this->_resource->getTableName($table);
        }

        return $this->_entityTable;
    }

    /**
     * Get link id
     *
     * @return string
     */
    public function getLinkField()
    {
        if (!$this->linkIdField) {
            $indexList = $this->getConnection()->getIndexList($this->getEntityTable());
            $pkName = $this->getConnection()->getPrimaryKeyName($this->getEntityTable());
            $this->linkIdField = $indexList[$pkName]['COLUMNS_LIST'][0];
            if (!$this->linkIdField) {
                $this->linkIdField = $this->getEntityIdField();
            }
        }

        return $this->linkIdField;
    }

    /**
     * Get entity id field name in entity table
     *
     * @return string
     */
    public function getEntityIdField()
    {
        if (!$this->_entityIdField) {
            $this->_entityIdField = $this->getEntityType()->getEntityIdField();
            if (!$this->_entityIdField) {
                $this->_entityIdField = \Magento\Eav\Model\Entity::DEFAULT_ENTITY_ID_FIELD;
            }
        }

        return $this->_entityIdField;
    }

    /**
     * Get default entity id field name in attribute values tables
     *
     * @return string
     */
    public function getValueEntityIdField()
    {
        return $this->getEntityIdField();
    }

    /**
     * Get prefix for value tables
     *
     * @return string
     */
    public function getValueTablePrefix()
    {
        if (!$this->_valueTablePrefix) {
            $prefix = (string) $this->getEntityType()->getValueTablePrefix();
            if (!empty($prefix)) {
                $this->_valueTablePrefix = $prefix;
                /**
                 * entity type prefix include DB table name prefix
                 */
                //$this->_resource->getTableName($prefix);
            } else {
                $this->_valueTablePrefix = $this->getEntityTable();
            }
        }

        return $this->_valueTablePrefix;
    }

    /**
     * Get entity table prefix for value
     *
     * @return string
     */
    public function getEntityTablePrefix()
    {
        if (empty($this->_entityTablePrefix)) {
            $prefix = $this->getEntityType()->getEntityTablePrefix();
            if (empty($prefix)) {
                $prefix = $this->getEntityType()->getEntityTable();
                if (empty($prefix)) {
                    $prefix = \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE;
                }
            }
            $this->_entityTablePrefix = $prefix;
        }

        return $this->_entityTablePrefix;
    }

    /**
     * Check whether the attribute is a real field in entity table
     *
     * @see \Magento\Eav\Model\Entity\AbstractEntity::getAttribute for $attribute format
     * @param int|string|AbstractAttribute $attribute
     * @return bool
     */
    public function isAttributeStatic($attribute)
    {
        $attrInstance = $this->getAttribute($attribute);
        return $attrInstance && $attrInstance->getBackend()->isStatic();
    }

    /**
     * Validate all object's attributes against configuration
     *
     * @param \Magento\Framework\DataObject $object
     * @throws \Magento\Eav\Model\Entity\Attribute\Exception
     * @return true|array
     */
    public function validate($object)
    {
        $this->loadAllAttributes($object);
        $result = $this->walkAttributes('backend/validate', [$object], $object->getCollectExceptionMessages());
        $errors = [];
        foreach ($result as $attributeCode => $error) {
            if ($error === false) {
                $errors[$attributeCode] = true;
            } elseif (is_string($error)) {
                $errors[$attributeCode] = $error;
            }
        }
        if (!$errors) {
            return true;
        }

        return $errors;
    }

    /**
     * Set new increment id to object
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setNewIncrementId(\Magento\Framework\DataObject $object)
    {
        if ($object->getIncrementId()) {
            return $this;
        }

        $incrementId = $this->getEntityType()->fetchNewIncrementId($object->getStoreId());

        if ($incrementId !== false) {
            $object->setIncrementId($incrementId);
        }

        return $this;
    }

    /**
     * Check attribute unique value
     *
     * @param AbstractAttribute $attribute
     * @param \Magento\Framework\DataObject $object
     * @return bool
     */
    public function checkAttributeUniqueValue(AbstractAttribute $attribute, $object)
    {
        $connection = $this->getConnection();
        $select = $connection->select();

        $entityIdField = $this->getEntityIdField();
        $attributeBackend = $attribute->getBackend();
        if ($attributeBackend->getType() === 'static') {
            $value = $object->getData($attribute->getAttributeCode());
            $bind = ['value' => trim($value)];

            $select->from(
                $this->getEntityTable(),
                $entityIdField
            )->where(
                $attribute->getAttributeCode() . ' = :value'
            );
        } else {
            $value = $object->getData($attribute->getAttributeCode());
            if ($attributeBackend->getType() == 'datetime') {
                $value = (new \DateTime($value))->format('Y-m-d H:i:s');
            }
            $bind = [
                'attribute_id' => $attribute->getId(),
                'value' => trim($value),
            ];

            $entityIdField = $object->getResource()->getLinkField();
            $select->from(
                $attributeBackend->getTable(),
                $entityIdField
            )->where(
                'attribute_id = :attribute_id'
            )->where(
                'value = :value'
            );
        }

        if ($this->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE) {
            $bind['entity_type_id'] = $this->getTypeId();
            $select->where('entity_type_id = :entity_type_id');
        }

        $data = $connection->fetchCol($select, $bind);

        $objectId = $object->getData($entityIdField);
        if ($objectId) {
            if (isset($data[0])) {
                return $data[0] == $objectId;
            }
            return true;
        }

        return !count($data);
    }

    /**
     * Retrieve default source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return \Magento\Eav\Model\Entity::DEFAULT_SOURCE_MODEL;
    }

    /**
     * Load entity's attributes into the object
     *
     * @param   AbstractModel $object
     * @param   int $entityId
     * @param   array|null $attributes
     * @return $this
     */
    public function load($object, $entityId, $attributes = [])
    {
        \Magento\Framework\Profiler::start('EAV:load_entity');
        /**
         * Load object base row data
         */
        $select = $this->_getLoadRowSelect($object, $entityId);
        $row = $this->getConnection()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        $this->loadAttributesMetadata($attributes);

        $this->_loadModelAttributes($object);

        $object->setOrigData();

        $this->_afterLoad($object);

        \Magento\Framework\Profiler::stop('EAV:load_entity');
        return $this;
    }

    /**
     * Loads attributes metadata.
     *
     * @param array|null $attributes
     * @return $this
     */
    protected function loadAttributesMetadata($attributes)
    {
        if (empty($attributes)) {
            $this->loadAllAttributes();
        } else {
            if (!is_array($attributes)) {
                $attributes = [$attributes];
            }
            foreach ($attributes as $attrCode) {
                $this->getAttribute($attrCode);
            }
        }
    }

    /**
     * Load model attributes data
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _loadModelAttributes($object)
    {
        if (!$object->getId()) {
            return $this;
        }

        \Magento\Framework\Profiler::start('load_model_attributes');

        $selects = [];
        foreach (array_keys($this->getAttributesByTable()) as $table) {
            $attribute = current($this->_attributesByTable[$table]);
            $eavType = $attribute->getBackendType();
            $select = $this->_getLoadAttributesSelect($object, $table);
            $selects[$eavType][] = $select->columns('*');
        }
        $selectGroups = $this->_resourceHelper->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                $select = $this->_prepareLoadSelect($selects);
                $values = $this->getConnection()->fetchAll($select);
                foreach ($values as $valueRow) {
                    $this->_setAttributeValue($object, $valueRow);
                }
            }
        }

        \Magento\Framework\Profiler::stop('load_model_attributes');

        return $this;
    }

    /**
     * Prepare select object for loading entity attributes values
     *
     * @param  array $selects
     * @return \Magento\Framework\DB\Select
     */
    protected function _prepareLoadSelect(array $selects)
    {
        return $this->getConnection()->select()->union($selects, \Magento\Framework\DB\Select::SQL_UNION_ALL);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   string|int $rowId
     * @return  \Magento\Framework\DB\Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getEntityTable()
        )->where(
            $this->getEntityIdField() . ' =?',
            $rowId
        );

        return $select;
    }

    /**
     * Retrieve select object for loading entity attributes values
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   string $table
     * @return  \Magento\Framework\DB\Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        $select = $this->getConnection()->select()->from(
            $table,
            []
        )->where(
            $this->getEntityIdField() . ' =?',
            $object->getId()
        );

        return $select;
    }

    /**
     * Initialize attribute value for object
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   array $valueRow
     * @return $this
     */
    protected function _setAttributeValue($object, $valueRow)
    {
        $attribute = $this->getAttribute($valueRow['attribute_id']);
        if ($attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $object->setData($attributeCode, $valueRow['value']);
            $attribute->getBackend()->setEntityValueId($object, $valueRow['value_id']);
        }

        return $this;
    }

    /**
     * Save entity's attributes into the object's resource
     *
     * @param  \Magento\Framework\Model\AbstractModel $object
     * @return $this
     * @throws \Exception
     */
    public function save(\Magento\Framework\Model\AbstractModel $object)
    {
        /**
         * Direct deleted items to delete method
         */
        if ($object->isDeleted()) {
            return $this->delete($object);
        }
        if (!$object->hasDataChanges()) {
            return $this;
        }
        $this->beginTransaction();
        try {
            $object->validateBeforeSave();
            $object->beforeSave();
            if ($object->isSaveAllowed()) {
                if (!$this->isPartialSave()) {
                    $this->loadAllAttributes($object);
                }

                if ($this->getEntityTable() ==  \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE
                    && !$object->getEntityTypeId()
                ) {
                    $object->setEntityTypeId($this->getTypeId());
                }

                $object->setParentId((int)$object->getParentId());

                $this->objectRelationProcessor->validateDataIntegrity($this->getEntityTable(), $object->getData());

                $this->_beforeSave($object);
                $this->processSave($object);
                $this->_afterSave($object);

                $object->afterSave();
            }
            $this->addCommitCallback([$object, 'afterCommitCallback'])->commit();
            $object->setHasDataChanges(false);
        } catch (\Exception $e) {
            $this->rollBack();
            $object->setHasDataChanges(true);
            throw $e;
        }

        return $this;
    }

    /**
     * Save entity process
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return void
     */
    protected function processSave($object)
    {
        $this->_processSaveData($this->_collectSaveData($object));
    }

    /**
     * Retrieve Object instance with original data
     *
     * @param \Magento\Framework\DataObject $object
     * @return \Magento\Framework\DataObject
     */
    protected function _getOrigObject($object)
    {
        $className = get_class($object);
        $origObject = $this->_universalFactory->create($className);
        $origObject->setData([]);
        $this->load($origObject, $object->getData($this->getEntityIdField()));

        return $origObject;
    }

    /**
     * Aggregate Data for attributes that will be deleted
     *
     * @param array &$delete
     * @param AbstractAttribute $attribute
     * @param \Magento\Eav\Model\Entity\AbstractEntity $object
     * @return void
     */
    private function _aggregateDeleteData(&$delete, $attribute, $object)
    {
        foreach ($attribute->getBackend()->getAffectedFields($object) as $tableName => $valuesData) {
            if (!isset($delete[$tableName])) {
                $delete[$tableName] = [];
            }
            $delete[$tableName] = array_merge((array)$delete[$tableName], $valuesData);
        }
    }

    /**
     * Prepare entity object data for save
     *
     * Result array structure:
     * array (
     *  'newObject', 'entityRow', 'insert', 'update', 'delete'
     * )
     *
     * @param   \Magento\Framework\Model\AbstractModel $newObject
     * @return  array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _collectSaveData($newObject)
    {
        $newData = $newObject->getData();
        $entityId = $newObject->getData($this->getEntityIdField());

        // define result data
        $entityRow = [];
        $insert = [];
        $update = [];
        $delete = [];

        if (!empty($entityId)) {
            $origData = $newObject->getOrigData();
            /**
             * get current data in db for this entity if original data is empty
             */
            if (empty($origData)) {
                $origData = $this->_getOrigObject($newObject)->getOrigData();
            }

            if (is_null($origData)) {
                $origData = [];
            }

            /**
             * drop attributes that are unknown in new data
             * not needed after introduction of partial entity loading
             */
            foreach ($origData as $k => $v) {
                if (!array_key_exists($k, $newData)) {
                    unset($origData[$k]);
                }
            }
        } else {
            $origData = [];
        }

        $staticFields = $this->getConnection()->describeTable($this->getEntityTable());
        $staticFields = array_keys($staticFields);
        $attributeCodes = array_keys($this->_attributesByCode);

        foreach ($newData as $k => $v) {
            /**
             * Check if data key is presented in static fields or attribute codes
             */
            if (!in_array($k, $staticFields) && !in_array($k, $attributeCodes)) {
                continue;
            }

            $attribute = $this->getAttribute($k);
            if (empty($attribute)) {
                continue;
            }

            if (!$attribute->isInSet($newObject->getAttributeSetId()) && !in_array($k, $staticFields)) {
                $this->_aggregateDeleteData($delete, $attribute, $newObject);
                continue;
            }

            $attrId = $attribute->getAttributeId();

            /**
             * Only scalar values can be stored in generic tables
             */
            if (!$attribute->getBackend()->isScalar()) {
                continue;
            }

            /**
             * if attribute is static add to entity row and continue
             */
            if ($this->isAttributeStatic($k)) {
                $entityRow[$k] = $this->_prepareStaticValue($k, $v);
                continue;
            }

            /**
             * Check comparability for attribute value
             */
            if ($this->_canUpdateAttribute($attribute, $v, $origData)) {
                if ($this->_isAttributeValueEmpty($attribute, $v)) {
                    $this->_aggregateDeleteData($delete, $attribute, $newObject);
                } elseif (!is_numeric($v) && $v !== $origData[$k] || is_numeric($v) && $v != $origData[$k]) {
                    $update[$attrId] = [
                        'value_id' => $attribute->getBackend()->getEntityValueId($newObject),
                        'value' => is_array($v) ? array_shift($v) : $v,//@TODO: MAGETWO-44182,
                    ];
                }
            } elseif (!$this->_isAttributeValueEmpty($attribute, $v)) {
                $insert[$attrId] = is_array($v) ? array_shift($v) : $v;//@TODO: MAGETWO-44182
            }
        }

        $result = compact('newObject', 'entityRow', 'insert', 'update', 'delete');
        return $result;
    }

    /**
     * Return if attribute exists in original data array.
     *
     * @param AbstractAttribute $attribute
     * @param mixed $v New value of the attribute. Can be used in subclasses.
     * @param array $origData
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _canUpdateAttribute(AbstractAttribute $attribute, $v, array &$origData)
    {
        return array_key_exists($attribute->getAttributeCode(), $origData);
    }

    /**
     * Retrieve static field properties
     *
     * @param string $field
     * @return array
     */
    protected function _getStaticFieldProperties($field)
    {
        if (empty($this->_describeTable[$this->getEntityTable()])) {
            $this->_describeTable[$this->getEntityTable()] = $this->getConnection()->describeTable(
                $this->getEntityTable()
            );
        }

        if (isset($this->_describeTable[$this->getEntityTable()][$field])) {
            return $this->_describeTable[$this->getEntityTable()][$field];
        }

        return false;
    }

    /**
     * Prepare static value for save
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    protected function _prepareStaticValue($key, $value)
    {
        $fieldProp = $this->_getStaticFieldProperties($key);

        if (!$fieldProp) {
            return $value;
        }

        if ($fieldProp['DATA_TYPE'] == 'decimal') {
            $value = $this->_localeFormat->getNumber($value);
        }

        return $value;
    }

    /**
     * Save object collected data
     *
     * @param   array $saveData array('newObject', 'entityRow', 'insert', 'update', 'delete')
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _processSaveData($saveData)
    {
        extract($saveData, EXTR_SKIP);
        /**
         * Import variables into the current symbol table from save data array
         *
         * @see \Magento\Eav\Model\Entity\AbstractEntity::_collectSaveData()
         *
         * @var array $entityRow
         * @var \Magento\Framework\Model\AbstractModel $newObject
         * @var array $insert
         * @var array $update
         * @var array $delete
         */
        $connection = $this->getConnection();
        $insertEntity = true;
        $entityTable = $this->getEntityTable();
        $entityIdField = $this->getEntityIdField();
        $entityId = $newObject->getId();

        unset($entityRow[$entityIdField]);
        if (!empty($entityId) && is_numeric($entityId)) {
            $bind = ['entity_id' => $entityId];
            $select = $connection->select()->from($entityTable, $entityIdField)->where("{$entityIdField} = :entity_id");
            $result = $connection->fetchOne($select, $bind);
            if ($result) {
                $insertEntity = false;
            }
        } else {
            $entityId = null;
        }

        /**
         * Process base row
         */
        $entityObject = new \Magento\Framework\DataObject($entityRow);
        $entityRow = $this->_prepareDataForTable($entityObject, $entityTable);
        if ($insertEntity) {
            if (!empty($entityId)) {
                $entityRow[$entityIdField] = $entityId;
                $connection->insertForce($entityTable, $entityRow);
            } else {
                $connection->insert($entityTable, $entityRow);
                $entityId = $connection->lastInsertId($entityTable);
            }
            $newObject->setId($entityId);
        } else {
            $where = sprintf('%s=%d', $connection->quoteIdentifier($entityIdField), $entityId);
            $connection->update($entityTable, $entityRow, $where);
        }

        /**
         * insert attribute values
         */
        if (!empty($insert)) {
            foreach ($insert as $attributeId => $value) {
                $attribute = $this->getAttribute($attributeId);
                $this->_insertAttribute($newObject, $attribute, $value);
            }
        }

        /**
         * update attribute values
         */
        if (!empty($update)) {
            foreach ($update as $attributeId => $v) {
                $attribute = $this->getAttribute($attributeId);
                $this->_updateAttribute($newObject, $attribute, $v['value_id'], $v['value']);
            }
        }

        /**
         * delete empty attribute values
         */
        if (!empty($delete)) {
            foreach ($delete as $table => $values) {
                $this->_deleteAttributes($newObject, $table, $values);
            }
        }

        $this->_processAttributeValues();

        $newObject->isObjectNew(false);

        return $this;
    }

    /**
     * Insert entity attribute value
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   AbstractAttribute $attribute
     * @param   mixed $value
     * @return $this
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        return $this->_saveAttribute($object, $attribute, $value);
    }

    /**
     * Update entity attribute value
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   AbstractAttribute $attribute
     * @param   mixed $valueId
     * @param   mixed $value
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _updateAttribute($object, $attribute, $valueId, $value)
    {
        return $this->_saveAttribute($object, $attribute, $value);
    }

    /**
     * Save entity attribute value
     *
     * Collect for mass save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return $this
     */
    protected function _saveAttribute($object, $attribute, $value)
    {
        $table = $attribute->getBackend()->getTable();
        if (!isset($this->_attributeValuesToSave[$table])) {
            $this->_attributeValuesToSave[$table] = [];
        }

        $entityIdField = $attribute->getBackend()->getEntityIdField();

        $data = [
            $entityIdField => $object->getId(),
            'attribute_id' => $attribute->getId(),
            'value' => $this->_prepareValueForSave($value, $attribute),
        ];

        if (!$this->getEntityTable() || $this->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE) {
            $data['entity_type_id'] = $object->getEntityTypeId();
        }

        $this->_attributeValuesToSave[$table][] = $data;

        return $this;
    }

    /**
     * Save and delete collected attribute values
     *
     * @return $this
     */
    protected function _processAttributeValues()
    {
        $connection = $this->getConnection();
        foreach ($this->_attributeValuesToSave as $table => $data) {
            $connection->insertOnDuplicate($table, $data, ['value']);
        }

        foreach ($this->_attributeValuesToDelete as $table => $valueIds) {
            $connection->delete($table, ['value_id IN (?)' => $valueIds]);
        }

        // reset data arrays
        $this->_attributeValuesToSave = [];
        $this->_attributeValuesToDelete = [];

        return $this;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param AbstractAttribute $attribute
     * @return mixed
     */
    protected function _prepareValueForSave($value, AbstractAttribute $attribute)
    {
        $type = $attribute->getBackendType();
        if (($type == 'int' || $type == 'decimal' || $type == 'datetime') && $value === '') {
            $value = null;
        } elseif ($type == 'decimal') {
            $value = $this->_localeFormat->getNumber($value);
        }
        $backendTable = $attribute->getBackendTable();
        if (!isset(self::$_attributeBackendTables[$backendTable])) {
            self::$_attributeBackendTables[$backendTable] = $this->getConnection()->describeTable($backendTable);
        }
        $describe = self::$_attributeBackendTables[$backendTable];
        return $this->getConnection()->prepareColumnValue($describe['value'], $value);
    }

    /**
     * Delete entity attribute values
     *
     * @param   \Magento\Framework\DataObject $object
     * @param   string $table
     * @param   array $info
     * @return  \Magento\Framework\DataObject
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $valueIds = [];
        foreach ($info as $itemData) {
            $valueIds[] = $itemData['value_id'];
        }

        if (empty($valueIds)) {
            return $this;
        }

        if (isset($this->_attributeValuesToDelete[$table])) {
            $this->_attributeValuesToDelete[$table] = array_merge($this->_attributeValuesToDelete[$table], $valueIds);
        } else {
            $this->_attributeValuesToDelete[$table] = $valueIds;
        }

        return $this;
    }

    /**
     * Save attribute
     *
     * @param \Magento\Framework\DataObject $object
     * @param string $attributeCode
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveAttribute(\Magento\Framework\DataObject $object, $attributeCode)
    {
        $attribute = $this->getAttribute($attributeCode);
        $backend = $attribute->getBackend();
        $table = $backend->getTable();
        $entity = $attribute->getEntity();
        $connection = $this->getConnection();
        $row = $this->getAttributeRow($entity, $object, $attribute);

        $newValue = $object->getData($attributeCode);
        if ($attribute->isValueEmpty($newValue)) {
            $newValue = null;
        }

        $whereArr = [];
        foreach ($row as $field => $value) {
            $whereArr[] = $connection->quoteInto($field . '=?', $value);
        }
        $where = implode(' AND ', $whereArr);

        $connection->beginTransaction();

        try {
            $select = $connection->select()->from($table, 'value_id')->where($where);
            $origValueId = $connection->fetchOne($select);

            if ($origValueId === false && $newValue !== null) {
                $this->_insertAttribute($object, $attribute, $newValue);
            } elseif ($origValueId !== false && $newValue !== null) {
                $this->_updateAttribute($object, $attribute, $origValueId, $newValue);
            } elseif ($origValueId !== false && $newValue === null) {
                $connection->delete($table, $where);
            }
            $this->_processAttributeValues();
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * Return attribute row to prepare where statement
     *
     * @param \Magento\Framework\DataObject $entity
     * @param \Magento\Framework\DataObject $object
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @return array
     */
    protected function getAttributeRow($entity, $object, $attribute)
    {
        $data = [
            'attribute_id' => $attribute->getId(),
            $entity->getLinkField() => $object->getData($entity->getLinkField()),
        ];

        if (!$this->getEntityTable()) {
            $data['entity_type_id'] = $entity->getTypeId();
        }

        return $data;
    }

    /**
     * Delete entity using current object's data
     *
     * @param \Magento\Framework\DataObject|int|string $object
     * @return $this
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function delete($object)
    {
        try {
            $connection = $this->transactionManager->start($this->getConnection());
            if (is_numeric($object)) {
                $id = (int) $object;
            } elseif ($object instanceof \Magento\Framework\Model\AbstractModel) {
                $object->beforeDelete();
                $id = (int) $object->getData($this->getLinkField());
            }
            $this->_beforeDelete($object);
            $this->evaluateDelete(
                $object,
                $id,
                $connection
            );

            $this->_afterDelete($object);

            if ($object instanceof \Magento\Framework\Model\AbstractModel) {
                $object->isDeleted(true);
                $object->afterDelete();
            }
            $this->transactionManager->commit();
            if ($object instanceof \Magento\Framework\Model\AbstractModel) {
                $object->afterDeleteCommit();
            }
        } catch (\Exception $e) {
            $this->transactionManager->rollBack();
            throw $e;
        }
        return $this;
    }

    /**
     * Evaluate Delete operations
     *
     * @param \Magento\Framework\DataObject|int|string $object
     * @param string|int $id
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function evaluateDelete($object, $id, $connection)
    {
        $where = [$this->getEntityIdField() . '=?' => $id];
        $this->objectRelationProcessor->delete(
            $this->transactionManager,
            $connection,
            $this->getEntityTable(),
            $this->getConnection()->quoteInto(
                $this->getEntityIdField() . '=?',
                $id
            ),
            [$this->getEntityIdField() => $id]
        );

        $this->loadAllAttributes($object);
        foreach ($this->getAttributesByTable() as $table => $attributes) {
            $this->getConnection()->delete(
                $table,
                $where
            );
        }
    }

    /**
     * After Load Entity process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterLoad(\Magento\Framework\DataObject $object)
    {
        \Magento\Framework\Profiler::start('after_load');
        $this->walkAttributes('backend/afterLoad', [$object]);
        \Magento\Framework\Profiler::stop('after_load');
        return $this;
    }

    /**
     * Before delete Entity process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\DataObject $object)
    {
        $this->walkAttributes('backend/beforeSave', [$object]);
        return $this;
    }

    /**
     * After Save Entity process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\DataObject $object)
    {
        $this->walkAttributes('backend/afterSave', [$object]);
        return $this;
    }

    /**
     * Before Delete Entity process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\DataObject $object)
    {
        $this->walkAttributes('backend/beforeDelete', [$object]);
        return $this;
    }

    /**
     * After delete entity process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    protected function _afterDelete(\Magento\Framework\DataObject $object)
    {
        $this->walkAttributes('backend/afterDelete', [$object]);
        return $this;
    }

    /**
     * Retrieve Default attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return \Magento\Eav\Model\Entity::DEFAULT_ATTRIBUTE_MODEL;
    }

    /**
     * Retrieve default entity attributes
     *
     * @return string[]
     */
    protected function _getDefaultAttributes()
    {
        return ['entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id'];
    }

    /**
     * Retrieve default entity static attributes
     *
     * @return string[]
     */
    public function getDefaultAttributes()
    {
        return array_unique(array_merge(
            $this->_getDefaultAttributes(),
            [$this->getEntityIdField(), $this->getLinkField()]
        ));
    }

    /**
     * Check is attribute value empty
     *
     * @param AbstractAttribute $attribute
     * @param mixed $value
     * @return bool
     */
    protected function _isAttributeValueEmpty(AbstractAttribute $attribute, $value)
    {
        return $attribute->isValueEmpty($value);
    }

    /**
     * The getter function to get the AttributeLoaderInterface
     *
     * @return AttributeLoaderInterface
     *
     * @deprecated
     */
    protected function getAttributeLoader()
    {
        if ($this->attributeLoader === null) {
            $this->attributeLoader= ObjectManager::getInstance()->get(AttributeLoaderInterface::class);
        }
        return $this->attributeLoader;
    }

    /**
     * Perform actions after entity load
     *
     * @param \Magento\Framework\DataObject $object
     */
    public function afterLoad(\Magento\Framework\DataObject $object)
    {
        $this->_afterLoad($object);
    }

    /**
     * Perform actions before entity save
     *
     * @param \Magento\Framework\DataObject $object
     */
    public function beforeSave(\Magento\Framework\DataObject $object)
    {
        $this->_beforeSave($object);
    }

    /**
     * Perform actions after entity save
     *
     * @param \Magento\Framework\DataObject $object
     */
    public function afterSave(\Magento\Framework\DataObject $object)
    {
        $this->_afterSave($object);
    }

    /**
     * Perform actions before entity delete
     *
     * @param \Magento\Framework\DataObject $object
     */
    public function beforeDelete(\Magento\Framework\DataObject $object)
    {
        $this->_beforeDelete($object);
    }

    /**
     * Perform actions after entity delete
     *
     * @param \Magento\Framework\DataObject $object
     */
    public function afterDelete(\Magento\Framework\DataObject $object)
    {
        $this->_afterDelete($object);
    }
}
