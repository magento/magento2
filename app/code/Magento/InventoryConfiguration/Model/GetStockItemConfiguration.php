<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;
use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySales\Model\GetStockItemDataInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * @inheritdoc
 */
class GetStockItemConfiguration implements GetStockItemConfigurationInterface
{
    /**
     * @var GetStockItemDataInterface
     */
    private $getStockItemData;

    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $legacyStockItemCriteriaFactory;

    /**
     * @var StockItemRepositoryInterface
     */
    private $legacyStockItemRepository;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var StockItemConfigurationFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory,
        StockItemRepositoryInterface $legacyStockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockItemConfigurationFactory $stockItemConfigurationFactory,
        DefaultStockProviderInterface $defaultStockProvider
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->legacyStockItemCriteriaFactory = $legacyStockItemCriteriaFactory;
        $this->legacyStockItemRepository = $legacyStockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
        $this->defaultStockProvider = $defaultStockProvider;
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
     * @throws LocalizedException
     */
    private function getLegacyStockItem(string $sku): StockItemInterface
    {
        $searchCriteria = $this->legacyStockItemCriteriaFactory->create();

        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];
        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);

        // TODO We use $legacyStockId until we have proper multi-stock item configuration
        $legacyStockId = $this->defaultStockProvider->getId();

        $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, $legacyStockId);

        $stockItemCollection = $this->legacyStockItemRepository->getList($searchCriteria);
        if ($stockItemCollection->getTotalCount() === 0) {
            // TODO:
            return \Magento\Framework\App\ObjectManager::getInstance()->create(StockItemInterface::class);
            #throw new LocalizedException(__('Legacy stock item is not found'));
        }

        $stockItems = $stockItemCollection->getItems();
        $stockItem = reset($stockItems);
        return $stockItem;
    }
}
