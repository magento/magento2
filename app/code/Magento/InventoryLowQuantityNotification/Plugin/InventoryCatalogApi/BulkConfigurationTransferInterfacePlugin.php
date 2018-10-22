<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryCatalogApi;

use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationTransfer;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationUnassign;

/**
 * This plugin keeps consistency between SourceItem and SourceItemConfiguration while bulk transferring
 */
class BulkConfigurationTransferInterfacePlugin
{
    /**
     * @var BulkConfigurationTransfer
     */
    private $bulkConfigurationTransfer;

    /**
     * @var BulkConfigurationUnassign
     */
    private $bulkConfigurationUnassign;

    /**
     * @param BulkConfigurationTransfer $bulkConfigurationTransfer
     * @param BulkConfigurationUnassign $bulkConfigurationUnassign
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationTransfer $bulkConfigurationTransfer,
        BulkConfigurationUnassign $bulkConfigurationUnassign
    ) {
        $this->bulkConfigurationTransfer = $bulkConfigurationTransfer;
        $this->bulkConfigurationUnassign = $bulkConfigurationUnassign;
    }

    /**
     * Keep database consistency while bulk source items transfer
     *
     * @param BulkInventoryTransferInterface $subject
     * @param callable $proceed
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignFromOrigin
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        BulkInventoryTransferInterface $subject,
        callable $proceed,
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignFromOrigin
    ): bool {
        $this->bulkConfigurationTransfer->execute($skus, $originSource, $destinationSource);
        $res = $proceed($skus, $originSource, $destinationSource, $unassignFromOrigin);
        if ($unassignFromOrigin) {
            $this->bulkConfigurationUnassign->execute($skus, [$originSource]);
        }
        return $res;
    }
}
