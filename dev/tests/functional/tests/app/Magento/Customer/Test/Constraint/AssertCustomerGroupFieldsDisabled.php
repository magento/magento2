<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupEdit;

/**
 * Assert that group fields are not available.
 */
class AssertCustomerGroupFieldsDisabled extends AbstractConstraint
{
    /**
     * Assert that fields are disabled on customer group form.
     *
     * @param CustomerGroupEdit $customerGroupEdit
     * @param array $disabledFields
     * @return void
     */
    public function processAssert(
        CustomerGroupEdit $customerGroupEdit,
        array $disabledFields
    ) {
        foreach ($disabledFields as $field) {
            \PHPUnit_Framework_Assert::assertTrue(
                $customerGroupEdit->getPageMainForm()->isFieldDisabled($field),
                "Field $field is not disabled."
            );
        }
    }

    /**
     * Success assert of customer fields are not available.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer fields are not available.';
    }
}
