<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db\Collection;

use \Magento\Framework\App\ResourceConnection\SourceProviderInterface;
use \Magento\Framework\Data\Collection\AbstractDb;

/**
 * Abstract Resource Collection
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractCollection extends AbstractDb implements SourceProviderInterface
{
    /**
     * Model name
     *
     * @var string
     */
    protected $_model;

    /**
     * Resource model name
     *
     * @var string
     */
    protected $_resourceModel;

    /**
     * Resource instance
     *
     * @var \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected $_resource;

    /**
     * Fields to select in query
     *
     * @var array|null
     */
    protected $_fieldsToSelect = null;

    /**
     * Expression fields to select in query.
     *
     * @var array
     */
    private $expressionFieldsToSelect = [];

    /**
     * Fields initial fields to select like id_field
     *
     * @var array|null
     */
    protected $_initialFieldsToSelect = null;

    /**
     * Fields to select changed flag
     *
     * @var boolean
     */
    protected $_fieldsToSelectChanged = false;

    /**
     * Store joined tables here
     *
     * @var array
     */
    protected $_joinedTables = [];

    /**
     * Collection main table
     *
     * @var string
     */
    protected $_mainTable = null;

    /**
     * Reset items data changed flag
     *
     * @var boolean
     */
    protected $_resetItemsDataChanged = false;

    /**
     * Name prefix of events that are dispatched by model
     *
     * @var string
     */
    protected $_eventPrefix = '';

    /**
     * Name of event parameter
     *
     * @var string
     */
    protected $_eventObject = '';

    /**
     * Event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_eventManager = $eventManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $connection);
        $this->_construct();
        $this->_resource = $resource;
        $this->setConnection($this->getResource()->getConnection());
        $this->_initSelect();
    }

    /**
     * Initialization here
     *
     * @return void
     */
    protected function _construct()
    {
    }

    /**
     * Retrieve main table
     *
     * @return string
     */
    public function getMainTable()
    {
        if ($this->_mainTable === null) {
            $this->setMainTable($this->getResource()->getMainTable());
        }

        return $this->_mainTable;
    }

    /**
     * Set main collection table
     *
     * @param string $table
     * @return $this
     */
    public function setMainTable($table)
    {
        $table = $this->getTable($table);
        if ($this->_mainTable !== null && $table !== $this->_mainTable && $this->getSelect() !== null) {
            $from = $this->getSelect()->getPart(\Magento\Framework\DB\Select::FROM);
            if (isset($from['main_table'])) {
                $from['main_table']['tableName'] = $table;
            }
            $this->getSelect()->setPart(\Magento\Framework\DB\Select::FROM, $from);
        }

        $this->_mainTable = $table;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->getSelect()->from(['main_table' => $this->getMainTable()]);
        return $this;
    }

    /**
     * Get \Magento\Framework\DB\Select instance and applies fields to select if needed
     *
     * @return \Magento\Framework\DB\Select
     */
    public function getSelect()
    {
        if ($this->_select && $this->_fieldsToSelectChanged) {
            $this->_fieldsToSelectChanged = false;
            $this->_initSelectFields();
        }
        return parent::getSelect();
    }

    /**
     * Init fields for select
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _initSelectFields()
    {
        $columns = $this->_select->getPart(\Magento\Framework\DB\Select::COLUMNS);
        $columnsToSelect = [];
        foreach ($columns as $columnEntry) {
            list($correlationName, $column, $alias) = $columnEntry;
            if ($correlationName !== 'main_table' || isset($this->expressionFieldsToSelect[$alias])) {
                // Add joined fields to select
                if ($column instanceof \Zend_Db_Expr) {
                    $column = $column->__toString();
                }
                $key = $alias !== null ? $alias : $column;
                $columnsToSelect[$key] = $columnEntry;
            }
        }

        $columns = $columnsToSelect;

        $columnsToSelect = array_keys($columnsToSelect);

        if ($this->_fieldsToSelect !== null) {
            $insertIndex = 0;
            foreach ($this->_fieldsToSelect as $alias => $field) {
                if (!is_string($alias)) {
                    $alias = null;
                }

                if ($field instanceof \Zend_Db_Expr) {
                    $column = $field->__toString();
                } else {
                    $column = $field;
                }

                if ($alias !== null &&
                    in_array($alias, $columnsToSelect) ||
                    // If field already joined from another table
                    $alias === null &&
                    isset($alias, $columnsToSelect)
                ) {
                    continue;
                }

                $columnEntry = ['main_table', $field, $alias];
                array_splice($columns, $insertIndex, 0, [$columnEntry]);
                // Insert column
                $insertIndex++;
            }
        } else {
            array_unshift($columns, ['main_table', '*', null]);
        }

        $this->_select->setPart(\Magento\Framework\DB\Select::COLUMNS, $columns);

        return $this;
    }

    /**
     * Retrieve initial fields to select like id field
     *
     * @return array
     */
    protected function _getInitialFieldsToSelect()
    {
        if ($this->_initialFieldsToSelect === null) {
            $this->_initialFieldsToSelect = [];
            $this->_initInitialFieldsToSelect();
        }

        return $this->_initialFieldsToSelect;
    }

    /**
     * Initialize initial fields to select like id field
     *
     * @return $this
     */
    protected function _initInitialFieldsToSelect()
    {
        $idFieldName = $this->getResource()->getIdFieldName();
        if ($idFieldName) {
            $this->_initialFieldsToSelect[] = $idFieldName;
        }
        return $this;
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return $this
     */
    public function addFieldToSelect($field, $alias = null)
    {
        if ($field === '*') {
            // If we will select all fields
            $this->_fieldsToSelect = null;
            $this->_fieldsToSelectChanged = true;
            return $this;
        }

        if (is_array($field)) {
            if ($this->_fieldsToSelect === null) {
                $this->_fieldsToSelect = $this->_getInitialFieldsToSelect();
            }

            foreach ($field as $key => $value) {
                $this->addFieldToSelect($value, is_string($key) ? $key : null);
            }

            $this->_fieldsToSelectChanged = true;
            return $this;
        }

        if ($alias === null) {
            $this->_fieldsToSelect[] = $field;
        } else {
            $this->_fieldsToSelect[$alias] = $field;
        }

        $this->_fieldsToSelectChanged = true;
        return $this;
    }

    /**
     * Add attribute expression (SUM, COUNT, etc)
     * Example: ('sub_total', 'SUM({{attribute}})', 'revenue')
     * Example: ('sub_total', 'SUM({{revenue}})', 'revenue')
     * For some functions like SUM use groupByAttribute.
     *
     * @param string $alias
     * @param string $expression
     * @param array|string $fields
     * @return $this
     */
    public function addExpressionFieldToSelect($alias, $expression, $fields)
    {
        // validate alias
        if (!is_array($fields)) {
            $fields = [$fields => $fields];
        }

        $fullExpression = $expression;
        foreach ($fields as $fieldKey => $fieldItem) {
            $fullExpression = str_replace('{{' . $fieldKey . '}}', $fieldItem, $fullExpression);
        }

        $this->getSelect()->columns([$alias => $fullExpression]);
        $this->expressionFieldsToSelect[$alias] = $fullExpression;

        return $this;
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return $this
     */
    public function removeFieldFromSelect($field, $isAlias = false)
    {
        if ($isAlias) {
            if (isset($this->_fieldsToSelect[$field])) {
                unset($this->_fieldsToSelect[$field]);
                $this->_fieldsToSelectChanged = true;
            }
        } else {
            foreach ($this->_fieldsToSelect as $key => $value) {
                if ($value === $field) {
                    unset($this->_fieldsToSelect[$key]);
                    $this->_fieldsToSelectChanged = true;
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Removes all fields from select
     *
     * @return $this
     */
    public function removeAllFieldsFromSelect()
    {
        $this->_fieldsToSelect = $this->_getInitialFieldsToSelect();
        $this->_fieldsToSelectChanged = true;
        return $this;
    }

    /**
     * Standard resource collection initialization
     *
     * @param string $model
     * @param string $resourceModel
     * @return $this
     */
    protected function _init($model, $resourceModel)
    {
        $this->setModel($model);
        $this->setResourceModel($resourceModel);
        return $this;
    }

    /**
     * Set model name for collection items
     *
     * @param string $model
     * @return $this
     */
    public function setModel($model)
    {
        if (is_string($model)) {
            $this->_model = $model;
            $this->setItemObjectClass($model);
        }
        return $this;
    }

    /**
     * Get model instance
     *
     * @return string
     */
    public function getModelName()
    {
        return $this->_model;
    }

    /**
     * Set resource model name for collection items
     *
     * @param string $model
     * @return void
     */
    public function setResourceModel($model)
    {
        $this->_resourceModel = $model;
    }

    /**
     *  Retrieve resource model name
     *
     * @return string
     */
    public function getResourceModelName()
    {
        return $this->_resourceModel;
    }

    /**
     * Get resource instance
     *
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    public function getResource()
    {
        if (empty($this->_resource)) {
            $this->_resource = \Magento\Framework\App\ObjectManager::getInstance()->create(
                $this->getResourceModelName()
            );
        }
        return $this->_resource;
    }

    /**
     * Retrieve table name
     *
     * @param string $table
     * @return string
     */
    public function getTable($table)
    {
        return $this->getResource()->getTable($table);
    }

    /**
     * Retrieve all ids for collection
     *
     * @return array
     */
    public function getAllIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(\Magento\Framework\DB\Select::ORDER);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $idsSelect->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $idsSelect->reset(\Magento\Framework\DB\Select::COLUMNS);

        $idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * Join table to collection select
     *
     * @param string|array $table
     * @param string $cond
     * @param string|array $cols
     * @return $this
     */
    public function join($table, $cond, $cols = '*')
    {
        if (is_array($table)) {
            foreach ($table as $k => $v) {
                $alias = $k;
                $table = $v;
                break;
            }
        } else {
            $alias = $table;
        }

        if (!isset($this->_joinedTables[$alias])) {
            $this->getSelect()->join([$alias => $this->getTable($table)], $cond, $cols);
            $this->_joinedTables[$alias] = true;
        }
        return $this;
    }

    /**
     * Redeclare before load method for adding event
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();
        $this->_eventManager->dispatch('core_collection_abstract_load_before', ['collection' => $this]);
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch($this->_eventPrefix . '_load_before', [$this->_eventObject => $this]);
        }
        return $this;
    }

    /**
     * Set reset items data changed flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setResetItemsDataChanged($flag)
    {
        $this->_resetItemsDataChanged = (bool)$flag;
        return $this;
    }

    /**
     * Set flag data has changed to all collection items
     *
     * @return $this
     */
    public function resetItemsDataChanged()
    {
        foreach ($this->_items as $item) {
            $item->setDataChanges(false);
        }

        return $this;
    }

    /**
     * Redeclare after load method for specifying collection items original data
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $item->setOrigData();
            if ($this->_resetItemsDataChanged && ($item instanceof \Magento\Framework\Model\AbstractModel)) {
                $item->setDataChanges(false);
            }
        }
        $this->_eventManager->dispatch('core_collection_abstract_load_after', ['collection' => $this]);
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch($this->_eventPrefix . '_load_after', [$this->_eventObject => $this]);
        }
        return $this;
    }

    /**
     * Save all the entities in the collection
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
     * @inheritdoc
     * @since 100.0.11
     */
    public function __sleep()
    {
        return array_diff(
            parent::__sleep(),
            ['_resource', '_eventManager']
        );
    }

    /**
     * @inheritdoc
     * @since 100.0.11
     */
    public function __wakeup()
    {
        parent::__wakeup();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_eventManager = $objectManager->get(\Magento\Framework\Event\ManagerInterface::class);
    }
}
