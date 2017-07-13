<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Service method for source items multiple save
 * Performance efficient API, used for stock synchronization
 *
 * @api
 */
interface SourceItemSaveInterface
{
    /**
     * Multiple Save Source item data
     *
     * @param \Magento\InventoryApi\Api\Data\SourceItemInterface[] $sourceItems
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function execute(array $sourceItems);
}
