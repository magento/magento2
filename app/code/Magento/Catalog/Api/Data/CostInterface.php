<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Cost interface.
 * @api
 */
interface CostInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const COST = 'cost';
    const STORE_ID = 'store_id';
    const SKU = 'sku';
    /**#@-*/

    /**
     * Set cost value.
     *
     * @param float $cost
     * @return $this
     */
    public function setCost($cost);

    /**
     * Get cost value.
     *
     * @return float
     */
    public function getCost();

    /**
     * Set store id.
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get store id.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set SKU.
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get SKU.
     *
     * @return string
     */
    public function getSku();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CostExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CostExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CostExtensionInterface $extensionAttributes
    );
}
