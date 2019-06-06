<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Api;

/**
 * Transfer Inventory between sources. Moves specified items from origin source to destination source.
 *
 * @api
 */
interface BulkPartialInventoryTransferInterface
{
    /**
     * Run bulk partial inventory transfer for specified items.
     *
     * @param string $originSourceCode
     * @param string $destinationSourceCode
     * @param \Magento\InventoryCatalogApi\Api\Data\PartialInventoryTransferItemInterface[] $items
     * @return void
     * @throws \Magento\Framework\Validation\ValidationException
     */
    public function execute(string $originSourceCode, string $destinationSourceCode, array $items): void;
}
