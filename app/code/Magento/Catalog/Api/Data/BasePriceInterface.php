<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Price interface.
 * @api
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
     */
    public function setPrice($price);

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice();

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
     * @return \Magento\Catalog\Api\Data\BasePriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\BasePriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\BasePriceExtensionInterface $extensionAttributes
    );
}
