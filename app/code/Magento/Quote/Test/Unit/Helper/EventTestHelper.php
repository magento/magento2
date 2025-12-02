<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Framework\Event;

/**
 * Test helper for Magento Framework Event to expose getOrder/setOrder methods
 * used by legacy unit tests for the Quote module.
 */
class EventTestHelper extends Event
{
    /**
     * @var mixed
     */
    private $order;
    /** @var mixed */
    private $quote;
    /** @var mixed */
    private $customerDataObject;
    /** @var mixed */
    private $origCustomerDataObject;

    /**
     * Constructor intentionally left empty to skip parent constructor.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Get order data from event helper.
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order data on event helper.
     *
     * @param mixed $order
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get quote from event helper.
     *
     * @return mixed
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Set quote on event helper.
     *
     * @param mixed $quote
     * @return $this
     */
    public function setQuote($quote)
    {
        $this->quote = $quote;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerDataObject()
    {
        return $this->customerDataObject;
    }

    /**
     * @param mixed $customer
     * @return $this
     */
    public function setCustomerDataObject($customer)
    {
        $this->customerDataObject = $customer;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrigCustomerDataObject()
    {
        return $this->origCustomerDataObject;
    }

    /**
     * @param mixed $customer
     * @return $this
     */
    public function setOrigCustomerDataObject($customer)
    {
        $this->origCustomerDataObject = $customer;
        return $this;
    }
}
