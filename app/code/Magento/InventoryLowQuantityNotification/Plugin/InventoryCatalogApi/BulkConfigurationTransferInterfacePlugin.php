<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Plugin\InventoryCatalogApi;

use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Magento\InventoryLowQuantityNotification\Model\ResourceModel\BulkConfigurationTransfer;

class BulkConfigurationTransferInterfacePlugin
{
    /**
     * @var BulkConfigurationTransfer
     */
    private $bulkConfigurationTransfer;

    /**
     * @param BulkConfigurationTransfer $bulkConfigurationTransfer
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        BulkConfigurationTransfer $bulkConfigurationTransfer
    ) {
        $this->bulkConfigurationTransfer = $bulkConfigurationTransfer;
    }

    /**
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
        $res = $proceed($skus, $originSource, $destinationSource, $unassignFromOrigin);
        $this->bulkConfigurationTransfer->execute($skus, $originSource, $destinationSource, $unassignFromOrigin);
        return $res;
    }
}
