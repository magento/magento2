<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\Source\Command;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Get Source by code command (Service Provider Interface - SPI)
 *
 * Separate command interface to which Repository proxies initial Get call, could be considered as SPI - Interfaces
 * that you should extend and implement to customize current behaviour, but NOT expected to be used (called) in the code
 * of business logic directly
 *
 * @see \Magento\InventoryApi\Api\SourceRepositoryInterface
 * @api
 */
interface GetInterface
{
    /**
     * Get Source data by given code
     *
     * @param string $sourceCode
     * @return SourceInterface
     * @throws NoSuchEntityException
     */
    public function execute(string $sourceCode): SourceInterface;
}
