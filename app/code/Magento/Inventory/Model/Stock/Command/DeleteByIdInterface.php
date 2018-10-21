<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Stock\Command;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Delete Stock by stockId command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial Delete call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\StockRepositoryInterface
 * @api
 */
interface DeleteByIdInterface
{
    /**
     * Delete the Stock data by stockId. If stock is not found do nothing
     *
     * @param int $stockId
     * @return void
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function execute(int $stockId);
}
