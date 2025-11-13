<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Helper;

use Magento\SalesRule\Model\Rule\Customer;

/**
 * Test helper for Rule Customer
 *
 * This helper extends the concrete Customer class to provide test-specific functionality
 * without dependency injection issues.
 *
 * Custom Methods:
 * - loadByCustomerRule() - Overrides parent to avoid database access in tests
 *
 * Inherited Methods (from AbstractModel and parent magic methods):
 * - getId() / setId() - Available from AbstractModel
 * - getTimesUsed() / setTimesUsed() - Available via parent class magic methods (@method annotations)
 * - getRuleId() / setRuleId() - Available via parent class magic methods
 * - getCustomerId() / setCustomerId() - Available via parent class magic methods
 *
 * All data is stored in the parent's $_data array via AbstractModel's getData/setData methods.
 */
class RuleCustomerTestHelper extends Customer
{
    /**
     * Constructor that skips parent initialization
     */
    public function __construct()
    {
        // Skip parent constructor to avoid dependency injection issues
    }

    /**
     * Load by customer rule
     *
     * Overrides parent method to avoid database access in tests.
     * Parent implementation requires initialized resource model.
     * Sets a default ID to simulate a loaded entity.
     *
     * @param int $customerId
     * @param int $ruleId
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function loadByCustomerRule($customerId, $ruleId)
    {
        // Set a default ID to simulate successful load
        parent::setId(1);
        return $this;
    }
}
