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
use Magento\Inventory\Model\IsSourceItemsManagementAllowedForProductTypeInterface;
use Magento\InventoryCatalog\Model\UpdateSourceItemBasedOnLegacyStockItem;

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
     * @var IsSourceItemsManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsManagementAllowedForProductType;

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
     * @param IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        UpdateSourceItemBasedOnLegacyStockItem $updateSourceItemBasedOnLegacyStockItem,
        ResourceConnection $resourceConnection,
        IsSourceItemsManagementAllowedForProductTypeInterface $isSourceItemsManagementAllowedForProductType,
        ProductRepositoryInterface $productRepository
    ) {
        $this->updateSourceItemBasedOnLegacyStockItem = $updateSourceItemBasedOnLegacyStockItem;
        $this->resourceConnection = $resourceConnection;
        $this->isSourceItemsManagementAllowedForProductType = $isSourceItemsManagementAllowedForProductType;
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
            if ($this->isSourceItemsManagementAllowedForProductType->execute($typeId)) {
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
