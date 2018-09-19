<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Returns information for "Recently Ordered" widget.
 * It contains list of 5 salable products from the last placed order.
 * Qty of products to display is limited by LastOrderedItems::SIDEBAR_ORDER_LIMIT constant.
 */
class LastOrderedItems implements SectionSourceInterface
{
    /**
     * Limit of orders in side bar
     */
    const SIDEBAR_ORDER_LIMIT = 5;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
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
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_orderConfig = $orderConfig;
        $this->_customerSession = $customerSession;
        $this->stockRegistry = $stockRegistry;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Init last placed customer order for display on front
     *
     * @return void
     */
    protected function initOrders()
    {
        $customerId = $this->_customerSession->getCustomerId();

        $orders = $this->_orderCollectionFactory->create()
            ->addAttributeToFilter('customer_id', $customerId)
            ->addAttributeToFilter('status', ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()])
            ->addAttributeToSort('created_at', 'desc')
            ->setPage(1, 1);
        //TODO: add filter by current website
        $this->orders = $orders;
    }

    /**
     * Get list of last ordered products
     *
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        $order = $this->getLastOrder();
        $limit = self::SIDEBAR_ORDER_LIMIT;

        if ($order) {
            $website = $this->_storeManager->getStore()->getWebsiteId();
            /** @var \Magento\Sales\Model\Order\Item $item */
            foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                /** @var \Magento\Catalog\Model\Product $product */
                try {
                    $product = $this->productRepository->getById(
                        $item->getProductId(),
                        false,
                        $this->_storeManager->getStore()->getId()
                    );
                } catch (NoSuchEntityException $noEntityException) {
                    $this->logger->critical($noEntityException);
                    continue;
                }
                if (isset($product) && in_array($website, $product->getWebsiteIds())) {
                    $url = $product->isVisibleInSiteVisibility() ? $product->getProductUrl() : null;
                    $items[] = [
                        'id' => $item->getId(),
                        'name' => $item->getName(),
                        'url' => $url,
                        'is_saleable' => $this->isItemAvailableForReorder($item),
                    ];
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
    protected function isItemAvailableForReorder(\Magento\Sales\Model\Order\Item $orderItem)
    {
        try {
            $stockItem = $this->stockRegistry->getStockItem(
                $orderItem->getProduct()->getId(),
                $orderItem->getStore()->getWebsiteId()
            );
            return $stockItem->getIsInStock();
        } catch (NoSuchEntityException $noEntityException) {
            return false;
        }
    }

    /**
     * Last order getter
     *
     * @return \Magento\Sales\Model\Order|void
     */
    protected function getLastOrder()
    {
        if (!$this->orders) {
            $this->initOrders();
        }
        foreach ($this->orders as $order) {
            return $order;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        return ['items' => $this->getItems()];
    }
}
