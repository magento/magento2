<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupNotInGrid
 */
class AssertCustomerGroupNotInGrid extends AbstractConstraint
{
    /**
     * Assert that customer group not in grid
     *
     * @param CustomerGroup $customerGroup
     * @param CustomerGroupIndex $customerGroupIndex
     * @return void
     */
    public function processAssert(
        CustomerGroup $customerGroup,
        CustomerGroupIndex $customerGroupIndex
    ) {
        $customerGroupIndex->open();
        $filter = ['code' => $customerGroup->getCustomerGroupCode()];
        \PHPUnit_Framework_Assert::assertFalse(
            $customerGroupIndex->getCustomerGroupGrid()->isRowVisible($filter),
            'Group with name \'' . $customerGroup->getCustomerGroupCode() . '\' in customer groups grid.'
        );
    }

    /**
     * Success assert of  customer group not in grid.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group not in grid.';
    }
}
