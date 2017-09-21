<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard\Tab\Customers;

/**
 * Adminhtml dashboard most recent customers grid
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @api
 * @since 100.0.2
 */
class Newest extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Reports\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
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
        $this->setId('customersNewestGrid');
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareCollection()
    {
        $collection = $this->_collectionFactory->create()->addCustomerName();

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

        $collection->addOrdersStatistics($storeFilter)->orderByCustomerRegistration();

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
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class,
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
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class,
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
        return $this->getUrl('customer/index/edit', ['id' => $row->getId()]);
    }
}
