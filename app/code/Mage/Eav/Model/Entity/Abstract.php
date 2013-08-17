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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Entity/Attribute/Model - entity abstract
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author     Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Eav_Model_Entity_Abstract extends Mage_Core_Model_Resource_Abstract
    implements Mage_Eav_Model_Entity_Interface
{
    /**
     * Read connection
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_read;

    /**
     * Write connection
     *
     * @var Varien_Db_Adapter_Pdo_Mysql
     */
    protected $_write;

    /**
     * Entity type configuration
     *
     * @var Mage_Eav_Model_Entity_Type
     */
    protected $_type;

    /**
     * Attributes array by attribute id
     *
     * @var array
     */
    protected $_attributesById              = array();

    /**
     * Attributes array by attribute name
     *
     * @var array
     */
    protected $_attributesByCode            = array();

    /**
     * 2-dimentional array by table name and attribute name
     *
     * @var array
     */
    protected $_attributesByTable           = array();

    /**
     * Attributes that are static fields in entity table
     *
     * @var array
     */
    protected $_staticAttributes = array();

    /**
     * Default Attributes that are static
     *
     * @var array
     */
    protected static $_defaultAttributes    = array();

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
    protected $_describeTable               = array();

    /**
     * Entity table identification field name
     *
     * @var string
     */
    protected $_entityIdField;

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

    /* Entity table string
     *
     * @var string
     */
    protected $_entityTablePrefix;

    /**
     * Partial load flag
     *
     * @var boolean
     */
    protected $_isPartialLoad = false;

    /**
     * Partial save flag
     *
     * @var boolean
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
    protected $_attributeValuesToDelete = array();

    /**
     * Entity attribute values per backend table to save
     *
     * @var array
     */
    protected $_attributeValuesToSave   = array();

    /**
     * Array of describe attribute backend tables
     * The table name as key
     *
     * @var array
     */
    protected static $_attributeBackendTables   = array();

    /**
     * Set connections for entity operations
     *
     * @param Zend_Db_Adapter_Abstract|string $read
     * @param Zend_Db_Adapter_Abstract|string|null $write
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function setConnection($read, $write = null)
    {
        $this->_read  = $read;
        $this->_write = $write ? $write : $read;

        return $this;
    }

    /**
     * Main constructor
     */
    public function __construct($data = array())
    {
        parent::__construct();
        $properties = get_object_vars($this);
        foreach ($data as $key => $value) {
            if (array_key_exists('_' . $key, $properties)) {
                $this->{'_' . $key} = $value;
            }
        }
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {}

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {
        if (is_string($this->_read)) {
            $this->_read = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection($this->_read);
        }
        return $this->_read;
    }

    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        if (is_string($this->_write)) {
            $this->_write = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection($this->_write);
        }
        return $this->_write;
    }

    /**
     * Retrieve read DB connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getReadConnection()
    {
        return $this->_getReadAdapter();
    }

    /**
     * Retrieve write DB connection
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function getWriteConnection()
    {
        return $this->_getWriteAdapter();
    }

    /**
     * For compatibility with Mage_Core_Model_Abstract
     *
     * @return string
     */
    public function getIdFieldName()
    {
        return $this->getEntityIdField();
    }

    /**
     * Retreive table name
     *
     * @param string $alias
     * @return string
     */
    public function getTable($alias)
    {
        return Mage::getSingleton('Mage_Core_Model_Resource')->getTableName($alias);
    }

    /**
     * Set configuration for the entity
     *
     * Accepts config node or name of entity type
     *
     * @param string|Mage_Eav_Model_Entity_Type $type
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function setType($type)
    {
        $this->_type = Mage::getSingleton('Mage_Eav_Model_Config')->getEntityType($type);
        return $this;
    }

    /**
     * Retrieve current entity config
     *
     * @return Mage_Eav_Model_Entity_Type
     */
    public function getEntityType()
    {
        if (empty($this->_type)) {
            throw Mage::exception('Mage_Eav', Mage::helper('Mage_Eav_Helper_Data')->__('Entity is not initialized'));
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
        return (int)$this->getEntityType()->getEntityTypeId();
    }

    /**
     * Unset attributes
     *
     * If NULL or not supplied removes configuration of all attributes
     * If string - removes only one, if array - all specified
     *
     * @param array|string|null $attributes
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function unsetAttributes($attributes = null)
    {
        if ($attributes === null) {
            $this->_attributesByCode    = array();
            $this->_attributesById      = array();
            $this->_attributesByTable   = array();
            return $this;
        }

        if (is_string($attributes)) {
            $attributes = array($attributes);
        }

        if (!is_array($attributes)) {
            throw Mage::exception('Mage_Eav', Mage::helper('Mage_Eav_Helper_Data')->__('Unknown parameter'));
        }

        foreach ($attributes as $attrCode) {
            if (!isset($this->_attributesByCode[$attrCode])) {
                continue;
            }

            $attr = $this->getAttribute($attrCode);
            unset($this->_attributesById[$attr->getId()]);
            unset($this->_attributesByTable[$attr->getBackend()->getTable()][$attrCode]);
            unset($this->_attributesByCode[$attrCode]);
        }

        return $this;
    }

    /**
     * Get EAV config model
     *
     * @return Mage_Eav_Model_Config
     */
    protected function _getConfig()
    {
        return Mage::getSingleton('Mage_Eav_Model_Config');
    }

    /**
     * Retrieve attribute instance by name, id or config node
     *
     * This will add the attribute configuration to entity's attributes cache
     *
     * If attribute is not found false is returned
     *
     * @param string|integer|Mage_Core_Model_Config_Element $attribute
     * @return Mage_Eav_Model_Entity_Attribute_Abstract || false
     */
    public function getAttribute($attribute)
    {
        /** @var $config Mage_Eav_Model_Config */
        $config = $this->_getConfig();
        if (is_numeric($attribute)) {
            $attributeId = $attribute;

            if (isset($this->_attributesById[$attributeId])) {
                return $this->_attributesById[$attributeId];
            }
            $attributeInstance = $config->getAttribute($this->getEntityType(), $attributeId);
            if ($attributeInstance) {
                $attributeCode = $attributeInstance->getAttributeCode();
            }
        } elseif (is_string($attribute)) {
            $attributeCode = $attribute;

            if (isset($this->_attributesByCode[$attributeCode])) {
                return $this->_attributesByCode[$attributeCode];
            }
            $attributeInstance = $config->getAttribute($this->getEntityType(), $attributeCode);
            if (!$attributeInstance->getAttributeCode() && in_array($attribute, $this->getDefaultAttributes())) {
                $attributeInstance
                    ->setAttributeCode($attribute)
                    ->setBackendType(Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC)
                    ->setIsGlobal(1)
                    ->setEntity($this)
                    ->setEntityType($this->getEntityType())
                    ->setEntityTypeId($this->getEntityType()->getId());
            }
        } elseif ($attribute instanceof Mage_Eav_Model_Entity_Attribute_Abstract) {
            $attributeInstance = $attribute;
            $attributeCode = $attributeInstance->getAttributeCode();
            if (isset($this->_attributesByCode[$attributeCode])) {
                return $this->_attributesByCode[$attributeCode];
            }
        }

        if (empty($attributeInstance)
            || !($attributeInstance instanceof Mage_Eav_Model_Entity_Attribute_Abstract)
            || (!$attributeInstance->getId()
            && !in_array($attributeInstance->getAttributeCode(), $this->getDefaultAttributes()))
        ) {
            return false;
        }

        $attribute = $attributeInstance;

        if (empty($attributeId)) {
            $attributeId = $attribute->getAttributeId();
        }

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
     * Return default static virtual attribute that doesn't exists in EAV attributes
     *
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Attribute
     */
    protected function _getDefaultAttribute($attributeCode)
    {
        $entityTypeId = $this->getEntityType()->getId();
        if (!isset(self::$_defaultAttributes[$entityTypeId][$attributeCode])) {
            $attribute = Mage::getModel($this->getEntityType()->getAttributeModel())
                ->setAttributeCode($attributeCode)
                ->setBackendType(Mage_Eav_Model_Entity_Attribute_Abstract::TYPE_STATIC)
                ->setIsGlobal(1)
                ->setEntityType($this->getEntityType())
                ->setEntityTypeId($this->getEntityType()->getId());
            self::$_defaultAttributes[$entityTypeId][$attributeCode] = $attribute;
        }

        return self::$_defaultAttributes[$entityTypeId][$attributeCode];
    }

    /**
     * Adding attribute to entity
     *
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    public function addAttribute(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        $attribute->setEntity($this);
        $attributeCode = $attribute->getAttributeCode();

        $this->_attributesByCode[$attributeCode] = $attribute;

        if ($attribute->isStatic()) {
            $this->_staticAttributes[$attributeCode] = $attribute;
        } else {
            $this->_attributesById[$attribute->getId()] = $attribute;
            $this->_attributesByTable[$attribute->getBackendTable()][$attributeCode] = $attribute;
        }

        return $this;
    }

    /**
     * Retreive partial load flag
     *
     * @param boolean $flag
     * @return boolean
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
     * Retreive partial save flag
     *
     * @param boolean $flag
     * @return boolean
     */
    public function isPartialSave($flag = null)
    {
        $result = $this->_isPartialSave;
        if ($flag !== null) {
            $this->_isPartialSave = (bool)$flag;
        }
        return $result;
    }

    /**
     * Retrieve configuration for all attributes
     *
     * @return Mage_Eav_Model_Entity_Attribute_Abstract
     */
    public function loadAllAttributes($object=null)
    {
        $attributeCodes = Mage::getSingleton('Mage_Eav_Model_Config')
            ->getEntityAttributeCodes($this->getEntityType(), $object);

        /**
         * Check and init default attributes
         */
        $defaultAttributes = $this->getDefaultAttributes();
        foreach ($defaultAttributes as $attributeCode) {
            $attributeIndex = array_search($attributeCode, $attributeCodes);
            if ($attributeIndex !== false) {
                $this->getAttribute($attributeCodes[$attributeIndex]);
                unset($attributeCodes[$attributeIndex]);
            } else {
                $this->addAttribute($this->_getDefaultAttribute($attributeCode));
            }
        }

        foreach ($attributeCodes as $code) {
            $this->getAttribute($code);
        }

        return $this;
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
        Mage::getSingleton('Mage_Eav_Model_Entity_Attribute_Set')
            ->addSetInfo($this->getEntityType(), $attributes, $setId);

        foreach ($attributes as $code => $attribute) {
            /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
            if (!$attribute->isInSet($setId)) {
                unset($attributes[$code]);
            }
        }

        $this->_sortingSetId = $setId;
        uasort($attributes, array($this, 'attributesCompare'));
        return $attributes;
    }

    /**
     * Compare attributes
     *
     * @param Mage_Eav_Model_Entity_Attribute $attribute1
     * @param Mage_Eav_Model_Entity_Attribute $attribute2
     * @return int
     */
    public function attributesCompare($attribute1, $attribute2)
    {
        $sort1 = $attribute1->getSortWeight((int)$this->_sortingSetId);
        $sort2 = $attribute2->getSortWeight((int)$this->_sortingSetId);

        if ($sort1 > $sort2) {
            return 1;
        } elseif ($sort1 < $sort2) {
            return -1;
        }

        return 0;
    }

    /**
     * Check whether the attribute is Applicable to the object
     *
     * @param   Varien_Object $object
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return  boolean
     */
    protected function _isApplicableAttribute($object, $attribute)
    {
        return true;
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
     * @throws Mage_Eav_Model_Entity_Attribute_Exception
     *
     * @return array
     */
    public function walkAttributes($partMethod, array $args = array(), $collectExceptionMessages = null)
    {
        $methodArr = explode('/', $partMethod);
        switch (sizeof($methodArr)) {
            case 1:
                $part   = 'attribute';
                $method = $methodArr[0];
                break;

            case 2:
                $part   = $methodArr[0];
                $method = $methodArr[1];
                break;
        }
        $results = array();
        foreach ($this->getAttributesByCode() as $attrCode => $attribute) {

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
            }

            if (!$this->_isCallableAttributeInstance($instance, $method, $args)) {
                continue;
            }

            try {
                $results[$attrCode] = call_user_func_array(array($instance, $method), $args);
            } catch (Mage_Eav_Model_Entity_Attribute_Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    throw $e;
                }
            } catch (Exception $e) {
                if ($collectExceptionMessages) {
                    $results[$attrCode] = $e->getMessage();
                } else {
                    $e = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Exception',
                        array('message' => $e->getMessage())
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
     * @param Mage_Eav_Model_Entity_Attribute_Abstract|Mage_Eav_Model_Entity_Attribute_Backend_Abstract|Mage_Eav_Model_Entity_Attribute_Frontend_Abstract|Mage_Eav_Model_Entity_Attribute_Source_Abstract $instance
     * @param string $method
     * @param array $args array of arguments
     * @return boolean
     */
    protected function _isCallableAttributeInstance($instance, $method, $args)
    {
        if (!is_object($instance) || !method_exists($instance, $method)) {
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
     * Get attributes by id array
     *
     * @return array
     */
    public function getAttributesById()
    {
        return $this->_attributesById;
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
                $table = Mage_Eav_Model_Entity::DEFAULT_ENTITY_TABLE;
            }
            $this->_entityTable = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName($table);
        }

        return $this->_entityTable;
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
                $this->_entityIdField = Mage_Eav_Model_Entity::DEFAULT_ENTITY_ID_FIELD;
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
            $prefix = (string)$this->getEntityType()->getValueTablePrefix();
            if (!empty($prefix)) {
                $this->_valueTablePrefix = $prefix;
                /**
                 * entity type prefix include DB table name prefix
                 */
                //Mage::getSingleton('Mage_Core_Model_Resource')->getTableName($prefix);
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
                    $prefix = Mage_Eav_Model_Entity::DEFAULT_ENTITY_TABLE;
                }
            }
            $this->_entityTablePrefix = $prefix;
        }

        return $this->_entityTablePrefix;
    }

    /**
     * Check whether the attribute is a real field in entity table
     *
     * @see Mage_Eav_Model_Entity_Abstract::getAttribute for $attribute format
     * @param integer|string|Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return boolean
     */
    public function isAttributeStatic($attribute)
    {
        $attrInstance = $this->getAttribute($attribute);
        return $attrInstance && $attrInstance->getBackend()->isStatic();
    }

    /**
     * Validate all object's attributes against configuration
     *
     * @param Varien_Object $object
     * @throws Mage_Eav_Model_Entity_Attribute_Exception
     * @return bool|array
     */
    public function validate($object)
    {
        $this->loadAllAttributes($object);
        $result = $this->walkAttributes('backend/validate', array($object), $object->getCollectExceptionMessages());
        $errors = array();
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
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function setNewIncrementId(Varien_Object $object)
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
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param Varien_Object $object
     * @return boolean
     */
    public function checkAttributeUniqueValue(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $object)
    {
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select();
        if ($attribute->getBackend()->getType() === 'static') {
            $value = $object->getData($attribute->getAttributeCode());
            $bind = array(
                'entity_type_id' => $this->getTypeId(),
                'attribute_code' => trim($value)
            );

            $select
                ->from($this->getEntityTable(), $this->getEntityIdField())
                ->where('entity_type_id = :entity_type_id')
                ->where($attribute->getAttributeCode() . ' = :attribute_code');
        } else {
            $value = $object->getData($attribute->getAttributeCode());
            if ($attribute->getBackend()->getType() == 'datetime') {
                $date  = new Zend_Date($value, Varien_Date::DATE_INTERNAL_FORMAT);
                $value = $date->toString(Varien_Date::DATETIME_INTERNAL_FORMAT);
            }
            $bind = array(
                'entity_type_id' => $this->getTypeId(),
                'attribute_id'   => $attribute->getId(),
                'value'          => trim($value)
            );
            $select
                ->from($attribute->getBackend()->getTable(), $attribute->getBackend()->getEntityIdField())
                ->where('entity_type_id = :entity_type_id')
                ->where('attribute_id = :attribute_id')
                ->where('value = :value');
        }
        $data = $adapter->fetchCol($select, $bind);

        if ($object->getId()) {
            if (isset($data[0])) {
                return $data[0] == $object->getId();
            }
            return true;
        }

        return !count($data);
    }

    /**
     * Retreive default source model
     *
     * @return string
     */
    public function getDefaultAttributeSourceModel()
    {
        return Mage_Eav_Model_Entity::DEFAULT_SOURCE_MODEL;
    }

    /**
     * Load entity's attributes into the object
     *
     * @param   Mage_Core_Model_Abstract $object
     * @param   integer $entityId
     * @param   array|null $attributes
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    public function load($object, $entityId, $attributes = array())
    {
        Magento_Profiler::start('EAV:load_entity');
        /**
         * Load object base row data
         */
        $select  = $this->_getLoadRowSelect($object, $entityId);
        $row     = $this->_getReadAdapter()->fetchRow($select);

        if (is_array($row)) {
            $object->addData($row);
        } else {
            $object->isObjectNew(true);
        }

        if (empty($attributes)) {
            $this->loadAllAttributes($object);
        } else {
            foreach ($attributes as $attrCode) {
                $this->getAttribute($attrCode);
            }
        }

        $this->_loadModelAttributes($object);

        $object->setOrigData();

        $this->_afterLoad($object);

        Magento_Profiler::stop('EAV:load_entity');
        return $this;
    }

    /**
     * Load model attributes data
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _loadModelAttributes($object)
    {
        if (!$object->getId()) {
            return $this;
        }

        Magento_Profiler::start('load_model_attributes');

        $selects = array();
        foreach (array_keys($this->getAttributesByTable()) as $table) {
            $attribute = current($this->_attributesByTable[$table]);
            $eavType = $attribute->getBackendType();
            $select = $this->_getLoadAttributesSelect($object, $table);
            $selects[$eavType][] = $this->_addLoadAttributesSelectFields($select, $table, $eavType);
        }
        $selectGroups = Mage::getResourceHelper('Mage_Eav')->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                $select = $this->_prepareLoadSelect($selects);
                $values = $this->_getReadAdapter()->fetchAll($select);
                foreach ($values as $valueRow) {
                    $this->_setAttributeValue($object, $valueRow);
                }
            }
        }

        Magento_Profiler::stop('load_model_attributes');

        return $this;
    }

    /**
     * Prepare select object for loading entity attributes values
     *
     * @param  array $selects
     * @return Zend_Db_Select
     */
    protected function _prepareLoadSelect(array $selects)
    {
        return $this->_getReadAdapter()->select()->union($selects, Zend_Db_Select::SQL_UNION_ALL);
    }

    /**
     * Retrieve select object for loading base entity row
     *
     * @param   Varien_Object $object
     * @param   mixed $rowId
     * @return  Zend_Db_Select
     */
    protected function _getLoadRowSelect($object, $rowId)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getEntityTable())
            ->where($this->getEntityIdField() . ' =?', $rowId);

        return $select;
    }

    /**
     * Retrieve select object for loading entity attributes values
     *
     * @param   Varien_Object $object
     * @param   mixed $rowId
     * @return  Zend_Db_Select
     */
    protected function _getLoadAttributesSelect($object, $table)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($table, array())
            ->where($this->getEntityIdField() . ' =?', $object->getId());

        return $select;
    }

    /**
     * Adds Columns prepared for union
     *
     * @param Varien_Db_Select $select
     * @param string $table
     * @param string $type
     * @return Varien_Db_Select
     */
    protected function _addLoadAttributesSelectFields($select, $table, $type)
    {
        $select->columns(
            Mage::getResourceHelper('Mage_Eav')->attributeSelectFields($table, $type)
        );
        return $select;
    }

    /**
     * Initialize attribute value for object
     *
     * @param   Varien_Object $object
     * @param   array $valueRow
     * @return  Mage_Eav_Model_Entity_Abstract
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
     * @param   Varien_Object $object
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    public function save(Varien_Object $object)
    {
        if ($object->isDeleted()) {
            return $this->delete($object);
        }

        if (!$this->isPartialSave()) {
            $this->loadAllAttributes($object);
        }

        if (!$object->getEntityTypeId()) {
            $object->setEntityTypeId($this->getTypeId());
        }

        $object->setParentId((int) $object->getParentId());

        $this->_beforeSave($object);
        $this->_processSaveData($this->_collectSaveData($object));
        $this->_afterSave($object);

        return $this;
    }

    /**
     * Retrieve Object instance with original data
     *
     * @param Varien_Object $object
     * @return Varien_Object
     */
    protected function _getOrigObject($object)
    {
        $className  = get_class($object);
        $origObject = Mage::getModel($className);
        $origObject->setData(array());
        $this->load($origObject, $object->getData($this->getEntityIdField()));

        return $origObject;
    }

    /**
     * Aggregate Data for attributes that will be deleted
     *
     * @param array $delete
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param Mage_Eav_Model_Entity_Abstract $object
     */
    private function _aggregateDeleteData(&$delete, $attribute, $object)
    {
        foreach ($attribute->getBackend()->getAffectedFields($object) as $tableName => $valuesData) {
            if (!isset($delete[$tableName])) {
                $delete[$tableName] = array();
            }
            $delete[$tableName] = array_merge((array)$delete[$tableName], $valuesData);
        }
    }

    /**
     * Prepare entity object data for save
     *
     * result array structure:
     * array (
     *  'newObject', 'entityRow', 'insert', 'update', 'delete'
     * )
     *
     * @param   Varien_Object $newObject
     * @return  array
     */
    protected function _collectSaveData($newObject)
    {
        $newData   = $newObject->getData();
        $entityId  = $newObject->getData($this->getEntityIdField());

        // define result data
        $entityRow  = array();
        $insert     = array();
        $update     = array();
        $delete     = array();

        if (!empty($entityId)) {
            $origData = $newObject->getOrigData();
            /**
             * get current data in db for this entity if original data is empty
             */
            if (empty($origData)) {
                $origData = $this->_getOrigObject($newObject)->getOrigData();
            }

            if (is_null($origData)) {
                $origData = array();
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
            $origData = array();
        }

        $staticFields   = $this->_getWriteAdapter()->describeTable($this->getEntityTable());
        $staticFields   = array_keys($staticFields);
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
            }

            $attrId = $attribute->getAttributeId();

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
                } elseif ((!is_numeric($v) && $v !== $origData[$k]) || (is_numeric($v) && $v != $origData[$k])) {
                    $update[$attrId] = array(
                        'value_id' => $attribute->getBackend()->getEntityValueId($newObject),
                        'value'    => $v,
                    );
                }
            } else if (!$this->_isAttributeValueEmpty($attribute, $v)) {
                $insert[$attrId] = $v;
            }
        }

        $result = compact('newObject', 'entityRow', 'insert', 'update', 'delete');
        return $result;
    }

    /**
     * Return if attribute exists in original data array.
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value New value of the attribute. Can be used in subclasses.
     * @param array $origData
     * @return bool
     */
    protected function _canUpdateAttribute(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $v, array &$origData)
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
            $this->_describeTable[$this->getEntityTable()] = $this->_getWriteAdapter()
                ->describeTable($this->getEntityTable());
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
            $value = Mage::app()->getLocale()->getNumber($value);
        }

        return $value;
    }

    /**
     * Save object collected data
     *
     * @param   array $saveData array('newObject', 'entityRow', 'insert', 'update', 'delete')
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _processSaveData($saveData)
    {
        extract($saveData);
        /**
         * Import variables into the current symbol table from save data array
         *
         * @see Mage_Eav_Model_Entity_Abstract::_collectSaveData()
         *
         * @var array $entityRow
         * @var Mage_Core_Model_Abstract $newObject
         * @var array $insert
         * @var array $update
         * @var array $delete
         */
        $adapter        = $this->_getWriteAdapter();
        $insertEntity   = true;
        $entityTable    = $this->getEntityTable();
        $entityIdField  = $this->getEntityIdField();
        $entityId       = $newObject->getId();

        unset($entityRow[$entityIdField]);
        if (!empty($entityId) && is_numeric($entityId)) {
            $bind   = array('entity_id' => $entityId);
            $select = $adapter->select()
                ->from($entityTable, $entityIdField)
                ->where("{$entityIdField} = :entity_id");
            $result = $adapter->fetchOne($select, $bind);
            if ($result) {
                $insertEntity = false;
            }
        } else {
            $entityId = null;
        }

        /**
         * Process base row
         */
        $entityObject = new Varien_Object($entityRow);
        $entityRow    = $this->_prepareDataForTable($entityObject, $entityTable);
        if ($insertEntity) {
            if (!empty($entityId)) {
                $entityRow[$entityIdField] = $entityId;
                $adapter->insertForce($entityTable, $entityRow);
            } else {
                $adapter->insert($entityTable, $entityRow);
                $entityId = $adapter->lastInsertId($entityTable);
            }
            $newObject->setId($entityId);
        } else {
            $where = sprintf('%s=%d', $adapter->quoteIdentifier($entityIdField), $entityId);
            $adapter->update($entityTable, $entityRow, $where);
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
     * @param   Varien_Object $object
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param   mixed $value
     * @return  Mage_Eav_Model_Entity_Abstract
     */
    protected function _insertAttribute($object, $attribute, $value)
    {
        return $this->_saveAttribute($object, $attribute, $value);
    }

    /**
     * Update entity attribute value
     *
     * @param   Varien_Object $object
     * @param   Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param   mixed $valueId
     * @param   mixed $value
     * @return  Mage_Eav_Model_Entity_Abstract
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
     * @param Mage_Core_Model_Abstract $object
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _saveAttribute($object, $attribute, $value)
    {
        $table = $attribute->getBackend()->getTable();
        if (!isset($this->_attributeValuesToSave[$table])) {
            $this->_attributeValuesToSave[$table] = array();
        }

        $entityIdField = $attribute->getBackend()->getEntityIdField();

        $data   = array(
            'entity_type_id'    => $object->getEntityTypeId(),
            $entityIdField      => $object->getId(),
            'attribute_id'      => $attribute->getId(),
            'value'             => $this->_prepareValueForSave($value, $attribute)
        );

        $this->_attributeValuesToSave[$table][] = $data;

        return $this;
    }

    /**
     * Save and detele collected attribute values
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _processAttributeValues()
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($this->_attributeValuesToSave as $table => $data) {
            $adapter->insertOnDuplicate($table, $data, array('value'));
        }

        foreach ($this->_attributeValuesToDelete as $table => $valueIds) {
            $adapter->delete($table, array('value_id IN (?)' => $valueIds));
        }

        // reset data arrays
        $this->_attributeValuesToSave   = array();
        $this->_attributeValuesToDelete = array();

        return $this;
    }

    /**
     * Prepare value for save
     *
     * @param mixed $value
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return mixed
     */
    protected function _prepareValueForSave($value, Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if ($attribute->getBackendType() == 'decimal') {
            return Mage::app()->getLocale()->getNumber($value);
        }

        $backendTable = $attribute->getBackendTable();
        if (!isset(self::$_attributeBackendTables[$backendTable])) {
            self::$_attributeBackendTables[$backendTable] = $this->_getReadAdapter()->describeTable($backendTable);
        }
        $describe = self::$_attributeBackendTables[$backendTable];
        return $this->_getReadAdapter()->prepareColumnValue($describe['value'], $value);
    }

    /**
     * Delete entity attribute values
     *
     * @param   Varien_Object $object
     * @param   string $table
     * @param   array $info
     * @return  Varien_Object
     */
    protected function _deleteAttributes($object, $table, $info)
    {
        $valueIds = array();
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
     * @param Varien_Object $object
     * @param string $attributeCode
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function saveAttribute(Varien_Object $object, $attributeCode)
    {
        $attribute      = $this->getAttribute($attributeCode);
        $backend        = $attribute->getBackend();
        $table          = $backend->getTable();
        $entity         = $attribute->getEntity();
        $entityIdField  = $entity->getEntityIdField();
        $adapter        = $this->_getWriteAdapter();

        $row = array(
            'entity_type_id' => $entity->getTypeId(),
            'attribute_id'   => $attribute->getId(),
            $entityIdField   => $object->getData($entityIdField),
        );

        $newValue = $object->getData($attributeCode);
        if ($attribute->isValueEmpty($newValue)) {
            $newValue = null;
        }

        $whereArr = array();
        foreach ($row as $field => $value) {
            $whereArr[] = $adapter->quoteInto($field . '=?', $value);
        }
        $where = implode(' AND ', $whereArr);

        $adapter->beginTransaction();

        try {
            $select = $adapter->select()
                ->from($table, 'value_id')
                ->where($where);
            $origValueId = $adapter->fetchOne($select);

            if ($origValueId === false && ($newValue !== null)) {
                $this->_insertAttribute($object, $attribute, $newValue);
            } elseif ($origValueId !== false && ($newValue !== null)) {
                $this->_updateAttribute($object, $attribute, $origValueId, $newValue);
            } elseif ($origValueId !== false && ($newValue === null)) {
                $adapter->delete($table, $where);
            }
            $this->_processAttributeValues();
            $adapter->commit();
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }

        return $this;
    }

    /**
     * Delete entity using current object's data
     *
     * @return Mage_Eav_Model_Entity_Abstract
     */
    public function delete($object)
    {
        if (is_numeric($object)) {
            $id = (int)$object;
        } elseif ($object instanceof Varien_Object) {
            $id = (int)$object->getId();
        }

        $this->_beforeDelete($object);

        try {
            $where = array(
                $this->getEntityIdField() . '=?' => $id
            );
            $this->_getWriteAdapter()->delete($this->getEntityTable(), $where);
            $this->loadAllAttributes($object);
            foreach ($this->getAttributesByTable() as $table => $attributes) {
                $this->_getWriteAdapter()->delete($table, $where);
            }
        } catch (Exception $e) {
            throw $e;
        }

        $this->_afterDelete($object);
        return $this;
    }

    /**
     * After Load Entity process
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _afterLoad(Varien_Object $object)
    {
        Magento_Profiler::start('after_load');
        $this->walkAttributes('backend/afterLoad', array($object));
        Magento_Profiler::stop('after_load');
        return $this;
    }

    /**
     * Before delete Entity process
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _beforeSave(Varien_Object $object)
    {
        $this->walkAttributes('backend/beforeSave', array($object));
        return $this;
    }

    /**
     * After Save Entity process
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _afterSave(Varien_Object $object)
    {
        $this->walkAttributes('backend/afterSave', array($object));
        return $this;
    }

    /**
     * Before Delete Entity process
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _beforeDelete(Varien_Object $object)
    {
        $this->walkAttributes('backend/beforeDelete', array($object));
        return $this;
    }

    /**
     * After delete entity process
     *
     * @param Varien_Object $object
     * @return Mage_Eav_Model_Entity_Abstract
     */
    protected function _afterDelete(Varien_Object $object)
    {
        $this->walkAttributes('backend/afterDelete', array($object));
        return $this;
    }

    /**
     * Retrieve Default attribute model
     *
     * @return string
     */
    protected function _getDefaultAttributeModel()
    {
        return Mage_Eav_Model_Entity::DEFAULT_ATTRIBUTE_MODEL;
    }

    /**
     * Retrieve default entity attributes
     *
     * @return array
     */
    protected function _getDefaultAttributes()
    {
        return array('entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id');
    }

    /**
     * Retrieve default entity static attributes
     *
     * @return array
     */
    public function getDefaultAttributes() {
        return array_unique(array_merge($this->_getDefaultAttributes(), array($this->getEntityIdField())));
    }

    /**
     * Check is attribute value empty
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param mixed $value
     * @return bool
     */
    protected function _isAttributeValueEmpty(Mage_Eav_Model_Entity_Attribute_Abstract $attribute, $value)
    {
        return $attribute->isValueEmpty($value);
    }
}
