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
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * @return int
     */
    public function getStockId();

    /**
     * @param int $stockId
     * @return $this
     */
    public function setStockId($stockId);

    /**
     * @return int
     */
    public function getQty();

    /**
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * @return int
     */
    public function getStockStatus();

    /**
     * @param int $stockStatus
     * @return $this
     */
    public function setStockStatus($stockStatus);

    /**
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem();
}
