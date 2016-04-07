<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface CartItemInterface
 * @api
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
     * @return float Product quantity.
     */
    public function getQty();

    /**
     * Sets the product quantity.
     *
     * @param float $qty
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
     * @return string
     */
    public function getQuoteId();

    /**
     * Sets Quote id.
     *
     * @param string $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Returns product option
     *
     * @return \Magento\Quote\Api\Data\ProductOptionInterface|null
     */
    public function getProductOption();

    /**
     * Sets product option
     *
     * @param \Magento\Quote\Api\Data\ProductOptionInterface $productOption
     * @return $this
     */
    public function setProductOption(\Magento\Quote\Api\Data\ProductOptionInterface $productOption);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\CartItemExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\CartItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\CartItemExtensionInterface $extensionAttributes);
}
