<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertCustomerGroupOnCustomerForm
 */
class AssertCustomerGroupOnCustomerForm extends AbstractConstraint
{
    /* tags */
    const SEVERITY = 'low';
    /* end tags */

    /**
     * Assert that customer group find on account information page
     *
     * @param FixtureFactory $fixtureFactory
     * @param CustomerGroupInjectable $customerGroup
     * @param CustomerIndexNew $customerIndexNew
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CustomerGroupInjectable $customerGroup,
        CustomerIndexNew $customerIndexNew,
        CustomerIndex $customerIndex
    ) {
        /** @var CustomerInjectable $customer */
        $customer = $fixtureFactory->createByCode(
            'customerInjectable',
            [
                'dataSet' => 'defaultBackend',
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
            "Customer group {$customerGroup->getCustomerGroupCode()} not in customer form."
        );
    }

    /**
     * Success assert of customer group find on account information page
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group find on account information page.';
    }
}
