<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;

/**
 * Assert that group field is not available.
 */
class AssertGroupNotAvailableInField extends AbstractConstraint
{
    /**
     * Assert that customer group field is not available.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param CustomerGroup $customerGroup
     * @param array $disabledFields
     * @return void
     */
    public function processAssert(
        CustomerGroupIndex $customerGroupIndex,
        CustomerGroup $customerGroup,
        array $disabledFields
    ) {
        foreach ($disabledFields as $field) {
            \PHPUnit_Framework_Assert::assertTrue(
                $customerGroupIndex->getPageMainForm()->isFieldDisabled($field),
                "Field for group {$customerGroup->getCustomerGroupCode()} is not disabled."
            );
        }
    }

    /**
     * Success assert of customer group field not available.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group field is not available.';
    }
}
