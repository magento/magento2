<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Api;

/**
 * Responsible for how we will deduct product qty from different Sources
 * @api
 */
interface ShippingAlgorithmResultInterface
{
    /**
     * Returns product SKU -> source selection mapping in the following format:
     * [
     *      'sku-1' => SourceSelectionInterface[],
     *      'sku-2' => SourceSelectionInterface[],
     * ]
     *
     * @return array
     */
    public function getSourceSelections(): array;
}
