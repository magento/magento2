<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api;

use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmInterface;

/**
 * Returns the list of Data Interfaces which represent registered SSA in the system
 *
 * @api
 */
interface GetSourceSelectionAlgorithmListInterface
{
    /**
     * @return SourceSelectionAlgorithmInterface[]
     */
    public function execute(): array;
}
