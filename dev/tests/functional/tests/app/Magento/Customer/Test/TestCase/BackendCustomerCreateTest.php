<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;

class BackendCustomerCreateTest extends Functional
{
    /**
     * Login into backend area before test
     */
    protected function setUp()
    {
        Factory::getApp()->magentoBackendLoginUser();
    }

    /**
     * New customer creation in backend
     *
     * @ZephyrId MAGETWO-12516
     */
    public function testCreateCustomer()
    {
        //Data
        $customerFixture = Factory::getFixtureFactory()->getMagentoCustomerCustomer();
        $customerFixture->switchData('backend_customer');
        $searchData = [
            'email' => $customerFixture->getEmail(),
        ];
        //Pages
        $customerPage = Factory::getPageFactory()->getCustomerIndex();
        $customerCreatePage = Factory::getPageFactory()->getCustomerIndexNew();

        //Steps
        $customerPage->open();
        $customerPage->getPageActionsBlock()->addNew();
        $customerCreatePage->getCustomerForm()->fillCustomer($customerFixture);
        $customerCreatePage->getPageActionsBlock()->saveAndContinue();
        $customerCreatePage->getMessagesBlock()->waitSuccessMessage();

        //Verifying
        $customerPage->open();
        $this->assertTrue(
            $customerPage->getCustomerGridBlock()->isRowVisible($searchData),
            'Customer email "' . $searchData['email'] . '" not found in the grid'
        );
    }
}
