<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\BulkTransferInventoryInterface;
use Magento\InventoryApi\Model\BulkSourceAssignValidatorInterface;
use Magento\Inventory\Model\ResourceModel\BulkSourceAssign as BulkSourceAssignResource;

/**
 * @inheritdoc
 */
class BulkTransferInventory implements BulkTransferInventoryInterface
{
    /**
     * @inheritdoc
     */
    public function execute(array $skus, string $destinationSource, bool $defaultSourceOnly): int
    {

    }
}
