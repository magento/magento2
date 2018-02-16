<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Customers;

/**
 * Adminhtml dashboard most active buyers
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Most extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customersMostGrid');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create();
        /* @var $collection \Magento\Reports\Model\ResourceModel\Order\Collection */
        $collection->groupByCustomer()->addOrdersCount()->joinCustomerName();

        $storeFilter = 0;
        if ($this->getParam('store')) {
            $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            $storeFilter = 1;
        } elseif ($this->getParam('website')) {
            $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
            $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
        } elseif ($this->getParam('group')) {
            $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
            $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
        }

        $collection->addSumAvgTotals($storeFilter)->orderByTotalAmount();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', ['header' => __('Customer'), 'sortable' => false, 'index' => 'name']);

        $this->addColumn(
            'orders_count',
            [
                'header' => __('Orders'),
                'sortable' => false,
                'index' => 'orders_count',
                'type' => 'number',
                'header_css_class' => 'col-orders',
                'column_css_class' => 'col-orders'
            ]
        );

        $baseCurrencyCode = (string)$this->_storeManager->getStore(
            (int)$this->getParam('store')
        )->getBaseCurrencyCode();

        $this->addColumn(
            'orders_avg_amount',
            [
                'header' => __('Average'),
                'sortable' => false,
                'type' => 'currency',
                'currency_code' => $baseCurrencyCode,
                'index' => 'orders_avg_amount',
                'header_css_class' => 'col-avg',
                'column_css_class' => 'col-avg'
            ]
        );

        $this->addColumn(
            'orders_sum_amount',
            [
                'header' => __('Total'),
                'sortable' => false,
                'type' => 'currency',
                'currency_code' => $baseCurrencyCode,
                'index' => 'orders_sum_amount',
                'header_css_class' => 'col-total',
                'column_css_class' => 'col-total'
            ]
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('customer/index/edit', ['id' => $row->getCustomerId()]);
    }
}
