<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\Framework\App\ObjectManager;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Model\LegacyCatalogInventorySynchronization\AlignLegacyCatalogInventoryByProducts;
use Magento\InventoryCatalog\Model\SourceItemsSaveSynchronization\SetDataToLegacyCatalogInventory;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Synchronization between legacy Stock Items and saved Source Items
 */
class SetDataToLegacyCatalogInventoryAtSourceItemsSavePlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var IsSourceItemManagementAllowedForProductTypeInterface
     */
    private $isSourceItemsAllowedForProductType;

    /**
     * @var GetProductTypesBySkusInterface
     */
    private $getProductTypeBySku;

    /**
     * @var AlignLegacyCatalogInventoryByProducts|null
     */
    private $alignLegacyCatalogInventoryByProducts;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypeBySku
     * @param SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory
     * @param AlignLegacyCatalogInventoryByProducts|null $alignLegacyCatalogInventoryByProducts
     * @SuppressWarnings(PHPMD.LongVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypeBySku,
        SetDataToLegacyCatalogInventory $setDataToLegacyCatalogInventory,
        AlignLegacyCatalogInventoryByProducts $alignLegacyCatalogInventoryByProducts = null
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->getProductTypeBySku = $getProductTypeBySku;
        $this->alignLegacyCatalogInventoryByProducts = $alignLegacyCatalogInventoryByProducts ?:
            ObjectManager::getInstance()->get(AlignLegacyCatalogInventoryByProducts::class);
    }

    /**
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $sourceItems): void
    {
        $skuToSynchronize = [];
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }

            $sku = $sourceItem->getSku();

            $productTypes = $this->getProductTypeBySku->execute([$sku]);
            if (isset($productTypes[$sku])) {
                $typeId = $productTypes[$sku];
            } else {
                continue;
            }

            if (false === $this->isSourceItemsAllowedForProductType->execute($typeId)) {
                continue;
            }

            $skuToSynchronize[] = $sourceItem->getSku();
        }

        $this->alignLegacyCatalogInventoryByProducts->execute($skuToSynchronize);
    }
}
