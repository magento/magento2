<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Directory\Model\Currency;
use Magento\Sales\Model\Order;

/**
 * Adminhtml customer view sales block
 * @since 2.0.0
 */
class Sales extends \Magento\Backend\Block\Template
{
    /**
     * Sales entity collection
     *
     * @var \Magento\Sales\Model\ResourceModel\Sale\Collection
     * @since 2.0.0
     */
    protected $_collection;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_groupedCollection;

    /**
     * @var int[]
     * @since 2.0.0
     */
    protected $_websiteCounts;

    /**
     * Currency model
     *
     * @var Currency
     * @since 2.0.0
     */
    protected $_currency;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     * @since 2.0.0
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory
     * @since 2.0.0
     */
    protected $_collectionFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Sales\Model\ResourceModel\Sale\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_currencyFactory = $currencyFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialize the sales grid.
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_view_sales_grid');
    }

    /**
     * Execute before toHtml() code.
     *
     * @return $this
     * @since 2.0.0
     */
    public function _beforeToHtml()
    {
        $this->_currency = $this->_currencyFactory->create()->load(
            $this->_scopeConfig->getValue(
                Currency::XML_PATH_CURRENCY_BASE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );

        $this->_collection = $this->_collectionFactory->create()->setCustomerIdFilter(
            (int)$this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID)
        )->setOrderStateFilter(
            Order::STATE_CANCELED,
            true
        )->load();

        $this->_groupedCollection = [];

        foreach ($this->_collection as $sale) {
            if ($sale->getStoreId() !== null) {
                $store = $this->_storeManager->getStore($sale->getStoreId());
                $websiteId = $store->getWebsiteId();
                $groupId = $store->getGroupId();
                $storeId = $store->getId();

                $sale->setWebsiteId($store->getWebsiteId());
                $sale->setWebsiteName($store->getWebsite()->getName());
                $sale->setGroupId($store->getGroupId());
                $sale->setGroupName($store->getGroup()->getName());
            } else {
                $websiteId = 0;
                $groupId = 0;
                $storeId = 0;

                $sale->setStoreName(__('Deleted Stores'));
            }

            $this->_groupedCollection[$websiteId][$groupId][$storeId] = $sale;
            $this->_websiteCounts[$websiteId] = isset(
                $this->_websiteCounts[$websiteId]
            ) ? $this->_websiteCounts[$websiteId] + 1 : 1;
        }

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve the website count for the specified website Id
     *
     * @param int $websiteId
     * @return int
     * @since 2.0.0
     */
    public function getWebsiteCount($websiteId)
    {
        return isset($this->_websiteCounts[$websiteId]) ? $this->_websiteCounts[$websiteId] : 0;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getRows()
    {
        return $this->_groupedCollection;
    }

    /**
     * @return \Magento\Framework\DataObject
     * @since 2.0.0
     */
    public function getTotals()
    {
        return $this->_collection->getTotals();
    }

    /**
     * Format price by specified website
     *
     * @param float $price
     * @param null|int $websiteId
     * @return string
     * @since 2.0.0
     */
    public function formatCurrency($price, $websiteId = null)
    {
        return $this->_storeManager->getWebsite($websiteId)->getBaseCurrency()->format($price);
    }

    /**
     * Is single store mode
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
