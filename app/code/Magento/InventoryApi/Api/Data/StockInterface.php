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
    const STOCK_ID = 'source_id';
    const NAME = 'name';
    /**#@-*/

    /**
     * Get source id.
     *
     * @return int|null
     */
    public function getStockId();

    /**
     * Set source id.
     *
     * @param int $sourceId
     * @return void
     */
    public function setStockId($sourceId);

    /**
     * Get source name.
     *
     * @return string
     */
    public function getName();

    /**
     * Set source name.
     *
     * @param string $name
     * @return void
     */
    public function setName($name);
}
