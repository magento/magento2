<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface StockStatusInterface
 * @api
 * @since 100.0.2
 */
interface StockStatusInterface extends ExtensibleDataInterface
{
    /**#@+
     * Stock status object data keys
     */
    const PRODUCT_ID = 'product_id';
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

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface $extensionAttributes
    );
}
