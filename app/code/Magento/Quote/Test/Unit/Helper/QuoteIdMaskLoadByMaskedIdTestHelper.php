<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\QuoteIdMask;

/**
 * Test helper that loads a fixed quote id when load() is called with a masked id.
 */
class QuoteIdMaskLoadByMaskedIdTestHelper extends QuoteIdMask
{
    /** @var int|string */
    private $quoteId;

    /**
     * @param int|string $quoteId
     */
    public function __construct($quoteId)
    {
        $this->quoteId = $quoteId;
    }

    /**
     * @param string $id
     * @param string|null $field
     * @return $this
     */
    public function load($id, $field = null)
    {
        // Touch parameters to satisfy PHPMD but ignore for test logic
        if ($id !== null || $field !== null) {
            // no-op
        }
        $this->setData('quote_id', $this->quoteId);
        return $this;
    }
}
