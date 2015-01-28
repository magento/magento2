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
     * Returns the product SKU.
     *
     * @return string|null Product SKU. Otherwise, null.
     */
    public function getSku();

    /**
     * Returns the product quantity.
     *
     * @return int Product quantity.
     */
    public function getQty();

    /**
     * Returns the product name.
     *
     * @return string|null Product name. Otherwise, null.
     */
    public function getName();

    /**
     * Returns the product price.
     *
     * @return float|null Product price. Otherwise, null.
     */
    public function getPrice();

    /**
     * Returns the product type.
     *
     * @return string|null Product type. Otherwise, null.
     */
    public function getProductType();

    /**
     * Returns Quote id.
     *
     * @return int
     */
    public function getQuoteId();
}
