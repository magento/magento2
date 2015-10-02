<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroup;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert that customer group find on account information page.
 */
class AssertCustomerGroupOnCustomerForm extends AbstractConstraint
{
    /**
     * Assert that customer group find on account information page.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerGroup $customerGroup
     * @param CustomerIndexNew $customerIndexNew
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CustomerGroup $customerGroup,
        CustomerIndexNew $customerIndexNew,
        CustomerIndex $customerIndex
    ) {
        /** @var Customer $customer */
        $customer = $fixtureFactory->createByCode(
            'customer',
            [
                'dataset' => 'defaultBackend',
                'data' => ['group_id' => ['customerGroup' => $customerGroup]]
            ]
        );
        $filter = ['email' => $customer->getEmail()];

        $customerIndexNew->open();
        $customerIndexNew->getCustomerForm()->fillCustomer($customer);
        $customerIndexNew->getPageActionsBlock()->save();
        $customerIndex->getCustomerGridBlock()->searchAndOpen($filter);
        $customerFormData = $customerIndexNew->getCustomerForm()->getData($customer);
        $customerFixtureData = $customer->getData();
        $diff = array_diff($customerFixtureData, $customerFormData);

        \PHPUnit_Framework_Assert::assertTrue(
            empty($diff),
            "Customer group {$customerGroup->getCustomerGroupCode()} not in account information page."
        );
    }

    /**
     * Success assert of customer group find on account information page.
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group find on account information page.';
    }
}
