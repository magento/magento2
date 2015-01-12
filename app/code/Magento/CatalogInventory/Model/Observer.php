<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockIndexInterface;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Sales\Model\Quote\Item as QuoteItem;

/**
 * Catalog inventory module observer
 */
class Observer
{
    /**
     * @var Item[]
     */
    protected $_itemsForReindex = [];

    /**
     * Array, indexed by product's id to contain stockItems of already loaded products
     * Some kind of singleton for product's stock item
     *
     * @var array
     */
    protected $_stockItemsArray = [];

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected $_resourceStock;

    /**
     * @var \Magento\CatalogInventory\Model\Indexer\Stock\Processor
     */
    protected $_stockIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $typeConfig;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_priceIndexer;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockManagementInterface
     */
    protected $stockManagement;

    /**
     * @var StockIndexInterface
     */
    protected $stockIndex;

    /**
     * @var \Magento\CatalogInventory\Helper\Stock
     */
    protected $stockHelper;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var array
     */
    protected $paramListToCheck = [
        'use_config_min_qty' => [
            'item' => 'stock_data/min_qty',
            'config' => 'stock_data/use_config_min_qty',
        ],
        'use_config_min_sale_qty' => [
            'item' => 'stock_data/min_sale_qty',
            'config' => 'stock_data/use_config_min_sale_qty',
        ],
        'use_config_max_sale_qty' => [
            'item' => 'stock_data/max_sale_qty',
            'config' => 'stock_data/use_config_max_sale_qty',
        ],
        'use_config_backorders' => [
            'item' => 'stock_data/backorders',
            'config' => 'stock_data/use_config_backorders',
        ],
        'use_config_notify_stock_qty' => [
            'item' => 'stock_data/notify_stock_qty',
            'config' => 'stock_data/use_config_notify_stock_qty',
        ],
        'use_config_enable_qty_inc' => [
            'item' => 'stock_data/enable_qty_increments',
            'config' => 'stock_data/use_config_enable_qty_inc',
        ],
        'use_config_qty_increments' => [
            'item' => 'stock_data/qty_increments',
            'config' => 'stock_data/use_config_qty_increments',
        ],
    ];

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param Indexer\Stock\Processor $stockIndexerProcessor
     * @param Resource\Stock $resourceStock
     * @param StockRegistryInterface $stockRegistry
     * @param StockManagementInterface $stockManagement
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Helper\Stock $stockHelper
     * @param \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\CatalogInventory\Model\Indexer\Stock\Processor $stockIndexerProcessor,
        \Magento\CatalogInventory\Model\Resource\Stock $resourceStock,
        StockRegistryInterface $stockRegistry,
        StockManagementInterface $stockManagement,
        StockIndexInterface $stockIndex,
        StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Helper\Stock $stockHelper,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->_priceIndexer = $priceIndexer;
        $this->_stockIndexerProcessor = $stockIndexerProcessor;
        $this->_resourceStock = $resourceStock;

