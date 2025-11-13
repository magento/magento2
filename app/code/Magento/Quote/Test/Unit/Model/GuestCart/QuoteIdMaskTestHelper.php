<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMask;

/**
 * Test helper for QuoteIdMask providing controllable load and getters for unit tests.
 */
class QuoteIdMaskTestHelper extends QuoteIdMask
{
    /** @var string|null */
    private $maskedId;

    /** @var int|null */
    private $quoteId;

    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Simulate loading by masked id.
     *
     * @param string $maskedId
     * @return $this
     */
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($modelId, $field = null)
    {
        $this->maskedId = $modelId;
        return $this;
    }

    /**
     * Set the quote id for testing.
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteIdForTest($quoteId)
    {
        $this->quoteId = $quoteId;
        return $this;
    }

    /**
     * Set quote id (compat with production API expectations).
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        $this->quoteId = $quoteId;
        return $this;
    }

    /**
     * Get quote id.
     *
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->quoteId;
    }

    /**
     * Get masked id.
     *
     * @return string|null
     */
    public function getMaskedId()
    {
        return $this->maskedId;
    }
}
