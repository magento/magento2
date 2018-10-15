<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * @inheritdoc
 */
class SalesEventToArrayConverter
{
    /**
     * Converts sales event data to array structure, which can be serialized to JSON
     *
     * @param SalesEventInterface $salesEvent
     * @return array
     */
    public function execute(SalesEventInterface $salesEvent): array
    {
        return [
            'event_type' => $salesEvent->getType(),
            'object_type' => $salesEvent->getObjectType(),
            'object_id' => $salesEvent->getObjectId(),
        ];
    }
}
