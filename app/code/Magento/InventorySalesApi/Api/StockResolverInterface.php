<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Api;

use Magento\InventoryApi\Api\Data\StockInterface;

/**
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
     * @return StockInterface
     */
    public function get(string $type, string $code): StockInterface;
}
