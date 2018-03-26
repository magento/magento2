<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockItem;
use Magento\InventoryCatalog\Model\ResourceModel\SetDataToLegacyStockStatus;
use Psr\Log\LoggerInterface;

/**
 * Set Qty and status for legacy CatalogInventory Stock Status and Stock Item DB tables,
 * if corresponding MSI SourceItem assigned to Default Source has been saved
 */
class SetDataToLegacyCatalogInventoryAtSourceItemsSavePlugin
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
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param SetDataToLegacyStockItem $setDataToLegacyStockItem
     * @param SetDataToLegacyStockStatus $setDataToLegacyStockStatus
     */
    public function __construct(
        DefaultSourceProviderInterface $defaultSourceProvider,
        SetDataToLegacyStockItem $setDataToLegacyStockItem,
        SetDataToLegacyStockStatus $setDataToLegacyStockStatus
    ) {
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->setDataToLegacyStockItem = $setDataToLegacyStockItem;
        $this->setDataToLegacyStockStatus = $setDataToLegacyStockStatus;
    }

    /**
     * @param SourceItemsSaveInterface $subject
     * @param void $result
     * @param SourceItemInterface[] $sourceItems
     * @return void
     * @see SourceItemsSaveInterface::execute
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterExecute(SourceItemsSaveInterface $subject, $result, array $sourceItems)
    {
        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getSourceCode() !== $this->defaultSourceProvider->getCode()) {
                continue;
            }
            $this->setDataToLegacyStockItem->execute(
                $sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                (int)$sourceItem->getStatus()
            );
            $this->setDataToLegacyStockStatus->execute(
                $sourceItem->getSku(),
                (float)$sourceItem->getQuantity(),
                (int)$sourceItem->getStatus()
            );
        }
    }
}
