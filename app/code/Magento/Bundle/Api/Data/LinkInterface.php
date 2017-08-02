<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api\Data;

/**
 * Interface LinkInterface
 * @api
 * @since 2.0.0
 */
interface LinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PRICE_TYPE_FIXED = 0;
    const PRICE_TYPE_PERCENT = 1;

    /**
     * Get the identifier
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getId();

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Get linked product sku
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Set linked product sku
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Get option id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     * @since 2.0.0
     */
    public function setOptionId($optionId);

    /**
     * Get qty
     *
     * @return float|null
     * @since 2.0.0
     */
    public function getQty();

    /**
     * Set qty
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty);

    /**
     * Get position
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     * @since 2.0.0
     */
    public function setPosition($position);

    /**
     * Get is default
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     * @since 2.0.0
     */
    public function getIsDefault();

    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     * @since 2.0.0
     */
    public function setIsDefault($isDefault);

    /**
     * Get price
     *
     * @return float
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return int
     * @since 2.0.0
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param int $priceType
     * @return $this
     * @since 2.0.0
     */
    public function setPriceType($priceType);

    /**
     * Get whether quantity could be changed
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCanChangeQuantity();

    /**
     * Set whether quantity could be changed
     *
     * @param int $canChangeQuantity
     * @return $this
     * @since 2.0.0
     */
    public function setCanChangeQuantity($canChangeQuantity);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\LinkExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes);
}
