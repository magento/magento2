<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

/**
 * Responsible for retrieving StockItem Data
 *
 * @api
 */
interface GetStockItemDataInterface
{
    /**
     * Given a product sku and a stock id, return stock item data.
     *
     * @param string $sku
     * @param int $stockId
     * @return array|null
     */
    public function execute(string $sku, int $stockId);
}
