<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\Framework\Exception\LocalizedException;

/**
 * Responsible for retrieving StockItem Data
 *
 * @api
 */
interface GetStockItemDataInterface
{
    /**
     * Constants for represent fields in result array
     */
    const SKU = 'sku';
    const QUANTITY = 'quantity';
    const IS_SALABLE = 'is_salable';
    /**#@-*/

    /**
     * Given a product sku and a stock id, return stock item data.
     *
     * @param string $sku
     * @param int $stockId
     * @return array|null
     * @throws LocalizedException
     */
    public function execute(string $sku, int $stockId);
}
