<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data;

/**
 * Cart data object.
 *
 * @codeCoverageIgnore
 */
class Cart extends \Magento\Framework\Api\AbstractExtensibleObject
{
    /**
     * Cart ID.
     */
    const ID = 'id';

    /**
     * ID of the store where the cart was created.
     */
    const STORE_ID = 'store_id';

    /**
     * Cart creation date and time.
     */
    const CREATED_AT = 'created_at';

    /**
     * Cart last update date and time.
     */
    const UPDATED_AT = 'updated_at';

    /**
     * Cart conversion date and time.
     */
    const CONVERTED_AT = 'converted_at';

    /**
     * Flag that shows whether the cart is still active.
     */
    const IS_ACTIVE = 'is_active';

    /**
     * Flag that shows whether the cart is virtual. A virtual cart contains virtual items.
     */
    const IS_VIRTUAL = 'is_virtual';

    /**
     * List of cart items.
     */
    const ITEMS = 'items';

    /**
     * Number of different items or products in the cart.
     */
    const ITEMS_COUNT = 'items_count';

    /**
     * Total quantity of all cart items.
     */
    const ITEMS_QUANTITY = 'items_qty';

    /**
     * Information about the customer who is assigned to the cart.
     */
    const CUSTOMER = 'customer';

    /**
     * Payment method that is used to process the cart.
     */
    const CHECKOUT_METHOD = 'checkout_method';

    /**
     * Cart shipping address.
     */
    const SHIPPING_ADDRESS = 'shipping_address';

    /**
     * Cart billing address.
     */
    const BILLING_ADDRESS = 'shipping_address';

    /**
     * Information about cart totals.
     */
    const TOTALS = 'totals';

    /**
     * The order ID that is reserved for the cart.
     */
    const RESERVED_ORDER_ID = 'reserved_order_id';

    /**
     * Original order ID.
     */
    const ORIG_ORDER_ID = 'orig_order_id';

    /**
     * Information about the quote currency, such as code, exchange rates, and so on.
     */
    const CURRENCY = 'currency';

    /**
     * Returns the cart/quote ID.
     *
     * @return int Cart/quote ID.
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Returns the store ID for the store where the cart was created.
     *
     * @return int|null Store ID. Otherwise, null.
     */
    public function getStoreId()
    {
        return $this->_get(self::STORE_ID);
    }

    /**
     * Returns the cart creation date and time.
     *
     * @return string|null Cart creation date and time. Otherwise, null.
     */
    public function getCreatedAt()
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Returns the cart last update date and time.
     *
     * @return string|null Cart last update date and time. Otherwise, null.
     */
    public function getUpdatedAt()
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Returns the cart conversion date and time.
     *
     * @return string|null Cart conversion date and time. Otherwise, null.
     */
    public function getConvertedAt()
    {
        return $this->_get(self::CONVERTED_AT);
    }

    /**
     * Determines whether the cart is still active.
     *
     * @return bool|null Active status flag value. Otherwise, null.
     */
    public function getIsActive()
    {
        $value = $this->_get(self::IS_ACTIVE);
        if (!is_null($value)) {
            $value = (bool)$value;
        }

        return $value;
    }

    /**
     * Determines whether the cart is a virtual cart.
     *
     * A virtual cart contains virtual items.
     *
     * @return bool|null Virtual flag value. Otherwise, null.
     */
    public function getIsVirtual()
    {
        $value = $this->_get(self::IS_VIRTUAL);
        if (!is_null($value)) {
            $value = (bool)$value;
        }

        return $value;
    }

    /**
     * Lists items in the cart.
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Item[]|null Array of items. Otherwise, null.
     */
    public function getItems()
    {
        return $this->_get(self::ITEMS);
    }

    /**
     * Returns the number of different items or products in the cart.
     *
     * @return int|null Number of different items or products in the cart. Otherwise, null.
     */
    public function getItemsCount()
    {
        return $this->_get(self::ITEMS_COUNT);
    }

    /**
     * Returns the total quantity of all cart items.
     *
     * @return float|null Total quantity of all cart items. Otherwise, null.
     */
    public function getItemsQty()
    {
        return $this->_get(self::ITEMS_QUANTITY);
    }

    /**
     * Returns information about the customer who is assigned to the cart.
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Customer Information about the customer who is assigned to the cart.
     */
    public function getCustomer()
    {
        return $this->_get(self::CUSTOMER);
    }

    /**
     * Returns the payment method that is used to process the cart.
     *
     * @return string|null Payment method. Otherwise, null.
     */
    public function getCheckoutMethod()
    {
        return $this->_get(self::CHECKOUT_METHOD);
    }

    /**
     * Returns the cart shipping address.
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address|null Cart shipping address. Otherwise, null.
     */
    public function getShippingAddress()
    {
        return $this->_get(self::SHIPPING_ADDRESS);
    }

    /**
     * Returns the cart billing address.
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Address|null Cart billing address. Otherwise, null.
     */
    public function getBillingAddress()
    {
        return $this->_get(self::BILLING_ADDRESS);
    }

    /**
     * Returns information about cart totals.
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Totals|null Information about cart totals. Otherwise, null.
     */
    public function getTotals()
    {
        return $this->_get(self::TOTALS);
    }

    /**
     * Returns the reserved order ID for the cart.
     *
     * @return string|null Reserved order ID. Otherwise, null.
     */
    public function getReservedOrderId()
    {
        return $this->_get(self::RESERVED_ORDER_ID);
    }

    /**
     * Returns the original order ID for the cart.
     *
     * @return string|null Original order ID. Otherwise, null.
     */
    public function getOrigOrderId()
    {
        return $this->_get(self::ORIG_ORDER_ID);
    }

    /**
     * Returns information about quote currency, such as code, exchange rate, and so on.
     *
     * @return \Magento\Checkout\Service\V1\Data\Cart\Currency|null Quote currency information. Otherwise, null.
     */
    public function getCurrency()
    {
        return $this->_get(self::CURRENCY);
    }
}
