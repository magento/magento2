<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

interface CartItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Returns the item ID.
     *
     * @return int|null Item ID. Otherwise, null.
     */
    public function getItemId();

    /**
     * Sets the item ID.
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId);

    /**
     * Returns the product SKU.
     *
     * @return string|null Product SKU. Otherwise, null.
     */
    public function getSku();

    /**
     * Sets the product SKU.
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku);

    /**
     * Returns the product quantity.
     *
     * @return int Product quantity.
     */
    public function getQty();

    /**
     * Sets the product quantity.
     *
     * @param int $qty
     * @return $this
     */
    public function setQty($qty);

    /**
     * Returns the product name.
     *
     * @return string|null Product name. Otherwise, null.
     */
    public function getName();

    /**
     * Sets the product name.
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Returns the product price.
     *
     * @return float|null Product price. Otherwise, null.
     */
    public function getPrice();

    /**
     * Sets the product price.
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price);

    /**
     * Returns the product type.
     *
     * @return string|null Product type. Otherwise, null.
     */
    public function getProductType();

    /**
     * Sets the product type.
     *
     * @param string $productType
     * @return $this
     */
    public function setProductType($productType);

    /**
     * Returns Quote id.
     *
     * @return int
     */
    public function getQuoteId();

    /**
     * Sets Quote id.
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);
}
