<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * @api
 */
interface SalesEventToArrayConverterInterface
{
    /**
     * Converts sales event data to array structure, which can be serialized to JSON
     *
     * @param SalesEventInterface $salesEvent
     * @return array
     */
    public function convert(SalesEventInterface $salesEvent): array;
}
