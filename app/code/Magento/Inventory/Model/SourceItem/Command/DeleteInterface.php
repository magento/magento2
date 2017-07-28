<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Inventory\Model\SourceItem\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

/**
 * Delete SourceItem by sourceItemId command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial Delete call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\SourceItemRepositoryInterface
 * @api
 */
interface DeleteInterface
{
    /**
     * Delete the SourceItem data
     *
     * @param SourceItemInterface $sourceItem
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(SourceItemInterface $sourceItem);
}
