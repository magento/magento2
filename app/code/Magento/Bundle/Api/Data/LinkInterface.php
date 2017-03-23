<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Bundle\Api\Data;

/**
 * Interface LinkInterface
 * @api
 */
interface LinkInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const PRICE_TYPE_FIXED = 0;
    const PRICE_TYPE_PERCENT = 1;

    /**
     * Get the identifier
     *
     * @return string|null
     */
    public function getId();

    /**
     * Set id
     *
     * @param string $id
     * @return $this
     */
    public function setId($id);

    /**
     * Get linked product sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * Set linked product sku
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Set option id
     *
     * @param int $optionId
     * @return $this
     */
    public function setOptionId($optionId);

    /**
     * Get qty
     *
     * @return float|null
     */
    public function getQty();

    /**
     * Set qty
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Get position
     *
     * @return int|null
     */
    public function getPosition();

    /**
     * Set position
     *
     * @param int $position
     * @return $this
     */
    public function setPosition($position);

    /**
     * Get is default
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsDefault();

    /**
     * Set is default
     *
     * @param bool $isDefault
     * @return $this
     */
    public function setIsDefault($isDefault);

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice();

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Get price type
     *
     * @return int
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param int $priceType
     * @return $this
     */
    public function setPriceType($priceType);

    /**
     * Get whether quantity could be changed
     *
     * @return int|null
     */
    public function getCanChangeQuantity();

    /**
     * Set whether quantity could be changed
     *
     * @param int $canChangeQuantity
     * @return $this
     */
    public function setCanChangeQuantity($canChangeQuantity);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Bundle\Api\Data\LinkExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Bundle\Api\Data\LinkExtensionInterface $extensionAttributes);
}
