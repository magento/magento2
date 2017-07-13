<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * @api
 */
interface StockInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_ID = 'stock_id';
    const NAME = 'name';
    /**#@-*/

    /**
     * Get stock id
     *
     * @return int|null
     */
    public function getStockId();

    /**
     * Set stock id
     *
     * @param int $stockId
     * @return void
     */
    public function setStockId($stockId);

    /**
     * Get stock name
     *
     * @return string
     */
    public function getName();

    /**
     * Set stock name
     *
     * @param string $name
     * @return void
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\SourceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(
        \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
    );
}
