<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockIndexInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Event\Observer as EventObserver;

class SaveInventoryDataObserver implements ObserverInterface
{
    /**
     * @var StockIndexInterface
     */
    protected $stockIndex;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var StockItemRepositoryInterface
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
     * @param StockIndexInterface $stockIndex
     * @param StockConfigurationInterface $stockConfiguration
     * @param StockRegistryInterface $stockRegistry
     * @param StockItemRepositoryInterface $stockItemRepository
     */
    public function __construct(
        StockIndexInterface $stockIndex,
        StockConfigurationInterface $stockConfiguration,
        StockRegistryInterface $stockRegistry,
        StockItemRepositoryInterface $stockItemRepository
    ) {
        $this->stockIndex = $stockIndex;
        $this->stockConfiguration = $stockConfiguration;
        $this->stockRegistry = $stockRegistry;
        $this->stockItemRepository = $stockItemRepository;
    }

    /**
     * Saving product inventory data. Product qty calculated dynamically.
     *
     * @param EventObserver $observer
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();

        if ($product->getStockData() === null) {
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
            $stockItemData['website_id'] = $this->stockConfiguration->getDefaultScopeId();
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
}
