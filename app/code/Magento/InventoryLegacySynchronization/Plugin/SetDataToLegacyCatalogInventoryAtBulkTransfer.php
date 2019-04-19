<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Plugin;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryLegacySynchronization\Model\GetDefaultSourceItemsBySkus;
use Magento\InventoryLegacySynchronization\Model\Synchronize;

class SetDataToLegacyCatalogInventoryAtBulkTransfer
{
    /**
     * @var GetDefaultSourceItemsBySkus
     */
    private $getDefaultSourceItemsBySkus;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var Synchronize
     */
    private $synchronize;

    /**
     * @param GetDefaultSourceItemsBySkus $getDefaultSourceItemsBySkus
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param Synchronize $synchronize
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        GetDefaultSourceItemsBySkus $getDefaultSourceItemsBySkus,
        DefaultSourceProviderInterface $defaultSourceProvider,
        Synchronize $synchronize
    ) {
        $this->getDefaultSourceItemsBySkus = $getDefaultSourceItemsBySkus;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->synchronize = $synchronize;
    }

    /**
     * @param BulkInventoryTransferInterface $subject
     * @param $result
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignFromOrigin
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        BulkInventoryTransferInterface $subject,
        bool $result,
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignFromOrigin
    ): bool {
        $defaultSourceCode = $this->defaultSourceProvider->getCode();

        $sourceItemsData = [];
        if ($unassignFromOrigin && ($originSource === $defaultSourceCode)) {
            foreach ($skus as $sku) {
                $sourceItemsData[] = [
                    SourceItemInterface::QUANTITY => 0.0,
                    SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
                    SourceItemInterface::SOURCE_CODE => $defaultSourceCode,
                    SourceItemInterface::SKU => $sku
                ];
            }
        } elseif (($destinationSource === $defaultSourceCode) || ($originSource === $defaultSourceCode)) {
            $sourceItems = $this->getDefaultSourceItemsBySkus->execute($skus);
            foreach ($sourceItems as $sourceItem) {
                $sourceItemsData[] = [
                    SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                    SourceItemInterface::STATUS => $sourceItem->getStatus(),
                    SourceItemInterface::SOURCE_CODE => $defaultSourceCode,
                    SourceItemInterface::SKU => $sourceItem->getSku()
                ];
            }
        }

        if (!empty($sourceItemsData)) {
            $this->synchronize->execute(Synchronize::MSI_TO_LEGACY, $sourceItemsData);
        }

        return $result;
    }
}