        $this->stockRegistry = $stockRegistry;
        $this->stockManagement = $stockManagement;
        $this->stockIndex = $stockIndex;
        $this->stockHelper = $stockHelper;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Add stock information to product
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function addInventoryData($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof \Magento\Catalog\Model\Product) {
            $this->stockHelper->assignStatusToProduct(
                $product,
                $product->getStockStatus()
            );
        }
        return $this;
    }

    /**
     * Add information about product stock status to collection
     * Used in for product collection after load
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function addStockStatusToCollection($observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        $this->stockHelper->addStockStatusToProducts($productCollection);
        return $this;
    }

    /**
     * Add Stock items to product collection
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function addInventoryDataToCollection($observer)
    {
        $productCollection = $observer->getEvent()->getProductCollection();
        $this->stockHelper->addStockStatusToProducts($productCollection);
    }

    /**
     * Saving product inventory data. Product qty calculated dynamically.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function saveInventoryData($observer)
    {
        $product = $observer->getEvent()->getProduct();

        if (is_null($product->getStockData())) {
            if ($product->getIsChangedWebsites() || $product->dataHasChangedFor('status')) {
                $this->stockIndex->rebuild(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
            }
            return $this;
        }

        $this->saveStockItemData($product);
        return $this;
    }

    /**
     * Prepare stock item data for save
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function saveStockItemData($product)
    {
        $stockItemData = $product->getStockData();
        $stockItemData['product_id'] = $product->getId();

        if (!isset($stockItemData['website_id'])) {
            $stockItemData['website_id'] = $this->stockConfiguration->getDefaultWebsiteId();
        }
        $stockItemData['stock_id'] = $this->stockRegistry->getStock($stockItemData['website_id'])->getStockId();

        foreach ($this->paramListToCheck as $dataKey => $configPath) {
            if (null !== $product->getData($configPath['item']) && null === $product->getData($configPath['config'])) {
                $stockItemData[$dataKey] = false;
            }
        }

        $originalQty = $product->getData('stock_data/original_inventory_qty');
        if (strlen($originalQty) > 0) {
            $stockItemData['qty_correction'] = (isset($stockItemData['qty']) ? $stockItemData['qty'] : 0)
                - $originalQty;
        }

        // todo resolve issue with builder and identity field name
        $stockItem = $this->stockRegistry->getStockItem($stockItemData['product_id'], $stockItemData['website_id']);

        $stockItem->addData($stockItemData);
        $this->stockItemRepository->save($stockItem);
        return $this;
    }

    /**
     * Subtract qtys of quote item products after multishipping checkout
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function checkoutAllSubmitAfter(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getInventoryProcessed()) {
            $this->subtractQuoteInventory($observer);
            $this->reindexQuoteInventory($observer);
        }
        return $this;
    }

    /**
     * Return creditmemo items qty to stock
     *
     * @param EventObserver $observer
     * @return void
     */
    public function refundOrderInventory($observer)
    {
        /* @var $creditmemo \Magento\Sales\Model\Order\Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        $itemsToUpdate = [];
        foreach ($creditmemo->getAllItems() as $item) {
            $qty = $item->getQty();
            if (($item->getBackToStock() && $qty) || $this->stockConfiguration->isAutoReturnEnabled()) {
                $productId = $item->getProductId();
                $parentItemId = $item->getOrderItem()->getParentItemId();
                /* @var $parentItem \Magento\Sales\Model\Order\Creditmemo\Item */
                $parentItem = $parentItemId ? $creditmemo->getItemByOrderId($parentItemId) : false;
                $qty = $parentItem ? $parentItem->getQty() * $qty : $qty;
                if (isset($itemsToUpdate[$productId])) {
                    $itemsToUpdate[$productId] += $qty;
                } else {
                    $itemsToUpdate[$productId] = $qty;
                }
            }
        }
        if (!empty($itemsToUpdate)) {
            $this->stockManagement->revertProductsSale(
                $itemsToUpdate,
                $creditmemo->getStore()->getWebsiteId()
            );

            $updatedItemIds = array_keys($itemsToUpdate);
            $this->_stockIndexerProcessor->reindexList($updatedItemIds);
            $this->_priceIndexer->reindexList($updatedItemIds);
        }
    }

    /**
     * Update items stock status and low stock date.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function updateItemsStockUponConfigChange($observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $this->_resourceStock->updateSetOutOfStock($website);
        $this->_resourceStock->updateSetInStock($website);
        $this->_resourceStock->updateLowStockDate($website);
    }

    /**
     * Subtract quote items qtys from stock items related with quote items products.
     *
     * Used before order placing to make order save/place transaction smaller
     * Also called after every successful order placement to ensure subtraction of inventory
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function subtractQuoteInventory(EventObserver $observer)
    {
        /** @var \Magento\Sales\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        if ($quote->getInventoryProcessed()) {
            return $this;
        }
        $items = $this->_getProductsQty($quote->getAllItems());

        /**
         * Remember items
         */
        $this->_itemsForReindex = $this->stockManagement->registerProductsSale(
            $items,
            $quote->getStore()->getWebsiteId()
        );

        $quote->setInventoryProcessed(true);
        return $this;
    }

    /**
     * Revert quote items inventory data (cover not success order place case)
     *
     * @param EventObserver $observer
     * @return void
     */
    public function revertQuoteInventory($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $items = $this->_getProductsQty($quote->getAllItems());
        $this->stockManagement->revertProductsSale($items, $quote->getStore()->getWebsiteId());
        $productIds = array_keys($items);
        if (!empty($productIds)) {
            $this->_stockIndexerProcessor->reindexList($productIds);
            $this->_priceIndexer->reindexList($productIds);
        }
        // Clear flag, so if order placement retried again with success - it will be processed
        $quote->setInventoryProcessed(false);
    }

    /**
     * Adds stock item qty to $items (creates new entry or increments existing one)
     * $items is array with following structure:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     *
     * @param QuoteItem $quoteItem
     * @param array &$items
     * @return void
     */
    protected function _addItemToQtyArray(QuoteItem $quoteItem, &$items)
    {
        $productId = $quoteItem->getProductId();
        if (!$productId) {
            return;
        }
        if (isset($items[$productId])) {
            $items[$productId] += $quoteItem->getTotalQty();
        } else {
            $stockItem = null;
            if ($quoteItem->getProduct()) {
                /** @var Item $stockItem */
                $stockItem = $this->stockRegistry->getStockItem(
                    $quoteItem->getProduct()->getId(),
                    $quoteItem->getStore()->getWebsiteId()
                );
            }
            $items[$productId] = $quoteItem->getTotalQty();
        }
    }

    /**
     * Prepare array with information about used product qty and product stock item
     * result is:
     * array(
     *  $productId  => array(
     *      'qty'   => $qty,
     *      'item'  => $stockItems|null
     *  )
     * )
     * @param array $relatedItems
     * @return array
     */
    protected function _getProductsQty($relatedItems)
    {
        $items = [];
        foreach ($relatedItems as $item) {
            $productId = $item->getProductId();
            if (!$productId) {
                continue;
            }
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $this->_addItemToQtyArray($childItem, $items);
                }
            } else {
                $this->_addItemToQtyArray($item, $items);
            }
        }
        return $items;
    }

    /**
     * Refresh stock index for specific stock items after successful order placement
     *
     * @param EventObserver $observer
     * @return void
     */
    public function reindexQuoteInventory($observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $productIds = [];
        foreach ($quote->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if (count($productIds)) {
            $this->_stockIndexerProcessor->reindexList($productIds);
        }

        // Reindex previously remembered items
        $productIds = [];
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }

        if (!empty($productIds)) {
            $this->_priceIndexer->reindexList($productIds);
        }

        $this->_itemsForReindex = [];
        // Clear list of remembered items - we don't need it anymore
    }

    /**
     * Cancel order item
     *
     * @param   EventObserver $observer
     * @return  void
     */
    public function cancelOrderItem($observer)
    {
        /** @var \Magento\Sales\Model\Order\Item $item */
        $item = $observer->getEvent()->getItem();
        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();
        if ($item->getId() && $item->getProductId() && empty($children) && $qty) {
            $this->stockManagement->backItemQty($item->getProductId(), $qty, $item->getStore()->getWebsiteId());
        }
        $this->_priceIndexer->reindexRow($item->getProductId());
    }

    /**
     * Catalog Product website update
     *
     * @param EventObserver $observer
     * @return void
     */
    public function catalogProductWebsiteUpdate(EventObserver $observer)
    {
        $websiteIds = $observer->getEvent()->getWebsiteIds();
        $productIds = $observer->getEvent()->getProductIds();

        foreach ($websiteIds as $websiteId) {
            foreach ($productIds as $productId) {
                $this->stockIndex->rebuild($productId, $websiteId);
            }
        }
    }

    /**
     * Add stock status to prepare index select
     *
     * @param EventObserver $observer
     * @return void
     */
    public function addStockStatusToPrepareIndexSelect(EventObserver $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $select = $observer->getEvent()->getSelect();
        $this->stockHelper->addStockStatusToSelect($select, $website);
    }

    /**
     * Detects whether product status should be shown
     *
     * @param EventObserver $observer
     * @return void
     */
    public function displayProductStatusInfo($observer)
    {
        $info = $observer->getEvent()->getStatus();
        $info->setDisplayStatus($this->stockConfiguration->isDisplayProductStockStatus());
    }
}
