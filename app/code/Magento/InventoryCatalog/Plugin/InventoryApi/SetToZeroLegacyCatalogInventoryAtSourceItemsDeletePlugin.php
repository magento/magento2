<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetToZeroLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetToZeroLegacyStockStatus;

/**
 * Plugin help to zeroing related entries from the legacy catalog inventory tables cataloginventory_stock_status and
 * cataloginventory_stock_item if deleted source item which is related to default source
 */
class SetToZeroLegacyCatalogInventoryAtSourceItemsDeletePlugin
{
    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var SetToZeroLegacyStockItem
     */
    private $setToZeroLegacyStockItem;

    /**
     * @var SetToZeroLegacyStockStatus
     */
    private $setToZeroLegacyStockStatus;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetToZeroLegacyStockItem $setToZeroLegacyStockItem
     * @param SetToZeroLegacyStockStatus $setToZeroLegacyStockStatus
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetToZeroLegacyStockItem $setToZeroLegacyStockItem,
        SetToZeroLegacyStockStatus $setToZeroLegacyStockStatus
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setToZeroLegacyStockItem = $setToZeroLegacyStockItem;
        $this->setToZeroLegacyStockStatus = $setToZeroLegacyStockStatus;
    }

    /**
     * @param SourceItemsDeleteInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @see SourceItemsDeleteInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsDeleteInterface $subject, $result, array $sourceItems)
    {
        foreach ($sourceItems as $sourceItem) {
            if ((int)$sourceItem->getSourceId() !== $this->defaultSourceProvider->getId()) {
                continue;
            }
            $this->setToZeroLegacyStockItem->execute($sourceItem->getSku());
            $this->setToZeroLegacyStockStatus->execute($sourceItem->getSku());
        }
    }
}
