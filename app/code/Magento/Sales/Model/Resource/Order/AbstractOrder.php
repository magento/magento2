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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Sales\Model\Resource\Order;

/**
 * Flat sales resource abstract
 */
abstract class AbstractOrder extends \Magento\Sales\Model\Resource\AbstractResource
{
    /**
     * Is grid available
     *
     * @var bool
     */
    protected $_grid = false;

    /**
     * Use additional is object new check for this resource
     *
     * @var bool
     */
    protected $_useIsObjectNew = true;

    /**
     * Flag for using of increment id
     *
     * @var bool
     */
    protected $_useIncrementId = false;

    /**
     * Entity code for increment id (Eav entity code)
     *
     * @var string
     */
    protected $_entityTypeForIncrementId = '';

    /**
     * Grid virtual columns
     *
     * @var array|null
     */
    protected $_virtualGridColumns = null;

    /**
     * Grid columns
     *
     * @var array|null
     */
    protected $_gridColumns = null;

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'sales_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * Core event manager proxy
     *
     * @var \Magento\Event\ManagerInterface
     */
    protected $_eventManager = null;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_eavEntityTypeFactory;

    /**
     * @param \Magento\App\Resource $resource
     * @param \Magento\Stdlib\DateTime $dateTime
     * @param \Magento\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Entity\TypeFactory $eavEntityTypeFactory
     */
    public function __construct(
        \Magento\App\Resource $resource,
        \Magento\Stdlib\DateTime $dateTime,
        \Magento\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Entity\TypeFactory $eavEntityTypeFactory
    ) {
        $this->_eventManager = $eventManager;
        $this->_eavEntityTypeFactory = $eavEntityTypeFactory;
        parent::__construct($resource, $dateTime);
    }

    /**
     * Add new virtual grid column
     *
     * @param string $alias
     * @param string $table
     * @param array $joinCondition
     * @param string $column
     * @return $this
     * @throws \Magento\Model\Exception
     */
    public function addVirtualGridColumn($alias, $table, $joinCondition, $column)
    {
        $table = $this->getTable($table);

        if (!in_array($alias, $this->getGridColumns())) {
            throw new \Magento\Model\Exception(
                __('Please specify a valid grid column alias name that exists in the grid table.')
            );
        }

        $this->_virtualGridColumns[$alias] = array($table, $joinCondition, $column);

        return $this;
    }

    /**
     * Retrieve virtual grid columns
     *
     * @return array
     */
    public function getVirtualGridColumns()
    {
        if ($this->_virtualGridColumns === null) {
            $this->_initVirtualGridColumns();
        }

        return $this->_virtualGridColumns;
    }

