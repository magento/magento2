<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Test\Unit\Helper;

use Magento\Backend\Model\Session\Quote;

/**
 * Test helper for Session\Quote
 *
 * This helper extends the concrete Session\Quote class to provide
 * test-specific functionality without dependency injection issues.
 */
class SessionQuoteTestHelper extends Quote
{
    /**
     * Constructor that skips parent initialization
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Get store ID
     *
     * @return int
     */
    public function getStoreId()
    {
        return 1;
    }
}
