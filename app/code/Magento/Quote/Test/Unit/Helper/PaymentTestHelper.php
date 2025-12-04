<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Model\Quote\Payment;

/**
 * Test helper extending Quote Payment to provide setChecks method for PHPUnit 12 migrations.
 */
class PaymentTestHelper extends Payment
{
    /**
     * Constructor intentionally left empty to skip parent dependencies.
     */
    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Emulate setting checks on payment method used by tests.
     *
     * @param array $checks
     * @return $this
     */
    public function setChecks(array $checks)
    {
        // Store checks to avoid unused parameter warnings and aid debugging in tests
        $this->setData('checks', $checks);
        return $this;
    }
}
