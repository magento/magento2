<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepageSuccess;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Click 'Create an Account' button.
 */
class ClickCreateAccountStep implements TestStepInterface
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
    protected $checkoutMethod;

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
     * Click 'Create an Account' button.
     *
     * @return void
     */
    public function run()
    {
        if ($this->checkoutMethod === 'register') {
            $this->checkoutOnepageSuccess->getRegistrationBlock()->clickCreateAccountButton();
        }
    }
}
