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
 * Assert that system customer group cannot be deleted.
 */
class AssertNoDeleteForSystemCustomerGroup extends AbstractConstraint
{
    /**
     * Assert that delete button is not available for system customer group.
     *
     * @param CustomerGroupEdit $customerGroupEdit
     * @return void
     */
    public function processAssert(CustomerGroupEdit $customerGroupEdit)
    {
        \PHPUnit\Framework\Assert::assertFalse(
            $customerGroupEdit->getPageMainActions()->checkDeleteButton(),
            "Delete button is visible."
        );
    }

    /**
     * Success assert of customer group not possible to delete.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group is not possible to delete.';
    }
}
