<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Data;

use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\Option\ArrayInterface;

/**
 * Data collection
 *
 * TODO: Refactor use of \Magento\Framework\Option\ArrayInterface in library.
 *
 * @api
 * @since 100.0.2
 */
class Collection implements \IteratorAggregate, \Countable, ArrayInterface, CollectionDataSourceInterface
{
    const SORT_ORDER_ASC = 'ASC';

    const SORT_ORDER_DESC = 'DESC';

    /**
     * Collection items
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $_items = [];

    /**
     * Item object class name
     *
     * @var string
     */
    protected $_itemObjectClass = \Magento\Framework\DataObject::class;

    /**
     * Order configuration
     *
     * @var array
     */
    protected $_orders = [];

    /**
     * Filters configuration
     *
     * @var \Magento\Framework\DataObject[]
     */
    protected $_filters = [];

    /**
     * Filter rendered flag
     *
     * @var bool
     */
    protected $_isFiltersRendered = false;

    /**
     * Current page number for items pager
     *
     * @var int
     */
    protected $_curPage = 1;

    /**
     * Pager page size
     *
     * if page size is false, then we works with all items
     *
     * @var int|false
     */
    protected $_pageSize = false;

    /**
     * Total items number
     *
     * @var int
     */
    protected $_totalRecords;

    /**
     * Loading state flag
     *
     * @var bool
     */
    protected $_isCollectionLoaded;

    /**
     * Additional collection flags
     *
     * @var array
     */
    protected $_flags = [];

    /**
     * @var EntityFactoryInterface
     */
    protected $_entityFactory;

