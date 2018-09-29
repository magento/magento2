<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api;

/**
 * Returns the list of Data Interfaces which represent registered SSA in the system
 *
 * @api
 */
interface GetSourceSelectionAlgorithmListInterface
{
    /**
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmInterface[]
     */
    public function execute(): array;
}
