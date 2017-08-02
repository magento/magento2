<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

/**
 * @api
 * @since 2.0.0
 */
interface ProductCustomOptionValuesInterface
{
    /**
     * Get option title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Set option title
     *
     * @param string $title
     * @return $this
     * @since 2.0.0
     */
    public function setTitle($title);

    /**
     * Get sort order
     *
     * @return int
     * @since 2.0.0
     */
    public function getSortOrder();

    /**
     * Set sort order
     *
     * @param int $sortOrder
     * @return $this
     * @since 2.0.0
     */
    public function setSortOrder($sortOrder);

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
     * @return string
     * @since 2.0.0
     */
    public function getPriceType();

    /**
     * Set price type
     *
     * @param string $priceType
     * @return $this
     * @since 2.0.0
     */
    public function setPriceType($priceType);

    /**
     * Get Sku
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Set Sku
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Get Option type id
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getOptionTypeId();

    /**
     * Set Option type id
     *
     * @param int $optionTypeId
     * @return int|null
     * @since 2.0.0
     */
    public function setOptionTypeId($optionTypeId);
}
