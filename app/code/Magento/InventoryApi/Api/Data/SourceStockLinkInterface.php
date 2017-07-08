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
interface SourceStockLinkInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const LINK_ID = 'link_id';
    const SOURCE_ID = 'source_id';
    const STOCK_ID = 'stock_id';
    /**#@-*/

    /**
     * Get link id.
     *
     * @return string
     */
    public function getLinkId();

    /**
     * Set link id.
     *
     * @param string $linkId
     * @return void
     */
    public function setLinkId($linkId);

    /**
     * Get source id.
     *
     * @return int|null
     */
    public function getSourceId();

    /**
     * Set source id.
     *
     * @param int|null $sourceId
     * @return void
     */
    public function setSourceId($sourceId);

    /**
     * Get stock id.
     *
     * @return int|null
     */
    public function getStockId();

    /**
     * Set stock id.
     *
     * @param int|null $stockId
     * @return void
     */
    public function setStockId($stockId);
}
