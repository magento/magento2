<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Helper;

use Magento\Quote\Api\Data\PaymentExtension;

/**
 * Test helper for PaymentExtension to expose getAgreementIds()/setAgreementIds() for unit tests.
 */
class PaymentExtensionTestHelper extends PaymentExtension
{
    /** @var array|null */
    private ?array $agreementIds = null;

    public function __construct()
    {
        // Skip parent constructor
    }

    /**
     * Get agreement IDs for tests.
     *
     * @return array<int>|null
     */
    public function getAgreementIds(): ?array
    {
        return $this->agreementIds;
    }

    /**
     * Set agreement IDs for tests.
     *
     * @param array<int> $agreementIds
     * @return $this
     */
    public function setAgreementIds($agreementIds)
    {
        $this->agreementIds = $agreementIds;
        return $this;
    }
}
