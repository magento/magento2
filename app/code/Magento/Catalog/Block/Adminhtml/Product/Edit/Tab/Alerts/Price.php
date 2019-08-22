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
class Price extends Extended
{
    /**
     * Catalog data
     *
     * @var \Magento\Framework\Module\ModuleManagerInterface
     */
    protected $moduleManager;

    /**
     * @var \Magento\ProductAlert\Model\PriceFactory
     */
    protected $_priceFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\ProductAlert\Model\PriceFactory $priceFactory
     * @param \Magento\Framework\Module\ModuleManagerInterface $moduleManager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\ProductAlert\Model\PriceFactory $priceFactory,
        \Magento\Framework\Module\ModuleManagerInterface $moduleManager,
        array $data = []
    ) {
        $this->_priceFactory = $priceFactory;
        $this->moduleManager = $moduleManager;
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

        $this->setId('alertPrice');
        $this->setDefaultSort('add_date');
        $this->setDefaultSort('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no customers for this alert.'));
    }

    /**
     * @inheritDoc
     *
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
            $collection = $this->_priceFactory->create()->getCustomerCollection()->join($productId, $websiteId);
            $this->setCollection($collection);
        }
        return parent::_prepareCollection();
    }

    /**
     * @inheritDoc
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('firstname', ['header' => __('First Name'), 'index' => 'firstname']);

        $this->addColumn('lastname', ['header' => __('Last Name'), 'index' => 'lastname']);

        $this->addColumn('email', ['header' => __('Email'), 'index' => 'email']);

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'index' => 'price',
                'type' => 'currency',
                'currency_code' => $this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );

        $this->addColumn('add_date', ['header' => __('Subscribe Date'), 'index' => 'add_date', 'type' => 'date']);

        $this->addColumn(
            'last_send_date',
            ['header' => __('Last Notified'), 'index' => 'last_send_date', 'type' => 'date']
        );

        $this->addColumn('send_count', ['header' => __('Send Count'), 'index' => 'send_count']);

        return parent::_prepareColumns();
    }

    /**
     * @inheritDoc
     *
     * @return string
     */
    public function getGridUrl()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }
        return $this->getUrl('catalog/product/alertsPriceGrid', ['id' => $productId, 'store' => $storeId]);
    }
}
