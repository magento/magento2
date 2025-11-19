<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Framework\Event\Observer;

/**
 * Test helper for Observer to expose getQuote/getShippingAssignment methods
 * required by observer unit tests.
 */
class ObserverTestHelper extends Observer
{
    /** @var mixed */
    private $quote;

    /** @var mixed */
    private $shippingAssignment;

    /**
     * Constructor intentionally left empty to skip parent.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * @return mixed
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
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
    public function getShippingAssignment()
    {
        return $this->shippingAssignment;
    }

    /**
     * @param mixed $shippingAssignment
     * @return $this
     */
    public function setShippingAssignment($shippingAssignment)
    {
        $this->shippingAssignment = $shippingAssignment;
        return $this;
    }
}
