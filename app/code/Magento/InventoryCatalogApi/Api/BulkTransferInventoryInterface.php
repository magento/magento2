<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

/**
 * Perform bulk product inventory transfer
 *
 * @api
 */
interface BulkTransferInventoryInterface
{
    /**
     * Run bulk inventory transfer
     * @param array $skus
     * @param string $destinationSource
     * @param bool $defaultSourceOnly
     * @return int
     */
    public function execute(array $skus, string $destinationSource, bool $defaultSourceOnly): int;
}
