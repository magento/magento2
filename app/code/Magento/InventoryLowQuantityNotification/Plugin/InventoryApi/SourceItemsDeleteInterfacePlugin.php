<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryApi;

use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\SourceItemsDeleteInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\SourceItemConfiguration\Delete;

/**
 * This plugin keeps consistency between SourceItem and SourceItemConfiguration while deleting
 */
class SourceItemsDeleteInterfacePlugin
{
    /**
     * @var Delete
     */
    private $delete;

    /**
     * @param Delete $delete
     */
    public function __construct(
        Delete $delete
    ) {
        $this->delete = $delete;
    }

    /**
     * Keep database consistency while a source item is removed
     *
     * @param SourceItemsDeleteInterface $subject
     * @param callable $proceed
     * @param array $sourceItems
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        SourceItemsDeleteInterface $subject,
        callable $proceed,
        array $sourceItems
    ) {
        $proceed($sourceItems);

        foreach ($sourceItems as $sourceItem) {
            /** @var SourceItemInterface $sourceItem */
            $this->delete->execute($sourceItem->getSourceCode(), $sourceItem->getSku());
        }
    }
}
