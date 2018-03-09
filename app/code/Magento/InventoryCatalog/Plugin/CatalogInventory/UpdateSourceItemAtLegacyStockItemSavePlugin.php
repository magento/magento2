<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryCatalog\Model\UpdateSourceItemBasedOnLegacyStockItem;
use Magento\InventoryConfiguration\Model\IsSourceItemsAllowedForProductTypeInterface;

/**
 * Class provides around Plugin on \Magento\CatalogInventory\Model\ResourceModel\Stock\Item::save
 * to update data in Inventory source item based on legacy Stock Item data
 */
class UpdateSourceItemAtLegacyStockItemSavePlugin
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var IsSourceItemsAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @var  ProductRepositoryInterface
     * */
    private $productRepository;

    /**
     * @var UpdateSourceItemBasedOnLegacyStockItem
     */
    private $updateSourceItemBasedOnLegacyStockItem;

    /**
     * @param UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem
     * @param ResourceConnection $resourceConnection
     * @param IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem,
        ResourceConnection $resourceConnection,
        IsSourceItemsAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        ProductRepositoryInterface $productRepository
    ) {
        $this->updateSourceItemBasedOnLegacyStockItem = $updateSourceItemBasedOnLegacyStockItem;
        $this->resourceConnection = $resourceConnection;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->productRepository = $productRepository;
    }

    /**
     * @param ItemResourceModel $subject
     * @param callable $proceed
     * @param AbstractModel $legacyStockItem
     *
     * @return ItemResourceModel
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(ItemResourceModel $subject, callable $proceed, AbstractModel $legacyStockItem)
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            // need to save configuration
            $proceed($legacyStockItem);

            $typeId = $this->getTypeId($legacyStockItem);
            if ($this->isSourceItemsAllowedForProductType->execute($typeId)) {
                $this->updateSourceItemBasedOnLegacyStockItem->execute($legacyStockItem);
            }

            $connection->commit();

            return $subject;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param $legacyStockItem
     *
     * @return string
     */
    private function getTypeId($legacyStockItem): string
    {
        $typeId = $legacyStockItem->getTypeId();
        if (null === $typeId) {
            $product = $this->productRepository->getById($legacyStockItem->getProductId());
            $typeId = $product->getTypeId();
        }

        return $typeId;
    }
}
