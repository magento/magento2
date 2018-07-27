<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Shipping carrier and method.
     *
     * @var array
     */
    private $shipping;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param Customer $customer
     * @param LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend
     * @param string $checkoutMethod
     * @param array $shipping
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage,
        Customer $customer,
        LogoutCustomerOnFrontendStep $logoutCustomerOnFrontend,
        $checkoutMethod,
        array $shipping = []
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->customer = $customer;
        $this->logoutCustomerOnFrontend = $logoutCustomerOnFrontend;
        $this->checkoutMethod = $checkoutMethod;
        $this->shipping = $shipping;
    }

    /**
     * Run step that selecting checkout method.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkoutMethod === 'login') {
            $this->checkoutOnepage->getLoginBlock()->loginCustomer($this->customer);
        } elseif (($this->checkoutMethod === 'guest') && empty($this->shipping)) {
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
