<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

/**
 * The stock resolver is responsible for getting the linked stock for a certain sales channel
 *
 * @api
 */
interface StockResolverInterface
{
    /**
     * Resolve Stock by Sales Channel type and code
     *
     * @param string $type
     * @param string $code
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return \Magento\InventoryApi\Api\Data\StockInterface
     */
    public function execute(string $type, string $code): \Magento\InventoryApi\Api\Data\StockInterface;
}
