<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\TestStep\TestStepInterface;

/**
 * Class SelectCheckoutMethodStep
 * Selecting checkout method
 */
class SelectCheckoutMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Checkout method
     *
     * @var string
     */
    protected $checkoutMethod;

    /**
     * Customer fixture
     *
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param CustomerInjectable $customer
     * @param string $checkoutMethod
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, CustomerInjectable $customer, $checkoutMethod)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->checkoutMethod = $checkoutMethod;
        $this->customer = $customer;
    }

    /**
     * Run step that selecting checkout method
     *
     * @return void
     * @throws \Exception
     */
    public function run()
    {
        $checkoutMethodBlock = $this->checkoutOnepage->getLoginBlock();
        switch ($this->checkoutMethod) {
            case 'guest':
                $checkoutMethodBlock->guestCheckout();
                $checkoutMethodBlock->clickContinue();
                break;
            case 'register':
                $checkoutMethodBlock->registerCustomer();
                $checkoutMethodBlock->clickContinue();
                break;
            case 'login':
                $checkoutMethodBlock->loginCustomer($this->customer);
                break;
            default:
                throw new \Exception("Undefined checkout method.");
                break;
        }
    }
}
