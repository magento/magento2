<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Model\GetProductTypesBySkusInterface;
use Magento\InventoryCatalogApi\Model\SourceItemsSaveSynchronizationInterface;
use Magento\InventoryConfigurationApi\Model\IsSourceItemManagementAllowedForProductTypeInterface;

/**
 * Synchronization between legacy Stock Item with Source Item
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
     * @var SourceItemsSaveSynchronizationInterface
     */
    private $sourceItemSynchronization;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType
     * @param GetProductTypesBySkusInterface $getProductTypeBySku
     * @param SourceItemsSaveSynchronizationInterface $sourceItemsSaveSynchronization
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        IsSourceItemManagementAllowedForProductTypeInterface $isSourceItemsAllowedForProductType,
        GetProductTypesBySkusInterface $getProductTypeBySku,
        SourceItemsSaveSynchronizationInterface $sourceItemsSaveSynchronization
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->isSourceItemsAllowedForProductType = $isSourceItemsAllowedForProductType;
        $this->getProductTypeBySku = $getProductTypeBySku;
        $this->sourceItemSynchronization = $sourceItemsSaveSynchronization;
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
        $sourceItemsForSynchronization = [];
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }

            $sku = $sourceItem->getSku();
            $typeId = $this->getProductTypeBySku->execute([$sku])[$sku];

            if (false === $this->isSourceItemsAllowedForProductType->execute($typeId)) {
                continue;
            }

            $sourceItemsForSynchronization[] = $sourceItem;
        }

        $this->sourceItemSynchronization->execute($sourceItemsForSynchronization);
    }
}
