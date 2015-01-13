<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StockStatusInterface
 */
interface StockStatusInterface extends ExtensibleDataInterface
{
    /**#@+
     * Stock status object data keys
     */
    const PRODUCT_ID = 'product_id';
    const WEBSITE_ID = 'website_id';
    const STOCK_ID = 'stock_id';
    const QTY = 'qty';
    const STOCK_STATUS = 'stock_status';
    const STOCK_ITEM = 'stock_item';

    /**#@-*/

    /**
     * @return int
     */
    public function getProductId();

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @return int
     */
    public function getStockId();

    /**
     * @return int
     */
    public function getQty();

    /**
     * @return int
     */
    public function getStockStatus();

    /**
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem();
}
