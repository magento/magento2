<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

/**
 * This service is responsible for creating reservations upon a sale event.
 *
 * @api
 */
interface PlaceReservationsForSalesEventInterface
{
    /**
     * @param \Magento\InventorySalesApi\Api\Data\ItemToSellInterface[] $items
     * @param \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel
     * @param \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(
        array $items,
        \Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel,
        \Magento\InventorySalesApi\Api\Data\SalesEventInterface $salesEvent
    ): void;
}
