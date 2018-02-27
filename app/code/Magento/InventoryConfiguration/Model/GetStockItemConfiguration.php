<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\InventorySales\Model\GetStockItemDataInterface;

/**
 * @inheritdoc
 */
class GetStockItemConfiguration implements GetStockItemConfigurationInterface
{
    /**
     * @var StockItemConfigurationFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var StockItemRepositoryInterface
     */
    private $stockItemRepository;

    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @param StockItemConfigurationFactory $stockItemConfigurationFactory
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param StockConfigurationInterface $stockConfiguration
     * @param GetStockItemDataInterface $getStockItemData
     */
    public function __construct(
        StockItemConfigurationFactory $stockItemConfigurationFactory,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepositoryInterface $stockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockConfigurationInterface $stockConfiguration,
        GetStockItemDataInterface $getStockItemData
    ) {
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->stockConfiguration = $stockConfiguration;
        $this->getStockItemData = $getStockItemData;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId)
    {
        $stockItemData = $this->getStockItemData->execute($sku, $stockId);
        if (null === $stockItemData) {
            // Sku is not assigned to Stock
            return null;
        }

        return $this->stockItemConfigurationFactory->create(
            [
                'stockItem' => $this->getLegacyStockItem($sku),
            ]
        );
    }

    /**
     * @param string $sku
     * @return StockItemInterface
     * @throws \Exception
     */
    private function getLegacyStockItem(string $sku)
    {
        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];
        $searchCriteria = $this->stockItemCriteriaFactory->create();
        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);

        // TODO: In legacy approach configuration has been saved only for default stock
        $stockId = 1;
        $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, $stockId);
        $stockItemCollection = $this->stockItemRepository->getList($searchCriteria);

        if ($stockItemCollection->getTotalCount() === 0) {
            // TODO:
            throw new \Exception('Legacy stock item is not found');
        }

        $stockItems = $stockItemCollection->getItems();
        $stockItem = reset($stockItems);
        return $stockItem;
    }
}
