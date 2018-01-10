<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

/**
 * Responsible for how we will deduct product qty from different Sources
 *
 * @api
 */
interface ShippingAlgorithmResultInterface
{
    /**
     * Returns product SKU -> source selection mapping in the following format:
     * [
     *      'source-code-1' => SourceSelectionInterface[],
     *      'source-code-2' => SourceSelectionInterface[],
     * ]
     *
     * @return SourceSelectionInterface[]
     */
    public function getSourceSelections(): array;
}
