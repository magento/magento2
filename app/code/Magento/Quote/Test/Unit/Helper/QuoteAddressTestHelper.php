<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Address;

/**
 * Test helper for Quote Address to provide setPrevQuoteCustomerGroupId used in tests.
 */
class QuoteAddressTestHelper extends Address
{
    /** @var mixed */
    private $prevGroupId;
    /** @var array */
    private $vatData = [];
    /** @var string|null */
    private $addressType;
    /** @var bool|null */
    private $collectShippingRates;

    /**
     * Constructor intentionally empty to skip parent dependencies.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Set previous quote customer group id for test assertions.
     *
     * @param mixed $groupId
     * @return $this
     */
    public function setPrevQuoteCustomerGroupId($groupId)
    {
        $this->prevGroupId = $groupId;
        return $this;
    }

    /**
     * VAT-related getters used in tests
     */
    public function getValidatedCountryCode()
    {
        return $this->vatData['validated_country_code'] ?? null;
    }

    public function getValidatedVatNumber()
    {
        return $this->vatData['validated_vat_number'] ?? null;
    }

    public function getVatIsValid()
    {
        return $this->vatData['vat_is_valid'] ?? null;
    }

    public function getVatRequestId()
    {
        return $this->vatData['vat_request_id'] ?? null;
    }

    public function getVatRequestDate()
    {
        return $this->vatData['vat_request_date'] ?? null;
    }

    public function getVatRequestSuccess()
    {
        return $this->vatData['vat_request_success'] ?? null;
    }

    /**
     * Address type getter used by VatValidator tests.
     *
     * @return string|null
     */
    public function getAddressType()
    {
        return $this->addressType;
    }

    /**
     * Explicit getter for delete immediately flag used by tests.
     *
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    // phpcs:ignore Magento2.NamingConvention.PublicMethodName
    public function getDeleteImmediately()
    {
        return (bool)($this->getData('delete_immediately') ?? false);
    }

    /**
     * Address type setter for tests.
     *
     * @param string|null $type
     * @return $this
     */
    public function setAddressType($type)
    {
        $this->addressType = $type;
        return $this;
    }

    /**
     * Emulate setting payment method on address for tests.
     *
     * @param string $method
     * @return $this
     */
    public function setPaymentMethod($method)
    {
        $this->setData('payment_method', $method);
        return $this;
    }

    /**
     * Emulate setting collect shipping rates flag used by tests.
     *
     * @param bool $flag
     * @return $this
     */
    public function setCollectShippingRates($flag)
    {
        $this->collectShippingRates = (bool)$flag;
        return $this;
    }

    /**
     * Explicit getter for quote id used by tests.
     *
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData('quote_id');
    }

    /**
     * Get customer address object for tests.
     *
     * @return mixed
     */
    public function getCustomerAddress()
    {
        return $this->getData('customer_address');
    }

    /**
     * No-op save method for tests.
     *
     * @return $this
     */
    public function save()
    {
        return $this;
    }

    /**
     * Import customer address data for tests.
     *
     * @param mixed $address
     * @return $this
     */
    public function importCustomerAddressData($address)
    {
        $this->setData('customer_address', $address);
        return $this;
    }

    /**
     * Get save in address book flag for tests.
     *
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    // phpcs:ignore Magento2.NamingConvention.PublicMethodName
    public function getSaveInAddressBook()
    {
        return (bool)($this->getData('save_in_address_book') ?? false);
    }

    /**
     * Get same as billing flag for tests.
     *
     * @return bool
     */
    /**
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    // phpcs:ignore Magento2.NamingConvention.PublicMethodName
    public function getSameAsBilling()
    {
        return (bool)($this->getData('same_as_billing') ?? false);
    }

    /**
     * Get customer address id for tests.
     *
     * @return int|null
     */
    public function getCustomerAddressId()
    {
        return $this->getData('customer_address_id');
    }

    /**
     * Set company for tests.
     *
     * @param mixed $company
     * @return $this
     */
    public function setCompany($company)
    {
        $this->setData('company', $company);
        return $this;
    }

    /**
     * Set same as billing flag for tests.
     *
     * @param mixed $flag
     * @return $this
     */
    public function setSameAsBilling($flag)
    {
        $this->setData('same_as_billing', (bool)$flag);
        return $this;
    }

    /**
     * Set save in address book flag for tests.
     *
     * @param mixed $flag
     * @return $this
     */
    public function setSaveInAddressBook($flag)
    {
        $this->setData('save_in_address_book', (bool)$flag);
        return $this;
    }

    /**
     * Get shipping amount for tests.
     *
     * @return float|int|string|null
     */
    public function getShippingAmount()
    {
        return $this->getData('shipping_amount');
    }

    /**
     * Get method for tests.
     *
     * @return string|null
     */
    public function getMethod()
    {
        return $this->getData('method');
    }

    /**
     * Get related order id for tests.
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData('order_id');
    }

    /**
     * Get related order object for tests.
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->getData('order');
    }
}
