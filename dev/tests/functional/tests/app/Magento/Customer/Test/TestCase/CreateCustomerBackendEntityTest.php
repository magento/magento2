<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\User\Test\Fixture\User;
use Magento\User\Test\Page\Adminhtml\UserEdit;
use Magento\User\Test\Page\Adminhtml\UserIndex;

/**
 * Steps:
 * 1. Log in as default admin user.
 * 2. Go to Customers > All Customers.
 * 3. Press "Add New Customer" button.
 * 4. Fill form.
 * 5. Click "Save Customer" button.
 * 6. Perform all assertions.
 *
 * @ZephyrId MAGETWO-23424
 */
class CreateCustomerBackendEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = 'extended_acceptance_test';
    /* end tags */

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * @var Address
     */
    private $address;

    /** @var array  */
    private $allowedCountriesData = [];

    /**
     * Customer index page.
     *
     * @var CustomerIndex
     */
    protected $pageCustomerIndex;

    /**
     * New customer page.
     *
     * @var CustomerIndexNew
     */
    protected $pageCustomerIndexNew;

    /** @var  FixtureFactory */
    private $fixtureFactory;

    /**
     * @var UserEdit
     */
    private $userEdit;

    /**
     * @var UserIndex
     */
    private $userIndex;

    /**
     * Array of steps.
     *
     * @var array
     */
    private $steps;

    /**
     * Inject customer pages.
     *
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexNew $pageCustomerIndexNew
     * @param FixtureFactory $fixtureFactory
     * @param UserEdit $userEdit
     * @param UserIndex $userIndex
     * @return void
     */
    public function __inject(
        CustomerIndex $pageCustomerIndex,
        CustomerIndexNew $pageCustomerIndexNew,
        \Magento\Mtf\Fixture\FixtureFactory $fixtureFactory,
        UserEdit $userEdit,
        UserIndex $userIndex
    ) {
        $this->pageCustomerIndex = $pageCustomerIndex;
        $this->pageCustomerIndexNew = $pageCustomerIndexNew;
        $this->fixtureFactory = $fixtureFactory;
        $this->userEdit = $userEdit;
        $this->userIndex = $userIndex;
    }

    /**
     * Create customer on backend.
     *
     * @param Customer $customer
     * @param string $customerAction
     * @param Address $address
     * @param array $steps
     * @param array $beforeActionCallback
     * @return void
     */
    public function test(
        Customer $customer,
        $customerAction,
        Address $address = null,
        array $steps = [],
        array $beforeActionCallback = []
    ) {
        $this->steps = $steps;
        ///Process initialize steps
        foreach ($steps as $methodName => $stepData) {
            if (method_exists($this, $methodName)) {
                call_user_func_array([$this, $methodName], $stepData);
            }
        }

        $this->pageCustomerIndex->open();
        $this->pageCustomerIndex->getPageActionsBlock()->addNew();
        $this->pageCustomerIndexNew->getCustomerForm()->fillCustomer($customer, $address);
        $this->address = $address;
        $this->customer = $customer;

        foreach ($beforeActionCallback as $methodName) {
            if (method_exists($this, $methodName)) {
                call_user_func([$this, $methodName]);
            }
        }

        $this->pageCustomerIndexNew->getPageActionsBlock()->$customerAction();
    }

    /**
     * Assert that allowed countries renders in correct way.
     * @return void
     */
    protected function assertAllowedCountries()
    {
        /** @var \Magento\Customer\Test\Constraint\AssertChangingWebsiteChangeCountries $assert */
        $assert = $this->objectManager->get(
            \Magento\Customer\Test\Constraint\AssertChangingWebsiteChangeCountries::class
        );

        foreach ($this->allowedCountriesData as $dataPerWebsite) {
            $customerWithWebsite = $this->fixtureFactory->createByCode(
                'customer',
                [
                    'data' => [
                        'website_id' => $dataPerWebsite['website']->getName()
                    ]
                ]
            );
            $assert->processAssert(
                $this->pageCustomerIndexNew,
                $customerWithWebsite,
                $dataPerWebsite['countries']
            );
        }

        $this->pageCustomerIndexNew->getCustomerForm()->openTab('account_information');
        $this->pageCustomerIndexNew->getCustomerForm()->fillCustomer($this->customer);
        $this->pageCustomerIndexNew->getCustomerForm()->openTab('addresses');
        $this->pageCustomerIndexNew->getCustomerForm()->getTab('addresses')->updateAddresses($this->address);
    }

    /**
     * @return \Magento\Store\Test\Fixture\Website
     */
    private function createWebsiteFixture()
    {
        /** @var \Magento\Store\Test\Fixture\Website $websiteFixture */
        $websiteFixture = $this->fixtureFactory->createByCode('website', ['dataset' => 'custom_website']);
        $websiteFixture->persist();
        $storeGroupFixture = $this->fixtureFactory->createByCode(
            'storeGroup',
            [
                'data' => [
                    'website_id' => [
                        'fixture' => $websiteFixture
                    ],
                    'root_category_id' => [
                        'dataset' => 'default_category'
                    ],
                    'name' => 'Store_Group_%isolation%',
                ]
            ]
        );
        $storeGroupFixture->persist();
        /** @var \Magento\Store\Test\Fixture\Store $storeFixture */
        $storeFixture = $this->fixtureFactory->createByCode(
            'store',
            [
                'data' => [
                    'website_id' => $websiteFixture->getWebsiteId(),
                    'group_id' => [
                        'fixture' => $storeGroupFixture
                    ],
                    'is_active' => true,
                    'name' => 'Store_%isolation%',
                    'code' => 'store_%isolation%'
                ]
            ]
        );
        $storeFixture->persist();

        return $websiteFixture;
    }

    /**
     * @param array $countryList
     */
    protected function configureAllowedCountries(array $countryList = [])
    {
        foreach ($countryList as $countries) {
            $websiteFixture = $this->createWebsiteFixture();
            /** @var FixtureInterface $configFixture */
            $configFixture = $this->fixtureFactory->createByCode(
                'configData',
                [
                    'data' => [
                        'general/country/allow' => [
                            'value' => $countries
                        ],
                        'scope' => [
                            'fixture' => $websiteFixture,
                            'scope_type' => 'website',
                            'website_id' => $websiteFixture->getWebsiteId(),
                            'set_level' => 'website',
                        ]
                    ]
                ]
            );

            $configFixture->persist();
            $this->allowedCountriesData[] = [
                'website' => $websiteFixture,
                'countries' => explode(",", $countries)
            ];
        }
    }

    /**
     * Change Admin locale.
     *
     * @param array $userData
     */
    protected function changeAdminLocale(array $userData)
    {
        /** @var User $adminUser */
        $adminUser = $this->fixtureFactory->createByCode('user', ['data' => $userData]);
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen(['username' => $adminUser->getUsername()]);
        $this->userEdit->getUserForm()->fill($adminUser);
        $this->userEdit->getPageActions()->save();
    }

    /**
     * Revert Admin locale.
     */
    protected function changeAdminLocaleRollback()
    {
        /** @var User $defaultAdminUser */
        $defaultAdminUser = $this->fixtureFactory->createByCode('user');
        $adminUserData = $defaultAdminUser->getData();
        unset($adminUserData['user_id']);
        $defaultAdminUser = $this->fixtureFactory->createByCode('user', ['data' => $adminUserData]);
        $this->userIndex->open();
        $this->userIndex->getUserGrid()->searchAndOpen(['username' => $defaultAdminUser->getUsername()]);
        $this->userEdit->getUserForm()->fill($defaultAdminUser);
        $this->userEdit->getPageActions()->save();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        foreach ($this->steps as $key => $stepData) {
            if (method_exists($this, $key . 'Rollback')) {
                call_user_func_array([$this, $key . 'Rollback'], $stepData);
            }
        }
    }
}
