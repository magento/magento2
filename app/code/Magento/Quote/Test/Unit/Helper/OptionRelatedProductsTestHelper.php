<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Item\Option;

/**
 * Test helper for Quote Item Option to expose getProductId() explicitly for PHPUnit 12 mocks.
 */
class OptionRelatedProductsTestHelper extends Option
{
    /**
     * Empty constructor to avoid parent dependencies in unit tests.
     */
    public function __construct()
    {
        // Intentionally empty
    }

    /**
     * Get product id from internal data for tests.
     *
     * @return int|string|null
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }
}
