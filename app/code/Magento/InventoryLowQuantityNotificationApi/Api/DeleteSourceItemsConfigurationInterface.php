<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationApi\Api;

/**
 * Delete the source item configuration
 *
 * @api
 */
interface DeleteSourceItemsConfigurationInterface
{
    /**
     * Delete multiple source items configuration for low quantity
     *
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems
     * @return void
     */
    public function execute(array $sourceItems): void;
}
