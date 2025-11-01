<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote;

/**
 * Test helper for Quote model used in unit tests to avoid complex constructor
 * dependencies and to emulate additional methods expected by legacy tests.
 */
class QuoteTestHelper extends Quote
{
    /**
     * @var array
     */
    private array $testData = [];

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
}
