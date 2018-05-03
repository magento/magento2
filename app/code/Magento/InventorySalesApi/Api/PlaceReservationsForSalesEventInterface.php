<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\InventorySalesApi\Api\Data\ItemToSellInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\Data\SalesEventInterface;

/**
 * This service is responsible for creating reservations upon a sale event.
 *
 * @api
 */
interface PlaceReservationsForSalesEventInterface
{
    /**
     * @param ItemToSellInterface[] $items
     * @param SalesChannelInterface $salesChannel
     * @param SalesEventInterface $salesEvent
     * @return void
     *
     * @throws LocalizedException
     * @throws InputException
     * @throws CouldNotSaveException
     */
    public function execute(array $items, SalesChannelInterface $salesChannel, SalesEventInterface $salesEvent);
}
