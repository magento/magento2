<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert customer name in Contact information block and Account info tab.
 */
class AssertCustomerNameFrontend extends AbstractConstraint
{
    /**
     * Asserts that customer name in Contact information block and Account info tab matches name in fixture.
     *
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAccountEdit $customerAccountEdit
     * @param Customer $customer
     * @return void
     */
    public function processAssert(
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountEdit $customerAccountEdit,
        Customer $customer
    ) {
        $customerName = $customer->getFirstname() . " " . $customer->getLastname();

        $customerAccountIndex->open();
        $infoBlock = $customerAccountIndex->getInfoBlock()->getContactInfoContent();
        $infoBlock = explode(PHP_EOL, $infoBlock);
        $nameInDashboard = $infoBlock[0];
        \PHPUnit_Framework_Assert::assertTrue(
            $nameInDashboard == $customerName,
            'Customer name in Contact info block is not matching the fixture.'
        );

        $customerAccountIndex->getInfoBlock()->openEditContactInfo();
        $nameInEdit = $customerAccountEdit->getAccountInfoForm()->getFirstName()
            . " " . $customerAccountEdit->getAccountInfoForm()->getLastName();
        \PHPUnit_Framework_Assert::assertTrue(
            $nameInEdit == $customerName,
            'Customer name on Account info tab is not matching the fixture.'
        );
    }

    /**
     * String representation of success assert.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer name in Contact information block and Account info is correct.';
    }
}
