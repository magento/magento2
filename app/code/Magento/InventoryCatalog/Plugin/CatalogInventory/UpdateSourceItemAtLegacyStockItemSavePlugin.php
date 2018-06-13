<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\CatalogInventory;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryCatalog\Model\GetDefaultSourceItemBySku;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryCatalog\Model\UpdateSourceItemBasedOnLegacyStockItem;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

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
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemManagementAllowedForProductType;

    /**
     * @var UpdateSourceItemBasedOnLegacyStockItem
     */
    private $updateSourceItemBasedOnLegacyStockItem;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypeBySku;

    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var GetDefaultSourceItemBySku
     */
    private $getDefaultSourceItemBySku;

    /**
     * @param UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem
     * @param ResourceConnection $resourceConnection
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypeBySku
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param GetDefaultSourceItemBySku $getDefaultSourceItemBySku
     */
    public function __construct(
        UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem,
        ResourceConnection $resourceConnection,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemManagementAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypeBySku,
        GetSkusByProductIdsInterface $getSkusByProductIds,
        GetDefaultSourceItemBySku $getDefaultSourceItemBySku
    ) {
        $this->updateSourceItemBasedOnLegacyStockItem = $updateSourceItemBasedOnLegacyStockItem;
        $this->resourceConnection = $resourceConnection;
        $this->isSourceItemManagementAllowedForProductType = $isSourceItemManagementAllowedForProductType;
        $this->getProductTypeBySku = $getProductTypeBySku;
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->getDefaultSourceItemBySku = $getDefaultSourceItemBySku;
    }

    /**
     * @param ItemResourceModel $subject
     * @param callable $proceed
     * @param AbstractModel $legacyStockItem
     * @return ItemResourceModel
     * @throws \Exception
     *
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
            if ($this->isSourceItemManagementAllowedForProductType->execute($typeId)) {
                if ($this->shouldAlignDefaultSourceWithLegacy($legacyStockItem)) {
                    $this->updateSourceItemBasedOnLegacyStockItem->execute($legacyStockItem);
                }
            }

            $connection->commit();

            return $subject;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    /**
     * Return true if legacy stock item should update default source (if existing)
     * @param Item $legacyStockItem
     * @return bool
     * @throws \Magento\Framework\Exception\InputException
     */
    private function shouldAlignDefaultSourceWithLegacy(Item $legacyStockItem): bool
    {
        $productSku = $this->getSkusByProductIds
            ->execute([$legacyStockItem->getProductId()])[$legacyStockItem->getProductId()];

        $result = $legacyStockItem->getIsInStock() ||
            ((float) $legacyStockItem->getQty() !== (float) 0) ||
            ($this->getDefaultSourceItemBySku->execute($productSku) !== null);

        return $result;
    }

    /**
     * @param Item $legacyStockItem
     * @return string
     * @throws \Magento\Framework\Exception\InputException
     */
    private function getTypeId(Item $legacyStockItem): string
    {
        $typeId = $legacyStockItem->getTypeId();
        if ($typeId === null) {
            $sku = $legacyStockItem->getSku();
            if ($sku === null) {
                $productId = $legacyStockItem->getProductId();
                $sku = $this->getSkusByProductIds->execute([$productId])[$productId];
            }
            $typeId = $this->getProductTypeBySku->execute([$sku])[$sku];
        }

        return $typeId;
    }
}
