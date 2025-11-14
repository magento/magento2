<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\QuoteIdMask;

/**
 * Test helper for creating a controllable QuoteIdMask instance.
 */
class QuoteIdMaskTestHelper extends QuoteIdMask
{
    /** @var int|string|null */
    private $quoteId;

    /** @var string|null */
    private $maskedId = null;

    /**
     * @param int|string|null $quoteId
     */
    public function __construct($quoteId = null)
    {
        $this->quoteId = $quoteId;
    }

    /**
     * @param int|string $id
     * @param string|null $field
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($id, $field = null)
    {
        $this->setData('quote_id', $id);
        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMaskedId()
    {
        return $this->maskedId;
    }

    /**
     * @param int|string $id
     * @return $this
     */
    public function setQuoteId($id)
    {
        $this->quoteId = $id;
        return $this;
    }
}
