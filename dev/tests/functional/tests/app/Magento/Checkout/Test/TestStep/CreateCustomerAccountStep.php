<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create customer account on checkout one page success after place order.
 */
class CreateCustomerAccountStep implements TestStepInterface
{
    /**
     * Checkout one page success.
     *
     * @var CheckoutOnepageSuccess
     */
    protected $checkoutOnepageSuccess;

    /**
     * Checkout method.
     *
     * @var string
     */
    private $checkoutMethod;

    /**
     * @constructor
     * @param CheckoutOnepageSuccess $checkoutOnepageSuccess
     * @param string $checkoutMethod
     */
    public function __construct(CheckoutOnepageSuccess $checkoutOnepageSuccess, $checkoutMethod)
    {
        $this->checkoutOnepageSuccess = $checkoutOnepageSuccess;
        $this->checkoutMethod = $checkoutMethod;
    }

    /**
     * Create customer account.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkoutMethod === 'register') {
            $this->checkoutOnepageSuccess->getRegistrationBlock()->createAccount();
        }
    }
}
