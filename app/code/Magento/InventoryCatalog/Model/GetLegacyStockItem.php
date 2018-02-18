<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\Exception\LocalizedException;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;

/**
 * Service GetLegacyStockItem returns old inventory item data
 * @package Magento\InventoryCatalog\Model
 */
class GetLegacyStockItem
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
     * @var Item[]
     */
    private $item;

    /**
     * GetLegacyStockItem constructor.
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
     * @return Item|null
     * @throws LocalizedException
     */
    public function execute(string $sku)
    {
        if (!$this->item) {
            $productId = $this->getProductIdsBySkus->execute([$sku])[$sku];

            $searchCriteria = $this->stockItemCriteriaFactory->create();
            $searchCriteria->addFilter(StockItemInterface::PRODUCT_ID, StockItemInterface::PRODUCT_ID, $productId);

            $legacyStockItem = $this->stockItemRepository->getList($searchCriteria);
            $this->item = $legacyStockItem->getItems()[0];
        }

        return $this->item;
    }
}
