<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\CatalogInventory\Model\Stock\Status;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockStatus;
use Psr\Log\LoggerInterface;

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
     * @var SetDataToLegacyStockItem
     */
    private $setDataToLegacyStockItem;

    /**
     * @var SetDataToLegacyStockStatus
     */
    private $setDataToLegacyStockStatus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param SetDataToLegacyStockStatus $setDataToLegacyStockStatus
     * @param LoggerInterface $logger
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        SetDataToLegacyStockStatus $setDataToLegacyStockStatus,
        LoggerInterface $logger
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->setDataToLegacyStockStatus = $setDataToLegacyStockStatus;
        $this->logger = $logger;
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
            try {
                if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                    continue;
                }
                $this->setDataToLegacyStockItem->execute($sourceItem->getSku(), 0, 0);
                $this->setDataToLegacyStockStatus->execute($sourceItem->getSku(), 0, Status::STATUS_OUT_OF_STOCK);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