    /**
     * Init virtual grid records for entity
     *
     * @return $this
     */
    protected function _initVirtualGridColumns()
    {
        $this->_virtualGridColumns = array();
        if ($this->_eventPrefix && $this->_eventObject) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_init_virtual_grid_columns',
                array($this->_eventObject => $this)
            );
        }
        return $this;
    }

    /**
     * Update records in grid table
     *
     * @param array|int $ids
     * @return $this
     */
    public function updateGridRecords($ids)
    {
        if ($this->_grid) {
            if (!is_array($ids)) {
                $ids = array($ids);
            }

            if ($this->_eventPrefix && $this->_eventObject) {
                $proxy = new \Magento\Object();
                $proxy->setIds($ids)->setData($this->_eventObject, $this);

                $this->_eventManager->dispatch($this->_eventPrefix . '_update_grid_records', array('proxy' => $proxy));
                $ids = $proxy->getIds();
            }

            if (empty($ids)) {
                // If nothing to update
                return $this;
            }
            $columnsToSelect = array();
            $table = $this->getGridTable();
            $select = $this->getUpdateGridRecordsSelect($ids, $columnsToSelect);
            $this->_getWriteAdapter()->query($select->insertFromSelect($table, $columnsToSelect, true));
        }

        return $this;
    }

    /**
     * Retrieve update grid records select
     *
     * @param array $ids
     * @param array &$flatColumnsToSelect
     * @param array|null $gridColumns
     * @return \Magento\DB\Select
     */
    public function getUpdateGridRecordsSelect($ids, &$flatColumnsToSelect, $gridColumns = null)
    {
        $flatColumns = array_keys($this->_getReadAdapter()->describeTable($this->getMainTable()));

        if ($gridColumns === null) {
            $gridColumns = $this->getGridColumns();
        }

        $flatColumnsToSelect = array_intersect($flatColumns, $gridColumns);

        $select = $this->_getWriteAdapter()->select()->from(
            array('main_table' => $this->getMainTable()),
            $flatColumnsToSelect
        )->where(
            'main_table.' . $this->getIdFieldName() . ' IN(?)',
            $ids
        );

        $this->joinVirtualGridColumnsToSelect('main_table', $select, $flatColumnsToSelect);

        return $select;
    }

    /**
     * Join virtual grid columns to select
     *
     * @param string $mainTableAlias
     * @param \Zend_Db_Select $select
     * @param array &$columnsToSelect
     * @return $this
     */
    public function joinVirtualGridColumnsToSelect($mainTableAlias, \Zend_Db_Select $select, &$columnsToSelect)
    {
        $adapter = $this->_getWriteAdapter();
        foreach ($this->getVirtualGridColumns() as $alias => $expression) {
            list($table, $joinCondition, $column) = $expression;
            $tableAlias = 'table_' . $alias;

            $joinConditionExpr = array();
            foreach ($joinCondition as $fkField => $pkField) {
                $pkField = $adapter->quoteIdentifier($tableAlias . '.' . $pkField);
                $fkField = $adapter->quoteIdentifier($mainTableAlias . '.' . $fkField);
                $joinConditionExpr[] = $fkField . '=' . $pkField;
            }

            $select->joinLeft(
                array($tableAlias => $table),
                implode(' AND ', $joinConditionExpr),
                array($alias => str_replace('{{table}}', $tableAlias, $column))
            );

            $columnsToSelect[] = $alias;
        }

        return $this;
    }

    /**
     * Retrieve list of grid columns
     *
     * @return array
     */
    public function getGridColumns()
    {
        if ($this->_gridColumns === null) {
            if ($this->_grid) {
                $this->_gridColumns = array_keys($this->_getReadAdapter()->describeTable($this->getGridTable()));
            } else {
                $this->_gridColumns = array();
            }
        }

        return $this->_gridColumns;
    }

    /**
     * Retrieve grid table
     *
     * @return string
     */
    public function getGridTable()
    {
        if ($this->_grid) {
            return $this->getTable($this->_mainTable . '_grid');
        }
        return false;
    }

    /**
     * Before save object attribute
     *
     * @param \Magento\Model\AbstractModel $object
     * @param string $attribute
     * @return $this
     */
    protected function _beforeSaveAttribute(\Magento\Model\AbstractModel $object, $attribute)
    {
        if ($this->_eventObject && $this->_eventPrefix) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_save_attribute_before',
                array($this->_eventObject => $this, 'object' => $object, 'attribute' => $attribute)
            );
        }
        return $this;
    }

    /**
     * After save object attribute
     *
     * @param \Magento\Model\AbstractModel $object
     * @param string $attribute
     * @return $this
     */
    protected function _afterSaveAttribute(\Magento\Model\AbstractModel $object, $attribute)
    {
        if ($this->_eventObject && $this->_eventPrefix) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_save_attribute_after',
                array($this->_eventObject => $this, 'object' => $object, 'attribute' => $attribute)
            );
        }
        return $this;
    }

    /**
     * Perform actions after object save
     *
     * @param \Magento\Model\AbstractModel $object
     * @param string $attribute
     * @return $this
     * @throws \Exception
     */
    public function saveAttribute(\Magento\Model\AbstractModel $object, $attribute)
    {
        if ($attribute instanceof \Magento\Eav\Model\Entity\Attribute\AbstractAttribute) {
            $attribute = $attribute->getAttributeCode();
        }

        if (is_string($attribute)) {
            $attribute = array($attribute);
        }

        if (is_array($attribute) && !empty($attribute)) {
            $this->beginTransaction();
            try {
                $this->_beforeSaveAttribute($object, $attribute);
                $data = new \Magento\Object();
                foreach ($attribute as $code) {
                    $data->setData($code, $object->getData($code));
                }

                $updateArray = $this->_prepareDataForTable($data, $this->getMainTable());
                $this->_postSaveFieldsUpdate($object, $updateArray);
                if (!$object->getForceUpdateGridRecords() && count(
                    array_intersect($this->getGridColumns(), $attribute)
                ) > 0
                ) {
                    $this->updateGridRecords($object->getId());
                }
                $this->_afterSaveAttribute($object, $attribute);
                $this->commit();
            } catch (\Exception $e) {
                $this->rollBack();
                throw $e;
            }
        }

        return $this;
    }

    /**
     * Perform actions before object save
     *
     * @param \Magento\Model\AbstractModel|\Magento\Object $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Model\AbstractModel $object)
    {
        if ($this->_useIncrementId && !$object->getIncrementId()) {
            /* @var $entityType \Magento\Eav\Model\Entity\Type */
            $entityType = $this->_eavEntityTypeFactory->create()->loadByCode($this->_entityTypeForIncrementId);
            $object->setIncrementId($entityType->fetchNewIncrementId($object->getStoreId()));
        }
        parent::_beforeSave($object);
        return $this;
    }

    /**
     * Update field in table if model have been already saved
     *
     * @param \Magento\Model\AbstractModel $object
     * @param array $data
     * @return $this
     */
    protected function _postSaveFieldsUpdate($object, $data)
    {
        if ($object->getId() && !empty($data)) {
            $table = $this->getMainTable();
            $this->_getWriteAdapter()->update(
                $table,
                $data,
                array($this->getIdFieldName() . '=?' => (int)$object->getId())
            );
            $object->addData($data);
        }

        return $this;
    }

    /**
     * Set main resource table
     *
     * @param string $table
     * @return $this
     */
    public function setMainTable($table)
    {
        $this->_mainTable = $table;
        return $this;
    }

    /**
     * Save object data
     *
     * @param \Magento\Model\AbstractModel $object
     * @return $this
     */
    public function save(\Magento\Model\AbstractModel $object)
    {
        if (!$object->getForceObjectSave()) {
            parent::save($object);
        }

        return $this;
    }

    /**
     * Update grid table on entity update
     *
     * @param string $field
     * @param int $entityId
     * @return $this
     */
    public function updateOnRelatedRecordChanged($field, $entityId)
    {
        $adapter = $this->_getWriteAdapter();
        $column = array();
        $select = $adapter->select()->from(
            array('main_table' => $this->getMainTable()),
            $column
        )->where(
            'main_table.' . $field . ' = ?',
            $entityId
        );
        $this->joinVirtualGridColumnsToSelect('main_table', $select, $column);
        $fieldsToUpdate = $adapter->fetchRow($select);
        if ($fieldsToUpdate) {
            $adapter->update(
                $this->getGridTable(),
                $fieldsToUpdate,
                $adapter->quoteInto($this->getGridTable() . '.' . $field . ' = ?', $entityId)
            );
        }
        return $this;
    }
}
