<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Cost interface.
 * @api
 * @since 2.2.0
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
     * @since 2.2.0
     */
    public function setCost($cost);

    /**
     * Get cost value.
     *
     * @return float
     * @since 2.2.0
     */
    public function getCost();

    /**
     * Set store id.
     *
     * @param int $storeId
     * @return $this
     * @since 2.2.0
     */
    public function setStoreId($storeId);

    /**
     * Get store id.
     *
     * @return int
     * @since 2.2.0
     */
    public function getStoreId();

    /**
     * Set SKU.
     *
     * @param string $sku
     * @return $this
     * @since 2.2.0
     */
    public function setSku($sku);

    /**
     * Get SKU.
     *
     * @return string
     * @since 2.2.0
     */
    public function getSku();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\CostExtensionInterface|null
     * @since 2.2.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\CostExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.2.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\CostExtensionInterface $extensionAttributes
    );
}
