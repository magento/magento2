<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Tier price interface.
 * @api
 */
interface TierPriceInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const PRICE = 'price';
    const PRICE_TYPE = 'price_type';
    const WEBSITE_ID = 'website_id';
    const SKU = 'sku';
    const CUSTOMER_GROUP = 'customer_group';
    const QUANTITY = 'quantity';
    const PRICE_TYPE_FIXED = 'fixed';
    const PRICE_TYPE_DISCOUNT = 'discount';
    /**#@-*/

    /**
     * Set tier price.
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get tier price.
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set tier price type.
     *
     * @param string $type
     * @return $this
     */
    public function setPriceType($type);

    /**
     * Get tier price type.
     *
     * @return string
     */
    public function getPriceType();

    /**
     * Set website id.
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId);

    /**
     * Get website id.
     *
     * @return int
     */
    public function getWebsiteId();

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
     * Set customer group.
     *
     * @param string $group
     * @return $this
     */
    public function setCustomerGroup($group);

    /**
     * Get customer group.
     *
     * @return string
     */
    public function getCustomerGroup();

    /**
     * Set quantity.
     *
     * @param float $quantity
     * @return $this
     */
    public function setQuantity($quantity);

    /**
     * Get quantity.
     *
     * @return float
     */
    public function getQuantity();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\TierPriceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\TierPriceExtensionInterface $extensionAttributes
    );
}
