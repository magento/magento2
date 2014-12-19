<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Api\Data;

/**
 * @see \Magento\Checkout\Service\V1\Data\Cart
 */
interface CartInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**
     * Returns the cart/quote ID.
     *
     * @return int Cart/quote ID.
     */
    public function getId();

    /**
     * Returns the store ID for the store where the cart was created.
     *
     * @return int|null Store ID. Otherwise, null.
     */
    public function getStoreId();

    /**
     * Returns the cart creation date and time.
     *
     * @return string|null Cart creation date and time. Otherwise, null.
     */
    public function getCreatedAt();

    /**
     * Returns the cart last update date and time.
     *
     * @return string|null Cart last update date and time. Otherwise, null.
     */
    public function getUpdatedAt();

    /**
     * Returns the cart conversion date and time.
     *
     * @return string|null Cart conversion date and time. Otherwise, null.
     */
    public function getConvertedAt();

    /**
     * Determines whether the cart is still active.
     *
     * @return bool|null Active status flag value. Otherwise, null.
     */
    public function getIsActive();

    /**
     * Determines whether the cart is a virtual cart.
     *
     * A virtual cart contains virtual items.
     *
     * @return bool|null Virtual flag value. Otherwise, null.
     */
    public function getIsVirtual();

    /**
     * Lists items in the cart.
     *
     * @return \Magento\Checkout\Api\Data\CartItemInterface[]|null Array of items. Otherwise, null.
     */
    public function getItems();

    /**
     * Returns the number of different items or products in the cart.
     *
     * @return int|null Number of different items or products in the cart. Otherwise, null.
     */
    public function getItemsCount();

    /**
     * Returns the total quantity of all cart items.
     *
     * @return float|null Total quantity of all cart items. Otherwise, null.
     */
    public function getItemsQty();

    /**
     * Returns information about the customer who is assigned to the cart.
     *
     * @return \Magento\Checkout\Api\Data\CustomerInterface Information about the customer who is assigned to the cart.
     */
    public function getCustomer();

    /**
     * Returns the payment method that is used to process the cart.
     *
     * @return string|null Payment method. Otherwise, null.
     */
    public function getCheckoutMethod();

    /**
     * Returns the cart shipping address.
     *
     * @return \Magento\Checkout\Api\Data\AddressInterface|null Cart shipping address. Otherwise, null.
     */
    public function getShippingAddress();

    /**
     * Returns the cart billing address.
     *
     * @return \Magento\Checkout\Api\Data\AddressInterface|null Cart billing address. Otherwise, null.
     */
    public function getBillingAddress();

    /**
     * Returns information about cart totals.
     *
     * @return \Magento\Checkout\Api\Data\TotalsInterface|null Information about cart totals. Otherwise, null.
     */
    public function getTotals();

    /**
     * Returns the reserved order ID for the cart.
     *
     * @return string|null Reserved order ID. Otherwise, null.
     */
    public function getReservedOrderId();

    /**
     * Returns the original order ID for the cart.
     *
     * @return string|null Original order ID. Otherwise, null.
     */
    public function getOrigOrderId();

    /**
     * Returns information about quote currency, such as code, exchange rate, and so on.
     *
     * @return \Magento\Checkout\Api\Data\CurrencyInterface|null Quote currency information. Otherwise, null.
     */
    public function getCurrency();
}
