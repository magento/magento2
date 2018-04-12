<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * This service is responsible for creating reservations upon a sale event.
 *
 * @api
 */
interface RegisterSalesEventInterface
{
    /**
     * @param string[] $items
     * @param string[] $productTypes
     * @param SalesChannelInterface $salesChannel
     * @param SalesEventInterface $salesEvent
     * @return void
     * @throws LocalizedException
     */
    public function execute(array $items, array $productTypes, SalesChannelInterface $salesChannel, SalesEventInterface $salesEvent);
}
