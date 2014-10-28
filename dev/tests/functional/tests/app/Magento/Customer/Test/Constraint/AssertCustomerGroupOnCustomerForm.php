<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Mtf\Constraint\AbstractConstraint;
use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertCustomerGroupOnCustomerForm
 */
class AssertCustomerGroupOnCustomerForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

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
