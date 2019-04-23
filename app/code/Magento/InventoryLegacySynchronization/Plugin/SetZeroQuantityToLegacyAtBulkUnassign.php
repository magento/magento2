<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Magento\InventoryLegacySynchronization\Plugin;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryCatalogApi\Api\BulkSourceUnassignInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryLegacySynchronization\Model\Synchronize;

/**
 * Set zero quantity plugin on bulk default source unassign
 */
class SetZeroQuantityToLegacyAtBulkUnassign
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var Synchronize
     */
    private $synchronize;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param Synchronize $synchronize
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        Synchronize $synchronize
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->synchronize = $synchronize;
    }

    /**
     * @param BulkSourceUnassignInterface $subject
     * @param int $result
     * @param array $skus
     * @param array $sourceCodes
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(
        BulkSourceUnassignInterface $subject,
        int $result,
        array $skus,
        array $sourceCodes
    ): int {
        $defaultSourceCode = $this->defaultSourceProvider->getCode();

        if (\in_array($this->defaultSourceProvider->getCode(), $sourceCodes, true)) {
            $sourceItemsData = [];
            foreach ($skus as $sku) {
                $sourceItemsData[] = [
                    SourceItemInterface::QUANTITY => 0.0,
                    SourceItemInterface::STATUS => SourceItemInterface::STATUS_OUT_OF_STOCK,
                    SourceItemInterface::SOURCE_CODE => $defaultSourceCode,
                    SourceItemInterface::SKU => $sku,
                ];
            }

            $this->synchronize->execute(Synchronize::MSI_TO_LEGACY, $sourceItemsData);
        }

        return $result;
    }
}