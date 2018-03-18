<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

/**
 * Result of how we will deduct product qty from different Sources
 *
 * @api
 */
interface SourceSelectionResultInterface
{
    /**
     * @return SourceSelectionItemInterface[]
     */
    public function getSourceSelectionItems(): array;

    /**
     * @return bool
     */
    public function isShippable() : bool;
}
