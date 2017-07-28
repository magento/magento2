<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * This observer prepares stock data for saving by combining stock data from the stock data property
 * and quantity_and_stock_status attribute and setting it to the single point represented by stock data property.
 *
 * @deprecated 2.2.0 Stock data should be processed using the module API
 * @see StockItemInterface when you want to change the stock data
 * @see StockStatusInterface when you want to read the stock data for representation layer (storefront)
 * @see StockItemRepositoryInterface::save as extension point for customization of saving process
 * @since 2.2.0
 */
class ProcessInventoryDataObserver implements ObserverInterface
{
    /**
     * Stock Registry
     *
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     * @since 2.2.0
     */
    private $stockRegistry;

    /**
     * Construct
     *
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @since 2.2.0
     */
    public function __construct(
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
    ) {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Process stock item data
     *
     * @param EventObserver $observer
     * @return void
     * @since 2.2.0
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->processStockData($product);
    }

    /**
     * Process stock item data
     *
     * Synchronize stock data from different sources (stock_data, quantity_and_stock_status, StockItem) and set it to
     * stock_data key
     *
     * @param Product $product
     * @return void
     * @since 2.2.0
     */
    private function processStockData(Product $product)
    {
        /** @var Item $stockItem */
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());

        $quantityAndStockStatus = $product->getData('quantity_and_stock_status');
        if (is_array($quantityAndStockStatus)) {
            $quantityAndStockStatus = $this->prepareQuantityAndStockStatus($stockItem, $quantityAndStockStatus);

            if ($quantityAndStockStatus) {
                $this->setStockDataToProduct($product, $stockItem, $quantityAndStockStatus);
            }
        }
        $product->unsetData('quantity_and_stock_status');
    }

    /**
     * Prepare quantity_and_stock_status data
     *
     * Remove not changed values from quantity_and_stock_status data
     * Set null value for qty if passed empty
     *
     * @param StockItemInterface $stockItem
     * @param array $quantityAndStockStatus
     * @return array
     * @since 2.2.0
     */
    private function prepareQuantityAndStockStatus(StockItemInterface $stockItem, array $quantityAndStockStatus)
    {
        $stockItemId = $stockItem->getItemId();

        if (null !== $stockItemId) {
            if (isset($quantityAndStockStatus['is_in_stock'])
                && $stockItem->getIsInStock() == $quantityAndStockStatus['is_in_stock']
            ) {
                unset($quantityAndStockStatus['is_in_stock']);
            }
            if (isset($quantityAndStockStatus['qty'])
                && $stockItem->getQty() == $quantityAndStockStatus['qty']
            ) {
                unset($quantityAndStockStatus['qty']);
            }
        }

        if (array_key_exists('qty', $quantityAndStockStatus) && $quantityAndStockStatus['qty'] === '') {
            $quantityAndStockStatus['qty'] = null;
        }
        return $quantityAndStockStatus;
    }

    /**
     * Set stock data to product
     *
     * First of all we take stock_data data, replace it from quantity_and_stock_status data (if was changed) and finally
     * replace it with data from Stock Item object (only if Stock Item was changed)
     *
     * @param Product $product
     * @param Item $stockItem
     * @param array $quantityAndStockStatus
     * @return void
     * @since 2.2.0
     */
    private function setStockDataToProduct(Product $product, Item $stockItem, array $quantityAndStockStatus)
    {
        $stockData = array_replace((array)$product->getData('stock_data'), $quantityAndStockStatus);
        if ($stockItem->hasDataChanges()) {
            $stockData = array_replace($stockData, $stockItem->getData());
        }
        $product->setData('stock_data', $stockData);
    }
}
