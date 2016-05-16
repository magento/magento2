<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupEdit;

/**
 * Class AssertNoDeleteForSystemCustomerGroup.
 */
class AssertNoDeleteForSystemCustomerGroup extends AbstractConstraint
{
    /**
     * Assert that system customer group NOT LOGGED IN is not possible to delete.
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param CustomerGroupEdit $customerGroupEdit
     * @return void
     */
    public function processAssert(
        CustomerGroupIndex $customerGroupIndex,
        CustomerGroupEdit $customerGroupEdit
    ) {
        $filter = [
            'code' => 'NOT LOGGED IN',
        ];

        $customerGroupIndex->open();
        $customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        \PHPUnit_Framework_Assert::assertFalse(
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
