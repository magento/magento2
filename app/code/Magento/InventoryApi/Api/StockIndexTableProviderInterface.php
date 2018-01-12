<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Stock Index table provider.
 *
 * @api
 */
interface StockIndexTableProviderInterface
{
    /**
     * Build index by stock id.
     *
     * @param int $stockId
     *
     * @return string
     */
    public function execute(int $stockId): string;
}
