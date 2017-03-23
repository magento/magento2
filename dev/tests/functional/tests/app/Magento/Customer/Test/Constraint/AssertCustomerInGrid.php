<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerInGrid
 *
 */
class AssertCustomerInGrid extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'middle';
    /* end tags */

    /**
     * Assert customer availability in Customer Grid
     *
     * @param Customer $customer
     * @param CustomerIndex $pageCustomerIndex
     * @return void
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        Customer $customer,
        CustomerIndex $pageCustomerIndex
    ) {
        $customer = $customer->getData();
        $name = (isset($customer['prefix']) ? $customer['prefix'] . ' ' : '')
            . $customer['firstname']
            . (isset($customer['middlename']) ? ' ' . $customer['middlename'] : '')
            . ' ' . $customer['lastname']
            . (isset($customer['suffix']) ? ' ' . $customer['suffix'] : '');
        $filter = [
            'name' => $name,
            'email' => $customer['email'],
        ];

        $pageCustomerIndex->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $pageCustomerIndex->getCustomerGridBlock()->isRowVisible($filter),
            'Customer with '
            . 'name \'' . $filter['name'] . '\', '
            . 'email \'' . $filter['email'] . '\' '
            . 'is absent in Customer grid.'
        );
    }

    /**
     * Text success exist Customer in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer is present in Customer grid.';
    }
}
