<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\Dashboard\Orders;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Module\Manager;
use Magento\Reports\Model\ResourceModel\Order\CollectionFactory;

/**
 * Adminhtml dashboard recent orders grid
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 100.0.2
 */
class Grid extends \Magento\Backend\Block\Dashboard\Grid
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Manager $moduleManager
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Manager $moduleManager,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct.
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('lastOrdersGrid');
    }

    /**
     * Prepare collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        if (!$this->_moduleManager->isEnabled('Magento_Reports')) {
            return $this;
        }
        $collection = $this->_collectionFactory->create()->addItemCountExpr()->joinCustomerName(
            'customer'
        )->orderByCreatedAt();

        if ($this->getParam('store') || $this->getParam('website') || $this->getParam('group')) {
            if ($this->getParam('store')) {
                $collection->addAttributeToFilter('store_id', $this->getParam('store'));
            } elseif ($this->getParam('website')) {
                $storeIds = $this->_storeManager->getWebsite($this->getParam('website'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
            } elseif ($this->getParam('group')) {
                $storeIds = $this->_storeManager->getGroup($this->getParam('group'))->getStoreIds();
                $collection->addAttributeToFilter('store_id', ['in' => $storeIds]);
            }

            $collection->addRevenueToSelect();
        } else {
            $collection->addRevenueToSelect(true);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        foreach ($this->getCollection() as $item) {
            $item->getCustomer() ?: $item->setCustomer($item->getBillingAddress()->getName());
        }
        return $this;
    }

    /**
     * Prepares page sizes for dashboard grid with las 5 orders
     *
     * @return void
     */
    protected function _preparePage()
    {
        $this->getCollection()->setPageSize($this->getParam($this->getVarNameLimit(), $this->_defaultLimit));
        // Remove count of total orders
        // $this->getCollection()->setCurPage($this->getParam($this->getVarNamePage(), $this->_defaultPage));
    }

    /**
     * Prepare columns.
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'customer',
            ['header' => __('Customer'), 'sortable' => false, 'index' => 'customer', 'default' => __('Guest')]
        );

        $this->addColumn(
            'items',
            [
                'header' => __('Items'),
                'type' => 'number',
                'sortable' => false,
                'index' => 'items_count'
            ]
        );

        $baseCurrencyCode = $this->_storeManager->getStore(
            (int)$this->getParam('store')
        )->getBaseCurrencyCode();

        $this->addColumn(
            'total',
            [
                'header' => __('Total'),
                'sortable' => false,
                'type' => 'currency',
                'currency_code' => $this->_escaper->escapeHtml($baseCurrencyCode),
                'index' => 'revenue'
            ]
        );

        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);

        return parent::_prepareColumns();
    }

    /**
     * @inheritdoc
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $row->getId()]);
    }
}
