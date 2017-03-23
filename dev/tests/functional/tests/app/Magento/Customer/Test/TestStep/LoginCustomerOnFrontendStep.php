<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Login customer on frontend.
 */
class LoginCustomerOnFrontendStep implements TestStepInterface
{
    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer login page.
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Logout customer on frontend step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    protected $logoutCustomerOnFrontend;

    /**
     * @constructor
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountLogin $customerAccountLogin
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend
     * @param Customer $customer
     */
    public function __construct(
        CmsIndex $cmsIndex,
        CustomerAccountLogin $customerAccountLogin,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend,
        Customer $customer
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customer = $customer;
        $this->logoutCustomerOnFrontend = $logoutCustomerOnFrontend;
    }

    /**
     * Login customer.
     *
     * @return void
     */
    public function run()
    {
        $this->logoutCustomerOnFrontend->run();
        $this->cmsIndex->getLinksBlock()->openLink('Sign In');
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
        $this->customerAccountLogin->getLoginBlock()->login($this->customer);
        $this->cmsIndex->getCmsPageBlock()->waitPageInit();
    }

    /**
     * Logout customer on fronted.
     *
     * @return void
     */
    public function cleanup()
    {
        $this->logoutCustomerOnFrontend->run();
    }
}
