<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLegacySynchronization\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalog\Model\GetDefaultSourceItemBySku;
use Magento\InventoryCatalog\Model\ResourceModel\TransferInventoryPartially;
use Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryLegacySynchronization\Model\Synchronize;

class SetDataToLegacyCatalogInventoryAtTransferInventoryPartial
{
    /**
     * @var Synchronize
     */
    private $synchronize;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var GetDefaultSourceItemBySku
     */
    private $getDefaultSourceItemBySku;

    /**
     * @param Synchronize $synchronize
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param GetDefaultSourceItemBySku $getDefaultSourceItemBySku
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Synchronize $synchronize,
        DefaultSourceProviderInterface $defaultSourceProvider,
        GetDefaultSourceItemBySku $getDefaultSourceItemBySku
    ) {
        $this->synchronize = $synchronize;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->getDefaultSourceItemBySku = $getDefaultSourceItemBySku;
    }

    /**
     * @param TransferInventoryPartially $subject
     * @param $result
     * @param PartialInventoryTransferItemInterface $transfer
     * @param string $originSourceCode
     * @param string $destinationSourceCode
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function afterExecute(
        TransferInventoryPartially $subject,
        $result,
        PartialInventoryTransferItemInterface $transfer,
        string $originSourceCode,
        string $destinationSourceCode
    ): void {
        $defaultSourceCode = $this->defaultSourceProvider->getCode();

        if ($originSourceCode === $defaultSourceCode || $destinationSourceCode === $defaultSourceCode) {
            $sourceItem = $this->getDefaultSourceItemBySku->execute($transfer->getSku());
            if ($sourceItem !== null) {
                $this->synchronize->execute(
                    Synchronize::MSI_TO_LEGACY,
                    [
                        SourceItemInterface::QUANTITY => $sourceItem->getQuantity(),
                        SourceItemInterface::SKU => $transfer->getSku(),
                        SourceItemInterface::SOURCE_CODE => $defaultSourceCode,
                        SourceItemInterface::STATUS => $sourceItem->getStatus()
                    ]
                );
            }
        }
    }
}
