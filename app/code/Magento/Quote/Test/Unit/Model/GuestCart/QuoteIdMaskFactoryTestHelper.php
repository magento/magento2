<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\GuestCart;

use Magento\Quote\Model\QuoteIdMaskFactory;

/**
 * Test helper factory to return a prepared QuoteIdMaskTestHelper instance.
 */
class QuoteIdMaskFactoryTestHelper extends QuoteIdMaskFactory
{
    /** @var QuoteIdMaskTestHelper */
    private $instance;

    public function __construct(QuoteIdMaskTestHelper $instance)
    {
        // Do not call parent; store instance for create()
        $this->instance = $instance;
    }

    public function create(array $data = [])
    {
        // Touch $data to satisfy PHPMD; behavior returns prebuilt instance
        if (!empty($data)) {
            // no-op
        }
        return $this->instance;
    }
}
