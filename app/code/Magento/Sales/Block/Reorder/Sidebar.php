<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Reorder;

use Magento\Customer\Model\Context;
use Magento\Framework\View\Block\IdentityInterface;

/**
 * Sales order view block
 *
 * @method Sidebar setOrders(\Magento\Sales\Model\Resource\Order\Collection $ordersCollection)
 * @method \Magento\Sales\Model\Resource\Order\Collection|null getOrders()
 */
class Sidebar extends \Magento\Framework\View\Element\Template implements IdentityInterface
{
    /**
     * Limit of orders in side bar
     */
    const SIDEBAR_ORDER_LIMIT = 5;

    /**
     * @var string
     */
    protected $_template = 'order/history.phtml';

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->_customerSession = $customerSession;
        $this->httpContext = $httpContext;
        $this->stockRegistry = $stockRegistry;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Init orders
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            $this->initOrders();
        }
    }

    /**
     * Init customer order for display on front
     *
     * @return void
     */
    public function initOrders()
    {
        $customerId = $this->getCustomerId() ? $this->getCustomerId() : $this->_customerSession->getCustomerId();

        $orders = $this->_orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId)
            ->addAttributeToFilter('status', ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()])
            ->addAttributeToSort('created_at', 'desc')
            ->setPage(1, 1);
        //TODO: add filter by current website
        $this->setOrders($orders);
    }

    /**
     * Get list of last ordered products
     *
     * @return array
     */
    public function getItems()
    {
        $items = [];
        $order = $this->getLastOrder();
        $limit = self::SIDEBAR_ORDER_LIMIT;

        if ($order) {
            $website = $this->_storeManager->getStore()->getWebsiteId();
            foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                if ($item->getProduct() && in_array($website, $item->getProduct()->getWebsiteIds())) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Check item product availability for reorder
     *
     * @param  \Magento\Sales\Model\Order\Item $orderItem
     * @return boolean
     */
    public function isItemAvailableForReorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        try {
            $stockItem = $this->stockRegistry->getStockItem(
                $orderItem->getProduct()->getId(),
                $orderItem->getStore()->getWebsiteId()
            );
            return $stockItem->getIsInStock();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
            return false;
        }
    }

    /**
     * Retrieve form action url and set "secure" param to avoid confirm
     * message when we submit form from secure page to unsecure
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('checkout/cart/addgroup', ['_secure' => true]);
    }

    /**
     * Last order getter
     *
     * @return \Magento\Sales\Model\Order|false
     */
    public function getLastOrder()
    {
        if (!$this->getOrders()) {
            return false;
        }
        foreach ($this->getOrders() as $order) {
            return $order;
        }
    }

    /**
     * Render "My Orders" sidebar block
     *
     * @return string
     */
    protected function _toHtml()
    {
        $isValid = $this->httpContext->getValue(Context::CONTEXT_AUTH) || $this->getCustomerId();
        return $isValid ? parent::_toHtml() : '';
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        foreach ($this->getItems() as $item) {
            $identities = array_merge($identities, $item->getProduct()->getIdentities());
        }
        return $identities;
    }
}
