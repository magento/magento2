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
 * @package     Magento_CatalogInventory
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Catalog inventory module observer
 */
namespace Magento\CatalogInventory\Model;

use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Event\Observer as EventObserver;
use Magento\Sales\Model\Quote\Item as QuoteItem;

class Observer
{
    /**
     * @var Item[]
     */
    protected $_itemsForReindex = array();

    /**
     * Array, indexed by product's id to contain stockItems of already loaded products
     * Some kind of singleton for product's stock item
     *
     * @var array
     */
    protected $_stockItemsArray = array();

    /**
     * Catalog inventory data
     *
     * @var \Magento\CatalogInventory\Helper\Data
     */
    protected $_catalogInventoryData;

    /**
     * Stock item factory
     *
     * @var \Magento\CatalogInventory\Model\Stock\ItemFactory
     */
    protected $_stockItemFactory;

    /**
     * Stock model factory
     *
     * @var StockFactory
     */
    protected $_stockFactory;

    /**
     * Stock status factory
     *
     * @var \Magento\CatalogInventory\Model\Stock\StatusFactory
     */
    protected $_stockStatusFactory;

    /**
     * Construct
     *
     * @var \Magento\Index\Model\Indexer
     */
    protected $_indexer;

    /**
     * @var Stock
     */
    protected $_stock;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\Status
     */
    protected $_stockStatus;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Stock
     */
    protected $_resourceStock;

    /**
     * @var \Magento\CatalogInventory\Model\Resource\Indexer\Stock
     */
    protected $_resourceIndexerStock;

    /**
     * @var \Magento\Catalog\Model\ProductTypes\ConfigInterface
     */
    protected $typeConfig;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    protected $_priceIndexer;

