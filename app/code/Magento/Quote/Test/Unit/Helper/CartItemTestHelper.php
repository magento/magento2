<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\CartItemExtensionInterface;
use Magento\Framework\DataObject;

/**
 * Test helper for CartItem
 *
 * This helper extends the concrete Item class to provide
 * test-specific functionality without dependency injection issues.
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 */
class CartItemTestHelper extends Item
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var array|null
     */
    private $children;

    /**
     * @var ProductInterface|null
     */
    private $product;

    /**
     * Constructor that optionally accepts children and product
     *
     * @param array|null $children
     * @param ProductInterface|null $product
     */
    public function __construct($children = null, $product = null)
    {
        $this->children = $children;
        $this->product = $product;
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Get children
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Get product
     *
     * @return ProductInterface|null
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Get quote
     *
     * @return Quote|null
     */
    public function getQuote()
    {
        return $this->data['quote'] ?? null;
    }

    /**
     * Set quote
     *
     * @param Quote $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->data['quote'] = $quote;
        return $this;
    }

    /**
     * Get base price
     *
     * @return float|null
     */
    public function getBasePrice()
    {
        return $this->data['basePrice'] ?? null;
    }

    /**
     * Set base price
     *
     * @param float $price
     * @return $this
     */
    public function setBasePrice($price)
    {
        $this->data['basePrice'] = $price;
        return $this;
    }

    /**
     * Get base discount amount
     *
     * @return float|null
     */
    public function getBaseDiscountAmount()
    {
        return $this->data['baseDiscountAmount'] ?? null;
    }

    /**
     * Set base discount amount
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseDiscountAmount($amount)
    {
        $this->data['baseDiscountAmount'] = $amount;
        return $this;
    }

    /**
     * Get base row total
     *
     * @return float|null
     */
    public function getBaseRowTotal()
    {
        return $this->data['baseRowTotal'] ?? null;
    }

    /**
     * Set base row total
     *
     * @param float $total
     * @return $this
     */
    public function setBaseRowTotal($total)
    {
        $this->data['baseRowTotal'] = $total;
        return $this;
    }

    /**
     * Get quantity
     *
     * @return float|null
     */
    public function getQty()
    {
        return $this->data['qty'] ?? null;
    }

    /**
     * Set quantity
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        $this->data['qty'] = $qty;
        return $this;
    }

    /**
     * Get extension attributes
     *
     * @return CartItemExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->data['extensionAttributes'] ?? null;
    }

    /**
     * Set extension attributes
     *
     * @param CartItemExtensionInterface $ext
     * @return $this
     */
    public function setExtensionAttributes($ext)
    {
        $this->data['extensionAttributes'] = $ext;
        return $this;
    }

    /**
     * Get item ID
     *
     * @return int|null
     */
    public function getItemId()
    {
        return $this->data['itemId'] ?? null;
    }

    /**
     * Set item ID
     *
     * @param int $itemId
     * @return $this
     */
    public function setItemId($itemId)
    {
        $this->data['itemId'] = $itemId;
        return $this;
    }

    /**
     * Get custom price
     *
     * @return float|null
     */
    public function getCustomPrice()
    {
        return $this->data['customPrice'] ?? null;
    }

    /**
     * Set custom price
     *
     * @param float $price
     * @return $this
     */
    public function setCustomPrice($price)
    {
        $this->data['customPrice'] = $price;
        return $this;
    }

    /**
     * Get store ID
     *
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->data['storeId'] ?? null;
    }

    /**
     * Set store ID
     *
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->data['storeId'] = $storeId;
        return $this;
    }

    /**
     * Get base tax amount
     *
     * @return float|null
     */
    public function getBaseTaxAmount()
    {
        return $this->data['baseTaxAmount'] ?? null;
    }

    /**
     * Set base tax amount
     *
     * @param float $amount
     * @return $this
     */
    public function setBaseTaxAmount($amount)
    {
        $this->data['baseTaxAmount'] = $amount;
        return $this;
    }

    /**
     * Check if children are calculated
     *
     * @return bool
     */
    public function isChildrenCalculated()
    {
        return !empty($this->children);
    }

    /**
     * Set children
     *
     * @param array|null $children
     * @return $this
     */
    public function setChildren($children)
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Get buy request
     *
     * @return DataObject|null
     */
    public function getBuyRequest()
    {
        return $this->data['buyRequest'] ?? null;
    }

    /**
     * Set buy request
     *
     * @param DataObject $buyRequest
     * @return $this
     */
    public function setBuyRequest($buyRequest)
    {
        $this->data['buyRequest'] = $buyRequest;
        return $this;
    }

    /**
     * Set product
     *
     * @param ProductInterface $product
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * Add option
     *
     * @param mixed $option
     * @return $this
     */
    public function addOption($option)
    {
        return $this;
    }

    /**
     * Set original custom price
     *
     * @param float $price
     * @return $this
     */
    public function setOriginalCustomPrice($price)
    {
        $this->data['originalCustomPrice'] = $price;
        return $this;
    }

    /**
     * Set no discount flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setNoDiscount($flag)
    {
        $this->data['noDiscount'] = $flag;
        return $this;
    }

    /**
     * Check if data exists
     *
     * @param string $key
     * @return bool
     */
    public function hasData($key = '')
    {
        if ($key === 'custom_price') {
            return isset($this->data['hasCustomPrice']) ? $this->data['hasCustomPrice'] : false;
        }
        return isset($this->data[$key]);
    }

    /**
     * Set has custom price flag
     *
     * @param bool $flag
     * @return $this
     */
    public function setHasCustomPrice($flag)
    {
        $this->data['hasCustomPrice'] = $flag;
        return $this;
    }

    /**
     * Unset data
     *
     * @param string|null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->data = [];
        } elseif ($key === 'custom_price') {
            $this->data['hasCustomPrice'] = false;
        } else {
            unset($this->data[$key]);
        }
        return $this;
    }

    /**
     * Get message
     *
     * @param bool $string
     * @return array
     */
    public function getMessage($string = true)
    {
        return $this->data['message'] ?? [];
    }

    /**
     * Set message
     *
     * @param array $message
     * @return $this
     */
    public function setMessage($message)
    {
        $this->data['message'] = $message;
        return $this;
    }

    /**
     * Check if item is deleted
     *
     * @param bool|null $isDeleted
     * @return bool|$this
     */
    public function isDeleted($isDeleted = null)
    {
        if ($isDeleted !== null) {
            $this->data['isDeleted'] = $isDeleted;
            return $this;
        }
        return $this->data['isDeleted'] ?? false;
    }

    /**
     * Get parent item
     *
     * @return mixed
     */
    public function getParentItem()
    {
        return $this->data['parentItem'] ?? null;
    }

    /**
     * Set parent item
     *
     * @param mixed $parentItem
     * @return $this
     */
    public function setParentItem($parentItem)
    {
        $this->data['parentItem'] = $parentItem;
        return $this;
    }

    /**
     * Get has error
     *
     * @return bool
     */
    public function getHasError()
    {
        return $this->data['hasError'] ?? false;
    }

    /**
     * Set has error
     *
     * @param bool $error
     * @return $this
     */
    public function setHasError($error)
    {
        $this->data['hasError'] = $error;
        return $this;
    }

    /**
     * Get product type
     *
     * @return string|null
     */
    public function getProductType()
    {
        return $this->data['productType'] ?? null;
    }

    /**
     * Set product type
     *
     * @param string $type
     * @return $this
     */
    public function setProductType($type)
    {
        $this->data['productType'] = $type;
        return $this;
    }

    /**
     * Check if item can be configured
     *
     * @return bool
     */
    public function canConfigure()
    {
        return $this->data['canConfigure'] ?? true;
    }

    /**
     * Set can configure flag
     *
     * @param bool $canConfigure
     * @return $this
     */
    public function setCanConfigure($canConfigure)
    {
        $this->data['canConfigure'] = $canConfigure;
        return $this;
    }

    /**
     * Get SKU
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->data['sku'] ?? null;
    }

    /**
     * Set SKU
     *
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        $this->data['sku'] = $sku;
        return $this;
    }

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->data['name'] ?? null;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->data['name'] = $name;
        return $this;
    }

    /**
     * Get price
     *
     * @return float|null
     */
    public function getPrice()
    {
        return $this->data['price'] ?? null;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return $this
     */
    public function setPrice($price)
    {
        $this->data['price'] = $price;
        return $this;
    }

    /**
     * Get product option
     *
     * @return mixed
     */
    public function getProductOption()
    {
        return $this->data['productOption'] ?? null;
    }

    /**
     * Set product option
     *
     * @param mixed $productOption
     * @return $this
     */
    public function setProductOption($productOption)
    {
        $this->data['productOption'] = $productOption;
        return $this;
    }

    /**
     * Get quote ID
     *
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->data['quoteId'] ?? null;
    }

    /**
     * Set quote ID
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        $this->data['quoteId'] = $quoteId;
        return $this;
    }

    /**
     * Get data
     *
     * @param string $key
     * @param mixed $index
     * @return mixed
     */
    public function getData($key = '', $index = null)
    {
        if ($key === '') {
            return $this->data;
        }
        return $this->data[$key] ?? null;
    }

    /**
     * Set data
     *
     * @param string|array $key
     * @param mixed $value
     * @return $this
     */
    public function setData($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }
        return $this;
    }

    /**
     * Get product ID
     *
     * @return int|null
     */
    public function getProductId()
    {
        return $this->data['productId'] ?? null;
    }

    /**
     * Set product ID
     *
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        $this->data['productId'] = $productId;
        return $this;
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->data['id'] = $id;
        return $this;
    }
}
