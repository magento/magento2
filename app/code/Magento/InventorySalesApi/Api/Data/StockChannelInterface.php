<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventorySalesApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents product aggregation among some different physical storages (in technical words, it is an index)
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface StockChannelInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCK_CHANNEL_ID = 'stock_channel_id';
    const TYPE = 'type';
    const CODE = 'code';
    /**#@-*/

    /**
     * Get stock id
     *
     * @return int|null
     */
    public function getStockChannelId();

    /**
     * Set stock channel id
     *
     * @param int|null $stockChannelId
     * @return void
     */
    public function setStockChannelId($stockChannelId);

    /**
     * Get stock channel type
     *
     * @return int|null
     */
    public function getType();

    /**
     * Set stock channel type
     *
     * @param int|null $type
     * @return void
     */
    public function setType($type);

    /**
     * Get stock channel code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Set stock channel code
     *
     * @param string|null $code
     * @return void
     */
    public function setCode($code);
}
