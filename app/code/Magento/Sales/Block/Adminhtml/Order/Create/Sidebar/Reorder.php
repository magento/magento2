<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Adminhtml sales order create sidebar cart block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Reorder extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
{
    /**
     * Storage action on selected item
     *
     * @var string
     * @since 2.0.0
     */
    protected $_sidebarStorageAction = 'add_order_item';

    /**
     * Orders factory
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     * @since 2.0.0
     */
    protected $_ordersFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Config $salesConfig
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $ordersFactory
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Config $salesConfig,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $ordersFactory,
        array $data = []
    ) {
        $this->_ordersFactory = $ordersFactory;
        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $salesConfig, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_sidebar_reorder');
        $this->setDataId('reorder');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Last Ordered Items');
    }

    /**
     * Retrieve last order on current website
     *
     * @return \Magento\Sales\Model\Order|false
     * @since 2.0.0
     */
    public function getLastOrder()
    {
        $storeIds = $this->getQuote()->getStore()->getWebsite()->getStoreIds();
        $collection = $this->_ordersFactory->create()->addFieldToFilter(
            'customer_id',
            $this->getCustomerId()
        )->addFieldToFilter(
            'store_id',
            ['in' => $storeIds]
        )->setOrder(
            'created_at',
            'desc'
        )->setPageSize(
            1
        )->load();
        foreach ($collection as $order) {
            return $order;
        }

        return false;
    }

    /**
     * Retrieve item collection
     *
     * @return array|false
     * @since 2.0.0
     */
    public function getItemCollection()
    {
        if ($order = $this->getLastOrder()) {
            $items = [];
            foreach ($order->getItemsCollection() as $item) {
                if (!$item->getParentItem()) {
                    $items[] = $item;
                }
            }
            return $items;
        }
        return false;
    }

    /**
     * Retrieve display item qty availability
     *
     * @return false
     * @since 2.0.0
     */
    public function canDisplayItemQty()
    {
        return false;
    }

    /**
     * Retrieve remove items availability
     *
     * @return false
     * @since 2.0.0
     */
    public function canRemoveItems()
    {
        return false;
    }

    /**
     * Retrieve display price availability
     *
     * @return false
     * @since 2.0.0
     */
    public function canDisplayPrice()
    {
        return false;
    }

    /**
     * Retrieve identifier of block item
     *
     * @param \Magento\Framework\DataObject $item
     * @return int
     * @since 2.0.0
     */
    public function getIdentifierId($item)
    {
        return $item->getId();
    }
}
