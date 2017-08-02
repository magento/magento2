<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Api\Data;

/**
 * Interface CartInterface
 * @api
 * @since 2.0.0
 */
interface CartInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    /**#@+
     * Constants defined for keys of array, makes typos less likely
     */
    const KEY_ID = 'id';

    const KEY_ENTITY_ID = 'entity_id';

    const KEY_CREATED_AT = 'created_at';

    const KEY_UPDATED_AT = 'updated_at';

    const KEY_CONVERTED_AT = 'converted_at';

    const KEY_IS_ACTIVE = 'is_active';

    const KEY_IS_VIRTUAL = 'is_virtual';

    const KEY_ITEMS = 'items';

    const KEY_ITEMS_COUNT = 'items_count';

    const KEY_ITEMS_QTY = 'items_qty';

    const KEY_CUSTOMER = 'customer';

    const KEY_CHECKOUT_METHOD = 'checkout_method';

    const KEY_SHIPPING_ADDRESS = 'shipping_address';

    const KEY_BILLING_ADDRESS = 'billing_address';

    const KEY_RESERVED_ORDER_ID = 'reserved_order_id';

    const KEY_ORIG_ORDER_ID = 'orig_order_id';

    const KEY_CURRENCY = 'currency';

    const KEY_CUSTOMER_IS_GUEST = 'customer_is_guest';

    const KEY_CUSTOMER_NOTE = 'customer_note';

    const KEY_CUSTOMER_NOTE_NOTIFY = 'customer_note_notify';

    const KEY_CUSTOMER_TAX_CLASS_ID = 'customer_tax_class_id';

    const KEY_STORE_ID = 'store_id';

    /**#@-*/

    /**
     * Returns the cart/quote ID.
     *
     * @return int Cart/quote ID.
     * @since 2.0.0
     */
    public function getId();

    /**
     * Sets the cart/quote ID.
     *
     * @param int $id
     * @return $this
     * @since 2.0.0
     */
    public function setId($id);

    /**
     * Returns the cart creation date and time.
     *
     * @return string|null Cart creation date and time. Otherwise, null.
     * @since 2.0.0
     */
    public function getCreatedAt();

    /**
     * Sets the cart creation date and time.
     *
     * @param string $createdAt
     * @return $this
     * @since 2.0.0
     */
    public function setCreatedAt($createdAt);

    /**
     * Returns the cart last update date and time.
     *
     * @return string|null Cart last update date and time. Otherwise, null.
     * @since 2.0.0
     */
    public function getUpdatedAt();

    /**
     * Sets the cart last update date and time.
     *
     * @param string $updatedAt
     * @return $this
     * @since 2.0.0
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Returns the cart conversion date and time.
     *
     * @return string|null Cart conversion date and time. Otherwise, null.
     * @since 2.0.0
     */
    public function getConvertedAt();

    /**
     * Sets the cart conversion date and time.
     *
     * @param string $convertedAt
     * @return $this
     * @since 2.0.0
     */
    public function setConvertedAt($convertedAt);

    /**
     * Determines whether the cart is still active.
     *
     * @return bool|null Active status flag value. Otherwise, null.
     * @since 2.0.0
     */
    public function getIsActive();

    /**
     * Sets whether the cart is still active.
     *
     * @param bool $isActive
     * @return $this
     * @since 2.0.0
     */
    public function setIsActive($isActive);

    /**
     * Determines whether the cart is a virtual cart.
     *
     * A virtual cart contains virtual items.
     *
     * @return bool|null Virtual flag value. Otherwise, null.
     * @since 2.0.0
     */
    public function getIsVirtual();

    /**
     * Lists items in the cart.
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[]|null Array of items. Otherwise, null.
     * @since 2.0.0
     */
    public function getItems();

    /**
     * Sets items in the cart.
     *
     * @param \Magento\Quote\Api\Data\CartItemInterface[] $items
     * @return $this
     * @since 2.0.0
     */
    public function setItems(array $items = null);

    /**
     * Returns the number of different items or products in the cart.
     *
     * @return int|null Number of different items or products in the cart. Otherwise, null.
     * @since 2.0.0
     */
    public function getItemsCount();

    /**
     * Sets the number of different items or products in the cart.
     *
     * @param int $itemsCount
     * @return $this
     * @since 2.0.0
     */
    public function setItemsCount($itemsCount);

    /**
     * Returns the total quantity of all cart items.
     *
     * @return float|null Total quantity of all cart items. Otherwise, null.
     * @since 2.0.0
     */
    public function getItemsQty();

    /**
     * Sets the total quantity of all cart items.
     *
     * @param float $itemQty
     * @return $this
     * @since 2.0.0
     */
    public function setItemsQty($itemQty);

    /**
     * Returns information about the customer who is assigned to the cart.
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface Information about the customer who is assigned to the cart.
     * @since 2.0.0
     */
    public function getCustomer();

    /**
     * Sets information about the customer who is assigned to the cart.
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return $this
     * @since 2.0.0
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer = null);

    /**
     * Returns the cart billing address.
     *
     * @return \Magento\Quote\Api\Data\AddressInterface|null Cart billing address. Otherwise, null.
     * @since 2.0.0
     */
    public function getBillingAddress();

    /**
     * Sets the cart billing address.
     *
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return $this
     * @since 2.0.0
     */
    public function setBillingAddress(\Magento\Quote\Api\Data\AddressInterface $billingAddress = null);

    /**
     * Returns the reserved order ID for the cart.
     *
     * @return int|null Reserved order ID. Otherwise, null.
     * @since 2.0.0
     */
    public function getReservedOrderId();

    /**
     * Sets the reserved order ID for the cart.
     *
     * @param int $reservedOrderId
     * @return $this
     * @since 2.0.0
     */
    public function setReservedOrderId($reservedOrderId);

    /**
     * Returns the original order ID for the cart.
     *
     * @return int|null Original order ID. Otherwise, null.
     * @since 2.0.0
     */
    public function getOrigOrderId();

    /**
     * Sets the original order ID for the cart.
     *
     * @param int $origOrderId
     * @return $this
     * @since 2.0.0
     */
    public function setOrigOrderId($origOrderId);

    /**
     * Returns information about quote currency, such as code, exchange rate, and so on.
     *
     * @return \Magento\Quote\Api\Data\CurrencyInterface|null Quote currency information. Otherwise, null.
     * @since 2.0.0
     */
    public function getCurrency();

    /**
     * Sets information about quote currency, such as code, exchange rate, and so on.
     *
     * @param \Magento\Quote\Api\Data\CurrencyInterface $currency
     * @return $this
     * @since 2.0.0
     */
    public function setCurrency(\Magento\Quote\Api\Data\CurrencyInterface $currency = null);

    /**
     * True for guest customers, false for logged in customers
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getCustomerIsGuest();

    /**
     * Sets true for guest customers, false for logged in customers
     *
     * @param bool $customerIsGuest
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerIsGuest($customerIsGuest);

    /**
     * Customer notice text
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getCustomerNote();

    /**
     * Sets Customer notice text
     *
     * @param string $customerNote
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerNote($customerNote);

    /**
     * Send customer notification flag
     *
     * @return bool|null
     * @since 2.0.0
     */
    public function getCustomerNoteNotify();

    /**
     * Sets send customer notification flag
     *
     * @param bool $customerNoteNotify
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerNoteNotify($customerNoteNotify);

    /**
     * Get customer tax class ID.
     *
     * @return int|null
     * @since 2.0.0
     */
    public function getCustomerTaxClassId();

    /**
     * Set customer tax class ID.
     *
     * @param int $customerTaxClassId
     * @return $this
     * @since 2.0.0
     */
    public function setCustomerTaxClassId($customerTaxClassId);

    /**
     * Get store identifier
     *
     * @return int
     * @since 2.0.0
     */
    public function getStoreId();

    /**
     * Sets store identifier
     *
     * @param int $storeId
     * @return $this
     * @since 2.0.0
     */
    public function setStoreId($storeId);

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\Quote\Api\Data\CartExtensionInterface|null
     * @since 2.0.0
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes
     * @return $this
     * @since 2.0.0
     */
    public function setExtensionAttributes(\Magento\Quote\Api\Data\CartExtensionInterface $extensionAttributes);
}
