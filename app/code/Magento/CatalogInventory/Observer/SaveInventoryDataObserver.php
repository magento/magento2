<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Observer;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\StockItemValidator;
use Magento\Framework\Event\Observer as EventObserver;

/**
 * Saves stock data from a product to the Stock Item
 *
 * @deprecated 100.2.0 Stock data should be processed using the module API
 * @see StockItemInterface when you want to change the stock data
 * @see StockStatusInterface when you want to read the stock data for representation layer (storefront)
 * @see StockItemRepositoryInterface::save as extension point for customization of saving process
 */
class SaveInventoryDataObserver implements ObserverInterface
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var StockItemValidator
     */
    private $stockItemValidator;

    /**
     * @var array
     */
    private $paramListToCheck = [
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
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemValidator $stockItemValidator
     */
    public function __construct(
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemValidator $stockItemValidator = null
    ) {
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemValidator = $stockItemValidator ?: ObjectManager::getInstance()->get(StockItemValidator::class);
    }

    /**
     * Saving product inventory data
     *
     * Takes data from the stock_data property of a product and sets it to Stock Item.
     * Validates and saves Stock Item object.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $stockItem = $this->getStockItemToBeUpdated($product);

        if ($product->getStockData() !== null) {
            $stockData = $this->getStockData($product);
            $stockItem->addData($stockData);
        }
        $this->stockItemValidator->validate($product, $stockItem);
        $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem);
    }

    /**
     * Return the stock item that needs to be updated
     *
     * @param Product $product
     * @return Item
     */
    private function getStockItemToBeUpdated(Product $product)
    {
        $extendedAttributes = $product->getExtensionAttributes();
        $stockItem = $extendedAttributes->getStockItem();

        if ($stockItem === null) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
        }
        return $stockItem;
    }

    /**
     * Get stock data
     *
     * @param Product $product
     * @return array
     */
    private function getStockData(Product $product)
    {
        $stockData = $product->getStockData();
        $stockData['product_id'] = $product->getId();

        if (!isset($stockData['website_id'])) {
            $stockData['website_id'] = $this->stockConfiguration->getDefaultScopeId();
        }
        $stockData['stock_id'] = $this->stockRegistry->getStock($stockData['website_id'])->getStockId();

        foreach ($this->paramListToCheck as $dataKey => $configPath) {
            if (null !== $product->getData($configPath['item']) && null === $product->getData($configPath['config'])) {
                $stockData[$dataKey] = false;
            }
        }

        $originalQty = $product->getData('stock_data/original_inventory_qty');
        if (strlen($originalQty) > 0) {
            $stockData['qty_correction'] = (isset($stockData['qty']) ? $stockData['qty'] : 0)
                - $originalQty;
        }
        return $stockData;
    }
}