    /**
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param Resource\Indexer\Stock $resourceIndexerStock
     * @param Resource\Stock $resourceStock
     * @param \Magento\Index\Model\Indexer $indexer
     * @param Stock $stock
     * @param Stock\Status $stockStatus
     * @param \Magento\CatalogInventory\Helper\Data $catalogInventoryData
     * @param Stock\ItemFactory $stockItemFactory
     * @param StockFactory $stockFactory
     * @param Stock\StatusFactory $stockStatusFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Magento\CatalogInventory\Model\Resource\Indexer\Stock $resourceIndexerStock,
        \Magento\CatalogInventory\Model\Resource\Stock $resourceStock,
        \Magento\Index\Model\Indexer $indexer,
        Stock $stock,
        \Magento\CatalogInventory\Model\Stock\Status $stockStatus,
        \Magento\CatalogInventory\Helper\Data $catalogInventoryData,
        \Magento\CatalogInventory\Model\Stock\ItemFactory $stockItemFactory,
        StockFactory $stockFactory,
        \Magento\CatalogInventory\Model\Stock\StatusFactory $stockStatusFactory
    ) {
        $this->_priceIndexer = $priceIndexer;
        $this->_resourceIndexerStock = $resourceIndexerStock;
        $this->_resourceStock = $resourceStock;
        $this->_indexer = $indexer;
        $this->_stock = $stock;
        $this->_stockStatus = $stockStatus;
        $this->_catalogInventoryData = $catalogInventoryData;
        $this->_stockItemFactory = $stockItemFactory;
        $this->_stockFactory = $stockFactory;
        $this->_stockStatusFactory = $stockStatusFactory;
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
            $productId = intval($product->getId());
            if (!isset($this->_stockItemsArray[$productId])) {
                $this->_stockItemsArray[$productId] = $this->_stockItemFactory->create();
            }
            $productStockItem = $this->_stockItemsArray[$productId];
            $productStockItem->assignProduct($product);
        }
        return $this;
    }

    /**
     * Remove stock information from static variable
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function removeInventoryData($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product instanceof \Magento\Catalog\Model\Product && $product->getId() && isset(
            $this->_stockItemsArray[$product->getId()]
        )
        ) {
            unset($this->_stockItemsArray[$product->getId()]);
        }
        return $this;
    }

    /**
     * Add information about producs stock status to collection
     * Used in for product collection after load
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function addStockStatusToCollection($observer)
    {
        $productCollection = $observer->getEvent()->getCollection();
        if ($productCollection->hasFlag('require_stock_items')) {
            $this->_stockFactory->create()->addItemsToProducts($productCollection);
        } else {
            $this->_stockStatusFactory->create()->addStockStatusToProducts($productCollection);
        }
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
        $this->_stockFactory->create()->addItemsToProducts($productCollection);
        return $this;
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
                $this->_stockStatus->updateStatus($product->getId());
            }
            return $this;
        }

        $item = $product->getStockItem();
        if (!$item) {
            $item = $this->_stockItemFactory->create();
        }
        $this->_prepareItemForSave($item, $product);
        $item->save();
        return $this;
    }

    /**
     * Prepare stock item data for save
     *
     * @param Item $item
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    protected function _prepareItemForSave($item, $product)
    {
        $item->addData(
            $product->getStockData()
        )->setProduct(
            $product
        )->setProductId(
            $product->getId()
        )->setStockId(
            $item->getStockId()
        );
        if (!is_null(
            $product->getData('stock_data/min_qty')
        ) && is_null(
            $product->getData('stock_data/use_config_min_qty')
        )
        ) {
            $item->setData('use_config_min_qty', false);
        }
        if (!is_null(
            $product->getData('stock_data/min_sale_qty')
        ) && is_null(
            $product->getData('stock_data/use_config_min_sale_qty')
        )
        ) {
            $item->setData('use_config_min_sale_qty', false);
        }
        if (!is_null(
            $product->getData('stock_data/max_sale_qty')
        ) && is_null(
            $product->getData('stock_data/use_config_max_sale_qty')
        )
        ) {
            $item->setData('use_config_max_sale_qty', false);
        }
        if (!is_null(
            $product->getData('stock_data/backorders')
        ) && is_null(
            $product->getData('stock_data/use_config_backorders')
        )
        ) {
            $item->setData('use_config_backorders', false);
        }
        if (!is_null(
            $product->getData('stock_data/notify_stock_qty')
        ) && is_null(
            $product->getData('stock_data/use_config_notify_stock_qty')
        )
        ) {
            $item->setData('use_config_notify_stock_qty', false);
        }
        $originalQty = $product->getData('stock_data/original_inventory_qty');
        if (strlen($originalQty) > 0) {
            $item->setQtyCorrection($item->getQty() - $originalQty);
        }
        if (!is_null(
            $product->getData('stock_data/enable_qty_increments')
        ) && is_null(
            $product->getData('stock_data/use_config_enable_qty_inc')
        )
        ) {
            $item->setData('use_config_enable_qty_inc', false);
        }
        if (!is_null(
            $product->getData('stock_data/qty_increments')
        ) && is_null(
            $product->getData('stock_data/use_config_qty_increments')
        )
        ) {
            $item->setData('use_config_qty_increments', false);
        }
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
     * Subtract quote items qtys from stock items related with quote items products.
     *
     * Used before order placing to make order save/place transaction smaller
     * Also called after every successful order placement to ensure subtraction of inventory
     *
     * @param EventObserver $observer
     * @return $this|void
     */
    public function subtractQuoteInventory(EventObserver $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        // Maybe we've already processed this quote in some event during order placement
        // e.g. call in event 'sales_model_service_quote_submit_before' and later in 'checkout_submit_all_after'
        if ($quote->getInventoryProcessed()) {
            return;
        }
        $items = $this->_getProductsQty($quote->getAllItems());

        /**
         * Remember items
         */
        $this->_itemsForReindex = $this->_stock->registerProductsSale($items);

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
        $this->_stock->revertProductsSale($items);

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
    protected function _addItemToQtyArray($quoteItem, &$items)
    {
        $productId = $quoteItem->getProductId();
        if (!$productId) {
            return;
        }
        if (isset($items[$productId])) {
            $items[$productId]['qty'] += $quoteItem->getTotalQty();
        } else {
            $stockItem = null;
            if ($quoteItem->getProduct()) {
                $stockItem = $quoteItem->getProduct()->getStockItem();
            }
            $items[$productId] = array('item' => $stockItem, 'qty' => $quoteItem->getTotalQty());
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
        $items = array();
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
     * @return $this
     */
    public function reindexQuoteInventory($observer)
    {
        // Reindex quote ids
        $quote = $observer->getEvent()->getQuote();
        $productIds = array();
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
            $this->_resourceIndexerStock->reindexProducts($productIds);
        }

        // Reindex previously remembered items
        $productIds = array();
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }

        if (!empty($productIds)) {
            $this->_priceIndexer->reindexList($productIds);
        }

        $this->_itemsForReindex = array();
        // Clear list of remembered items - we don't need it anymore

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
        $items = array();
        foreach ($creditmemo->getAllItems() as $item) {
            /* @var $item \Magento\Sales\Model\Order\Creditmemo\Item */
            $return = false;
            if ($item->hasBackToStock()) {
                if ($item->getBackToStock() && $item->getQty()) {
                    $return = true;
                }
            } elseif ($this->_catalogInventoryData->isAutoReturnEnabled()) {
                $return = true;
            }
            if ($return) {
                $parentOrderId = $item->getOrderItem()->getParentItemId();
                /* @var $parentItem \Magento\Sales\Model\Order\Creditmemo\Item */
                $parentItem = $parentOrderId ? $creditmemo->getItemByOrderId($parentOrderId) : false;
                $qty = $parentItem ? $parentItem->getQty() * $item->getQty() : $item->getQty();
                if (isset($items[$item->getProductId()])) {
                    $items[$item->getProductId()]['qty'] += $qty;
                } else {
                    $items[$item->getProductId()] = array('qty' => $qty, 'item' => null);
                }
            }
        }
        $this->_stock->revertProductsSale($items);
    }

    /**
     * Cancel order item
     *
     * @param   EventObserver $observer
     * @return  $this
     */
    public function cancelOrderItem($observer)
    {
        $item = $observer->getEvent()->getItem();

        $children = $item->getChildrenItems();
        $qty = $item->getQtyOrdered() - max($item->getQtyShipped(), $item->getQtyInvoiced()) - $item->getQtyCanceled();

        if ($item->getId() && ($productId = $item->getProductId()) && empty($children) && $qty) {
            $this->_stock->backItemQty($productId, $qty);
        }

        return $this;
    }

    /**
     * Update items stock status and low stock date.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function updateItemsStockUponConfigChange($observer)
    {
        $this->_resourceStock->updateSetOutOfStock();
        $this->_resourceStock->updateSetInStock();
        $this->_resourceStock->updateLowStockDate();
        return $this;
    }

    /**
     * Update Only product status observer
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function productStatusUpdate(EventObserver $observer)
    {
        $productId = $observer->getEvent()->getProductId();
        $this->_stockStatus->updateStatus($productId);
        return $this;
    }

    /**
     * Catalog Product website update
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function catalogProductWebsiteUpdate(EventObserver $observer)
    {
        $websiteIds = $observer->getEvent()->getWebsiteIds();
        $productIds = $observer->getEvent()->getProductIds();

        foreach ($websiteIds as $websiteId) {
            foreach ($productIds as $productId) {
                $this->_stockStatus->updateStatus($productId, null, $websiteId);
            }
        }

        return $this;
    }

    /**
     * Add stock status to prepare index select
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function addStockStatusToPrepareIndexSelect(EventObserver $observer)
    {
        $website = $observer->getEvent()->getWebsite();
        $select = $observer->getEvent()->getSelect();

        $this->_stockStatus->addStockStatusToSelect($select, $website);

        return $this;
    }

    /**
     * Add stock status limitation to catalog product price index select object
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function prepareCatalogProductIndexSelect(EventObserver $observer)
    {
        $select = $observer->getEvent()->getSelect();
        $entity = $observer->getEvent()->getEntityField();
        $website = $observer->getEvent()->getWebsiteField();

        $this->_stockStatus->prepareCatalogProductIndexSelect($select, $entity, $website);

        return $this;
    }

    /**
     * Reindex all events of product-massAction type
     *
     * @param EventObserver $observer
     * @return void
     */
    public function reindexProductsMassAction($observer)
    {
        $this->_indexer->indexEvents(
            \Magento\Catalog\Model\Product::ENTITY,
            \Magento\Index\Model\Event::TYPE_MASS_ACTION
        );
    }

    /**
     * Detects whether product status should be shown
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function displayProductStatusInfo($observer)
    {
        $info = $observer->getEvent()->getStatus();
        $info->setDisplayStatus($this->_catalogInventoryData->isDisplayProductStockStatus());
        return $this;
    }
}
