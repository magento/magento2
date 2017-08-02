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
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getProductId();

    /**
     * @param int $productId
     * @return $this
     * @since 2.0.0
     */
    public function setProductId($productId);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getStockId();

    /**
     * @param int $stockId
     * @return $this
     * @since 2.0.0
     */
    public function setStockId($stockId);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getQty();

    /**
     * @param int $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty);

    /**
     * @return int
     * @since 2.0.0
     */
    public function getStockStatus();

    /**
     * @param int $stockStatus
     * @return $this
     * @since 2.0.0
     */
    public function setStockStatus($stockStatus);

    /**
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @since 2.0.0
     */
    public function getStockItem();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockStatusExtensionInterface $extensionAttributes
    );
}
