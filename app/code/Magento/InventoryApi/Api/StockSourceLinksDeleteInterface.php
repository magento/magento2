<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Remove StockSourceLink list command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial Save call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\StockSourceLinkRepositoryInterface
 * @api
 */
interface StockSourceLinksDeleteInterface
{
    /**
     * Remove StockSourceLink list
     *
     * @param StockSourceLinkInterface[] $links
     * @return void
     * @throws CouldNotDeleteException
     */
    public function execute(array $links);
}
