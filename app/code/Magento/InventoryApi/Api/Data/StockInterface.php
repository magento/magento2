<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents product aggregation among some different physical storages (in technical words, it is an index)
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
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
     * @param int|null $stockId
     * @return void
     */
    public function setStockId($stockId);

    /**
     * Get stock name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Set stock name
     *
     * @param string|null $name
     * @return void
     */
    public function setName($name);

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventoryApi\Api\Data\StockExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\StockExtensionInterface $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(StockExtensionInterface $extensionAttributes);
}
