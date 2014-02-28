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
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer view wishlist block
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Tab\View;

class Sales extends \Magento\Backend\Block\Template
{

    /**
     * Sales entity collection
     *
     * @var \Magento\Sales\Model\Resource\Sale\Collection
     */
    protected $_collection;

    protected $_groupedCollection;
    protected $_websiteCounts;

    /**
     * Currency model
     *
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * Core registry
     *
     * @var \Magento\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $_currencyFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Sale\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Sales\Model\Resource\Sale\CollectionFactory $collectionFactory
     * @param \Magento\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Sales\Model\Resource\Sale\CollectionFactory $collectionFactory,
        \Magento\Registry $coreRegistry,
        array $data = array()
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_currencyFactory = $currencyFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_view_sales_grid');
    }

    public function _beforeToHtml()
    {
        $this->_currency = $this->_currencyFactory->create()
            ->load($this->_storeConfig->getConfig(\Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE));

        $customerId = $this->_coreRegistry->registry('current_customer')
            ? $this->_coreRegistry->registry('current_customer')->getId()
            : 0;
        $this->_collection = $this->_collectionFactory->create()
            ->setCustomerFilter($customerId)
            ->setOrderStateFilter(\Magento\Sales\Model\Order::STATE_CANCELED, true)
            ->load();

        $this->_groupedCollection = array();

        foreach ($this->_collection as $sale) {
            if (!is_null($sale->getStoreId())) {
                $store      = $this->_storeManager->getStore($sale->getStoreId());
                $websiteId  = $store->getWebsiteId();
                $groupId    = $store->getGroupId();
                $storeId    = $store->getId();

                $sale->setWebsiteId($store->getWebsiteId());
                $sale->setWebsiteName($store->getWebsite()->getName());
                $sale->setGroupId($store->getGroupId());
                $sale->setGroupName($store->getGroup()->getName());
            } else {
                $websiteId  = 0;
                $groupId    = 0;
                $storeId    = 0;

                $sale->setStoreName(__('Deleted Stores'));
            }

            $this->_groupedCollection[$websiteId][$groupId][$storeId] = $sale;
            $this->_websiteCounts[$websiteId] = isset($this->_websiteCounts[$websiteId])
                ? $this->_websiteCounts[$websiteId] + 1
                : 1;
        }

        return parent::_beforeToHtml();
    }

    public function getWebsiteCount($websiteId)
    {
        return isset($this->_websiteCounts[$websiteId]) ? $this->_websiteCounts[$websiteId] : 0;
    }

    public function getRows()
    {
        return $this->_groupedCollection;
    }

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
     */
    public function formatCurrency($price, $websiteId = null)
    {
        return $this->_storeManager->getWebsite($websiteId)->getBaseCurrency()->format($price);
    }

    /**
     * Is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