    /**
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(EntityFactoryInterface $entityFactory)
    {
        $this->_entityFactory = $entityFactory;
    }

    /**
     * Add collection filter
     *
     * @param string $field
     * @param string $value
     * @param string $type and|or|string
     * @return $this
     */
    public function addFilter($field, $value, $type = 'and')
    {
        $filter = new \Magento\Framework\DataObject();
        // implements ArrayAccess
        $filter['field'] = $field;
        $filter['value'] = $value;
        $filter['type'] = strtolower($type);

        $this->_filters[] = $filter;
        $this->_isFiltersRendered = false;
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * If $condition integer or string - exact value will be filtered ('eq' condition)
     *
     * If $condition is array - one of the following structures is expected:
     * <pre>
     * - ["from" => $fromValue, "to" => $toValue]
     * - ["eq" => $equalValue]
     * - ["neq" => $notEqualValue]
     * - ["like" => $likeValue]
     * - ["in" => [$inValues]]
     * - ["nin" => [$notInValues]]
     * - ["notnull" => $valueIsNotNull]
     * - ["null" => $valueIsNull]
     * - ["moreq" => $moreOrEqualValue]
     * - ["gt" => $greaterValue]
     * - ["lt" => $lessValue]
     * - ["gteq" => $greaterOrEqualValue]
     * - ["lteq" => $lessOrEqualValue]
     * - ["finset" => $valueInSet]
     * </pre>
     *
     * If non matched - sequential parallel arrays are expected and OR conditions
     * will be built using above mentioned structure.
     *
     * Example:
     * <pre>
     * $field = ['age', 'name'];
     * $condition = [42, ['like' => 'Mage']];
     * </pre>
     * The above would find where age equal to 42 OR name like %Mage%.
     *
     * @param string|array $field
     * @param string|int|array $condition
     * @throws \Magento\Framework\Exception\LocalizedException if some error in the input could be detected.
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addFieldToFilter($field, $condition)
    {
        throw new \Magento\Framework\Exception\LocalizedException(new \Magento\Framework\Phrase('Not implemented'));
    }

    /**
     * Search for a filter by specified field
     *
     * Multiple filters can be matched if an array is specified:
     * - 'foo' -- get the first filter with field name 'foo'
     * - array('foo') -- get all filters with field name 'foo'
     * - array('foo', 'bar') -- get all filters with field name 'foo' or 'bar'
     * - array() -- get all filters
     *
     * @param string|string[] $field
     * @return \Magento\Framework\DataObject|\Magento\Framework\DataObject[]|void
     */
    public function getFilter($field)
    {
        if (is_array($field)) {
            // empty array: get all filters
            if (empty($field)) {
                return $this->_filters;
            }
            // non-empty array: collect all filters that match specified field names
            $result = [];
            foreach ($this->_filters as $filter) {
                if (in_array($filter['field'], $field)) {
                    $result[] = $filter;
                }
            }
            return $result;
        }

        // get a first filter by specified name
        foreach ($this->_filters as $filter) {
            if ($filter['field'] === $field) {
                return $filter;
            }
        }
    }

    /**
     * Retrieve collection loading status
     *
     * @return bool
     */
    public function isLoaded()
    {
        return $this->_isCollectionLoaded;
    }

    /**
     * Set collection loading status flag
     *
     * @param bool $flag
     * @return $this
     */
    protected function _setIsLoaded($flag = true)
    {
        $this->_isCollectionLoaded = $flag;
        return $this;
    }

    /**
     * Get current collection page
     *
     * @param  int $displacement
     * @return int
     */
    public function getCurPage($displacement = 0)
    {
        if ($this->_curPage + $displacement < 1) {
            return 1;
        } elseif ($this->_curPage + $displacement > $this->getLastPageNumber()) {
            return $this->getLastPageNumber();
        } else {
            return $this->_curPage + $displacement;
        }
    }

    /**
     * Retrieve collection last page number
     *
     * @return int
     */
    public function getLastPageNumber()
    {
        $collectionSize = (int)$this->getSize();
        if (0 === $collectionSize) {
            return 1;
        } elseif ($this->_pageSize) {
            return (int)ceil($collectionSize / $this->_pageSize);
        } else {
            return 1;
        }
    }

    /**
     * Retrieve collection page size
     *
     * @return int
     */
    public function getPageSize()
    {
        return $this->_pageSize;
    }

    /**
     * Retrieve collection all items count
     *
     * @return int
     */
    public function getSize()
    {
        $this->load();
        if ($this->_totalRecords === null) {
            $this->_totalRecords = count($this->getItems());
        }
        return (int)$this->_totalRecords;
    }

    /**
     * Retrieve collection first item
     *
     * @return \Magento\Framework\DataObject
     */
    public function getFirstItem()
    {
        $this->load();

        if (count($this->_items)) {
            reset($this->_items);
            return current($this->_items);
        }

        return $this->_entityFactory->create($this->_itemObjectClass);
    }

    /**
     * Retrieve collection last item
     *
     * @return \Magento\Framework\DataObject
     */
    public function getLastItem()
    {
        $this->load();

        if (count($this->_items)) {
            return end($this->_items);
        }

        return $this->_entityFactory->create($this->_itemObjectClass);
    }

    /**
     * Retrieve collection items
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getItems()
    {
        $this->load();
        return $this->_items;
    }

    /**
     * Retrieve field values from all items
     *
     * @param   string $colName
     * @return  array
     */
    public function getColumnValues($colName)
    {
        $this->load();

        $col = [];
        foreach ($this->getItems() as $item) {
            $col[] = $item->getData($colName);
        }
        return $col;
    }

    /**
     * Search all items by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  array
     */
    public function getItemsByColumnValue($column, $value)
    {
        $this->load();

        $res = [];
        foreach ($this as $item) {
            if ($item->getData($column) == $value) {
                $res[] = $item;
            }
        }
        return $res;
    }

    /**
     * Search first item by field value
     *
     * @param   string $column
     * @param   mixed $value
     * @return  \Magento\Framework\DataObject || null
     */
    public function getItemByColumnValue($column, $value)
    {
        $this->load();

        foreach ($this as $item) {
            if ($item->getData($column) == $value) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Adding item to item array
     *
     * @param \Magento\Framework\DataObject $item
     * @return $this
     * @throws \Exception
     */
    public function addItem(\Magento\Framework\DataObject $item)
    {
        $itemId = $this->_getItemId($item);

        if ($itemId !== null) {
            if (isset($this->_items[$itemId])) {
                //phpcs:ignore Magento2.Exceptions.DirectThrow
                throw new \Exception(
                    'Item (' . get_class($item) . ') with the same ID "' . $item->getId() . '" already exists.'
                );
            }
            $this->_items[$itemId] = $item;
        } else {
            $this->_addItem($item);
        }
        return $this;
    }

    /**
     * Add item that has no id to collection
     *
     * @param \Magento\Framework\DataObject $item
     * @return $this
     */
    protected function _addItem($item)
    {
        $this->_items[] = $item;
        return $this;
    }

    /**
     * Retrieve item id
     *
     * @param \Magento\Framework\DataObject $item
     * @return mixed
     */
    protected function _getItemId(\Magento\Framework\DataObject $item)
    {
        return $item->getId();
    }

    /**
     * Retrieve ids of all items
     *
     * @return array
     */
    public function getAllIds()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $this->_getItemId($item);
        }
        return $ids;
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
            unset($this->_items[$key]);
        }
        return $this;
    }

