<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert required fields on customer form.
 */
class AssertChangingWebsiteChangeCountries extends AbstractConstraint
{
    /**
     * Assert required fields on customer form.
     *
     * @param CustomerIndexNew $customerNewPage
     * @param array $expectedRequiredFields
     * @return void
     */
    public function processAssert(
        CustomerIndexNew $customerIndexNew,
        Customer $customer,
        $expectedList
    ) {
        $customerIndexNew->getCustomerForm()
            ->openTab('account_information');
        $customerIndexNew->getCustomerForm()->fillCustomer($customer);
        $customerIndexNew->getCustomerForm()
            ->openTab('addresses');
        $tab = $customerIndexNew->getCustomerForm()
            ->getTab('addresses');
        $countriesList = $tab->getCountriesList(1);
        sort($countriesList);
        sort($expectedList);
        \PHPUnit_Framework_Assert::assertEquals(
            $countriesList,
            $expectedList,
            'Wrong country list is displayed.'
        );
    }

    /**
     * Return string representation of object.
     *
     * @return string
     */
    public function toString()
    {
        return 'All required fields on customer form are highlighted.';
    }
}
