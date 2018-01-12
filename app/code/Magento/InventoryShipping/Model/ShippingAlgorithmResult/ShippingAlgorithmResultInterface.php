<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model\ShippingAlgorithmResult;

/**
 * Result of how we will deduct product qty from different Sources
 *
 * @api
 */
interface ShippingAlgorithmResultInterface
{
    /**
     * @return SourceSelectionInterface[]
     */
    public function getSourceSelections(): array;
}
