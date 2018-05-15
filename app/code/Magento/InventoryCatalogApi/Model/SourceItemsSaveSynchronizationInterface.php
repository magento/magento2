<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogApi\Model;

use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * @api
 */
interface SourceItemsSaveSynchronizationInterface
{
    /**
     * @param SourceItemInterface[] $sourceItems
     * @return void
     */
    public function execute(array $sourceItems): void;
}
