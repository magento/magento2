<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function processAssert(
        Customer $customer,
        CustomerIndex $pageCustomerIndex
    ) {
        $customerData = $customer->getData();
        $name = (isset($customerData['prefix']) ? $customerData['prefix'] . ' ' : '')
            . $customerData['firstname']
            . (isset($customerData['middlename']) ? ' ' . $customerData['middlename'] : '')
            . ' ' . $customerData['lastname']
            . (isset($customerData['suffix']) ? ' ' . $customerData['suffix'] : '');
        $filter = [
            'name' => $name,
            'email' => $customerData['email'],
        ];
        $errorMessage = 'Customer with '
            . 'name \'' . $filter['name'] . '\', '
            . 'email \'' . $filter['email'] . '\'';

        if ($customer->hasData('dob')) {
            $filter['dob_from'] = $customer->getData('dob');
            $filter['dob_to'] = $customer->getData('dob');
        }

        $pageCustomerIndex->open();
        $pageCustomerIndex->getCustomerGridBlock()->isRowVisible($filter);
        if ($customer->hasData('dob')) {
            unset($filter['dob_from']);
            unset($filter['dob_to']);
            $filter['dob'] = $this->prepareDob($customer->getData('dob'));
            $errorMessage .= ', dob \'' . $filter['dob'] . '\' ';
        }

        $errorMessage .= 'is absent in Customer grid.';

        \PHPUnit\Framework\Assert::assertTrue(
            $pageCustomerIndex->getCustomerGridBlock()->isRowVisible($filter, false),
            $errorMessage
        );
    }

    /**
     * Prepare dob string to grid date format.
     *
     * @param string $date
     * @return false|string
     */
    private function prepareDob($date)
    {
        return date('M d, Y', strtotime($date));
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
