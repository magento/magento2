<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\CustomerData;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Item;
use Magento\Sales\Model\ResourceModel\Order\Collection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Returns information for "Recently Ordered" widget.
 * It contains list of 5 salable products from the last placed order.
 * Qty of products to display is limited by LastOrderedItems::SIDEBAR_ORDER_LIMIT constant.
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class LastOrderedItems implements SectionSourceInterface
{
    /**
     * Limit of orders in sidebar
     */
    public const SIDEBAR_ORDER_LIMIT = 5;

    /**
     * @var CollectionFactoryInterface
     */
    protected $_orderCollectionFactory;

    /**
     * @var Config
     */
    protected $_orderConfig;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var Context
     */
    protected $httpContext;

    /**
     * @var Collection
     */
    protected $orders;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CollectionFactoryInterface $orderCollectionFactory
     * @param Config $orderConfig
     * @param Session $customerSession
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        CollectionFactoryInterface $orderCollectionFactory,
        Config $orderConfig,
        Session $customerSession,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
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
    protected function initOrders(): void
    {
        $customerId = $this->_customerSession->getCustomerId();

        $orders = $this->_orderCollectionFactory->create($customerId)
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
     * @throws NoSuchEntityException
     */
    protected function getItems(): array
    {
        $items = [];
        $order = $this->getLastOrder();
        $limit = self::SIDEBAR_ORDER_LIMIT;

        if ($order) {
            $website = $this->_storeManager->getStore()->getWebsiteId();
            /** @var Item $item */
            foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                /** @var Product $product */
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
                if (in_array($website, $product->getWebsiteIds())) {
                    $url = $product->isVisibleInSiteVisibility() ? $product->getProductUrl() : null;
                    $items[] = [
                        'id' => $item->getId(),
                        'name' => $item->getName(),
                        'url' => $url,
                        'is_saleable' => $this->isItemAvailableForReorder($item),
                        'product_id' => $item->getProductId(),
                    ];
                }
            }
        }

        return $items;
    }

    /**
     * Check item product availability for reorder
     *
     * @param  Item $orderItem
     * @return boolean
     */
    protected function isItemAvailableForReorder(Item $orderItem)
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
     * @return Order|void
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
     * @inheritdoc
     */
    public function getSectionData(): array
    {
        return ['items' => $this->getItems()];
    }
}
