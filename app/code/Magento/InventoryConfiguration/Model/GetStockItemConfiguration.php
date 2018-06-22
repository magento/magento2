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
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Model\IsProductAssignedToStockInterface;
use Magento\InventoryCatalogApi\Model\GetProductIdsBySkusInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @inheritdoc
 */
class GetStockItemConfiguration implements GetStockItemConfigurationInterface
{
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
     * @var IsProductAssignedToStockInterface
     */
    private $isProductAssignedToStock;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var IsSourceItemManagementAllowedForSku
     */
    private $isSourceItemManagementAllowedForSku;

    /**
     * @param StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory
     * @param StockItemRepositoryInterface $legacyStockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     * @param StockItemConfigurationFactory $stockItemConfigurationFactory
     * @param IsProductAssignedToStockInterface $isProductAssignedToStock
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param IsSourceItemManagementAllowedForSku $isSourceItemManagementAllowedForSku
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $legacyStockItemCriteriaFactory,
        StockItemRepositoryInterface $legacyStockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus,
        StockItemConfigurationFactory $stockItemConfigurationFactory,
        IsProductAssignedToStockInterface $isProductAssignedToStock,
        DefaultStockProviderInterface $defaultStockProvider,
        IsSourceItemManagementAllowedForSku $isSourceItemManagementAllowedForSku
    ) {
        $this->legacyStockItemCriteriaFactory = $legacyStockItemCriteriaFactory;
        $this->legacyStockItemRepository = $legacyStockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
        $this->isProductAssignedToStock = $isProductAssignedToStock;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->isSourceItemManagementAllowedForSku = $isSourceItemManagementAllowedForSku;
    }

    /**
     * @inheritdoc
     */
    public function execute(string $sku, int $stockId)
    {
        if ($this->defaultStockProvider->getId() !== $stockId
            && true === $this->isSourceItemManagementAllowedForSku->execute($sku)
            && false === $this->isProductAssignedToStock->execute($sku, $stockId)) {
            throw new NoSuchEntityException(
                __('The requested sku is not assigned to given stock.')
            );
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
     */
    private function getLegacyStockItem(string $sku): StockItemInterface
    {
        $searchCriteria = $this->legacyStockItemCriteriaFactory->create();

        try {
            $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];
        } catch (NoSuchEntityException $skuNotFoundInCatalog) {
            $stockItem = ObjectManager::getInstance()->create(StockItemInterface::class);
            $stockItem->setManageStock(true);  // Make possible to Manage Stock for Products removed from Catalog
            return $stockItem;
        }
        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);

        // Stock::DEFAULT_STOCK_ID is used until we have proper multi-stock item configuration
        $searchCriteria->addFilter(StockItemInterface::STOCK_ID, StockItemInterface::STOCK_ID, Stock::DEFAULT_STOCK_ID);

        $stockItemCollection = $this->legacyStockItemRepository->getList($searchCriteria);
        if ($stockItemCollection->getTotalCount() === 0) {
            return ObjectManager::getInstance()->create(StockItemInterface::class);
        }

        $stockItems = $stockItemCollection->getItems();
        $stockItem = reset($stockItems);
        return $stockItem;
    }
}
