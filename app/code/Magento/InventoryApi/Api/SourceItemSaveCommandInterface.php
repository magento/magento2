<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api;

/**
 * Command for multiple saving of source items
 *
 * @api
 */
interface SourceItemSaveCommandInterface
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
