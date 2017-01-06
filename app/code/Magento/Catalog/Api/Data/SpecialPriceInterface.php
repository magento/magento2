<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Product Special Price Interface is used to encapsulate data that can be processed by efficient price API.
 * @api
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
     */
    public function setPrice($price);

    /**
     * Get product special price value.
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set ID of store, that contains special price value.
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId);

    /**
     * Get ID of store, that contains special price value.
     *
     * @return int
     */
    public function getStoreId();

    /**
     * Set SKU of product, that contains special price value.
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get SKU of product, that contains special price value.
     *
     * @return string
     */
    public function getSku();

    /**
     * Set start date for special price in Y-m-d H:i:s format.
     *
     * @param string $datetime
     * @return $this
     */
    public function setPriceFrom($datetime);

    /**
     * Get start date for special price in Y-m-d H:i:s format.
     *
     * @return string
     */
    public function getPriceFrom();

    /**
     * Set end date for special price in Y-m-d H:i:s format.
     *
     * @param string $datetime
     * @return $this
     */
    public function setPriceTo($datetime);

    /**
     * Get end date for special price in Y-m-d H:i:s format.
     *
     * @return string
     */
    public function getPriceTo();

    /**
     * Retrieve existing extension attributes object.
     * If extension attributes do not exist return null.
     *
     * @return \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\SpecialPriceExtensionInterface $extensionAttributes
    );
}
