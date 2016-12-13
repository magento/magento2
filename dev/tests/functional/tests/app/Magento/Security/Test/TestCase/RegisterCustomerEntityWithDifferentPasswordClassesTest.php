<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\TestCase;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\TestCase\Injectable;
use Magento\Config\Test\Fixture\ConfigData;

/**
 * Test Flow:
 * 1. Go to frontend.
 * 2. Click Register link.
 * 3. Fill registry form.
 * 4. Click 'Create account' button.
 * 5. Perform assertions.
 *
 * @ZephyrId MAGETWO-49045
 */
class RegisterCustomerEntityWithDifferentPasswordClassesTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Customer registry page.
     *
     * @var CustomerAccountCreate
     */
    protected $customerAccountCreate;

    /**
     * Cms page.
     *
     * @var CmsIndex $cmsIndex
     */
    protected $cmsIndex;

    /**
     * Inject data.
     *
     * @param CustomerAccountCreate $customerAccountCreate
     * @param CmsIndex $cmsIndex
     * @return void
     */
    public function __inject(
        CustomerAccountCreate $customerAccountCreate,
        CmsIndex $cmsIndex
    ) {
        $this->customerAccountCreate = $customerAccountCreate;
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Create Customer account on Storefront.
     *
     * @param Customer $customer
     * @param ConfigData $config
     * @return void
     */
    public function test(Customer $customer, ConfigData $config)
    {
        // Preconditions
        $config->persist();
        // Steps
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink('Create an Account');
        $this->customerAccountCreate->getRegisterForm()->registerCustomer($customer);

        $characterClassesNumber = $config
            ->getData('section')['customer/password/required_character_classes_number']['value'];

        return ['characterClassesNumber' => $characterClassesNumber];
    }

    /**
     * Set default settings and logout customer.
     *
     * @return void
     */
    protected function tearDown()
    {
        //Set default required character classes for the password
        $this->objectManager->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => 'default_required_character_classes_number']
        )->run();
        // Logout customer
        $this->objectManager->create(
            \Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class
        )->run();
    }
}
