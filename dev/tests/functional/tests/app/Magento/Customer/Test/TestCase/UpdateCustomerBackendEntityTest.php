<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Precondition:
 * 1. Create customer.
 *
 * Steps:
 * 1. Login to backend as admin.
 * 2. Navigate to CUSTOMERS->All Customers.
 * 3. Open from grid test customer.
 * 4. Edit some values, if addresses fields are not presented click 'Add New Address' button.
 * 5. Click 'Save' button.
 * 6. Perform all assertions.
 *
 * @ZephyrId MAGETWO-23881
 */
class UpdateCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Customer grid page.
     *
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * Customer edit page.
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEditPage;

    /**
     * FixtureFactory.
     *
     * @var FixtureFactory
     */
    private $fixtureFactory;

    /**
     * Inject FixtureFactory.
     *
     * @param FixtureFactory $fixtureFactory
     * @return void
     */
    public function __prepare(FixtureFactory $fixtureFactory)
    {
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Inject pages.
     *
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     * @return void
     */
    public function __inject(
        CustomerIndex $customerIndexPage,
        CustomerIndexEdit $customerIndexEditPage
    ) {
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * Prepares customer returned after test.
     *
     * @param Customer $customer
     * @param Customer $initialCustomer
     * @param Address|null $address
     * @param Address|null $addressToDelete
     * @return Customer
     */
    private function prepareCustomer(
        Customer $customer,
        Customer $initialCustomer,
        Address $address = null,
        Address $addressToDelete = null
    ) {
        $data = $customer->hasData()
            ? array_replace_recursive($initialCustomer->getData(), $customer->getData())
            : $initialCustomer->getData();
        $groupId = $customer->hasData('group_id') ? $customer : $initialCustomer;
        $data['group_id'] = ['customerGroup' => $groupId->getDataFieldConfig('group_id')['source']->getCustomerGroup()];
        $customerAddress = $this->prepareCustomerAddress($initialCustomer, $address, $addressToDelete);
        if (!empty($customerAddress)) {
            $data['address'] = $customerAddress;
        }

        return $this->fixtureFactory->createByCode(
            'customer',
            ['data' => $data]
        );
    }

    /**
     * Prepare address for customer entity.
     *
     * @param Customer $initialCustomer
     * @param Address|null $address
     * @param Address|null $addressToDelete
     * @return array
     */
    private function prepareCustomerAddress(
        Customer $initialCustomer,
        Address $address = null,
        Address $addressToDelete = null
    ) {
        $customerAddress = [];

        if ($initialCustomer->hasData('address')) {
            $addressesList = $initialCustomer->getDataFieldConfig('address')['source']->getAddresses();
            foreach ($addressesList as $key => $addressFixture) {
                if ($addressToDelete === null || $addressFixture != $address) {
                    $customerAddress = ['addresses' => [$key => $addressFixture]];
                }
            }
        }
        if ($address !== null) {
            $customerAddress['addresses'][] = $address;
        }

        return $customerAddress;
    }

    /**
     * Run update customer test.
     *
     * @param Customer $initialCustomer
     * @param Customer $customer
     * @param Address|null $address
     * @param int|null $addressIndexToDelete
     * @throws \Exception
     * @return array
     */
    public function testUpdateCustomerBackendEntity(
        Customer $initialCustomer,
        Customer $customer,
        Address $address = null,
        $addressIndexToDelete = null
    ) {
        // Precondition
        $initialCustomer->persist();

        $addressToDelete = null;
        if ($addressIndexToDelete !== null) {
            $addressToDelete =
                $initialCustomer->getDataFieldConfig('address')['source']->getAddresses()[$addressIndexToDelete];
        }

        // Steps
        $filter = ['email' => $initialCustomer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getCustomerForm()->updateCustomer($customer, $address, $addressToDelete);
        $this->customerIndexEditPage->getPageActionsBlock()->save();

        return [
            'customer' => $this->prepareCustomer($customer, $initialCustomer, $address, $addressToDelete),
            'addressToDelete' => $addressToDelete,
        ];
    }
}
