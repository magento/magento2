<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * Tier price interface.
 * @api
 * @since 102.0.0
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
     * @since 102.0.0
     */
    public function setPrice($price);

    /**
     * Get tier price.
     *
     * @return float
     * @since 102.0.0
     */
    public function getPrice();

    /**
     * Set tier price type.
     *
     * @param string $type
     * @return $this
     * @since 102.0.0
     */
    public function setPriceType($type);

    /**
     * Get tier price type.
     *
     * @return string
     * @since 102.0.0
     */
    public function getPriceType();

    /**
     * Set website id.
     *
     * @param int $websiteId
     * @return $this
     * @since 102.0.0
     */
    public function setWebsiteId($websiteId);

    /**
     * Get website id.
     *
     * @return int
     * @since 102.0.0
     */
    public function getWebsiteId();

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
     * Set customer group.
     *
     * @param string $group
     * @return $this
     * @since 102.0.0
     */
    public function setCustomerGroup($group);

    /**
     * Get customer group.
     *
     * @return string
     * @since 102.0.0
     */
    public function getCustomerGroup();

    /**
     * Set quantity.
     *
     * @param float $quantity
     * @return $this
     * @since 102.0.0
     */
    public function setQuantity($quantity);

    /**
     * Get quantity.
     *
     * @return float
     * @since 102.0.0
     */
    public function getQuantity();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Catalog\Api\Data\TierPriceExtensionInterface|null
     * @since 102.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Catalog\Api\Data\TierPriceExtensionInterface $extensionAttributes
     * @return $this
     * @since 102.0.0
     */
    public function setExtensionAttributes(
        \Magento\Catalog\Api\Data\TierPriceExtensionInterface $extensionAttributes
    );
}
