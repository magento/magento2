<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartExtensionInterface;
use BadMethodCallException;

/**
 * Test helper for Quote model used in unit tests to avoid complex constructor
 * dependencies and to emulate additional methods expected by legacy tests.
 *
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class QuoteTestHelper extends Quote
{
    /**
     * @var array
     */
    private array $testData = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor intentionally left empty to skip parent constructor.
     */
    public function __construct()
    {
        // Intentionally empty to skip parent constructor with complex dependencies
    }

    /**
     * Set shared store ids for the quote instance in tests.
     *
     * @param array $ids
     * @return $this
     */
    public function setSharedStoreIds($ids)
    {
        $this->testData['shared_store_ids'] = $ids;
        return $this;
    }

    /**
     * Get customer id set for the quote instance in tests.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->testData['customer_id'] ?? null;
    }

    /**
     * Set customer id for the quote instance in tests.
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->testData['customer_id'] = $customerId;
        return $this;
    }

    /**
     * Set customer group id for the quote instance in tests.
     *
     * @param mixed $groupId
     * @return $this
     */
    public function setCustomerGroupId($groupId)
    {
        $this->testData['customer_group_id'] = $groupId;
        return $this;
    }

    /**
     * Get customer group id for the quote instance in tests.
     *
     * @return mixed
     */
    public function getCustomerGroupId()
    {
        return $this->testData['customer_group_id'] ?? null;
    }

    /**
     * Set website for the quote in tests.
     *
     * @param mixed $website
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->testData['website'] = $website;
        return $this;
    }

    /**
     * Get base currency code for tests.
     *
     * @return string|null
     */
    public function getBaseCurrencyCode()
    {
        return $this->testData['base_currency_code'] ?? null;
    }

    /**
     * Get quote currency code for tests.
     *
     * @return string|null
     */
    public function getQuoteCurrencyCode()
    {
        return $this->testData['quote_currency_code'] ?? null;
    }

    /**
     * Set coupon code for tests.
     *
     * @param string $code
     * @return $this
     */
    public function setCouponCode($code)
    {
        $this->testData['coupon_code'] = $code;
        return $this;
    }

    /**
     * Get coupon code for tests.
     *
     * @return string|null
     */
    public function getCouponCode()
    {
        return $this->testData['coupon_code'] ?? null;
    }

    /**
     * Get password hash for tests.
     *
     * @return string|null
     */
    public function getPasswordHash()
    {
        return $this->testData['password_hash'] ?? null;
    }

    /**
     * Set totals collected flag for tests.
     *
     * @param bool $flag
     * @return $this
     */
    public function setTotalsCollectedFlag($flag)
    {
        $this->testData['totals_collected'] = (bool)$flag;
        return $this;
    }

    /**
     * Get customer email for tests.
     *
     * @return string|null
     */
    public function getCustomerEmail()
    {
        return $this->testData['customer_email'] ?? null;
    }

    /**
     * Set customer email for tests.
     *
     * @param string|null $email
     * @return $this
     */
    public function setCustomerEmail($email)
    {
        $this->testData['customer_email'] = $email;
        return $this;
    }

    /**
     * Set remote IP for tests.
     *
     * @param string $ip
     * @return $this
     */
    public function setRemoteIp($ip)
    {
        $this->testData['remote_ip'] = $ip;
        return $this;
    }

    /**
     * Set X-Forwarded-For header value for tests.
     *
     * @param string $forwarded
     * @return $this
     */
    public function setXForwardedFor($forwarded)
    {
        $this->testData['x_forwarded_for'] = $forwarded;
        return $this;
    }

    /**
     * Get customer first name for tests.
     *
     * @return string|null
     */
    public function getCustomerFirstname()
    {
        return $this->testData['customer_firstname'] ?? null;
    }

    /**
     * Get customer last name for tests.
     *
     * @return string|null
     */
    public function getCustomerLastname()
    {
        return $this->testData['customer_lastname'] ?? null;
    }

    /**
     * Get customer middle name for tests.
     *
     * @return string|null
     */
    public function getCustomerMiddlename()
    {
        return $this->testData['customer_middlename'] ?? null;
    }

    /**
     * Set customer first name for tests.
     *
     * @param string|null $firstname
     * @return $this
     */
    public function setCustomerFirstname($firstname)
    {
        $this->testData['customer_firstname'] = $firstname;
        return $this;
    }

    /**
     * Set customer last name for tests.
     *
     * @param string|null $lastname
     * @return $this
     */
    public function setCustomerLastname($lastname)
    {
        $this->testData['customer_lastname'] = $lastname;
        return $this;
    }

    /**
     * Set customer middle name for tests.
     *
     * @param string|null $middlename
     * @return $this
     */
    public function setCustomerMiddlename($middlename)
    {
        $this->testData['customer_middlename'] = $middlename;
        return $this;
    }

    /**
     * Get last added item id for tests.
     *
     * @return int|string|null
     */
    public function getLastAddedItem()
    {
        return $this->testData['last_added_item'] ?? null;
    }

    /**
     * Set last added item for tests.
     *
     * @param mixed $item
     * @return $this
     */
    public function setLastAddedItem($item)
    {
        $this->testData['last_added_item'] = $item;
        return $this;
    }

    /**
     * Get has error flag for tests.
     *
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    // phpcs:ignore Magento2.NamingConvention.PublicMethodName
    public function getHasError(): bool
    {
        return (bool)($this->testData['has_error'] ?? false);
    }

    /**
     * Set has error flag for tests.
     *
     * @param bool $flag
     * @return $this
     */
    public function setHasError($flag)
    {
        $this->testData['has_error'] = (bool)$flag;
        return $this;
    }

    /**
     * Add message for tests.
     *
     * @param mixed $message
     * @return $this
     */
    public function addMessage($message, $index = 'error')
    {
        $messages = $this->testData['messages'] ?? [];
        if (isset($messages[$index])) {
            return $this;
        }
        $messages[$index] = (string)$message;
        $this->testData['messages'] = $messages;
        return $this;
    }

    /**
     * Get shipping method title for tests.
     *
     * @return string|null
     */
    public function getMethodTitle()
    {
        return $this->testData['method_title'] ?? null;
    }

    /**
     * Get carrier title for tests.
     *
     * @return string|null
     */
    public function getCarrierTitle()
    {
        return $this->testData['carrier_title'] ?? null;
    }

    /**
     * Get price excluding tax for tests.
     *
     * @return mixed
     */
    public function getPriceExclTax()
    {
        return $this->testData['price_excl_tax'] ?? null;
    }

    /**
     * Get price including tax for tests.
     *
     * @return mixed
     */
    public function getPriceInclTax()
    {
        return $this->testData['price_incl_tax'] ?? null;
    }

    /**
     * Get is multi shipping flag for tests.
     *
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    // phpcs:ignore Magento2.NamingConvention.PublicMethodName
    public function getIsMultiShipping()
    {
        return (bool)($this->testData['is_multi_shipping'] ?? false);
    }

    /**
     * Get data
     *
     * Core method: Provides access to all stored data.
     * Replaces 50+ individual getter methods.
     *
     * Usage: $quote->getData('id') instead of $quote->getId()
     *
     * @param string $key
     * @param mixed $index
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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
     * Core method: Provides access to set all data.
     * Replaces 50+ individual setter methods.
     *
     * Usage: $quote->setData('id', 123) instead of $quote->setId(123)
     *        $quote->setData(['id' => 123, 'storeId' => 1]) for bulk set
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

    // ========== OVERRIDES FOR CONCRETE PARENT METHODS ==========
    // These methods exist in parent Quote class and are commonly used in tests.
    // We override them to route through getData/setData to avoid dependency issues.

    /**
     * Override: Get ID
     * @return int|null
     */
    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * Override: Set ID
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData('id', $id);
    }

    /**
     * Override: Get extension attributes
     * @return CartExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->getData('extensionAttributes');
    }

    /**
     * Override: Set extension attributes
     *
     * Note: Type hint removed to accept mocks and null values in tests
     *
     * @param CartExtensionInterface|null $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes($extensionAttributes)
    {
        return $this->setData('extensionAttributes', $extensionAttributes);
    }

    /**
     * Override: Get store ID
     * @return int|null
     */
    public function getStoreId()
    {
        return $this->getData('storeId');
    }

    /**
     * Override: Set store ID
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        return $this->setData('storeId', $storeId);
    }

    /**
     * Override: Collect totals (stub to avoid calling parent's complex logic)
     * @return $this
     */
    public function collectTotals()
    {
        return $this;
    }

    /**
     * Override: Is virtual
     *
     * Checks both 'isVirtual' and 'is_virtual' for backward compatibility.
     * Magic method setIsVirtual() stores as 'is_virtual' (snake_case).
     *
     * @return bool
     */
    public function isVirtual()
    {
        return (bool)($this->getData('isVirtual') ?: $this->getData('is_virtual'));
    }

    /**
     * Override: Get all items
     *
     * Checks multiple possible data keys for compatibility
     *
     * @return array
     */
    public function getAllItems()
    {
        $items = $this->getData('allItems')
            ?: $this->getData('all_items')
            ?: $this->getData('items')
            ?: [];

        return is_array($items) ? $items : [];
    }

    /**
     * Set all items
     *
     * Also sets allVisibleItems for compatibility with tests
     *
     * @param array $items
     * @return $this
     */
    public function setAllItems($items)
    {
        $this->setData('allItems', $items);
        // Some tests expect allVisibleItems to be populated when allItems is set
        if (!$this->getData('allVisibleItems')) {
            $this->setData('allVisibleItems', $items);
        }
        return $this;
    }

    /**
     * Override: Get all visible items
     *
     * Returns test data directly without calling parent logic.
     * Parent's getAllVisibleItems() requires item objects with isDeleted() method.
     *
     * Checks multiple possible data keys for compatibility:
     * - allVisibleItems (set by setAllVisibleItems)
     * - visible_items (set by magic setVisibleItems)
     * - all_visible_items (snake_case variant)
     * - items (generic fallback)
     * - allItems (set by setAllItems)
     *
     * @return array
     */
    public function getAllVisibleItems()
    {
        // Return directly from data storage - don't call parent
        $items = $this->getData('allVisibleItems')
            ?: $this->getData('visible_items')
            ?: $this->getData('all_visible_items')
            ?: $this->getData('items')
            ?: $this->getData('allItems');

        // Handle different data types
        if ($items instanceof \Traversable) {
            // Convert iterator to array
            return iterator_to_array($items);
        }

        return is_array($items) ? $items : [];
    }

    /**
     * Set all visible items
     *
     * Accepts arrays, iterators, or traversable objects
     *
     * @param array|\Traversable $items
     * @return $this
     */
    public function setAllVisibleItems($items)
    {
        $this->setData('allVisibleItems', $items);
        return $this;
    }

    /**
     * Get items collection
     *
     * @param bool $useCache
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getItemsCollection($useCache = true)
    {
        return $this->getData('itemsCollection') ?: $this->getAllItems();
    }

    /**
     * Set items collection
     *
     * @param mixed $items
     * @return $this
     */
    public function setItemsCollection($items)
    {
        $this->setData('itemsCollection', $items);
        return $this;
    }

    /**
     * Set item by ID (for testing getItemById)
     *
     * @param int $id
     * @param mixed $item
     * @return $this
     */
    public function setItemById($id, $item)
    {
        $items = $this->getData('itemById') ?? [];
        $items[$id] = $item;
        $this->setData('itemById', $items);
        return $this;
    }

    /**
     * Override: Unset data
     *
     * @param string|null $key
     * @return $this
     */
    public function unsetData($key = null)
    {
        if ($key === null) {
            $this->data = [];
        } else {
            unset($this->data[$key]);
        }
        return $this;
    }

    /**
     * Override: Get item by ID
     * @param int $id
     * @return mixed
     */
    public function getItemById($id)
    {
        $items = $this->getData('itemById') ?? [];
        return $items[$id] ?? null;
    }

    /**
     * Override: Remove item
     * @param int $id
     * @return $this
     */
    public function removeItem($id)
    {
        $items = $this->getData('itemById') ?? [];
        unset($items[$id]);
        $this->setData('itemById', $items);
        return $this;
    }

    /**
     * Override: Get payment
     * @return mixed
     */
    public function getPayment()
    {
        return $this->getData('payment');
    }

    /**
     * Override: Set payment
     *
     * Note: Type hint removed to accept mocks (OrderPaymentInterface, etc.) in tests
     *
     * @param mixed $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        return $this->setData('payment', $payment);
    }

    /**
     * Override: Get billing address
     * @return AddressInterface|null
     */
    public function getBillingAddress()
    {
        return $this->getData('billingAddress');
    }

    /**
     * Override: Set billing address
     * @param AddressInterface|null $billingAddress
     * @return $this
     */
    public function setBillingAddress(?AddressInterface $billingAddress = null)
    {
        return $this->setData('billingAddress', $billingAddress);
    }

    /**
     * Override: Get shipping address
     * @return AddressInterface|null
     */
    public function getShippingAddress()
    {
        return $this->getData('shippingAddress');
    }

    /**
     * Override: Set shipping address
     * @param AddressInterface|null $shippingAddress
     * @return $this
     */
    public function setShippingAddress(?AddressInterface $shippingAddress = null)
    {
        return $this->setData('shippingAddress', $shippingAddress);
    }

    /**
     * Override: Get all addresses
     * @return array
     */
    public function getAllAddresses()
    {
        return $this->getData('allAddresses') ?? [];
    }

    /**
     * Set all addresses
     *
     * @param array $addresses
     * @return $this
     */
    public function setAllAddresses($addresses)
    {
        $this->setData('allAddresses', $addresses);
        return $this;
    }

    // ========== CUSTOM HELPER METHODS ==========
    // These methods provide test-specific functionality not available in parent.

    /**
     * Set super mode (stub for testing)
     * @param mixed $value
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setIsSuperMode($value)
    {
        return $this;
    }

    /**
     * Remove all addresses (test helper)
     * @return $this
     */
    public function removeAllAddresses()
    {
        $this->setData('allAddresses', []);
        return $this;
    }

    /**
     * Remove all items (test helper)
     * @return $this
     */
    public function removeAllItems()
    {
        $this->setData('itemsCollection', []);
        $this->setData('allVisibleItems', []);
        $this->setData('allItems', []);
        return $this;
    }

    // ========== END OF EXPLICIT METHODS ==========

    /**
     * Magic method to handle removed getter/setter methods
     *
     * This enables backward compatibility for tests still using old method names.
     * Maps getXxx() to getData('xxx') and setXxx($v) to setData('xxx', $v).
     *
     * IMPORTANT: Always routes through getData/setData instead of parent methods
     * because parent methods may require dependencies that aren't initialized.
     *
     * Examples:
     *   $quote->getCustomerId()         → $quote->getData('customerId')
     *   $quote->setCustomerId(123)      → $quote->setData('customerId', 123)
     *   $quote->getCouponCode()         → $quote->getData('couponCode')
     *   $quote->setCouponCode('ABC')    → $quote->setData('couponCode', 'ABC')
     *   $quote->isVirtual()             → (bool)$quote->getData('isVirtual')
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        // Handle getXxx() methods - always use getData, never parent
        if (strpos($method, 'get') === 0 && strlen($method) > 3) {
            $key = lcfirst(substr($method, 3));
            $key = $this->camelCaseToSnakeCase($key);
            return $this->getData($key);
        }

        // Handle setXxx($value) methods - always use setData, never parent
        if (strpos($method, 'set') === 0 && strlen($method) > 3) {
            $key = lcfirst(substr($method, 3));
            $key = $this->camelCaseToSnakeCase($key);
            $value = $args[0] ?? null;
            return $this->setData($key, $value);
        }

        // Handle isXxx() methods
        if (strpos($method, 'is') === 0 && strlen($method) > 2) {
            $key = lcfirst(substr($method, 2));
            $key = $this->camelCaseToSnakeCase($key);
            return (bool)$this->getData($key);
        }

        // No fallback to parent - always throw exception for undefined methods
        // This prevents calling parent methods that require uninitialized dependencies
        throw new BadMethodCallException(
            "Method $method does not exist in " . __CLASS__ . ". " .
            "Use getData/setData for dynamic properties."
        );
    }

    /**
     * Convert camelCase to snake_case
     *
     * @param string $input
     * @return string
     */
    private function camelCaseToSnakeCase($input)
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $input));
    }
}
