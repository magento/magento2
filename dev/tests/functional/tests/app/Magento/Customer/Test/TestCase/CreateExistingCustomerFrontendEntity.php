<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestCase;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Mtf\TestCase\Injectable;

/**
 * Preconditions:
 *  1.Customer is created.
 *
 * Steps:
 * 1. Go to frontend.
 * 2. Click Register link.
 * 3. Fill registry form.
 * 4. Click 'Create account' button.
 * 5. Perform assertions.
 *
 * @group Customer_Account
 * @ZephyrId MAGETWO-23545
 */
class CreateExistingCustomerFrontendEntity extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Page CustomerAccountCreate.
     *
     * @var CustomerAccountCreate
     */
    protected $customerAccountCreate;

    /**
     * Page CustomerAccountLogout.
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Page CmsIndex
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Inject pages.
     *
     * @param CustomerAccountCreate $customerAccountCreate
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CmsIndex $cmsIndex
     * @return array
     */
    public function __inject(
        CustomerAccountCreate $customerAccountCreate,
        CustomerAccountLogout $customerAccountLogout,
        CmsIndex $cmsIndex
    ) {
        $this->customerAccountLogout = $customerAccountLogout;
        $this->customerAccountCreate = $customerAccountCreate;
        $this->cmsIndex = $cmsIndex;
    }

    /**
     * Create Existing Customer account on frontend.
     *
     * @param Customer $customer
     * @return void
     */
    public function testCreateExistingCustomer(Customer $customer)
    {
        // Precondition
        $existingCustomer = clone $customer;
        $customer->persist();

        // Steps
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink('Create an Account');
        $this->customerAccountCreate->getRegisterForm()->registerCustomer($existingCustomer);
    }

    /**
     * Logout customer from frontend account.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->objectManager->create(\Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep::class)->run();
    }
}
