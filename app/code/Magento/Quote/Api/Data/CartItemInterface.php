<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface CartItemInterface
 * @api
 * @since 2.0.0
 */
interface CartItemInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ITEM_ID = 'item_id';

    const KEY_SKU = 'sku';

    const KEY_QTY = 'qty';

    const KEY_NAME = 'name';

    const KEY_PRICE = 'price';

    const KEY_PRODUCT_TYPE = 'product_type';

    const KEY_QUOTE_ID = 'quote_id';

    const KEY_PRODUCT_OPTION = 'product_option';

    /**#@-*/

    /**
     * Returns the item ID.
     *
     * @return int|null Item ID. Otherwise, null.
     * @since 2.0.0
     */
    public function getItemId();

    /**
     * Sets the item ID.
     *
     * @param int $itemId
     * @return $this
     * @since 2.0.0
     */
    public function setItemId($itemId);

    /**
     * Returns the product SKU.
     *
     * @return string|null Product SKU. Otherwise, null.
     * @since 2.0.0
     */
    public function getSku();

    /**
     * Sets the product SKU.
     *
     * @param string $sku
     * @return $this
     * @since 2.0.0
     */
    public function setSku($sku);

    /**
     * Returns the product quantity.
     *
     * @return float Product quantity.
     * @since 2.0.0
     */
    public function getQty();

    /**
     * Sets the product quantity.
     *
     * @param float $qty
     * @return $this
     * @since 2.0.0
     */
    public function setQty($qty);

    /**
     * Returns the product name.
     *
     * @return string|null Product name. Otherwise, null.
     * @since 2.0.0
     */
    public function getName();

    /**
     * Sets the product name.
     *
     * @param string $name
     * @return $this
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Returns the product price.
     *
     * @return float|null Product price. Otherwise, null.
     * @since 2.0.0
     */
    public function getPrice();

    /**
     * Sets the product price.
     *
     * @param float $price
     * @return $this
     * @since 2.0.0
     */
    public function setPrice($price);

    /**
     * Returns the product type.
     *
     * @return string|null Product type. Otherwise, null.
     * @since 2.0.0
     */
    public function getProductType();

    /**
     * Sets the product type.
     *
     * @param string $productType
     * @return $this
     * @since 2.0.0
     */
    public function setProductType($productType);

    /**
     * Returns Quote id.
     *
     * @return string
     * @since 2.0.0
     */
    public function getQuoteId();

    /**
     * Sets Quote id.
     *
     * @param string $quoteId
     * @return $this
     * @since 2.0.0
     */
    public function setQuoteId($quoteId);

    /**
     * Returns product option
     *
     * @return \Magento\Quote\Api\Data\ProductOptionInterface|null
     * @since 2.0.0
     */
    public function getProductOption();

    /**
     * Sets product option
     *
     * @param \Magento\Quote\Api\Data\ProductOptionInterface $productOption
     * @return $this
     * @since 2.0.0
     */
    public function setProductOption(\Magento\Quote\Api\Data\ProductOptionInterface $productOption);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\CartItemExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\CartItemExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\CartItemExtensionInterface $extensionAttributes);
}
