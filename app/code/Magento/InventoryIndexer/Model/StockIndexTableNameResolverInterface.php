<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryIndexer\Model;

/**
 * Stock Index table name resolver. Get stock index table by stock id.
 *
 * @api
 */
interface StockIndexTableNameResolverInterface
{
    /**
     * @param int $stockId
     * @return string
     */
    public function execute(int $stockId): string;
}
