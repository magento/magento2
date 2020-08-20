<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Price interface.
 * @api
 * @since 102.0.0
 */
interface BasePriceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const PRICE = 'price';
    const STORE_ID = 'store_id';
    const SKU = 'sku';
    /**#@-*/

    /**
     * Set price.
     *
     * @param float $price
     * @return $this
     * @since 102.0.0
     */
    public function setPrice($price);

    /**
     * Get price.
     *
     * @return float
     * @since 102.0.0
     */
    public function getPrice();

    /**
     * Set store id.
     *
     * @param int $storeId
     * @return $this
     * @since 102.0.0
     */
    public function setStoreId($storeId);

    /**
     * Get store id.
     *
     * @return int
     * @since 102.0.0
     */
    public function getStoreId();

    /**
     * Set SKU.
     *
     * @param string $sku
     * @return $this
     * @since 102.0.0
     */
    public function setSku($sku);

    /**
     * Get SKU.
     *
     * @return string
     * @since 102.0.0
     */
    public function getSku();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\BasePriceExtensionInterface|null
     * @since 102.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\BasePriceExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\BasePriceExtensionInterface $extensionAttributes
    );
}
