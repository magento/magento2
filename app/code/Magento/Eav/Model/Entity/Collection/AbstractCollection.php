<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Eav\Model\Entity\Collection;

use Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;

/**
 * Entity/Attribute/Model - collection abstract
 *
 * @api
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
abstract class AbstractCollection extends AbstractDb implements SourceProviderInterface
{
    /**
     * Attribute table alias prefix
     */
    const ATTRIBUTE_TABLE_ALIAS_PREFIX = 'at_';

    /**
     * Array of items with item id key
     *
     * @var array
     */
    protected $_itemsById = [];

    /**
     * Entity static fields
     *
     * @var array
     */
    protected $_staticFields = [];

    /**
     * Entity object to define collection's attributes
     *
     * @var \Magento\Eav\Model\Entity\AbstractEntity
     */
    protected $_entity;

    /**
     * Entity types to be fetched for objects in collection
     *
     * @var array
     */
    protected $_selectEntityTypes = [];

    /**
     * Attributes to be fetched for objects in collection
     *
     * @var array
     */
    protected $_selectAttributes = [];

    /**
     * Attributes to be filtered order sorted by
     *
     * @var array
     */
    protected $_filterAttributes = [];

    /**
     * Joined entities
     *
     * @var array
     */
    protected $_joinEntities = [];

    /**
     * Joined attributes
     *
     * @var array
     */
    protected $_joinAttributes = [];

    /**
     * Joined fields data
     *
     * @var array
     */
    protected $_joinFields = [];

    /**
     * Cast map for attribute order
     *
     * @var string[]
     */
    protected $_castToIntMap = ['validate-digits'];

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $_eavEntityFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Helper
     */
    protected $_resourceHelper;

    /**
     * @var \Magento\Framework\Validator\UniversalFactory
     */
    protected $_universalFactory;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param mixed $connection
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->_eventManager = $eventManager;
        $this->_eavConfig = $eavConfig;
        $this->_resource = $resource;
        $this->_eavEntityFactory = $eavEntityFactory;
        $this->_resourceHelper = $resourceHelper;
        $this->_universalFactory = $universalFactory;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $connection);
        $this->_construct();
        $this->setConnection($this->getEntity()->getConnection());
        $this->_prepareStaticFields();
        $this->_initSelect();
    }

    /**
     * Initialize collection
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     * @codeCoverageIgnore
     */
    public function getTable($table)
    {
        return $this->getResource()->getTable($table);
    }

    /**
     * Prepare static entity fields
     *
     * @return $this
     */
    protected function _prepareStaticFields()
    {
        foreach ($this->getEntity()->getDefaultAttributes() as $field) {
            $this->_staticFields[$field] = $field;
        }
        return $this;
    }

    /**
     * Init select
     *
     * @return $this
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['e' => $this->getEntity()->getEntityTable()]);
        $entity = $this->getEntity();
        if ($entity->getTypeId() && $entity->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE) {
            $this->addAttributeToFilter('entity_type_id', $this->getEntity()->getTypeId());
        }
        return $this;
    }

    /**
     * Standard resource collection initialization
     *
     * @param string $model
     * @param string $entityModel
     * @return $this
     */
    protected function _init($model, $entityModel)
    {
        $this->setItemObjectClass($model);
        $entity = $this->_universalFactory->create($entityModel);
        $this->setEntity($entity);

        return $this;
    }

    /**
     * Set entity to use for attributes
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity
     * @return $this
     * @throws LocalizedException
     */
    public function setEntity($entity)
    {
        if ($entity instanceof \Magento\Eav\Model\Entity\AbstractEntity) {
            $this->_entity = $entity;
        } elseif (is_string($entity) || $entity instanceof \Magento\Framework\App\Config\Element) {
            $this->_entity = $this->_eavEntityFactory->create()->setType($entity);
        } else {
            throw new LocalizedException(
                __('The "%1" entity supplied is invalid. Verify the entity and try again.', print_r($entity, 1))
            );
        }
        return $this;
    }

    /**
     * Get collection's entity object
     *
     * @return \Magento\Eav\Model\Entity\AbstractEntity
     * @throws LocalizedException
     */
    public function getEntity()
    {
        if (empty($this->_entity)) {
            throw new LocalizedException(__('Entity is not initialized'));
        }
        return $this->_entity;
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @codeCoverageIgnore
     */
    public function getResource()
    {
        return $this->getEntity();
    }

    /**
     * Set template object for the collection
     *
     * @param   \Magento\Framework\DataObject $object
     * @return $this
     */
    public function setObject($object = null)
    {
        if (is_object($object)) {
            $this->setItemObjectClass(get_class($object));
        } else {
            $this->setItemObjectClass($object);
        }
        return $this;
    }

    /**
     * Add an object to the collection
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     * @throws LocalizedException
     */
    public function addItem(\Magento\Framework\DataObject $object)
    {
        if (!$object instanceof $this->_itemObjectClass) {
            throw new LocalizedException(
                __("The object wasn't added because it's invalid. To continue, enter a valid object and try again.")
            );
        }
        return parent::addItem($object);
    }

    /**
     * Retrieve entity attribute
     *
     * @param   string $attributeCode
     * @return  \Magento\Eav\Model\Entity\Attribute\AbstractAttribute
     */
    public function getAttribute($attributeCode)
    {
        if (isset($this->_joinAttributes[$attributeCode])) {
            return $this->_joinAttributes[$attributeCode]['attribute'];
        }

        return $this->getEntity()->getAttribute($attributeCode);
    }

    /**
     * Add attribute filter to collection
     *
     * If $attribute is an array will add OR condition with following format:
     * array(
     *     array('attribute'=>'firstname', 'like'=>'test%'),
     *     array('attribute'=>'lastname', 'like'=>'test%'),
     * )
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AttributeInterface|integer|string|array $attribute
     * @param null|string|array $condition
     * @param string $joinType
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     * @see self::_getConditionSql for $condition
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        if ($attribute === null) {
            $this->getSelect();
            return $this;
        }

        if (is_numeric($attribute)) {
            $attributeModel = $this->getEntity()->getAttribute($attribute);
            if (!$attributeModel) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Invalid attribute identifier for filter (%1)', get_class($attribute))
                );
            }
            $attribute = $attributeModel->getAttributeCode();
        } elseif ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AttributeInterface) {
            $attribute = $attribute->getAttributeCode();
        }

        if (is_array($attribute)) {
            $sqlArr = [];
            foreach ($attribute as $condition) {
                $sqlArr[] = $this->_getAttributeConditionSql($condition['attribute'], $condition, $joinType);
            }
            $conditionSql = '(' . implode(') OR (', $sqlArr) . ')';
        } elseif (is_string($attribute)) {
            if ($condition === null) {
                $condition = '';
            }
            $conditionSql = $this->_getAttributeConditionSql($attribute, $condition, $joinType);
        }

        if (!empty($conditionSql)) {
            $this->getSelect()->where($conditionSql, null, \Magento\Framework\DB\Select::TYPE_CONDITION);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Invalid attribute identifier for filter (%1)', get_class($attribute))
            );
        }

        return $this;
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param mixed $attribute
     * @param mixed $condition
     * @return $this|AbstractDb
     * @codeCoverageIgnore
     */
    public function addFieldToFilter($attribute, $condition = null)
    {
        return $this->addAttributeToFilter($attribute, $condition);
    }

    /**
     * Add attribute to sort order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function addAttributeToSort($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (isset($this->_joinFields[$attribute])) {
            $this->getSelect()->order($this->_getAttributeFieldName($attribute) . ' ' . $dir);
            return $this;
        }
        if (isset($this->_staticFields[$attribute])) {
            $this->getSelect()->order("e.{$attribute} {$dir}");
            return $this;
        }
        if (isset($this->_joinAttributes[$attribute])) {
            $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            $entityField = $this->_getAttributeTableAlias($attribute) . '.' . $attrInstance->getAttributeCode();
        } else {
            $attrInstance = $this->getEntity()->getAttribute($attribute);
            $entityField = 'e.' . $attribute;
        }

        if ($attrInstance) {
            if ($attrInstance->getBackend()->isStatic()) {
                $orderExpr = $entityField;
            } else {
                $this->_addAttributeJoin($attribute, 'left');
                if (isset($this->_joinAttributes[$attribute]) || isset($this->_joinFields[$attribute])) {
                    $orderExpr = $attribute;
                } else {
                    $orderExpr = $this->_getAttributeTableAlias($attribute) . '.value';
                }
            }

            if (in_array($attrInstance->getFrontendClass(), $this->_castToIntMap)) {
                $orderExpr = new \Zend_Db_Expr("CAST({$this->_prepareOrderExpression($orderExpr)} AS SIGNED)");
            }

            $orderExpr .= ' ' . $dir;
            $this->getSelect()->order($orderExpr);
        }
        return $this;
    }

    /**
     * Retrieve attribute expression by specified column
     *
     * @param string $field
     * @return string|Zend_Db_Expr
     */
    protected function _prepareOrderExpression($field)
    {
        foreach ($this->getSelect()->getPart(\Magento\Framework\DB\Select::COLUMNS) as $columnEntry) {
            if ($columnEntry[2] != $field) {
                continue;
            }
            if ($columnEntry[1] instanceof \Zend_Db_Expr) {
                return $columnEntry[1];
            }
        }
        return $field;
    }

    /**
     * Add attribute to entities in collection
     *
     * If $attribute == '*' select all attributes
     *
     * @param array|string|integer|\Magento\Framework\App\Config\Element $attribute
     * @param bool|string $joinType flag for joining attribute
     * @return $this
     * @throws LocalizedException
     */
    public function addAttributeToSelect($attribute, $joinType = false)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $a) {
                $this->addAttributeToSelect($a, $joinType);
            }
            return $this;
        }
        if ($joinType !== false && !$this->getEntity()->getAttribute($attribute)->isStatic()) {
            $this->_addAttributeJoin($attribute, $joinType);
        } elseif ('*' === $attribute) {
            $entity = clone $this->getEntity();
            $attributes = $entity->loadAllAttributes()->getAttributesByCode();
            foreach ($attributes as $attrCode => $attr) {
                $this->_selectAttributes[$attrCode] = $attr->getId();
            }
        } else {
            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
            } else {
                $attrInstance = $this->_eavConfig->getAttribute($this->getEntity()->getType(), $attribute);
            }
            if (empty($attrInstance)) {
                throw new LocalizedException(
                    __(
                        'The "%1" attribute requested is invalid. Verify the attribute and try again.',
                        (string)$attribute
                    )
                );
            }
            $this->_selectAttributes[$attrInstance->getAttributeCode()] = $attrInstance->getId();
        }
        return $this;
    }

    /**
     * Add entity type to select statement
     *
     * @param string $entityType
     * @param string $prefix
     * @return $this
     * @codeCoverageIgnore
     */
    public function addEntityTypeToSelect($entityType, $prefix)
    {
        $this->_selectEntityTypes[$entityType] = ['prefix' => $prefix];
        return $this;
    }

    /**
     * Add field to static
     *
     * @param string $field
     * @return $this
     */
    public function addStaticField($field)
    {
        if (!isset($this->_staticFields[$field])) {
            $this->_staticFields[$field] = $field;
        }
        return $this;
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     *
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     *
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param string $attribute
     * @return $this
     * @throws LocalizedException
     */
    public function addExpressionAttributeToSelect($alias, $expression, $attribute)
    {
        // validate alias
        if (isset($this->_joinFields[$alias])) {
            throw new LocalizedException(__('Joint field or attribute expression with this alias is already declared'));
        }
        if (!is_array($attribute)) {
            $attribute = [$attribute];
        }

        $fullExpression = $expression;
        // Replacing multiple attributes
        foreach ($attribute as $attributeItem) {
            if (isset($this->_staticFields[$attributeItem])) {
                $attrField = sprintf('e.%s', $attributeItem);
            } else {
                $attributeInstance = $this->getAttribute($attributeItem);

                if ($attributeInstance->getBackend()->isStatic()) {
                    $attrField = 'e.' . $attributeItem;
                } else {
                    $this->_addAttributeJoin($attributeItem, 'left');
                    $attrField = $this->_getAttributeFieldName($attributeItem);
                }
            }

            $fullExpression = str_replace('{{attribute}}', $attrField, $fullExpression);
            $fullExpression = str_replace('{{' . $attributeItem . '}}', $attrField, $fullExpression);
        }

        $this->getSelect()->columns([$alias => $fullExpression]);

        $this->_joinFields[$alias] = ['table' => false, 'field' => $fullExpression];

        return $this;
    }

    /**
     * Groups results by specified attribute
     *
     * @param string|array $attribute
     * @return $this
     */
    public function groupByAttribute($attribute)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attributeItem) {
                $this->groupByAttribute($attributeItem);
            }
        } else {
            if (isset($this->_joinFields[$attribute])) {
                $this->getSelect()->group($this->_getAttributeFieldName($attribute));
                return $this;
            }

            if (isset($this->_staticFields[$attribute])) {
                $this->getSelect()->group(sprintf('e.%s', $attribute));
                return $this;
            }

            if (isset($this->_joinAttributes[$attribute])) {
                $attrInstance = $this->_joinAttributes[$attribute]['attribute'];
                $entityField = $this->_getAttributeTableAlias($attribute) . '.' . $attrInstance->getAttributeCode();
            } else {
                $attrInstance = $this->getEntity()->getAttribute($attribute);
                $entityField = 'e.' . $attribute;
            }

            if ($attrInstance->getBackend()->isStatic()) {
                $this->getSelect()->group($entityField);
            } else {
                $this->_addAttributeJoin($attribute);
                $this->getSelect()->group($this->_getAttributeTableAlias($attribute) . '.value');
            }
        }

        return $this;
    }

    /**
     * Add attribute from joined entity to select
     *
     * Examples:
     * ('billing_firstname', 'customer_address/firstname', 'default_billing')
     * ('billing_lastname', 'customer_address/lastname', 'default_billing')
     * ('shipping_lastname', 'customer_address/lastname', 'default_billing')
     * ('shipping_postalcode', 'customer_address/postalcode', 'default_shipping')
     * ('shipping_city', $cityAttribute, 'default_shipping')
     *
     * Developer is encouraged to use existing instances of attributes and entities
     * After first use of string entity name it will be cached in the collection
     *
     * @todo connect between joined attributes of same entity
     * @param string $alias alias for the joined attribute
     * @param string|\Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute
     * @param string $bind attribute of the main entity to link with joined $filter
     * @param string $filter primary key for the joined entity (entity_id default)
     * @param string $joinType inner|left
     * @param null $storeId
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function joinAttribute($alias, $attribute, $bind, $filter = null, $joinType = 'inner', $storeId = null)
    {
        // validate alias
        if (isset($this->_joinAttributes[$alias])) {
            throw new LocalizedException(__('Invalid alias, already exists in joint attributes'));
        }

        $bindAttribute = null;
        // validate bind attribute
        if (is_string($bind)) {
            $bindAttribute = $this->getAttribute($bind);
        }

        if (!$bindAttribute || !$bindAttribute->isStatic() && !$bindAttribute->getId()) {
            throw new LocalizedException(__('The foreign key is invalid. Verify the foreign key and try again.'));
        }

        // try to explode combined entity/attribute if supplied
        if (is_string($attribute)) {
            $attrArr = explode('/', $attribute);
            if (isset($attrArr[1])) {
                $entity = $attrArr[0];
                $attribute = $attrArr[1];
            }
        }

        // validate entity
        if (empty($entity) && $attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
            $entity = $attribute->getEntity();
        } elseif (is_string($entity)) {
            // retrieve cached entity if possible
            if (isset($this->_joinEntities[$entity])) {
                $entity = $this->_joinEntities[$entity];
            } else {
                $entity = $this->_eavEntityFactory->create()->setType($attrArr[0]);
            }
        }
        if (!$entity || !$entity->getTypeId()) {
            throw new LocalizedException(__('The entity type is invalid. Verify the entity type and try again.'));
        }

        // cache entity
        if (!isset($this->_joinEntities[$entity->getType()])) {
            $this->_joinEntities[$entity->getType()] = $entity;
        }

        // validate attribute
        if (is_string($attribute)) {
            $attribute = $entity->getAttribute($attribute);
        }
        if (!$attribute) {
            throw new LocalizedException(__('The attribute type is invalid. Verify the attribute type and try again.'));
        }

        if (empty($filter)) {
            $filter = $entity->getLinkField();
        }

        // add joined attribute
        $this->_joinAttributes[$alias] = [
            'bind' => $bind,
            'bindAttribute' => $bindAttribute,
            'attribute' => $attribute,
            'filter' => $filter,
            'store_id' => $storeId,
        ];

        $this->_addAttributeJoin($alias, $joinType);

        return $this;
    }

    /**
     * Join regular table field and use an attribute as fk
     *
     * Examples:
     * ('country_name', 'directory_country_name', 'name', 'country_id=shipping_country',
     *      "{{table}}.language_code='en'", 'left')
     *
     * @param string $alias 'country_name'
     * @param string $table 'directory_country_name'
     * @param string $field 'name'
     * @param string $bind 'PK(country_id)=FK(shipping_country_id)'
     * @param string|array $cond "{{table}}.language_code='en'" OR array('language_code'=>'en')
     * @param string $joinType 'left'
     * @return $this
     * @throws LocalizedException
     */
    public function joinField($alias, $table, $field, $bind, $cond = null, $joinType = 'inner')
    {
        // validate alias
        if (isset($this->_joinFields[$alias])) {
            throw new LocalizedException(__('A joined field with this alias is already declared.'));
        }

        $table = $this->_resource->getTableName($table);
        $tableAlias = $this->_getAttributeTableAlias($alias);

        // validate bind
        list($pKey, $fKey) = explode('=', $bind);
        $pKey = $this->getSelect()->getConnection()->quoteColumnAs(trim($pKey), null);
        $bindCond = $tableAlias . '.' . trim($pKey) . '=' . $this->_getAttributeFieldName(trim($fKey));

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;
            default:
                $joinMethod = 'join';
                break;
        }
        $condArr = [$bindCond];

        // add where condition if needed
        if ($cond !== null) {
            if (is_array($cond)) {
                foreach ($cond as $key => $value) {
                    $condArr[] = $this->_getConditionSql($tableAlias . '.' . $key, $value);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '(' . implode(') AND (', $condArr) . ')';

        // join table
        $this->getSelect()->{$joinMethod}(
            [$tableAlias => $table],
            $cond,
            $field ? [$alias => $field] : []
        );

        // save joined attribute
        $this->_joinFields[$alias] = ['table' => $tableAlias, 'field' => $field];

        return $this;
    }

    /**
     * Join a table
     *
     * @param string|array $table
     * @param string $bind
     * @param string|array $fields
     * @param null|array $cond
     * @param string $joinType
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function joinTable($table, $bind, $fields = null, $cond = null, $joinType = 'inner')
    {
        $tableAlias = null;
        if (is_array($table)) {
            list($tableAlias, $tableName) = [key($table), current($table)];
        } else {
            $tableName = $table;
        }

        $tableName = $this->_resource->getTableName($tableName);
        if (empty($tableAlias)) {
            $tableAlias = $tableName;
        }

        // validate fields and aliases
        if (!$fields) {
            throw new LocalizedException(__('Invalid joint fields'));
        }
        foreach ($fields as $alias => $field) {
            if (isset($this->_joinFields[$alias])) {
                throw new LocalizedException(__('A joint field with a "%1" alias is already declared.', $alias));
            }
            $this->_joinFields[$alias] = ['table' => $tableAlias, 'field' => $field];
        }

        // validate bind
        list($pKey, $fKey) = explode('=', $bind);
        $bindCond = $tableAlias . '.' . $pKey . '=' . $this->_getAttributeFieldName($fKey);

        // process join type
        switch ($joinType) {
            case 'left':
                $joinMethod = 'joinLeft';
                break;

            default:
                $joinMethod = 'join';
        }
        $condArr = [$bindCond];

        // add where condition if needed
        if ($cond !== null) {
            if (is_array($cond)) {
                foreach ($cond as $key => $value) {
                    $condArr[] = $this->_getConditionSql($tableAlias . '.' . $key, $value);
                }
            } else {
                $condArr[] = str_replace('{{table}}', $tableAlias, $cond);
            }
        }
        $cond = '(' . implode(') AND (', $condArr) . ')';

        // join table
        $this->getSelect()->{$joinMethod}([$tableAlias => $tableName], $cond, $fields);

        return $this;
    }

    /**
     * Remove an attribute from selection list
     *
     * @param string $attribute
     * @return $this
     */
    public function removeAttributeToSelect($attribute = null)
    {
        if ($attribute === null) {
            $this->_selectAttributes = [];
        } else {
            unset($this->_selectAttributes[$attribute]);
        }
        return $this;
    }

    /**
     * Set collection page start and records to show
     *
     * @param integer $pageNum
     * @param integer $pageSize
     * @return $this
     * @codeCoverageIgnore
     */
    public function setPage($pageNum, $pageSize)
    {
        $this->setCurPage($pageNum)->setPageSize($pageSize);
        return $this;
    }

    /**
     * Load collection data into object items
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        \Magento\Framework\Profiler::start('EAV:load_collection');

        \Magento\Framework\Profiler::start('before_load');
        $this->_eventManager->dispatch('eav_collection_abstract_load_before', ['collection' => $this]);
        $this->_beforeLoad();
        \Magento\Framework\Profiler::stop('before_load');

        $this->_renderFilters();
        $this->_renderOrders();

        \Magento\Framework\Profiler::start('load_entities');
        $this->_loadEntities($printQuery, $logQuery);
        \Magento\Framework\Profiler::stop('load_entities');
        \Magento\Framework\Profiler::start('load_attributes');
        $this->_loadAttributes($printQuery, $logQuery);
        \Magento\Framework\Profiler::stop('load_attributes');

        \Magento\Framework\Profiler::start('set_orig_data');
        foreach ($this->_items as $item) {
            $item->setOrigData();
            $this->beforeAddLoadedItem($item);
            $item->setDataChanges(false);
        }
        \Magento\Framework\Profiler::stop('set_orig_data');

        $this->_setIsLoaded();
        \Magento\Framework\Profiler::start('after_load');
        $this->_afterLoad();
        \Magento\Framework\Profiler::stop('after_load');

        \Magento\Framework\Profiler::stop('EAV:load_collection');
        return $this;
    }

    /**
     * Clone and reset collection
     *
     * @param null $limit
     * @param null $offset
     * @return Select
     */
    protected function _getAllIdsSelect($limit = null, $offset = null)
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());
        $idsSelect->limit($limit, $offset);

        return $idsSelect;
    }

    /**
     * Retrieve all ids for collection
     *
     * @param null|int|string $limit
     * @param null|int|string $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol($this->_getAllIdsSelect($limit, $offset), $this->_bindParams);
    }

    /**
     * Retrieve all ids sql
     *
     * @return Select
     */
    public function getAllIdsSql()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $idsSelect->reset(\Magento\Framework\DB\Select::GROUP);
        $idsSelect->columns('e.' . $this->getEntity()->getIdFieldName());

        return $idsSelect;
    }

    /**
     * Save all the entities in the collection
     *
     * @todo make batch save directly from collection
     *
     * @return $this
     */
    public function save()
    {
        foreach ($this->getItems() as $item) {
            $item->save();
        }
        return $this;
    }

    /**
     * Delete all the entities in the collection
     *
     * @todo make batch delete directly from collection
     *
     * @return $this
     */
    public function delete()
    {
        foreach ($this->getItems() as $key => $item) {
            $this->getEntity()->delete($item);
            unset($this->_items[$key]);
        }
        return $this;
    }

    /**
     * Import 2D array into collection as objects
     *
     * If the imported items already exist, update the data for existing objects
     *
     * @param array $arr
     * @return $this
     */
    public function importFromArray($arr)
    {
        $entityIdField = $this->getEntity()->getLinkField();
        foreach ($arr as $row) {
            $entityId = $row[$entityIdField];
            if (!isset($this->_items[$entityId])) {
                $this->_items[$entityId] = $this->getNewEmptyItem();
                $this->_items[$entityId]->setData($row);
            } else {
                $this->_items[$entityId]->addData($row);
            }
        }
        return $this;
    }

    /**
     * Get collection data as a 2D array
     *
     * @return array
     */
    public function exportToArray()
    {
        $result = [];
        $entityIdField = $this->getEntity()->getLinkField();
        foreach ($this->getItems() as $item) {
            $result[$item->getData($entityIdField)] = $item->getData();
        }
        return $result;
    }

    /**
     * Retrieve row id field name
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getRowIdFieldName()
    {
        return $this->getIdFieldName();
    }

    /**
     * Id field name getter
     *
     * @return string
     */
    public function getIdFieldName()
    {
        if ($this->_idFieldName === null) {
            $this->_setIdFieldName($this->getEntity()->getIdFieldName());
        }

        return $this->_idFieldName;
    }

    /**
     * Set row id field name
     *
     * @param string $fieldName
     * @return $this
     */
    public function setRowIdFieldName($fieldName)
    {
        return $this->_setIdFieldName($fieldName);
    }

    /**
     * Load entities records into items
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws \Exception
     */
    public function _loadEntities($printQuery = false, $logQuery = false)
    {
        $this->getEntity();

        if ($this->_pageSize) {
            $this->getSelect()->limitPage($this->getCurPage(), $this->_pageSize);
        }

        $this->printLogQuery($printQuery, $logQuery);

        try {
            /**
             * Prepare select query
             * @var string $query
             */
            $query = $this->getSelect();
            $rows = $this->_fetchAll($query);
        } catch (\Exception $e) {
            $this->printLogQuery(false, true, $query);
            throw $e;
        }

        foreach ($rows as $value) {
            $object = $this->getNewEmptyItem()->setData($value);
            $this->addItem($object);
            if (isset($this->_itemsById[$object->getId()])) {
                $this->_itemsById[$object->getId()][] = $object;
            } else {
                $this->_itemsById[$object->getId()] = [$object];
            }
        }

        return $this;
    }

    /**
     * Load attributes into loaded entities
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @throws LocalizedException
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _loadAttributes($printQuery = false, $logQuery = false)
    {
        if (empty($this->_items) || empty($this->_itemsById) || empty($this->_selectAttributes)) {
            return $this;
        }

        $entity = $this->getEntity();

        $tableAttributes = [];
        $attributeTypes = [];
        foreach ($this->_selectAttributes as $attributeCode => $attributeId) {
            if (!$attributeId) {
                continue;
            }
            $attribute = $this->_eavConfig->getAttribute($entity->getType(), $attributeCode);
            if ($attribute && !$attribute->isStatic()) {
                $tableAttributes[$attribute->getBackendTable()][] = $attributeId;
                if (!isset($attributeTypes[$attribute->getBackendTable()])) {
                    $attributeTypes[$attribute->getBackendTable()] = $attribute->getBackendType();
                }
            }
        }

        $selects = [];
        foreach ($tableAttributes as $table => $attributes) {
            $select = $this->_getLoadAttributesSelect($table, $attributes);
            $selects[$attributeTypes[$table]][] = $this->_addLoadAttributesSelectValues(
                $select,
                $table,
                $attributeTypes[$table]
            );
        }
        $selectGroups = $this->_resourceHelper->getLoadAttributesSelectGroups($selects);
        foreach ($selectGroups as $selects) {
            if (!empty($selects)) {
                try {
                    if (is_array($selects)) {
                        $select = implode(' UNION ALL ', $selects);
                    } else {
                        $select = $selects;
                    }
                    $values = $this->getConnection()->fetchAll($select);
                } catch (\Exception $e) {
                    $this->printLogQuery(true, true, $select);
                    throw $e;
                }

                foreach ($values as $value) {
                    $this->_setItemAttributeValue($value);
                }
            }
        }

        return $this;
    }

    /**
     * Retrieve attributes load select
     *
     * @param string $table
     * @param string[] $attributeIds
     * @return Select
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }
        $entity = $this->getEntity();
        $linkField = $entity->getLinkField();
        $select = $this->getConnection()->select()
            ->from(
                ['e' => $this->getEntity()->getEntityTable()],
                ['entity_id']
            )
            ->join(
                ['t_d' => $table],
                "e.{$linkField} = t_d.{$linkField}",
                ['t_d.attribute_id']
            )->where(
                " e.entity_id IN (?)",
                array_keys($this->_itemsById)
            )->where(
                't_d.attribute_id IN (?)',
                $attributeIds
            );

        if ($entity->getEntityTable() == \Magento\Eav\Model\Entity::DEFAULT_ENTITY_TABLE && $entity->getTypeId()) {
            $select->where(
                'entity_type_id =?',
                $entity->getTypeId()
            );
        }
        return $select;
    }

    /**
     * Add select values
     *
     * @param \Magento\Framework\DB\Select $select
     * @param string $table
     * @param string $type
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        $select->columns(['value' => 't_d.value']);
        return $select;
    }

    /**
     * Initialize entity object property value
     *
     * Parameter $valueInfo is _getLoadAttributesSelect fetch result row
     *
     * @param array $valueInfo
     * @return $this
     * @throws LocalizedException
     */
    protected function _setItemAttributeValue($valueInfo)
    {
        $entityIdField = $this->getEntity()->getEntityIdField();
        $entityId = $valueInfo[$entityIdField];
        if (!isset($this->_itemsById[$entityId])) {
            throw new LocalizedException(
                __('A header row is missing for an attribute. Verify the header row and try again.')
            );
        }
        $attributeCode = array_search($valueInfo['attribute_id'], $this->_selectAttributes);
        if (!$attributeCode) {
            $attribute = $this->_eavConfig->getAttribute(
                $this->getEntity()->getType(),
                $valueInfo['attribute_id']
            );
            $attributeCode = $attribute->getAttributeCode();
        }

        foreach ($this->_itemsById[$entityId] as $object) {
            $object->setData($attributeCode, $valueInfo['value']);
        }

        return $this;
    }

    /**
     * Get alias for attribute value table
     *
     * @param string $attributeCode
     * @return string
     */
    protected function _getAttributeTableAlias($attributeCode)
    {
        return $this->getConnection()->getTableName(self::ATTRIBUTE_TABLE_ALIAS_PREFIX . $attributeCode);
    }

    /**
     * Retrieve attribute field name by attribute code
     *
     * @param string $attributeCode
     * @return string
     * @throws LocalizedException
     */
    protected function _getAttributeFieldName($attributeCode)
    {
        $attributeCode = trim($attributeCode);
        if (isset($this->_joinAttributes[$attributeCode]['condition_alias'])) {
            return $this->_joinAttributes[$attributeCode]['condition_alias'];
        }
        if (isset($this->_staticFields[$attributeCode])) {
            return sprintf('e.%s', $attributeCode);
        }
        if (isset($this->_joinFields[$attributeCode])) {
            $attr = $this->_joinFields[$attributeCode];
            return $attr['table'] ? $attr['table'] . '.' . $attr['field'] : $attr['field'];
        }

        $attribute = $this->getAttribute($attributeCode);
        if (!$attribute) {
            throw new LocalizedException(
                __('The "%1" attribute name is invalid. Reset the name and try again.', $attributeCode)
            );
        }

        if ($attribute->isStatic()) {
            if (isset($this->_joinAttributes[$attributeCode])) {
                $fieldName = $this->_getAttributeTableAlias($attributeCode) . '.' . $attributeCode;
            } else {
                $fieldName = 'e.' . $attributeCode;
            }
        } else {
            $fieldName = $this->_getAttributeTableAlias($attributeCode) . '.value';
        }

        return $fieldName;
    }

    /**
     * Add attribute value table to the join if it wasn't added previously
     *
     * @param   string $attributeCode
     * @param   string $joinType inner|left
     * @return $this
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _addAttributeJoin($attributeCode, $joinType = 'inner')
    {
        if (!empty($this->_filterAttributes[$attributeCode])) {
            return $this;
        }

        $connection = $this->getConnection();

        $attrTable = $this->_getAttributeTableAlias($attributeCode);
        if (isset($this->_joinAttributes[$attributeCode])) {
            $attribute = $this->_joinAttributes[$attributeCode]['attribute'];
            $fkName = $this->_joinAttributes[$attributeCode]['bind'];
            $fkAttribute = $this->_joinAttributes[$attributeCode]['bindAttribute'];
            $fkTable = $this->_getAttributeTableAlias($fkName);

            if ($fkAttribute->getBackend()->isStatic()) {
                if (isset($this->_joinAttributes[$fkName])) {
                    $fKey = $fkTable . '.' . $fkAttribute->getAttributeCode();
                } else {
                    $fKey = 'e.' . $fkAttribute->getAttributeCode();
                }
            } else {
                $this->_addAttributeJoin($fkAttribute->getAttributeCode(), $joinType);
                $fKey = $fkTable . '.value';
            }
            $pKey = $attrTable . '.' . $this->_joinAttributes[$attributeCode]['filter'];
        } else {
            $entity = $this->getEntity();
            $fKey = 'e.' . $this->getEntityPkName($entity);
            $pKey = $attrTable . '.' . $this->getEntityPkName($entity);
            $attribute = $entity->getAttribute($attributeCode);
        }

        if (!$attribute) {
            throw new LocalizedException(
                __('The "%1" attribute name is invalid. Reset the name and try again.', $attributeCode)
            );
        }

        if ($attribute->getBackend()->isStatic()) {
            $attrFieldName = $attrTable . '.' . $attribute->getAttributeCode();
        } else {
            $attrFieldName = $attrTable . '.value';
        }

        $fKey = $connection->quoteColumnAs($fKey, null);
        $pKey = $connection->quoteColumnAs($pKey, null);

        $condArr = ["{$pKey} = {$fKey}"];
        if (!$attribute->getBackend()->isStatic()) {
            $condArr[] = $this->getConnection()->quoteInto(
                $connection->quoteColumnAs("{$attrTable}.attribute_id", null) . ' = ?',
                $attribute->getId()
            );
        }

        /**
         * process join type
         */
        $joinMethod = $joinType == 'left' ? 'joinLeft' : 'join';

        $this->_joinAttributeToSelect($joinMethod, $attribute, $attrTable, $condArr, $attributeCode, $attrFieldName);

        $this->removeAttributeToSelect($attributeCode);
        $this->_filterAttributes[$attributeCode] = $attribute->getId();

        /**
         * Fix double join for using same as filter
         */
        $this->_joinFields[$attributeCode] = ['table' => '', 'field' => $attrFieldName];

        return $this;
    }

    /**
     * Retrieve Entity Primary Key
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity
     * @return string
     * @since 100.1.0
     */
    protected function getEntityPkName(\Magento\Eav\Model\Entity\AbstractEntity $entity)
    {
        return $entity->getEntityIdField();
    }

    /**
     * Adding join statement to collection select instance
     *
     * @param   string $method
     * @param   object $attribute
     * @param   string $tableAlias
     * @param   array $condition
     * @param   string $fieldCode
     * @param   string $fieldAlias
     * @return $this
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        $this->getSelect()->{$method}(
            [$tableAlias => $attribute->getBackend()->getTable()],
            '(' . implode(') AND (', $condition) . ')',
            [$fieldCode => $fieldAlias]
        );
        return $this;
    }

    /**
     * Get condition sql for the attribute
     *
     * @param string $attribute
     * @param mixed $condition
     * @param string $joinType
     * @return string
     *
     * @see self::_getConditionSql
     */
    protected function _getAttributeConditionSql($attribute, $condition, $joinType = 'inner')
    {
        if (isset($this->_joinFields[$attribute])) {
            return $this->_getConditionSql($this->_getAttributeFieldName($attribute), $condition);
        }
        if (isset($this->_staticFields[$attribute])) {
            return $this->_getConditionSql($this->getConnection()->quoteIdentifier('e.' . $attribute), $condition);
        }
        // process linked attribute
        if (isset($this->_joinAttributes[$attribute])) {
            $entity = $this->getAttribute($attribute)->getEntity();
        } else {
            $entity = $this->getEntity();
        }

        if ($entity->isAttributeStatic($attribute)) {
            $conditionSql = $this->_getConditionSql(
                $this->getConnection()->quoteIdentifier('e.' . $attribute),
                $condition
            );
        } else {
            if (isset($condition['null'])) {
                $joinType = 'left';
            }

            $this->_addAttributeJoin($attribute, $joinType);
            if (isset($this->_joinAttributes[$attribute]['condition_alias'])) {
                $field = $this->_joinAttributes[$attribute]['condition_alias'];
            } else {
                $field = $this->_getAttributeTableAlias($attribute) . '.value';
            }
            $conditionSql = $this->_getConditionSql($field, $condition);
        }

        return $conditionSql;
    }

    /**
     * Set sorting order
     *
     * Parameter $attribute can also be an array of attributes
     *
     * @param string|array $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_ASC)
    {
        if (is_array($attribute)) {
            foreach ($attribute as $attr) {
                parent::setOrder($attr, $dir);
            }
            return $this;
        }
        return parent::setOrder($attribute, $dir);
    }

    /**
     * Retrieve array of attributes
     *
     * @param array $arrAttributes
     * @return array
     */
    public function toArray($arrAttributes = [])
    {
        $arr = [];
        foreach ($this->getItems() as $key => $item) {
            $arr[$key] = $item->toArray($arrAttributes);
        }
        return $arr;
    }

    /**
     * Treat "order by" items as attributes to sort
     *
     * @return $this
     */
    protected function _renderOrders()
    {
        if (!$this->_isOrdersRendered) {
            foreach ($this->_orders as $attribute => $direction) {
                $this->addAttributeToSort($attribute, $direction);
            }
            $this->_isOrdersRendered = true;
        }
        return $this;
    }

    /**
     * After load method
     *
     * @return $this
     * @codeCoverageIgnore
     */
    protected function _afterLoad()
    {
        return $this;
    }

    /**
     * Reset collection
     *
     * @return $this
     */
    protected function _reset()
    {
        parent::_reset();

        $this->_selectEntityTypes = [];
        $this->_selectAttributes = [];
        $this->_filterAttributes = [];
        $this->_joinEntities = [];
        $this->_joinAttributes = [];
        $this->_joinFields = [];

        return $this;
    }

    /**
     * Check whether attribute with code is already added to collection
     *
     * @param string $attributeCode
     * @return bool
     */
    public function isAttributeAdded($attributeCode) : bool
    {
        return isset($this->_selectAttributes[$attributeCode]);
    }

    /**
     * Returns already loaded element ids
     *
     * @return array
     * @codeCoverageIgnore
     */
    public function getLoadedIds()
    {
        return array_keys($this->_items);
    }

    /**
     * Clear collection
     * @return $this
     */
    public function clear()
    {
        $this->_itemsById = [];
        return parent::clear();
    }

    /**
     * Remove all items from collection
     * @return $this
     */
    public function removeAllItems()
    {
        $this->_itemsById = [];
        return parent::removeAllItems();
    }

    /**
     * Remove item from collection by item key
     *
     * @param mixed $key
     * @return $this
     */
    public function removeItemByKey($key)
    {
        if (isset($this->_items[$key])) {
            unset($this->_itemsById[$this->_items[$key]->getId()]);
        }
        return parent::removeItemByKey($key);
    }

    /**
     * Returns main table name - extracted from "module/table" style and
     * validated by db adapter
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->getSelect()->getPart(Select::FROM)['e']['tableName'];
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param string $field
     * @param string $alias
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function addFieldToSelect($field, $alias = null)
    {
        return $this->addAttributeToSelect($field);
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @param string $field
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     * @codeCoverageIgnore
     */
    public function removeFieldFromSelect($field)
    {
        return $this->removeAttributeToSelect($field);
    }

    /**
     * Wrapper for compatibility with \Magento\Framework\Data\Collection\AbstractDb
     *
     * @return $this|\Magento\Framework\Data\Collection\AbstractDb
     * @codeCoverageIgnore
     */
    public function removeAllFieldsFromSelect()
    {
        return $this->removeAttributeToSelect();
    }
}
