<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CheckoutAgreements\Test\Unit\Helper;

use Magento\CheckoutAgreements\Model\Agreement as AgreementModel;

/**
 * Test helper for Agreement model to expose setStores() for PHPUnit 12.
 */
class AgreementModelTestHelper extends AgreementModel
{
    /** @var array|null */
    private ?array $stores = null;

    public function __construct()
    {
        // Intentionally skip parent constructor
    }

    /**
     * Set stores for tests.
     *
     * @param array|null $stores
     * @return $this
     */
    public function setStores($stores)
    {
        $this->stores = $stores;
        return $this;
    }
}
