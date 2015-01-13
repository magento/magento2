<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Api\Data;

interface ProductCustomOptionInterface
{
    /**
     * Get product SKU
     *
     * @return string
     */
    public function getProductSku();

    /**
     * Get option id
     *
     * @return int|null
     */
    public function getOptionId();

    /**
     * Get option title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get option type
     *
     * @return string
     */
    public function getType();

    /**
     * Get sort order
     *
     * @return int
     */
    public function getSortOrder();

    /**
     * Get is require
     *
     * @return bool
     */
    public function getIsRequire();

    /**
     * Get price
     *
     * @return float|null
     */
    public function getPrice();

    /**
     * Get price type
     *
     * @return string|null
     */
    public function getPriceType();

    /**
     * Get Sku
     *
     * @return string|null
     */
    public function getSku();

    /**
     * @return string|null
     */
    public function getFileExtension();

    /**
     * @return int|null
     */
    public function getMaxCharacters();

    /**
     * @return int|null
     */
    public function getImageSizeX();

    /**
     * @return int|null
     */
    public function getImageSizeY();

    /**
     * @return \Magento\Catalog\Api\Data\ProductCustomOptionValuesInterface[]|null
     */
    public function getValues();
}