    /**
     * Remove all items from collection
     *
     * @return $this
     */
    public function removeAllItems()
    {
        $this->_items = [];
        return $this;
    }

    /**
     * Clear collection
     *
     * @return $this
     */
    public function clear()
    {
        $this->_setIsLoaded(false);
        $this->_items = [];
        $this->_totalRecords = null;
        return $this;
    }

    /**
     * Walk through the collection and run model method or external callback with optional arguments
     *
     * Returns array with results of callback for each item
     *
     * @param callable $callback
     * @param array $args
     * @return array
     */
    public function walk($callback, array $args = [])
    {
        $results = [];
        $useItemCallback = is_string($callback) && strpos($callback, '::') === false;
        foreach ($this->getItems() as $id => $item) {
            $params = $args;
            if ($useItemCallback) {
                $cb = [$item, $callback];
            } else {
                $cb = $callback;
                array_unshift($params, $item);
            }
            $results[$id] = call_user_func_array($cb, $params);
        }
        return $results;
    }

    /**
     * Call method or callback on each item in the collection.
     *
     * @param string|array|\Closure $objMethod
     * @param array $args
     * @return void
     */
    public function each($objMethod, $args = [])
    {
        if ($objMethod instanceof \Closure) {
            foreach ($this->getItems() as $item) {
                $objMethod($item, ...$args);
            }
        } elseif (is_array($objMethod)) {
            foreach ($this->getItems() as $item) {
                call_user_func($objMethod, $item, ...$args);
            }
        } else {
            foreach ($this->getItems() as $item) {
                $item->$objMethod(...$args);
            }
        }
    }

