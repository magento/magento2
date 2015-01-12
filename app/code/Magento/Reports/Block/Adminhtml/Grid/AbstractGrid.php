<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Block\Adminhtml\Grid;

class AbstractGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var string
     */
    protected $_resourceCollectionName = '';

    /**
     * @var null
     */
    protected $_currentCurrencyCode = null;

    /**
     * @var array
     */
    protected $_storeIds = [];

    /**
     * @var null
     */
    protected $_aggregatedColumns = null;

    /**
     * Reports data
     *
     * @var \Magento\Reports\Helper\Data
     */
    protected $_reportsData = null;

    /**
     * Reports grouped collection factory
     *
     * @var \Magento\Reports\Model\Grouped\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Resource collection factory
     *
     * @var \Magento\Reports\Model\Resource\Report\Collection\Factory
     */
    protected $_resourceFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\Resource\Report\Collection\Factory $resourceFactory
     * @param \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory
     * @param \Magento\Reports\Helper\Data $reportsData
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\Resource\Report\Collection\Factory $resourceFactory,
        \Magento\Reports\Model\Grouped\CollectionFactory $collectionFactory,
        \Magento\Reports\Helper\Data $reportsData,
        array $data = []
    ) {
        $this->_resourceFactory = $resourceFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_reportsData = $reportsData;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setUseAjax(false);
        if (isset($this->_columnGroupBy)) {
            $this->isColumnGrouped($this->_columnGroupBy, true);
        }
        $this->setEmptyCellLabel(__('We couldn\'t find records for this period.'));
    }

    /**
     * @return string
     */
    public function getResourceCollectionName()
    {
        return $this->_resourceCollectionName;
    }

    /**
     * @return \Magento\Framework\Data\Collection
     */
    public function getCollection()
    {
        if (is_null($this->_collection)) {
            $this->setCollection($this->_collectionFactory->create());
        }
        return $this->_collection;
    }

    /**
     * @return array
     */
    protected function _getAggregatedColumns()
    {
        if (is_null($this->_aggregatedColumns)) {
            foreach ($this->getColumns() as $column) {
                if (!is_array($this->_aggregatedColumns)) {
                    $this->_aggregatedColumns = [];
                }
                if ($column->hasTotal()) {
                    $this->_aggregatedColumns[$column->getId()] = "{$column->getTotal()}({$column->getIndex()})";
                }
            }
        }
        return $this->_aggregatedColumns;
    }

    /**
     * Add column to grid
     * Overridden to add support for visibility_filter column option
     * It stands for conditional visibility of the column depending on filter field values
     * Value of visibility_filter supports (filter_field_name => filter_field_value) pairs
     *
     * @param string $columnId
     * @param array $column
     * @return $this
     */
    public function addColumn($columnId, $column)
    {
        if (is_array($column) && array_key_exists('visibility_filter', $column)) {
            $filterData = $this->getFilterData();
            $visibilityFilter = $column['visibility_filter'];
            if (!is_array($visibilityFilter)) {
                $visibilityFilter = [$visibilityFilter];
            }
            foreach ($visibilityFilter as $k => $v) {
                if (is_int($k)) {
                    $filterFieldId = $v;
                    $filterFieldValue = true;
                } else {
                    $filterFieldId = $k;
                    $filterFieldValue = $v;
                }
                if (!$filterData->hasData($filterFieldId) || $filterData->getData($filterFieldId) != $filterFieldValue
                ) {
                    return $this;  // don't add column
                }
            }
        }
        return parent::addColumn($columnId, $column);
    }

    /**
     * Get allowed store ids array intersected with selected scope in store switcher
     *
     * @return array
     */
    protected function _getStoreIds()
    {
        $filterData = $this->getFilterData();
        if ($filterData) {
            $storeIds = explode(',', $filterData->getData('store_ids'));
        } else {
            $storeIds = [];
        }
        // By default storeIds array contains only allowed stores
        $allowedStoreIds = array_keys($this->_storeManager->getStores());
        // And then array_intersect with post data for prevent unauthorized stores reports
        $storeIds = array_intersect($allowedStoreIds, $storeIds);
        // If selected all websites or unauthorized stores use only allowed
        if (empty($storeIds)) {
            $storeIds = $allowedStoreIds;
        }
        // reset array keys
        $storeIds = array_values($storeIds);

        return $storeIds;
    }

    /**
     * @return $this|\Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        $filterData = $this->getFilterData();

        if ($filterData->getData('from') == null || $filterData->getData('to') == null) {
            $this->setCountTotals(false);
            $this->setCountSubTotals(false);
            return parent::_prepareCollection();
        }

        $storeIds = $this->_getStoreIds();

        $orderStatuses = $filterData->getData('order_statuses');
        if (is_array($orderStatuses)) {
            if (count($orderStatuses) == 1 && strpos($orderStatuses[0], ',') !== false) {
                $filterData->setData('order_statuses', explode(',', $orderStatuses[0]));
            }
        }

        $resourceCollection = $this->_resourceFactory->create(
            $this->getResourceCollectionName()
        )->setPeriod(
            $filterData->getData('period_type')
        )->setDateRange(
            $filterData->getData('from', null),
            $filterData->getData('to', null)
        )->addStoreFilter(
            $storeIds
        )->setAggregatedColumns(
            $this->_getAggregatedColumns()
        );

        $this->_addOrderStatusFilter($resourceCollection, $filterData);
        $this->_addCustomFilter($resourceCollection, $filterData);

        if ($this->_isExport) {
            $this->setCollection($resourceCollection);
            return $this;
        }

        if ($filterData->getData('show_empty_rows', false)) {
            $this->_reportsData->prepareIntervalsCollection(
                $this->getCollection(),
                $filterData->getData('from', null),
                $filterData->getData('to', null),
                $filterData->getData('period_type')
            );
        }

        if ($this->getCountSubTotals()) {
            $this->getSubTotals();
        }

        if ($this->getCountTotals()) {
            $totalsCollection = $this->_resourceFactory->create(
                $this->getResourceCollectionName()
            )->setPeriod(
                $filterData->getData('period_type')
            )->setDateRange(
                $filterData->getData('from', null),
                $filterData->getData('to', null)
            )->addStoreFilter(
                $storeIds
            )->setAggregatedColumns(
                $this->_getAggregatedColumns()
            )->isTotals(
                true
            );

            $this->_addOrderStatusFilter($totalsCollection, $filterData);
            $this->_addCustomFilter($totalsCollection, $filterData);

            foreach ($totalsCollection as $item) {
                $this->setTotals($item);
                break;
            }
        }

        $this->getCollection()->setColumnGroupBy($this->_columnGroupBy);
        $this->getCollection()->setResourceCollection($resourceCollection);

        return parent::_prepareCollection();
    }

    /**
     * @return array
     */
    public function getCountTotals()
    {
        if (!$this->getTotals()) {
            $filterData = $this->getFilterData();
            $totalsCollection = $this->_resourceFactory->create(
                $this->getResourceCollectionName()
            )->setPeriod(
                $filterData->getData('period_type')
            )->setDateRange(
                $filterData->getData('from', null),
                $filterData->getData('to', null)
            )->addStoreFilter(
                $this->_getStoreIds()
            )->setAggregatedColumns(
                $this->_getAggregatedColumns()
            )->isTotals(
                true
            );

            $this->_addOrderStatusFilter($totalsCollection, $filterData);

            if (count($totalsCollection->getItems()) < 1 || !$filterData->getData('from')) {
                $this->setTotals(new \Magento\Framework\Object());
                $this->setCountTotals(false);
            } else {
                foreach ($totalsCollection->getItems() as $item) {
                    $this->setTotals($item);
                    break;
                }
            }
        }
        return parent::getCountTotals();
    }

    /**
     * @return array
     */
    public function getSubTotals()
    {
        $filterData = $this->getFilterData();
        $subTotalsCollection = $this->_resourceFactory->create(
            $this->getResourceCollectionName()
        )->setPeriod(
            $filterData->getData('period_type')
        )->setDateRange(
            $filterData->getData('from', null),
            $filterData->getData('to', null)
        )->addStoreFilter(
            $this->_getStoreIds()
        )->setAggregatedColumns(
            $this->_getAggregatedColumns()
        )->setIsSubTotals(
            true
        );

        $this->_addOrderStatusFilter($subTotalsCollection, $filterData);
        $this->_addCustomFilter($subTotalsCollection, $filterData);

        $this->setSubTotals($subTotalsCollection->getItems());
        return parent::getSubTotals();
    }

    /**
     * @param array $storeIds
     *
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @return string|\Magento\Directory\Model\Currency $currencyCode
     */
    public function getCurrentCurrencyCode()
    {
        if (is_null($this->_currentCurrencyCode)) {
            $this->_currentCurrencyCode = count(
                $this->_storeIds
            ) > 0 ? $this->_storeManager->getStore(
                array_shift($this->_storeIds)
            )->getBaseCurrencyCode() : $this->_storeManager->getStore()->getBaseCurrencyCode();
        }
        return $this->_currentCurrencyCode;
    }

    /**
     * Get currency rate (base to given currency)
     *
     * @param string|\Magento\Directory\Model\Currency $toCurrency
     * @return float
     */
    public function getRate($toCurrency)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);
    }

    /**
     * Add order status filter
     *
     * @param \Magento\Reports\Model\Resource\Report\Collection\AbstractCollection $collection
     * @param \Magento\Framework\Object $filterData
     * @return $this
     */
    protected function _addOrderStatusFilter($collection, $filterData)
    {
        $collection->addOrderStatusFilter($filterData->getData('order_statuses'));
        return $this;
    }

    /**
     * Adds custom filter to resource collection
     * Can be overridden in child classes if custom filter needed
     *
     * @param \Magento\Reports\Model\Resource\Report\Collection\AbstractCollection $collection
     * @param \Magento\Framework\Object $filterData
     * @return $this
     */
    protected function _addCustomFilter($collection, $filterData)
    {
        return $this;
    }
}
