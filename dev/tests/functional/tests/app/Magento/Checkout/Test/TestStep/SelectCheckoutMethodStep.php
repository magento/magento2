<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Customer\Test\Fixture\Customer;
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
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend
     * @param ClickProceedToCheckoutStep $clickProceedToCheckoutStep
     * @param string $checkoutMethod
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Customer $customer,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend,
        ClickProceedToCheckoutStep $clickProceedToCheckoutStep,
        $checkoutMethod
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
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
        if ($this->checkoutMethod === 'login') {
            if ($this->checkoutOnepage->getAuthenticationPopupBlock()->isVisible()) {
                $this->checkoutOnepage->getAuthenticationPopupBlock()->loginCustomer($this->customer);
                $this->clickProceedToCheckoutStep->run();
            } else {
                $this->checkoutOnepage->getLoginBlock()->loginCustomer($this->customer);
            }
        } elseif ($this->checkoutMethod === 'guest') {
            $this->checkoutOnepage->getLoginBlock()->fillGuestFields($this->customer);
        }
    }

    /**
     * Logout customer on fronted.
     *
     * @return void
     */
    public function cleanup()
    {
        if ($this->checkoutMethod === 'login') {
            $this->logoutCustomerOnFrontend->run();
        }
    }
}
