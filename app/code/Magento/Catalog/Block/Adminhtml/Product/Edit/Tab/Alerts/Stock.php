<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Alerts;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;

/**
 * Sign up for an alert when the product price changes grid
 *
 * @api
 * @since 100.0.2
 */
class Stock extends Extended
{
    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\ProductAlert\Model\StockFactory
     */
    protected $_stockFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ProductAlert\Model\StockFactory $stockFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\ProductAlert\Model\StockFactory $stockFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_stockFactory = $stockFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('alertStock');
        $this->setDefaultSort('add_date');
        $this->setDefaultSort('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no customers for this alert.'));
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $productId = $this->getRequest()->getParam('id');
        $websiteId = 0;
        if ($store = $this->getRequest()->getParam('store')) {
            $websiteId = $this->_storeManager->getStore($store)->getWebsiteId();
        }
        if ($this->moduleManager->isEnabled('Magento_ProductAlert')) {
            $collection = $this->_stockFactory->create()->getCustomerCollection()->join($productId, $websiteId);
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('firstname', ['header' => __('First Name'), 'index' => 'firstname']);

        $this->addColumn('lastname', ['header' => __('Last Name'), 'index' => 'lastname']);

        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);

        $this->addColumn('add_date', ['header' => __('Subscribe Date'), 'index' => 'add_date', 'type' => 'date']);

        $this->addColumn(
            'send_date',
            ['header' => __('Last Notified'), 'index' => 'send_date', 'type' => 'date']
        );

        $this->addColumn('send_count', ['header' => __('Send Count'), 'index' => 'send_count']);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }
        return $this->getUrl('catalog/product/alertsStockGrid', ['id' => $productId, 'store' => $storeId]);
    }
}
