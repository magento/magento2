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
 * Class AssertCustomerGroupInGrid
 */
class AssertCustomerGroupInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that customer group in grid
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
        \PHPUnit_Framework_Assert::assertTrue(
            $customerGroupIndex->getCustomerGroupGrid()->isRowVisible($filter),
            'Group with type \'' . $customerGroup->getCustomerGroupCode() . '\'is absent in customer groups grid.'
        );
    }

    /**
     * Success assert of  customer group in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group in grid.';
    }
}
