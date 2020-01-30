<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Product Special Price Interface is used to encapsulate data that can be processed by efficient price API.
 * @api
 * @since 102.0.0
 */
interface SpecialPriceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const PRICE = 'price';
    const STORE_ID = 'store_id';
    const SKU = 'sku';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';
    /**#@-*/

    /**
     * Set product special price value.
     *
     * @param float $price
     * @return $this
     * @since 102.0.0
     */
    public function setPrice($price);

    /**
     * Get product special price value.
     *
     * @return float
     * @since 102.0.0
     */
    public function getPrice();

    /**
     * Set ID of store, that contains special price value.
     *
     * @param int $storeId
     * @return $this
     * @since 102.0.0
     */
    public function setStoreId($storeId);

    /**
     * Get ID of store, that contains special price value.
     *
     * @return int
     * @since 102.0.0
     */
    public function getStoreId();

    /**
     * Set SKU of product, that contains special price value.
     *
     * @param string $sku
     * @return $this
     * @since 102.0.0
     */
    public function setSku($sku);

    /**
     * Get SKU of product, that contains special price value.
     *
     * @return string
     * @since 102.0.0
     */
    public function getSku();

    /**
     * Set start date for special price in Y-m-d H:i:s format.
     *
     * @param string $datetime
     * @return $this
     * @since 102.0.0
     */
    public function setPriceFrom($datetime);

    /**
     * Get start date for special price in Y-m-d H:i:s format.
     *
     * @return string
     * @since 102.0.0
     */
    public function getPriceFrom();

    /**
     * Set end date for special price in Y-m-d H:i:s format.
     *
     * @param string $datetime
     * @return $this
     * @since 102.0.0
     */
    public function setPriceTo($datetime);

    /**
     * Get end date for special price in Y-m-d H:i:s format.
     *
     * @return string
     * @since 102.0.0
     */
    public function getPriceTo();

    /**
     * Retrieve existing extension attributes object.
     *
     * If extension attributes do not exist return null.
     *
     * @return \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface|null
     * @since 102.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
    );
}
