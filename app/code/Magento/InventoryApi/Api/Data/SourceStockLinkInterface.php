<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\InventoryApi\Api\Data\SourceStockLinkExtensionInterface;

/**
 * Represents relation between some physical storages and stock aggregation
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
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
     * Get link id
     *
     * @return int
     */
    public function getLinkId();

    /**
     * Set link id
     *
     * @param int $linkId
     * @return void
     */
    public function setLinkId($linkId);

    /**
     * Get source id
     *
     * @return int
     */
    public function getSourceId();

    /**
     * Set source id
     *
     * @param int $sourceId
     * @return void
     */
    public function setSourceId($sourceId);

    /**
     * Get stock id
     *
     * @return int
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
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventoryApi\Api\Data\SourceStockLinkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryApi\Api\Data\SourceStockLinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(SourceStockLinkExtensionInterface $extensionAttributes);
}
