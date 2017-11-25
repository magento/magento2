<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents a configuration object
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemConfigurationInterface extends ExtensibleDataInterface
{
    /**
     * Constant for fields in data array
     */
    const SOURCE_ID = 'source_id';
    const SKU = 'sku';
    const INVENTORY_NOTIFY_QTY = 'notify_stock_qty';

    /**
     * Get source id
     *
     * @return int|null
     */
    public function getSourceId();

    /**
     * Set source id
     *
     * @param int $sourceItemId
     * @return void
     */
    public function setSourceId(int $sourceItemId);

    /**
     * Get notify stock qty
     *
     * @return float|null
     */
    public function getNotifyStockQty();

    /**
     * Set notify stock qty
     *
     * @param float|null $quantity
     * @return void
     */
    public function setNotifyStockQty($quantity);

    /**
     * Get SKU
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set SKU
     *
     * @param string $sku
     * @return void
     */
    public function setSku(string $sku);
}
