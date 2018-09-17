<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create sidebar recently view block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Pviewed extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * Event factory
     *
     * @var \Magento\Reports\Model\EventFactory
     */
    protected $_eventFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Reports\Model\EventFactory $eventFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Reports\Model\EventFactory $eventFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_eventFactory = $eventFactory;
        $this->_productFactory = $productFactory;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesConfig, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_pviewed');
        $this->setDataId('pviewed');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Recently Viewed Products');
    }

    /**
     * Retrieve item collection
     *
     * @return mixed
     */
    public function getItemCollection()
    {
        $productCollection = $this->getData('item_collection');
        if ($productCollection === null) {
            $stores = [];
            $website = $this->_storeManager->getStore($this->getStoreId())->getWebsite();
            foreach ($website->getStores() as $store) {
                $stores[] = $store->getId();
            }

            $collection = $this->_eventFactory->create()->getCollection()->addStoreFilter(
                $stores
            )->addRecentlyFiler(
                \Magento\Reports\Model\Event::EVENT_PRODUCT_VIEW,
                $this->getCustomerId(),
                0
            );
            $productIds = [];
            foreach ($collection as $event) {
                $productIds[] = $event->getObjectId();
            }

            $productCollection = null;
            if ($productIds) {
                $productCollection = $this->_productFactory->create()->getCollection()->setStoreId(
                    $this->getQuote()->getStoreId()
                )->addStoreFilter(
                    $this->getQuote()->getStoreId()
                )->addAttributeToSelect(
                    'name'
                )->addAttributeToSelect(
                    'price'
                )->addAttributeToSelect(
                    'small_image'
                )->addIdFilter(
                    $productIds
                )->load();
            }
            $this->setData('item_collection', $productCollection);
        }
        return $productCollection;
    }

    /**
     * Retrieve availability removing items in block
     *
     * @return false
     */
    public function canRemoveItems()
    {
        return false;
    }

    /**
     * Retrieve identifier of block item
     *
     * @param \Magento\Framework\DataObject $item
     * @return int
     */
    public function getIdentifierId($item)
    {
        return $item->getId();
    }
}
