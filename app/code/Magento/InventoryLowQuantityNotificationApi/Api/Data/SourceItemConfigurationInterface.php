<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Represents a Source Item Configuration object
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
    const SOURCE_CODE = 'source_code';
    const SKU = 'sku';
    const INVENTORY_NOTIFY_QTY = 'notify_stock_qty';

    /**
     * Get source code
     *
     * @return string|null
     */
    public function getSourceCode();

    /**
     * Set source code
     *
     * @param string $sourceCode
     * @return void
     */
    public function setSourceCode(string $sourceCode);

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

    /**
     * Retrieve existing extension attributes object
     *
     * Null for return is specified for proper work SOAP requests parser
     *
     * @return \Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object
     *
     * @param \Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationExtensionInterface
     *      $extensionAttributes
     * @return void
     */
    public function setExtensionAttributes(SourceItemConfigurationExtensionInterface $extensionAttributes);
}
