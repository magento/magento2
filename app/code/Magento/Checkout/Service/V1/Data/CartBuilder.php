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
namespace Magento\Checkout\Service\V1\Data;

use \Magento\Checkout\Service\V1\Data\Cart\Currency;

/**
 * Cart data object builder
 *
 * @codeCoverageIgnore
 */
class CartBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Cart/quote id
     *
     * @param int $value
     * @return $this
     */
    public function setId($value)
    {
        return $this->_set(Cart::ID, $value);
    }

    /**
     * Store id
     *
     * @param int $value
     * @return $this
     */
    public function setStoreId($value)
    {
        return $this->_set(Cart::STORE_ID, $value);
    }

    /**
     * set creation date and time
     *
     * @param string $value
     * @return $this
     */
    public function setCreatedAt($value)
    {
        return $this->_set(Cart::CREATED_AT, $value);
    }

    /**
     * Set last update date and time
     *
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt($value)
    {
        return $this->_set(Cart::UPDATED_AT, $value);
    }

    /**
     * Set convertion date and time
     *
     * @param string $value
     * @return $this
     */
    public function setConvertedAt($value)
    {
        return $this->_set(Cart::CONVERTED_AT, $value);
    }

    /**
     * Set active status
     *
     * @param bool|null $value
     * @return $this
     */
    public function setIsActive($value)
    {
        return $this->_set(Cart::IS_ACTIVE, $value);
    }

    /**
     * Set virtual flag(if cart contains virtual products)
     *
     * @param bool|null $value
     * @return $this
     */
    public function setIsVirtual($value)
    {
        return $this->_set(Cart::IS_VIRTUAL, $value);
    }

    /**
     * Set cart items
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Item[] $value
     * @return $this
     */
    public function setItems($value)
    {
        return $this->_set(Cart::ITEMS, $value);
    }

    /**
     * Set items count(amount of different products)
     *
     * @param int $value
     * @return $this
     */
    public function setItemsCount($value)
    {
        return $this->_set(Cart::ITEMS_COUNT, $value);
    }

    /**
     * Set items quantity(total amount of all products)
     *
     * @param float $value
     * @return $this
     */
    public function setItemsQty($value)
    {
        return $this->_set(Cart::ITEMS_QUANTITY, $value);
    }

    /**
     * Set customer data object
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Customer $value
     * @return $this
     */
    public function setCustomer($value)
    {
        return $this->_set(Cart::CUSTOMER, $value);
    }

    /**
     * Set checkout method
     *
     * @param string $value
     * @return $this
     */
    public function setCheckoutMethod($value)
    {
        return $this->_set(Cart::CHECKOUT_METHOD, $value);
    }

    /**
     * Set shipping address data object
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $value
     * @return $this
     */
    public function setShippingAddress($value)
    {
        return $this->_set(Cart::SHIPPING_ADDRESS, $value);
    }

    /**
     * Set billing address data object
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart\Address $value
     * @return $this
     */
    public function setBillingAddress($value)
    {
        return $this->_set(Cart::BILLING_ADDRESS, $value);
    }

    /**
     * @param \Magento\Checkout\Service\V1\Data\Cart\Totals $value
     * @return $this
     */
    public function setTotals($value)
    {
        return $this->_set(Cart::TOTALS, $value);
    }

    /**
     * Set reserved order id
     *
     * @param string $value
     * @return $this
     */
    public function setReservedOrderId($value)
    {
        return $this->_set(Cart::RESERVED_ORDER_ID, $value);
    }

    /**
     * Set original order id
     *
     * @param string $value
     * @return $this
     */
    public function setOrigOrderId($value)
    {
        return $this->_set(Cart::ORIG_ORDER_ID, $value);
    }

    /**
     * @param \Magento\Checkout\Service\V1\Data\Cart\Currency|null $value
     * @return $this
     */
    public function setCurrency($value)
    {
        return $this->_set(Cart::CURRENCY, $value);
    }
}
