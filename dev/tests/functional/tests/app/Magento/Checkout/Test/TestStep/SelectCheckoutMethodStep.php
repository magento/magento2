<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\TestStep\LogoutCustomerOnFrontendStep;

/**
 * Selecting checkout method.
 */
class SelectCheckoutMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page.
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Checkout method.
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * Customer fixture.
     *
     * @var Customer
     */
    protected $customer;

    /**
     * Logout customer on frontend step.
     *
     * @var LogoutCustomerOnFrontendStep
     */
    protected $logoutCustomerOnFrontend;

    /**
     * Proceed to checkout from current page without reloading.
     *
     * @var ClickProceedToCheckoutStep
     */
    private $clickProceedToCheckoutStep;

    /**
     * Customer account create page instance.
     *
     * @var CustomerAccountCreate
     */
    private $customerAccountCreatePage;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param CustomerAccountCreate $customerAccountCreatePage
     * @param Customer $customer
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend
     * @param ClickProceedToCheckoutStep $clickProceedToCheckoutStep
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        CustomerAccountCreate $customerAccountCreatePage,
        Customer $customer,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend,
        ClickProceedToCheckoutStep $clickProceedToCheckoutStep,
        $checkoutMethod
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->customerAccountCreatePage = $customerAccountCreatePage;
        $this->customer = $customer;
        $this->logoutCustomerOnFrontend = $logoutCustomerOnFrontend;
        $this->clickProceedToCheckoutStep = $clickProceedToCheckoutStep;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Run step that selecting checkout method.
     *
     * @return void
     */
    public function run()
    {
        $this->processLogin();
        $this->processRegister();
    }

    /**
     * Process login action.
     *
     * @return void
     */
    private function processLogin()
    {
        if ($this->checkoutMethod === 'login') {
            if ($this->checkoutOnepage->getAuthenticationPopupBlock()->isVisible()) {
                $this->checkoutOnepage->getAuthenticationPopupBlock()->loginCustomer($this->customer);
                $this->clickProceedToCheckoutStep->run();
            } else {
                $this->checkoutOnepage->getLoginBlock()->loginCustomer($this->customer);
            }
        } elseif ($this->checkoutMethod === 'guest') {
            $this->checkoutOnepage->getLoginBlock()->fillGuestFields($this->customer);
        } elseif ($this->checkoutMethod === 'sign_in') {
            $this->checkoutOnepage->getAuthenticationWrapperBlock()->signInLinkClick();
            $this->checkoutOnepage->getAuthenticationWrapperBlock()->loginCustomer($this->customer);
        }
    }

    /**
     * Process customer register action.
     *
     * @return void
     */
    private function processRegister()
    {
        if ($this->checkoutMethod === 'register_before_checkout') {
            $this->checkoutOnepage->getAuthenticationPopupBlock()->createAccount();
            $this->customerAccountCreatePage->getRegisterForm()->registerCustomer($this->customer);
        }
    }

    /**
     * Logout customer on frontend.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->checkoutMethod === 'login' ||
            $this->checkoutMethod === 'sign_in' ||
            $this->checkoutMethod === 'register_before_checkout') {
            $this->logoutCustomerOnFrontend->run();
        }
    }
}
