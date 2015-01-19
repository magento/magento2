<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Mtf\TestStep\TestStepInterface;

/**
 * Class SelectPaymentMethodStep
 * Selecting payment method
 */
class SelectPaymentMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Payment information
     *
     * @var string
     */
    protected $payment;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, array $payment)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
    }

    /**
     * Run step that selecting payment method
     *
     * @return void
     */
    public function run()
    {
        if ($this->payment['method'] !== 'free') {
            $this->checkoutOnepage->getPaymentMethodsBlock()->selectPaymentMethod($this->payment);
        }
        $this->checkoutOnepage->getPaymentMethodsBlock()->clickContinue();
    }
}
