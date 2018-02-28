<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
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
     * @var StockCriteriaInterfaceFactory
     */
    private $legacyStockCriteriaFactory;

    /**
     * @var StockRepositoryInterface
     */
    private $legacyStockRepository;

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
     * @var StockConfigurationInterface
     */
    private $legacyStockConfiguration;

    /**
     * @var StockItemConfigurationFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @param GetStockItemDataInterface $getStockItemData
     * @param StockCriteriaInterfaceFactory $legacyStockCriteriaFactory
     * @param StockRepositoryInterface $legacyStockRepository
     * @param StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory
     * @param StockItemRepositoryInterface $legacyStockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param StockConfigurationInterface $legacyStockConfiguration
     * @param StockItemConfigurationFactory $stockItemConfigurationFactory
     */
    public function __construct(
        GetStockItemDataInterface $getStockItemData,
        StockCriteriaInterfaceFactory $legacyStockCriteriaFactory,
        StockRepositoryInterface $legacyStockRepository,
        StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory,
        StockItemRepositoryInterface $legacyStockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockConfigurationInterface $legacyStockConfiguration,
        StockItemConfigurationFactory $stockItemConfigurationFactory
    ) {
        $this->getStockItemData = $getStockItemData;
        $this->legacyStockCriteriaFactory = $legacyStockCriteriaFactory;
        $this->legacyStockRepository = $legacyStockRepository;
        $this->legacyStockItemCriteriaFactory = $legacyStockItemCriteriaFactory;
        $this->legacyStockItemRepository = $legacyStockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->legacyStockConfiguration = $legacyStockConfiguration;
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
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

        $legacyStockId = $this->resolveLegacyStockId();
        $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, $legacyStockId);

        $stockItemCollection = $this->legacyStockItemRepository->getList($searchCriteria);
        if ($stockItemCollection->getTotalCount() === 0) {
            // TODO:
            return \Magento\Framework\App\ObjectManager::getInstance()->create(StockItemInterface::class);
            throw new LocalizedException(__('Legacy stock item is not found'));
        }

        $stockItems = $stockItemCollection->getItems();
        $stockItem = reset($stockItems);
        return $stockItem;
    }

    /**
     * In legacy approach configuration has been saved only for default stock
     *
     * @return int
     */
    private function resolveLegacyStockId(): int
    {
        $scopeId = $this->legacyStockConfiguration->getDefaultScopeId();

        $criteria = $this->legacyStockCriteriaFactory->create();
        $criteria->setScopeFilter($scopeId);
        $collection = $this->legacyStockRepository->getList($criteria);

        $legacyStock = current($collection->getItems());
        return (int)$legacyStock->getStockId();
    }
}
