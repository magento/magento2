<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\Inventory;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Api\Data\ProductInterface as Product;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Api\StockItemRepositoryInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Observer\ParentItemProcessorInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Process parent stock item
 */
class ParentItemProcessor implements ParentItemProcessorInterface
{
    /**
     * @var ChangeParentStockStatus
     */
    private $changeParentStockStatus;

    /**
     * @param Configurable $configurableType
     * @param StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory
     * @param StockItemRepositoryInterface $stockItemRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param ChangeParentStockStatus|null $changeParentStockStatus
     * @SuppressWarnings(PHPMD.UnusedFormalParameter) Deprecated dependencies
     */
    public function __construct(
        Configurable $configurableType,
        StockItemCriteriaInterfaceFactory $criteriaInterfaceFactory,
        StockItemRepositoryInterface $stockItemRepository,
        StockConfigurationInterface $stockConfiguration,
        ?ChangeParentStockStatus $changeParentStockStatus = null
    ) {
        $this->changeParentStockStatus = $changeParentStockStatus
            ?? ObjectManager::getInstance()->get(ChangeParentStockStatus::class);
    }

    /**
     * Process parent products
     *
     * @param Product $product
     * @return void
     */
    public function process(Product $product)
    {
        $this->changeParentStockStatus->execute([$product->getId()]);
    }
}
