<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Plugin;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;
use Magento\InventoryLegacySynchronization\Model\Synchronize;

/**
 * Set to zero Qty and status to ‘Out of Stock’ for legacy CatalogInventory Stock Status and Stock Item DB tables,
 * if corresponding MSI SourceItem assigned to Default Source has been deleted
 */
class SetToZeroLegacyCatalogInventoryAtSourceItemsDeletePlugin
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
     * @var Synchronize
     */
    private $synchronize;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypeBySku
     * @param Synchronize $synchronize
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypeBySku,
        Synchronize $synchronize
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->getProductTypeBySku = $getProductTypeBySku;
        $this->synchronize = $synchronize;
    }

    /**
     * @param SourceItemsDeleteInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsDeleteInterface $subject, $result, array $sourceItems)
    {
        $sourceItemsData = [];
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }

            $sku = $sourceItem->getSku();

            $typeId = $this->getProductTypeBySku->execute([$sku])[$sku];
            if (false === $this->isSourceItemsAllowedForProductType->execute($typeId)) {
                continue;
            }

            $sourceItemData = $sourceItem->getData();
            $sourceItemData[SourceItemInterface::STATUS] = SourceItemInterface::STATUS_OUT_OF_STOCK;
            $sourceItemData[SourceItemInterface::QUANTITY] = 0;

            $sourceItemsData[] = $sourceItemData;
        }

        if (!empty($sourceItemsData)) {
            $this->synchronize->execute(Synchronize::MSI_TO_LEGACY, $sourceItemsData);
        }
    }
}