    /**
     * Setting data for all collection items
     *
     * @param mixed $key
     * @param mixed $value
     * @return $this
     */
    public function setDataToAll($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setDataToAll($k, $v);
            }
            return $this;
        }
        foreach ($this->getItems() as $item) {
            $item->setData($key, $value);
        }
        return $this;
    }

    /**
     * Set current page
     *
     * @param int $page
     * @return $this
     */
    public function setCurPage($page)
    {
        $this->_curPage = $page;
        return $this;
    }

    /**
     * Set collection page size
     *
     * @param int $size
     * @return $this
     */
    public function setPageSize($size)
    {
        $this->_pageSize = $size;
        return $this;
    }

    /**
     * Set select order
     *
     * @param string $field
     * @param string $direction
     * @return $this
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        $this->_orders[$field] = $direction;
        return $this;
    }

    /**
     * Set collection item class name
     *
     * @param string $className
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setItemObjectClass($className)
    {
        if (!is_a($className, \Magento\Framework\DataObject::class, true)) {
            throw new \InvalidArgumentException($className . ' does not extend \Magento\Framework\DataObject');
        }
        $this->_itemObjectClass = $className;
        return $this;
    }

    /**
     * Retrieve collection empty item
     *
     * @return \Magento\Framework\DataObject
     */
    public function getNewEmptyItem()
    {
        return $this->_entityFactory->create($this->_itemObjectClass);
    }

    /**
     * Render sql select conditions
     *
     * @return $this
     */
    protected function _renderFilters()
    {
        return $this;
    }

    /**
     * Render sql select orders
     *
     * @return $this
     */
    protected function _renderOrders()
    {
        return $this;
    }

    /**
     * Render sql select limit
     *
     * @return $this
     */
    protected function _renderLimit()
    {
        return $this;
    }

    /**
     * Set select distinct
     *
     * @param bool $flag
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function distinct($flag)
    {
        return $this;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        return $this;
    }

    /**
     * Load data
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function load($printQuery = false, $logQuery = false)
    {
        return $this->loadData($printQuery, $logQuery);
    }

    /**
     * Load data with filter in place
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return $this
     */
    public function loadWithFilter($printQuery = false, $logQuery = false)
    {
        return $this->loadData($printQuery, $logQuery);
    }

    /**
     * Convert collection to XML
     *
     * @return string
     */
    public function toXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <collection>
           <totalRecords>' .
            $this->_totalRecords .
            '</totalRecords>
           <items>';

        foreach ($this as $item) {
            $xml .= $item->toXml();
        }
        $xml .= '</items>
        </collection>';
        return $xml;
    }

    /**
     * Convert collection to array
     *
     * @param array $arrRequiredFields
     * @return array
     */
    public function toArray($arrRequiredFields = [])
    {
        $arrItems = [];
        $arrItems['totalRecords'] = $this->getSize();

        $arrItems['items'] = [];
        foreach ($this as $item) {
            $arrItems['items'][] = $item->toArray($arrRequiredFields);
        }
        return $arrItems;
    }

    /**
     * Convert items array to array for select options
     *
     * Return items array
     * array(
     *      $index => array(
     *          'value' => mixed
     *          'label' => mixed
     *      )
     * )
     *
     * @param string $valueField
     * @param string $labelField
     * @param array $additional
     * @return array
     */
    protected function _toOptionArray($valueField = 'id', $labelField = 'name', $additional = [])
    {
        $res = [];
        $additional['value'] = $valueField;
        $additional['label'] = $labelField;

        foreach ($this as $item) {
            foreach ($additional as $code => $field) {
                $data[$code] = $item->getData($field);
            }
            $res[] = $data;
        }
        return $res;
    }

    /**
     * Returns option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray();
    }

    /**
     * Returns options hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash();
    }

    /**
     * Convert items array to hash for select options
     *
     * Return items hash
     * array($value => $label)
     *
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    protected function _toOptionHash($valueField = 'id', $labelField = 'name')
    {
        $res = [];
        foreach ($this as $item) {
            $res[$item->getData($valueField)] = $item->getData($labelField);
        }
        return $res;
    }

    /**
     * Retrieve item by id
     *
     * @param mixed $idValue
     * @return \Magento\Framework\DataObject
     */
    public function getItemById($idValue)
    {
        $this->load();
        if (isset($this->_items[$idValue])) {
            return $this->_items[$idValue];
        }
        return null;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        $this->load();
        return new \ArrayIterator($this->_items);
    }

    /**
     * Retrieve count of collection loaded items
     *
     * @return int
     */
    public function count()
    {
        $this->load();
        return count($this->_items);
    }

    /**
     * Retrieve Flag
     *
     * @param string $flag
     * @return bool|null
     */
    public function getFlag($flag)
    {
        return $this->_flags[$flag] ?? null;
    }

    /**
     * Set Flag
     *
     * @param string $flag
     * @param bool|null $value
     * @return $this
     */
    public function setFlag($flag, $value = null)
    {
        $this->_flags[$flag] = $value;
        return $this;
    }

    /**
     * Has Flag
     *
     * @param string $flag
     * @return bool
     */
    public function hasFlag($flag)
    {
        return array_key_exists($flag, $this->_flags);
    }

    /**
     * Sleep handler
     *
     * @return string[]
     * @since 100.0.11
     */
    public function __sleep()
    {
        $properties = array_keys(get_object_vars($this));
        $properties = array_diff(
            $properties,
            [
                '_entityFactory',
            ]
        );
        return $properties;
    }

    /**
     * Init not serializable fields
     *
     * @return void
     * @since 100.0.11
     */
    public function __wakeup()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_entityFactory = $objectManager->get(EntityFactoryInterface::class);
    }
}
