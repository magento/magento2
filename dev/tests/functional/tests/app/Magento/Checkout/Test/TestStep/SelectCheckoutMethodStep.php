<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Proceed to checkout from minicart step
     *
     * @var proceedToCheckoutFromMiniShoppingCartStep
     */
    private $proceedToCheckoutFromMiniShoppingCartStep;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param CustomerAccountCreate $customerAccountCreatePage
     * @param Customer $customer
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend
     * @param ClickProceedToCheckoutStep $clickProceedToCheckoutStep
     * @param ProceedToCheckoutFromMiniShoppingCartStep $proceedToCheckoutFromMiniShoppingCartStep
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        CustomerAccountCreate $customerAccountCreatePage,
        Customer $customer,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend,
        ClickProceedToCheckoutStep $clickProceedToCheckoutStep,
        ProceedToCheckoutFromMiniShoppingCartStep $proceedToCheckoutFromMiniShoppingCartStep,
        $checkoutMethod
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->customerAccountCreatePage = $customerAccountCreatePage;
        $this->customer = $customer;
        $this->logoutCustomerOnFrontend = $logoutCustomerOnFrontend;
        $this->clickProceedToCheckoutStep = $clickProceedToCheckoutStep;
        $this->checkoutMethod = $checkoutMethod;
        $this->proceedToCheckoutFromMiniShoppingCartStep = $proceedToCheckoutFromMiniShoppingCartStep;
    }

    /**
     * Run step that selecting checkout method.
     *
     * @return void
     */
    public function run()
    {
        sleep(20);
        $this->processLogin();
        $this->processRegister();
        sleep(20);
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
            $this->proceedToCheckoutFromMiniShoppingCartStep->run();
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
