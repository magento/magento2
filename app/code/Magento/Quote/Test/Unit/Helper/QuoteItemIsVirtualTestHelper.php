<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item;

/**
 * Test helper that exposes a virtual quote item implementation for unit tests.
 *
 * @SuppressWarnings(PHPMD.BooleanGetMethodName)
 */
class QuoteItemIsVirtualTestHelper extends Item
{
    /**
     * Override parent constructor; not needed for tests.
     */
    public function __construct()
    {
    }

    /**
     * Indicates that the quote item is virtual.
     *
     * @return bool
     */
    public function getIsVirtual()
    {
        return true;
    }
}
