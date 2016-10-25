<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Proceeds with checkout on PayPal side.
 */
class ContinueToPaypalStep implements TestStepInterface
{
    /**
     * @var CheckoutOnepage
     */
    private $checkoutOnepage;

    /**
     * @param CheckoutOnepage $checkoutOnepage
     */
    public function __construct(
        CheckoutOnepage $checkoutOnepage
    ) {
        $this->checkoutOnepage = $checkoutOnepage;
    }

    /**
     * Clicks Continue to PayPal button, proceeds with checkout on PayPal side.
     */
    public function run()
    {
        $parentWindow = $this->checkoutOnepage->getPaymentBlock()
            ->getSelectedPaymentMethodBlock()
            ->clickContinueToPaypal();
        $this->checkoutOnepage->getBraintreePaypalBlock()->process($parentWindow);
    }
}
