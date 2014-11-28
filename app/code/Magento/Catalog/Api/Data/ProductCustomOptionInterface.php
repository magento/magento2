<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
