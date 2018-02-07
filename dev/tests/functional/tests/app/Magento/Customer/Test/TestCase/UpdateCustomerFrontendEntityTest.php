<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Constraint\AssertCustomerInfoSuccessSavedMessage;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountEdit;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAddressEdit;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 * 1. Default test customer is created.
 *
 * Steps:
 * 1. Login to fronted as test customer from preconditions.
 * 2. Navigate to Account Dashboard page.
 * 3. Click "Edit" link near "Contact Information".
 * 4. Fill fields with test data and save.
 * 5. Click "Edit Address" link near "Default Billing Address", save and return to Account Dashboard page.
 * 6. Fill fields with test data and save.
 * 7. Perform all assertions.
 *
 * @group Customer_Account_(CS)
 * @ZephyrId MAGETWO-25925
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCustomerFrontendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const DOMAIN = 'CS';
    const TEST_TYPE = 'acceptance_test, extended_acceptance_test';
    /* end tags */

    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * CmsIndex page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * CustomerAccountIndex page.
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * CustomerAccountEdit page.
     *
     * @var CustomerAccountEdit
     */
    protected $customerAccountEdit;

    /**
     * CustomerAddressEdit page.
     *
     * @var CustomerAddressEdit
     */
    protected $customerAddressEdit;

    /**
     * Preparing data for test.
     *
     * @param CmsIndex $cmsIndex
     * @param FixtureFactory $fixtureFactory
     * @param CustomerAccountIndex $customerAccountIndex
     * @param CustomerAccountEdit $customerAccountEdit
     * @param CustomerAddressEdit $customerAddressEdit
     * @return void
     */
    public function __inject(
        CmsIndex $cmsIndex,
        FixtureFactory $fixtureFactory,
        CustomerAccountIndex $customerAccountIndex,
        CustomerAccountEdit $customerAccountEdit,
        CustomerAddressEdit $customerAddressEdit
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->fixtureFactory = $fixtureFactory;
        $this->customerAccountIndex = $customerAccountIndex;
        $this->customerAccountEdit = $customerAccountEdit;
        $this->customerAddressEdit = $customerAddressEdit;
    }

    /**
     * Prepares customer returned after test.
     *
     * @param Customer $customer
     * @param Customer $initialCustomer
     * @return Customer
     */
    private function prepareCustomer(
        Customer $customer,
        Customer $initialCustomer
    ) {
        if (!$customer->hasData()) {
            return $initialCustomer;
        }
        $data = array_replace_recursive($initialCustomer->getData(), $customer->getData());
        $data['group_id'] = [
            'customerGroup' => $initialCustomer->getDataFieldConfig('group_id')['source']->getCustomerGroup()
        ];

        return $this->fixtureFactory->createByCode('customer', ['data' => $data]);
    }

    /**
     * Run Update Customer Entity test.
     *
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @param Address $address
     * @param AssertCustomerInfoSuccessSavedMessage $assertCustomerInfoSuccessSavedMessage
     * @return array
     */
    public function test(
        Customer $initialCustomer,
        Customer $customer,
        Address $address,
        AssertCustomerInfoSuccessSavedMessage $assertCustomerInfoSuccessSavedMessage
    ) {
        // Preconditions
        $initialCustomer->persist();

        // Steps
        $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $initialCustomer]
        )->run();
        $this->customerAccountIndex->getInfoBlock()->openEditContactInfo();
        $this->customerAccountEdit->getAccountInfoForm()->fill($customer);
        $this->customerAccountEdit->getAccountInfoForm()->submit();

        \PHPUnit_Framework_Assert::assertThat($this->getName(), $assertCustomerInfoSuccessSavedMessage);

        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->customerAccountIndex->getDashboardAddress()->editBillingAddress();
        $this->customerAddressEdit->getEditForm()->fill($address);
        $this->customerAddressEdit->getEditForm()->saveAddress();

        return ['customer' => $this->prepareCustomer($customer, $initialCustomer)];
    }
}
