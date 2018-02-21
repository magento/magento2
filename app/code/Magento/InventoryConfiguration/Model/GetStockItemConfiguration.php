<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

use Magento\InventoryCatalog\Model\GetProductIdsBySkusInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;

/**
 * Service to return  stock item configuration interface object
 */
class GetStockItemConfiguration implements GetStockItemConfigurationInterface
{
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var GetProductIdsBySkusInterface
     */
    private $getProductIdsBySkus;

    /**
     * @var StockItemRepository
     */
    private $stockItemRepository;

    /**
     * GetStockItemConfiguration constructor.
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockItemRepository $stockItemRepository
     * @param GetProductIdsBySkusInterface $getProductIdsBySkus
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepository $stockItemRepository,
        GetProductIdsBySkusInterface $getProductIdsBySkus
    ) {
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->getProductIdsBySkus = $getProductIdsBySkus;
    }

    /**
     * @param string $sku
     * @param int $stockId
     * @return StockItemConfigurationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(string $sku, int $stockId): StockItemConfigurationInterface
    {
        //below - old logic from getLegacyStockItem service
        $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];
        $searchCriteria = $this->stockItemCriteriaFactory->create();
        $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);
        $legacyStockItem = $this->stockItemRepository->getList($searchCriteria);

        return $legacyStockItem->getItems()[0];
    }
}
