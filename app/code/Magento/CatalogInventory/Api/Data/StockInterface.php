<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface Stock
 * @api
 * @since 2.0.0
 */
interface StockInterface extends ExtensibleDataInterface
{
    const STOCK_ID = 'stock_id';

    const STOCK_NAME = 'stock_name';

    /**
     * Retrieve stock identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getStockId();

    /**
     * Set stock identifier
     *
     * @param int $stockId
     * @return $this
     * @since 2.0.0
     */
    public function setStockId($stockId);

    /**
     * Retrieve stock name
     *
     * @return string
     * @since 2.0.0
     */
    public function getStockName();

    /**
     * Set stock name
     *
     * @param string $stockName
     * @return $this
     * @since 2.0.0
     */
    public function setStockName($stockName);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\CatalogInventory\Api\Data\StockExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\CatalogInventory\Api\Data\StockExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(
        \Magento\CatalogInventory\Api\Data\StockExtensionInterface $extensionAttributes
    );
}
